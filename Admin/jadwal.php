<?php
session_start();
require_once '../config.php';
check_login();

$conn = getConnection();

$jadwal_list = $conn->query("
    SELECT 
        j.id,
        j.tanggal,
        j.jam_mulai,
        j.jam_selesai,
        IFNULL(r.nama_ruangan, '-') AS nama_ruangan,
        j.keterangan,
        p.nama_pelatihan
    FROM jadwal j
    JOIN pelatihan p ON j.pelatihan_id = p.id
    LEFT JOIN ruangan r ON j.ruangan_id = r.id
    ORDER BY j.tanggal DESC, j.jam_mulai ASC
");

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Jadwal - <?php echo APP_NAME; ?></title>
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
                    <h1><i class="fas fa-calendar-alt"></i> Jadwal Pelatihan</h1>
                    <p class="text-muted">Kelola jadwal pelaksanaan pelatihan</p>
                </div>

                <?php if ($flash = get_flash()): ?>
                    <div class="alert alert-<?php echo $flash['type']; ?> alert-dismissible fade show">
                        <i class="fas fa-info-circle"></i> <?php echo $flash['message']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="content-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5><i class="fas fa-list"></i> Daftar Jadwal</h5>
                        <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#addModal">
                            <i class="fas fa-plus"></i> Tambah Jadwal
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th width="50">#</th>
                                    <th>Pelatihan</th>
                                    <th>Tanggal</th>
                                    <th>Jam</th>
                                    <th>Ruangan</th>
                                    <th>Keterangan</th>
                                    <th width="180" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                while ($row = $jadwal_list->fetch_assoc()):
                                ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><strong><?php echo $row['nama_pelatihan']; ?></strong></td>
                                        <td><?php echo format_tanggal($row['tanggal']); ?></td>
                                        <td>
                                            <?php echo substr($row['jam_mulai'], 0, 5); ?> -
                                            <?php echo substr($row['jam_selesai'], 0, 5); ?>
                                        </td>
                                        <td><?php echo $row['nama_ruangan']; ?></td>
                                        <td><?php echo $row['keterangan'] ?? '-'; ?></td>
                                        <td class="text-center action-buttons">
                                            <button class="btn btn-warning btn-sm"
                                                onclick="editJadwal(<?php echo $row['id']; ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>

                                            <button class="btn btn-info btn-sm"
                                                onclick="sendWhatsApp(<?php echo $row['id']; ?>)"
                                                title="Kirim notifikasi ke WhatsApp">
                                                <i class="fas fa-whatsapp"></i>
                                            </button>

                                            <button class="btn btn-danger btn-sm"
                                                onclick="deleteJadwal(<?php echo $row['id']; ?>)">
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

    <!-- Modal Tambah Jadwal -->
    <div class="modal fade" id="addModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <form action="proses_jadwal.php?action=add" method="post">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-plus"></i> Tambah Jadwal
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Pelatihan</label>
                            <select name="pelatihan_id" class="form-select" required>
                                <option value="">-- Pilih Pelatihan --</option>
                                <?php
                                $pelatihan = $conn->query("SELECT id, nama_pelatihan FROM pelatihan");
                                while ($p = $pelatihan->fetch_assoc()):
                                ?>
                                    <option value="<?= $p['id']; ?>">
                                        <?= $p['nama_pelatihan']; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tanggal</label>
                                <input type="date" name="tanggal" class="form-control" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Jam Mulai</label>
                                <input type="time" name="jam_mulai" class="form-control" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Jam Selesai</label>
                                <input type="time" name="jam_selesai" class="form-control" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Ruangan</label>
                            <select name="ruangan_id" class="form-select" required>
                                <option value="">-- Pilih Ruangan --</option>
                                <?php
                                $ruangan = $conn->query("SELECT id, nama_ruangan FROM ruangan ORDER BY nama_ruangan ASC");
                                while ($r = $ruangan->fetch_assoc()):
                                ?>
                                    <option value="<?= $r['id']; ?>">
                                        <?= htmlspecialchars($r['nama_ruangan']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Keterangan</label>
                            <textarea name="keterangan" class="form-control"></textarea>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            Batal
                        </button>
                        <button type="submit" class="btn btn-primary">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Edit Jadwal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-edit"></i> Edit Jadwal
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body" id="editModalBody">
                    <!-- Konten dari get_jadwal.php akan dimuat di sini -->
                </div>
            </div>
        </div>
    </div>

    <!-- ✅ Modal Kirim WhatsApp (PERBAIKAN UTAMA) -->
    <div class="modal fade" id="whatsappModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-whatsapp"></i> Kirim Notifikasi WhatsApp
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <!-- ✅ PENTING: div ini harus ada dan punya ID yang benar -->
                <div class="modal-body" id="whatsappModalBody">
                    <div class="text-center py-4">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2 text-muted">Memuat form...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editJadwal(id) {
            fetch('get_jadwal.php?id=' + id)
                .then(res => res.text())
                .then(html => {
                    document.getElementById('editModalBody').innerHTML = html;
                    new bootstrap.Modal(document.getElementById('editModal')).show();
                });
        }

        function deleteJadwal(id) {
            if (confirm('Yakin ingin menghapus jadwal ini?')) {
                window.location.href = 'proses_jadwal.php?action=delete&id=' + id;
            }
        }

        function sendWhatsApp(jadwal_id) {
            console.log('📱 Opening WhatsApp modal for jadwal_id:', jadwal_id);

            // ✅ Buka modal terlebih dahulu
            const modal = new bootstrap.Modal(document.getElementById('whatsappModal'));
            modal.show();

            // ✅ Fetch form dengan error handling
            fetch('proses_whatsapp.php?jadwal_id=' + jadwal_id)
                .then(response => {
                    console.log('Response status:', response.status);
                    if (!response.ok) {
                        throw new Error('Network response was not ok. Status: ' + response.status);
                    }
                    return response.text();
                })
                .then(html => {
                    console.log('✅ Form received, length:', html.length);
                    console.log('Form content:', html.substring(0, 200));

                    // ✅ Pastikan modal body ada sebelum update
                    const modalBody = document.getElementById('whatsappModalBody');
                    if (modalBody) {
                        modalBody.innerHTML = html;
                        console.log('✅ Modal body updated successfully');
                    } else {
                        console.error('❌ Modal body element not found!');
                    }
                })
                .catch(err => {
                    console.error('❌ Error:', err);
                    document.getElementById('whatsappModalBody').innerHTML =
                        '<div class="alert alert-danger">' +
                        '<i class="fas fa-exclamation-circle"></i> ' +
                        'Gagal memuat form: ' + err.message +
                        '</div>';
                });
        }
    </script>
    <script>
        document.addEventListener('submit', function(e) {
            if (e.target && e.target.id === 'whatsappForm') {
                e.preventDefault();

                const form = e.target;
                const formData = new FormData(form);

                if (formData.getAll('peserta_id[]').length === 0) {
                    alert('Pilih minimal 1 peserta!');
                    return;
                }

                const btn = form.querySelector('button[type="submit"]');
                btn.disabled = true;
                btn.innerHTML = 'Mengirim...';

                const jadwalId = formData.get('jadwal_id');

                fetch('proses_whatsapp.php?jadwal_id=' + jadwalId, {
                        method: 'POST',
                        body: formData
                    })
                    .then(res => res.text())
                    .then(html => {
                        document.getElementById('whatsappModalBody').innerHTML = html;
                    })
                    .catch(err => {
                        alert('Error: ' + err);
                    })
                    .finally(() => {
                        btn.disabled = false;
                        btn.innerHTML = 'Kirim WhatsApp';
                    });
            }
        });
    </script>


</body>

</html>

<?php $conn->close(); ?>