<?php
session_start();
require_once '../config.php';
check_login();

$conn = getConnection();

// Ambil semua pelatihan beserta jumlah peserta dan jumlah lulus
$pelatihan = $conn->query("
    SELECT 
        p.id,
        p.nama_pelatihan,
        p.penyelenggara,
        u.nama_lengkap AS instruktur,
        p.created_at,
        COUNT(rp.id) AS total_peserta,
        SUM(CASE WHEN rp.status = 'Lulus' THEN 1 ELSE 0 END) AS total_lulus,
        SUM(CASE WHEN rp.status = 'Tidak Lulus' THEN 1 ELSE 0 END) AS total_tidak_lulus
    FROM pelatihan p
    LEFT JOIN users u ON p.instruktur_id = u.id
    LEFT JOIN riwayat_peserta rp ON p.id = rp.pelatihan_id
    GROUP BY p.id
    ORDER BY p.created_at DESC
");

// Pelatihan yang sudah selesai (ada peserta lulus/tidak lulus, tidak hanya 'Proses')
$pelatihan_selesai = $conn->query("
    SELECT 
        p.id,
        p.nama_pelatihan,
        p.penyelenggara,
        u.nama_lengkap AS instruktur,
        p.created_at,
        COUNT(rp.id) AS total_peserta,
        SUM(CASE WHEN rp.status = 'Lulus' THEN 1 ELSE 0 END) AS total_lulus,
        SUM(CASE WHEN rp.status = 'Tidak Lulus' THEN 1 ELSE 0 END) AS total_tidak_lulus
    FROM pelatihan p
    LEFT JOIN users u ON p.instruktur_id = u.id
    LEFT JOIN riwayat_peserta rp ON p.id = rp.pelatihan_id
    WHERE rp.status IN ('Lulus','Tidak Lulus')
    GROUP BY p.id
    HAVING total_lulus > 0 OR total_tidak_lulus > 0
    ORDER BY p.created_at DESC
");

$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Pelatihan - <?php echo APP_NAME; ?></title>
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
                    <h1><i class="fas fa-file-alt"></i> Laporan Pelatihan</h1>
                    <p class="text-muted">Rekap semua pelatihan dan pelatihan yang telah selesai</p>
                </div>

                <div class="content-card mb-4">
                    <div class="card-header">
                        <h5><i class="fas fa-list"></i> Semua Pelatihan</h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th width="50">#</th>
                                    <th>Nama Pelatihan</th>
                                    <th>Penyelenggara</th>
                                    <th>Instruktur</th>
                                    <th>Tanggal Buat</th>
                                    <th class="text-center">Peserta</th>
                                    <th class="text-center">Lulus</th>
                                    <th class="text-center">Tidak Lulus</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; foreach ($pelatihan as $row): ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td><strong><?= htmlspecialchars($row['nama_pelatihan']); ?></strong></td>
                                        <td><?= htmlspecialchars($row['penyelenggara'] ?: '-'); ?></td>
                                        <td><?= htmlspecialchars($row['instruktur'] ?: '-'); ?></td>
                                        <td><?= format_tanggal($row['created_at']); ?></td>
                                        <td class="text-center">
                                            <span class="badge bg-primary"><?= $row['total_peserta']; ?></span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-success"><?= $row['total_lulus'] ?: 0; ?></span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-danger"><?= $row['total_tidak_lulus'] ?: 0; ?></span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if ($no === 1): ?>
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">
                                            <i class="fas fa-info-circle"></i> Tidak ada data pelatihan
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="content-card">
                    <div class="card-header">
                        <h5><i class="fas fa-check-circle"></i> Pelatihan Telah Selesai</h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th width="50">#</th>
                                    <th>Nama Pelatihan</th>
                                    <th>Penyelenggara</th>
                                    <th>Instruktur</th>
                                    <th>Tanggal Buat</th>
                                    <th class="text-center">Peserta</th>
                                    <th class="text-center">Lulus</th>
                                    <th class="text-center">Tidak Lulus</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; foreach ($pelatihan_selesai as $row): ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td><strong><?= htmlspecialchars($row['nama_pelatihan']); ?></strong></td>
                                        <td><?= htmlspecialchars($row['penyelenggara'] ?: '-'); ?></td>
                                        <td><?= htmlspecialchars($row['instruktur'] ?: '-'); ?></td>
                                        <td><?= format_tanggal($row['created_at']); ?></td>
                                        <td class="text-center">
                                            <span class="badge bg-primary"><?= $row['total_peserta']; ?></span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-success"><?= $row['total_lulus'] ?: 0; ?></span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-danger"><?= $row['total_tidak_lulus'] ?: 0; ?></span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if ($no === 1): ?>
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">
                                            <i class="fas fa-info-circle"></i> Tidak ada pelatihan selesai
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
