<!-- Styles -->
<link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" />
<link href="<?= URL; ?>css/tables.css" rel="stylesheet" />

<main>
<div class="container-fluid px-4">
    <h3 class="mt-4">Bin Card</h3>

    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="<?= URL; ?>home">Home</a></li>
        <li class="breadcrumb-item"><a href="<?= URL; ?>consumables">Consumables</a></li>
        <li class="breadcrumb-item">Bin Card</li>
    </ol>

    <!-- Item Info -->
    <div class="card mb-4">
        <div class="card-header">
            <strong>Item Details</strong>
        </div>
        <div class="card-body">
            <p><strong>Name:</strong> <?= htmlspecialchars($item->item_name); ?></p>
            <p><strong>Code:</strong> <?= htmlspecialchars($item->item_code); ?></p>
            <p><strong>Unit:</strong> <?= htmlspecialchars($item->unit); ?></p>
            <p><strong>Reorder Level:</strong> <?= htmlspecialchars($item->reorder_level); ?></p>
            <p><strong>Current Balance:</strong> <?= htmlspecialchars($balance); ?></p>
        </div>
    </div>

    <!-- Transactions Table -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-history me-1"></i> Transaction History
        </div>
        <div class="card-body table-responsive">
            <table id="binCardTable" class="table table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Transaction Type</th>
                        <th>Quantity</th>
                        <th>Date</th>
                        <th>Receiver</th>
                        <th>Issuer</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($transactions)): ?>
                        <?php foreach ($transactions as $index => $tx): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><?= ucfirst(htmlspecialchars($tx->transaction_type)) ?></td>
                                <td><?= htmlspecialchars($tx->quantity) ?></td>
                                <td><?= htmlspecialchars($tx->transaction_date) ?></td>
                                <td><?= htmlspecialchars($tx->receiver_name ?? '-') ?></td>
                                <td><?= htmlspecialchars($tx->issuer_name ?? '-') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7">No transactions found for this item.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <a href="<?= URL; ?>consumables" class="btn btn-secondary">Back to Consumables</a>
</div>
</main>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" crossorigin="anonymous"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const table = document.getElementById('binCardTable');
    if (table) {
        new simpleDatatables.DataTable(table);
    }
});
</script>
