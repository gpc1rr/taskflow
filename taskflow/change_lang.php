<?php
session_start();
if (isset($_POST['lang'])) {
    $_SESSION['lang'] = $_POST['lang'];
}
// العودة لنفس الصفحة التي أتى منها المستخدم أو لوحة التحكم
$redirect = $_SERVER['HTTP_REFERER'] ?? 'index.php';
header("Location: " . $redirect);
exit();
?>