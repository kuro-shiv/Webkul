<?php
require_once "includes/db.php";
require_once "includes/auth.php";
checkLogin();

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION["user_id"]]);
$user = $stmt->fetch();

if (isset($_GET["logout"])) {
    session_destroy();
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Profile</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <h2>Welcome, <?php echo htmlspecialchars($user["full_name"]); ?>!</h2>
    <p>Email: <?php echo htmlspecialchars($user["email"]); ?></p>
    <p>Age: <?php echo htmlspecialchars($user["age"]); ?></p>
    <p>
        <img src="assets/uploads/<?php echo $user["profile_pic"] ?: 'default.png'; ?>" width="120">
    </p>
    <p><a href="profile.php?logout=1">Logout</a></p>
</body>
</html>
