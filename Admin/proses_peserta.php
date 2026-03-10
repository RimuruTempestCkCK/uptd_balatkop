<?php
session_start();
require_once '../config.php';
check_login();

$conn = getConnection();
$action = $_POST['action'] ?? $_GET['action'] ?? '';

/* =========================
   ADD PESERTA
========================= */
if ($action == 'add' && $_SERVER['REQUEST_METHOD'] == 'POST') {

    $nama_lengkap   = clean_input($_POST['nama_lengkap']);
    $email          = clean_input($_POST['email']);
    $telepon        = clean_input($_POST['telepon']);
    $tanggal_lahir  = clean_input($_POST['tanggal_lahir']);
    $pelatihan_id   = (int)($_POST['pelatihan_id'] ?? 0);
    $status         = clean_input($_POST['status']);

    if (empty($nama_lengkap)) {
        set_flash('danger', 'Nama Lengkap wajib diisi!');
        redirect('peserta.php');
    }

    $stmt = $conn->prepare("
        INSERT INTO peserta 
        (nama_lengkap, email, telepon, tanggal_lahir, pelatihan_id, status)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param(
        "ssssis",
        $nama_lengkap,
        $email,
        $telepon,
        $tanggal_lahir,
        $pelatihan_id,
        $status
    );

    if ($stmt->execute()) {
        set_flash('success', 'Data peserta berhasil ditambahkan!');
    } else {
        set_flash('danger', 'Gagal menambahkan data peserta: ' . $stmt->error);
    }

    $stmt->close();
    redirect('peserta.php');
}

/* =========================
   DELETE PESERTA
========================= */
elseif ($action == 'delete' && isset($_GET['id'])) {

    $id = (int)$_GET['id'];

    // Cek relasi data
    $check_pelatihan = $conn->query(
        "SELECT COUNT(*) AS total FROM riwayat_peserta WHERE peserta_id = $id"
    )->fetch_assoc()['total'];

    if ($check_pelatihan > 0) {
        set_flash(
            'warning',
            'Peserta tidak dapat dihapus karena memiliki riwayat pelatihan. Ubah status menjadi Tidak Aktif.'
        );
        redirect('peserta.php');
    }

    $stmt = $conn->prepare("DELETE FROM peserta WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        set_flash('success', 'Data peserta berhasil dihapus!');
    } else {
        set_flash('danger', 'Gagal menghapus data peserta: ' . $stmt->error);
    }

    $stmt->close();
    redirect('peserta.php');
}

else {
    redirect('peserta.php');
}

$conn->close();
?>
