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
$stmt = $conn->prepare("
    SELECT p.*, u.nama_lengkap AS nama_instruktur 
    FROM pelatihan p 
    LEFT JOIN users u ON p.instruktur_id = u.id 
    WHERE p.id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    exit('Data tidak ditemukan');
}

$data = $result->fetch_assoc();

/* =======================
   DATA PESERTA
======================= */
$stmtPeserta = $conn->prepare("
    SELECT 
        rp.tanggal_ikut,
        rp.status,
        u.nama_lengkap,
        u.username
    FROM riwayat_peserta rp
    JOIN peserta p ON rp.peserta_id = p.id
    JOIN users u ON p.user_id = u.id
    WHERE rp.pelatihan_id = ?
    ORDER BY rp.tanggal_ikut DESC
");

$stmtPeserta->bind_param("i", $id);
$stmtPeserta->execute();
$peserta = $stmtPeserta->get_result();
?>

<div class="row">
    <div class="col-md-12 mb-3">
        <h4><?php echo htmlspecialchars($data['nama_pelatihan']); ?></h4>
    </div>
</div>

<table class="table table-bordered mb-4">
    <tr>
        <td width="30%" class="fw-bold">Penyelenggara</td>
        <td><?php echo $data['penyelenggara'] ?: '-'; ?></td>
    </tr>
    <tr>
        <td class="fw-bold">Instruktur</td>
        <td><?php echo $data['nama_instruktur'] ?: '-'; ?></td>
    </tr>
    <tr>
        <td class="fw-bold">Kuota</td>
        <td><?php echo $data['kuota'] ? $data['kuota'] . ' orang' : 'Tidak terbatas'; ?></td>
    </tr>
    <tr>
        <td class="fw-bold">Jumlah Peserta</td>
        <td>
            <span class="badge <?php echo ($data['kuota'] && $peserta->num_rows >= $data['kuota']) ? 'bg-danger' : 'bg-primary'; ?>">
                <?php echo $peserta->num_rows; ?> orang
                <?php if ($data['kuota']): ?>
                    / <?php echo $data['kuota']; ?>
                <?php endif; ?>
            </span>
        </td>
    </tr>
</table>

<h5 class="mb-3">
    <i class="fas fa-users"></i> Daftar Peserta
</h5>

<?php if ($peserta->num_rows > 0): ?>
    <div class="table-responsive">
        <table class="table table-sm table-hover">
            <thead>
                <tr>
                    <th width="40">No</th>
                    <th>Username</th>
                    <th>Nama Lengkap</th>
                    <th>Tanggal Ikut</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; ?>
                <?php while ($p = $peserta->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td><?php echo htmlspecialchars($p['username']); ?></td>
                        <td><?php echo htmlspecialchars($p['nama_lengkap']); ?></td>
                        <td><?php echo format_tanggal($p['tanggal_ikut']); ?></td>
                        <td>
                            <span class="badge bg-<?php
                                                    echo $p['status'] === 'Lulus'
                                                        ? 'success'
                                                        : ($p['status'] === 'Tidak Lulus'
                                                            ? 'danger'
                                                            : 'warning');
                                                    ?>">
                                <?php echo $p['status']; ?>
                            </span>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i>
        Belum ada peserta terdaftar
    </div>
<?php endif; ?>

<?php
$stmt->close();
$stmtPeserta->close();
$conn->close();
?>