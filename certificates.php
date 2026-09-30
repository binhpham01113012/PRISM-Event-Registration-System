<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

require_once __DIR__ . '/includes/functions.php';

require_admin();

$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $issueDate = post('issue_date') ?: null;

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
        'INSERT INTO certificates (
            class_id,
            teacher_id,
            certificate_status,
            certificate_file,
            issue_date
        )
        VALUES (?, ?, ?, ?, ?)'
    );

    $stmt->execute([
        post('class_id'),
        post('teacher_id'),
        post('certificate_status'),
        $certificateFile,
        $issueDate
    ]);

    flash('Certificate record saved.');
    redirect('certificates.php');
}

$people = $pdo
    ->query(
        "SELECT 
            r.class_id,
            c.title,
            t.teacher_id,
            t.first_name,
            t.last_name
         FROM registrations r
         JOIN classes c ON c.class_id = r.class_id
         JOIN teachers t ON t.user_id = r.user_id
         WHERE r.status = 'registered'
         ORDER BY c.title, t.last_name"
    )
    ->fetchAll();

$rows = $pdo
    ->query(
        "SELECT 
            cert.*,
            c.title,
            t.first_name,
            t.last_name
         FROM certificates cert
         JOIN classes c ON c.class_id = cert.class_id
         JOIN teachers t ON t.teacher_id = cert.teacher_id
         ORDER BY cert.certificate_id DESC"
    )
    ->fetchAll();

page_start('PRISM | Certificates');
?>

<section class="card form">
    <h1>Issue Certificates</h1>

    <?php if (!$people): ?>
        <p class="muted">
            No registered participants are available for certificates yet.
        </p>
    <?php else: ?>
        <form method="post" enctype="multipart/form-data">
            <div class="field">
                <label>Participant/Class</label>

                <select
                    name="teacher_id"
                    required
                    onchange="document.getElementById('class_id').value = this.selectedOptions[0].dataset.classid"
                >
                    <?php foreach ($people as $person): ?>
                        <option
                            data-classid="<?= h($person['class_id']) ?>"
                            value="<?= h($person['teacher_id']) ?>"
                        >
                            <?= h($person['first_name'] . ' ' . $person['last_name'] . ' - ' . $person['title']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <input
                    type="hidden"
                    id="class_id"
                    name="class_id"
                    value="<?= h($people[0]['class_id'] ?? '') ?>"
                >
            </div>

            <div class="form-row">
                <div class="field">
                    <label>Status</label>

                    <select name="certificate_status" required>
                        <option value="not-issued">Not Issued</option>
                        <option value="pending">Pending</option>
                        <option value="issued">Issued</option>
                    </select>
                </div>

                <div class="field">
                    <label>Issue Date</label>
                    <input type="date" name="issue_date">
                </div>
            </div>

            <div class="field">
                <label>Certificate File</label>
                <input
                    type="file"
                    name="certificate_file"
                    accept=".pdf,.doc,.docx,.png,.jpg,.jpeg"
                >
            </div>

            <button class="button" type="submit">Save Certificate</button>
        </form>
    <?php endif; ?>
</section>

<section class="card">
    <h2>Certificate Statuses</h2>

    <div class="table-wrap">
        <table class="table">
            <tr>
                <th>Participant</th>
                <th>Class</th>
                <th>Status</th>
                <th>Issue Date</th>
                <th>File</th>
            </tr>

            <?php foreach ($rows as $row): ?>
                <tr>
                    <td><?= h($row['first_name'] . ' ' . $row['last_name']) ?></td>
                    <td><?= h($row['title']) ?></td>

                    <td>
                        <span class="badge <?= h($row['certificate_status']) ?>">
                            <?= h($row['certificate_status']) ?>
                        </span>
                    </td>

                    <td><?= h($row['issue_date'] ?: 'None') ?></td>

                    <td>
                        <?php if (!empty($row['certificate_file']) && $row['certificate_file'] !== 'None'): ?>
                            <a href="<?= h($row['certificate_file']) ?>" target="_blank">
                                View Certificate
                            </a>
                        <?php else: ?>
                            None
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>

            <?php if (!$rows): ?>
                <tr>
                    <td colspan="5">No certificate records yet.</td>
                </tr>
            <?php endif; ?>
        </table>
    </div>
</section>

<?php page_end(); ?>