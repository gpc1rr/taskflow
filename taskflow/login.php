<?php
session_start();
include 'db.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (!empty($email) && !empty($password)) {
        // البحث عن المستخدم بواسطة البريد الإلكتروني
        $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            
            // التحقق من صحة كلمة المرور المدخلة مقارنة بالكلمة المشفرة في القاعدة
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                header("Location: index.php");
                exit();
            } else {
                $error = "كلمة المرور غير صحيحة!";
            }
        } else {
            $error = "لا يوجد حساب مسجل بهذا البريد الإلكتروني!";
        }
        $stmt->close();
    } else {
        $error = "الرجاء تعبئة جميع الحقول.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TaskFlow Pro | Login</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body style="display: flex; justify-content: center; align-items: center; height: 100vh;">

    <div class="card" style="width: 400px; padding: 2rem;">
        <h2 style="text-align: center; color: var(--primary); margin-bottom: 1.5rem;"><i class="fa-solid fa-right-to-bracket"></i> Welcome Back</h2>
        
        <?php if (!empty($error)): ?>
            <p style="color: #ef4444; background: #fee2e2; padding: 10px; border-radius: 8px; margin-bottom: 1rem; text-align: center; font-size: 0.9rem;"><?php echo $error; ?></p>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div style="margin-bottom: 1rem;">
                <label style="display: block; margin-bottom: 5px; font-weight: 600; font-size: 0.9rem;">Email</label>
                <input type="email" name="email" placeholder="Enter your email" required style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px; outline: none;">
            </div>
            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 5px; font-weight: 600; font-size: 0.9rem;">Password</label>
                <input type="password" name="password" placeholder="Enter password" required style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px; outline: none;">
            </div>
            <button type="submit" class="btn-primary" style="width: 100%; padding: 12px;">Log In</button>
        </form>
        <p style="text-align: center; margin-top: 1rem; font-size: 0.9rem; color: var(--text-muted);">Don't have an account? <a href="register.php" style="color: var(--primary); text-decoration: none; font-weight: 600;">Sign up</a></p>
    </div>

</body>
</html>