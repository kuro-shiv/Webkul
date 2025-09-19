<?php
require_once "includes/db.php";
require_once "includes/auth.php";
checkLogin();

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION["user_id"]]);
$user = $stmt->fetch();

// Handle new post
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["new_post"])) {
    $content = trim($_POST["content"]);
    $postImage = null;

    if (!empty($_FILES["post_image"]["name"])) {
        $targetDir = "assets/uploads/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        $filename = time() . "_" . basename($_FILES["post_image"]["name"]);
        $targetFile = $targetDir . $filename;
        $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

        if (in_array($fileType, ["jpg", "jpeg", "png", "gif"])) {
            if (move_uploaded_file($_FILES["post_image"]["tmp_name"], $targetFile)) {
                $postImage = $filename;
            }
        }
    }

    $stmt = $pdo->prepare("INSERT INTO posts (user_id, content, image) VALUES (?, ?, ?)");
    $stmt->execute([$_SESSION["user_id"], $content, $postImage]);
    header("Location: profile.php");
    exit;
}

// Handle like/dislike
if (isset($_GET["like"])) {
    $stmt = $pdo->prepare("UPDATE posts SET likes = likes + 1 WHERE id = ?");
    $stmt->execute([$_GET["like"]]);
    header("Location: profile.php");
    exit;
}
if (isset($_GET["dislike"])) {
    $stmt = $pdo->prepare("UPDATE posts SET dislikes = dislikes + 1 WHERE id = ?");
    $stmt->execute([$_GET["dislike"]]);
    header("Location: profile.php");
    exit;
}

// Handle comment
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["comment"])) {
    $postId = $_POST["post_id"];
    $comment = trim($_POST["comment"]);
    $stmt = $pdo->prepare("INSERT INTO comments (post_id, user_id, comment) VALUES (?, ?, ?)");
    $stmt->execute([$postId, $_SESSION["user_id"], $comment]);
    header("Location: profile.php");
    exit;
}

// Fetch posts with user info
$posts = $pdo->query("SELECT posts.*, users.full_name, users.profile_pic 
                      FROM posts 
                      JOIN users ON posts.user_id = users.id 
                      ORDER BY posts.created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Profile</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f9;
            margin: 0;
            display: flex;
        }
        .sidebar {
            width: 250px;
            background: #fff;
            padding: 20px;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
            height: 100vh;
        }
        .sidebar img {
            border-radius: 50%;
            width: 80px;
            height: 80px;
        }
        .sidebar h3 {
            margin: 10px 0 5px;
        }
        .content {
            flex: 1;
            padding: 20px;
        }
        .post-box, .feed-post {
            background: #fff;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 10px;
            box-shadow: 0 0 6px rgba(0,0,0,0.1);
        }
        textarea {
            width: 100%;
            padding: 10px;
            border-radius: 6px;
            border: 1px solid #ddd;
        }
        button {
            background: #007bff;
            color: white;
            border: none;
            padding: 8px 15px;
            margin-top: 10px;
            border-radius: 6px;
            cursor: pointer;
        }
        button:hover { background: #0056b3; }
        .post-header {
            display: flex;
            align-items: center;
        }
        .post-header img {
            border-radius: 50%;
            margin-right: 10px;
        }
        .comment-box {
            margin-left: 40px;
            padding: 5px;
        }
        .post-actions a {
            margin-right: 10px;
            text-decoration: none;
            color: #007bff;
        }
        .post-actions a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <img src="assets/uploads/<?php echo $user["profile_pic"] ?: 'default.png'; ?>" alt="Profile">
        <h3><?php echo htmlspecialchars($user["full_name"]); ?></h3>
        <p><?php echo htmlspecialchars($user["email"]); ?></p>
        <p><a href="profile_view.php?id=<?php echo $user["id"]; ?>">Show Profile</a></p>
        <p><a href="login.php?logout=1">Logout</a></p>
    </div>

    <!-- Main Content -->
    <div class="content">
        <!-- Add Post -->
        <div class="post-box">
            <form method="post" enctype="multipart/form-data">
                <textarea name="content" placeholder="What's on your mind?" required></textarea>
                <input type="file" name="post_image"><br>
                <button type="submit" name="new_post">Post</button>
            </form>
        </div>

        <!-- Feed Posts -->
        <?php foreach ($posts as $post): ?>
            <div class="feed-post">
                <div class="post-header">
                    <img src="assets/uploads/<?php echo $post["profile_pic"] ?: 'default.png'; ?>" width="40" height="40">
                    <strong><a href="profile_view.php?id=<?php echo $post['user_id']; ?>">
                        <?php echo htmlspecialchars($post["full_name"]); ?>
                    </a></strong>
                    <small style="margin-left:10px; color:gray;"><?php echo $post["created_at"]; ?></small>
                </div>
                <p><?php echo nl2br(htmlspecialchars($post["content"])); ?></p>
                <?php if ($post["image"]): ?>
                    <img src="assets/uploads/<?php echo $post["image"]; ?>" width="100%">
                <?php endif; ?>
                <div class="post-actions">
                    <a href="profile.php?like=<?php echo $post["id"]; ?>">👍 Like (<?php echo $post["likes"]; ?>)</a>
                    <a href="profile.php?dislike=<?php echo $post["id"]; ?>">👎 Dislike (<?php echo $post["dislikes"]; ?>)</a>
                </div>

                <!-- Comment Form -->
                <form method="post" class="comment-box">
                    <input type="hidden" name="post_id" value="<?php echo $post["id"]; ?>">
                    <input type="text" name="comment" placeholder="Write a comment..." required>
                    <button type="submit">Comment</button>
                </form>

                <!-- Show Comments -->
                <?php
                $stmt = $pdo->prepare("SELECT comments.*, users.full_name FROM comments 
                                       JOIN users ON comments.user_id = users.id 
                                       WHERE comments.post_id = ? 
                                       ORDER BY comments.created_at ASC");
                $stmt->execute([$post["id"]]);
                $comments = $stmt->fetchAll();
                foreach ($comments as $c): ?>
                    <div class="comment-box">
                        <strong><a href="profile_view.php?id=<?php echo $c['user_id']; ?>">
                            <?php echo htmlspecialchars($c["full_name"]); ?>
                        </a>:</strong>
                        <?php echo htmlspecialchars($c["comment"]); ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
