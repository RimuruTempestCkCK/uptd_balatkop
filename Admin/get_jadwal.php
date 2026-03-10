<?php
session_start();
require_once '../config.php';
check_login();

if (!isset($_GET['id'])) {
    exit('Invalid request');
}

$conn = getConnection();
$id = (int) $_GET['id'];

/* =======================
   DATA JADWAL (EDIT)
======================= */
$stmt = $conn->prepare("
    SELECT 
        j.id,
        j.pelatihan_id,
        j.tanggal,
        j.jam_mulai,
        j.jam_selesai,
        j.ruangan_id,
        j.keterangan
    FROM jadwal j
    WHERE j.id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    exit('Data tidak ditemukan');
}

$jadwal = $result->fetch_assoc();

/* =======================
   LIST PELATIHAN
======================= */
$pelatihan = $conn->query("SELECT id, nama_pelatihan FROM pelatihan");
$ruangan = $conn->query("SELECT id, nama_ruangan FROM ruangan ORDER BY nama_ruangan ASC");

?>

<form action="proses_jadwal.php?action=edit" method="post">
    <input type="hidden" name="id" value="<?= $jadwal['id']; ?>">

    <div class="mb-3">
        <label class="form-label">Pelatihan <span class="text-danger">*</span></label>
        <select name="pelatihan_id" class="form-select" required>
            <?php while ($p = $pelatihan->fetch_assoc()): ?>
                <option value="<?= $p['id']; ?>"
                    <?= $p['id'] == $jadwal['pelatihan_id'] ? 'selected' : ''; ?>>
                    <?= htmlspecialchars($p['nama_pelatihan']); ?>
                </option>
            <?php endwhile; ?>
        </select>
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label">Tanggal <span class="text-danger">*</span></label>
            <input type="date" name="tanggal" class="form-control"
                value="<?= $jadwal['tanggal']; ?>" required>
        </div>

        <div class="col-md-3 mb-3">
            <label class="form-label">Jam Mulai <span class="text-danger">*</span></label>
            <input type="time" name="jam_mulai" class="form-control"
                value="<?= substr($jadwal['jam_mulai'], 0, 5); ?>" required>
        </div>

        <div class="col-md-3 mb-3">
            <label class="form-label">Jam Selesai <span class="text-danger">*</span></label>
            <input type="time" name="jam_selesai" class="form-control"
                value="<?= substr($jadwal['jam_selesai'], 0, 5); ?>" required>
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label">Ruangan <span class="text-danger">*</span></label>
        <select name="ruangan_id" class="form-select" required>
            <?php while ($r = $ruangan->fetch_assoc()): ?>
                <option value="<?= $r['id']; ?>"
                    <?= $r['id'] == $jadwal['ruangan_id'] ? 'selected' : ''; ?>>
                    <?= htmlspecialchars($r['nama_ruangan']); ?>
                </option>
            <?php endwhile; ?>
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label">Keterangan</label>
        <textarea name="keterangan" class="form-control"><?= htmlspecialchars($jadwal['keterangan']); ?></textarea>
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