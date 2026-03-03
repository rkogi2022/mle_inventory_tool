<!-- Styles -->
<link href="<?= URL; ?>css/tables.css" rel="stylesheet" />

<main>
<div class="container-fluid px-4">
    <h3 class="mt-4">Quarterly Consumables Summary</h3>

    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="<?= URL; ?>home">Home</a></li>
        <li class="breadcrumb-item"><a href="<?= URL; ?>consumables">Consumables</a></li>
        <li class="breadcrumb-item">Quarterly Summary</li>
    </ol>

    <form method="GET" class="mb-3">
        <label>Select Year:</label>
        <select name="year" onchange="this.form.submit()" class="form-select w-auto d-inline-block">
            <?php for ($y = date('Y'); $y >= 2025; $y--): ?>
                <option value="<?= $y ?>" <?= ($y == ($_GET['year'] ?? date('Y'))) ? 'selected' : '' ?>>
                    <?= $y ?>
                </option>
            <?php endfor; ?>
        </select>
    </form>


    <div class="row">

        <?php for ($q = 1; $q <= 4; $q++): ?>
            <div class="col-xl-6 col-md-6 mb-4">
                <div class="card shadow h-100">
                    <div class="card-header bg-primary text-white">
                        <strong>Quarter <?= $q ?></strong>
                    </div>
                    <div class="card-body table-responsive">

                        <table class="table table-striped table-sm">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Received</th>
                                    <th>Issued</th>
                                    <th>Net</th>
                                </tr>
                            </thead>
                            <tbody>

                                <?php if (!empty($summary[$q])): ?>
                                    <?php foreach ($summary[$q] as $item): 
                                        $net = $item['received'] - $item['issued'];
                                    ?>
                                        <tr>
                                            <td><?= htmlspecialchars($item['item_name']); ?></td>
                                            <td><?= htmlspecialchars($item['received']); ?></td>
                                            <td><?= htmlspecialchars($item['issued']); ?></td>
                                            <td>
                                                <strong class="<?= $net < 0 ? 'text-danger' : 'text-success'; ?>">
                                                    <?= $net; ?>
                                                </strong>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center">
                                            No transactions in this quarter.
                                        </td>
                                    </tr>
                                <?php endif; ?>

                            </tbody>
                        </table>

                    </div>
                </div>
            </div>
        <?php endfor; ?>

    </div>

    <a href="<?= URL; ?>consumables" class="btn btn-secondary mt-3">
        Back to Consumables
    </a>

</div>
</main>
