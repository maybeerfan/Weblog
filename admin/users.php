<?php
require_once 'auth.php';

// Get user information
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT username, email FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// حذف کاربر
if (isset($_POST['delete_user'])) {
    $delete_user_id = $_POST['user_id'];
    
    // Prevent admin from deleting themselves
    if ($delete_user_id == $user_id) {
        $error_message = "You cannot delete your own account!";
    } else {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$delete_user_id]);
        header('Location: users.php?deleted=1');
        exit();
    }
}

// گرفتن همه کاربران
$stmt = $pdo->query("SELECT u.*, COUNT(p.id) as post_count FROM users u LEFT JOIN posts p ON u.id = p.user_id GROUP BY u.id ORDER BY u.created_at DESC");
$users = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin Panel</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <header class="header">
        <nav class="nav">
            <div class="logo">Admin Panel</div>
            <div class="nav-links">
                <a href="index.php">Dashboard</a>
                <a href="posts.php">Manage Posts</a>
                <a href="users.php" class="active">Manage Users</a>
                <a href="settings.php">Settings</a>
                <a href="../index.php">Back to Site</a>
                <a href="../logout.php">Logout</a>
            </div>
        </nav>
    </header>

    <div class="container">
        <div class="welcome-bg-slider">
            <div class="welcome-bg-img"></div>
            <div class="welcome-message">
                <h1>Manage Users</h1>
                <p>Manage all users on the website</p>
            </div>
        </div>

        <?php if (isset($_GET['deleted'])): ?>
            <div style="background: #d4edda; color: #155724; padding: 1rem; border-radius: 5px; margin-bottom: 1rem;">
                User deleted successfully!
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div style="background: #f8d7da; color: #721c24; padding: 1rem; border-radius: 5px; margin-bottom: 1rem;">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <div class="posts">
            <?php if (empty($users)): ?>
                <div class="post">
                    <p style="text-align: center; color: #666;">No users registered yet.</p>
                </div>
            <?php else: ?>
                <?php foreach ($users as $user_item): ?>
                    <article class="post">
                        <h3 class="post-title"><?php echo htmlspecialchars($user_item['username']); ?></h3>
                        <div class="post-meta">
                            <span class="post-meta-author">Email: <?php echo htmlspecialchars($user_item['email']); ?></span>
                            <span class="post-meta-date">Posts: <?php echo $user_item['post_count']; ?></span>
                            <span class="category-badge">Joined: <?php echo date('Y/m/d H:i', strtotime($user_item['created_at'])); ?></span>
                        </div>
                        <div style="margin-top: 0.5rem;">
                                                            <?php if ($user_item['username'] === 'erfan'): ?>
                                    <span style="background: #667eea; color: white; padding: 0.2rem 0.5rem; border-radius: 3px; font-size: 0.8rem;">Admin <img src="../statics/blue.png" style="width: 12px; height: 12px; margin-left: 3px; vertical-align: middle;"></span>
                                <?php else: ?>
                                    <span style="background: #28a745; color: white; padding: 0.2rem 0.5rem; border-radius: 3px; font-size: 0.8rem;">Regular User</span>
                                <?php endif; ?>
                        </div>
                        <div style="display: flex; gap: 0.5rem; margin-top: 1rem;">
                            <?php if ($user_item['id'] == $user_id): ?>
                                <button class="btn" disabled style="background: #6c757d;">Delete</button>
                            <?php else: ?>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this user? All their posts will also be deleted.')">
                                    <input type="hidden" name="user_id" value="<?php echo $user_item['id']; ?>">
                                    <button type="submit" name="delete_user" class="btn" style="background: #dc3545;">Delete</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
