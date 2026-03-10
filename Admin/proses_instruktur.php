<?php
session_start();
require_once '../config.php';
check_login();

$conn = getConnection();

if ($_GET['action'] === 'add') {
    $stmt = $conn->prepare("
        INSERT INTO users (nama_lengkap, username, password, role)
        VALUES (?, ?, ?, 'Instruktur')
    ");
    $stmt->bind_param(
        "sss",
        $_POST['nama_lengkap'],
        $_POST['username'],
        password_hash($_POST['password'], PASSWORD_DEFAULT)
    );
    $stmt->execute();

    set_flash('success', 'Instruktur berhasil ditambahkan');
}

if ($_GET['action'] === 'update') {
    if (!empty($_POST['password'])) {
        $stmt = $conn->prepare("
            UPDATE users SET
                nama_lengkap = ?, username = ?, password = ?
            WHERE id = ?
        ");
        $stmt->bind_param(
            "sssi",
            $_POST['nama_lengkap'],
            $_POST['username'],
            password_hash($_POST['password'], PASSWORD_DEFAULT),
            $_POST['id']
        );
    } else {
        $stmt = $conn->prepare("
            UPDATE users SET
                nama_lengkap = ?, username = ?
            WHERE id = ?
        ");
        $stmt->bind_param(
            "ssi",
            $_POST['nama_lengkap'],
            $_POST['username'],
            $_POST['id']
        );
    }
    $stmt->execute();

    set_flash('success', 'Instruktur berhasil diperbarui');
}

if ($_GET['action'] === 'delete') {
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $_GET['id']);
    $stmt->execute();

    set_flash('success', 'Instruktur berhasil dihapus');
}

header('Location: instruktur.php');
exit;
