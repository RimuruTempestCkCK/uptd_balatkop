<?php
session_start();
require_once '../config.php';
check_login();

$conn = getConnection();
$action = $_POST['action'] ?? $_GET['action'] ?? '';

/* =======================
   ADD JADWAL
======================= */
if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {

    $pelatihan_id = (int) $_POST['pelatihan_id'];
    $tanggal      = clean_input($_POST['tanggal']);
    $jam_mulai    = clean_input($_POST['jam_mulai']);
    $jam_selesai  = clean_input($_POST['jam_selesai']);
    $ruangan_id   = (int) $_POST['ruangan_id'];
    $keterangan   = clean_input($_POST['keterangan'] ?? '');

    if (!$pelatihan_id || !$tanggal || !$jam_mulai || !$jam_selesai || !$ruangan_id) {
        set_flash('danger', 'Data wajib diisi!');
        redirect('jadwal.php');
    }

    if ($jam_selesai <= $jam_mulai) {
        set_flash('warning', 'Jam selesai harus lebih besar dari jam mulai!');
        redirect('jadwal.php');
    }

    /* 🔴 CEK BENTROK RUANGAN */
    $cek = $conn->prepare("
        SELECT id FROM jadwal
        WHERE tanggal = ?
          AND ruangan_id = ?
          AND (? < jam_selesai AND ? > jam_mulai)
    ");
    $cek->bind_param("siss", $tanggal, $ruangan_id, $jam_mulai, $jam_selesai);
    $cek->execute();
    $cek->store_result();

    if ($cek->num_rows > 0) {
        set_flash('danger', '❌ Jadwal bentrok! Ruangan sudah dipakai.');
        redirect('jadwal.php');
    }
    $cek->close();

    /* ✅ INSERT */
    $stmt = $conn->prepare("
        INSERT INTO jadwal
        (pelatihan_id, tanggal, jam_mulai, jam_selesai, ruangan_id, keterangan)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param(
        "isssis",
        $pelatihan_id,
        $tanggal,
        $jam_mulai,
        $jam_selesai,
        $ruangan_id,
        $keterangan
    );

    $stmt->execute();
    $stmt->close();

    set_flash('success', 'Jadwal berhasil ditambahkan!');
    redirect('jadwal.php');
}

/* =======================
   EDIT JADWAL
======================= */
elseif ($action === 'edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {

    $id           = (int) $_POST['id'];
    $pelatihan_id = (int) $_POST['pelatihan_id'];
    $tanggal      = clean_input($_POST['tanggal']);
    $jam_mulai    = clean_input($_POST['jam_mulai']);
    $jam_selesai  = clean_input($_POST['jam_selesai']);
    $ruangan_id   = (int) $_POST['ruangan_id'];
    $keterangan   = clean_input($_POST['keterangan'] ?? '');

    if ($jam_selesai <= $jam_mulai) {
        set_flash('warning', 'Jam selesai harus lebih besar dari jam mulai!');
        redirect('jadwal.php');
    }

    /* 🔴 CEK BENTROK (KECUALI DIRI SENDIRI) */
    $cek = $conn->prepare("
        SELECT id FROM jadwal
        WHERE tanggal = ?
          AND ruangan_id = ?
          AND id != ?
          AND (? < jam_selesai AND ? > jam_mulai)
    ");
    $cek->bind_param("siiss", $tanggal, $ruangan_id, $id, $jam_mulai, $jam_selesai);
    $cek->execute();
    $cek->store_result();

    if ($cek->num_rows > 0) {
        set_flash('danger', '❌ Jadwal bentrok! Ruangan sudah digunakan.');
        redirect('jadwal.php');
    }
    $cek->close();

    $stmt = $conn->prepare("
        UPDATE jadwal SET
            pelatihan_id = ?,
            tanggal = ?,
            jam_mulai = ?,
            jam_selesai = ?,
            ruangan_id = ?,
            keterangan = ?
        WHERE id = ?
    ");
    $stmt->bind_param(
        "isssisi",
        $pelatihan_id,
        $tanggal,
        $jam_mulai,
        $jam_selesai,
        $ruangan_id,
        $keterangan,
        $id
    );

    $stmt->execute();
    $stmt->close();

    set_flash('success', 'Jadwal berhasil diperbarui!');
    redirect('jadwal.php');
}

/* =======================
   DELETE JADWAL
======================= */
elseif ($action === 'delete' && isset($_GET['id'])) {

    $id = (int) $_GET['id'];

    $stmt = $conn->prepare("DELETE FROM jadwal WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    set_flash('success', 'Jadwal berhasil dihapus!');
    redirect('jadwal.php');
}

else {
    redirect('jadwal.php');
}

$conn->close();
