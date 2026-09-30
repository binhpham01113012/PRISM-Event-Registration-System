<?php
session_start();

require_once __DIR__ . '/includes/functions.php';

$pdo = db();

$stats = [
    'classes' => $pdo
        ->query('SELECT COUNT(*) AS count FROM classes')
        ->fetch()['count'] ?? 0,

    'registrations' => $pdo
        ->query("SELECT COUNT(*) AS count FROM registrations WHERE status = 'registered'")
        ->fetch()['count'] ?? 0,

    'certificates' => $pdo
        ->query("SELECT COUNT(*) AS count FROM certificates WHERE certificate_status = 'pending'")
        ->fetch()['count'] ?? 0,
];

page_start('PRISM | Home');
?>
<section class="hero">
  <div>
    <p class="muted"><strong>Professional Development Registration System</strong></p>
    <h1>Manage PRISM classes, registrations, surveys, attendance, and certificates in one website.</h1>
    <p>Teachers, coaches, administrators, and PRISM workers can use this system to register for professional development events and manage class records.</p>
    <div class="actions"><a class="button" href="classes.php">View Classes</a><a class="button secondary" href="register.php">Create Account</a></div>
  </div>
 
</section>
<section class="grid">
  <div class="card"><div class="stat"><?=h($stats['classes'])?></div><p>Classes available</p></div>
  <div class="card"><div class="stat"><?=h($stats['registrations'])?></div><p>Active registrations</p></div>
  <div class="card"><div class="stat"><?=h($stats['certificates'])?></div><p>Pending certificates</p></div>
</section>
<?php page_end(); ?>
