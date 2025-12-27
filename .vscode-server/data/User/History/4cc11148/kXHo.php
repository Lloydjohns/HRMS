<?php
// Fetch surveys from the database
$surveysType = $DB->SELECT_WHERE("surveys", "*", ["position" => AUTH_USER['position']], "Group by survey_type");
$surveysType = $DB->SELECT_WHERE("surveys", "*", ["position" => AUTH_USER['user_id']], "Group by survey_type");

?>

<style>
    .evaluation-table th {
        text-align: center;
    }

    .evaluation-table td {
        text-align: center;
    }

    .evaluation-table input[type="radio"] {
        width: 30px;
        height: 30px;
    }
</style>

<div class="container mt-5">
    <h2 class="text-center">Employee Performance Evaluation</h2>
    <form id="evaluationForm">
        <?php foreach ($surveysType as $type) {

            $surveys = $DB->SELECT_WHERE("surveys", "*", ["survey_type" => $type["survey_type"]]);

        ?>

            <table class="table table-bordered evaluation-table">
                <thead>
                    <tr>
                        <th><?= $type["survey_type"] ?></th>
                        <th>1 - Needs Improvement</th>
                        <th>2 - Not Bad</th>
                        <th>3 - Good</th>
                        <th>4 - Very Good</th>
                        <th>5 - Excellent</th>
                        <th>6 - Very Excellent</th>
                    </tr>
                </thead>
                <tbody>
                    <?php

                    $i = 1;  // Counter for question numbering in the table qq
                    // Iterate through each survey and create a row for each question
                    foreach ($surveys as $survey) {
                        echo "<tr>";
                        echo "<td class='text-start'><b>" . $i++ . ".) </b>" . htmlspecialchars($survey['survey_question']) . "</td>";
                        for ($j = 1; $j <= 6; $j++) {
                            // Dynamically generate radio buttons for each score
                            echo "<td><input type='radio' name='survey_{$survey['id']}' value='{$j}' required></td>";
                        }
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        <?php } ?>

        <div class="form-group text-center">
            <button id="btnSurvey" type="submit" class="btn btn-primary">Submit Evaluation</button>
        </div>
    </form>
</div>

<div id="responseSurvey"></div>
<script>
    $(document).ready(function() {
        $('#evaluationForm').on('submit', function(event) {
            event.preventDefault(); // Prevent default form submission
            var surveyResults = {};

            // Collect all selected radio buttons
            $('input[type="radio"]:checked').each(function() {
                var question_id = $(this).attr('name').split('_')[1]; // Get survey question ID
                var rating = $(this).val(); // Get the selected value

                surveyResults[question_id] = rating; // Store the survey answer
            });

            // Send the survey results to the backend using AJAX
            $.ajax({
                url: '<?= ROUTE('api/survey/submit.php') ?>', // The PHP script handling the form submission
                type: 'POST',
                data: surveyResults, // Send collected survey results
                success: function(res) {
                    $('#responseSurvey').html(res);
                    btnLoadingReset('#btnSurvey');
                },
                error: function() {
                    $('#responseSurvey').html('<div class="text-red-500">An error occurred. Please try again.</div>');
                    btnLoadingReset('#btnSurvey');
                }
            });
        });
    });
</script>