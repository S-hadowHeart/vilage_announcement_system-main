<?php
session_start();
require_once 'db.php';

// Authenticate admin and start session
function admin_login($username, $password) {
    $db = (new Database())->getConnection();
    $stmt = $db->prepare('SELECT * FROM admins WHERE username = ? AND is_active = 1');
    $stmt->execute([$username]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($admin && password_verify($password, $admin['password_hash'])) {
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_role'] = $admin['role'];
        $_SESSION['admin_name'] = $admin['name'];
        return true;
    }
    return false;
}

// Logout admin
function admin_logout() {
    session_unset();
    session_destroy();
}

// Check if admin is logged in
function is_admin_logged_in() {
    return isset($_SESSION['admin_id']);
}

// Check if admin is master
function is_master_admin() {
    return isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'master';
} 