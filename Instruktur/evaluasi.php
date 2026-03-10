<?php
session_start();
require_once '../config.php';
check_login();

if ($_SESSION['role'] !== 'Instruktur') {
    set_flash('warning', 'Akses ditolak');
    redirect('../logout.php');
}

$conn = getConnection();
$instruktur_id = $_SESSION['user_id'];

// Ambil pelatihan yang diampu instruktur
$pelatihan = $conn->query("
    SELECT id, nama_pelatihan
    FROM pelatihan
    WHERE instruktur_id = $instruktur_id
    ORDER BY nama_pelatihan
");

// Pilih pelatihan
$pelatihan_id = isset($_GET['pelatihan_id']) ? (int)$_GET['pelatihan_id'] : 0;

// Ambil peserta pelatihan
$peserta = [];
if ($pelatihan_id) {
    $q = $conn->prepare("
        SELECT p.id, p.nama_lengkap, rp.status
        FROM riwayat_peserta rp
        JOIN peserta p ON rp.peserta_id = p.id
        WHERE rp.pelatihan_id = ? 
        ORDER BY p.nama_lengkap
    ");
    $q->bind_param("i", $pelatihan_id);
    $q->execute();
    $peserta = $q->get_result();
}

// Jika form submit evaluasi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['peserta_id'])) {
    $peserta_id = (int)$_POST['peserta_id'];
    $pelatihan_id = (int)$_POST['pelatihan_id'];
    $nilai_kehadiran = (int)$_POST['nilai_kehadiran'];
    $nilai_partisipasi = (int)$_POST['nilai_partisipasi'];
    $nilai_praktik = (int)$_POST['nilai_praktik'];
    $nilai_sikap = (int)$_POST['nilai_sikap'];
    $keterangan = clean_input($_POST['keterangan'] ?? '');

    // Hitung nilai akhir (rata-rata)
    $nilai_akhir = round(($nilai_kehadiran + $nilai_partisipasi + $nilai_praktik + $nilai_sikap) / 4, 2);

    // Cek sudah ada evaluasi
    $cek = $conn->prepare("SELECT id FROM evaluasi_pelatihan WHERE peserta_id=? AND pelatihan_id=? AND instruktur_id=?");
    $cek->bind_param("iii", $peserta_id, $pelatihan_id, $instruktur_id);
    $cek->execute();
    $cek->store_result();

    if ($cek->num_rows > 0) {
        // Update
        $stmt = $conn->prepare("
            UPDATE evaluasi_pelatihan SET 
                nilai_kehadiran=?, nilai_partisipasi=?, nilai_praktik=?, nilai_sikap=?, nilai_akhir=?, keterangan=?
            WHERE peserta_id=? AND pelatihan_id=? AND instruktur_id=?
        ");
        $stmt->bind_param(
            "iiiidsiii",
            $nilai_kehadiran, $nilai_partisipasi, $nilai_praktik, $nilai_sikap, $nilai_akhir, $keterangan,
            $peserta_id, $pelatihan_id, $instruktur_id
        );
        $stmt->execute();
        set_flash('success', 'Evaluasi berhasil diperbarui!');
    } else {
        // Insert
        $stmt = $conn->prepare("
            INSERT INTO evaluasi_pelatihan
            (peserta_id, pelatihan_id, instruktur_id, nilai_kehadiran, nilai_partisipasi, nilai_praktik, nilai_sikap, nilai_akhir, keterangan)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param(
            "iiiiiiids",
            $peserta_id, $pelatihan_id, $instruktur_id,
            $nilai_kehadiran, $nilai_partisipasi, $nilai_praktik, $nilai_sikap, $nilai_akhir, $keterangan
        );
        $stmt->execute();
        set_flash('success', 'Evaluasi berhasil disimpan!');
    }
    $cek->close();
    $stmt->close();

    // Update status di riwayat_peserta
    $status = ($nilai_akhir >= 60) ? 'Lulus' : 'Tidak Lulus';
    $stmt_status = $conn->prepare("UPDATE riwayat_peserta SET status = ? WHERE peserta_id = ? AND pelatihan_id = ?");
    $stmt_status->bind_param("sii", $status, $peserta_id, $pelatihan_id);
    $stmt_status->execute();
    $stmt_status->close();

    redirect("evaluasi.php?pelatihan_id=$pelatihan_id");
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Evaluasi Peserta - <?php echo APP_NAME; ?></title>
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
                <h1><i class="fas fa-star"></i> Evaluasi Peserta</h1>
                <p class="text-muted">Beri nilai evaluasi untuk peserta pelatihan</p>
            </div>
            <?php if ($flash = get_flash()): ?>
                <div class="alert alert-<?php echo $flash['type']; ?> alert-dismissible fade show">
                    <i class="fas fa-info-circle"></i> <?php echo $flash['message']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="content-card">
                <div class="card-header">
                    <form method="get" class="row g-2 align-items-center">
                        <div class="col-auto">
                            <label for="pelatihan_id" class="form-label mb-0">Pilih Pelatihan:</label>
                        </div>
                        <div class="col-auto">
                            <select name="pelatihan_id" id="pelatihan_id" class="form-select" onchange="this.form.submit()" required>
                                <option value="">-- Pilih Pelatihan --</option>
                                <?php while ($p = $pelatihan->fetch_assoc()): ?>
                                    <option value="<?= $p['id']; ?>" <?= $p['id'] == $pelatihan_id ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars($p['nama_pelatihan']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </form>
                </div>

                <?php if ($pelatihan_id && $peserta && $peserta->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th width="50">#</th>
                                    <th>Nama Peserta</th>
                                    <th>Status</th>
                                    <th>Nilai</th>
                                    <th width="120" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; foreach ($peserta as $ps): ?>
                                    <?php
                                    // Ambil evaluasi jika ada
                                    $ev = $conn->query("SELECT * FROM evaluasi_pelatihan WHERE peserta_id={$ps['id']} AND pelatihan_id=$pelatihan_id AND instruktur_id=$instruktur_id")->fetch_assoc();
                                    ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td><?= htmlspecialchars($ps['nama_lengkap']); ?></td>
                                        <td>
                                            <span class="badge bg-<?= $ps['status'] == 'Lulus' ? 'success' : ($ps['status'] == 'Tidak Lulus' ? 'danger' : 'warning'); ?>">
                                                <?= $ps['status']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($ev): ?>
                                                <span class="badge bg-primary">Akhir: <?= $ev['nilai_akhir']; ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">Belum dinilai</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-info btn-sm" onclick="showEvaluasiModal(<?= $ps['id']; ?>)">
                                                <i class="fas fa-star"></i> Nilai
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php elseif ($pelatihan_id): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Tidak ada peserta pada pelatihan ini.
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

<!-- Modal Evaluasi -->
<div class="modal fade" id="evaluasiModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-star"></i> Form Evaluasi Peserta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="evaluasiModalBody">
                <div class="text-center text-muted py-4">
                    <i class="fas fa-spinner fa-spin"></i> Memuat form...
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function showEvaluasiModal(peserta_id) {
    var pelatihan_id = document.getElementById('pelatihan_id').value;
    fetch('form_evaluasi.php?peserta_id=' + peserta_id + '&pelatihan_id=' + pelatihan_id)
        .then(res => res.text())
        .then(html => {
            document.getElementById('evaluasiModalBody').innerHTML = html;
            new bootstrap.Modal(document.getElementById('evaluasiModal')).show();
        });
}
</script>
</body>
</html>
<?php
$conn->close();
?>
