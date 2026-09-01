<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$lang = $_SESSION['lang'] ?? 'en';
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['username'])) {
    $new_username = trim($_POST['username']);
    $new_password = $_POST['password'];

    if (!empty($new_username)) {
        if (!empty($new_password)) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET username = ?, password = ? WHERE id = ?");
            $stmt->bind_param("ssi", $new_username, $hashed_password, $user_id);
        } else {
            $stmt = $conn->prepare("UPDATE users SET username = ? WHERE id = ?");
            $stmt->bind_param("si", $new_username, $user_id);
        }

        if ($stmt->execute()) {
            $_SESSION['username'] = $new_username;
            $username = $new_username;
            $success = ($lang == 'ar') ? "تم تحديث الإعدادات بنجاح!" : "Settings updated successfully!";
        } else {
            $error = "Something went wrong.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo ($lang == 'ar') ? 'rtl' : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TaskFlow Pro | Settings</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <div class="app-container">
        <!-- Sidebar -->
        <aside class="sidebar" style="display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <h2><i class="fa-solid fa-layer-group"></i> TaskFlow</h2>
                <ul>
                    <li><a href="index.php"><i class="fa-solid fa-house"></i> <?php echo ($lang == 'ar') ? 'لوحة التحكم' : 'Dashboard'; ?></a></li>
                    <li><a href="tasks.php"><i class="fa-solid fa-list-check"></i> <?php echo ($lang == 'ar') ? 'مهامي' : 'My Tasks'; ?></a></li>
                    <li class="active"><a href="settings.php"><i class="fa-solid fa-gear"></i> <?php echo ($lang == 'ar') ? 'الإعدادات' : 'Settings'; ?></a></li>
                </ul>
            </div>
            <div>
                <!-- زر الوضع الليلي -->
                <button onclick="toggleTheme()" style="width: 100%; padding: 10px; margin-bottom: 10px; background: transparent; border: 1px solid var(--border); color: var(--text-main); border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px;">
                    <i class="fa-solid fa-moon" id="theme-icon"></i> <span id="theme-text">Dark Mode</span>
                </button>
                <a href="logout.php" style="display: flex; align-items: center; gap: 10px; padding: 10px; color: #ef4444; text-decoration: none; font-weight: 600; border-radius: 8px;"><i class="fa-solid fa-right-from-bracket"></i> <?php echo ($lang == 'ar') ? 'تسجيل الخروج' : 'Logout'; ?></a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header>
                <h1><?php echo ($lang == 'ar') ? 'إعدادات الحساب ⚙️' : 'Account Settings ⚙️'; ?></h1>
                <p><?php echo ($lang == 'ar') ? 'تعديل تفضيلات الحساب والمظهر واللغة.' : 'Update your profile information and preferences.'; ?></p>
            </header>

            <section class="task-section" style="margin-top: 2rem; max-width: 600px;">
                <!-- Language Selector Card (يرسل إلى change_lang.php المخصص) -->
                <div class="card" style="margin-bottom: 2rem;">
                    <h2><i class="fa-solid fa-globe"></i> <?php echo ($lang == 'ar') ? 'لغة الواجهة' : 'Interface Language'; ?></h2>
                    <form action="change_lang.php" method="POST" style="margin-top: 1rem; display: flex; gap: 10px;">
                        <select name="lang" style="flex: 1; padding: 10px; border: 1px solid var(--border); border-radius: 8px; background: var(--card-bg); color: var(--text-main);">
                            <option value="en" <?php if($lang=='en') echo 'selected'; ?>>English 🇺🇸</option>
                            <option value="ar" <?php if($lang=='ar') echo 'selected'; ?>>العربية 🇸🇦</option>
                        </select>
                        <button type="submit" class="btn-primary" style="padding: 10px 20px;"><?php echo ($lang == 'ar') ? 'تحديث' : 'Update'; ?></button>
                    </form>
                </div>

                <!-- Profile Edit Card -->
                <div class="card">
                    <h2><i class="fa-solid fa-user-pen"></i> <?php echo ($lang == 'ar') ? 'تعديل الملف الشخصي' : 'Edit Profile'; ?></h2>

                    <?php if (!empty($error)): ?><p style="color: #ef4444; background: #fee2e2; padding: 10px; border-radius: 8px; margin: 1rem 0;"><?php echo $error; ?></p><?php endif; ?>
                    <?php if (!empty($success)): ?><p style="color: #10b981; background: #d1fae5; padding: 10px; border-radius: 8px; margin: 1rem 0;"><?php echo $success; ?></p><?php endif; ?>

                    <form action="settings.php" method="POST" style="margin-top: 1.5rem;">
                        <div style="margin-bottom: 1.2rem;">
                            <label style="display: block; margin-bottom: 5px; font-weight: 600;"><?php echo ($lang == 'ar') ? 'اسم المستخدم' : 'Username'; ?></label>
                            <input type="text" name="username" value="<?php echo htmlspecialchars($username); ?>" required style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px; background: var(--bg-color); color: var(--text-main);">
                        </div>
                        <div style="margin-bottom: 1.5rem;">
                            <label style="display: block; margin-bottom: 5px; font-weight: 600;"><?php echo ($lang == 'ar') ? 'كلمة مرور جديدة (اختياري)' : 'New Password (Optional)'; ?></label>
                            <input type="password" name="password" placeholder="***" style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px; background: var(--bg-color); color: var(--text-main);">
                        </div>
                        <button type="submit" class="btn-primary" style="width: 100%; padding: 12px;"><?php echo ($lang == 'ar') ? 'حفظ التغييرات' : 'Save Changes'; ?></button>
                    </form>
                </div>
            </section>
        </main>
    </div>

</body>
<script src="script.js"></script>
</html>