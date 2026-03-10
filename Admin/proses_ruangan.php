<?php
session_start();
require_once '../config.php';
check_login();

$conn = getConnection();

if ($_GET['action'] === 'add') {
    $stmt = $conn->prepare("
        INSERT INTO ruangan (nama_ruangan, kapasitas, keterangan)
        VALUES (?, ?, ?)
    ");
    $stmt->bind_param(
        "sis",
        $_POST['nama_ruangan'],
        $_POST['kapasitas'],
        $_POST['keterangan']
    );
    $stmt->execute();

    set_flash('success', 'Ruangan berhasil ditambahkan');
}

if ($_GET['action'] === 'update') {
    $stmt = $conn->prepare("
        UPDATE ruangan SET
            nama_ruangan = ?, kapasitas = ?, keterangan = ?
        WHERE id = ?
    ");
    $stmt->bind_param(
        "sisi",
        $_POST['nama_ruangan'],
        $_POST['kapasitas'],
        $_POST['keterangan'],
        $_POST['id']
    );
    $stmt->execute();

    set_flash('success', 'Ruangan berhasil diperbarui');
}

if ($_GET['action'] === 'delete') {
    $stmt = $conn->prepare("DELETE FROM ruangan WHERE id = ?");
    $stmt->bind_param("i", $_GET['id']);
    $stmt->execute();

    set_flash('success', 'Ruangan berhasil dihapus');
}

header('Location: ruangan.php');
exit;
