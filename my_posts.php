<?php
session_start();
require_once 'connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Delete post
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $post_id = (int)$_GET['delete'];
    
    // Check if post belongs to current user
    $stmt = $pdo->prepare("SELECT id FROM posts WHERE id = ? AND user_id = ?");
    $stmt->execute([$post_id, $_SESSION['user_id']]);
    
    if ($stmt->rowCount() > 0) {
        $delete_stmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
        if ($delete_stmt->execute([$post_id])) {
            $_SESSION['success_message'] = "Post deleted successfully!";
        } else {
            $_SESSION['error_message'] = "Error deleting post";
        }
    } else {
        $_SESSION['error_message'] = "Post not found or access denied";
    }
    
    header("Location: my_posts.php");
    exit();
}

// Get user's posts

$stmt = $pdo->prepare("
    SELECT p.*, c.name as category_name, u.username
    FROM posts p
    LEFT JOIN categories c ON p.category_id = c.id
    LEFT JOIN users u ON p.user_id = u.id
    WHERE p.user_id = ?
    ORDER BY p.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$posts = $stmt->fetchAll();

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
    <title>My Posts - Blog</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

</head>
<body>
    <header class="header">
        <nav class="nav">
            <div class="logo">My Blog</div>
            <div class="nav-links">
                <a href="index.php">Home</a>
                <a href="write_post.php">Write Post</a>
                <a href="my_posts.php" class="active">My Posts</a>
                <?php if (isset($_SESSION['username'])): ?>
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
        <div class="my-posts-header">
            <h1>My Posts</h1>
            <p>Manage your published posts</p>
        </div>

        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="success"><?php echo $_SESSION['success_message']; ?></div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="error"><?php echo $_SESSION['error_message']; ?></div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <div class="post-stats">
            <h3>Post Statistics</h3>
            <p>You have published <strong><?php echo count($posts); ?></strong> post<?php echo count($posts) != 1 ? 's' : ''; ?></p>
        </div>

        <div class="posts">
            <?php if (empty($posts)): ?>
                <div class="no-posts">
                    <p>You haven't published any posts yet.</p>
                    <a href="write_post.php" class="btn">Write Your First Post</a>
                </div>
            <?php else: ?>
                <?php foreach ($posts as $post): ?>
                    <article class="post">
                        <div class="post-image">
                            <?php if (!empty($post['image'])): ?>
                                <img src="<?php echo htmlspecialchars($post['image']); ?>" alt="Post Image" class="post-thumbnail">
                            <?php else: ?>
                                <img src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzAwIiBoZWlnaHQ9IjIwMCIgdmlld0JveD0iMCAwIDMwMCAyMDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIzMDAiIGhlaWdodD0iMjAwIiBmaWxsPSIjRjVGNUY1Ii8+CjxwYXRoIGQ9Ik0xNTAgMTAwQzE2Ni41NTkgMTAwIDE4MCA4Ni41NTg2IDE4MCA3MEMxODAgNTMuNDQxNCAxNjYuNTU5IDQwIDE1MCA0MEMxMzMuNDQxIDQwIDEyMCA1My40NDE0IDEyMCA3MEMxMjAgODYuNTU4NiAxMzMuNDQxIDEwMCAxNTAgMTAwWiIgZmlsbD0iI0M5QzlDOSIvPgo8cGF0aCBkPSJNMTUwIDE4MEMxNjYuNTU5IDE4MCAxODAgMTY2LjU1OSAxODAgMTUwQzE4MCAxMzMuNDQxIDE2Ni41NTkgMTIwIDE1MCAxMjBDMTMzLjQ0MSAxMjAgMTIwIDEzMy40NDEgMTIwIDE1MEMxMjAgMTY2LjU1OSAxMzMuNDQxIDE4MCAxNTAgMTgwWiIgZmlsbD0iI0M5QzlDOSIvPgo8L3N2Zz4K" alt="No Image" class="post-thumbnail no-image">
                            <?php endif; ?>
                        </div>
                        <h3 class="post-title"><?php echo htmlspecialchars($post['title']); ?></h3>
                        <div class="post-meta">
                            <?php
                            $author_img = '';
                            if (!empty($post['user_id'])) {
                                $stmtA = $pdo->prepare("SELECT profile_image FROM users WHERE id = ?");
                                $stmtA->execute([$post['user_id']]);
                                $author_img = $stmtA->fetchColumn();
                            }
                            ?>
                            <span class="post-meta-author">
                                <img src="<?php echo !empty($author_img) ? htmlspecialchars($author_img) : 'https://cdn-icons-png.flaticon.com/512/149/149071.png'; ?>" alt="Author" class="nav-user-avatar" style="margin-right:7px;">
                                <?php echo isset($post['username']) ? htmlspecialchars($post['username']) : '<span style="color:#dc3545;font-size:0.95em">Unknown</span>'; ?>
                                <?php if (isset($post['username']) && $post['username'] === 'erfan'): ?>
                                    <img src="statics/blue.png" style="width: 14px; height: 14px; margin-left: 3px; vertical-align: text-top;">
                                <?php endif; ?>
                            </span>
                            <span>Published: <?php echo date('F j, Y \a\t g:i A', strtotime($post['created_at'])); ?></span>
                            <span>Status: Published</span>
                            <?php if (!empty($post['category_name'])): ?>
                                <span class="category-badge">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="vertical-align:middle;margin-right:3px;"><rect x="4" y="4" width="16" height="16" rx="4"/></svg>
                                    <?php echo htmlspecialchars($post['category_name']); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="post-content">
                            <?php 
                            $content = htmlspecialchars($post['content']);
                            // Show first 300 characters with ellipsis if longer
                            if (strlen($content) > 300) {
                                echo nl2br(substr($content, 0, 300)) . '...';
                            } else {
                                echo nl2br($content);
                            }
                            ?>
                        </div>
                        <div class="post-actions">
                            <a href="edit_post.php?id=<?php echo $post['id']; ?>" class="btn btn-edit btn-small">Edit</a>
                            <a href="?delete=<?php echo $post['id']; ?>" class="btn btn-delete btn-small" 
                               onclick="return confirm('Are you sure you want to delete this post? This action cannot be undone.')">Delete</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

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
</body>
</html>