<?php
$complaints = $DB->SELECT_WHERE("complaints", "*", ["user_id" => AUTH_USER_ID], "ORDER BY created_at DESC");
?>
<?php
$feedbacks = $DB->SELECT_WHERE("feedback", "*", ["user_id" => AUTH_USER_ID], "ORDER BY created_at DESC");
?>
<?php
$recievers = $DB->SELECT_WHERE("complaints", "*", ["reciever_id" => AUTH_USER_ID]);
?>
<?php
$frecievers = $DB->SELECT_WHERE("feedback", "*", ["reciever_id" => AUTH_USER_ID]);
?>

<div class="card">
    <div class="card-body">
        <div class="mb-3">
            <a href="<?= ROUTE('complaint-feedback/complaint') ?>" type="button" class="btn btn-primary">
                <i class="bi bi-plus"></i> Add Complaint
            </a>
        </div>
        <table id="dataTable" class="table table-hover table-responsive">
            <thead class="table-secondary">
                <tr>
                    <th class="text-start">Action</th>
                    <th class="text-start">Complaint ID</th>
                    <th class="text-start">Title</th>
                    <th class="text-start">Status</th>
                    <th class="text-start">Created</th>
                    <th class="text-end">Updated</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($complaints as $complaint) { ?>
                    <tr>
                        <td class="d-flex gap-2">
                            <a href="<?= ROUTE('complaint-feedback/details_complaint?complaint_id=' . $complaint['complaint_id']) ?>" class="btn btn-sm btn-light"><i class="bi bi-eye"></i></a>
                            <button id="btnDeleteComplaint" class="btn btn-sm btn-danger" data-complaint_id="<?= $complaint['complaint_id'] ?>"><i class="bi bi-trash"></i></button>
                        </td>
                        <td class="text-start"><?= $complaint['complaint_id'] ?></td>
                        <td class="text-start"><?= $complaint['title'] ?></td>
                        <td class="text-start"><?= BadgeStatus(htmlspecialchars($complaint['status'])) ?></td>
                        <td class="text-start"><?= DATE_TIME_SHORT($complaint['created_at']) ?></td>
                        <td class="text-start"><?= DATE_TIME_SHORT($complaint['updated_at'] ?? '') ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<!-- <div class="card mt-5">
    <div class="card-body">
        <div class="mb-3 fw-bold">
            Complaint About You
        </div>
        <table id="dataTableTwo" class="table table-hover table-responsive">
            <thead class="table-secondary">
                <tr>
                    <th class="text-start">Action</th>
                    <th class="text-start">Complaint ID</th>
                    <th class="text-start">Title</th>
                    <th class="text-start">Status</th>
                    <th class="text-start">Created</th>
                    <th class="text-end">Updated</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recievers as $complaint) { ?>
                    <tr>
                        <td class="d-flex gap-2">
                            <a href="<?= ROUTE('complaint-feedback/details_complaint?complaint_id=' . $complaint['complaint_id']) ?>" class="btn btn-sm btn-light"><i class="bi bi-eye"></i></a>
                        </td>
                        <td class="text-start"><?= $complaint['complaint_id'] ?></td>
                        <td class="text-start"><?= $complaint['title'] ?></td>
                        <td class="text-start"><?= BadgeStatus(htmlspecialchars($complaint['status'])) ?></td>
                        <td class="text-start"><?= DATE_TIME_SHORT($complaint['created_at']) ?></td>
                        <td class="text-start"><?= DATE_TIME_SHORT($complaint['updated_at'] ?? '') ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div> -->

<!-- <div class="card mt-5">
    <div class="card-body">
        <div class="mb-3 fw-bold">
            Feedback About You
        </div>
        <table id="dataTableThree" class="table table-hover table-responsive">
            <thead class="table-secondary">
                <tr>
                    <th class="text-start">Action</th>
                    <th class="text-start">Feedback ID</th>
                    <th class="text-start">Title</th>
                    <th class="text-start">Rating</th>
                    <th class="text-start">Status</th>
                    <th class="text-start">Created</th>
                    <th class="text-end">Updated</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($frecievers as $feedback) { ?>
                    <tr>
                        <td class="d-flex gap-2">
                            <a href="<?= ROUTE('complaint-feedback/details_feedback?feedback_id=' . $feedback['feedback_id']) ?>" class="btn btn-sm btn-light"><i class="bi bi-eye"></i></a>
                        </td>
                        <td class="text-start"><?= $feedback['feedback_id'] ?></td>
                        <td class="text-start"><?= $feedback['title'] ?></td>
                        <td class="text-start"><?= $feedback['rating'] ?></td>
                        <td class="text-start"><?= BadgeStatus(htmlspecialchars($feedback['status'])) ?></td>
                        <td class="text-start"><?= DATE_TIME_SHORT($feedback['created_at']) ?></td>
                        <td class="text-start"><?= DATE_TIME_SHORT($feedback['updated_at'] ?? '') ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div> -->

<div class="card mt-5">
    <div class="card-body">
        <div class="mb-3">
            <a href="<?= ROUTE('complaint-feedback/feedback') ?>" type="button" class="btn btn-primary">
                <i class="bi bi-plus"></i> Add Feedback
            </a>
        </div>
        <table id="dataTableTwo" class="table table-hover table-responsive">
            <thead class="table-secondary">
                <tr>
                    <th class="text-start">Action</th>
                    <th class="text-start">Feedback ID</th>
                    <th class="text-start">Title</th>
                    <th class="text-start">Rating</th>
                    <th class="text-start">Status</th>
                    <th class="text-start">Created</th>
                    <th class="text-end">Updated</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($feedbacks as $feedback) { ?>
                    <tr>
                        <td class="d-flex gap-2">
                            <a href="<?= ROUTE('complaint-feedback/details_feedback?feedback_id=' . $feedback['feedback_id']) ?>" class="btn btn-sm btn-light"><i class="bi bi-eye"></i></a>
                            <button id="btnDeleteFeedback" class="btn btn-sm btn-danger" data-feedback_id="<?= $feedback['feedback_id'] ?>"><i class="bi bi-trash"></i></button>
                        </td>
                        <td class="text-start"><?= $feedback['feedback_id'] ?></td>
                        <td class="text-start"><?= $feedback['title'] ?></td>
                        <td class="text-start"><?= $feedback['rating'] ?></td>
                        <td class="text-start"><?= BadgeStatus(htmlspecialchars($feedback['status'])) ?></td>
                        <td class="text-start"><?= DATE_TIME_SHORT($feedback['created_at']) ?></td>
                        <td class="text-start"><?= DATE_TIME_SHORT($feedback['updated_at'] ?? '') ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    $(document).on('click', '#btnDeleteComplaint, #btnDeleteFeedback', function() {
        if (!confirm('Are you sure you want to delete this item?')) {
            return;
        }

        const complaintId = $(this).data('complaint_id'); // For recievers
        const feedbackId = $(this).data('feedback_id'); // For feedbacks
        const button = this; // Store the context of the button
        btnLoading(button); // Show loading spinner

        let endpoint = '../api/complaint-feedback/delete_complaint.php'; // Default endpoint for complaints
        let data = {
            complaint_id: complaintId
        };

        // If feedback
        if (feedbackId) {
            endpoint = '../api/complaint-feedback/delete_feedback.php';
            data = {
                feedback_id: feedbackId
            };
        }

        $.post(endpoint, data, function(res) {
            $('#responseDelete').html(res);
            btnLoadingReset(button); // Reset loading spinner
            if (res.includes('success')) {
                $(button).closest('tr').remove();
            }
        }).fail(function(jqXHR, textStatus, errorThrown) {
            $('#responseDelete').html(`<div class="alert alert-danger">An error occurred: ${errorThrown}</div>`);
            btnLoadingReset(button);
        });
    });
    new DataTable("#dataTableOne", {
        scrollX: true,
        layout: {
            topStart: {
                buttons: ["excel", "pdf", "colvis"],
            },
        },
    });
    new DataTable("#dataTableTwo", {
        scrollX: true,
        layout: {
            topStart: {
                buttons: ["excel", "pdf", "colvis"],
            },
        },
    });
    new DataTable("#dataTableThree", {
        scrollX: true,
        layout: {
            topStart: {
                buttons: ["excel", "pdf", "colvis"],
            },
        },
    });
</script>