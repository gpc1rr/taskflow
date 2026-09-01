<?php
session_start();
include 'db.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (!empty($username) && !empty($email) && !empty($password)) {
        // التحقق من عدم تكرار الإيميل
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $error = "This email is already registered!";
        } else {
            // تشفير كلمة المرور لحماية البيانات
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $email, $hashed_password);
            
            if ($stmt->execute()) {
                // تسجيل الدخول تلقائياً بعد إنشاء الحساب بنجاح
                $_SESSION['user_id'] = $stmt->insert_id;
                $_SESSION['username'] = $username;
                
                header("Location: index.php");
                exit();
            } else {
                $error = "Something went wrong. Please try again.";
            }
            $stmt->close();
        }
        $check->close();
    } else {
        $error = "Please fill in all fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TaskFlow Pro | Register</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body style="display: flex; justify-content: center; align-items: center; height: 100vh;">

    <div class="card" style="width: 400px; padding: 2rem;">
        <h2 style="text-align: center; color: var(--primary); margin-bottom: 1.5rem;"><i class="fa-solid fa-user-plus"></i> Create Account</h2>
        
        <?php if (!empty($error)): ?>
            <p style="color: #ef4444; background: #fee2e2; padding: 10px; border-radius: 8px; margin-bottom: 1rem; text-align: center; font-size: 0.9rem;"><?php echo $error; ?></p>
        <?php endif; ?>

        <form action="register.php" method="POST">
            <div style="margin-bottom: 1rem;">
                <label style="display: block; margin-bottom: 5px; font-weight: 600; font-size: 0.9rem;">Username</label>
                <input type="text" name="username" placeholder="Enter your name" required style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px; outline: none;">
            </div>
            <div style="margin-bottom: 1rem;">
                <label style="display: block; margin-bottom: 5px; font-weight: 600; font-size: 0.9rem;">Email</label>
                <input type="email" name="email" placeholder="Enter your email" required style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px; outline: none;">
            </div>
            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 5px; font-weight: 600; font-size: 0.9rem;">Password</label>
                <input type="password" name="password" placeholder="Enter password" required style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px; outline: none;">
            </div>
            <button type="submit" class="btn-primary" style="width: 100%; padding: 12px;">Sign Up & Login</button>
        </form>
        <p style="text-align: center; margin-top: 1rem; font-size: 0.9rem; color: var(--text-muted);">Already have an account? <a href="login.php" style="color: var(--primary); text-decoration: none; font-weight: 600;">Log in</a></p>
    </div>

</body>
</html>