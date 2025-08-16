<?php
require_once 'connect.php';
$q = isset($_GET['q']) ? trim($_GET['q']) : '';
if ($q === '' || mb_strlen($q) < 2) exit;
$stmt = $pdo->prepare("SELECT id, title FROM posts WHERE title LIKE ? ORDER BY id DESC LIMIT 8");
$stmt->execute(['%' . $q . '%']);
$rows = $stmt->fetchAll();
if (!$rows) {
    echo '<div class="search-suggest-empty">No suggestions</div>';
    exit;
}
echo '<ul class="search-suggest-list">';
foreach ($rows as $row) {
    echo '<li><a href="view_post.php?id=' . $row['id'] . '">' . htmlspecialchars($row['title']) . '</a></li>';
}
echo '</ul>';
