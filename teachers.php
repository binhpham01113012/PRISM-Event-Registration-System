<?php
session_start();

require_once __DIR__ . '/includes/functions.php';

require_admin();

$rows = db()
    ->query(
        "SELECT 
            u.username,
            u.role,
            t.*
         FROM users u
         LEFT JOIN teachers t ON t.user_id = u.user_id
         ORDER BY t.last_name, u.username"
    )
    ->fetchAll();

page_start('PRISM | Teacher Records');
?>

<section class="card">
    <h1>Teacher Records</h1>

    <div class="table-wrap">
        <table class="table">
            <tr>
                <th>Name</th>
                <th>Role</th>
                <th>Email</th>
                <th>District</th>
                <th>School</th>
                <th>Eligibility</th>
            </tr>

            <?php foreach ($rows as $row): ?>
                <tr>
                    <td>
                        <?= h(trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? '')) ?: $row['username']) ?>
                    </td>

                    <td><?= h($row['role']) ?></td>
                    <td><?= h($row['work_email'] ?: 'N/A') ?></td>
                    <td><?= h($row['district'] ?: 'N/A') ?></td>
                    <td><?= h($row['school'] ?: 'N/A') ?></td>

                    <td>
                        <span class="badge <?= h($row['eligibility_status'] ?: 'needs-survey') ?>">
                            <?= h($row['eligibility_status'] ?: 'needs-survey') ?>
                        </span>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
</section>

<?php page_end(); ?>