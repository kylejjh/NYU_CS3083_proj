<?php
// auth.php - helper functions for authentication
require_once 'config.php';

function current_user_id() {
    return $_SESSION['player_id'] ?? null;
}

function current_username() {
    return $_SESSION['username'] ?? null;
}

function require_login() {
    if (!current_user_id()) {
        header('Location: login.php');
        exit;
    }
}
?>