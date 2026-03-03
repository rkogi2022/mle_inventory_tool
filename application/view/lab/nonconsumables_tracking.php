<!-- nonconsumables_tracking.php -->

<div class="mb-3">
    <h5>Condition Logs</h5>
    <div class="table-responsive">
        <table class="table table-bordered table-striped table-hover">
            <thead>
                <tr>
                    <th>Date Checked</th>
                    <th>Condition Status</th>
                    <th>Remarks</th>
                    <th>Checked By</th>
                </tr>
            </thead>
            <tbody>
                <?php if(!empty($conditionLogs)): ?>
                    <?php foreach($conditionLogs as $log): ?>
                        <tr>
                            <td><?= htmlspecialchars($log['date_checked']) ?></td>
                            <td><?= htmlspecialchars($log['condition_status']) ?></td>
                            <td><?= htmlspecialchars($log['remarks']) ?></td>
                            <td><?= htmlspecialchars($log['checked_by']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="4" class="text-center">No condition logs found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="mb-3">
    <h5>Movement Logs</h5>
    <div class="table-responsive">
        <table class="table table-bordered table-striped table-hover">
            <thead>
                <tr>
                    <th>Movement Date</th>
                    <th>Movement Type</th>
                    <th>Destination</th>
                    <th>Remarks</th>
                    <th>Recorded By</th>
                </tr>
            </thead>
            <tbody>
                <?php if(!empty($movements)): ?>
                    <?php foreach($movements as $move): ?>
                        <tr>
                            <td><?= htmlspecialchars($move['movement_date']) ?></td>
                            <td><?= htmlspecialchars($move['movement_type']) ?></td>
                            <td><?= htmlspecialchars($move['destination']) ?></td>
                            <td><?= htmlspecialchars($move['remarks']) ?></td>
                            <td><?= htmlspecialchars($move['recorded_by']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="text-center">No movement logs found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>


