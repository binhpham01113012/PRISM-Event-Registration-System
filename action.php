<?php
session_start();

require_once __DIR__ . '/includes/functions.php';

require_login();

$do = getv('do');
$id = (int) getv('id');
$pdo = db();

try {
    if ($do === 'register_class') {
        $stmt = $pdo->prepare('SELECT * FROM classes WHERE class_id = ?');
        $stmt->execute([$id]);
        $class = $stmt->fetch();

        if (!$class) {
            throw new Exception('Class not found.');
        }

        $stmt = $pdo->prepare(
            'SELECT registration_id, status 
             FROM registrations 
             WHERE user_id = ? AND class_id = ?'
        );
        $stmt->execute([$_SESSION['user_id'], $id]);
        $existing = $stmt->fetch();

        if ($existing && $existing['status'] !== 'cancelled') {
            throw new Exception('You are already registered or waitlisted for this class.');
        }

        [$count, $status] = class_status($id, $class['capacity']);

        if ($existing) {
            $stmt = $pdo->prepare(
                'UPDATE registrations 
                 SET status = ?, registration_timestamp = NOW() 
                 WHERE registration_id = ?'
            );
            $stmt->execute([$status, $existing['registration_id']]);
        } else {
            $stmt = $pdo->prepare(
                'INSERT INTO registrations (user_id, class_id, status) 
                 VALUES (?, ?, ?)'
            );
            $stmt->execute([$_SESSION['user_id'], $id, $status]);
        }

        if ($status === 'registered') {
            flash('You are registered for the class.');
        } else {
            flash('The class is full, so you were added to the waitlist.');
        }

        redirect('dashboard.php');
    }

    if ($do === 'cancel_registration') {
        $stmt = $pdo->prepare(
            'SELECT r.*, c.class_date 
             FROM registrations r
             JOIN classes c ON c.class_id = r.class_id
             WHERE r.registration_id = ? AND r.user_id = ?'
        );
        $stmt->execute([$id, $_SESSION['user_id']]);
        $registration = $stmt->fetch();

        if (!$registration) {
            throw new Exception('Registration not found.');
        }

        $deadline = new DateTime($registration['class_date']);
        $deadline->modify('-5 weekdays');

        $today = new DateTime(date('Y-m-d'));

        if ($today > $deadline) {
            throw new Exception(
                'Registration can only be cancelled up to 5 working days before the event.'
            );
        }

        $stmt = $pdo->prepare(
            "UPDATE registrations 
             SET status = 'cancelled' 
             WHERE registration_id = ? AND user_id = ?"
        );
        $stmt->execute([$id, $_SESSION['user_id']]);

        flash('Registration cancelled.');
        redirect('dashboard.php');
    }

    if ($do === 'delete_class' && is_admin()) {
        $stmt = $pdo->prepare('DELETE FROM classes WHERE class_id = ?');
        $stmt->execute([$id]);

        flash('Class deleted.');
        redirect('manage_classes.php');
    }

    throw new Exception('Invalid action.');
} catch (Exception $e) {
    flash($e->getMessage(), 'error');

    $redirectTo = !empty($_SERVER['HTTP_REFERER'])
        ? $_SERVER['HTTP_REFERER']
        : 'dashboard.php';

    redirect($redirectTo);
}