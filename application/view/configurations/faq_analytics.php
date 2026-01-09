<!-- Styles -->
<link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" />
<link href="<?php echo URL; ?>css/tables.css" rel="stylesheet" />

<main>
<div class="container-fluid px-4">
    <h3 class="mt-4">FAQ Analytics Dashboard</h3>

    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="<?= URL; ?>home">Home</a></li>
        <li class="breadcrumb-item">Configurations</li>
        <li class="breadcrumb-item">FAQs</li>
    </ol>

    <div class="card mb-4">
        <div class="card-body">
            Here you can see analytics for FAQs: top searched questions, most viewed FAQs, and most active users.
        </div>
    </div>

    <!-- Top Searched Keywords -->
    <div class="card mb-4">
        <div class="card-header">
            <span><i class="fas fa-search me-1"></i> Top Searched Keywords</span>
        </div>
        <div class="card-body table-responsive">
            <table id="topSearchesTable" class="table table-bordered">
                <thead>
                    <tr>
                        <th>Keyword</th>
                        <th>Search Count</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!empty($topSearches)): ?>
                    <?php foreach ($topSearches as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row->searched_text) ?></td>
                            <td><?= $row->total ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="2" class="text-center">No search data found.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Most Viewed FAQs -->
    <div class="card mb-4">
        <div class="card-header">
            <span><i class="fas fa-eye me-1"></i> Most Viewed FAQs</span>
        </div>
        <div class="card-body table-responsive">
            <table id="mostViewedTable" class="table table-bordered">
                <thead>
                    <tr>
                        <th>FAQ Question</th>
                        <th>View Count</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!empty($topViewedFaqs)): ?>
                    <?php foreach ($topViewedFaqs as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row->question) ?></td>
                            <td><?= $row->total ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="2" class="text-center">No view data found.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Top Users -->
    <div class="card mb-4">
        <div class="card-header">
            <span><i class="fas fa-user me-1"></i> Most Active Users</span>
        </div>
        <div class="card-body table-responsive">
            <table id="topUsersTable" class="table table-bordered">
                <thead>
                    <tr>
                        <th>User Name</th>
                        <th>Email</th>
                        <th>Activity Count</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!empty($topUsers)): ?>
                    <?php foreach ($topUsers as $user): ?>
                        <tr>
                            <td><?= htmlspecialchars($user->user_name) ?></td>
                            <td><?= htmlspecialchars($user->user_email) ?></td>
                            <td><?= $user->total ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="3" class="text-center">No user activity found.</td></tr>
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
// Initialize all tables with DataTables
new simpleDatatables.DataTable("#topSearchesTable");
new simpleDatatables.DataTable("#mostViewedTable");
new simpleDatatables.DataTable("#topUsersTable");
</script>
