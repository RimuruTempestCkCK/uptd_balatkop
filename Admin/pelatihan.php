<?php
session_start();
require_once '../config.php';
check_login();

$conn = getConnection();

/*
  riwayat_pelatihan ❌
  riwayat_peserta   ✅
*/
$pelatihan_list = $conn->query("
    SELECT 
        p.id,
        p.nama_pelatihan,
        p.penyelenggara,
        p.kuota,
        p.instruktur_id,
        u.nama_lengkap AS nama_instruktur,
        COUNT(rp.id) AS jumlah_peserta
    FROM pelatihan p
    LEFT JOIN users u ON p.instruktur_id = u.id
    LEFT JOIN riwayat_peserta rp ON p.id = rp.pelatihan_id
    GROUP BY p.id
    ORDER BY p.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Pelatihan - <?php echo APP_NAME; ?></title>
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
                    <h1><i class="fas fa-graduation-cap"></i> Data Pelatihan</h1>
                    <p class="text-muted">Kelola program pelatihan</p>
                </div>

                <?php if ($flash = get_flash()): ?>
                    <div class="alert alert-<?php echo $flash['type']; ?> alert-dismissible fade show">
                        <i class="fas fa-info-circle"></i> <?php echo $flash['message']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="content-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5><i class="fas fa-list"></i> Daftar Pelatihan</h5>
                        <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#addModal">
                            <i class="fas fa-plus"></i> Tambah Pelatihan
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th width="50">#</th>
                                    <th>Nama Pelatihan</th>
                                    <th>Penyelenggara</th>
                                    <th>Instruktur</th>
                                    <th width="120" class="text-center">Kuota</th>
                                    <th width="120" class="text-center">Peserta</th>
                                    <th width="150" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                while ($row = $pelatihan_list->fetch_assoc()):
                                ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><strong><?php echo $row['nama_pelatihan']; ?></strong></td>
                                        <td><?php echo $row['penyelenggara'] ?? '-'; ?></td>
                                        <td><?php echo $row['nama_instruktur'] ?? '-'; ?></td>
                                        <td class="text-center">
                                            <?php if ($row['kuota']): ?>
                                                <span class="badge <?php echo $row['jumlah_peserta'] >= $row['kuota'] ? 'bg-danger' : 'bg-success'; ?>">
                                                    <?php echo $row['jumlah_peserta']; ?> / <?php echo $row['kuota']; ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">
                                                    <?php echo $row['jumlah_peserta']; ?> / ∞
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-primary">
                                                <?php echo $row['jumlah_peserta']; ?> orang
                                            </span>
                                        </td>
                                        <td class="text-center action-buttons">
                                            <button class="btn btn-info btn-sm"
                                                onclick="viewPelatihan(<?php echo $row['id']; ?>)">
                                                <i class="fas fa-eye"></i>
                                            </button>

                                            <button class="btn btn-warning btn-sm"
                                                onclick="editPelatihan(<?php echo $row['id']; ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>

                                            <?php if ($row['jumlah_peserta'] == 0): ?>
                                                <button class="btn btn-danger btn-sm"
                                                    onclick="deletePelatihan(<?php echo $row['id']; ?>, '<?php echo $row['nama_pelatihan']; ?>')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            <?php else: ?>
                                                <button class="btn btn-secondary btn-sm" disabled>
                                                    <i class="fas fa-lock"></i>
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Modal Tambah Pelatihan -->
    <div class="modal fade" id="addModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <form action="proses_pelatihan.php" method="post">
                    <input type="hidden" name="action" value="add">

                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-plus"></i> Tambah Pelatihan
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nama Pelatihan *</label>
                            <input type="text" name="nama_pelatihan" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Penyelenggara</label>
                            <input type="text" name="penyelenggara" class="form-control">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Instruktur</label>
                            <select name="instruktur_id" class="form-select" required>
                                <option value="">Pilih Instruktur</option>
                                <?php
                                $instruktur = $conn->query("SELECT id, nama_lengkap FROM users WHERE role = 'Instruktur' ORDER BY nama_lengkap ASC");
                                while ($row = $instruktur->fetch_assoc()):
                                ?>
                                    <option value="<?= $row['id']; ?>"><?= htmlspecialchars($row['nama_lengkap']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Kuota Peserta</label>
                            <input type="number" name="kuota" class="form-control" min="1" placeholder="Kosongkan = tidak terbatas">
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Edit Pelatihan -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-edit"></i> Edit Pelatihan
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body" id="editModalBody">
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-spinner fa-spin"></i> Memuat data...
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal View Pelatihan -->
    <div class="modal fade" id="viewModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-eye"></i> Detail Pelatihan
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body" id="viewModalBody">
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-spinner fa-spin"></i> Memuat data...
                    </div>
                </div>
            </div>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editPelatihan(id) {
            fetch('get_pelatihan.php?id=' + id)
                .then(res => res.text())
                .then(html => {
                    document.getElementById('editModalBody').innerHTML = html;
                    new bootstrap.Modal(document.getElementById('editModal')).show();
                });
        }

        function viewPelatihan(id) {
            fetch('view_pelatihan.php?id=' + id)
                .then(res => res.text())
                .then(html => {
                    document.getElementById('viewModalBody').innerHTML = html;
                    new bootstrap.Modal(document.getElementById('viewModal')).show();
                });
        }

        function deletePelatihan(id, nama) {
            if (confirm('Hapus pelatihan "' + nama + '" ?')) {
                window.location.href = 'proses_pelatihan.php?action=delete&id=' + id;
            }
        }
    </script>

</body>

</html>

<?php $conn->close(); ?>