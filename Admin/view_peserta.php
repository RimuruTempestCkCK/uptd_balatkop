<?php
session_start();
require_once '../config.php';
check_login();

if (!isset($_GET['id'])) {
    exit('Invalid request');
}

$conn = getConnection();
$id = (int)$_GET['id'];

/* ===== DATA PESERTA ===== */
$stmt = $conn->prepare("
    SELECT 
        p.*,
        pl.nama_pelatihan AS pelatihan_terakhir
    FROM peserta p
    LEFT JOIN riwayat_peserta rp 
        ON rp.peserta_id = p.id
    LEFT JOIN pelatihan pl 
        ON pl.id = rp.pelatihan_id
    WHERE p.id = ?
    ORDER BY rp.tanggal_ikut DESC
    LIMIT 1
");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    exit('Data tidak ditemukan');
}

$data = $result->fetch_assoc();


/* ===== RIWAYAT PELATIHAN PESERTA ===== */
$riwayat = $conn->query("
    SELECT rp.*, pl.nama_pelatihan, pl.penyelenggara
    FROM riwayat_peserta rp
    JOIN pelatihan pl ON rp.pelatihan_id = pl.id
    WHERE rp.peserta_id = $id
    ORDER BY rp.tanggal_ikut DESC
");
?>

<div class="row">
    <div class="col-md-4 text-center mb-4">
        <div class="user-avatar mx-auto mb-3"
            style="width:120px;height:120px;font-size:50px;
             background:linear-gradient(135deg,#4f46e5 0%,#4338ca 100%);">
            <?php echo strtoupper(substr($data['nama_lengkap'], 0, 1)); ?>
        </div>
        <h4><?php echo $data['nama_lengkap']; ?></h4>
        <p class="text-muted"><?php echo $data['email'] ?? '-'; ?></p>
        <span class="badge badge-<?php echo $data['status'] == 'Aktif' ? 'success' : 'danger'; ?> px-3 py-2">
            <?php echo $data['status']; ?>
        </span>
    </div>

    <div class="col-md-8">
        <h5 class="mb-3">
            <i class="fas fa-info-circle text-primary"></i> Informasi Peserta
        </h5>
        <table class="table table-bordered">
            <tr>
                <td width="40%" class="fw-bold">Pelatihan</td>
                <td>
                    <?php echo !empty($data['pelatihan_terakhir'])
                        ? $data['pelatihan_terakhir']
                        : '-'; ?>
                </td>
            </tr>
            <tr>
                <td class="fw-bold">Email</td>
                <td><?php echo $data['email'] ?? '-'; ?></td>
            </tr>
            <tr>
                <td class="fw-bold">Telepon</td>
                <td><?php echo $data['telepon'] ?? '-'; ?></td>
            </tr>
            <tr>
                <td class="fw-bold">Tanggal Lahir</td>
                <td><?php echo format_tanggal($data['tanggal_lahir']); ?></td>
            </tr>
            <tr>
                <td class="fw-bold">Tanggal Daftar</td>
                <td><?php echo format_tanggal($data['created_at']); ?></td>
            </tr>
            <tr>
                <td class="fw-bold">Status</td>
                <td><?php echo $data['status']; ?></td>
            </tr>
        </table>
    </div>
</div>

<hr class="my-4">

<div class="row">
    <div class="col-md-12">
        <h5 class="mb-3">
            <i class="fas fa-graduation-cap text-success"></i> Riwayat Pelatihan
        </h5>
        <?php if ($riwayat->num_rows > 0): ?>
            <div class="list-group">
                <?php while ($r = $riwayat->fetch_assoc()): ?>
                    <div class="list-group-item">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1"><?php echo $r['nama_pelatihan']; ?></h6>
                            <small class="text-muted">
                                <?php echo format_tanggal($r['tanggal_ikut']); ?>
                            </small>
                        </div>
                        <p class="mb-1 text-muted small">
                            <?php echo $r['penyelenggara']; ?>
                        </p>
                        <span class="badge bg-<?php
                                                echo $r['status'] == 'Lulus' ? 'success' : ($r['status'] == 'Tidak Lulus' ? 'danger' : 'warning');
                                                ?>">
                            <?php echo $r['status']; ?>
                        </span>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Belum ada riwayat pelatihan
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$stmt->close();
$conn->close();
?>