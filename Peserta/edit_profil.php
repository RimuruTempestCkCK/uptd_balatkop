<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Peserta') {
    header('Location: ../index.php');
    exit;
}

$conn = getConnection();
$user_id = $_SESSION['user_id'];

// Ambil data peserta
$stmt = $conn->prepare("SELECT id, nama_lengkap, email, telepon, tanggal_lahir, file_ktp, file_kk FROM peserta WHERE user_id = ? LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmtInsert = $conn->prepare("INSERT INTO peserta (user_id, nama_lengkap, status) VALUES (?, ?, 'Aktif')");
    $nama_from_session = $_SESSION['nama_lengkap'] ?? 'Peserta';
    $stmtInsert->bind_param("is", $user_id, $nama_from_session);
    $stmtInsert->execute();
    $peserta_id = $stmtInsert->insert_id;
    $peserta = [
        'id'            => $peserta_id,
        'nama_lengkap'  => $nama_from_session,
        'email'         => null,
        'telepon'       => null,
        'tanggal_lahir' => null,
        'file_ktp'      => null,
        'file_kk'       => null,
    ];
    $stmtInsert->close();
} else {
    $peserta = $result->fetch_assoc();
    $peserta_id = $peserta['id'];
}

$stmt->close();

/* =======================
   PROSES UPDATE PROFIL
======================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_lengkap  = clean_input($_POST['nama_lengkap'] ?? '');
    $email         = clean_input($_POST['email'] ?? '');
    $telepon       = clean_input($_POST['telepon'] ?? '');
    $tanggal_lahir = clean_input($_POST['tanggal_lahir'] ?? '');

    if (empty($nama_lengkap)) {
        set_flash('danger', 'Nama lengkap wajib diisi!');
        header('Location: edit_profil.php');
        exit;
    }

    $upload_dir  = '../uploads/dokumen/';
    $allowed_ext = ['jpg', 'jpeg', 'png', 'pdf'];
    $max_size    = 2 * 1024 * 1024; // 2 MB

    $file_ktp = $peserta['file_ktp']; // default: tetap file lama
    $file_kk  = $peserta['file_kk'];

    // Fungsi upload
    $upload_error = '';

    foreach (['ktp', 'kk'] as $dok) {
        $key = 'file_' . $dok;
        if (!empty($_FILES[$key]['name'])) {
            $file     = $_FILES[$key];
            $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $size     = $file['size'];

            if (!in_array($ext, $allowed_ext)) {
                $upload_error = "File $dok harus berformat JPG, PNG, atau PDF!";
                break;
            }
            if ($size > $max_size) {
                $upload_error = "Ukuran file $dok maksimal 2MB!";
                break;
            }
            if ($file['error'] !== UPLOAD_ERR_OK) {
                $upload_error = "Gagal mengupload file $dok!";
                break;
            }

            // Hapus file lama jika ada
            $old = $peserta[$key];
            if ($old && file_exists($upload_dir . $old)) {
                unlink($upload_dir . $old);
            }

            // Nama file baru: ktp_peserta4_1741234567.jpg
            $nama_file = $dok . '_peserta' . $peserta_id . '_' . time() . '.' . $ext;
            move_uploaded_file($file['tmp_name'], $upload_dir . $nama_file);

            if ($dok === 'ktp') {
                $file_ktp = $nama_file;
            } else {
                $file_kk = $nama_file;
            }
        }
    }

    if ($upload_error) {
        set_flash('danger', $upload_error);
        header('Location: edit_profil.php');
        exit;
    }

    $stmtUpdate = $conn->prepare("
        UPDATE peserta 
        SET nama_lengkap = ?, email = ?, telepon = ?, tanggal_lahir = ?, file_ktp = ?, file_kk = ?
        WHERE id = ?
    ");
    $stmtUpdate->bind_param("ssssssi", $nama_lengkap, $email, $telepon, $tanggal_lahir, $file_ktp, $file_kk, $peserta_id);

    if ($stmtUpdate->execute()) {
        $_SESSION['nama_lengkap'] = $nama_lengkap;
        $peserta['nama_lengkap']  = $nama_lengkap;
        $peserta['email']         = $email;
        $peserta['telepon']       = $telepon;
        $peserta['tanggal_lahir'] = $tanggal_lahir;
        $peserta['file_ktp']      = $file_ktp;
        $peserta['file_kk']       = $file_kk;
        set_flash('success', 'Profil berhasil diperbarui!');
    } else {
        set_flash('danger', 'Gagal memperbarui profil: ' . $stmtUpdate->error);
    }

    $stmtUpdate->close();
    header('Location: edit_profil.php');
    exit;
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Profil - <?php echo APP_NAME; ?></title>
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
                <h1><i class="fas fa-user-edit"></i> Edit Profil</h1>
                <p class="text-muted">Perbarui data profil Anda</p>
            </div>

            <?php if ($flash = get_flash()): ?>
                <div class="alert alert-<?= $flash['type']; ?> alert-dismissible fade show">
                    <i class="fas fa-info-circle"></i> <?= $flash['message']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="content-card">
                <div class="card-header">
                    <h5><i class="fas fa-user"></i> Data Profil</h5>
                </div>
                <div class="p-4">
                    <form method="post" enctype="multipart/form-data" autocomplete="off">

                        <div class="mb-3">
                            <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" name="nama_lengkap" class="form-control" required
                                   value="<?= htmlspecialchars($peserta['nama_lengkap'] ?? ''); ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control"
                                   value="<?= htmlspecialchars($peserta['email'] ?? ''); ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Telepon</label>
                            <input type="text" name="telepon" class="form-control"
                                   value="<?= htmlspecialchars($peserta['telepon'] ?? ''); ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tanggal Lahir</label>
                            <input type="date" name="tanggal_lahir" class="form-control"
                                   value="<?= htmlspecialchars($peserta['tanggal_lahir'] ?? ''); ?>">
                        </div>

                        <hr class="my-4">
                        <h6 class="mb-3"><i class="fas fa-file-alt"></i> Dokumen Identitas</h6>

                        <!-- Upload KTP -->
                        <div class="mb-3">
                            <label class="form-label">KTP <small class="text-muted">(JPG/PNG/PDF, maks 2MB)</small></label>
                            <input type="file" name="file_ktp" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                            <?php if (!empty($peserta['file_ktp'])): ?>
                                <div class="mt-2">
                                    <small class="text-muted">File saat ini: </small>
                                    <?php
                                    $ext_ktp = strtolower(pathinfo($peserta['file_ktp'], PATHINFO_EXTENSION));
                                    if ($ext_ktp === 'pdf'):
                                    ?>
                                        <a href="../uploads/dokumen/<?= $peserta['file_ktp']; ?>" target="_blank" class="btn btn-outline-secondary btn-sm">
                                            <i class="fas fa-file-pdf"></i> Lihat KTP
                                        </a>
                                    <?php else: ?>
                                        <a href="../uploads/dokumen/<?= $peserta['file_ktp']; ?>" target="_blank">
                                            <img src="../uploads/dokumen/<?= $peserta['file_ktp']; ?>"
                                                 style="height:60px; border-radius:4px; border:1px solid #ddd;" alt="KTP">
                                        </a>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Upload KK -->
                        <div class="mb-3">
                            <label class="form-label">Kartu Keluarga (KK) <small class="text-muted">(JPG/PNG/PDF, maks 2MB)</small></label>
                            <input type="file" name="file_kk" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                            <?php if (!empty($peserta['file_kk'])): ?>
                                <div class="mt-2">
                                    <small class="text-muted">File saat ini: </small>
                                    <?php
                                    $ext_kk = strtolower(pathinfo($peserta['file_kk'], PATHINFO_EXTENSION));
                                    if ($ext_kk === 'pdf'):
                                    ?>
                                        <a href="../uploads/dokumen/<?= $peserta['file_kk']; ?>" target="_blank" class="btn btn-outline-secondary btn-sm">
                                            <i class="fas fa-file-pdf"></i> Lihat KK
                                        </a>
                                    <?php else: ?>
                                        <a href="../uploads/dokumen/<?= $peserta['file_kk']; ?>" target="_blank">
                                            <img src="../uploads/dokumen/<?= $peserta['file_kk']; ?>"
                                                 style="height:60px; border-radius:4px; border:1px solid #ddd;" alt="KK">
                                        </a>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Simpan Perubahan
                            </button>
                            <a href="dashboard_peserta.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </a>
                        </div>

                    </form>
                </div>
            </div>
        </main>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>