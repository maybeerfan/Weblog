<?php
session_start();
require_once 'connect.php';

// Check if user is logged in
$is_logged_in = isset($_SESSION['user_id']);

// Get post ID from URL
$post_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$post_id) {
    header("Location: index.php");
    exit();
}

// Get post data with category and author
$stmt = $pdo->prepare("
    SELECT p.*, u.username, c.name as category_name 
    FROM posts p 
    JOIN users u ON p.user_id = u.id 
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.id = ?
");
$stmt->execute([$post_id]);
$post = $stmt->fetch();

if (!$post) {
    header("Location: index.php");
    exit();
}

// User logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($post['title']); ?> - Blog</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
    $(function() {
        // منوی کشویی یوزر: باز و بسته شدن با کلیک
        $(document).on('click', '.nav-user-name', function(e) {
            var $dropdown = $(this).closest('.nav-user-dropdown');
            $('.nav-user-dropdown').not($dropdown).removeClass('open');
            $dropdown.toggleClass('open');
            e.stopPropagation();
        });
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.nav-user-dropdown').length) {
                $('.nav-user-dropdown').removeClass('open');
            }
        });
    });
    </script>
</head>
<body>
    <header class="header">
        <nav class="nav">
            <div class="logo">My Blog</div>
            <div class="nav-links">
                <a href="index.php">Home</a>
                <a href="write_post.php">Write Post</a>
                <a href="my_posts.php">My Posts</a>
                <?php if ($is_logged_in): ?>
                    <?php if (basename($_SERVER['PHP_SELF']) !== 'profile.php'): ?>
                        <div class="nav-user-dropdown">
                            <?php
                            $user_info = null;
                            if (isset($_SESSION['user_id'])) {
                                $stmtUser = $pdo->prepare("SELECT username, profile_image FROM users WHERE id = ?");
                                $stmtUser->execute([$_SESSION['user_id']]);
                                $user_info = $stmtUser->fetch();
                            }
                            ?>
                            <span class="nav-user-name">
                                <img src="<?php echo !empty($user_info['profile_image']) ? htmlspecialchars($user_info['profile_image']) : 'https://cdn-icons-png.flaticon.com/512/149/149071.png'; ?>" alt="Profile" class="nav-user-avatar">
                                <?php echo htmlspecialchars($user_info['username'] ?? $_SESSION['username']); ?>
                                <?php if ($user_info['username'] === 'erfan'): ?>
                                    <img src="statics/blue.png" style="width: 16px; height: 16px; margin-left: 5px; vertical-align: middle;">
                                <?php endif; ?>
                            </span>
                            <div class="nav-user-menu">
                                <a href="profile.php" class="nav-user-profile">User Profile</a>
                                <a href="settings.php" class="nav-user-settings">Settings</a>
                                <?php if ($user_info['username'] === 'erfan'): ?>
                                    <a href="admin/" class="nav-user-admin">Admin Panel</a>
                                <?php endif; ?>
                                <a href="logout.php" class="nav-user-logout">Logout</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <span class="nav-user-name">Hello <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="login.php">Login</a>
                    <a href="register.php">Register</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>

    <div class="container">
        <article class="post-full">
            <div class="post-header">
                <h1><?php echo htmlspecialchars($post['title']); ?></h1>
                <div class="post-meta">
                    <span class="post-meta-author">
                        <?php
                        $author_img = '';
                        if (!empty($post['user_id'])) {
                            $stmtA = $pdo->prepare("SELECT profile_image FROM users WHERE id = ?");
                            $stmtA->execute([$post['user_id']]);
                            $author_img = $stmtA->fetchColumn();
                        }
                        ?>
                        <img src="<?php echo !empty($author_img) ? htmlspecialchars($author_img) : 'https://cdn-icons-png.flaticon.com/512/149/149071.png'; ?>" alt="Author" class="nav-user-avatar" style="margin-right:7px;">
                        <?php echo htmlspecialchars($post['username']); ?>
                        <?php if ($post['username'] === 'erfan'): ?>
                            <img src="statics/blue.png" style="width: 14px; height: 14px; margin-left: 3px; vertical-align: text-top;">
                        <?php endif; ?>
                    </span>
                    <span class="post-meta-date">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="vertical-align:middle;margin-right:4px;"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                        <?php echo date('F j, Y \a\t g:i A', strtotime($post['created_at'])); ?>
                    </span>
                    <?php if (!empty($post['category_name'])): ?>
                        <span class="category-badge">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="vertical-align:middle;margin-right:3px;"><rect x="4" y="4" width="16" height="16" rx="4"/></svg>
                            <?php echo htmlspecialchars($post['category_name']); ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (!empty($post['image'])): ?>
                <div class="post-image-full">
                    <img src="<?php echo htmlspecialchars($post['image']); ?>" alt="Post Image">
                </div>
            <?php else: ?>
                <div class="no-image-placeholder">
                    <p>📷 No image for this post</p>
                </div>
            <?php endif; ?>

            <div class="post-content-full">
                <?php echo nl2br(htmlspecialchars($post['content'])); ?>
            </div>

            <div class="back-button">
                <a href="index.php" class="btn">← Back to Home</a>
            </div>
        </article>
    </div>
</body>
</html>