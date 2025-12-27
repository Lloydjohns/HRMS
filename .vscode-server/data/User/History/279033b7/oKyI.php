<?php
require("../../app/init.php");
require("../auth/auth.php");

try {
    $DB->DB->begin_transaction();

    // Check if survey results are submitted
    if (empty($_POST)) {
        throw new Exception("No survey data received.");
    }

    $result = $DB->SELECT_ONE_WHERE("survey_result", "*", ["user_id" => AUTH_USER_ID]);

    if (!empty($result)) {
        die(toast("success", "You've already submitted!"));
    }

    // Iterate through all received survey answers and process each one
    foreach ($_POST as $surveyId => $rating) {
        // Validate the rating (must be between 1 and 6)
        if (!is_numeric($rating) || $rating < 1 || $rating > 6) {
            throw new Exception("Invalid rating for survey question ID: $surveyId.");
        }

        // Fetch the survey question associated with the survey ID
        $survey = $DB->SELECT_ONE_WHERE('surveys', '*', ['id' => $surveyId]);
        if (!$survey) {
            throw new Exception("Invalid survey question ID: $surveyId.");
        }

        // Insert notification into the database
        $notificationInsert = $DB->INSERT('notifications', [
            'user_id' => AUTH_USER_ID,
            'message' => "You have submitted a survey.",
            "action" => "Survey Submitted",
            'status' => 'Unread',
            'created_at' => DATE_TIME
        ]);

        // Check if the notification insert was successful
        if (!$notificationInsert) {
            throw new Exception("Failed to save notification for survey question ID: $surveyId.");
        }

        // Insert survey result into the database (assuming this step is also needed)
        $surveyResultInsert = $DB->INSERT('survey_result', [
            'user_id' => AUTH_USER_ID,
            'survey_id' => $surveyId,
            'survey_type' => $survey['survey_type'],
            'survey_rating' => $rating,
            'position' => AUTH_USER['position'],
            'created_at' => DATE_TIME
        ]);

        // Check if the survey result insert was successful
        if (!$surveyResultInsert) {
            throw new Exception("Failed to save rating for survey question ID: $surveyId.");
        }
    }

    // Commit the transaction
    $DB->DB->commit();
    // Success message
    toast("success", "Evaluation submitted successfully!");
    die(redirect('survey', 2000));
} catch (Exception $e) {
    // Rollback the transaction on error
    $DB->DB->rollback();
    die(toast("error", $e->getMessage()));
}
