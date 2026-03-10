<?php
session_start();
require_once '../config.php';
check_login();

if ($_SESSION['role'] !== 'Instruktur') exit('Akses ditolak');

$conn = getConnection();
$instruktur_id = $_SESSION['user_id'];
$peserta_id = (int)($_GET['peserta_id'] ?? 0);
$pelatihan_id = (int)($_GET['pelatihan_id'] ?? 0);

// Validasi peserta & pelatihan
$cek = $conn->prepare("SELECT p.nama_lengkap FROM riwayat_peserta rp JOIN peserta p ON rp.peserta_id=p.id WHERE rp.peserta_id=? AND rp.pelatihan_id=?");
$cek->bind_param("ii", $peserta_id, $pelatihan_id);
$cek->execute();
$cek->store_result();
if ($cek->num_rows == 0) exit('Data tidak ditemukan');
$cek->bind_result($nama_peserta);
$cek->fetch();

// Ambil evaluasi jika ada
$ev = $conn->query("SELECT * FROM evaluasi_pelatihan WHERE peserta_id=$peserta_id AND pelatihan_id=$pelatihan_id AND instruktur_id=$instruktur_id")->fetch_assoc();
?>
<form method="post" action="evaluasi.php?pelatihan_id=<?= $pelatihan_id; ?>">
    <input type="hidden" name="peserta_id" value="<?= $peserta_id; ?>">
    <input type="hidden" name="pelatihan_id" value="<?= $pelatihan_id; ?>">
    <div class="mb-3">
        <label class="form-label">Nama Peserta</label>
        <input type="text" class="form-control" value="<?= htmlspecialchars($nama_peserta); ?>" readonly>
    </div>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label">Nilai Kehadiran (0-100)</label>
            <input type="number" name="nilai_kehadiran" class="form-control" min="0" max="100" required value="<?= $ev['nilai_kehadiran'] ?? ''; ?>">
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">Nilai Partisipasi (0-100)</label>
            <input type="number" name="nilai_partisipasi" class="form-control" min="0" max="100" required value="<?= $ev['nilai_partisipasi'] ?? ''; ?>">
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">Nilai Praktik (0-100)</label>
            <input type="number" name="nilai_praktik" class="form-control" min="0" max="100" required value="<?= $ev['nilai_praktik'] ?? ''; ?>">
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">Nilai Sikap (0-100)</label>
            <input type="number" name="nilai_sikap" class="form-control" min="0" max="100" required value="<?= $ev['nilai_sikap'] ?? ''; ?>">
        </div>
    </div>
    <div class="mb-3">
        <label class="form-label">Keterangan</label>
        <textarea name="keterangan" class="form-control"><?= $ev['keterangan'] ?? ''; ?></textarea>
    </div>
    <div class="modal-footer p-0 pt-3">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            Batal
        </button>
        <button type="submit" class="btn btn-primary">
            Simpan Evaluasi
        </button>
    </div>
</form>
<?php
$cek->close();
$conn->close();
?>
