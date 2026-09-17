<?php

// ------------------------------------------------------
// Redirect
// ------------------------------------------------------

function redirect($path)
{
    header("Location: " . BASE_URL . $path);
    exit;
}

// ------------------------------------------------------
// Session Functions
// ------------------------------------------------------

// Start User Session
function startUserSession($pdo)
{
    if (!isset($_SESSION['user_id'])) {
        return false;
    }

    $user_id = $_SESSION['user_id'];

    $stmt = $pdo->prepare("
        INSERT INTO user_sessions (
            user_id,
            session_start
        )
        VALUES (
            :user_id,
            NOW()
        )
    ");

    $stmt->execute([
        'user_id' => $user_id
    ]);

    return $pdo->lastInsertId();
}


// End User Session
function endUserSession($pdo)
{
    if (!isset($_SESSION['session_id'])) {
        return false;
    }

    $session_id = $_SESSION['session_id'];

    $stmt = $pdo->prepare("
        UPDATE user_sessions
        SET
            session_end = NOW(),
            session_duration = TIMESTAMPDIFF(
                SECOND,
                session_start,
                NOW()
            )
        WHERE session_id = :session_id
    ");

    return $stmt->execute([
        'session_id' => $session_id
    ]);
}


// Get User Session Duration
function getSessionDuration($pdo)
{
    if (!isset($_SESSION['session_id'])) {
        return 0;
    }

    $session_id = $_SESSION['session_id'];

    $stmt = $pdo->prepare("
        SELECT TIMESTAMPDIFF(
            SECOND,
            session_start,
            COALESCE(session_end, NOW())
        )
        FROM user_sessions
        WHERE session_id = :session_id
    ");

    $stmt->execute([
        'session_id' => $session_id
    ]);

    return (int) $stmt->fetchColumn();
}


// ------------------------------------------------------
// Login
// ------------------------------------------------------

function loginUser($pdo, $login, $password)
{
    $sql = "
        SELECT
            user_id,
            user_email,
            user_username,
            user_password,
            user_role
        FROM users
        WHERE user_email = :login
            OR user_username = :login
        LIMIT 1
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        'login' => $login
    ]);

    $user = $stmt->fetch();

    // User not found
    if (!$user) {
        return false;
    }

    // Invalid password
    if (!password_verify($password, $user['user_password'])) {
        return false;
    }

    // Store user information in PHP session
    $_SESSION['user_id']       = $user['user_id'];
    $_SESSION['user_email']    = $user['user_email'];
    $_SESSION['user_username'] = $user['user_username'];
    $_SESSION['user_role']     = $user['user_role'];

    // Create database session record
    $_SESSION['session_id'] = startUserSession($pdo);

    return true;
}


// ------------------------------------------------------
// Authentication
// ------------------------------------------------------

// Require Login
function requireLogin()
{
    if (!isset($_SESSION['user_id'])) {
        header('Location: ' . BASE_URL . '/index.php');
        exit;
    }
}


// Require Specific Role
function requireRole($role)
{
    requireLogin();

    if ($_SESSION['user_role'] !== $role) {
        http_response_code(403);
        die('Access denied.');
    }
}

?>