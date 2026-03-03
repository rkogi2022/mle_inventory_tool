<!-- Styles -->
<link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" />
<link href="<?= URL; ?>css/tables.css" rel="stylesheet" />

<main>
<div class="container-fluid px-4">
    <h3 class="mt-4">Serialized Non-Consumable Items</h3>

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
        <button class="btn btn-success btn-sm" onclick="openAddSerialisedModal()">Add Serialized Item</button>
        <button class="btn btn-primary btn-sm" onclick="openAddConditionLogModal()">Add Condition Log</button>
        <button class="btn btn-warning btn-sm" onclick="openAddMovementModal()">Add Movement</button>
    </div>

    <div class="card mb-4">
        <div class="card-body table-responsive">
            <table id="serialisedTable" class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th>Item Name</th>
                        <th>Description</th>
                        <th>Serial</th>
                        <th>Specification</th>
                        <th>Accessories</th>
                        <th>Date Received</th>
                        <th>Qty Received</th>
                        <th>Qty In Store</th>
                        <th>Recipient</th>
                        <th>Condition</th>
                        <th>Current Status</th>
                        <th>Remarks</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if(!empty($allSerialised)): ?>
                    <?php foreach($allSerialised as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item->item_name) ?></td>
                            <td><?= htmlspecialchars($item->description) ?></td>
                            <td><?= htmlspecialchars($item->serial_number) ?></td>
                            <td><?= htmlspecialchars($item->specification) ?></td>
                            <td><?= htmlspecialchars($item->accessories) ?></td>
                            <td><?= htmlspecialchars($item->date_received) ?></td>
                            <td><?= htmlspecialchars($item->quantity_received) ?></td>
                            <td><?= htmlspecialchars($item->quantity_in_store) ?></td>
                            <td><?= htmlspecialchars($item->recipient_name) ?></td>
                            <td><?= htmlspecialchars($item->condition_status) ?></td>
                            <td><?= htmlspecialchars($item->current_status) ?></td>
                            <td><?= htmlspecialchars($item->remarks) ?></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" 
                                        onclick="openEditSerialisedModal(
                                            <?= $item->id ?>,
                                            '<?= htmlspecialchars($item->item_name, ENT_QUOTES) ?>',
                                            '<?= htmlspecialchars($item->description, ENT_QUOTES) ?>',
                                            '<?= htmlspecialchars($item->serial_number, ENT_QUOTES) ?>',
                                            '<?= htmlspecialchars($item->specification, ENT_QUOTES) ?>',
                                            '<?= htmlspecialchars($item->accessories, ENT_QUOTES) ?>',
                                            <?= $item->quantity_received ?>,
                                            <?= $item->quantity_in_store ?>,
                                            '<?= $item->date_received ?>',
                                            '<?= htmlspecialchars($item->recipient_name, ENT_QUOTES) ?>',
                                            '<?= $item->condition_status ?>',
                                            '<?= $item->current_status ?>',
                                            '<?= htmlspecialchars($item->remarks, ENT_QUOTES) ?>'
                                        )">Edit</button>

                                <button class="btn btn-sm btn-outline-info"
                                        onclick="openViewLogsModal(<?= $item->id ?>)">View Logs</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="13">No serialized non-consumable items found.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</main>

