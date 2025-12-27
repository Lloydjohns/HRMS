<?php
// Fetch positions from the database
$positions = $DB->SELECT("positions", "*");

$surveysType = []; // Initialize an empty array for surveys

// Fetch surveys if a position is selected
if (isset($_GET['position']) && !empty($_GET['position'])) {
    $selectedPosition = $_GET['position'];
    // Fetch survey types for the selected position
    $surveysType = $DB->SELECT_WHERE("surveys", "*", ["position" => $selectedPosition], "Group by survey_type");
}
?>

<div class="container mt-5">
    <h2 class="text-center">Manage Employee Performance Evaluation Surveys</h2>

    <!-- Position Selection -->
    <div class="form-group">
        <label for="positionSelect">Select Position:</label>
        <form id="surveyPositionForm" method="GET">
            <select id="positionSelect" class="form-control" name="position">
                <option value="">Select a Position</option>
                <?php foreach ($positions as $position): ?>
                    <?php if (!in_array($position['position'], ['Administrator', 'Manager'])): ?>
                        <option value="<?= $position['position']; ?>" <?= isset($selectedPosition) && $selectedPosition == $position['position'] ? 'selected' : ''; ?>><?= $position['position']; ?></option>
                    <?php endif; ?>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-primary mt-3">Load Surveys</button>
        </form>
    </div>

    <!-- Survey Display -->
    <?php if (!empty($surveysType)): ?>
        <div id="surveyContainer" class="mt-4">
            <?php foreach ($surveysType as $type): ?>
                <div class="surveyBlock" data-survey-type="<?= $type["survey_type"] ?>">
                    <form id="surveyForm_<?= htmlspecialchars($type["id"]) ?>" class="saveSurveyForm">
                        <h4>
                            <input type="text" class="form-control" name="surveyType" value="<?= htmlspecialchars($type["survey_type"]) ?>" />
                        </h4>
                        <ul>
                            <?php
                            $surveys = $DB->SELECT_WHERE("surveys", "*", ["survey_type" => $type["survey_type"]]);
                            foreach ($surveys as $survey): ?>
                                <li class="surveyItem" data-survey-id="<?= $survey['id'] ?>">
                                    <label for="surveyQuestion_<?= $survey['id'] ?>"><?= htmlspecialchars($survey['survey_question']) ?></label>
                                    <input type="text" class="form-control mt-2" name="surveyQuestion_<?= $survey['id'] ?>" value="<?= htmlspecialchars($survey['survey_question']) ?>" />
                                    <input type="hidden" name="surveyIds[]" value="<?= $survey['id'] ?>" />
                                    <button type="button" class="btn btn-danger mt-2 deleteSurveyQuestion" data-id="<?= $survey['id'] ?>">Delete</button>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <input type="hidden" name="position" value="<?= htmlspecialchars($selectedPosition) ?>" />
                        <button type="submit" class="btn btn-success mt-3 btnSave">Save Changes</button>
                    </form>
                    <hr />
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Add New Survey Type Form -->
    <div id="addSurveyTypeForm" class="mt-4">
        <h4>Add New Survey Type</h4>
        <form id="newSurveyForm">
            <div class="form-group">
                <label for="newSurveyType">Survey Type:</label>
                <input type="text" class="form-control" id="newSurveyType" name="newSurveyType" required />
            </div>
            <div class="form-group">
                <label for="newSurveyQuestion">Survey Question:</label>
                <textarea class="form-control" id="newSurveyQuestion" name="newSurveyQuestion[]" rows="3" required></textarea>
            </div>
            <button type="button" id="addNewQuestionButton" class="btn btn-secondary mt-4">Add Another Question</button>

            <div class="form-group text-center mt-4">
                <button id="btnCreateSurvey" type="submit" class="btn btn-primary">Create New Survey</button>
            </div>
        </form>
    </div>
</div>

<div id="responseSurvey"></div>

<script>
    $(document).ready(function() {
        // Show survey creation form when a position is selected
        $('#positionSelect').change(function() {
            var selectedPosition = $(this).val();
            $('#surveyCreationForm').show();
            console.log('Position selected: ' + selectedPosition);
        });

        // Add another survey question for the new survey
        $('#addNewQuestionButton').click(function() {
            var questionBlock = `
            <div class="form-group">
                <label for="newSurveyQuestion[]">Survey Question:</label>
                <textarea name="newSurveyQuestion[]" class="form-control" rows="3" required></textarea>
            </div>
        `;
            $('#newSurveyForm').append(questionBlock); // Append the new question block
        });

        // Form submission for creating a new survey
        $('#newSurveyForm').on('submit', function(event) {
            event.preventDefault(); // Prevent default form submission

            // Collect survey data
            var surveyData = {
                position: $('#positionSelect').val(),
                surveyType: $('#newSurveyType').val(),
                surveyQuestions: $('textarea[name="newSurveyQuestion[]"]').map(function() {
                    return $(this).val();
                }).get(),
            };

            // Send the survey data to the backend to save in the database
            $.ajax({
                url: '<?= ROUTE('api/survey/create.php'); ?>', // Endpoint for creating surveys
                type: 'POST',
                data: surveyData,
                success: function(res) {
                    $('#responseSurvey').html(res); // Display the HTML response from the server
                    $('#newSurveyForm')[0].reset(); // Reset the form after successful creation
                },
                error: function() {
                    $('#responseSurvey').html('<div class="alert alert-danger">An error occurred. Please try again.</div>');
                }
            });
        });

        // Handle delete survey question
        $('.deleteSurveyQuestion').click(function() {
            var surveyId = $(this).data('id');
            var $surveyItem = $(this).closest('.surveyItem');

            // Send the request to delete the survey question
            $.ajax({
                url: '<?= ROUTE('api/survey/delete.php'); ?>', // Endpoint for deleting survey questions
                type: 'POST',
                data: {
                    surveyId: surveyId
                },
                success: function(res) {
                    $('#responseSurvey').html(res); // Display the HTML response from the server
                    $surveyItem.remove(); // Remove the survey item from the DOM
                },
                error: function() {
                    $('#responseSurvey').html('<div class="alert alert-danger">An error occurred. Please try again.</div>');
                }
            });
        });

        // --- FORM SUBMISSION TO SAVE CHANGES ---
        $('.saveSurveyForm').on('submit', function(event) {
            event.preventDefault(); // Prevent default form submission

            // Collect the survey data
            var formData = $(this).serialize(); // Collect all the form data

            $.ajax({
                url: '<?= ROUTE('api/survey/save_survey.php'); ?>', // Endpoint to save survey changes
                type: 'POST',
                data: formData,
                beforeSend: function() {
                    $('.btnSave').prop('disabled', true).text('Saving...');
                },
                success: function(res) {
                    $('#responseSurvey').html(res); // Display the HTML response from the server
                    $('.btnSave').prop('disabled', false).text('Save Changes');
                },
                error: function() {
                    $('#responseSurvey').html('<div class="alert alert-danger">An error occurred. Please try again.</div>');
                    $('.btnSave').prop('disabled', false).text('Save Changes');
                }
            });
        });
    });
</script>