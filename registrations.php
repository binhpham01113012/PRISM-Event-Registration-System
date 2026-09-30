<?php
session_start();

require_once __DIR__ . '/includes/functions.php';

require_admin();

$rows = db()
    ->query(
        "SELECT 
            r.*,
            c.title,
            c.class_date,
            u.username,
            t.first_name,
            t.last_name,
            t.work_email
         FROM registrations r
         JOIN classes c ON c.class_id = r.class_id
         JOIN users u ON u.user_id = r.user_id
         LEFT JOIN teachers t ON t.user_id = u.user_id
         ORDER BY c.class_date DESC, r.status"
    )
    ->fetchAll();

page_start('PRISM | Registration Lists');
?>

<section class="card">
    <h1>Registration List Per Event</h1>

    <div class="table-wrap">
        <table class="table">
            <tr>
                <th>Class</th>
                <th>Date</th>
                <th>Participant</th>
                <th>Email</th>
                <th>Status</th>
                <th>Registered At</th>
            </tr>

            <?php foreach ($rows as $row): ?>
                <tr>
                    <td><?= h($row['title']) ?></td>
                    <td><?= h($row['class_date']) ?></td>

                    <td>
                        <?= h(trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? '')) ?: $row['username']) ?>
                    </td>

                    <td><?= h($row['work_email'] ?: 'N/A') ?></td>

                    <td>
                        <span class="badge <?= h($row['status']) ?>">
                            <?= h($row['status']) ?>
                        </span>
                    </td>

                    <td><?= h($row['registration_timestamp']) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
</section>

<?php page_end(); ?>