<!-- Add/Edit Serialized Item Modal -->
<div id="serialisedModal" class="modal fade" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="serialised-form" method="POST" action="<?= URL ?>nonconsumables/addSerialised">
                <div class="modal-header">
                    <h5 class="modal-title" id="serialisedModalTitle">Add Serialized Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="serialised_id" name="id">

                    <div class="mb-2"><label>Item Name</label><input type="text" name="item_name" id="serialised_name" class="form-control" required></div>
                    <div class="mb-2"><label>Description</label><input type="text" name="description" id="serialised_description" class="form-control"></div>
                    <div class="mb-2"><label>Serial Number</label><input type="text" name="serial_number" id="serialised_serial" class="form-control" required></div>
                    <div class="mb-2"><label>Specification</label><input type="text" name="specification" id="serialised_specification" class="form-control"></div>
                    <div class="mb-2"><label>Accessories</label><input type="text" name="accessories" id="serialised_accessories" class="form-control"></div>
                    <div class="mb-2"><label>Quantity Received</label><input type="number" name="quantity_received" id="serialised_quantity_received" class="form-control" value="1" required></div>
                    <div class="mb-2"><label>Quantity In Store</label><input type="number" name="quantity_in_store" id="serialised_quantity_in_store" class="form-control" value="1" required></div>
                    <div class="mb-2"><label>Date Received</label><input type="date" name="date_received" id="serialised_received" class="form-control" required></div>
                    <div class="mb-2">
                        <label>Recipient</label>
                        <select name="recipient_name" id="serialised_recipient" class="form-control" required>
                            <option value="">-- Select Recipient --</option>
                            <?php foreach($recipients as $r): ?>
                                <option value="<?= htmlspecialchars($r['name']) ?>"><?= htmlspecialchars($r['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label>Condition Status</label>
                        <select name="condition_status" id="serialised_condition_status" class="form-control">
                            <option value="good">Good</option>
                            <option value="fair">Fair</option>
                            <option value="needs_service">Needs Service</option>
                            <option value="faulty">Faulty</option>
                            <option value="not_functional">Not Functional</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label>Current Status</label>
                        <select name="current_status" id="serialised_current_status" class="form-control">
                            <option value="instock">In Stock</option>
                            <option value="dispatched">Dispatched</option>
                            <option value="under_service">Under Service</option>
                            <option value="faulty">Faulty</option>
                            <option value="disposed">Disposed</option>
                        </select>
                    </div>
                    <div class="mb-2"><label>Remarks</label><textarea name="remarks" id="serialised_remarks" class="form-control"></textarea></div>
                </div>
                <div class="modal-footer"><button type="submit" class="btn btn-primary">Save Item</button></div>
            </form>
        </div>
    </div>
</div>

<!-- Condition Log Modal -->
<div id="conditionLogModal" class="modal fade" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="condition-log-form" method="POST" action="<?= URL ?>nonconsumables/addSerialisedConditionLog">
                <div class="modal-header">
                    <h5 class="modal-title">Add Condition Log</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label>Select Item</label>
                        <select name="serialized_id" class="form-control" required>
                            <option value="">-- Select Item --</option>
                            <?php foreach($allSerialised as $item): ?>
                                <option value="<?= $item->id ?>"><?= htmlspecialchars($item->item_name) ?> (Serial: <?= htmlspecialchars($item->serial_number) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label>Condition Status</label>
                        <select name="condition_status" class="form-control" required>
                            <option value="good">Good</option>
                            <option value="fair">Fair</option>
                            <option value="needs_service">Needs Service</option>
                            <option value="faulty">Faulty</option>
                            <option value="not_functional">Not Functional</option>
                        </select>
                    </div>
                    <div class="mb-2"><label>Remarks</label><textarea name="remarks" class="form-control"></textarea></div>
                    <div class="mb-2">
                        <label>Checked By</label>
                        <select name="checked_by" class="form-control" required>
                            <option value="">-- Select Staff --</option>
                            <?php foreach($recipients as $r): ?>
                                <option value="<?= htmlspecialchars($r['name']) ?>" <?= (isset($_SESSION['user_name']) && $_SESSION['user_name'] == $r['name']) ? 'selected' : '' ?>><?= htmlspecialchars($r['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-2"><label>Date Checked</label><input type="date" name="date_checked" class="form-control" value="<?= date('Y-m-d') ?>"></div>
                </div>
                <div class="modal-footer"><button type="submit" class="btn btn-primary">Save Log</button></div>
            </form>
        </div>
    </div>
</div>

<!-- Movement Modal -->
<div id="movementModal" class="modal fade" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="movement-form" method="POST" action="<?= URL ?>nonconsumables/addMovement">
                <div class="modal-header">
                    <h5 class="modal-title">Add Movement</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label>Select Item</label>
                        <select name="serialized_id" class="form-control" required>
                            <option value="">-- Select Item --</option>
                            <?php foreach($allSerialised as $item): ?>
                                <option value="<?= $item->id ?>"><?= htmlspecialchars($item->item_name) ?> (Serial: <?= htmlspecialchars($item->serial_number) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label>Movement Type</label>
                        <select name="movement_type" class="form-control" required>
                            <option value="received">Received</option>
                            <option value="dispatched">Dispatched</option>
                            <option value="returned">Returned</option>
                            <option value="sent_for_service">Sent for Service</option>
                            <option value="disposed">Disposed</option>
                        </select>
                    </div>
                    <div class="mb-2"><label>Movement Date</label><input type="date" name="movement_date" class="form-control" value="<?= date('Y-m-d') ?>" required></div>
                    <div class="mb-2"><label>Destination</label><input type="text" name="destination" class="form-control"></div>
                    <div class="mb-2"><label>Remarks</label><textarea name="remarks" class="form-control"></textarea></div>
                    <div class="mb-2">
                        <label>Recorded By</label>
                        <select name="recorded_by" class="form-control" required>
                            <option value="">-- Select Staff --</option>
                            <?php foreach($recipients as $r): ?>
                                <option value="<?= htmlspecialchars($r['name']) ?>" <?= (isset($_SESSION['user_name']) && $_SESSION['user_name'] == $r['name']) ? 'selected' : '' ?>><?= htmlspecialchars($r['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer"><button type="submit" class="btn btn-primary">Save Movement</button></div>
            </form>
        </div>
    </div>
</div>

<!-- View Logs Modal -->
<div id="viewLogsModal" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Condition & Movement Logs</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="logsContent">
                <!-- AJAX-loaded content will go here -->
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" crossorigin="anonymous"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const table = document.getElementById('serialisedTable');
    if(table) new simpleDatatables.DataTable(table);

    // Open Modals
    window.openAddSerialisedModal = function() { new bootstrap.Modal(document.getElementById('serialisedModal')).show(); }
    window.openAddConditionLogModal = function() { new bootstrap.Modal(document.getElementById('conditionLogModal')).show(); }
    window.openAddMovementModal = function() { new bootstrap.Modal(document.getElementById('movementModal')).show(); }

    window.openEditSerialisedModal = function(id, name, description, serial, specification, accessories,
                                             quantity_received, quantity_in_store, date_received,
                                             recipient_name, condition_status, current_status, remarks) {
        const form = document.getElementById('serialised-form');
        form.action = '<?= URL ?>nonconsumables/updateSerialised/' + id;
        document.getElementById('serialisedModalTitle').textContent = 'Edit Serialized Item';

        document.getElementById('serialised_id').value = id;
        document.getElementById('serialised_name').value = name;
        document.getElementById('serialised_description').value = description;
        document.getElementById('serialised_serial').value = serial;
        document.getElementById('serialised_specification').value = specification;
        document.getElementById('serialised_accessories').value = accessories;
        document.getElementById('serialised_quantity_received').value = quantity_received;
        document.getElementById('serialised_quantity_in_store').value = quantity_in_store;
        document.getElementById('serialised_received').value = date_received;
        document.getElementById('serialised_recipient').value = recipient_name;
        document.getElementById('serialised_condition_status').value = condition_status;
        document.getElementById('serialised_current_status').value = current_status;
        document.getElementById('serialised_remarks').value = remarks;

        new bootstrap.Modal(document.getElementById('serialisedModal')).show();
    };

    window.openViewLogsModal = function(serialized_id) {
        fetch('<?= URL ?>nonconsumables/getLogs/' + serialized_id)
            .then(res => res.text())
            .then(html => {
                document.getElementById('logsContent').innerHTML = html;
                new bootstrap.Modal(document.getElementById('viewLogsModal')).show();
            });
    };
});
</script>
