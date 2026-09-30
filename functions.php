<?php
require_once __DIR__ . '/db.php';

function h($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function post($key, $default = '')
{
    return trim($_POST[$key] ?? $default);
}

function getv($key, $default = '')
{
    return trim($_GET[$key] ?? $default);
}

function redirect($path): void
{
    header('Location: ' . $path);
    exit;
}

function current_user()
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }

    $stmt = db()->prepare(
        'SELECT * 
         FROM users 
         WHERE user_id = ? 
         LIMIT 1'
    );

    $stmt->execute([$_SESSION['user_id']]);

    return $stmt->fetch();
}

function require_login(): void
{
    if (empty($_SESSION['user_id'])) {
        redirect('login.php');
    }
}

function is_admin(): bool
{
    return isset($_SESSION['role'])
        && in_array($_SESSION['role'], ['admin', 'prism_worker'], true);
}

function require_admin(): void
{
    require_login();

    if (!is_admin()) {
        redirect('dashboard.php');
    }
}

function nav(): void
{
    $logged = !empty($_SESSION['user_id']);
    $admin = is_admin();

    echo '<header class="navbar">';
    echo '<a class="logo" href="index.php">PRISM</a>';
    echo '<nav>';

    echo '<a href="classes.php">Classes</a>';

    if ($logged) {
        echo '<a href="dashboard.php">Dashboard</a>';
        echo '<a href="profile.php">Profile</a>';
        echo '<a href="calendar.php">Calendar</a>';
    }

    if ($admin) {
        echo '<a href="class_staff.php">Class Staff</a>';
    }

    echo '<a href="resources.php">Past Events</a>';

    if ($logged) {
        echo '<a class="nav-button" href="logout.php">Logout</a>';
    } else {
        echo '<a class="nav-button" href="login.php">Login</a>';
    }

    echo '</nav>';
    echo '</header>';
}

function flash($message = null, $type = 'success')
{
    if ($message !== null) {
        $_SESSION['flash'] = [
            'message' => $message,
            'type' => $type
        ];

        return;
    }

    if (!empty($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);

        echo '<div class="flash ' . h($flash['type']) . '">';
        echo h($flash['message']);
        echo '</div>';
    }
}

function page_start($title): void
{
    echo '<!doctype html>';
    echo '<html lang="en">';
    echo '<head>';
    echo '<meta charset="utf-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
    echo '<title>' . h($title) . '</title>';
    echo '<link rel="stylesheet" href="style.css">';
    echo '</head>';
    echo '<body>';

    nav();

    echo '<main class="container">';

    flash();
}

function page_end(): void
{
    echo '</main>';
    echo '<footer>&copy; 2026 PRISM. Teacher Professional Development Registration System.</footer>';
    echo '</body>';
    echo '</html>';
}

function class_status($class_id, $capacity)
{
    $stmt = db()->prepare(
        "SELECT COUNT(*) AS total 
         FROM registrations 
         WHERE class_id = ? 
            AND status = 'registered'"
    );

    $stmt->execute([$class_id]);

    $count = (int) $stmt->fetch()['total'];
    $status = $count >= (int) $capacity ? 'waitlisted' : 'registered';

    return [$count, $status];
}
?>