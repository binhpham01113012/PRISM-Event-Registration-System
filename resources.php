<?php
session_start();

require_once __DIR__ . '/includes/functions.php';

$rows = db()
    ->query(
        "SELECT *
         FROM classes
         WHERE class_date < CURDATE()
         ORDER BY class_date DESC"
    )
    ->fetchAll();

page_start('PRISM | Past Events');
?>

<section class="card">
    <h1>Past Events and Resources</h1>

    <p class="muted">
        View past events and descriptions from previous PRISM classes.
    </p>
</section>

<section class="grid">
    <?php if (!$rows): ?>
        <div class="card">
            No past events yet.
        </div>
    <?php endif; ?>

    <?php foreach ($rows as $row): ?>
        <article class="card">
            <h2><?= h($row['title']) ?></h2>

            <p><?= h($row['summary']) ?></p>

            <p>
                <strong>Date:</strong> <?= h($row['class_date']) ?>
                •
                <strong>Facilitator:</strong> <?= h($row['facilitator']) ?>
            </p>
        </article>
    <?php endforeach; ?>
</section>

<?php page_end(); ?>