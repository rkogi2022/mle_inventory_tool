<!-- Styles -->
<link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" />
<link href="<?php echo URL; ?>css/tables.css" rel="stylesheet" />

<main>
<div class="container-fluid px-4">
    <h3 class="mt-4">Issued Gear per Staff</h3>

    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="<?= URL; ?>home">Home</a></li>
        <li class="breadcrumb-item">Lab</li>
        <li class="breadcrumb-item">Issued Gear</li>
    </ol>

    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['message']); ?></div>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']); ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-user me-1"></i> Staff Issued Gear
        </div>

        <div class="card-body table-responsive">
            <table id="issuedStaffTable" class="table">
                <thead>
                    <tr>
                        <th>Staff Name</th>
                        <th>Duty Station</th>
                        <th>Total Items</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!empty($staffList)): ?>
                    <?php foreach ($staffList as $staff): ?>
                        <tr>
                            <td><?= htmlspecialchars($staff['staff_name'] ?? ''); ?></td>
                            <td><?= htmlspecialchars($staff['duty_station'] ?? ''); ?></td>
                            <td><?= htmlspecialchars($staff['total_items'] ?? 0); ?></td>
                            <td>
                                <a href="<?= URL; ?>gears/issuedPerStaff/<?= $staff['id']; ?>" 
                                   class="btn btn-primary btn-sm">
                                   View Gears
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="4">No issued gear found.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</main>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" crossorigin="anonymous"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const table = document.getElementById('issuedStaffTable');
    if (table) {
        new simpleDatatables.DataTable(table);
    }
});
</script>
