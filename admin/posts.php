<?php
require_once 'auth.php';

// Get user information
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT username, email FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// حذف پست
if (isset($_POST['delete_post'])) {
    $post_id = $_POST['post_id'];
    $stmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
    $stmt->execute([$post_id]);
    header('Location: posts.php?deleted=1');
    exit();
}

// گرفتن همه پست‌ها با اطلاعات نویسنده
$stmt = $pdo->query("SELECT p.*, u.username FROM posts p JOIN users u ON p.user_id = u.id ORDER BY p.created_at DESC");
$posts = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Posts - Admin Panel</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <header class="header">
        <nav class="nav">
            <div class="logo">Admin Panel</div>
            <div class="nav-links">
                <a href="index.php">Dashboard</a>
                <a href="posts.php" class="active">Manage Posts</a>
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
                <h1>Manage Posts</h1>
                <p>Manage all posts on the website</p>
            </div>
        </div>

        <?php if (isset($_GET['deleted'])): ?>
            <div style="background: #d4edda; color: #155724; padding: 1rem; border-radius: 5px; margin-bottom: 1rem;">
                Post deleted successfully!
            </div>
        <?php endif; ?>

        <div class="posts">
            <?php if (empty($posts)): ?>
                <div class="post">
                    <p style="text-align: center; color: #666;">No posts published yet.</p>
                </div>
            <?php else: ?>
                <?php foreach ($posts as $post): ?>
                    <article class="post">
                        <h3 class="post-title"><?php echo htmlspecialchars($post['title']); ?></h3>
                        <div class="post-meta">
                            <span class="post-meta-author">Author: <?php echo htmlspecialchars($post['username']); ?></span>
                            <span class="post-meta-date">Date: <?php echo date('Y/m/d H:i', strtotime($post['created_at'])); ?></span>
                        </div>
                        <div style="display: flex; gap: 0.5rem; margin-top: 1rem;">
                            <a href="../view_post.php?id=<?php echo $post['id']; ?>" style="padding: 0.8rem 1.5rem; font-size: 0.9rem; text-decoration: none; border-radius: 5px; background: #007bff; color: white; border: none; cursor: pointer; display: inline-block; text-align: center; min-width: 80px;">View</a>
                            <a href="../edit_post.php?id=<?php echo $post['id']; ?>" style="padding: 0.8rem 1.5rem; font-size: 0.9rem; text-decoration: none; border-radius: 5px; background: #28a745; color: white; border: none; cursor: pointer; display: inline-block; text-align: center; min-width: 80px;">Edit</a>
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this post?')">
                                <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                <button type="submit" name="delete_post" style="padding: 0.8rem 1.5rem; font-size: 0.9rem; border-radius: 5px; background: #dc3545; color: white; border: none; cursor: pointer; min-width: 80px;">Delete</button>
                            </form>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
