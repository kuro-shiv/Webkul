<?php
require_once "includes/db.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["full_name"]);
    $email = trim($_POST["email"]);
    $password = password_hash($_POST["password"], PASSWORD_BCRYPT);
    $age = intval($_POST["age"]);

    // Profile picture upload
    $profilePic = null;
    if (!empty($_FILES["profile_pic"]["name"])) {
        $targetDir = "assets/uploads/";
        $filename = time() . "_" . basename($_FILES["profile_pic"]["name"]);
        $targetFile = $targetDir . $filename;
        $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

        if (in_array($fileType, ["jpg", "jpeg", "png", "gif"])) {
            if (move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $targetFile)) {
                $profilePic = $filename;
            }
        }
    }

    // Insert into DB
    $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password, age, profile_pic) VALUES (?, ?, ?, ?, ?)");
    try {
        $stmt->execute([$name, $email, $password, $age, $profilePic]);
        header("Location: login.php?success=1");
        exit;
    } catch (PDOException $e) {
        $error = "Email already exists!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Signup</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <h2>Signup</h2>
    <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
    <form method="post" enctype="multipart/form-data">
        <input type="text" name="full_name" placeholder="Full Name" required><br>
        <input type="email" name="email" placeholder="Email" required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <input type="number" name="age" placeholder="Age" required><br>
        <input type="file" name="profile_pic"><br>
        <button type="submit">Signup</button>
    </form>
    <p>Already registered? <a href="login.php">Login</a></p>
</body>
</html>
