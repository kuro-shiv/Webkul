<?php
require_once "includes/db.php";
require_once "includes/auth.php";
checkLogin();

// Get user ID from URL
if (!isset($_GET["id"])) {
    die("No profile selected!");
}
$userId = intval($_GET["id"]);

// Fetch user info
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$profileUser = $stmt->fetch();

if (!$profileUser) {
    die("User not found!");
}

// Fetch user's posts
$stmt = $pdo->prepare("SELECT posts.*, users.full_name, users.profile_pic 
                       FROM posts 
                       JOIN users ON posts.user_id = users.id 
                       WHERE posts.user_id = ? 
                       ORDER BY posts.created_at DESC");
$stmt->execute([$userId]);
$posts = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo htmlspecialchars($profileUser["full_name"]); ?> - Profile</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f9;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: auto;
        }
        .profile-header {
            background: #fff;
            padding: 25px;
            margin-bottom: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        .profile-header img {
            border-radius: 50%;
            width: 120px;
            height: 120px;
            object-fit: cover;
            border: 3px solid #007bff;
            margin-bottom: 10px;
        }
        .profile-header h2 {
            margin: 10px 0;
            color: #333;
        }
        .post-box {
            background: #fff;
            padding: 20px;
            margin-bottom: 15px;
            border-radius: 10px;
            box-shadow: 0 3px 8px rgba(0,0,0,0.08);
        }
        .post-box img {
            max-width: 100%;
            border-radius: 6px;
            margin-top: 10px;
        }
        .post-meta {
            font-size: 13px;
            color: #777;
            margin-top: 8px;
        }
        a.back-link {
            text-decoration: none;
            color: #007bff;
            font-size: 14px;
            display: inline-block;
            margin-bottom: 15px;
        }
        a.back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="container">
    <a href="profile.php" class="back-link">⬅ Back to My Profile</a>

    <!-- Profile Info -->
    <div class="profile-header">
        <img src="assets/uploads/<?php echo $profileUser["profile_pic"] ?: 'default.png'; ?>" alt="Profile Picture">
        <h2><?php echo htmlspecialchars($profileUser["full_name"]); ?></h2>
        <p>Email: <?php echo htmlspecialchars($profileUser["email"]); ?></p>
        <p>Age: <?php echo htmlspecialchars($profileUser["age"]); ?></p>
    </div>

    <!-- User's Posts -->
    <h3><?php echo htmlspecialchars($profileUser["full_name"]); ?>'s Posts</h3>
    <?php if (count($posts) === 0): ?>
        <p>No posts yet.</p>
    <?php endif; ?>

    <?php foreach ($posts as $post): ?>
        <div class="post-box">
            <p><?php echo nl2br(htmlspecialchars($post["content"])); ?></p>
            <?php if ($post["image"]): ?>
                <img src="assets/uploads/<?php echo $post["image"]; ?>" alt="Post Image">
            <?php endif; ?>
            <div class="post-meta">
                Posted on <?php echo $post["created_at"]; ?><br>
                👍 <?php echo $post["likes"]; ?> | 👎 <?php echo $post["dislikes"]; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>
</body>
</html>
