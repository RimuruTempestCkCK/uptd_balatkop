<?php
session_start();
require_once '../config.php';
check_login();

if (!isset($_GET['id'])) exit('Invalid request');

$conn = getConnection();
$id = (int) $_GET['id'];

$stmt = $conn->prepare("
    SELECT id, nama_pelatihan, penyelenggara, kuota, instruktur_id
    FROM pelatihan
    WHERE id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) exit('Data tidak ditemukan');

// Ambil daftar instruktur
$instruktur = $conn->query("SELECT id, nama_lengkap FROM users WHERE role = 'Instruktur' ORDER BY nama_lengkap ASC");
?>

<form action="proses_pelatihan.php" method="post">
    <input type="hidden" name="action" value="edit">
    <input type="hidden" name="id" value="<?= $data['id']; ?>">

    <div class="mb-3">
        <label class="form-label">Nama Pelatihan *</label>
        <input type="text" name="nama_pelatihan" class="form-control"
            value="<?= htmlspecialchars($data['nama_pelatihan']); ?>" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Penyelenggara</label>
        <input type="text" name="penyelenggara" class="form-control"
            value="<?= htmlspecialchars($data['penyelenggara']); ?>">
    </div>

    <div class="mb-3">
        <label class="form-label">Instruktur</label>
        <select name="instruktur_id" class="form-select" required>
            <option value="">Pilih Instruktur</option>
            <?php while ($row = $instruktur->fetch_assoc()): ?>
                <option value="<?= $row['id']; ?>" <?= $row['id'] == $data['instruktur_id'] ? 'selected' : ''; ?>>
                    <?= htmlspecialchars($row['nama_lengkap']); ?>
                </option>
            <?php endwhile; ?>
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label">Kuota Peserta</label>
        <input type="number" name="kuota" class="form-control" min="1"
            value="<?= $data['kuota']; ?>"
            placeholder="Kosongkan = tidak terbatas">
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