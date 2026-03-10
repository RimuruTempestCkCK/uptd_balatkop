<?php
session_start();
require_once '../config.php';
check_login();

/* =======================
   VALIDASI ROLE
======================= */
if ($_SESSION['role'] !== 'Peserta') {
    set_flash('warning', 'Akses ditolak');
    redirect('pelatihan.php');
}

$conn = getConnection();

/* =======================
   DATA PESERTA LOGIN (PAKAI user_id)
======================= */
$stmtPeserta = $conn->prepare("
    SELECT id
    FROM peserta
    WHERE user_id = ?
    LIMIT 1
");
$stmtPeserta->bind_param("i", $_SESSION['user_id']);
$stmtPeserta->execute();
$resultPeserta = $stmtPeserta->get_result();

/* =======================
   JIKA PESERTA BELUM ADA → BUAT OTOMATIS
======================= */
if ($resultPeserta->num_rows === 0) {
    $insertPeserta = $conn->prepare("
        INSERT INTO peserta (user_id, nama_lengkap, status)
        VALUES (?, ?, 'Aktif')
    ");
    $insertPeserta->bind_param(
        "is",
        $_SESSION['user_id'],
        $_SESSION['nama_lengkap']
    );
    $insertPeserta->execute();
    $peserta_id = $insertPeserta->insert_id;
} else {
    $peserta_id = $resultPeserta->fetch_assoc()['id'];
}

/* =======================
   VALIDASI INPUT
======================= */
$pelatihan_id = (int) ($_POST['pelatihan_id'] ?? 0);

if ($pelatihan_id <= 0) {
    set_flash('danger', 'Permintaan tidak valid');
    redirect('pelatihan.php');
}

/* =======================
   CEK SUDAH DAFTAR
======================= */
$cek = $conn->prepare("
    SELECT id
    FROM riwayat_peserta
    WHERE peserta_id = ? AND pelatihan_id = ?
");
$cek->bind_param("ii", $peserta_id, $pelatihan_id);
$cek->execute();

if ($cek->get_result()->num_rows > 0) {
    set_flash('warning', 'Anda sudah terdaftar di pelatihan ini');
    redirect('pelatihan.php');
}

/* =======================
   CEK KUOTA
======================= */
$stmtKuota = $conn->prepare("
    SELECT p.kuota,
           COUNT(rp.id) AS terisi
    FROM pelatihan p
    LEFT JOIN riwayat_peserta rp ON p.id = rp.pelatihan_id
    WHERE p.id = ?
    GROUP BY p.id
");
$stmtKuota->bind_param("i", $pelatihan_id);
$stmtKuota->execute();
$kuota = $stmtKuota->get_result()->fetch_assoc();
$stmtKuota->close();

if ($kuota && $kuota['kuota'] !== null && $kuota['terisi'] >= $kuota['kuota']) {
    set_flash('warning', 'Maaf, kuota pelatihan ini sudah penuh');
    redirect('pelatihan.php');
}

/* =======================
   INSERT PENDAFTARAN
======================= */
$stmt = $conn->prepare("
    INSERT INTO riwayat_peserta
    (peserta_id, pelatihan_id, tanggal_ikut, status)
    VALUES (?, ?, CURDATE(), 'Proses')
");
$stmt->bind_param("ii", $peserta_id, $pelatihan_id);

if ($stmt->execute()) {
    set_flash('success', 'Berhasil mendaftar pelatihan');
} else {
    set_flash('danger', 'Gagal mendaftar pelatihan');
}

$stmt->close();
redirect('pelatihan.php');
