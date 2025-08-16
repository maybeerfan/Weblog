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

// Get post ID from URL
$post_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$post_id) {
    header("Location: my_posts.php");
    exit();
}

// Get post data
$stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ? AND user_id = ?");
$stmt->execute([$post_id, $_SESSION['user_id']]);
$post = $stmt->fetch();

if (!$post) {
    $_SESSION['error_message'] = "Post not found or access denied";
    header("Location: my_posts.php");
    exit();
}

// Process edit form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    $image_path = $post['image']; // Keep existing image by default
    
    // Handle new image upload
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
                    // Delete old image if exists
                    if (!empty($post['image']) && file_exists($post['image'])) {
                        unlink($post['image']);
                    }
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
        $update_stmt = $pdo->prepare("UPDATE posts SET title = ?, content = ?, category_id = ?, image = ? WHERE id = ? AND user_id = ?");
        if ($update_stmt->execute([$title, $content, $category_id, $image_path, $post_id, $_SESSION['user_id']])) {
            $_SESSION['success_message'] = "Post updated successfully!";
            header("Location: my_posts.php");
            exit();
        } else {
            $error = "Error updating post";
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
    <title>Edit Post - Blog</title>
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
        <div class="edit-post-container">
            <div class="edit-post-header">
                <h1>Edit Post</h1>
                <p>Update your post content</p>
            </div>

            <div class="edit-post-form">
                <?php if (isset($error)): ?>
                    <div class="error"><?php echo $error; ?></div>
                <?php endif; ?>

                <form method="POST" id="editForm" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="title">Post Title:</label>
                        <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($post['title']); ?>" required maxlength="200">
                        <div class="character-count">
                            <span id="titleCount"><?php echo strlen($post['title']); ?></span>/200 characters
                        </div>
                        <div id="titlePreview" class="title-preview">
                            <h3 id="previewTitle"><?php echo htmlspecialchars($post['title']); ?></h3>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="category_id">Category:</label>
                        <select id="category_id" name="category_id" class="form-select">
                            <option value="">No category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>" <?php echo ($post['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="post_image">Post Image:</label>
                        <div style="font-size: 0.95rem; color: #888; margin-bottom: 0.5rem;">Recommended size: 800×300px (aspect ratio 8:3)</div>
                        <input type="file" id="post_image" name="post_image" accept="image/*" class="form-file">
                        <?php if (!empty($post['image'])): ?>
                            <div class="current-image" style="position:relative;">
                                <p><strong>Current Image:</strong></p>
                                <img id="currentImageTag" src="<?php echo htmlspecialchars($post['image']); ?>" alt="Current Image" style="width:100%;height:300px;object-fit:cover;border-radius:12px;">
                                <span id="removeCurrentImageX" class="remove-image-x" title="Remove image">&times;</span>
                            </div>
                        <?php endif; ?>
                        <div class="image-preview-container" style="display:none;">
                            <div id="imageLoader" class="image-loader-bar" style="display:none;"></div>
                            <img id="imagePreview" src="" alt="Preview" style="display:none;width:100%;max-width:100%;height:300px;object-fit:cover;border-radius:12px;">
                            <button type="button" id="removeImage" class="btn btn-secondary">Remove Image</button>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="content">Post Content:</label>
                        <textarea id="content" name="content" required minlength="10" rows="15"><?php echo htmlspecialchars($post['content']); ?></textarea>
                        <div class="character-count">
                            <span id="contentCount"><?php echo strlen($post['content']); ?></span> characters (minimum 10)
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <a href="my_posts.php" class="btn btn-cancel">Cancel</a>
                        <button type="submit" class="btn">Update Post</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Character counter for title
            $('#title').on('input', function() {
                $('#titleCount').text(this.value.length);
                $('#previewTitle').text(this.value);
            });

            // Character counter for content
            $('#content').on('input', function() {
                $('#contentCount').text(this.value.length);
            });

            // Image upload preview with loader
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
                $('#post_image').val('');
                $('.image-preview-container').hide();
                $('#imagePreview').attr('src', '').hide();
                $('#imageLoader').hide();
            });

            // Remove current image (X on current image)
            $('#removeCurrentImageX').on('click', function() {
                $('.current-image').hide();
                // Add a hidden input to signal removal on submit
                if ($('#remove_current_image').length === 0) {
                    $('<input>').attr({type:'hidden',id:'remove_current_image',name:'remove_current_image',value:'1'}).appendTo('#editForm');
                }
            });
        });
    </script>
</body>
</html> 