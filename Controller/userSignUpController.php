<?php
session_start();
require_once '../DBConnection/DBConnector.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Step 1: Verify reCAPTCHA first
    $recaptcha_secret = '6LdR0GQrAAAAAKjp3XiqMRYMojiTeyF1iVpd2YbU';
    $recaptcha_response = $_POST['g-recaptcha-response'];

    $verify_url = 'https://www.google.com/recaptcha/api/siteverify';
    $data = [
        'secret' => $recaptcha_secret,
        'response' => $recaptcha_response,
        'remoteip' => $_SERVER['REMOTE_ADDR']
    ];

    $options = [
        'http' => [
            'method'  => 'POST',
            'header'  => 'Content-type: application/x-www-form-urlencoded',
            'content' => http_build_query($data)
        ]
    ];
    $context  = stream_context_create($options);
    $verify = file_get_contents($verify_url, false, $context);
    $captcha_success = json_decode($verify);

    if (!$captcha_success->success) {
        $_SESSION['signup_error'] = "Captcha verification failed. Please try again.";
        header("Location: ../User/SignUp.php");
        exit;
    }

    // Step 2: Proceed with user validation and creation
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    if ($password !== $confirm) {
        $_SESSION['signup_error'] = "Passwords do not match.";
        header("Location: ../User/SignUp.php");
        exit;
    }

    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z0-9]).{8,}$/', $password)) {
        $_SESSION['signup_error'] = "Password must be at least 8 characters long and include uppercase, lowercase, a number, and a special character.";
        header("Location: ../User/SignUp.php");
        exit;
    }

    try {
        $check = $pdo->prepare("SELECT * FROM users WHERE username = :username OR email = :email");
        $check->execute([':username' => $username, ':email' => $email]);

        if ($check->fetch()) {
            $_SESSION['signup_error'] = "Username or email already exists.";
            header("Location: ../User/SignUp.php");
            exit;
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, created_at) VALUES (:username, :email, :password, NOW())");
        $stmt->execute([
            ':username' => $username,
            ':email' => $email,
            ':password' => $hash
        ]);

        $_SESSION['user_id'] = $pdo->lastInsertId();
        $_SESSION['username'] = $username;

        header("Location: ../index.php");
        exit;

    } catch (PDOException $e) {
        $_SESSION['signup_error'] = "Signup failed: " . $e->getMessage();
        header("Location: ../User/SignUp.php");
        exit;
    }
} else {
    header("Location: ../User/SignUp.php");
    exit;
}
