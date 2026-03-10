<?php
session_start();
require_once '../config.php';
check_login();

if ($_SESSION['role'] !== 'Peserta') {
    set_flash('warning', 'Akses ditolak');
    redirect('../logout.php');
}

$conn = getConnection();

// Ambil ID peserta dari user_id session
$stmtPeserta = $conn->prepare("SELECT id FROM peserta WHERE user_id = ? LIMIT 1");
$stmtPeserta->bind_param("i", $_SESSION['user_id']);
$stmtPeserta->execute();
$resultPeserta = $stmtPeserta->get_result();

if ($resultPeserta->num_rows === 0) {
    $peserta_id = 0;
} else {
    $peserta_id = $resultPeserta->fetch_assoc()['id'];
}

// Ambil riwayat pelatihan yang sudah selesai (status Lulus/Tidak Lulus)
$riwayat = [];
if ($peserta_id) {
    $q = $conn->prepare("
        SELECT 
            rp.tanggal_ikut,
            rp.status,
            p.nama_pelatihan,
            p.penyelenggara,
            u.nama_lengkap AS instruktur
        FROM riwayat_peserta rp
        JOIN pelatihan p ON rp.pelatihan_id = p.id
        LEFT JOIN users u ON p.instruktur_id = u.id
        WHERE rp.peserta_id = ? AND rp.status IN ('Lulus','Tidak Lulus')
        ORDER BY rp.tanggal_ikut DESC
    ");
    $q->bind_param("i", $peserta_id);
    $q->execute();
    $riwayat = $q->get_result();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pelatihan Saya - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    <div class="container-fluid">
        <div class="row">
            <?php include '../includes/sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="page-header">
                    <h1><i class="fas fa-history"></i> Riwayat Pelatihan</h1>
                    <p class="text-muted">Pelatihan yang telah selesai Anda ikuti</p>
                </div>

                <div class="content-card">
                    <div class="card-header">
                        <h5><i class="fas fa-list"></i> Daftar Riwayat Pelatihan</h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th width="50">#</th>
                                    <th>Nama Pelatihan</th>
                                    <th>Penyelenggara</th>
                                    <th>Instruktur</th>
                                    <th>Tanggal Ikut</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($riwayat && $riwayat->num_rows > 0): ?>
                                    <?php $no = 1; while ($row = $riwayat->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= $no++; ?></td>
                                            <td><?= htmlspecialchars($row['nama_pelatihan']); ?></td>
                                            <td><?= htmlspecialchars($row['penyelenggara'] ?: '-'); ?></td>
                                            <td><?= htmlspecialchars($row['instruktur'] ?: '-'); ?></td>
                                            <td><?= format_tanggal($row['tanggal_ikut']); ?></td>
                                            <td>
                                                <span class="badge bg-<?= $row['status'] == 'Lulus' ? 'success' : 'danger'; ?>">
                                                    <?= $row['status']; ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            <i class="fas fa-info-circle"></i> Belum ada riwayat pelatihan selesai
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
$stmtPeserta->close();
$conn->close();
?>
