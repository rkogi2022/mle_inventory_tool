<!-- Styles -->
<link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" />
<link href="<?php echo URL; ?>css/tables.css" rel="stylesheet" />

<main>
<div class="container-fluid px-4">
    <h3 class="mt-4">Consumable Items</h3>

    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="<?= URL; ?>home">Home</a></li>
        <li class="breadcrumb-item">Lab</li>
        <li class="breadcrumb-item">Consumables</li>
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
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-boxes me-1"></i> Consumables List</span>
            <div>
                <button class="btn add-btn btn-sm" onclick="openAddItemModal()">Add Item</button>
                <button class="btn btn-sm btn-outline-success" onclick="openAddReceiptModal()">Record Receipt</button>
                <button class="btn btn-sm btn-outline-warning" onclick="openAddIssueModal()">Record Issue</button>
                <a href="<?= URL ?>consumables/checkExpiringItems" class="btn btn-sm btn-warning">
                    Check Expiry
                </a>
            </div>
        </div>

        <div class="card-body table-responsive">
            <table id="consumablesTable" class="table">
                <thead>
                    <tr>
                        <th>Item Name</th>
                        <th>Item Code</th>
                        <th>Unit</th>
                        <th>Reorder Level</th>
                        <th>Current Balance</th>
                        <th>Expiry Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!empty($items)): ?>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item->item_name); ?></td>
                            <td><?= htmlspecialchars($item->item_code); ?></td>
                            <td><?= htmlspecialchars($item->unit); ?></td>
                            <td><?= htmlspecialchars($item->reorder_level); ?></td>
                            <td><?= htmlspecialchars($this->model->getConsumableBalance($item->id)); ?></td>
                            <td><?= htmlspecialchars($item->expiry_date ?? '-'); ?></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary"
                                    onclick="openEditItemModal(
                                        <?= $item->id ?>,
                                        '<?= htmlspecialchars($item->item_name, ENT_QUOTES) ?>',
                                        '<?= htmlspecialchars($item->item_code, ENT_QUOTES) ?>',
                                        '<?= htmlspecialchars($item->unit, ENT_QUOTES) ?>',
                                        <?= $item->reorder_level ?>,
                                        '<?= $item->expiry_date ?>'
                                    )">
                                    Edit
                                </button>

                                <a href="<?= URL ?>consumables/delete?delete=<?= $item->id ?>"
                                   class="btn btn-sm btn-outline-danger"
                                   onclick="return confirm('Are you sure you want to delete this item?')">
                                   Delete
                                </a>

                                <a href="<?= URL ?>consumables/viewBinCard/<?= $item->id ?>"
                                   class="btn btn-sm btn-outline-info">
                                   Bin Card
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="7">No consumable items found.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</main>

<!-- Add/Edit Item Modal -->
<div id="itemModal" class="modal fade" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="item-form" method="POST" action="<?= URL ?>consumables/addItem">
                <div class="modal-header">
                    <h5 class="modal-title" id="itemModalTitle">Add Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <input type="hidden" name="id" id="item_id">

                    <div class="mb-3">
                        <label class="form-label">Item Name:</label>
                        <input type="text" name="item_name" id="item_name" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Item Code:</label>
                        <input type="text" name="item_code" id="item_code" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Unit:</label>
                        <input type="text" name="unit" id="unit" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Reorder Level:</label>
                        <input type="number" name="reorder_level" id="reorder_level" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Expiry Date:</label>
                        <input type="date" name="expiry_date" id="expiry_date" class="form-control">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save Item</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Receipt Modal -->
<div id="receiptModal" class="modal fade" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="receipt-form" method="POST" action="<?= URL ?>consumables/addReceipt">
                <div class="modal-header">
                    <h5 class="modal-title">Record Receipt</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Item:</label>
                        <select name="item_id" class="form-control" required>
                            <?php foreach ($items as $item): ?>
                                <option value="<?= $item->id ?>"><?= htmlspecialchars($item->item_name) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Quantity:</label>
                        <input type="number" name="quantity" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Receiver:</label>
                        <select name="receiver_name" class="form-control" required>
                            <option value="">-- Select Receiver --</option>
                            <?php foreach ($users as $user): ?>
                                <option value="<?= htmlspecialchars($user['name']) ?>"><?= htmlspecialchars($user['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Date:</label>
                        <input type="date" name="transaction_date" class="form-control" required>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Save Receipt</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Issue Modal -->
<div id="issueModal" class="modal fade" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="issue-form" method="POST" action="<?= URL ?>consumables/addIssue">
                <div class="modal-header">
                    <h5 class="modal-title">Record Issue</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Item:</label>
                        <select name="item_id" class="form-control" required>
                            <?php foreach ($items as $item): ?>
                                <option value="<?= $item->id ?>"><?= htmlspecialchars($item->item_name) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Quantity:</label>
                        <input type="number" name="quantity" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Issuer:</label>
                        <select name="issuer_name" class="form-control" required>
                            <option value="">-- Select Issuer --</option>
                            <?php foreach ($users as $user): ?>
                                <option value="<?= htmlspecialchars($user['name']) ?>"><?= htmlspecialchars($user['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Date:</label>
                        <input type="date" name="transaction_date" class="form-control" required>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-warning">Save Issue</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" crossorigin="anonymous"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const table = document.getElementById('consumablesTable');
    if (table) {
        new simpleDatatables.DataTable(table);
    }

    window.openAddItemModal = function () {
        const form = document.getElementById('item-form');
        form.action = '<?= URL ?>consumables/addItem';
        document.getElementById('itemModalTitle').textContent = 'Add Item';
        form.reset();
        new bootstrap.Modal(document.getElementById('itemModal')).show();
    }

    window.openEditItemModal = function (id, name, code, unit, reorder, expiry) {
        const form = document.getElementById('item-form');
        form.action = '<?= URL ?>consumables/editItem';
        document.getElementById('itemModalTitle').textContent = 'Edit Item';
        document.getElementById('item_id').value = id;
        document.getElementById('item_name').value = name;
        document.getElementById('item_code').value = code;
        document.getElementById('unit').value = unit;
        document.getElementById('reorder_level').value = reorder;
        document.getElementById('expiry_date').value = expiry ?? '';
        new bootstrap.Modal(document.getElementById('itemModal')).show();
    }

    window.openAddReceiptModal = function () {
        document.getElementById('receipt-form').reset();
        new bootstrap.Modal(document.getElementById('receiptModal')).show();
    }

    window.openAddIssueModal = function () {
        document.getElementById('issue-form').reset();
        new bootstrap.Modal(document.getElementById('issueModal')).show();
    }
});
</script>