<?php
session_start();

require_once __DIR__ . '/includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = post('username');
    $password = $_POST['password'] ?? '';

    $stmt = db()->prepare(
        'SELECT * 
         FROM users 
         WHERE username = ? OR email = ? 
         LIMIT 1'
    );

    $stmt->execute([$username, $username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        flash('Login successful.');
        redirect('dashboard.php');
    }

    flash('Invalid username/email or password.', 'error');
}

page_start('PRISM | Login');
?>

<section class="card form">
    <h1>Login</h1>

    <p class="muted">
        Use your PRISM account to access registrations and dashboards.
    </p>

    <form method="post">
        <div class="field">
            <label>Username or Email</label>
            <input name="username" required>
        </div>

        <div class="field">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>

        <button class="button" type="submit">
            Login
        </button>
    </form>

    <p>
        Need an account?
        <a href="register.php">Create one here</a>.
    </p>
</section>

<?php page_end(); ?>