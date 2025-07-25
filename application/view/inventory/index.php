<link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" crossorigin="anonymous"></script>
<link href="<?php echo URL; ?>css/tables.css" rel="stylesheet">

<style>
/* Hide default simple-datatables search box */
.dataTable-input {
    display: none !important;
}
</style>

<main>
    <div class="container-fluid px-4">
        <h3 class="mt-4">Inventory List</h3>

        <ol class="breadcrumb mb-4">
            <li class="breadcrumb-item"><a href="<?php echo URL; ?>home">Home</a></li>
            <li class="breadcrumb-item">Configurations</li>
            <li class="breadcrumb-item">Inventory</li>
        </ol>
        <div class="card mb-4">
            <div class="card-body">
                This is a collection of all the assets in the organisation and their details.
            </div>
        </div>
        
        <!-- success/error message (using 'update' and 'message') -->
        <?php if (isset($_GET['update']) && isset($_GET['message'])): ?>
            <?php
                $bgColor = $_GET['update'] === 'success' ? 'green' : 'red';
                $textColor = 'white'; 
            ?>
            <div id="messageDiv" style="background-color: <?= $bgColor; ?>; color: <?= $textColor; ?>; padding: 10px; text-align: center;">
                <?= htmlspecialchars(urldecode($_GET['message'])); ?>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    setTimeout(function() {
                        const messageDiv = document.getElementById('messageDiv');
                        if (messageDiv) {
                            messageDiv.style.display = 'none';
                        }
                    }, 10000);
                });
            </script>
        <?php elseif (
            isset($_GET['success']) || isset($_GET['error']) || isset($_GET['warning'])
        ): 
            $messageType = null;
            $messageContent = null;

            if (isset($_GET['success'])) {
                $messageType = 'green';
                $messageContent = urldecode($_GET['success']);
            } elseif (isset($_GET['error'])) {
                $messageType = 'red';
                $messageContent = urldecode($_GET['error']);
            } elseif (isset($_GET['warning'])) {
                $messageType = 'orange';
                $messageContent = urldecode($_GET['warning']);
            }
        ?>
        <div id="messageDiv" style="background-color: <?= $messageType; ?>; color: white; padding: 10px; text-align: center;">
            <?= htmlspecialchars($messageContent); ?>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(function() {
                    const messageDiv = document.getElementById('messageDiv');
                    if (messageDiv) {
                        messageDiv.style.display = 'none';
                    }
                }, 10000);
            });
        </script>
        <?php endif; ?>

        <!-- Card for inventory list -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-table me-1"></i></span>
                <div class="d-flex gap-2">
                    <!-- Download Template Icon Button -->
                    <a href="<?= URL; ?>inventory/downloadInventoryTemplate" 
                       class="btn btn-sm text-white" 
                       style="background-color: #05545a;">
                       <i class="fas fa-download"></i>
                    </a>

                    <!-- Upload CSV Button -->
                    <form action="<?= URL; ?>inventory/bulkUpdate" method="POST" enctype="multipart/form-data" class="mb-0 d-flex align-items-center gap-2">
                        <input type="file" name="bulk_file" accept=".csv" required class="form-control form-control-sm" style="max-width: 200px;" />
                        <button type="submit" class="add-btn" style="background-color: #05545a; border-color: #05545a;">
                            Upload CSV
                        </button>
                    </form>

                    <!-- Add New Item Button -->
                    <form method="GET" action="<?= URL ?>inventory/add" class="mb-0">
                        <button type="submit" class="add-btn">
                            Add New Item
                        </button>
                    </form>
                </div>
            </div>

            <div class="card-body table-responsive">
                <!-- Manual Search Input -->
                <div class="mb-3 d-flex justify-content-end">
                    <div class="d-flex align-items-center gap-2">
                        <input
                            type="text"
                            id="manualSearchInput"
                            class="form-control"
                            placeholder="Search inventory..."
                            style="max-width: 300px;"
                        />
                        <button id="manualSearchBtn" class="add-btn">Search</button>
                        <button id="clearSearchBtn" class="btn btn-secondary">Clear</button>
                    </div>
                </div>

                <!-- Inventory Table -->
                <table id="datatablesSimple">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Description</th>
                            <th>Serial Number</th>
                            <th>Tag Number</th>
                            <th>Acquisition Date</th>
                            <th>Acquisition Cost ($)</th>
                            <th>Warranty Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($items)): ?>
                            <?php foreach ($items as $item): ?>
                                <tr>
                                    <td><?= htmlspecialchars($item['category'] ?? 'N/A'); ?></td>
                                    <td><?= htmlspecialchars($item['description'] ?? ''); ?></td>
                                    <td><?= htmlspecialchars($item['serial_number'] ?? ''); ?></td>
                                    <td><?= htmlspecialchars($item['tag_number'] ?? ''); ?></td>
                                    <td><?= htmlspecialchars($item['acquisition_date'] ?? ''); ?></td>
                                    <td><?= htmlspecialchars($item['acquisition_cost'] ?? ''); ?></td>
                                    <td><?= htmlspecialchars($item['warranty_date'] ?? ''); ?></td>

                                    <td class="d-flex justify-content-between">
                                        <!-- Edit Button -->
                                        <a href="<?= URL ?>inventory/edit/<?= htmlspecialchars($item['id'] ?? ''); ?>" 
                                           class="btn btn-sm btn-outline-primary me-1">Edit</a>

                                        <!-- Delete Button -->
                                        <form action="<?= URL ?>inventory/delete" method="POST" 
                                              onsubmit="return confirm('Are you sure you want to delete this item?');" 
                                              style="display:inline;">
                                            <input type="hidden" name="id" value="<?= htmlspecialchars($item['id'] ?? ''); ?>">
                                            <button type="submit" name="delete" class="btn btn-sm btn-outline-danger me-1">Delete</button>
                                        </form>

                                        <!-- Assign Button (Modal Trigger) -->
                                        <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#assignModal" data-item-id="<?= htmlspecialchars($item['id'] ?? ''); ?>">Assign</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center">No items found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal for Item Assignment -->
    <div class="modal fade" id="assignModal" tabindex="-1" aria-labelledby="assignModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="assignModalLabel">Assign Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="<?= URL ?>inventory/assignSingle">
                        <input type="hidden" name="item_id" id="item_id">

                        <!-- Dropdown for User -->
                        <div class="mb-3">
                            <label for="user_id" class="form-label">User</label>
                            <select class="form-control" name="user_id" required>
                                <option value="">Select User</option>
                                <?php foreach ($users as $user): ?>
                                    <?php
                                        $emailPrefix = strtok($user['email'], '@');
                                        $parts = preg_split('/[._]/', $emailPrefix);
                                        $formattedName = implode(' ', array_map(function($part) {
                                            return ucfirst(strtolower($part));
                                        }, $parts));
                                    ?>
                                    <option value="<?= htmlspecialchars($user['id']); ?>">
                                        <?= htmlspecialchars($formattedName); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Dropdown for Manager Email -->
                        <div class="mb-3">
                            <label for="manager_email" class="form-label">Manager</label>
                            <select class="form-control" name="manager_email" required>
                                <option value="">Select Manager</option>
                                <?php foreach ($managers as $manager): ?>
                                    <?php
                                        $emailPrefix = strtok($manager['email'], '@');
                                        $parts = preg_split('/[._]/', $emailPrefix);
                                        $formattedName = implode(' ', array_map(function($part) {
                                            return ucfirst(strtolower($part));
                                        }, $parts));
                                    ?>
                                    <option value="<?= htmlspecialchars($manager['email']); ?>">
                                        <?= htmlspecialchars($formattedName); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Date Assigned -->
                        <div class="mb-3">
                            <label for="date_assigned" class="form-label">Date Assigned</label>
                            <input type="date" class="form-control" name="date_assigned" value="<?= date('Y-m-d'); ?>" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Assign Item</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
