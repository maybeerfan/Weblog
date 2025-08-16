<?php
session_start();
require_once 'connect.php';

// جستجو و دریافت پست‌ها با اطلاعات کامل
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$results = [];
if ($search !== '') {
    $stmt = $pdo->prepare("
        SELECT p.*, u.username, c.name as category_name 
        FROM posts p 
        JOIN users u ON p.user_id = u.id 
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.title LIKE ?
        ORDER BY p.created_at DESC
        LIMIT 30
    ");
    $stmt->execute(['%' . $search . '%']);
    $results = $stmt->fetchAll();
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results - My Blog</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="header">
        <nav class="nav">
            <div class="logo">My Blog</div>
            <form class="nav-search" method="GET" action="search.php" autocomplete="off">
                <span class="nav-search-icon">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="9" cy="9" r="7" stroke="#888" stroke-width="2"/>
                        <line x1="14.2" y1="14.2" x2="18" y2="18" stroke="#888" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </span>
                <input type="text" name="search" placeholder="Search posts..." value="<?php echo htmlspecialchars($search); ?>" aria-label="Search posts" id="searchInput">
            </form>
            <div class="nav-links">
                <a href="index.php">Home</a>
                <a href="write_post.php">Write Post</a>
                <a href="my_posts.php">My Posts</a>
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
        <h2>Search Results for "<?php echo htmlspecialchars($search); ?>"</h2>
        <?php if ($search === ''): ?>
            <p>Type something in the search bar above.</p>
        <?php elseif (empty($results)): ?>
            <p>No posts found.</p>
        <?php else: ?>
            <div class="posts">
                <?php foreach ($results as $post): ?>
                    <article class="post clickable-post" onclick="window.location.href='view_post.php?id=<?php echo $post['id']; ?>'">
                        <div class="post-image">
                            <?php if (!empty($post['image'])): ?>
                                <img src="<?php echo htmlspecialchars($post['image']); ?>" alt="Post Image" class="post-thumbnail">
                            <?php else: ?>
                                <img src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzAwIiBoZWlnaHQ9IjIwMCIgdmlld0JveD0iMCAwIDMwMCAyMDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIzMDAiIGhlaWdodD0iMjAwIiBmaWxsPSIjRjVGNUY1Ii8+CjxwYXRoIGQ9Ik0xNTAgMTAwQzE2Ni41NTkgMTAwIDE4MCA4Ni41NTg2IDE4MCA3MEMxODAgNTMuNDQxNCAxNjYuNTU5IDQwIDE1MCA0MEMxMzMuNDQxIDQwIDEyMCA1My40NDE0IDEyMCA3MEMxMjAgODYuNTU4NiAxMzMuNDQxIDEwMCAxNTAgMTAwWiIgZmlsbD0iI0M5QzlDOSIvPgo8cGF0aCBkPSJNMTUwIDE4MEMxNjYuNTU5IDE4MCAxODAgMTY2LjU1OSAxODAgMTUwQzE4MCAxMzMuNDQxIDE2Ni41NTkgMTIwIDE1MCAxMjBDMTMzLjQ0MSAxMjAgMTIwIDEzMy40NDEgMTIwIDE1MEMxMjAgMTY2LjU1OSAxMzMuNDQxIDE4MCAxNTAgMTgwWiIgZmlsbD0iI0M5QzlDOSIvPgo8L3N2Zz4K" alt="No Image" class="post-thumbnail no-image">
                            <?php endif; ?>
                        </div>
                        <h3 class="post-title"><?php echo htmlspecialchars($post['title']); ?></h3>
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
                        <div class="post-content">
                            <?php 
                            $content = htmlspecialchars($post['content']);
                            // Show first 200 characters with ellipsis if longer
                            if (strlen($content) > 200) {
                                echo nl2br(substr($content, 0, 200)) . '...';
                            } else {
                                echo nl2br($content);
                            }
                            ?>
                        </div>
                        <div class="read-more">
                            <span class="read-more-text">Click to read full post →</span>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
    // Live search suggestions
    $(function() {
        var $input = $('#searchInput');
        var $form = $input.closest('form');
        var $suggestBox = $('<div class="search-suggest-box"></div>').css({position:'absolute',zIndex:1000,display:'none'});
        $form.css('position','relative').append($suggestBox);
        $input.on('input', function() {
            var val = this.value.trim();
            if(val.length < 2) { $suggestBox.hide(); return; }
            $.get('search_suggest.php', {q: val}, function(data) {
                $suggestBox.html(data).show();
            });
        });
        $input.on('blur', function() { setTimeout(function(){$suggestBox.hide();}, 200); });
        $suggestBox.on('mousedown', 'a', function(e) {
            window.location = $(this).attr('href');
        });
    });
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
