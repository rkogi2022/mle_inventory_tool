<!-- Styles -->
<link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" />
<link href="<?php echo URL; ?>css/tables.css" rel="stylesheet" />

<main>
<div class="container-fluid px-4">
    <h3 class="mt-4">Issued Gear per Staff</h3>

    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="<?= URL; ?>home">Home</a></li>
        <li class="breadcrumb-item">Lab</li>
        <li class="breadcrumb-item"><a href="<?= URL; ?>gears/issuedStaffList">Gear Assignments</a></li>
        <li class="breadcrumb-item">Issued Gear</li>
    </ol>

    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['message']) ?></div>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']) ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-user me-1"></i> Issued Gear History
        </div>

        <div class="card-body table-responsive">
            <table id="issuedStaffTable" class="table">
                <thead>
                    <tr>
                        <th>Gear Item</th>
                        <th>Quantity</th>
                        <th>Condition</th>
                        <th>Date Issued</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!empty($issued)): ?>
                    <?php foreach ($issued as $issue): ?>
                        <tr>
                            <td><?= htmlspecialchars($issue['item_name'] ?? 'Unknown'); ?></td>
                            <td><?= htmlspecialchars($issue['quantity']); ?></td>
                            <td><?= htmlspecialchars($issue['item_condition']); ?></td>
                            <td><?= htmlspecialchars($issue['date_issued']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5">No issued gear found.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<a href="<?= URL; ?>gears/issuedStaffList" class="btn btn-secondary">Back to Assignments</a>
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
