<?php
session_start();

require_once __DIR__ . '/includes/functions.php';

require_admin();

$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $certificateFile = trim(post('certificate_file'));

    if ($certificateFile === '') {
        $certificateFile = 'None';
    }
    
    $certificateFile = 'None';

    if (!empty($_FILES['certificate_file']['name'])) {
        $uploadDir = __DIR__ . '/uploads/certificates/';
    
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
    
        $originalName = basename($_FILES['certificate_file']['name']);
        $safeName = time() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $originalName);
        $targetPath = $uploadDir . $safeName;
    
        if (move_uploaded_file($_FILES['certificate_file']['tmp_name'], $targetPath)) {
            $certificateFile = 'uploads/certificates/' . $safeName;
        }
    }

    $stmt = $pdo->prepare(
        'INSERT INTO classes (
            title,
            summary,
            topic,
            grade_band,
            modality,
            capacity,
            class_date,
            class_time,
            location,
            pd_hours,
            facilitator,
            certificate_file
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );

    $stmt->execute([
        post('title'),
        post('summary'),
        post('topic'),
        post('grade_band'),
        post('modality'),
        post('capacity'),
        post('class_date'),
        post('class_time'),
        post('location'),
        post('pd_hours'),
        post('facilitator'),
        $certificateFile
    ]);

    flash('Class created.');
    redirect('manage_classes.php');
}

$classes = $pdo
    ->query(
        "SELECT 
            c.*,
            (
                SELECT COUNT(*) 
                FROM registrations r 
                WHERE r.class_id = c.class_id 
                    AND r.status = 'registered'
            ) AS registered_count
         FROM classes c
         ORDER BY class_date DESC"
    )
    ->fetchAll();

page_start('PRISM | Manage Classes');
?>
<section class="card form">
    <h1>Set Up a Class</h1>
    <form method="post" enctype="multipart/form-data">
        <div class="field">
            <label>Class Title</label>
            <input name="title" required>
        </div>
        <div class="field">
            <label>Summary</label>
            <textarea name="summary" required></textarea>
        </div>
        <div class="form-row">
            <div class="field">
                <label>Topic</label>
                <select name="topic">
                    <option value="general-science">General Science</option>
                    <option value="computer-science">Computer Science</option>
                    <option value="biology">Biology</option>
                    <option value="chemistry">Chemistry</option>
                    <option value="physics">Physics</option>
                    <option value="engineering">Engineering</option>
                    <option value="mathematics">Mathematics</option>
                    <option value="pedagogy">Pedagogy</option>
                </select>
            </div>
        <div class="field">
            <label>Grade Band</label>
            <select name="grade_band">
                <option value="pre-k">Pre-K</option>
                <option value="k-2">K-2</option>
                <option value="3-5">3-5</option>
                <option value="6-8">6-8</option>
                <option value="9-12">9-12</option>
                <option value="higher-education">Higher Education</option>
            </select>
        </div>
        <div class="field">
            <label>Modality</label>
            <select name="modality">
                <option value="in-person">In-person</option>
                <option value="online">Online</option>
                <option value="field">Field</option>
            </select>
        </div>
    </div>
    <div class="form-row">
        <div class="field">
            <label>Capacity</label>
            <input type="number" name="capacity" min="1" required>
        </div>
        <div class="field">
            <label>Date</label>
            <input type="date" name="class_date" required>
        </div>
        <div class="field">
            <label>Time</label>
            <input type="time" name="class_time" required>
        </div>
    </div>
        <div class="form-row">
            <div class="field">
                <label>Location</label>
                <input name="location" required>
        </div>
        <div class="field">
            <label>PD Hours</label>
            <input type="number" step="0.5" name="pd_hours" required>
        </div>
        <div class="field">
            <label>Facilitator</label>
            <input name="facilitator" required>
        </div>
    </div>
    <div class="field">
        <label>Certificate File</label>
        <input type="file" name="certificate_file" accept=".pdf,.doc,.docx,.png,.jpg,.jpeg">
    </div>
        <button class="button">Create Class</button>
    </form>
</section>
<section class="card">
    <h2>Current Classes</h2>
    <div class="table-wrap">
        <table class="table">
            <tr>
                <th>Title</th>
                <th>Date</th>
                <th>Seats</th>
                <th>Facilitator</th>
                <th>Action</th>
            </tr>
            <?php foreach ($classes as $class): ?>
            <tr>
                <td><?= h($class['title']) ?></td>
                <td><?= h($class['class_date']) ?></td>
                <td><?= h($class['registered_count']) ?> / <?= h($class['capacity']) ?></td>
                <td><?= h($class['facilitator']) ?></td>
                <td>
                    <a
                        class="button small danger"
                        href="action.php?do=delete_class&id=<?= h($class['class_id']) ?>"
                        onclick="return confirm('Delete this class?')"
                    >
                        Delete
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>
        
        </table>
    </div>
</section>
<?php page_end(); ?>
