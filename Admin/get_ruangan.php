<?php
session_start();
require_once '../config.php';
check_login();

if (!isset($_GET['id'])) {
    exit('Invalid request');
}

$conn = getConnection();
$id = (int) $_GET['id'];

$stmt = $conn->prepare("
    SELECT id, nama_ruangan, kapasitas, keterangan
    FROM ruangan
    WHERE id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    exit('Data tidak ditemukan');
}

$data = $result->fetch_assoc();
?>

<form action="proses_ruangan.php?action=update" method="post">
    <input type="hidden" name="id" value="<?= $data['id']; ?>">

    <div class="mb-3">
        <label class="form-label">Nama Ruangan</label>
        <input type="text" name="nama_ruangan" class="form-control"
               value="<?= htmlspecialchars($data['nama_ruangan']); ?>" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Kapasitas</label>
        <input type="number" name="kapasitas" class="form-control"
               value="<?= $data['kapasitas']; ?>" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Keterangan</label>
        <textarea name="keterangan" class="form-control"><?= htmlspecialchars($data['keterangan']); ?></textarea>
    </div>

    <div class="modal-footer p-0 pt-3">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
    </div>
</form>

<?php
$stmt->close();
$conn->close();
?>
