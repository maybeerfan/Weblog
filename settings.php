<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - My Blog</title>
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
                <?php if (isset($_SESSION['username'])): ?>
                    <?php if (basename($_SERVER['PHP_SELF']) !== 'profile.php'): ?>
                        <div class="nav-user-dropdown">
                            <?php
                            $user_info = null;
                            if (isset($_SESSION['user_id'])) {
                                require_once 'connect.php';
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
        <h2>Settings</h2>
        <p>Here you can manage your account settings. (Coming soon)</p>
    </div>
</body>
</html>
