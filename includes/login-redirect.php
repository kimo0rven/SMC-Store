<?php
session_start();
include '../includes/db_connection.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $email    = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $redirect = $_POST['redirect'] ?? '/';

    $stmt = $pdo->prepare("SELECT * FROM user_account WHERE email = ?");
    $stmt->execute([$email]);
    $user_account = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user_account && $user_account['password']) {
        $user_id_stmt = $pdo->prepare("SELECT user_id FROM user WHERE user_account_id = ?");
        $user_id_stmt->execute([$user_account['user_account_id']]);
        $user_details = $user_id_stmt->fetch(PDO::FETCH_ASSOC);

        $_SESSION['isLoggedIn'] = true;
        $_SESSION['user_account_id'] = $user_account['user_account_id'];
        $_SESSION['user_id'] = $user_details['user_id'];
        $_SESSION['email'] = $email;
        unset($_SESSION['error_message']);

        echo json_encode([
            'success'  => true,
            'redirect' => $redirect
        ]);
        exit;
    } else {
        $_SESSION['isLoggedIn'] = false;
        $_SESSION['error_message'] = "Invalid email or password.";

        echo json_encode([
            'success' => false,
            'message' => 'Invalid email or password.'
        ]);
        exit;
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method.'
    ]);
    exit;
}
