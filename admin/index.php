<?php
require_once 'auth.php';

// Get user information
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT username, email FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Site statistics
$stats = [];

// Total posts count
$stmt = $pdo->query("SELECT COUNT(*) as total_posts FROM posts");
$stats['total_posts'] = $stmt->fetch()['total_posts'];

// Total users count
$stmt = $pdo->query("SELECT COUNT(*) as total_users FROM users");
$stats['total_users'] = $stmt->fetch()['total_users'];

// Recent posts
$stmt = $pdo->query("SELECT p.*, u.username FROM posts p JOIN users u ON p.user_id = u.id ORDER BY p.created_at DESC LIMIT 5");
$recent_posts = $stmt->fetchAll();

// Recent users
$stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 5");
$recent_users = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - My Blog</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <header class="header">
        <nav class="nav">
            <div class="logo">Admin Panel</div>
            <div class="nav-links">
                <a href="index.php" class="active">Dashboard</a>
                <a href="posts.php">Manage Posts</a>
                <a href="users.php">Manage Users</a>
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
                <h1>Welcome, <?php echo htmlspecialchars($user['username']); ?>!</h1>
                <p>Admin Dashboard - Manage your blog</p>
            </div>
        </div>

        <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
            <div class="post" style="text-align: center; padding: 2rem;">
                <div style="font-size: 3rem; font-weight: bold; color: #667eea; margin-bottom: 0.5rem;"><?php echo $stats['total_posts']; ?></div>
                <div style="color: #666; font-size: 1.2rem;">Total Posts</div>
            </div>
            <div class="post" style="text-align: center; padding: 2rem;">
                <div style="font-size: 3rem; font-weight: bold; color: #667eea; margin-bottom: 0.5rem;"><?php echo $stats['total_users']; ?></div>
                <div style="color: #666; font-size: 1.2rem;">Total Users</div>
            </div>
        </div>

        <div class="posts">
            <div class="post">
                <h3 style="color: #333; margin-bottom: 1rem; border-bottom: 2px solid #667eea; padding-bottom: 0.5rem;">Recent Posts</h3>
                <?php if (empty($recent_posts)): ?>
                    <p style="text-align: center; color: #666;">No posts published yet.</p>
                <?php else: ?>
                    <?php foreach ($recent_posts as $post): ?>
                        <div style="padding: 1rem; border-bottom: 1px solid #eee; margin-bottom: 1rem;">
                            <div style="font-weight: bold; color: #333; margin-bottom: 0.5rem;"><?php echo htmlspecialchars($post['title']); ?></div>
                            <div style="color: #666; font-size: 0.9rem;">
                                Author: <?php echo htmlspecialchars($post['username']); ?> | 
                                Date: <?php echo date('Y/m/d H:i', strtotime($post['created_at'])); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="post">
                <h3 style="color: #333; margin-bottom: 1rem; border-bottom: 2px solid #667eea; padding-bottom: 0.5rem;">Recent Users</h3>
                <?php if (empty($recent_users)): ?>
                    <p style="text-align: center; color: #666;">No users registered yet.</p>
                <?php else: ?>
                    <?php foreach ($recent_users as $user_item): ?>
                        <div style="padding: 1rem; border-bottom: 1px solid #eee; margin-bottom: 1rem;">
                            <div style="font-weight: bold; color: #333; margin-bottom: 0.5rem;"><?php echo htmlspecialchars($user_item['username']); ?></div>
                            <div style="color: #666; font-size: 0.9rem;">
                                Email: <?php echo htmlspecialchars($user_item['email']); ?> | 
                                Joined: <?php echo date('Y/m/d H:i', strtotime($user_item['created_at'])); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
