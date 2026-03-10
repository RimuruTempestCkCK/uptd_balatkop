<?php
session_start();
require_once '../config.php';
check_login();

/* =======================
   VALIDASI ROLE INSTRUKTUR
======================= */
if ($_SESSION['role'] !== 'Instruktur') {
    set_flash('warning', 'Akses ditolak');
    redirect('../logout.php');
}

$conn = getConnection();

/* =======================
   AMBIL ID INSTRUKTUR (users.id)
======================= */
$instruktur_id = (int) $_SESSION['user_id'];

$pelatihanData = [];

/* =======================
   PELATIHAN + JADWAL YANG DIAJAR INSTRUKTUR
======================= */
$stmt = $conn->prepare("
    SELECT
        pl.id AS pelatihan_id,
        pl.nama_pelatihan,
        pl.penyelenggara,
        j.tanggal,
        j.jam_mulai,
        j.jam_selesai,
        r.nama_ruangan AS lokasi,
        j.keterangan
    FROM pelatihan pl
    LEFT JOIN jadwal j ON j.pelatihan_id = pl.id
    LEFT JOIN ruangan r ON j.ruangan_id = r.id
    WHERE pl.instruktur_id = ?
    ORDER BY pl.nama_pelatihan, j.tanggal ASC, j.jam_mulai ASC
");

$stmt->bind_param("i", $instruktur_id);
$stmt->execute();
$result = $stmt->get_result();

/* =======================
   KELOMPOKKAN JADWAL PER PELATIHAN
======================= */
while ($row = $result->fetch_assoc()) {
    $pid = $row['pelatihan_id'];

    if (!isset($pelatihanData[$pid])) {
        $pelatihanData[$pid] = [
            'nama_pelatihan' => $row['nama_pelatihan'],
            'penyelenggara'  => $row['penyelenggara'],
            'jadwal'         => []
        ];
    }

    if ($row['tanggal']) {
        $pelatihanData[$pid]['jadwal'][] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Mengajar - <?php echo APP_NAME; ?></title>
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
                <h1><i class="fas fa-calendar-alt"></i> Jadwal Mengajar</h1>
                <p class="text-muted">Daftar jadwal pelatihan yang Anda ampu</p>
            </div>

            <?php if (empty($pelatihanData)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    Anda belum memiliki jadwal pelatihan
                </div>
            <?php else: ?>

                <?php foreach ($pelatihanData as $pelatihan): ?>
                    <div class="content-card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <?php echo htmlspecialchars($pelatihan['nama_pelatihan']); ?>
                            </h5>
                            <small class="text-muted">
                                Penyelenggara: <?php echo htmlspecialchars($pelatihan['penyelenggara'] ?: '-'); ?>
                            </small>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th width="50">#</th>
                                        <th>Tanggal</th>
                                        <th>Jam</th>
                                        <th>Ruangan</th>
                                        <th>Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($pelatihan['jadwal'])): ?>
                                        <?php $no = 1; ?>
                                        <?php foreach ($pelatihan['jadwal'] as $j): ?>
                                            <tr>
                                                <td><?= $no++; ?></td>
                                                <td><?= format_tanggal($j['tanggal']); ?></td>
                                                <td>
                                                    <?= substr($j['jam_mulai'], 0, 5); ?> -
                                                    <?= substr($j['jam_selesai'], 0, 5); ?>
                                                </td>
                                                <td><?= htmlspecialchars($j['lokasi']); ?></td>
                                                <td><?= htmlspecialchars($j['keterangan'] ?: '-'); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-3">
                                                Jadwal belum tersedia
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endforeach; ?>

            <?php endif; ?>

        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

<?php
$stmt->close();
$conn->close();
?>
