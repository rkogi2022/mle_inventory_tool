<!-- CSS Links -->
<link href="<?= URL ?>css/style.css" rel="stylesheet">
<link href="<?= URL ?>css/tables.css" rel="stylesheet">

<!-- Inline Styles -->
<style>
    .card-centered {
        max-width: 650px;
        margin: 30px auto;
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.05);
        border-radius: 10px;
    }

    .card-header {
        font-weight: 600;
        font-size: 1.2rem;
    }

    .form-label {
        font-weight: 500;
    }

    button[type="submit"] {
        width: 100%;
    }

    @media (max-width: 768px) {
        .card-centered {
            margin: 20px 10px;
        }
    }
</style>

<main>
<div class="container-fluid px-4">
    <h3 class="mt-4">Edit Item Assignment</h3>

    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="<?= URL ?>home">Home</a></li>
        <li class="breadcrumb-item"><a href="<?= URL ?>inventoryassignment">Assignments</a></li>
        <li class="breadcrumb-item">Edit Assignment</li>
    </ol>

    <!-- Alert Messages -->
    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_GET['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_GET['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Assignment Edit Form -->
    <div class="card mb-4 card-centered">
        <div class="card-header">
            <i class="fas fa-edit me-1"></i> Edit Assignment
        </div>
        <div class="card-body">
            <form action="<?= URL ?>inventoryassignment/edit/<?= htmlspecialchars($assignment['id']); ?>" method="POST">
                <input type="hidden" name="assignment_id" value="<?= htmlspecialchars($assignment['id']); ?>">

                <!-- Assigned Item(s) -->
                <div id="item-container">
                    <?php foreach ($assignment['items'] as $item): ?>
                        <div class="row g-3 align-items-end item-group mb-3">
                            <div class="col-md-12">
                                <label class="form-label">Assigned Item</label>
                                <select name="inventory_id[]" class="form-select" required>
                                    <option value="">Choose an item</option>
                                    <?php foreach ($unassignedItems as $availableItem): ?>
                                        <option value="<?= htmlspecialchars($availableItem['id']); ?>"
                                            <?= ($availableItem['id'] == $item['id']) ? 'selected' : ''; ?>>
                                            <?= htmlspecialchars($availableItem['description'] ?? 'No Description'); ?>
                                            (<?= htmlspecialchars($availableItem['serial_number'] ?? 'N/A'); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- User Selection -->
                <div class="mb-3">
                    <label class="form-label">Select User</label>
                    <select name="user_id" class="form-select" required>
                        <option value="">Select User</option>
                        <?php foreach ($users as $user): ?>
                            <?php
                                $emailPrefix = strtok($user['email'], '@');
                                $parts = preg_split('/[._]/', $emailPrefix);
                                $formattedName = implode(' ', array_map('ucfirst', $parts));
                                $selected = ($user['id'] == $assignment['user_id']) ? 'selected' : '';
                            ?>
                            <option value="<?= htmlspecialchars($user['id']); ?>" <?= $selected ?>>
                                <?= htmlspecialchars($formattedName); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Date Assigned -->
                <div class="mb-3">
                    <label class="form-label">Date Assigned</label>
                    <input type="date" name="date_assigned" class="form-control" value="<?= htmlspecialchars($assignment['date_assigned']); ?>" required>
                </div>

                <!-- Manager Selection -->
                <div class="mb-3">
                    <label class="form-label">Managed By</label>
                    <select name="managed_by" class="form-select" required>
                        <option value="">Select Manager</option>
                        <?php foreach ($users as $user): ?>
                            <?php
                                $emailPrefix = strtok($user['email'], '@');
                                $parts = preg_split('/[._]/', $emailPrefix);
                                $formattedName = implode(' ', array_map('ucfirst', $parts));
                                $selected = ($user['email'] == $assignment['managed_by']) ? 'selected' : '';
                            ?>
                            <option value="<?= htmlspecialchars($user['email']); ?>" <?= $selected ?>>
                                <?= htmlspecialchars($formattedName); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Submit -->
                <button type="submit" name="update_assignment" class="btn btn-success">Update Assignment</button>
            </form>
        </div>
    </div>
</div>
</main>
