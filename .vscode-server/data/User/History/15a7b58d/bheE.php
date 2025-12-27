<?php
$get_user_id = $_GET['uid'] ?? null;
$user = $DB->SELECT_ONE_WHERE("users", "*", ["user_id" => $get_user_id]);
?>

<div class="row">
    <div class="col-md-6 offset-md-3">

        <div class="card card-body">
            <div class="text-center py-4">
                <img src="<?= UserImage($user['image']) ?>" class="img rounded-circle thumb mb-2" width="125"
                    height="125">
                <div class="mt-3">
                    <div class="fs-4 fw-bold"><?= $user['username'] ?></div>
                    <span><?= $user['user_id'] ?></span>
                </div>
            </div>
            <form id="formEditUser">
                <?= CSRF(); ?>
                <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">
                <input type="hidden" name="update_user" value="1">
                <!-- This will trigger form submission in the backend -->
                <table class="table table-light">
                    <tr>
                        <td>First Name</td>
                        <td><input type="text" name="firstname" value="<?= $user['firstname'] ?>" class="form-control">
                        </td>
                    </tr>
                    <tr>
                        <td>Last Name</td>
                        <td><input type="text" name="lastname" value="<?= $user['lastname'] ?>" class="form-control">
                        </td>
                    </tr>
                    <tr>
                        <td>Email</td>
                        <td><input type="text" name="email" value="<?= $user['email'] ?>" class="form-control"></td>
                    </tr>
                    <tr>
                        <td>Contact</td>
                        <td><input type="number" name="contact" value="<?= $user['contact'] ?>" class="form-control">
                        </td>
                    </tr>
                    <tr>
                        <td>Age</td>
                        <td><input type="number" name="age" value="<?= $user['age'] ?>" class="form-control"></td>
                    </tr>
                    <tr>
                        <td>Gender</td>
                        <td>
                            <select name="gender" class="form-control">
                                <option value="Male" <?= $user['gender'] == 'Male' ? 'selected' : '' ?>>Male</option>
                                <option value="Female" <?= $user['gender'] == 'Female' ? 'selected' : '' ?>>Female</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>Address</td>
                        <td><input type="text" name="address" value="<?= $user['address'] ?>" class="form-control"></td>
                    </tr>
                    <tr>
                        <td>Status</td>
                        <td>
                            <select name="status" class="form-control">
                                <option value="Pending" <?= isset($user['status']) && $user['status'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="Active" <?= isset($user['status']) && $user['status'] == 'Active' ? 'selected' : '' ?>>Active</option>
                                <option value="Inactive" <?= isset($user['status']) && $user['status'] == 'Inactive' ? 'selected' : '' ?>>Inactive</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>Role</td>
                        <td>
                            <select name="role" id="role" class="form-control">
                                <option value="Admin" <?= $user['role'] == 'Admin' ? 'selected' : '' ?>>Admin</option>
                                <option value="Employee" <?= $user['role'] == 'Employee' ? 'selected' : '' ?>>Employee</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>Position</td>
                        <td>
                            <select name="position" id="position" class="form-control">
                                <option value="Professor" <?= $user['position'] == 'Professor' ? 'selected' : '' ?>>Professor</option>
                                <option value="Cashier" <?= $user['position'] == 'Cashier' ? 'selected' : '' ?>>Cashier</option>
                                <option value="Staff" <?= $user['position'] == 'Clinic Staff' ? 'selected' : '' ?>>Clinic Staff</option>
                                <option value="Staff" <?= $user['position'] == 'Registrar Staff' ? 'selected' : '' ?>>Registrar Staff</option>
                                <option value="Staff" <?= $user['position'] == 'Enrollment Staff' ? 'selected' : '' ?>>Enrollment Staff</option>
                                <option value="Clerk" <?= $user['position'] == 'Clerk' ? 'selected' : '' ?>>Clerk</option>
                                <option value="Admin" <?= $user['position'] == 'Administrator' ? 'selected' : '' ?>>Administrator</option>
                                <option value="Manager" <?= $user['position'] == 'Manager' ? 'selected' : '' ?>>Manager</option>
                                <option value="Guard" <?= $user['position'] == 'Guard' ? 'selected' : '' ?>>Guard</option>
                                <option value="Human Resources" <?= $user['position'] == 'Human Resources' ? 'selected' : '' ?>>Human Resources</option>
                            </select>
                        </td>
                    </tr>
                </table>
                <div>
                    <button type="submit" id="btnUpdateUser" class="btn btn-primary w-100">Update</button>
                </div>
            </form>
            <div id="responseUpdateUser"></div>

        </div>
    </div>
</div>

<?php

$admins = $DB->SELECT_WHERE("positions", "*", ["role" => 'Admin']);
$admins = array_column($admins, 'position');
$admin_positions = "['" . implode("', '", $admins) . "']";

$employees = $DB->SELECT_WHERE("positions", "*", ["role" => 'Employee']);
$employees = array_column($employees, 'position');
$employee_positions = "['" . implode("', '", $employees) . "']";

?>

<script>
    $('#role').change(function() {

        var role = $(this).val();
        var positionOptions = {
            'Admin': <?= $admin_positions ?>,
            'Employee': <?= $employee_positions ?>
        };

        var $position = $('#position');
        $position.empty();

        var positions = positionOptions[role];
        $.each(positions, function(index, value) {
            $position.append($('<option>', {
                value: value,
                text: value
            }));
        });
    });
</script>

<script>
    $('#formEditUser').submit(function(e) {
        e.preventDefault();
        btnLoading('#btnUpdateUser');

        // Submit the form data via AJAX
        $.post('<?= Route('api/user-management/update_user.php'); ?>', $('#formEditUser').serialize(), function(res) {
            $('#responseUpdateUser').html(res);
            btnLoadingReset('#btnUpdateUser');
        }).fail(function() {
            $('#responseUpdateUser').html('<div class="text-red-500">An error occurred. Please try again.</div>');
            btnLoadingReset('#btnUpdateUser');
        });
    });
</script>