<?php
session_start();

require_once __DIR__ . '/includes/functions.php';

require_admin();

$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $classId = (int) post('class_id');

    $teacherIds = $_POST['teacher_id'] ?? [];
    $statuses = $_POST['attendance_status'] ?? [];
    $notes = $_POST['notes'] ?? [];

    $stmt = $pdo->prepare(
        'REPLACE INTO attendance (class_id, teacher_id, attendance_status, notes)
         VALUES (?, ?, ?, ?)'
    );

    foreach ($teacherIds as $index => $teacherId) {
        $note = trim($notes[$index] ?? '');

        if ($note === '') {
            $note = 'None';
        }

        $stmt->execute([
            $classId,
            (int) $teacherId,
            $statuses[$index] ?? 'present',
            $note
        ]);
    }

    flash('Attendance saved.');
    redirect('attendance.php?class_id=' . $classId);
}

$classes = $pdo
    ->query('SELECT * FROM classes ORDER BY class_date DESC')
    ->fetchAll();

$classId = (int) getv('class_id', $classes[0]['class_id'] ?? 0);

$stmt = $pdo->prepare(
    "SELECT 
        t.teacher_id,
        t.first_name,
        t.last_name,
        r.status,
        a.attendance_status,
        a.notes
     FROM registrations r
     JOIN teachers t ON t.user_id = r.user_id
     LEFT JOIN attendance a 
        ON a.teacher_id = t.teacher_id 
        AND a.class_id = r.class_id
     WHERE r.class_id = ?
        AND r.status IN ('registered', 'waitlisted')
     ORDER BY t.last_name"
);

$stmt->execute([$classId]);
$people = $stmt->fetchAll();

page_start('PRISM | Attendance');
?>

<section class="card">
    <h1>Record Attendance</h1>

    <form method="get" class="searchbar">
        <select name="class_id" onchange="this.form.submit()">
            <?php foreach ($classes as $class): ?>
                <option 
                    value="<?= h($class['class_id']) ?>" 
                    <?= $classId == $class['class_id'] ? 'selected' : '' ?>
                >
                    <?= h($class['title']) ?> (<?= h($class['class_date']) ?>)
                </option>
            <?php endforeach; ?>
        </select>

        <button class="button">Load</button>
    </form>

    <form method="post">
        <input type="hidden" name="class_id" value="<?= h($classId) ?>">

        <div class="table-wrap">
            <table class="table">
                <tr>
                    <th>Participant</th>
                    <th>Registration</th>
                    <th>Attendance</th>
                    <th>Notes</th>
                </tr>

                <?php foreach ($people as $person): ?>
                    <tr>
                        <td>
                            <?= h($person['first_name'] . ' ' . $person['last_name']) ?>
                            <input 
                                type="hidden" 
                                name="teacher_id[]" 
                                value="<?= h($person['teacher_id']) ?>"
                            >
                        </td>

                        <td><?= h($person['status']) ?></td>

                        <td>
                            <select name="attendance_status[]">
                                <option 
                                    value="present" 
                                    <?= ($person['attendance_status'] ?? '') === 'present' ? 'selected' : '' ?>
                                >
                                    Present
                                </option>

                                <option 
                                    value="absent" 
                                    <?= ($person['attendance_status'] ?? '') === 'absent' ? 'selected' : '' ?>
                                >
                                    Absent
                                </option>

                                <option 
                                    value="excused" 
                                    <?= ($person['attendance_status'] ?? '') === 'excused' ? 'selected' : '' ?>
                                >
                                    Excused
                                </option>
                            </select>
                        </td>

                        <td>
                            <input 
                                name="notes[]" 
                                value="<?= h($person['notes'] ?? 'None') ?>"
                            >
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>

        <br>

        <button class="button">Save Attendance</button>
    </form>
</section>

<?php page_end(); ?>