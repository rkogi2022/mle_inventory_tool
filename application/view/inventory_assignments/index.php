<link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" crossorigin="anonymous"></script>
<link href="<?php echo URL; ?>css/tables.css" rel="stylesheet">

<main>
<div class="container-fluid px-4">
<h3 class="mt-4">Assignments</h3>

<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item"><a href="<?php echo URL; ?>home">Home</a></li>
    <li class="breadcrumb-item">Item Assignments</li>
</ol>
<div class="card mb-4">
    <div class="card-body">
        Here is the list of items allocated to different users.
    </div>
</div>
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-table me-1"></i></span>

        <!-- Assign New Item Button -->
        <form action="<?= URL; ?>inventoryassignment/add" method="GET" class="mb-0">
            <button type="submit" class="add-btn">Assign New Item</button>
        </form>
        <form action="<?= URL; ?>inventoryassignment/triggerAcknowledgmentReminders" method="GET" class="mb-0" onsubmit="return confirm('Send reminder emails to all users with pending acknowledgment?');">
    <button type="submit" class="add-btn">Send Acknowledgment Reminders</button>
</form>
    </div>

    <div class="card-body table-responsive">

        <!-- Success & Error Messages -->
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success"><?= htmlspecialchars($_GET['success']); ?></div>
        <?php elseif (isset($_GET['error'])): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>
        
        <div class="card-body table-responsive">
        <!-- DataTable -->
        <table id="datatablesSimple">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Location</th>
                    <th>Item</th>
                    <th>Serial Number</th>
                    <th>Date Assigned</th>
                    <th>Managed By</th>
                    <th>Acknowledgment</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($assignments)): ?>
                    <?php foreach ($assignments as $assignment): ?>
                        <tr>
                            <td><?= htmlspecialchars($assignment['user_name'] ?? 'N/A'); ?></td>
                            <td><?= htmlspecialchars($assignment['email'] ?? 'N/A'); ?></td>
                            <td><?= htmlspecialchars(($assignment['department'] ?? 'N/A') . ' - ' . ($assignment['position'] ?? 'N/A')); ?></td>
                            <td><?= htmlspecialchars($assignment['location'] ?? 'N/A'); ?></td>
                            <td><?= htmlspecialchars($assignment['description'] ?? 'N/A'); ?></td>
                            <td><?= htmlspecialchars($assignment['serial_number'] ?? 'N/A'); ?></td>
                            <td><?= htmlspecialchars($assignment['date_assigned'] ?? 'N/A'); ?></td>
                            <td><?= htmlspecialchars($assignment['managed_by'] ?? 'N/A'); ?></td>
                            <td>
                                <span class="<?= $assignment['acknowledgment_status'] === 'acknowledged' ? 'badge bg-success' : 'badge bg-warning text-dark'; ?>">
                                    <?= ucfirst($assignment['acknowledgment_status'] ?? 'Pending'); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($assignment['acknowledgment_status'] === 'pending'): ?>
                                    <div class="d-flex gap-2">
                                        <!-- Edit Button -->
                                        <a href="<?= URL ?>inventoryassignment/edit/<?= $assignment['id']; ?>" 
                                        class="btn btn-sm btn-outline-primary" title="Edit">
                                            Edit
                                        </a>

                                        <!-- Delete Button -->
                                        <form action="<?= URL; ?>inventoryassignment/delete" method="POST" 
                                            onsubmit="return confirm('Are you sure you want to delete this assignment?');" 
                                            style="margin: 0;">
                                            <input type="hidden" name="assignment_id" value="<?= htmlspecialchars($assignment['id']); ?>">
                                            <button type="submit" name="delete" class="btn btn-sm btn-outline-danger" title="Delete">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">N/A</span>
                                <?php endif; ?>
                            </td>

                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="10" class="text-center">No item assignments found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</div>
</main>
<script>
    window.addEventListener('DOMContentLoaded', () => {
        const datatable = document.getElementById('datatablesSimple');
        if (datatable) {
            new simpleDatatables.DataTable(datatable);
        }
    });
</script>
