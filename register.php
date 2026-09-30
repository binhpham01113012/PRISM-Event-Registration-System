<?php
session_start();

require_once __DIR__ . '/includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = post('username');
    $email = post('email');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $role = post('role', 'teacher');

    if ($role === 'prism_worker') {
        $role = 'admin';
    }

    if ($password !== $confirmPassword) {
        flash('Passwords do not match.', 'error');
    } else {
        try {
            $pdo = db();
            $pdo->beginTransaction();

            $stmt = $pdo->prepare(
                'INSERT INTO users (username, email, password, role)
                 VALUES (?, ?, ?, ?)'
            );

            $stmt->execute([
                $username,
                $email,
                password_hash($password, PASSWORD_DEFAULT),
                $role
            ]);

            $userId = $pdo->lastInsertId();

            $jobPosition = 'teacher';

                if ($role === 'coach') {
                    $jobPosition = 'coach';
                } elseif ($role === 'admin') {
                    $jobPosition = 'administrator';
                } elseif ($role === 'prism_worker') {
                    $jobPosition = 'prism_worker';
                }

            $stmt = $pdo->prepare(
                'INSERT INTO teachers (
                    user_id,
                    first_name,
                    middle_initial,
                    last_name,
                    work_email,
                    personal_email,
                    preferred_phone,
                    district,
                    school,
                    job_position,
                    grade_band,
                    gender,
                    race,
                    eligibility_status
                )
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );

            $stmt->execute([
                $userId,
                post('first_name'),
                'N/A',
                post('last_name'),
                $email,
                'N/A',
                'N/A',
                'N/A',
                'N/A',
                $jobPosition,
                'multiple',
                'prefer-not-to-say',
                'N/A',
                'needs-survey'
            ]);

            $pdo->commit();

            flash('Account created. You can log in now.');
            redirect('login.php');
        } catch (PDOException $e) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }

            flash('Database error: ' . $e->getMessage(), 'error');
        }
    }
}

page_start('PRISM | Create Account');
?>

<section class="card form">
    <h1>Create Account</h1>

    <form method="post">
        <div class="form-row">
            <div class="field">
                <label>First Name</label>
                <input name="first_name" required>
            </div>

            <div class="field">
                <label>Last Name</label>
                <input name="last_name" required>
            </div>
        </div>

        <div class="form-row">
            <div class="field">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>

            <div class="field">
                <label>Username</label>
                <input name="username" required>
            </div>
        </div>

        <div class="field">
            <label>Account Type</label>
            <select name="role" required>
                <option value="teacher">Teacher</option>
                <option value="coach">Coach</option>
                <option value="admin">Admin</option>
                <option value="prism_worker">PRISM Worker</option>
            </select>
        </div>

        <div class="form-row">
            <div class="field">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>

            <div class="field">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" required>
            </div>
        </div>

        <button class="button" type="submit">Create Account</button>
    </form>
</section>

<?php page_end(); ?>