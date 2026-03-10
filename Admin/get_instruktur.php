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
    SELECT id, nama_lengkap, username
    FROM users
    WHERE id = ? AND role = 'Instruktur'
");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    exit('Data tidak ditemukan');
}

$data = $result->fetch_assoc();
?>

<form action="proses_instruktur.php?action=update" method="post">
    <input type="hidden" name="id" value="<?= $data['id']; ?>">

    <div class="mb-3">
        <label class="form-label">Nama Lengkap</label>
        <input type="text" name="nama_lengkap" class="form-control"
               value="<?= htmlspecialchars($data['nama_lengkap']); ?>" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Username</label>
        <input type="text" name="username" class="form-control"
               value="<?= htmlspecialchars($data['username']); ?>" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Password (Opsional)</label>
        <input type="password" name="password" class="form-control"
               placeholder="Kosongkan jika tidak diubah">
    </div>

    <div class="modal-footer p-0 pt-3">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            Batal
        </button>
        <button type="submit" class="btn btn-primary">
            Simpan Perubahan
        </button>
    </div>
</form>

<?php
$stmt->close();
$conn->close();
?>
