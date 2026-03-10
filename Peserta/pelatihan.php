<?php
session_start();
require_once '../config.php';
check_login();

/* =======================
   VALIDASI ROLE PESERTA
======================= */
if ($_SESSION['role'] !== 'Peserta') {
    set_flash('warning', 'Akses ditolak');
    redirect('../logout.php');
}

$conn = getConnection();


/* =======================
   DATA PESERTA LOGIN
======================= */
$stmtPeserta = $conn->prepare("
    SELECT p.id
    FROM peserta p
    WHERE p.user_id = ?
    LIMIT 1
");
$stmtPeserta->bind_param("i", $_SESSION['user_id']);
$stmtPeserta->execute();
$resultPeserta = $stmtPeserta->get_result();

if ($resultPeserta->num_rows === 0) {
    // Peserta BELUM punya profil → BUKAN alasan logout
    $peserta_id = 0;
} else {
    $peserta_id = $resultPeserta->fetch_assoc()['id'];
}



/* =======================
   DATA PELATIHAN
======================= */
$pelatihan = $conn->query("
    SELECT p.*,
        (
            SELECT COUNT(*)
            FROM riwayat_peserta rp
            WHERE rp.pelatihan_id = p.id
            AND rp.peserta_id = $peserta_id
        ) AS sudah_daftar,
        (
            SELECT COUNT(*)
            FROM riwayat_peserta rp2
            WHERE rp2.pelatihan_id = p.id
        ) AS terisi,
        u.nama_lengkap AS nama_instruktur
    FROM pelatihan p
    LEFT JOIN users u ON p.instruktur_id = u.id
    ORDER BY p.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Pelatihan - <?php echo APP_NAME; ?></title>
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
                    <h1><i class="fas fa-book"></i> Daftar Pelatihan</h1>
                    <p class="text-muted">Pilih pelatihan yang ingin Anda ikuti</p>
                </div>

                <?php if ($flash = get_flash()): ?>
                    <div class="alert alert-<?php echo $flash['type']; ?> alert-dismissible fade show">
                        <i class="fas fa-info-circle"></i> <?php echo $flash['message']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="content-card">
                    <div class="card-header">
                        <h5><i class="fas fa-list"></i> Pelatihan Tersedia</h5>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th width="50">#</th>
                                    <th>Nama Pelatihan</th>
                                    <th>Penyelenggara</th>
                                    <th>Instruktur</th>
                                    <th width="160" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($pelatihan->num_rows > 0): ?>
                                    <?php $no = 1;
                                    while ($row = $pelatihan->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo $no++; ?></td>
                                            <td><strong><?php echo $row['nama_pelatihan']; ?></strong></td>
                                            <td><?php echo $row['penyelenggara'] ?? '-'; ?></td>
                                            <td><?php echo $row['nama_instruktur'] ?? '-'; ?></td>
                                            <td class="text-center">
                                                <?php if ($row['sudah_daftar'] > 0): ?>
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-check"></i> Terdaftar
                                                    </span>
                                                <?php elseif ($row['kuota'] !== null && $row['terisi'] >= $row['kuota']): ?>
                                                    <span class="badge bg-danger">
                                                        <i class="fas fa-times-circle"></i> Kuota Penuh
                                                    </span>
                                                <?php else: ?>
                                                    <form method="post" action="proses_pelatihan.php" class="d-inline">
                                                        <input type="hidden" name="pelatihan_id" value="<?php echo $row['id']; ?>">
                                                        <button class="btn btn-primary btn-sm"
                                                            onclick="return confirm('Daftar pelatihan ini?')">
                                                            <i class="fas fa-sign-in-alt"></i> Daftar
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            <i class="fas fa-info-circle"></i> Tidak ada pelatihan tersedia
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