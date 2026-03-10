<?php
session_start();
require_once '../config.php'; // sesuaikan path jika perlu
check_login();

if (!isset($_GET['id'])) {
    exit('Invalid request');
}

$conn = getConnection();
$id = clean_input($_GET['id']);

$stmt = $conn->prepare("SELECT * FROM peserta WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    exit('Data tidak ditemukan');
}

$data = $result->fetch_assoc();
?>
<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Nama Lengkap</label>
        <input type="text" class="form-control" value="<?= htmlspecialchars($data['nama_lengkap']); ?>" readonly>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Email</label>
        <input type="text" class="form-control" value="<?= htmlspecialchars($data['email']); ?>" readonly>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Telepon</label>
        <input type="text" class="form-control" value="<?= htmlspecialchars($data['telepon']); ?>" readonly>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Tanggal Lahir</label>
        <input type="text" class="form-control" value="<?= format_tanggal($data['tanggal_lahir']); ?>" readonly>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Status</label>
        <input type="text" class="form-control" value="<?= htmlspecialchars($data['status']); ?>" readonly>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Tanggal Daftar</label>
        <input type="text" class="form-control" value="<?= format_tanggal($data['created_at']); ?>" readonly>
    </div>
</div>
<?php
$stmt->close();
$conn->close();
?>