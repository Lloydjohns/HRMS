<?php
require("../../app/init.php");
require("../auth/auth.php");

// Ensure at least one survey answer is submitted
if (empty($_POST['surveyQuestions']) || empty($_POST['surveyType'])) {
    die(toast("error", "No survey answers submitted."));
}

// Begin transaction    
$DB->DB->begin_transaction();

try {
    // Get the posted arrays
    $surveyQuestions = $_POST['surveyQuestions'];
    $surveyType = $_POST['surveyType'];  // Get the rating range defined by the admin
    $position = $_POST['position'];

    // Sanitize the position input
    $position = $DB->ESCAPE($position);

    // Check if the position exists in the database
    $positionCheck = $DB->SELECT_ONE_WHERE('positions', 'id', ['position' => $position]);
    if (!$positionCheck) {
        die(toast("error", "Invalid position selected."));
    }

    // Insert the survey questions into the surveys table
    foreach ($surveyQuestions as $index => $surveyQuestion) {

        $surveyQuestion = $DB->ESCAPE($surveyQuestion);

        // Insert the survey question into the surveys table
        $insertSurveyData = [
            'user_id' => AUTH_USER_ID,
            'position' => $position,
            'survey_type' => UPPER($surveyType),
            'survey_question' => $surveyQuestion,
            'created_at' => DATE_TIME,
            'updated_at' => DATE_TIME
        ];

        $insertSurvey = $DB->INSERT('surveys', $insertSurveyData);
        if (!$insertSurvey['success']) {
            $DB->DB->rollback();
            die(toast("error", "Failed to save survey question: " . htmlspecialchars($surveyQuestion)));
        }
    }

    // Insert a notification for the admin/creator or other relevant users
    $notificationData = [
        'user_id' => AUTH_USER_ID,
        'message' => "You have submitted an employee evaluation.",
        'action' => "Survey Submitted",
        'status' => 'Unread',
        'created_at' => DATE_TIME
    ];

    $insertNotification = $DB->INSERT('notifications', $notificationData);
    if (!$insertNotification['success']) {
        $DB->DB->rollback();
        die(toast("error", "Failed to create notification for survey submission."));
    }

    $DB->INSERT('notifications', [
        'user_id' => AUTH_USER_ID,
        'message' => "You have create a survey.",
        "action" => "Survey Saves",
        'status' => 'Unread',
        'created_at' => DATE_TIME
    ]);

    // Commit the transaction if everything is successful
    $DB->DB->commit();

    toast("success", "Survey submitted successfully.");
    die(redirect('/survey/main', 2000));
} catch (Exception $e) {
    // Rollback the transaction if there's an error
    $DB->DB->rollback();
    die(toast("error", "Something went wrong. Please try again. Error: " . $e->getMessage()));
}
