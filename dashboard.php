<?php
session_start();

require_once __DIR__ . '/includes/functions.php';

require_login();

$pdo = db();
$user = current_user();

if (is_admin()) {
    redirect('admin.php');
}

$stmt = $pdo->prepare(
    "SELECT r.*, c.*
     FROM registrations r
     JOIN classes c ON c.class_id = r.class_id
     WHERE r.user_id = ?
     ORDER BY c.class_date"
);

$stmt->execute([$_SESSION['user_id']]);
$registrations = $stmt->fetchAll();

$certificates = $pdo->prepare(
    "SELECT COUNT(*) AS total
     FROM certificates cert
     JOIN teachers t ON t.teacher_id = cert.teacher_id
     WHERE t.user_id = ?
        AND cert.certificate_status = 'issued'"
);

$certificates->execute([$_SESSION['user_id']]);
$certificateCount = $certificates->fetch()['total'];

page_start('PRISM | Dashboard');
?>

<section class="hero">
    <div>
        <p class="muted">Teacher/Coach Dashboard</p>
        <h1>Welcome, <?= h($user['username']) ?></h1>

        <p>
            View your registrations, cancel eligible classes, complete surveys,
            and update your personal/demographic information.
        </p>
    </div>

    <div class="card">
        <h2>Quick Actions</h2>

        <div class="actions">
            <a class="button" href="classes.php">Register for a Class</a>
            <a class="button secondary" href="profile.php">Update Profile</a>
            <a class="button secondary" href="survey.php">Post Survey</a>
        </div>
    </div>
</section>

<section class="grid">
    <div class="card">
        <div class="stat"><?= count($registrations) ?></div>
        <p>Total registrations</p>
    </div>

    <div class="card">
        <div class="stat"><?= h($certificateCount) ?></div>
        <p>Issued certificates</p>
    </div>
</section>

<section class="card">
    <h2>My Registrations</h2>

    <div class="table-wrap">
        <table class="table">
            <tr>
                <th>Class</th>
                <th>Date</th>
                <th>Status</th>
                <th>Action</th>
            </tr>

            <?php if (!$registrations): ?>
                <tr>
                    <td colspan="4">No registrations yet.</td>
                </tr>
            <?php endif; ?>

            <?php foreach ($registrations as $registration): ?>
                <tr>
                    <td><?= h($registration['title']) ?></td>

                    <td>
                        <?= h($registration['class_date']) ?>
                        at <?= h(substr($registration['class_time'], 0, 5)) ?>
                    </td>

                    <td>
                        <span class="badge <?= h($registration['status']) ?>">
                            <?= h($registration['status']) ?>
                        </span>
                    </td>

                    <td>
                        <?php if ($registration['status'] !== 'cancelled'): ?>
                            <a
                                class="button small danger"
                                href="action.php?do=cancel_registration&id=<?= h($registration['registration_id']) ?>"
                                onclick="return confirm('Cancel this registration?')"
                            >
                                Cancel
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
</section>

<?php page_end(); ?>