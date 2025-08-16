<?php
session_start();
require_once 'connect.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
$user_id = $_SESSION['user_id'];
// اطلاعات تکمیلی
$stmt = $pdo->prepare("SELECT username, email, created_at, first_name, last_name, nickname, profile_image FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
if (!$user) {
    echo 'User not found.';
    exit();
}

// ذخیره اطلاعات تکمیلی
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $nickname = trim($_POST['nickname'] ?? '');
    $profile_image = $user['profile_image'];
    // آپلود عکس پروفایل
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        $file_extension = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (in_array($file_extension, $allowed_extensions)) {
            $file_size = $_FILES['profile_image']['size'];
            if ($file_size <= 3 * 1024 * 1024) {
                $unique_filename = uniqid() . '_' . time() . '.' . $file_extension;
                $upload_path = $upload_dir . $unique_filename;
                if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $upload_path)) {
                    // حذف عکس قبلی
                    if (!empty($user['profile_image']) && file_exists($user['profile_image'])) {
                        unlink($user['profile_image']);
                    }
                    $profile_image = $upload_path;
                }
            }
        }
    }
    $update = $pdo->prepare("UPDATE users SET first_name=?, last_name=?, nickname=?, profile_image=? WHERE id=?");
    $update->execute([$first_name, $last_name, $nickname, $profile_image, $user_id]);
    header('Location: profile.php');
    exit();
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile - My Blog</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="header">
        <nav class="nav">
            <div class="logo">My Blog</div>
            <div class="nav-links">
                <a href="index.php">Home</a>
                <a href="write_post.php">Write Post</a>
                <a href="my_posts.php">My Posts</a>
                <span>Hello <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
                <a href="logout.php">Logout</a>
            </div>
        </nav>
    </header>
    <div class="container">
        <h2>User Profile</h2>
        <form class="profile-form" method="POST" enctype="multipart/form-data">
            <div class="profile-row">
                <div class="profile-avatar-box">
                    <label for="profile_image" class="profile-avatar-label">
                        <img src="<?php echo !empty($user['profile_image']) ? htmlspecialchars($user['profile_image']) : 'https://cdn-icons-png.flaticon.com/512/149/149071.png'; ?>" alt="Profile Image" class="profile-avatar-img">
                    </label>
                    <input type="file" id="profile_image" name="profile_image" accept="image/*" style="display:none;">
                    <label for="profile_image" class="profile-avatar-edit-icon" title="Edit profile image">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#292929" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19.5 3 21l1.5-4L16.5 3.5z"/></svg>
                    </label>
                </div>
                <div class="profile-fields">
                    <div class="profile-fields-row">
                        <div class="form-group">
                            <label for="first_name">First Name</label>
                            <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['first_name'] ?? ''); ?>" maxlength="50">
                        </div>
                        <div class="form-group">
                            <label for="last_name">Last Name</label>
                            <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user['last_name'] ?? ''); ?>" maxlength="50">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="nickname">Nickname</label>
                        <input type="text" id="nickname" name="nickname" value="<?php echo htmlspecialchars($user['nickname'] ?? ''); ?>" maxlength="50">
                    </div>
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                    </div>
                    <div class="form-group">
                        <label>Member since</label>
                        <input type="text" value="<?php echo date('F j, Y', strtotime($user['created_at'])); ?>" disabled>
                    </div>
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn">Save Changes</button>
            </div>
        </form>
        <script>
        // نمایش پیش‌نمایش عکس پروفایل
        document.getElementById('profile_image').addEventListener('change', function(e) {
            const file = this.files[0];
            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(ev) {
                    document.querySelector('.profile-avatar-img').src = ev.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
        </script>
    </div>
</body>
</html>
