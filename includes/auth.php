<?php
session_start();

function checkLogin() {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
        header("Location: /login.php");
        exit();
    }
}

function checkRole($allowed_roles) {
    if (!in_array($_SESSION['role'], $allowed_roles)) {
        header("Location: /dashboard/" . $_SESSION['role'] . "/index.php");
        exit();
    }
}

function getUserId() {
    return $_SESSION['user_id'] ?? null;
}

function getRole() {
    return $_SESSION['role'] ?? null;
}
?> 