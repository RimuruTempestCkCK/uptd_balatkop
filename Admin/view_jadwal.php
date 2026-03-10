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
   DATA PELATIHAN
======================= */
$stmt = $conn->prepare("SELECT * FROM pelatihan WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    exit('Data tidak ditemukan');
}

$pelatihan = $result->fetch_assoc();

/* =======================
   DATA JADWAL
======================= */
$stmtJadwal = $conn->prepare("
    SELECT j.*, r.nama_ruangan
    FROM jadwal j
    JOIN ruangan r ON j.ruangan_id = r.id
    WHERE pelatihan_id = ?
");
$stmtJadwal->bind_param("i", $id);
$stmtJadwal->execute();
$jadwal = $stmtJadwal->get_result();
?>

<div class="row">
    <div class="col-md-12 mb-3">
        <h4><?php echo htmlspecialchars($pelatihan['nama_pelatihan']); ?></h4>
        <p class="text-muted">
            <?php echo $pelatihan['deskripsi'] ?: 'Tidak ada deskripsi'; ?>
        </p>
    </div>
</div>

<table class="table table-bordered mb-4">
    <tr>
        <td width="30%" class="fw-bold">Penyelenggara</td>
        <td><?php echo $pelatihan['penyelenggara'] ?: '-'; ?></td>
    </tr>
    <tr>
        <td class="fw-bold">Tanggal Mulai</td>
        <td><?php echo format_tanggal($pelatihan['tanggal_mulai']); ?></td>
    </tr>
    <tr>
        <td class="fw-bold">Tanggal Selesai</td>
        <td><?php echo format_tanggal($pelatihan['tanggal_selesai']); ?></td>
    </tr>
    <tr>
        <td class="fw-bold">Jumlah Jadwal</td>
        <td>
            <span class="badge bg-primary">
                <?php echo $jadwal->num_rows; ?> sesi
            </span>
        </td>
    </tr>
</table>

<h5 class="mb-3">
    <i class="fas fa-calendar-alt"></i> Jadwal Pelatihan
</h5>

<?php if ($jadwal->num_rows > 0): ?>
    <div class="table-responsive">
        <table class="table table-sm table-hover">
            <thead>
                <tr>
                    <th width="40">No</th>
                    <th>Tanggal</th>
                    <th>Waktu</th>
                    <th>Lokasi</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; ?>
                <?php while ($j = $jadwal->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td><?php echo format_tanggal($j['tanggal']); ?></td>
                        <td>
                            <?php echo substr($j['jam_mulai'], 0, 5); ?>
                            -
                            <?php echo substr($j['jam_selesai'], 0, 5); ?>
                        </td>
                        <td><?= htmlspecialchars($j['nama_ruangan']); ?></td>
                        <td><?php echo htmlspecialchars($j['keterangan'] ?: '-'); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i>
        Jadwal pelatihan belum tersedia
    </div>
<?php endif; ?>

<?php
$stmt->close();
$stmtJadwal->close();
$conn->close();
?>