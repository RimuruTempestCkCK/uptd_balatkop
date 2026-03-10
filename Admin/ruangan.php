<?php
session_start();
require_once '../config.php';
check_login();

$conn = getConnection();

/*
  ruangan
*/
$ruangan = $conn->query("
    SELECT id, nama_ruangan, kapasitas, keterangan, created_at
    FROM ruangan
    ORDER BY created_at DESC
");
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Ruangan - <?php echo APP_NAME; ?></title>
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
                <h1><i class="fas fa-door-open"></i> Data Ruangan</h1>
                <p class="text-muted">Kelola data ruangan</p>
            </div>

            <?php if ($flash = get_flash()): ?>
                <div class="alert alert-<?php echo $flash['type']; ?> alert-dismissible fade show">
                    <i class="fas fa-info-circle"></i> <?php echo $flash['message']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="content-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5><i class="fas fa-list"></i> Daftar Ruangan</h5>
                    <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#addModal">
                        <i class="fas fa-plus"></i> Tambah Ruangan
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                        <tr>
                            <th width="50">#</th>
                            <th>Nama Ruangan</th>
                            <th>Kapasitas</th>
                            <th>Keterangan</th>
                            <!-- <th>Tanggal Dibuat</th> -->
                            <th width="120" class="text-center">Aksi</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php $no = 1; while ($row = $ruangan->fetch_assoc()): ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <td><strong><?= htmlspecialchars($row['nama_ruangan']); ?></strong></td>
                                <td><?= $row['kapasitas']; ?></td>
                                <td><?= htmlspecialchars($row['keterangan']); ?></td>
                                <!-- <td><?= format_tanggal($row['created_at']); ?></td> -->
                                <td class="text-center">
                                    <button class="btn btn-warning btn-sm"
                                            onclick="editRuangan(<?= $row['id']; ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-danger btn-sm"
                                            onclick="deleteRuangan(<?= $row['id']; ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
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

<!-- ================= MODAL TAMBAH ================= -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form action="proses_ruangan.php?action=add" method="post">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus"></i> Tambah Ruangan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Ruangan</label>
                        <input type="text" name="nama_ruangan" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kapasitas</label>
                        <input type="number" name="kapasitas" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Keterangan</label>
                        <textarea name="keterangan" class="form-control"></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ================= MODAL EDIT ================= -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit"></i> Edit Ruangan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="editModalBody"></div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function editRuangan(id) {
    fetch('get_ruangan.php?id=' + id)
        .then(res => res.text())
        .then(html => {
            document.getElementById('editModalBody').innerHTML = html;
            new bootstrap.Modal(document.getElementById('editModal')).show();
        });
}

function deleteRuangan(id) {
    if (confirm('Yakin ingin menghapus ruangan ini?')) {
        window.location.href = 'proses_ruangan.php?action=delete&id=' + id;
    }
}
</script>

</body>
</html>

<?php $conn->close(); ?>
