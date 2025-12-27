<?php
// Assume $DB is an instance of your Database class

// Check if a position is selected via GET parameter and escape it
$selectedPosition = isset($_GET['position']) ? $DB->ESCAPE($_GET['position']) : '';

// Fetch distinct positions from survey_result to populate the dropdown
$positions = $DB->SELECT("survey_result", "DISTINCT position");

// Initialize arrays to hold chart data
$groupedCategories = [];
$seriesData = [];
$overallAverages = [];

// Fetch distinct survey types from the surveys table
$survey_types = $DB->SELECT("surveys", "DISTINCT survey_type");

// Iterate over each survey type
foreach ($survey_types as $type) {
    // Build the WHERE clause for survey type and optionally for position
    $whereClause = "t1.survey_type = '{$type['survey_type']}'";
    if ($selectedPosition !== '') {
        $whereClause .= " AND t2.position = '{$selectedPosition}'";
    }

    // Build the SQL query to fetch survey questions and their average ratings
    $query = "
        SELECT 
            t1.survey_question, 
            AVG(t2.survey_rating) AS avg_rating
        FROM 
            surveys t1
        INNER JOIN 
            survey_result t2 ON t1.id = t2.survey_id
        WHERE 
            {$whereClause}
        GROUP BY 
            t1.survey_question
    ";

    // Execute the query using the SQL method
    $results = $DB->SQL($query);

    $questions = [];
    $averages = [];
    foreach ($results as $row) {
        $questions[] = $row['survey_question'];
        $averages[] = (float) $row['avg_rating'];
    }

    if (!empty($questions)) {
        $overallAvg = array_sum($averages) / count($averages);
        $groupedCategories[] = [
            'name' => $type['survey_type'],
            'categories' => $questions
        ];
        $seriesData = array_merge($seriesData, $averages);
        $overallAverages[] = [
            'name' => $type['survey_type'],
            'y' => $overallAvg
        ];
    }
}

// Encode the results as a JSON response
$response = json_encode([
    'groupedCategories' => $groupedCategories,
    'seriesData' => $seriesData,
    'overallAverages' => $overallAverages
]);
?>

<div class="container mt-5">
    <h2>Survey Results - Performance Evaluation</h2>
    <!-- Position Selection Form -->
    <form id="positionForm" method="GET" class="mb-3">
        <label for="position">Select Position:</label>
        <select id="position" name="position" onchange="document.getElementById('positionForm').submit()">
            <option value="">All Positions</option>
            <?php foreach ($positions as $pos): ?>
                <option value="<?= $pos['position'] ?>" <?= ($selectedPosition === $pos['position']) ? 'selected' : '' ?>>
                    <?= $pos['position'] ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>

    <!-- Container for Charts -->
    <div id="charts-container" class="mt-4 row">
        <div class="col-md-6">
            <div id="column-chart" style="height: 400px;"></div>
        </div>
        <div class="col-md-6">
            <div id="pie-chart" style="height: 400px;"></div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const data = <?= $response ?>;
    const container = document.getElementById('charts-container');

    if (data.groupedCategories.length === 0) {
        container.innerHTML = `<div class="alert alert-warning">No survey data available for the selected position.</div>`;
        return;
    }

    // Prepare data for column chart with numbered categories
    const allQuestions = [];
    const numberedCategories = [];
    const series = [];
    const questionMap = {}; // To store question text for tooltips
    
    data.groupedCategories.forEach((group, groupIndex) => {
        // Store questions and create numbered categories
        group.categories.forEach((question, qIndex) => {
            const questionNumber = allQuestions.length + 1;
            numberedCategories.push(`Q${questionNumber}`);
            allQuestions.push(question);
            questionMap[`Q${questionNumber}`] = question;
        });
        
        // Add series data for this group
        series.push({
            name: group.name,
            data: data.seriesData.slice(
                groupIndex * group.categories.length,
                (groupIndex + 1) * group.categories.length
            ),
            color: Highcharts.getOptions().colors[groupIndex]
        });
    });

    // Column chart for question ratings
    Highcharts.chart('column-chart', {
        chart: {
            type: 'column'
        },
        title: {
            text: 'Question Ratings by Survey Type'
        },
        xAxis: {
            categories: numberedCategories,
            labels: {
                rotation: -45,
                style: {
                    fontSize: '13px'
                }
            }
        },
        yAxis: {
            title: {
                text: 'Average Rating'
            },
            min: 0,
            max: 6
        },
        tooltip: {
            formatter: function() {
                // Show full question in tooltip
                return `<b>${questionMap[this.x]}</b><br/>
                        ${this.series.name}: <b>${this.y.toFixed(2)}</b>`;
            }
        },
        plotOptions: {
            column: {
                grouping: false,
                shadow: false,
                borderWidth: 0
            }
        },
        series: series,
        credits: {
            enabled: false
        }
    });

    // Pie chart for overall averages (unchanged)
    Highcharts.chart('pie-chart', {
        chart: {
            type: 'pie'
        },
        title: {
            text: 'Overall Averages by Survey Type'
        },
        tooltip: {
            pointFormat: '{series.name}: <b>{point.y:.2f}</b>'
        },
        plotOptions: {
            pie: {
                allowPointSelect: true,
                cursor: 'pointer',
                dataLabels: {
                    enabled: true,
                    format: '<b>{point.name}</b>: {point.y:.2f}',
                    distance: -30,
                    filter: {
                        property: 'percentage',
                        operator: '>',
                        value: 4
                    }
                }
            }
        },
        series: [{
            name: 'Overall Average',
            data: data.overallAverages,
            showInLegend: true
        }],
        credits: {
            enabled: false
        }
    });

});
</script>