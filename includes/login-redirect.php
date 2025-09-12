<?php
session_start();
include '../includes/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === "POST" && isset($_POST['login_submit'])) {
    $email    = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    $stmt = $pdo->prepare("SELECT * FROM user_account WHERE email = ?");
    $stmt->execute([$email]);
    $user_account = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user_account && $password) {
        $_SESSION['isLoggedIn'] = true;
        $_SESSION['user_id'] = $user_account['user_account_id'];
        $_SESSION['email'] = $email;
        unset($_SESSION['error_message']);
        header("Location: /");
        exit;
    } else {
        $_SESSION['isLoggedIn'] = false;
        $_SESSION['error_message'] = "Invalid email or password.";
        header("Location: /");
        exit;
    }
} else {
    header("Location: /");
    exit;
}
?>
