<?php
session_start();

require_once __DIR__ . '/includes/functions.php';

require_admin();

$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $classId = post('class_id');
    $staffId = post('staff_id');
    $staffRole = post('staff_role');

    $stmt = $pdo->prepare(
        'REPLACE INTO class_staff (
            class_id,
            staff_id,
            staff_role
        )
        VALUES (?, ?, ?)'
    );

    $stmt->execute([
        $classId,
        $staffId,
        $staffRole
    ]);

    flash('Staff assigned to class.');
    redirect('class_staff.php');
}

$classes = $pdo
    ->query('SELECT class_id, title, class_date FROM classes ORDER BY class_date DESC')
    ->fetchAll();

$staff = $pdo
    ->query('SELECT staff_id, staff_name, email FROM staff ORDER BY staff_name')
    ->fetchAll();

$rows = $pdo
    ->query(
        "SELECT 
            cs.class_id,
            cs.staff_id,
            cs.staff_role,
            c.title,
            c.class_date,
            s.staff_name,
            s.email
         FROM class_staff cs
         JOIN classes c ON c.class_id = cs.class_id
         JOIN staff s ON s.staff_id = cs.staff_id
         ORDER BY c.class_date DESC, s.staff_name"
    )
    ->fetchAll();

page_start('PRISM | Class Staff');
?>

<section class="card form">
    <h1>Assign Staff to Class</h1>

    <form method="post">
        <div class="field">
            <label>Class</label>
            <select name="class_id" required>
                <?php foreach ($classes as $class): ?>
                    <option value="<?= h($class['class_id']) ?>">
                        <?= h($class['title']) ?> (<?= h($class['class_date']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="field">
            <label>Staff Member</label>
            <select name="staff_id" required>
                <?php foreach ($staff as $person): ?>
                    <option value="<?= h($person['staff_id']) ?>">
                        <?= h($person['staff_name']) ?> - <?= h($person['email']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="field">
            <label>Staff Role</label>
            <select name="staff_role" required>
                <option value="No data">No data</option>
                <option value="maintaining attendance">Maintaining Attendance</option>
                <option value="set up">Set Up</option>
                <option value="clean up">Clean Up</option>
                <option value="equipment">Equipment</option>
                <option value="host">Host</option>
            </select>
        </div>

        <button class="button" type="submit">Assign Staff</button>
    </form>
</section>

<section class="card">
    <h2>Class Staff Assignments</h2>

    <div class="table-wrap">
        <table class="table">
            <tr>
                <th>Class</th>
                <th>Date</th>
                <th>Staff</th>
                <th>Email</th>
                <th>Role</th>
            </tr>

            <?php foreach ($rows as $row): ?>
                <tr>
                    <td><?= h($row['title']) ?></td>
                    <td><?= h($row['class_date']) ?></td>
                    <td><?= h($row['staff_name']) ?></td>
                    <td><?= h($row['email']) ?></td>
                    <td><?= h($row['staff_role']) ?></td>
                </tr>
            <?php endforeach; ?>

            <?php if (!$rows): ?>
                <tr>
                    <td colspan="5">No staff assignments yet.</td>
                </tr>
            <?php endif; ?>
        </table>
    </div>
</section>

<?php page_end(); ?>