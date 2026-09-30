<?php
session_start();

require_once __DIR__ . '/includes/functions.php';

require_login();

$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare(
        'SELECT teacher_id 
         FROM teachers 
         WHERE user_id = ?'
    );

    $stmt->execute([$_SESSION['user_id']]);
    $exists = $stmt->fetch();

    $data = [
        post('first_name'),
        post('middle_initial') ?: 'N/A',
        post('last_name'),
        post('work_email'),
        post('personal_email') ?: 'N/A',
        post('phone') ?: 'N/A',
        post('district') ?: 'N/A',
        post('school') ?: 'N/A',
        post('job_position') ?: 'teacher',
        post('grade_band') ?: 'multiple',
        post('gender') ?: 'prefer-not-to-say',
        post('race') ?: 'N/A',
        'eligible',
        $_SESSION['user_id']
    ];

    if ($exists) {
        $sql = 
            'UPDATE teachers 
             SET first_name = ?,
                 middle_initial = ?,
                 last_name = ?,
                 work_email = ?,
                 personal_email = ?,
                 preferred_phone = ?,
                 district = ?,
                 school = ?,
                 job_position = ?,
                 grade_band = ?,
                 gender = ?,
                 race = ?,
                 eligibility_status = ?
             WHERE user_id = ?';
    } else {
        $sql = 
            'INSERT INTO teachers (
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
                eligibility_status,
                user_id
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
    }

    $pdo->prepare($sql)->execute($data);

    flash('Profile updated.');
    redirect('profile.php');
}

$stmt = $pdo->prepare(
    'SELECT u.email, t.* 
     FROM users u
     LEFT JOIN teachers t ON t.user_id = u.user_id
     WHERE u.user_id = ?'
);

$stmt->execute([$_SESSION['user_id']]);
$profile = $stmt->fetch();

page_start('PRISM | Profile');
?>

<section class="card form">
    <h1>Personal and Demographic Information</h1>

    <p class="muted">
        Complete this information so PRISM workers can review eligibility, certificates, and district reporting.
    </p>

    <form method="post">
        <div class="form-row">
            <div class="field">
                <label>First Name</label>
                <input name="first_name" value="<?= h($profile['first_name'] ?? '') ?>" required>
            </div>

            <div class="field">
                <label>Middle Initial</label>
                <input name="middle_initial" value="<?= h($profile['middle_initial'] ?? 'N/A') ?>">
            </div>

            <div class="field">
                <label>Last Name</label>
                <input name="last_name" value="<?= h($profile['last_name'] ?? '') ?>" required>
            </div>
        </div>

        <div class="form-row">
            <div class="field">
                <label>Work Email</label>
                <input 
                    type="email" 
                    name="work_email" 
                    value="<?= h($profile['work_email'] ?? $profile['email'] ?? '') ?>" 
                    required
                >
            </div>

            <div class="field">
                <label>Personal Email</label>
                <input 
                    name="personal_email" 
                    value="<?= h($profile['personal_email'] ?? 'N/A') ?>"
                >
            </div>

            <div class="field">
                <label>Phone</label>
                <input name="phone" value="<?= h($profile['preferred_phone'] ?? 'N/A') ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="field">
                <label>District</label>
                <input name="district" value="<?= h($profile['district'] ?? 'N/A') ?>">
            </div>

            <div class="field">
                <label>School</label>
                <input name="school" value="<?= h($profile['school'] ?? 'N/A') ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="field">
                <label>Job Position</label>
                <select name="job_position">
                    <option value="teacher" <?= ($profile['job_position'] ?? '') === 'teacher' ? 'selected' : '' ?>>Teacher</option>
                    <option value="coach" <?= ($profile['job_position'] ?? '') === 'coach' ? 'selected' : '' ?>>Coach</option>
                    <option value="administrator" <?= ($profile['job_position'] ?? '') === 'administrator' ? 'selected' : '' ?>>Administrator</option>
                    <option value="other" <?= ($profile['job_position'] ?? '') === 'other' ? 'selected' : '' ?>>Other</option>
                </select>
            </div>

            <div class="field">
                <label>Grade Band</label>
                <select name="grade_band">
                    <option value="pre-k" <?= ($profile['grade_band'] ?? '') === 'pre-k' ? 'selected' : '' ?>>Pre-K</option>
                    <option value="k-2" <?= ($profile['grade_band'] ?? '') === 'k-2' ? 'selected' : '' ?>>K-2</option>
                    <option value="3-5" <?= ($profile['grade_band'] ?? '') === '3-5' ? 'selected' : '' ?>>3-5</option>
                    <option value="6-8" <?= ($profile['grade_band'] ?? '') === '6-8' ? 'selected' : '' ?>>6-8</option>
                    <option value="9-12" <?= ($profile['grade_band'] ?? '') === '9-12' ? 'selected' : '' ?>>9-12</option>
                    <option value="higher-ed" <?= ($profile['grade_band'] ?? '') === 'higher-ed' ? 'selected' : '' ?>>Higher Education</option>
                    <option value="multiple" <?= ($profile['grade_band'] ?? '') === 'multiple' ? 'selected' : '' ?>>Multiple</option>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="field">
                <label>Gender</label>
                <select name="gender">
                    <option value="female" <?= ($profile['gender'] ?? '') === 'female' ? 'selected' : '' ?>>Female</option>
                    <option value="male" <?= ($profile['gender'] ?? '') === 'male' ? 'selected' : '' ?>>Male</option>
                    <option value="non-binary" <?= ($profile['gender'] ?? '') === 'non-binary' ? 'selected' : '' ?>>Non-binary</option>
                    <option value="prefer-not-to-say" <?= ($profile['gender'] ?? '') === 'prefer-not-to-say' ? 'selected' : '' ?>>Prefer not to say</option>
                </select>
            </div>

            <div class="field">
                <label>Race/Ethnicity</label>
                <input name="race" value="<?= h($profile['race'] ?? 'N/A') ?>">
            </div>
        </div>

        <button class="button" type="submit">Save Profile</button>
    </form>
</section>

<?php page_end(); ?>