<?php
require_once 'auth.php';

// Get user information
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT username, email FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$success_message = '';
$error_message = '';

// تغییر پسورد ادمین
if (isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // چک کردن پسورد فعلی
    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $current_hashed = $stmt->fetch()['password'];
    
    if (!password_verify($current_password, $current_hashed)) {
        $error_message = "Current password is incorrect!";
    } elseif ($new_password !== $confirm_password) {
        $error_message = "New password and confirmation do not match!";
    } elseif (strlen($new_password) < 6) {
        $error_message = "New password must be at least 6 characters!";
    } else {
        $new_hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$new_hashed, $user_id]);
        $success_message = "Password changed successfully!";
    }
}

// آمار سیستم
$stats = [];

// تعداد کل پست‌ها
$stmt = $pdo->query("SELECT COUNT(*) as total_posts FROM posts");
$stats['total_posts'] = $stmt->fetch()['total_posts'];

// تعداد کل کاربران
$stmt = $pdo->query("SELECT COUNT(*) as total_users FROM users");
$stats['total_users'] = $stmt->fetch()['total_users'];

// آخرین پست
$stmt = $pdo->query("SELECT created_at FROM posts ORDER BY created_at DESC LIMIT 1");
$last_post = $stmt->fetch();

// آخرین کاربر
$stmt = $pdo->query("SELECT created_at FROM users ORDER BY created_at DESC LIMIT 1");
$last_user = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Admin Panel</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <header class="header">
        <nav class="nav">
            <div class="logo">Admin Panel</div>
            <div class="nav-links">
                <a href="index.php">Dashboard</a>
                <a href="posts.php">Manage Posts</a>
                <a href="users.php">Manage Users</a>
                <a href="settings.php" class="active">Settings</a>
                <a href="../index.php">Back to Site</a>
                <a href="../logout.php">Logout</a>
            </div>
        </nav>
    </header>

    <div class="container">
        <div class="welcome-bg-slider">
            <div class="welcome-bg-img"></div>
            <div class="welcome-message">
                <h1>System Settings</h1>
                <p>Manage system settings and information</p>
            </div>
        </div>

        <?php if ($success_message): ?>
            <div style="background: #d4edda; color: #155724; padding: 1rem; border-radius: 5px; margin-bottom: 1rem;">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div style="background: #f8d7da; color: #721c24; padding: 1rem; border-radius: 5px; margin-bottom: 1rem;">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
            <div class="post">
                <h3 style="color: #333; margin-bottom: 1.5rem; border-bottom: 2px solid #667eea; padding-bottom: 0.5rem;">Change Admin Password</h3>
                <form method="POST">
                    <div style="margin-bottom: 1.5rem;">
                        <label for="current_password" style="display: block; margin-bottom: 0.5rem; color: #333; font-weight: 500;">Current Password:</label>
                        <input type="password" id="current_password" name="current_password" required style="width: 100%; padding: 0.8rem; border: 1px solid #ddd; border-radius: 5px; font-size: 1rem;">
                    </div>
                    <div style="margin-bottom: 1.5rem;">
                        <label for="new_password" style="display: block; margin-bottom: 0.5rem; color: #333; font-weight: 500;">New Password:</label>
                        <input type="password" id="new_password" name="new_password" required style="width: 100%; padding: 0.8rem; border: 1px solid #ddd; border-radius: 5px; font-size: 1rem;">
                    </div>
                    <div style="margin-bottom: 1.5rem;">
                        <label for="confirm_password" style="display: block; margin-bottom: 0.5rem; color: #333; font-weight: 500;">Confirm New Password:</label>
                        <input type="password" id="confirm_password" name="confirm_password" required style="width: 100%; padding: 0.8rem; border: 1px solid #ddd; border-radius: 5px; font-size: 1rem;">
                    </div>
                    <button type="submit" name="change_password" class="btn">Change Password</button>
                </form>
            </div>

            <div class="post">
                <h3 style="color: #333; margin-bottom: 1.5rem; border-bottom: 2px solid #667eea; padding-bottom: 0.5rem;">System Information</h3>
                <div style="background: #f8f9fa; padding: 1rem; border-radius: 5px;">
                    <p><strong>Total Posts:</strong> <?php echo $stats['total_posts']; ?></p>
                    <p><strong>Total Users:</strong> <?php echo $stats['total_users']; ?></p>
                    <?php if ($last_post): ?>
                        <p><strong>Last Post:</strong> <?php echo date('Y/m/d H:i', strtotime($last_post['created_at'])); ?></p>
                    <?php endif; ?>
                    <?php if ($last_user): ?>
                        <p><strong>Last User:</strong> <?php echo date('Y/m/d H:i', strtotime($last_user['created_at'])); ?></p>
                    <?php endif; ?>
                    <p><strong>PHP Version:</strong> <?php echo PHP_VERSION; ?></p>
                    <p><strong>Server:</strong> <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?></p>
                </div>
            </div>
        </div>

        <div class="post" style="border: 2px solid #dc3545; margin-top: 2rem;">
            <h4 style="color: #dc3545; margin-bottom: 1rem;">⚠️ Danger Zone</h4>
            <p>These operations are irreversible!</p>
            <button class="btn" style="background: #dc3545; margin-right: 0.5rem;" onclick="alert('This feature is under development!')">Delete All Posts</button>
            <button class="btn" style="background: #dc3545; margin-right: 0.5rem;" onclick="alert('This feature is under development!')">Delete All Users</button>
            <button class="btn" style="background: #dc3545;" onclick="alert('This feature is under development!')">Database Backup</button>
        </div>
    </div>
</body>
</html>
