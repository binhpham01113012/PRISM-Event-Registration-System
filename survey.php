<?php
session_start();

require_once __DIR__ . '/includes/functions.php';

require_login();

$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare(
        'INSERT INTO post_surveys (
            user_id,
            class_id,
            class_rating,
            recommend,
            content_helpful
        )
        VALUES (?, ?, ?, ?, ?)'
    );

    $stmt->execute([
        $_SESSION['user_id'],
        post('class_id'),
        post('class_rating'),
        post('recommend'),
        post('content_helpful')
    ]);

    flash('Survey submitted. Thank you for your feedback.');
    redirect('dashboard.php');
}

$stmt = $pdo->prepare(
    "SELECT c.*
     FROM registrations r
     JOIN classes c ON c.class_id = r.class_id
     WHERE r.user_id = ?
        AND r.status IN ('registered', 'waitlisted')
     ORDER BY c.class_date DESC"
);

$stmt->execute([$_SESSION['user_id']]);
$classes = $stmt->fetchAll();

page_start('PRISM | Post Survey');
?>

<section class="card form">
    <h1>Post Survey</h1>

    <?php if (!$classes): ?>
        <p class="muted">
            You do not have any registered or waitlisted classes to review yet.
        </p>

        <a class="button" href="classes.php">Browse Classes</a>
    <?php else: ?>
        <form method="post">
            <div class="field">
                <label>Completed Class</label>

                <select name="class_id" required>
                    <?php foreach ($classes as $class): ?>
                        <option value="<?= h($class['class_id']) ?>">
                            <?= h($class['title']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="field">
                <label>Class Rating</label>

                <select name="class_rating" required>
                    <option value="5">5 - Excellent</option>
                    <option value="4">4 - Good</option>
                    <option value="3">3 - Average</option>
                    <option value="2">2 - Poor</option>
                    <option value="1">1 - Very Poor</option>
                </select>
            </div>

            <div class="field">
                <label>Would you recommend this class?</label>

                <select name="recommend" required>
                    <option value="yes">Yes</option>
                    <option value="maybe">Maybe</option>
                    <option value="no">No</option>
                </select>
            </div>

            <div class="field">
                <label>Was the content helpful?</label>

                <select name="content_helpful" required>
                    <option value="very-helpful">Very helpful</option>
                    <option value="helpful">Helpful</option>
                    <option value="somewhat-helpful">Somewhat helpful</option>
                    <option value="not-helpful">Not helpful</option>
                </select>
            </div>

            <button class="button" type="submit">Submit Survey</button>
        </form>
    <?php endif; ?>
</section>

<?php page_end(); ?>