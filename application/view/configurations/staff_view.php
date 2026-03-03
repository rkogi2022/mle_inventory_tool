<link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" />
<link href="<?php echo URL; ?>css/tables.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" crossorigin="anonymous"></script>

<main>
    <div class="container-fluid px-4">
        <h3 class="mt-4">System Users</h3>

        <ol class="breadcrumb mb-4">
            <li class="breadcrumb-item"><a href="<?= URL; ?>home">Home</a></li>
            <li class="breadcrumb-item">Configurations</li>
            <li class="breadcrumb-item">Users</li>
        </ol>

        <div class="card mb-4">
            <div class="card-body">
                List of users, their roles and departments.
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-table me-1"></i></span>
                <div class="d-flex gap-2 align-items-center">
                    <!-- Re-confirm toggle -->
                    <?php
                    $activeSession = $this->model->getActiveReconfirmationSession();
                    $canToggle = !$activeSession || ($activeSession['initiated_by'] === $_SESSION['user_email']);
                    ?>

                    <div class="form-check form-switch" style="margin-top: 10px;">
                        <form method="POST" action="<?= URL ?>inventoryassignment/toggleReconfirmation">
                            <input type="hidden" name="enable_reconfirm" value="<?= $activeSession ? '0' : '1' ?>">
                            <input 
                                class="form-check-input" 
                                type="checkbox" 
                                id="adminToggle" 
                                <?= $activeSession ? 'checked' : '' ?> 
                                onchange="this.form.submit();" 
                                <?= $canToggle ? '' : 'disabled' ?>
                            >
                            <label class="form-check-label" for="adminToggle">RE-CONFIRM</label>
                            <?php if ($activeSession): ?>
                                <p class="text-muted" style="font-size: small;">
                                    Session initiated by: <strong><?= $activeSession['initiated_by'] ?></strong> on <?= date('Y-m-d', strtotime($activeSession['start_date'])) ?>
                                </p>
                            <?php endif; ?>
                        </form>
                    </div>

                    <!-- Add User button -->
                    <button class="add-btn" onclick="openUserModal()">Add User</button>
                </div>
                <!-- <button class="add-btn" onclick="window.location.href='<?= URL ?>inventoryassignment/triggerAcknowledgmentReminders'">
                    Trigger
                </button> -->

            </div>
        </div>



            <div class="card-body table-responsive">
                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success"> <?= htmlspecialchars($_GET['success']) ?> </div>
                <?php endif; ?>

                <table id="usersTable">
                    <thead>
                        <tr>
                            <th>Email</th>
                            <th>Department</th>
                            <th>Position</th>
                            <th>Role</th>
                            <th>Duty Station</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($users)): ?>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?= htmlspecialchars($user->email) ?></td>
                                    <td><?= htmlspecialchars($user->department_name) ?></td>
                                    <td><?= htmlspecialchars($user->position_name) ?></td>
                                    <td><?= htmlspecialchars($user->role) ?></td>
                                    <td><?= htmlspecialchars($user->dutystation) ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary"
                                            onclick="openUserModal('<?php echo $user->id; ?>', 
                                                                    '<?php echo $user->email; ?>', 
                                                                    '<?php echo $user->department; ?>', 
                                                                    '<?php echo $user->position; ?>', 
                                                                    '<?php echo $user->role; ?>',
                                                                    '<?php echo $user->dutystation; ?>')">
                                            Edit
                                        </button>

                                        <a href="<?= URL ?>users/delete?delete=<?= $user->id ?>" 
                                           class="btn btn-sm btn-outline-danger"
                                           onclick="return confirm('Are you sure you want to delete this user?');">
                                           Delete
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5">No users found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<!-- Add/Edit User Modal -->
<div id="userModal" class="modal fade" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="user-form" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="userModalTitle">Add User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="user_id">
                    <input type="hidden" name="mode" id="user_mode" value="add">

                    <div class="mb-3">
                        <label>Email:</label>
                        <input type="email" name="email" id="user_email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Department:</label>
                        <select name="department" id="user_department" class="form-control">
                            <option value="">Select Department</option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?= $dept->id ?>"><?= htmlspecialchars($dept->department_name) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Position:</label>
                        <select name="position" id="user_position" class="form-control">
                            <option value="">Select Position</option>
                            <?php foreach ($positions as $pos): ?>
                                <option value="<?= $pos->id ?>"><?= htmlspecialchars($pos->position_name) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Role:</label>
                        <select name="role" id="user_role" class="form-control">
                            <option value="">Select Role</option>
                            <?php foreach ($roles as $role): ?>
                                <option value="<?= $role ?>"><?= ucfirst($role) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="dutystation">Duty Station (Office):</label>
                        <select name="dutystation" id="user_dutystation" class="form-control">
                            <option value="">Select Duty Station</option>
                            <?php foreach ($offices as $office): ?>
                                <option value="<?= $office['id'] ?>">
                                    <?= htmlspecialchars($office['location_name'] . ' - ' . $office['office_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary" id="submitButton">Add User</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- JS Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function openUserModal(id = '', email = '', department = '', position = '', role = '', dutystation = '') {
    document.getElementById('user_id').value = id;
    document.getElementById('user_email').value = email;
    document.getElementById('user_department').value = department;
    document.getElementById('user_position').value = position;
    document.getElementById('user_role').value = role;
    document.getElementById('user_dutystation').value = dutystation;

    // Change modal title and form mode
    if (id) {
        document.getElementById('userModalTitle').innerText = 'Edit User';
        document.getElementById('user-form').action = '<?= URL ?>users/edit';
    } else {
        document.getElementById('userModalTitle').innerText = 'Add User';
        document.getElementById('user-form').action = '<?= URL ?>users/add';
    }

    new bootstrap.Modal(document.getElementById('userModal')).show();
    var myModal = new bootstrap.Modal(document.getElementById('userModal'));
    myModal.show();
}
// Handle the toggle action for enabling/disabling reconfirmation
function handleAdminToggle(checkbox) {
    fetch("<?= URL ?>inventoryassignment/toggleReconfirmStatus", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({ enabled: checkbox.checked })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert("Confirmation Session Activated.");
        } else {
            alert("Error: " + data.message);
        }
    })
    .catch(err => console.error("Request failed", err));
}


// Initialize DataTable
window.addEventListener('DOMContentLoaded', () => {
    const table = document.querySelector("#usersTable");
    if (table) {
        new simpleDatatables.DataTable(table);
    }
});
</script>
