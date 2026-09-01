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

// تحديث حالة المهمة
if (isset($_GET['toggle'])) {
    $task_id = $_GET['toggle'];
    $stmt = $conn->prepare("UPDATE tasks SET status = IF(status = 'Pending', 'Completed', 'Pending') WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $task_id, $user_id);
    $stmt->execute();
    $stmt->close();
    header("Location: tasks.php");
    exit();
}

// حذف المهمة
if (isset($_GET['delete'])) {
    $task_id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $task_id, $user_id);
    $stmt->execute();
    $stmt->close();
    header("Location: tasks.php");
    exit();
}

// جلب كل مهام المستخدم
$stmt = $conn->prepare("SELECT * FROM tasks WHERE user_id = ? ORDER BY id DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo ($lang == 'ar') ? 'rtl' : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TaskFlow Pro | My Tasks</title>
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
                    <li class="active"><a href="tasks.php"><i class="fa-solid fa-list-check"></i> <?php echo ($lang == 'ar') ? 'مهامي' : 'My Tasks'; ?></a></li>
                    <li><a href="settings.php"><i class="fa-solid fa-gear"></i> <?php echo ($lang == 'ar') ? 'الإعدادات' : 'Settings'; ?></a></li>
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
                <h1><?php echo ($lang == 'ar') ? 'إدارة المهام 📋' : 'My Tasks Management 📋'; ?></h1>
                <p><?php echo ($lang == 'ar') ? 'عرض وتحديث وإدارة كافة مهامك الشخصية هنا.' : 'View, update, or manage all your personal tasks here.'; ?></p>
            </header>

            <section class="task-section" style="margin-top: 2rem;">
                <div class="card">
                    <h2><i class="fa-solid fa-tasks"></i> <?php echo ($lang == 'ar') ? 'جميع مهامك' : 'All Your Tasks'; ?></h2>
                    <ul class="task-list" style="margin-top: 1rem;">
                        <?php if ($result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <li class="task-item" style="display: flex; justify-content: space-between; align-items: center; padding: 12px; border-bottom: 1px solid var(--border);">
                                    <span style="font-size: 1rem; <?php echo ($row['status'] == 'Completed') ? 'text-decoration: line-through; color: var(--text-muted);' : ''; ?>">
                                        <?php echo htmlspecialchars($row['task_name']); ?>
                                    </span>
                                    <div class="task-actions" style="display: flex; gap: 10px; align-items: center;">
                                        <a href="tasks.php?toggle=<?php echo $row['id']; ?>" class="badge <?php echo strtolower($row['status']); ?>" style="text-decoration: none; cursor: pointer;">
                                            <?php echo $row['status']; ?>
                                        </a>
                                        <a href="tasks.php?delete=<?php echo $row['id']; ?>" class="btn-delete" style="color: #ef4444;"><i class="fa-solid fa-trash"></i></a>
                                    </div>
                                </li>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p style="color: var(--text-muted); text-align: center; padding: 20px;"><?php echo ($lang == 'ar') ? 'لا توجد مهام. عُد لوحة التحكم لإضافة مهام!' : 'No tasks found. Go back to Dashboard to add tasks!'; ?></p>
                        <?php endif; ?>
                    </ul>
                </div>
            </section>
        </main>
    </div>

</body>
<script src="script.js"></script>
</html>