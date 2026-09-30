<?php
session_start();

require_once __DIR__ . '/includes/functions.php';

require_admin();

$pdo = db();

$genderRows = $pdo
    ->query(
        "SELECT gender, COUNT(*) AS total
         FROM teachers
         GROUP BY gender"
    )
    ->fetchAll();

$raceRows = $pdo
    ->query(
        "SELECT race, COUNT(*) AS total
         FROM teachers
         GROUP BY race"
    )
    ->fetchAll();

$genderLabels = [];
$genderData = [];

foreach ($genderRows as $row) {
    $genderLabels[] = $row['gender'] ?: 'N/A';
    $genderData[] = (int) $row['total'];
}

$raceLabels = [];
$raceData = [];

foreach ($raceRows as $row) {
    $raceLabels[] = $row['race'] ?: 'N/A';
    $raceData[] = (int) $row['total'];
}

$stats = [];

foreach (['classes', 'registrations', 'teachers', 'concern_flags'] as $table) {
    $stats[$table] = $pdo
        ->query("SELECT COUNT(*) AS count FROM $table")
        ->fetch()['count'];
}

$districtSummary = $pdo
    ->query(
        "SELECT district, COUNT(*) AS total
         FROM teachers
         WHERE district IS NOT NULL AND district <> ''
         GROUP BY district
         ORDER BY total DESC
         LIMIT 5"
    )
    ->fetchAll();

page_start('PRISM | Admin Dashboard');
?>

<section class="hero">
    <div>
        <p class="muted">PRISM Worker Dashboard</p>
        <h1>PRISM Worker Dashboard</h1>
        <p>Manage classes, registration lists, attendance, teacher records, certificates, concerns, and reports.</p>
    </div>

    <div class="actions">
        <a class="button" href="manage_classes.php">Set Up Class</a>
        <a class="button secondary" href="attendance.php">Record Attendance</a>
    </div>
</section>

<section class="grid">
    <div class="card">
        <div class="stat"><?= h($stats['classes']) ?></div>
        <p>Total classes</p>
    </div>

    <div class="card">
        <div class="stat"><?= h($stats['registrations']) ?></div>
        <p>Registrations</p>
    </div>

    <div class="card">
        <div class="stat"><?= h($stats['teachers']) ?></div>
        <p>Teacher records</p>
    </div>

    <div class="card">
        <div class="stat"><?= h($stats['concern_flags']) ?></div>
        <p>Concern flags</p>
    </div>
</section>

<section class="grid">
    <a class="card" href="manage_classes.php">
        <h2>Manage Classes</h2>
        <p>Create, review, and delete classes.</p>
    </a>

    <a class="card" href="registrations.php">
        <h2>Registration Lists</h2>
        <p>See all class rosters and waitlists.</p>
    </a>

    <a class="card" href="attendance.php">
        <h2>Attendance</h2>
        <p>Record present, absent, or excused.</p>
    </a>

    <a class="card" href="certificates.php">
        <h2>Certificates</h2>
        <p>Issue certificates and update status.</p>
    </a>

    <a class="card" href="teachers.php">
        <h2>Teacher Records</h2>
        <p>Review profile and demographic data.</p>
    </a>

    <a class="card" href="flags.php">
        <h2>Flag Concerns</h2>
        <p>Document attendance or profile concerns.</p>
    </a>
    <a class="card" href="class_staff.php">
        <h2>Class Staff</h2>
        <p>Assign PRISM staff to classes and roles.</p>
    </a>
</section>

<section class="card">
    <h2>District Summary</h2>

    <table class="table">
        <tr>
            <th>District</th>
            <th>Teachers</th>
        </tr>

        <?php foreach ($districtSummary as $district): ?>
            <tr>
                <td><?= h($district['district']) ?></td>
                <td><?= h($district['total']) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</section>

<section class="grid">
    <div class="card">
        <h2>Gender Demographics</h2>
        <canvas id="genderChart"></canvas>
    </div>

    <div class="card">
        <h2>Race/Ethnicity Demographics</h2>
        <canvas id="raceChart"></canvas>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
const genderLabels = <?= json_encode($genderLabels) ?>;
const genderData = <?= json_encode($genderData) ?>;

const raceLabels = <?= json_encode($raceLabels) ?>;
const raceData = <?= json_encode($raceData) ?>;

new Chart(document.getElementById('genderChart'), {
    type: 'pie',
    data: {
        labels: genderLabels,
        datasets: [{
            data: genderData
        }]
    }
});

new Chart(document.getElementById('raceChart'), {
    type: 'pie',
    data: {
        labels: raceLabels,
        datasets: [{
            data: raceData
        }]
    }
});
</script>
<?php page_end(); ?>