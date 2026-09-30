<?php
session_start();

require_once __DIR__ . '/includes/functions.php';

$q = getv('q');
$topic = getv('topic');

$sql = "
    SELECT 
        c.*,
        (
            SELECT COUNT(*) 
            FROM registrations r 
            WHERE r.class_id = c.class_id 
                AND r.status = 'registered'
        ) AS registered_count
    FROM classes c
    WHERE 1 = 1
";

$args = [];

if ($q) {
    $sql .= " 
        AND (
            title LIKE ? 
            OR summary LIKE ? 
            OR facilitator LIKE ? 
            OR location LIKE ?
        )
    ";

    $args = array_fill(0, 4, "%$q%");
}

if ($topic) {
    $sql .= " AND topic = ?";
    $args[] = $topic;
}

$sql .= " ORDER BY class_date, class_time";

$stmt = db()->prepare($sql);
$stmt->execute($args);

$classes = $stmt->fetchAll();

$topics = [
    'general-science',
    'computer-science',
    'biology',
    'chemistry',
    'physics',
    'engineering',
    'mathematics',
    'pedagogy'
];

page_start('PRISM | Classes');
?>

<section class="card">
    <h1>Available Classes</h1>

    <p class="muted">
        Teachers, coaches, and admins can browse classes, register, or join the waitlist if a class is full.
    </p>

    <form class="searchbar" method="get">
        <input
            name="q"
            placeholder="Search classes"
            value="<?= h($q) ?>"
        >

        <select name="topic">
            <option value="">All Topics</option>

            <?php foreach ($topics as $classTopic): ?>
                <option
                    value="<?= h($classTopic) ?>"
                    <?= $topic === $classTopic ? 'selected' : '' ?>
                >
                    <?= h($classTopic) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button class="button" type="submit">
            Filter
        </button>
    </form>
</section>

<section class="grid">
    <?php if (!$classes): ?>
        <div class="card">
            No classes found.
        </div>
    <?php endif; ?>

    <?php foreach ($classes as $class): ?>
        <?php $isFull = $class['registered_count'] >= $class['capacity']; ?>

        <article class="card class-card">
            <div>
                <span class="badge <?= $isFull ? 'waitlisted' : 'open' ?>">
                    <?= $isFull ? 'Waitlist' : 'Open' ?>
                </span>

                <h2><?= h($class['title']) ?></h2>

                <p><?= h($class['summary']) ?></p>
            </div>

            <div class="class-meta">
                <p><strong>Topic:</strong> <?= h($class['topic']) ?></p>
                <p><strong>Grade:</strong> <?= h($class['grade_band']) ?></p>
                <p><strong>Modality:</strong> <?= h($class['modality']) ?></p>
                <p><strong>Date:</strong> <?= h($class['class_date']) ?></p>
                <p><strong>Time:</strong> <?= h(substr($class['class_time'], 0, 5)) ?></p>
                <p><strong>Location:</strong> <?= h($class['location']) ?></p>
                <p><strong>PD Hours:</strong> <?= h($class['pd_hours']) ?></p>
                <p><strong>Seats:</strong> <?= h($class['registered_count']) ?> / <?= h($class['capacity']) ?></p>
                <p><strong>Facilitator:</strong> <?= h($class['facilitator']) ?></p>
            </div>

            <div class="actions">
                <?php if (!empty($_SESSION['user_id'])): ?>
                    <a
                        class="button"
                        href="action.php?do=register_class&id=<?= h($class['class_id']) ?>"
                    >
                        <?= $isFull ? 'Join Waitlist' : 'Register' ?>
                    </a>
                <?php else: ?>
                    <a class="button" href="login.php">
                        Login to Register
                    </a>
                <?php endif; ?>
            </div>
        </article>
    <?php endforeach; ?>
</section>

<?php page_end(); ?>