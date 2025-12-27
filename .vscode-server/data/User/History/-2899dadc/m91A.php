<?php
// Get the employee_id and assignment_id from the query string
$employee_id = $_GET['emp_id'] ?? null;  // Correctly capture employee_id
$assignment_id = $_GET['ass_id'] ?? null; // Correctly capture assignment_id

// Check if both employee_id and assignment_id are provided
if ($employee_id && $assignment_id) {
    // Fetch the training assignment from the database based on employee_id and assignment_id
    $assignment = $DB->SELECT_ONE_WHERE(
        'employee_training_assignments',
        '*',
        ['employee_id' => $employee_id, 'assignment_id' => $assignment_id]
    );

    // Check if the result exists, otherwise show an error
    if (!$assignment) {
        die(toast("error", "No training assignment found with the provided parameters."));
    }
} else {
    die(toast("error", "Missing employee_id or assignment_id."));
}
?>

<div class="row">
    <div class="col-md-6 offset-md-3">
        <div class="card card-body">
            <div class="text-center py-4">
                <div class="fs-4 fw-bold">Update Training Assignment</div>
            </div>
            <form id="formEditAssignment">
                <?= CSRF() ?>
                <input type="hidden" name="employee_id" value="<?= $assignment['employee_id'] ?>">
                <input type="hidden" name="assignment_id" value="<?= $assignment['assignment_id'] ?>">

                <!-- Start Date -->
                <div class="mb-3">
                    <label for="start_date" class="form-label">Start Date</label>
                    <input type="date" name="start_date" id="start_date" class="form-control" value="<?= $assignment['start_date'] ?>" required>
                </div>

                <!-- Completion Date -->
                <div class="mb-3">
                    <label for="completion_date" class="form-label">Completion Date</label>
                    <input type="date" name="completion_date" id="completion_date" class="form-control" value="<?= $assignment['completion_date'] ?>" required>
                </div>

                <div class="mb-3">
                    <label for="status" class="form-label">Select Training Program Status</label>
                    <select name="status" id="status" class="form-control" required>
                        <option value="Not Started" <?= $assignment['status'] == 'Not Started' ? 'selected' : '' ?>>Not Started</option>
                        <option value="In Progress" <?= $assignment['status'] == 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                        <option value="Completed" <?= $assignment['status'] == 'Completed' ? 'selected' : '' ?>>Completed</option>
                        <option value="Failed" <?= $assignment['status'] == 'Failed' ? 'selected' : '' ?>>Failed</option>
                    </select>
                </div>

                <!-- File Upload -->
                <div class="mb-3">
                    <label for="attachment" class="form-label">Upload Documents (if any)</label>
                    <input type="file" name="attachment" id="attachment" multiple accept=".pdf,.docx,.jpg,.jpeg,.png" class="form-control">
                </div>

                <button id="btnEditAssignment" type="submit" class="btn btn-primary w-100">Update Training Assignment</button>
            </form>
            <div id="responseEditAssignment"></div>
        </div>
    </div>
</div>

<script>
    $('#formEditAssignment').submit(function(e) {
        e.preventDefault();
        btnLoading('#btnEditAssignment'); // Assuming you have a loading function
        var formData = new FormData(this);

        $.ajax({
            url: '<?= ROUTE('api/training-program/update_assignments.php'); ?>', // Change this to the backend URL
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(res) {
                $('#responseEditAssignment').html(res);
                btnLoadingReset('#btnEditAssignment');
            },
            error: function() {
                $('#responseEditAssignment').html('<div class="text-red-500">An error occurred. Please try again.</div>');
                btnLoadingReset('#btnEditAssignment');
            }
        });
    });
</script>