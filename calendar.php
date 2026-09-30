<?php
session_start();

require_once __DIR__ . '/includes/functions.php';

require_login();

$pdo = db();

if (is_admin()) {
    $stmt = $pdo->query(
        'SELECT * 
         FROM classes 
         ORDER BY class_date, class_time'
    );

    $classes = $stmt->fetchAll();
} else {
    $stmt = $pdo->prepare(
        "SELECT c.*, r.status 
         FROM registrations r
         JOIN classes c ON c.class_id = r.class_id
         WHERE r.user_id = ?
            AND r.status <> 'cancelled'
         ORDER BY c.class_date, c.class_time"
    );

    $stmt->execute([$_SESSION['user_id']]);
    $classes = $stmt->fetchAll();
}

page_start('PRISM | Calendar');
?>

<section class="card">
    <h1>Calendar View</h1>

    <p class="muted">
        <?= is_admin() ? 'All PRISM events.' : 'Your registered and waitlisted events.' ?>
    </p>
</section>

<section class="grid">
    <?php if (!$classes): ?>
        <div class="card">
            No events on your calendar.
        </div>
    <?php endif; ?>

    <?php foreach ($classes as $class): ?>
        <article class="card calendar-day">
            <h2>
                <?= h(date('M j, Y', strtotime($class['class_date']))) ?>
            </h2>

            <p>
                <strong><?= h($class['title']) ?></strong>
            </p>

            <p>
                <?= h(substr($class['class_time'], 0, 5)) ?>
                • <?= h($class['location']) ?>
                • <?= h($class['modality']) ?>
            </p>

            <p class="muted">
                Facilitator: <?= h($class['facilitator']) ?>
            </p>

            <?php if (isset($class['status'])): ?>
                <span class="badge <?= h($class['status']) ?>">
                    <?= h($class['status']) ?>
                </span>
            <?php endif; ?>
        </article>
    <?php endforeach; ?>
</section>

<?php page_end(); ?>