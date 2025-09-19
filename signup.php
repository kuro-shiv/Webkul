<?php
require_once "includes/db.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["full_name"]);
    $email = trim($_POST["email"]);
    $dob = trim($_POST["dob"]);
    $password = $_POST["password"];
    $confirmPassword = $_POST["confirm_password"];

    // Validate password confirmation
    if ($password !== $confirmPassword) {
        $error = "Passwords do not match!";
    } else {
        // Calculate age from DOB (dd/mm/yyyy)
        $dobObj = DateTime::createFromFormat("d/m/Y", $dob);
        $age = $dobObj ? $dobObj->diff(new DateTime())->y : null;

        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        // Profile picture upload
        $profilePic = null;
        if (!empty($_FILES["profile_pic"]["name"])) {
            $targetDir = "assets/uploads/";
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0777, true);
            }

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
        try {
            $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password, age, profile_pic) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $email, $hashedPassword, $age, $profilePic]);
            header("Location: login.php?success=1");
            exit;
        } catch (PDOException $e) {
            $error = "Email already exists!";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Signup</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background: #fff;
            padding: 30px 40px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            text-align: center;
            width: 350px;
        }
        .container h2 {
            margin-bottom: 20px;
            color: #333;
        }
        form input, form button {
            display: block;
            margin: 10px auto;
            padding: 12px;
            width: 100%;
            max-width: 280px;
            border: 1px solid #ddd;
            border-radius: 8px;
            outline: none;
            font-size: 14px;
        }
        form input:focus {
            border-color: #4a90e2;
            box-shadow: 0 0 4px rgba(74,144,226,0.5);
        }
        button {
            background: #007bff;
            color: #fff;
            border: none;
            font-size: 15px;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.3s;
        }
        button:hover {
            background: #0056b3;
        }
        p {
            margin-top: 15px;
            font-size: 14px;
        }
        a {
            color: #007bff;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
        .error {
            color: red;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Join Social Network</h2>
        <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
        <form method="post" enctype="multipart/form-data">
            <input type="text" name="full_name" placeholder="Full Name" required>
            <input type="text" name="dob" placeholder="dd/mm/yyyy" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="password" name="confirm_password" placeholder="Re-Password" required>
            <input type="file" name="profile_pic">
            <button type="submit">Sign Up</button>
        </form>
        <p>Already registered? <a href="login.php">Login</a></p>
    </div>
</body>
</html>
