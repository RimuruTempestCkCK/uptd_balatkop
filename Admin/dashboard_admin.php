<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header('Location: login.php');
    exit;
}

$conn = getConnection();

/* ===== STATISTIK DASHBOARD SESUAI DATABASE ===== */

// Total user (users)
$total_users = $conn->query("SELECT COUNT(*) AS total FROM users")->fetch_assoc()['total'];

// Total peserta (peserta)
$total_peserta = $conn->query("SELECT COUNT(*) AS total FROM peserta")->fetch_assoc()['total'];

// Total instruktur (users)
$total_instruktur = $conn->query("SELECT COUNT(*) AS total FROM users WHERE role='Instruktur'")->fetch_assoc()['total'];

// Total pelatihan
$total_pelatihan = $conn->query("SELECT COUNT(*) AS total FROM pelatihan")->fetch_assoc()['total'];

// Total jadwal
$total_jadwal = $conn->query("SELECT COUNT(*) AS total FROM jadwal")->fetch_assoc()['total'];

// Total evaluasi
$total_evaluasi = $conn->query("SELECT COUNT(*) AS total FROM evaluasi_pelatihan")->fetch_assoc()['total'];

// Total peserta lulus
$total_lulus = $conn->query("SELECT COUNT(*) AS total FROM riwayat_peserta WHERE status='Lulus'")->fetch_assoc()['total'];

// Total peserta tidak lulus
$total_tidak_lulus = $conn->query("SELECT COUNT(*) AS total FROM riwayat_peserta WHERE status='Tidak Lulus'")->fetch_assoc()['total'];

/* ===== USER TERBARU (users) ===== */
$recent_users = $conn->query("
    SELECT nama_lengkap, username, role, created_at
    FROM users
    ORDER BY created_at DESC
    LIMIT 5
");

/* ===== PELATIHAN TERBARU ===== */
$recent_pelatihan = $conn->query("
    SELECT p.nama_pelatihan, p.penyelenggara, u.nama_lengkap AS instruktur, p.created_at
    FROM pelatihan p
    LEFT JOIN users u ON p.instruktur_id = u.id
    ORDER BY p.created_at DESC
    LIMIT 5
");

$conn->close();
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
                <p class="text-muted">
                    Selamat datang, <?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?>!
                </p>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-xl-3 col-md-6">
                    <div class="stats-card">
                        <div class="stats-icon bg-primary">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stats-content">
                            <h3><?php echo $total_users; ?></h3>
                            <p>Total User</p>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="stats-card">
                        <div class="stats-icon bg-success">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <div class="stats-content">
                            <h3><?php echo $total_peserta; ?></h3>
                            <p>Total Peserta</p>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="stats-card">
                        <div class="stats-icon bg-info">
                            <i class="fas fa-chalkboard-teacher"></i>
                        </div>
                        <div class="stats-content">
                            <h3><?php echo $total_instruktur; ?></h3>
                            <p>Total Instruktur</p>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="stats-card">
                        <div class="stats-icon bg-warning">
                            <i class="fas fa-book"></i>
                        </div>
                        <div class="stats-content">
                            <h3><?php echo $total_pelatihan; ?></h3>
                            <p>Total Pelatihan</p>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="stats-card">
                        <div class="stats-icon bg-secondary">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div class="stats-content">
                            <h3><?php echo $total_jadwal; ?></h3>
                            <p>Total Jadwal</p>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="stats-card">
                        <div class="stats-icon bg-dark">
                            <i class="fas fa-star"></i>
                        </div>
                        <div class="stats-content">
                            <h3><?php echo $total_evaluasi; ?></h3>
                            <p>Total Evaluasi</p>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="stats-card">
                        <div class="stats-icon bg-success">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stats-content">
                            <h3><?php echo $total_lulus; ?></h3>
                            <p>Peserta Lulus</p>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="stats-card">
                        <div class="stats-icon bg-danger">
                            <i class="fas fa-times-circle"></i>
                        </div>
                        <div class="stats-content">
                            <h3><?php echo $total_tidak_lulus; ?></h3>
                            <p>Peserta Tidak Lulus</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="content-card">
                        <div class="card-header">
                            <h5><i class="fas fa-user-plus"></i> User Terbaru</h5>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Nama Lengkap</th>
                                        <th>Username</th>
                                        <th>Role</th>
                                        <th>Tanggal Daftar</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $recent_users->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                                            <td><strong><?php echo htmlspecialchars($row['username']); ?></strong></td>
                                            <td>
                                                <span class="badge bg-primary"><?php echo $row['role']; ?></span>
                                            </td>
                                            <td><?php echo date('d M Y', strtotime($row['created_at'])); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="content-card">
                        <div class="card-header">
                            <h5><i class="fas fa-book"></i> Pelatihan Terbaru</h5>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Nama Pelatihan</th>
                                        <th>Penyelenggara</th>
                                        <th>Instruktur</th>
                                        <th>Tanggal Buat</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $recent_pelatihan->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['nama_pelatihan']); ?></td>
                                            <td><?php echo htmlspecialchars($row['penyelenggara'] ?: '-'); ?></td>
                                            <td><?php echo htmlspecialchars($row['instruktur'] ?: '-'); ?></td>
                                            <td><?php echo date('d M Y', strtotime($row['created_at'])); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
