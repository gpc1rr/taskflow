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

// إضافة مهمة جديدة
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_task'])) {
    $task_name = trim($_POST['task_name']);
    if (!empty($task_name)) {
        $stmt = $conn->prepare("INSERT INTO tasks (user_id, task_name, status) VALUES (?, ?, 'Pending')");
        $stmt->bind_param("is", $user_id, $task_name);
        $stmt->execute();
        $stmt->close();
        header("Location: index.php");
        exit();
    }
}

// حذف مهمة
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $id, $user_id);
    $stmt->execute();
    $stmt->close();
    header("Location: index.php");
    exit();
}

// استعراض مهام المستخدم الحالي
$stmt = $conn->prepare("SELECT * FROM tasks WHERE user_id = ? ORDER BY id DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// عدادات المهام
$stmt_pending = $conn->prepare("SELECT COUNT(*) as count FROM tasks WHERE user_id = ? AND status='Pending'");
$stmt_pending->bind_param("i", $user_id);
$stmt_pending->execute();
$total_pending = $stmt_pending->get_result()->fetch_assoc()['count'] ?? 0;

$stmt_completed = $conn->prepare("SELECT COUNT(*) as count FROM tasks WHERE user_id = ? AND status='Completed'");
$stmt_completed->bind_param("i", $user_id);
$stmt_completed->execute();
$total_completed = $stmt_completed->get_result()->fetch_assoc()['count'] ?? 0;
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo ($lang == 'ar') ? 'rtl' : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TaskFlow Pro | Dashboard</title>
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
                    <li class="active"><a href="index.php"><i class="fa-solid fa-house"></i> <?php echo ($lang == 'ar') ? 'لوحة التحكم' : 'Dashboard'; ?></a></li>
                    <li><a href="tasks.php"><i class="fa-solid fa-list-check"></i> <?php echo ($lang == 'ar') ? 'مهامي' : 'My Tasks'; ?></a></li>
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
                <h1><?php echo ($lang == 'ar') ? 'أهلاً بك، ' . htmlspecialchars($username) . ' 👋' : 'Welcome Back, ' . htmlspecialchars($username) . '! 👋'; ?></h1>
                <p><?php echo ($lang == 'ar') ? 'إليك نظرة عامة على مشاريعك اليوم.' : "Here is what's happening with your projects today."; ?></p>
            </header>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                    <div>
                        <h3><?php echo ($lang == 'ar') ? 'المهام المعلقة' : 'Pending Tasks'; ?></h3>
                        <p><?php echo $total_pending; ?></p>
                    </div>
                </div>
                <div class="stat-card">
                    <i class="fa-solid fa-circle-check"></i>
                    <div>
                        <h3><?php echo ($lang == 'ar') ? 'المكتملة' : 'Completed'; ?></h3>
                        <p><?php echo $total_completed; ?></p>
                    </div>
                </div>
            </div>

            <!-- Task Form Section -->
            <section class="task-section">
                <div class="card">
                    <h2><i class="fa-solid fa-plus-circle"></i> <?php echo ($lang == 'ar') ? 'إضافة مهمة جديدة' : 'Add New Task'; ?></h2>
                    <form action="index.php" method="POST">
                        <div class="input-group">
                            <input type="text" name="task_name" placeholder="<?php echo ($lang == 'ar') ? 'ما الذي تريد إنجازه؟' : 'What needs to be done?'; ?>" required>
                            <button type="submit" name="add_task" class="btn-primary"><?php echo ($lang == 'ar') ? 'إضافة مهمة' : 'Add Task'; ?></button>
                        </div>
                    </form>
                </div>

                <!-- Tasks List -->
                <div class="card">
                    <h2><i class="fa-solid fa-tasks"></i> <?php echo ($lang == 'ar') ? 'المهام الأخيرة' : 'Recent Tasks'; ?></h2>
                    <ul class="task-list">
                        <?php if ($result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <li class="task-item">
                                    <span><?php echo htmlspecialchars($row['task_name']); ?></span>
                                    <div class="task-actions">
                                        <span class="badge <?php echo strtolower($row['status']); ?>"><?php echo $row['status']; ?></span>
                                        <a href="index.php?delete=<?php echo $row['id']; ?>" class="btn-delete"><i class="fa-solid fa-trash"></i></a>
                                    </div>
                                </li>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p style="color: var(--text-muted); text-align: center; padding: 10px;"><?php echo ($lang == 'ar') ? 'لا توجد مهام. أضف مهمتك الأولى بالأعلى!' : 'No tasks found. Add your first task above!'; ?></p>
                        <?php endif; ?>
                    </ul>
                </div>
            </section>
        </main>
    </div>

</body>
<script src="script.js"></script>
</html>