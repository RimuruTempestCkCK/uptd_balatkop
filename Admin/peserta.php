<?php
session_start();
require_once '../config.php';
check_login();

$conn = getConnection();

/* ===== DATA PELATIHAN (DROPDOWN) ===== */
$pelatihan_list = $conn->query("SELECT id, nama_pelatihan FROM pelatihan ORDER BY nama_pelatihan");

/* ===== SEARCH ===== */
$search = isset($_GET['search']) ? clean_input($_GET['search']) : '';
$where_clause = $search
    ? "WHERE p.nama_lengkap LIKE '%$search%' 
       OR p.email LIKE '%$search%' 
       OR p.telepon LIKE '%$search%'"
    : '';

/* ===== DATA PESERTA ===== */
$query = "
SELECT 
    p.*,
    GROUP_CONCAT(pl.nama_pelatihan SEPARATOR ', ') AS nama_pelatihan
FROM peserta p
LEFT JOIN riwayat_peserta rp ON rp.peserta_id = p.id
LEFT JOIN pelatihan pl ON pl.id = rp.pelatihan_id
$where_clause
GROUP BY p.id
ORDER BY p.created_at DESC
";




$peserta = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Peserta Pelatihan - <?php echo APP_NAME; ?></title>
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
                    <h1><i class="fas fa-users"></i> Data Peserta Pelatihan</h1>
                    <p class="text-muted">Kelola seluruh data peserta pelatihan</p>
                </div>

                <?php if ($flash = get_flash()): ?>
                    <div class="alert alert-<?php echo $flash['type']; ?> alert-dismissible fade show">
                        <i class="fas fa-check-circle"></i> <?php echo $flash['message']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="content-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5><i class="fas fa-list"></i> Daftar Peserta</h5>
                    </div>

                    <div class="p-3">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <form method="GET" class="search-box">
                                    <i class="fas fa-search"></i>
                                    <input type="text" class="form-control" name="search"
                                        placeholder="Cari nama, email, atau telepon..."
                                        value="<?php echo $search; ?>">
                                </form>
                            </div>
                            <div class="col-md-6 text-end">
                                <a href="peserta.php" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-refresh"></i> Reset
                                </a>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Nama Lengkap</th>
                                        <!-- <th>Pelatihan</th> -->
                                        <th>Email</th>
                                        <th>Telepon</th>
                                        <th>Status</th>
                                        <th width="120" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($peserta->num_rows > 0): ?>
                                        <?php while ($row = $peserta->fetch_assoc()): ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        
                                                        <?php echo $row['nama_lengkap']; ?>
                                                    </div>
                                                </td>
                                                <!-- <td>
                                                    <span class="badge bg-primary">
                                                        <?php echo $row['nama_pelatihan'] ?? '-'; ?>
                                                    </span>
                                                </td> -->
                                                <td><?php echo $row['email'] ?? '-'; ?></td>
                                                <td><?php echo $row['telepon'] ?? '-'; ?></td>
                                                <td>
                                                    <span class="badge badge-<?php echo $row['status'] == 'Aktif' ? 'success' : 'danger'; ?>">
                                                        <?php echo $row['status']; ?>
                                                    </span>
                                                </td>
                                                <td class="text-center action-buttons">
                                                    <button class="btn btn-info btn-sm"
                                                        onclick="viewPeserta(<?php echo $row['id']; ?>)">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-4 text-muted">
                                                <i class="fas fa-inbox fa-2x mb-2"></i><br>
                                                Tidak ada data peserta
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- MODAL VIEW PESERTA -->
    <div class="modal fade" id="viewModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-eye"></i> Detail Peserta
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="viewModalBody">
                    <!-- Diisi oleh view_peserta.php -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function viewPeserta(id) {
            fetch('view_peserta.php?id=' + id)
                .then(r => r.text())
                .then(html => {
                    document.getElementById('viewModalBody').innerHTML = html;
                    new bootstrap.Modal(document.getElementById('viewModal')).show();
                });
        }
    </script>
</body>

</html>
<?php $conn->close(); ?>