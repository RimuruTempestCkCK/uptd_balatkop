<?php
session_start();
require_once '../config.php';
check_login();

if ($_SESSION['role'] !== 'Instruktur') {
    redirect('../logout.php');
}

$conn = getConnection();
$instruktur_id = $_SESSION['user_id'];

/* =======================
   STATISTIK DASHBOARD
======================= */

// Total pelatihan yang diampu instruktur
$total_pelatihan = $conn->query("
    SELECT COUNT(*) AS total
    FROM pelatihan
    WHERE instruktur_id = $instruktur_id
")->fetch_assoc()['total'];

// Total peserta dari semua pelatihan instruktur
$total_peserta = $conn->query("
    SELECT COUNT(rp.id) AS total
    FROM riwayat_peserta rp
    JOIN pelatihan p ON rp.pelatihan_id = p.id
    WHERE p.instruktur_id = $instruktur_id
")->fetch_assoc()['total'];

// Total jadwal pelatihan instruktur
$total_jadwal = $conn->query("
    SELECT COUNT(j.id) AS total
    FROM jadwal j
    JOIN pelatihan p ON j.pelatihan_id = p.id
    WHERE p.instruktur_id = $instruktur_id
")->fetch_assoc()['total'];

// Peserta yang masih proses
$peserta_proses = $conn->query("
    SELECT COUNT(*) AS total
    FROM riwayat_peserta rp
    JOIN pelatihan p ON rp.pelatihan_id = p.id
    WHERE p.instruktur_id = $instruktur_id
    AND rp.status = 'Proses'
")->fetch_assoc()['total'];

/* =======================
   PESERTA TERBARU
======================= */
$recent_employees = $conn->query("
    SELECT 
        ps.nama_lengkap,
        pl.nama_pelatihan,
        rp.tanggal_ikut,
        rp.status
    FROM riwayat_peserta rp
    JOIN peserta ps ON rp.peserta_id = ps.id
    JOIN pelatihan pl ON rp.pelatihan_id = pl.id
    WHERE pl.instruktur_id = $instruktur_id
    ORDER BY rp.created_at DESC
    LIMIT 5
");
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo APP_NAME; ?></title>
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
                    <h1><i class="fas fa-home"></i> Dashboard</h1>
                    <?php
                    $nama = isset($_SESSION['nama_lengkap']) ? $_SESSION['nama_lengkap'] : null;
                    ?>
                    <p class="text-muted">
                        Selamat datang, <?php echo htmlspecialchars($nama ?? 'Guest User', ENT_QUOTES, 'UTF-8'); ?>!
                    </p>
                </div>

                <?php if ($flash = get_flash()): ?>
                    <div class="alert alert-<?php echo $flash['type']; ?> alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle"></i> <?php echo $flash['message']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="row g-4 mb-4">
                    <div class="col-xl-3 col-md-6">
                        <div class="stats-card">
                            <div class="stats-icon bg-primary">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="stats-content">
                                <h3><?php echo $total_peserta; ?></h3>
                                <p>Total Peserta</p>

                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <div class="stats-card">
                            <div class="stats-icon bg-success">
                                <i class="fas fa-briefcase"></i>
                            </div>
                            <div class="stats-content">
                                <h3><?php echo $total_pelatihan; ?></h3>
                                <p>Pelatihan Diampu</p>

                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <div class="stats-card">
                            <div class="stats-icon bg-warning">
                                <i class="fas fa-graduation-cap"></i>
                            </div>
                            <div class="stats-content">
                                <h3><?php echo $total_jadwal; ?></h3>
                                <p>Jadwal Pelatihan</p>

                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <div class="stats-card">
                            <div class="stats-icon bg-info">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                            <div class="stats-content">
                                <h3><?php echo $peserta_proses; ?></h3>
                                <p>Peserta Proses</p>

                            </div>
                        </div>
                    </div>
                </div>

                <div class="content-card">
                    <div class="card-header">
                        <h5><i class="fas fa-user-plus"></i> Karyawan Terbaru</h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Peserta</th>
                                    <th>Pelatihan</th>
                                    <th>Tanggal Daftar</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; ?>
                                <?php while ($row = $recent_employees->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>

                                        <td><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>

                                        <td>
                                            <span class="badge bg-primary">
                                                <?php echo htmlspecialchars($row['nama_pelatihan']); ?>
                                            </span>
                                        </td>

                                        <td><?php echo format_tanggal($row['tanggal_ikut']); ?></td>

                                        <td>
                                            <span class="badge bg-<?php
                                                                    echo $row['status'] === 'Lulus'
                                                                        ? 'success'
                                                                        : ($row['status'] === 'Tidak Lulus'
                                                                            ? 'danger'
                                                                            : 'warning');
                                                                    ?>">
                                                <?php echo $row['status']; ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>

                    </div>
                    <div class="text-center py-3">
                        <a href="karyawan.php" class="btn btn-primary">
                            <i class="fas fa-list"></i> Lihat Semua Karyawan
                        </a>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>