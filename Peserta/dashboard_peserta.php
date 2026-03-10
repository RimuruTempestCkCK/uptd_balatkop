<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Peserta') {
    header('Location: ../login.php');
    exit;
}

$conn = getConnection();
$user_id = $_SESSION['user_id'];

/* ================= DATA PESERTA LOGIN ================= */
$peserta_q = $conn->query("
    SELECT id, nama_lengkap 
    FROM peserta 
    WHERE user_id = '$user_id'
    LIMIT 1
");
$peserta = $peserta_q->fetch_assoc();
$peserta_id = $peserta['id'] ?? 0;


/* ================= CEK KELENGKAPAN PROFIL ================= */
$profil_q = $conn->query("
    SELECT 
        nama_lengkap,
        email,
        telepon,
        tanggal_lahir
    FROM peserta
    WHERE id = '$peserta_id'
    LIMIT 1
");

$profil = $profil_q->fetch_assoc();

$profil_tidak_lengkap = false;

$wajib = ['nama_lengkap', 'email', 'telepon', 'tanggal_lahir'];
foreach ($wajib as $field) {
    if (empty($profil[$field])) {
        $profil_tidak_lengkap = true;
        break;
    }
}


/* ================= STATISTIK ================= */

// Total peserta aktif
$q1 = $conn->query("SELECT COUNT(*) AS total FROM peserta WHERE status = 'Aktif'");
$total_karyawan = $q1->fetch_assoc()['total'] ?? 0;

// Total pelatihan yang diikuti peserta
$q2 = $conn->query("
    SELECT COUNT(*) AS total 
    FROM riwayat_peserta 
    WHERE peserta_id = '$peserta_id'
");
$total_jabatan = $q2->fetch_assoc()['total'] ?? 0;

// Total program pelatihan tersedia
$q3 = $conn->query("SELECT COUNT(*) AS total FROM pelatihan");
$total_pelatihan = $q3->fetch_assoc()['total'] ?? 0;

// Jadwal hari ini (offline)
$q4 = $conn->query("
    SELECT COUNT(*) AS total
    FROM jadwal j
    JOIN riwayat_peserta r ON j.pelatihan_id = r.pelatihan_id
    WHERE r.peserta_id = '$peserta_id'
    AND j.tanggal = CURDATE()
");
$hadir_hari_ini = $q4->fetch_assoc()['total'] ?? 0;

/* ================= RIWAYAT PELATIHAN ================= */
$recent_employees = $conn->query("
    SELECT 
        p.nama_pelatihan,
        r.tanggal_ikut,
        r.status,
        e.nilai_akhir
    FROM riwayat_peserta r
    JOIN pelatihan p ON r.pelatihan_id = p.id
    LEFT JOIN evaluasi_pelatihan e 
        ON e.peserta_id = r.peserta_id 
        AND e.pelatihan_id = r.pelatihan_id
    WHERE r.peserta_id = '$peserta_id'
    ORDER BY r.created_at DESC
    LIMIT 5
");

$conn->close();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
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
                    <h1><i class="fas fa-home"></i> Dashboard Peserta</h1>
                    <!-- <p class="text-muted">
                        Selamat datang, <?php echo htmlspecialchars($peserta['nama_lengkap'] ?? 'Guest'); ?>!
                    </p> -->
                </div>
                <?php if ($profil_tidak_lengkap): ?>
                    <div class="alert alert-warning d-flex align-items-center mb-4">
                        <i class="fas fa-exclamation-triangle fa-lg me-3"></i>
                        <div>
                            <strong>Profil belum lengkap!</strong><br>
                            Silakan lengkapi data profil Anda agar dapat mengikuti pelatihan dengan lancar.
                            <br>
                            <a href="edit_profil.php" class="btn btn-sm btn-warning mt-2">
                                <i class="fas fa-user-edit"></i> Lengkapi Profil
                            </a>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="row g-4 mb-4">
                    <div class="col-xl-3 col-md-6">
                        <div class="stats-card">
                            <div class="stats-icon bg-primary"><i class="fas fa-users"></i></div>
                            <div class="stats-content">
                                <h3><?php echo $total_karyawan; ?></h3>
                                <p>Peserta Aktif</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <div class="stats-card">
                            <div class="stats-icon bg-success"><i class="fas fa-book"></i></div>
                            <div class="stats-content">
                                <h3><?php echo $total_jabatan; ?></h3>
                                <p>Pelatihan Diikuti</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <div class="stats-card">
                            <div class="stats-icon bg-warning"><i class="fas fa-graduation-cap"></i></div>
                            <div class="stats-content">
                                <h3><?php echo $total_pelatihan; ?></h3>
                                <p>Total Program</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <div class="stats-card">
                            <div class="stats-icon bg-info"><i class="fas fa-calendar-check"></i></div>
                            <div class="stats-content">
                                <h3><?php echo $hadir_hari_ini; ?></h3>
                                <p>Jadwal Hari Ini</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="content-card">
                    <div class="card-header">
                        <h5><i class="fas fa-history"></i> Riwayat Pelatihan</h5>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Nama Pelatihan</th>
                                    <th>Tanggal Ikut</th>
                                    <th>Status</th>
                                    <th>Nilai Akhir</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $recent_employees->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $row['nama_pelatihan']; ?></td>
                                        <td><?php echo format_tanggal($row['tanggal_ikut']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php
                                                                    echo $row['status'] == 'Lulus' ? 'success' : ($row['status'] == 'Proses' ? 'warning' : 'danger'); ?>">
                                                <?php echo $row['status']; ?>
                                            </span>
                                        </td>
                                        <td><?php echo $row['nilai_akhir'] ?? '-'; ?></td>
                                    </tr>
                                <?php endwhile; ?>
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