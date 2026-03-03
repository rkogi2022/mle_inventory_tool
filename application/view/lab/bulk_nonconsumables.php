<!-- Styles -->
<link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" />
<link href="<?= URL; ?>css/tables.css" rel="stylesheet" />

<main>
<div class="container-fluid px-4">
    <h3 class="mt-4">Bulk Non-Consumables</h3>

    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="<?= URL; ?>home">Home</a></li>
        <li class="breadcrumb-item">Lab</li>
        <li class="breadcrumb-item">Non-Consumables</li>
    </ol>

    <?php if(isset($_SESSION['message'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['message']) ?></div>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>

    <?php if(isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']) ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Top-right buttons -->
    <div class="d-flex justify-content-end mb-3 gap-2">
        <button class="btn btn-success btn-sm" onclick="openAddBulkModal()">Add Bulk Item</button>
        <button class="btn btn-warning btn-sm" onclick="openAddBulkMovementModal()">Add Movement</button>
    </div>

    <div class="card mb-4">
        <div class="card-body table-responsive">
            <table id="bulkTable" class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th>Item Name</th>
                        <th>Description</th>
                        <th>Specification</th>
                        <th>Condition</th>
                        <th>Qty In Store</th>
                        <th>Last Checked</th>
                        <th>Remarks</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if(!empty($allBulk)): ?>
                    <?php foreach($allBulk as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['item_name']) ?></td>
                            <td><?= htmlspecialchars($item['description']) ?></td>
                            <td><?= htmlspecialchars($item['specification']) ?></td>
                            <td><?= htmlspecialchars($item['condition_status']) ?></td>
                            <td><?= htmlspecialchars($item['quantity_in_store']) ?></td>
                            <td><?= htmlspecialchars($item['last_checked_date']) ?></td>
                            <td><?= htmlspecialchars($item['remarks']) ?></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary"
                                        onclick="openEditBulkModal(
                                            <?= $item['id'] ?>,
                                            '<?= htmlspecialchars($item['item_name'], ENT_QUOTES) ?>',
                                            '<?= htmlspecialchars($item['description'], ENT_QUOTES) ?>',
                                            '<?= htmlspecialchars($item['specification'], ENT_QUOTES) ?>',
                                            '<?= $item['condition_status'] ?>',
                                            '<?= $item['last_checked_date'] ?>',
                                            '<?= htmlspecialchars($item['remarks'], ENT_QUOTES) ?>'
                                        )">Edit</button>

                                <button class="btn btn-sm btn-outline-info"
                                        onclick="openViewBulkMovementsModal(<?= $item['id'] ?>)">Movements</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="8">No bulk non-consumable items found.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</main>

<!-- Add/Edit Bulk Item Modal -->
<div id="bulkModal" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="bulk-form" method="POST" action="<?= URL ?>nonconsumables/addBulk">
                <div class="modal-header">
                    <h5 class="modal-title" id="bulkModalTitle">Add Bulk Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="bulk_id">

                    <div class="mb-2">
                        <label>Item Name</label>
                        <input type="text" name="item_name" id="bulk_name" class="form-control" required>
                    </div>

                    <div class="mb-2">
                        <label>Description</label>
                        <textarea name="description" id="bulk_description" class="form-control"></textarea>
                    </div>

                    <div class="mb-2">
                        <label>Specification</label>
                        <input type="text" name="specification" id="bulk_specification" class="form-control">
                    </div>

                    <div class="mb-2">
                        <label>Condition Status</label>
                        <select name="condition_status" id="bulk_condition" class="form-control">
                            <option value="good">Good</option>
                            <option value="fair">Fair</option>
                            <option value="mixed">Mixed</option>
                            <option value="faulty">Faulty</option>
                        </select>
                    </div>

                    <div class="mb-2">
                        <label>Last Checked Date</label>
                        <input type="date" name="last_checked_date" id="bulk_last_checked" class="form-control">
                    </div>

                    <div class="mb-2">
                        <label>Remarks</label>
                        <textarea name="remarks" id="bulk_remarks" class="form-control"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary">Save Item</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Bulk Movement Modal -->
<div id="addBulkMovementModal" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="bulk-movement-form" method="POST" action="<?= URL ?>nonconsumables/addBulkMovement">
                <div class="modal-header">
                    <h5 class="modal-title">Record Bulk Item Movement</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- Select Item -->
                    <div class="mb-2">
                        <label>Select Item</label>
                        <select name="bulk_id" id="movement_bulk_id" class="form-control" required>
                            <option value="">-- Select Item --</option>
                            <?php foreach($allBulk as $item): ?>
                                <option value="<?= $item['id'] ?>">
                                    <?= htmlspecialchars($item['item_name']) ?> (Qty: <?= $item['quantity_in_store'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Movement Type -->
                    <div class="mb-2">
                        <label>Movement Type</label>
                        <select name="movement_type" class="form-control" required>
                            <option value="">-- Select Type --</option>
                            <option value="received">Received</option>
                            <option value="dispatched">Dispatched</option>
                            <option value="returned">Returned</option>
                            <option value="damaged">Damaged</option>
                            <option value="disposed">Disposed</option>
                        </select>
                    </div>

                    <!-- Quantity -->
                    <div class="mb-2">
                        <label>Quantity</label>
                        <input type="number" name="quantity" class="form-control" min="1" required>
                    </div>

                    <!-- Destination -->
                    <div class="mb-2">
                        <label>Destination / Receiver</label>
                        <input type="text" name="destination" class="form-control" placeholder="Department or person">
                    </div>

                    <!-- Remarks -->
                    <div class="mb-2">
                        <label>Remarks</label>
                        <textarea name="remarks" class="form-control"></textarea>
                    </div>

                    <!-- Movement Date -->
                    <div class="mb-2">
                        <label>Movement Date</label>
                        <input type="date" name="movement_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary">Record Movement</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bulk Movements Modal (View) -->
<div id="bulkMovementsModal" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bulk Movements</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="bulkMovementsContent">
                <!-- AJAX-loaded content -->
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const table = document.getElementById('bulkTable');
    if(table) new simpleDatatables.DataTable(table);

    window.openAddBulkModal = function() {
        document.getElementById('bulk-form').action = '<?= URL ?>nonconsumables/addBulk';
        document.getElementById('bulkModalTitle').textContent = 'Add Bulk Item';
        document.getElementById('bulk_id').value = '';
        document.getElementById('bulk_name').value = '';
        document.getElementById('bulk_description').value = '';
        document.getElementById('bulk_specification').value = '';
        document.getElementById('bulk_condition').value = 'good';
        document.getElementById('bulk_last_checked').value = '';
        document.getElementById('bulk_remarks').value = '';
        new bootstrap.Modal(document.getElementById('bulkModal')).show();
    };

    window.openEditBulkModal = function(id, name, description, specification, condition, last_checked, remarks) {
        document.getElementById('bulk-form').action = '<?= URL ?>nonconsumables/updateBulk/' + id;
        document.getElementById('bulkModalTitle').textContent = 'Edit Bulk Item';
        document.getElementById('bulk_id').value = id;
        document.getElementById('bulk_name').value = name;
        document.getElementById('bulk_description').value = description;
        document.getElementById('bulk_specification').value = specification;
        document.getElementById('bulk_condition').value = condition;
        document.getElementById('bulk_last_checked').value = last_checked;
        document.getElementById('bulk_remarks').value = remarks;
        new bootstrap.Modal(document.getElementById('bulkModal')).show();
    };

    window.openAddBulkMovementModal = function() {
        document.getElementById('bulk-movement-form').reset();
        new bootstrap.Modal(document.getElementById('addBulkMovementModal')).show();
    };

    window.openViewBulkMovementsModal = function(bulk_id) {
        fetch('<?= URL ?>nonconsumables/getBulkMovements/' + bulk_id)
            .then(res => res.text())
            .then(html => {
                document.getElementById('bulkMovementsContent').innerHTML = html;
                new bootstrap.Modal(document.getElementById('bulkMovementsModal')).show();
            });
    };
});
</script>