<?php
session_start();

require_once __DIR__ . '/includes/functions.php';

require_admin();

$pdo = db();


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $classId = post('class_id') ?: null;
    $concernNotes = trim(post('concern_notes'));

    if ($concernNotes === '') {
        $concernNotes = 'None';
    }

    $teacherId = post('teacher_id');

    $stmt = $pdo->prepare(
        'INSERT INTO concern_flags (
            teacher_id,
            class_id,
            concern_type,
            concern_notes
        )
        VALUES (?, ?, ?, ?)'
    );

    $stmt->execute([
        $teacherId,
        $classId,
        post('concern_type'),
        $concernNotes
    ]);

    flash('Concern flag saved.');
    redirect('flags.php');
}
$teachers = $pdo
    ->query(
        'SELECT teacher_id, first_name, last_name, work_email 
         FROM teachers 
         ORDER BY last_name, first_name'
    )
    ->fetchAll();
    
$classes = $pdo
    ->query('SELECT class_id, title FROM classes ORDER BY title')
    ->fetchAll();

$rows = $pdo
    ->query(
        "SELECT 
            f.*,
            c.title,
            t.first_name,
            t.last_name,
            t.work_email
         FROM concern_flags f
         LEFT JOIN classes c ON c.class_id = f.class_id
         LEFT JOIN teachers t ON t.teacher_id = f.teacher_id
         ORDER BY f.created_at DESC"
    )
    ->fetchAll();

page_start('PRISM | Flag Concerns');
?>

<section class="card form">
    <h1>Flag a Teacher Concern</h1>

    <form method="post">
        <div class="form-row">
            <div class="field">
                <label>Teacher Name</label>
                <input name="teacher_name" required>
            </div>

            <div class="field">
                <label>Teacher Email</label>
                <input type="email" name="teacher_email" required>
            </div>
        </div>

        <div class="field">
            <label>Related Class</label>
            <select name="class_id">
                <option value="">None</option>

                <?php foreach ($classes as $class): ?>
                    <option value="<?= h($class['class_id']) ?>">
                        <?= h($class['title']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="field">
            <label>Concern Type</label>
            <select name="concern_type" required>
                <option value="did-not-attend">Did not attend</option>
                <option value="incomplete-profile">Incomplete profile</option>
                <option value="missing-demographics">Missing demographics</option>
                <option value="other">Other</option>
            </select>
        </div>

        <div class="field">
            <label>Concern Notes</label>
            <textarea name="concern_notes" required></textarea>
        </div>

        <button class="button">Save Flag</button>
    </form>
</section>

<section class="card">
    <h2>Concern Flags</h2>

    <table class="table">
        <tr>
            <th>Teacher</th>
            <th>Class</th>
            <th>Type</th>
            <th>Notes</th>
            <th>Created At</th>
        </tr>

        <?php foreach ($rows as $row): ?>
            <tr>
                <td>
                    <?= h($row['teacher_name']) ?><br>
                    <span class="muted"><?= h($row['teacher_email']) ?></span>
                </td>

                <td><?= h($row['title'] ?? 'None') ?></td>
                <td><?= h($row['concern_type']) ?></td>
                <td><?= h($row['concern_notes']) ?></td>
                <td><?= h($row['created_at']) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</section>

<?php page_end(); ?>