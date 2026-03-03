<!-- Styles -->
<link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" />
<link href="<?= URL; ?>css/tables.css" rel="stylesheet" />

<main>
<div class="container-fluid px-4">
    <h3 class="mt-4">Gear Inventory</h3>

    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="<?= URL; ?>home">Home</a></li>
        <li class="breadcrumb-item">Lab</li>
        <li class="breadcrumb-item">Gear Inventory</li>
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
            <span><i class="fas fa-cogs me-1"></i> Gear List</span>
            <div>
                <button class="btn add-btn btn-sm" onclick="openAddItemModal()">Add Gear Item</button>
                <button class="btn btn-sm btn-outline-warning" onclick="openAddIssueModal()">Issue Gear</button>
            </div>
        </div>

        <div class="card-body table-responsive">
            <table id="gearTable" class="table">
                <thead>
                    <tr>
                        <th>Item Name</th>
                        <th>Number Procured</th>
                        <th>Number Issued</th>
                        <th>In Store</th>
                        <th>Comments</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!empty($items)): ?>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['item_name']); ?></td>
                            <td><?= htmlspecialchars($item['number_procured']); ?></td>
                            <td><?= htmlspecialchars($item['number_issued']); ?></td>
                            <td><?= htmlspecialchars($item['in_store']); ?></td>
                            <td><?= htmlspecialchars($item['comments']); ?></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary"
                                    onclick="openEditItemModal(
                                        <?= $item['id'] ?>,
                                        '<?= htmlspecialchars($item['item_name'], ENT_QUOTES) ?>',
                                        <?= $item['number_procured'] ?>,
                                        <?= $item['number_issued'] ?>,
                                        <?= $item['in_store'] ?>,
                                        '<?= htmlspecialchars($item['comments'], ENT_QUOTES) ?>'
                                    )">
                                    Edit
                                </button>

                                <a href="<?= URL ?>gears/deleteItem?delete=<?= $item['id'] ?>"
                                   class="btn btn-sm btn-outline-danger"
                                   onclick="return confirm('Are you sure you want to delete this gear item?')">
                                   Delete
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6">No gear items found.</td></tr>
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
            <form id="item-form" method="POST" action="<?= URL ?>gears/addInventory">
                <div class="modal-header">
                    <h5 class="modal-title" id="itemModalTitle">Add Gear Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <input type="hidden" name="id" id="item_id">

                    <div class="mb-3">
                        <label class="form-label">Item Name:</label>
                        <input type="text" name="item_name" id="item_name" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Number Procured:</label>
                        <input type="number" name="number_procured" id="number_procured" class="form-control" min="1" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Comments:</label>
                        <textarea name="comments" id="comments" class="form-control"></textarea>
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

<!-- Issue Modal -->
<div id="issueModal" class="modal fade" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="issue-form" method="POST" action="<?= URL ?>gears/issueGear">
                <div class="modal-header">
                    <h5 class="modal-title">Issue Gear to Staff</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Staff:</label>
                        <select name="staff_id" class="form-control" required>
                            <option value="">-- Select Staff --</option>
                            <?php foreach ($staffs as $staff): ?>
                                <option value="<?= $staff['id'] ?>"><?= htmlspecialchars($staff['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Gear Item:</label>
                        <select name="item_id" class="form-control" required>
                            <option value="">-- Select Gear --</option>
                            <?php foreach ($items as $item): ?>
                                <option value="<?= $item['id'] ?>"><?= htmlspecialchars($item['item_name']) ?> (In store: <?= $item['in_store'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Quantity:</label>
                        <input type="number" name="quantity" class="form-control" min="1" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Condition:</label>
                        <select name="item_condition" class="form-control" required>
                            <option value="Good">Good</option>
                            <option value="Damaged">Damaged</option>
                            <option value="Lost">Lost</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Date Issued:</label>
                        <input type="date" name="date_issued" class="form-control" required value="<?= date('Y-m-d') ?>">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-warning">Issue Gear</button>
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
    // Initialize DataTable
    const table = document.getElementById('gearTable');
    if (table) {
        new simpleDatatables.DataTable(table);
    }

    // Add Gear Item Modal
    window.openAddItemModal = function () {
        const form = document.getElementById('item-form');
        form.action = '<?= URL ?>gears/addInventory';
        document.getElementById('itemModalTitle').textContent = 'Add Gear Item';
        form.reset();
        new bootstrap.Modal(document.getElementById('itemModal')).show();
    }

    // Edit Gear Item Modal
    window.openEditItemModal = function (id, name, procured, issued, in_store, comments) {
        const form = document.getElementById('item-form');
        form.action = '<?= URL ?>gears/editInventory';
        document.getElementById('itemModalTitle').textContent = 'Edit Gear Item';
        document.getElementById('item_id').value = id;
        document.getElementById('item_name').value = name;
        document.getElementById('number_procured').value = procured;
        form.querySelector('#comments').value = comments;
        new bootstrap.Modal(document.getElementById('itemModal')).show();
    }

    // Issue Gear Modal
    window.openAddIssueModal = function () {
        document.getElementById('issue-form').reset();
        new bootstrap.Modal(document.getElementById('issueModal')).show();
    }
});
</script>