window.addEventListener('DOMContentLoaded', () => {
    const tableEl = document.getElementById('datatablesSimple');
    if (!tableEl) return;

    // Initialize DataTable
        const dataTable = new simpleDatatables.DataTable(tableEl, {
        searchable: false
    });

    // Manual search input & buttons
    const searchInput = document.getElementById('manualSearchInput');
    const searchBtn = document.getElementById('manualSearchBtn');
    const clearBtn = document.getElementById('clearSearchBtn');

    // Apply saved search from sessionStorage
    const savedSearch = sessionStorage.getItem('inventorySearch');
    if (savedSearch) {
        searchInput.value = savedSearch;
        dataTable.search(savedSearch);
    }

    // Search function: apply filter and save to sessionStorage
    function applySearch() {
        const term = searchInput.value.trim();
        dataTable.search(term);
        sessionStorage.setItem('inventorySearch', term);
    }

    // Search button click event
    searchBtn.addEventListener('click', () => {
        applySearch();
    });

    // Clear button click event
    clearBtn.addEventListener('click', () => {
        searchInput.value = '';
        dataTable.search('');
        sessionStorage.removeItem('inventorySearch');
    });

    // Optional: allow Enter key in input to trigger search
    searchInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            applySearch();
        }
    });

    // Assign Modal: set hidden input item_id
    const assignButtons = document.querySelectorAll('[data-bs-toggle="modal"]');
    assignButtons.forEach(button => {
        button.addEventListener('click', function () {
            const itemId = this.getAttribute('data-item-id');
            document.getElementById('item_id').value = itemId;
        });
    });
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
