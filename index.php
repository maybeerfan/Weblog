<?php
session_start();
require_once 'connect.php';

// Check if user is logged in
$is_logged_in = isset($_SESSION['user_id']);

// Get all posts with pagination
$posts_per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $posts_per_page;

// Get total number of posts
$total_posts = $pdo->query("SELECT COUNT(*) FROM posts")->fetchColumn();
$total_pages = ceil($total_posts / $posts_per_page);

// Get posts for current page
$stmt = $pdo->prepare("
    SELECT p.*, u.username, c.name as category_name 
    FROM posts p 
    JOIN users u ON p.user_id = u.id 
    LEFT JOIN categories c ON p.category_id = c.id
    ORDER BY p.created_at DESC 
    LIMIT " . (int)$posts_per_page . " OFFSET " . (int)$offset
);
$stmt->execute();
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
    <title>My Blog</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

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
                <input type="text" name="search" placeholder="Search posts..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>" aria-label="Search posts" id="searchInput">
                <div class="search-suggest-box" style="display:none;"></div>
            </form>
            <div class="nav-links">
                <a href="index.php" class="active">Home</a>
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
        <div class="welcome-bg-slider">
            <div class="welcome-bg-img"></div>
            <div class="welcome-message">
                <?php if (!$is_logged_in): ?>
                    <h2>Welcome to My Blog!</h2>
                    <p>To write new posts and participate in the blog, please login or register.</p>
                    <div class="welcome-buttons">
                        <a href="login.php" class="btn">Login</a>
                        <a href="register.php" class="btn btn-secondary">Register</a>
                    </div>
                <?php else: ?>
                    <h2>Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
                    <p>Ready to share your thoughts? Write a new post or browse existing ones.</p>
                    <div class="welcome-buttons">
                        <a href="write_post.php" class="btn">Write New Post</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="posts">
            <?php if (empty($posts)): ?>
                <div class="post">
                    <p class="no-posts">No posts have been published yet.</p>
                </div>
            <?php else: ?>
                <?php foreach ($posts as $post): ?>
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
            <?php endif; ?>
        </div>

        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?>">&laquo; Previous</a>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <?php if ($i == $page): ?>
                        <span class="current"><?php echo $i; ?></span>
                    <?php else: ?>
                        <a href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page + 1; ?>">Next &raquo;</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
// لیست تصاویر بکگراند
const bgImages = [
    '‌background/1.jpg',
    '‌background/2.jpg',
    '‌background/3.jpg',
    '‌background/4.jpg',
];
let currentBg = 0;
const bgDiv = document.querySelector('.welcome-bg-img');
const welcomeMsg = document.querySelector('.welcome-message');

function getImageLightness(url, callback) {
    const img = new window.Image();
    img.crossOrigin = 'Anonymous'; // حذف برای رفع مشکل CORS لوکال
    img.src = url;
    img.onload = function() {
        const canvas = document.createElement('canvas');
        canvas.width = 16; canvas.height = 16;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(img, 0, 0, 16, 16);
        let data = ctx.getImageData(0, 0, 16, 16).data;
        let r=0,g=0,b=0,count=0;
        for(let i=0;i<data.length;i+=4){
            r+=data[i];g+=data[i+1];b+=data[i+2];count++;
        }
        r/=count;g/=count;b/=count;
        // Perceived brightness
        let brightness = 0.299*r + 0.587*g + 0.114*b;
        callback(brightness);
    };
    img.onerror = function(){ callback(255); };
}

function setBg(idx) {
    if (!bgDiv) return;
    bgDiv.style.opacity = 0;
    setTimeout(() => {
        const url = bgImages[idx];
        bgDiv.style.backgroundImage = `url('${url}')`;
        bgDiv.style.opacity = 1;
        // Contrast detection
        getImageLightness(url, function(brightness){
            if (!welcomeMsg) return;
            if (brightness < 128) {
                welcomeMsg.style.color = '#fff';
                welcomeMsg.style.textShadow = 'none';
            } else {
                welcomeMsg.style.color = '#222';
                welcomeMsg.style.textShadow = 'none';
            }
        });
    }, 400);
}
setBg(currentBg);
setInterval(() => {
    currentBg = (currentBg + 1) % bgImages.length;
    setBg(currentBg);
}, 5000); // تغییر هر 5 ثانیه

// پیشنهاد زنده سرچ
$(function() {
    var $input = $('#searchInput');
    var $form = $input.closest('form');
    var $suggestBox = $form.find('.search-suggest-box');
    $form.css('position','relative');
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
</html>
