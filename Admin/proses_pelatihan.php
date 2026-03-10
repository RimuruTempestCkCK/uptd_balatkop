<?php
session_start();
require_once '../config.php';
check_login();

$conn = getConnection();

$action = $_POST['action'] ?? $_GET['action'] ?? '';

/* =======================
   CEK KUOTA
======================= */
$stmtKuota = $conn->prepare("
    SELECT p.kuota, COUNT(rp.id) AS terisi
    FROM pelatihan p
    LEFT JOIN riwayat_peserta rp ON p.id = rp.pelatihan_id
    WHERE p.id = ?
    GROUP BY p.id
");
$stmtKuota->bind_param("i", $pelatihan_id);
$stmtKuota->execute();
$kuotaData = $stmtKuota->get_result()->fetch_assoc();
$stmtKuota->close();

if ($kuotaData && $kuotaData['kuota'] !== null && $kuotaData['terisi'] >= $kuotaData['kuota']) {
    set_flash('warning', 'Kuota pelatihan ini sudah penuh!');
    redirect('pelatihan.php');
}

/* =======================
   ADD PELATIHAN
======================= */
if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {

    $nama_pelatihan = clean_input($_POST['nama_pelatihan']);
    $penyelenggara  = clean_input($_POST['penyelenggara'] ?? '');
    $instruktur_id  = (int)($_POST['instruktur_id'] ?? 0);
    $kuota          = !empty($_POST['kuota']) ? (int)$_POST['kuota'] : null;

    if (!$nama_pelatihan || !$instruktur_id) {
        set_flash('danger', 'Nama pelatihan dan instruktur wajib diisi!');
        redirect('pelatihan.php');
    }

    $stmt = $conn->prepare(
        "INSERT INTO pelatihan (nama_pelatihan, penyelenggara, kuota, instruktur_id) VALUES (?, ?, ?, ?)"
    );
    $stmt->bind_param("ssii", $nama_pelatihan, $penyelenggara, $kuota, $instruktur_id);

    if ($stmt->execute()) {
        set_flash('success', 'Pelatihan berhasil ditambahkan!');
    } else {
        set_flash('danger', 'Gagal menambahkan pelatihan!');
    }

    $stmt->close();
    redirect('pelatihan.php');
}

/* =======================
   EDIT PELATIHAN
======================= */ 
elseif ($action === 'edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {

    $id             = (int)$_POST['id'];
    $nama_pelatihan = clean_input($_POST['nama_pelatihan']);
    $penyelenggara  = clean_input($_POST['penyelenggara'] ?? '');
    $instruktur_id  = (int)($_POST['instruktur_id'] ?? 0);
    $kuota          = !empty($_POST['kuota']) ? (int)$_POST['kuota'] : null;

    if (!$id || !$nama_pelatihan || !$instruktur_id) {
        set_flash('danger', 'Data tidak lengkap!');
        redirect('pelatihan.php');
    }

    $stmt = $conn->prepare(
        "UPDATE pelatihan SET nama_pelatihan = ?, penyelenggara = ?, kuota = ?, instruktur_id = ? WHERE id = ?"
    );
    $stmt->bind_param("ssiii", $nama_pelatihan, $penyelenggara, $kuota, $instruktur_id, $id);

    if ($stmt->execute()) {
        set_flash('success', 'Pelatihan berhasil diperbarui!');
    } else {
        set_flash('danger', 'Gagal memperbarui pelatihan!');
    }

    $stmt->close();
    redirect('pelatihan.php');
}

/* =======================
   DELETE PELATIHAN
======================= */ elseif ($action === 'delete' && isset($_GET['id'])) {

    $id = (int) $_GET['id'];

    // Cek relasi riwayat_peserta
    $stmtCheck = $conn->prepare("
        SELECT COUNT(*) 
        FROM riwayat_peserta
        WHERE pelatihan_id = ?
    ");
    $stmtCheck->bind_param("i", $id);
    $stmtCheck->execute();
    $stmtCheck->bind_result($total);
    $stmtCheck->fetch();
    $stmtCheck->close();

    if ($total > 0) {
        set_flash('warning', 'Tidak dapat menghapus pelatihan karena sudah memiliki peserta!');
        redirect('pelatihan.php');
    }

    $stmt = $conn->prepare("DELETE FROM pelatihan WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        set_flash('success', 'Pelatihan berhasil dihapus!');
    } else {
        set_flash('danger', 'Gagal menghapus pelatihan!');
    }

    $stmt->close();
    redirect('pelatihan.php');
} else {
    redirect('pelatihan.php');
}

$conn->close();
