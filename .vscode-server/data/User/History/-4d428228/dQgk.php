<?php
require("../../app/init.php");
require("../auth/auth.php");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require("../../vendor/autoload.php");

CSRF('verify'); // Verifying CSRF token

if (isset($_POST)) {
    // Check if all required POST data exists
    $requiredFields = ['employee_id', 'assignment_id', 'start_date', 'completion_date', 'status'];
    foreach ($requiredFields as $field) {
        if (!isset($_POST[$field])) {
            die(toast('error', "Missing field: $field"));
        }
    }

    // Get data from POST
    $employee_id = $_POST['employee_id'];
    $assignment_id = $_POST['assignment_id'];
    $start_date = $_POST['start_date'];
    $completion_date = $_POST['completion_date'] ?? null;
    $status = $_POST['status'];
    $file_upload = $_FILES['attachment']; // Get the file from the form

    // Begin Transaction
    $DB->DB->begin_transaction();

    try {
        // Validate that the employee exists
        $employee = $DB->SELECT_ONE_WHERE("users", "*", ["user_id" => $employee_id]);
        if (!$employee) {
            $DB->DB->rollback();
            die(toast("error", "Employee not found."));
        }

        // Get current assignment to check for existing attachment
        $current_assignment = $DB->SELECT_ONE_WHERE("employee_training_assignments", "*", ["assignment_id" => $assignment_id]);
        if (!$current_assignment) {
            $DB->DB->rollback();
            die(toast("error", "Training assignment not found."));
        }

        // Process file upload (if any)
        $attachment_name = $current_assignment['attachment']; // Default to current attachment

        if (isset($file_upload['name']) && !empty($file_upload['name']) && $file_upload['error'] == 0) {
            $upload_dir = '../../upload/training/';
            $file_name = $file_upload['name'];
            $file_tmp = $file_upload['tmp_name'];
            $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
            $new_file_name = uniqid(); // Generate unique name for the file

            // Validate file type
            $allowed_extensions = ['pdf', 'docx', 'jpg', 'jpeg', 'png'];
            if (!in_array(strtolower($file_extension), $allowed_extensions)) {
                $DB->DB->rollback();
                die(toast("error", "Invalid file type for file: " . $file_name));
            }

            // Upload file
            $file_destination = $upload_dir . $new_file_name . '.' . $file_extension;
            if (move_uploaded_file($file_tmp, $file_destination)) {
                $attachment_name = $new_file_name . '.' . $file_extension;

                // Optionally, delete the old file
                if ($current_assignment['attachment'] && file_exists($upload_dir . $current_assignment['attachment'])) {
                    unlink($upload_dir . $current_assignment['attachment']);
                }
            } else {
                $DB->DB->rollback();
                die(toast("error", "Failed to upload file"));
            }
        }

        // Prepare data for update
        $assignmentData = [
            'start_date' => $start_date,
            'completion_date' => $completion_date,
            'status' => $status,
            'attachment' => $attachment_name, // Store file path
            'updated_at' => DATE_TIME
        ];

        // Update the training assignment in the database
        $update_status = $DB->UPDATE('employee_training_assignments', $assignmentData, ['assignment_id' => $assignment_id]);
        if (!$update_status) {
            $DB->DB->rollback();
            die(toast("error", "Failed to update the training assignment."));
        }

        // If status is completed, update user status to Active
        if ($status === 'Completed') {
            $update_user = $DB->UPDATE('users', ['status' => 'Active', 'updated_at' => DATE_TIME], ['user_id' => $employee_id]);
            if (!$update_user) {
                $DB->DB->rollback();
                die(toast("error", "Failed to update user status."));
            }

            // Add notification about user status change
            $DB->INSERT('notifications', [
                'user_id' => $employee_id,
                'message' => "Your account status has been updated to Active after completing the training.",
                "action" => "Status Update",
                'status' => 'Unread',
                'created_at' => DATE_TIME
            ]);
        }

        if ($status === 'Failed') {
             $DB->INSERT('notifications', [
                'user_id' => $employee_id,
                'message' => "Your training program has been marked as Failed. Please contact your supervisor for further instructions.",
                "action" => "Training Status Update",
                'status' => 'Unread',
                'created_at' => DATE_TIME
            ]);
        }
        // Send confirmation email
        $training = $DB->SELECT_ONE_WHERE('training_programs', '*', ['training_id' => $current_assignment['training_id']]);
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = APP_EMAIL; // SMTP username
            $mail->Password = APP_EMAIL_PASS; // SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom(APP_EMAIL, 'Bestlink College');
            $mail->addAddress($employee['email'], $employee['firstname'] . ' ' . $employee['lastname']);
            $mail->isHTML(true);

            // Modify email content based on status
            if ($status === 'Completed') {
                $mail->Subject = 'Training Program Completed';
                $mail->Body = "<p>Dear {$employee['firstname']},</p>
                                <p>Congratulations! You have successfully completed your training program:</p>
                                <ul>
                                    <li><strong>Training Program:</strong> {$training['program_name']}</li>
                                    <li><strong>Start Date:</strong> {$start_date}</li>
                                    <li><strong>Completion Date:</strong> {$completion_date}</li>
                                </ul>
                                <p>Your account status has been updated to Active.</p>";
            } elseif ($status === 'Failed') {
                $mail->Subject = 'Training Program Failed';
                $mail->Body = "<p>Dear {$employee['firstname']},</p>
                                <p>We regret to inform you that your training program has been marked as Failed:</p>
                                <ul>
                                    <li><strong>Training Program:</strong> {$training['program_name']}</li>
                                    <li><strong>Start Date:</strong> {$start_date}</li>
                                    <li><strong>Completion Date:</strong> {$completion_date}</li>
                                    <li><strong>Status:</strong> {$status}</li>
                                </ul>
                                <p>Please contact your supervisor for further instructions.</p>";
            } else {
                $mail->Subject = 'Training Program Updated';
                $mail->Body = "<p>Dear {$employee['firstname']},</p>
                                <p>Your training program has been updated as follows:</p>
                                <ul>
                                    <li><strong>Training Program:</strong> {$training['program_name']}</li>
                                    <li><strong>Start Date:</strong> {$start_date}</li>
                                    <li><strong>Completion Date:</strong> {$completion_date}</li>
                                    <li><strong>Status:</strong> {$status}</li>
                                </ul>
                                <p>Please find the updated attachment for further details.</p>";
            }

            // Attach the uploaded file (if any)
            if ($attachment_name) {
                $file_path = '../../upload/training/' . $attachment_name;
                if (file_exists($file_path)) {
                    $mail->addAttachment($file_path);
                }
            }

            // Send the email
            if (!$mail->send()) {
                error_log("Failed to send training update email: " . $mail->ErrorInfo);
            }
        } catch (Exception $e) {
            error_log("Email error: " . $e->getMessage());
        }

        // Insert a notification for the training update
        $DB->INSERT('notifications', [
            'user_id' => $employee_id,
            'message' => "Your training program has been updated: {$training['program_name']}.",
            "action" => "Training Assignment",
            'status' => 'Unread',
            'created_at' => DATE_TIME
        ]);

        // Commit transaction
        $DB->DB->commit();
        toast("success", "Training assignment updated successfully.");
        die(redirect(ROUTE('training-program'), 2000)); // Redirect to the training assignments page

    } catch (Exception $e) {
        // Rollback transaction on error
        $DB->DB->rollback();
        die(toast("error", "Something went wrong. Please try again. Error: " . $e->getMessage()));
    }
}
