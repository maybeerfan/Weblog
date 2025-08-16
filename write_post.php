<?php
session_start();
require_once 'connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get categories for dropdown
$categories = $pdo->query("SELECT id, name FROM categories ORDER BY name")->fetchAll();

// Process new post form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    $image_path = null;
    
    // Handle image upload
    if (isset($_FILES['post_image']) && $_FILES['post_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        $file_extension = strtolower(pathinfo($_FILES['post_image']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (in_array($file_extension, $allowed_extensions)) {
            $file_size = $_FILES['post_image']['size'];
            if ($file_size <= 5 * 1024 * 1024) { // 5MB limit
                $unique_filename = uniqid() . '_' . time() . '.' . $file_extension;
                $upload_path = $upload_dir . $unique_filename;
                if (move_uploaded_file($_FILES['post_image']['tmp_name'], $upload_path)) {
                    $image_path = $upload_path;
                } else {
                    $error = "Error uploading image";
                }
            } else {
                $error = "Image size should be less than 5MB";
            }
        } else {
            $error = "Only JPG, PNG, GIF and WebP images are allowed";
        }
    }
    
    if (empty($title) || empty($content)) {
        $error = "Please fill in both title and content";
    } elseif (!isset($error)) {
        $stmt = $pdo->prepare("INSERT INTO posts (title, content, user_id, category_id, image, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        if ($stmt->execute([$title, $content, $_SESSION['user_id'], $category_id, $image_path])) {
            $_SESSION['success_message'] = "Post published successfully!";
            header("Location: index.php");
            exit();
        } else {
            $error = "Error publishing post";
        }
    }
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
    <title>Write Post - Blog</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

</head>
<body>
    <header class="header">
        <nav class="nav">
            <div class="logo">My Blog</div>
            <div class="nav-links">
                <a href="index.php">Home</a>
                <a href="write_post.php" class="active">Write Post</a>
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
        <div class="write-post-container">
            <div class="write-post-header">
                <h1>Write New Post</h1>
                <p>Share your thoughts with the world</p>
            </div>

            <div class="write-post-form">
                <?php if (isset($error)): ?>
                    <div class="error"><?php echo $error; ?></div>
                <?php endif; ?>

                <form method="POST" id="postForm" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="title">Post Title:</label>
                        <input type="text" id="title" name="title" value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>" required maxlength="200">
                        <div class="character-count">
                            <span id="titleCount">0</span>/200 characters
                        </div>
                        <div id="titlePreview" class="title-preview">
                            <h3 id="previewTitle"></h3>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="category_id">Category:</label>
                        <select id="category_id" name="category_id" class="form-select">
                            <option value="">Select a category (optional)</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>" <?php echo (isset($_POST['category_id']) && $_POST['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="post_image">Post Image:</label>
                        <div style="font-size: 0.95rem; color: #888; margin-bottom: 0.5rem;">Recommended size: 800×300px (aspect ratio 8:3)</div>
                        <input type="file" id="post_image" name="post_image" accept="image/*" class="form-file">
                        <div class="image-preview-container" style="display:none;">
                            <div id="imageLoader" class="image-loader-bar" style="display:none;"></div>
                            <img id="imagePreview" src="" alt="Preview" style="display:none;">

                            <button type="button" id="removeImage" class="btn btn-secondary">Remove Image</button>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="content">Post Content:</label>
                        <textarea id="content" name="content" required minlength="10" rows="15"><?php echo isset($_POST['content']) ? htmlspecialchars($_POST['content']) : ''; ?></textarea>
                        <div class="character-count">
                            <span id="contentCount">0</span> characters (minimum 10)
                        </div>
                    </div>
                    
                    <div class="form-actions" style="gap: 1.5rem;">
                        <a href="all_posts.php" class="btn btn-cancel">Cancel</a>
                        <button type="submit" class="btn">Publish Post</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Character counter for title
            $('#title').on('input', function() {
                const length = this.value.length;
                $('#titleCount').text(length);
                
                if (length > 0) {
                    $('#titlePreview').show();
                    $('#previewTitle').text(this.value);
                } else {
                    $('#titlePreview').hide();
                }
            });

            // Character counter for content
            $('#content').on('input', function() {
                $('#contentCount').text(this.value.length);
            });

            // Image upload preview
            $('#post_image').on('change', function() {
                const file = this.files[0];
                if (file) {
                    // Validate file type
                    if (!file.type.startsWith('image/')) {
                        alert('Please select an image file');
                        this.value = '';
                        return;
                    }
                    // Validate file size (max 5MB)
                    if (file.size > 5 * 1024 * 1024) {
                        alert('Image size should be less than 5MB');
                        this.value = '';
                        return;
                    }
                    $('.image-preview-container').show();
                    $('#imagePreview').hide();
                    $('#imageLoader').show();
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        setTimeout(function() {
                            $('#imagePreview').attr('src', e.target.result).show();
                            $('#imageLoader').hide();
                        }, 2000);
                    };
                    reader.readAsDataURL(file);
                }
            });

                // Remove image
                $('#removeImage').on('click', function() {
                    $('#imagePreview').attr('src', '').hide();
                    $('#post_image').val('');
                    $('#imageLoader').hide();
                });

            // Remove image
            $('#removeImage').on('click', function() {
                $('#post_image').val('');
                $('.image-preview-container').hide();
                $('#imagePreview').attr('src', '').hide();
                $('#imageLoader').hide();
            });

            // Initialize counts
            $('#titleCount').text($('#title').val().length);
            $('#contentCount').text($('#content').val().length);
            
            if ($('#title').val().length > 0) {
                $('#titlePreview').show();
                $('#previewTitle').text($('#title').val());
            }
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