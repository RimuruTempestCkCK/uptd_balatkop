<?php
session_start();
require_once '../config.php';
check_login();

if (!isset($_GET['jadwal_id']) && !isset($_POST['jadwal_id'])) {
    exit('Invalid request');
}

$conn = getConnection();
$jadwal_id = (int)($_GET['jadwal_id'] ?? $_POST['jadwal_id']);

// GET: Tampilkan form
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Ambil data jadwal
    $stmt = $conn->prepare("
        SELECT 
            j.*,
            p.nama_pelatihan,
            p.id AS pelatihan_id,
            r.nama_ruangan AS lokasi
        FROM jadwal j
        JOIN pelatihan p ON j.pelatihan_id = p.id
        LEFT JOIN ruangan r ON j.ruangan_id = r.id
        WHERE j.id = ?
    ");

    $stmt->bind_param("i", $jadwal_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        exit('Jadwal tidak ditemukan');
    }

    $jadwal = $result->fetch_assoc();

    // Ambil peserta yang sudah terdaftar di pelatihan ini
    $peserta_result = $conn->prepare("
        SELECT DISTINCT p.id, p.nama_lengkap, p.telepon
        FROM peserta p
        JOIN riwayat_peserta rp ON p.id = rp.peserta_id
        WHERE rp.pelatihan_id = ? AND p.telepon IS NOT NULL AND p.telepon != ''
        ORDER BY p.nama_lengkap
    ");
    $peserta_result->bind_param("i", $jadwal['pelatihan_id']);
    $peserta_result->execute();
    $peserta_list = $peserta_result->get_result();
    $total_peserta = $peserta_list->num_rows;

?>
    <form method="post" id="whatsappForm">
        <input type="hidden" name="jadwal_id" value="<?= $jadwal_id; ?>">

        <div class="alert alert-info">
            <strong><i class="fas fa-info-circle"></i> Jadwal Pelatihan:</strong><br>
            <strong><?= htmlspecialchars($jadwal['nama_pelatihan']); ?></strong><br>
            Tanggal: <?= format_tanggal($jadwal['tanggal']); ?> |
            Jam: <?= substr($jadwal['jam_mulai'], 0, 5); ?> - <?= substr($jadwal['jam_selesai'], 0, 5); ?><br>
            Ruangan: <?= htmlspecialchars($jadwal['lokasi']); ?>
        </div>

        <div class="mb-3">
            <label class="form-label"><strong>Pesan Notifikasi</strong></label>
            <!-- <textarea name="pesan" class="form-control" rows="5" required><?= "Assalamu'alaikum,\n\nReminder: Jadwal Pelatihan " . htmlspecialchars($jadwal['nama_pelatihan']) . "\n\nTanggal: " . format_tanggal($jadwal['tanggal']) . "\nJam: " . substr($jadwal['jam_mulai'], 0, 5) . " - " . substr($jadwal['jam_selesai'], 0, 5) . " WIB\nLokasi: " . htmlspecialchars($jadwal['lokasi']) . "\n\nMohon untuk hadir tepat waktu.\nTerima kasih."; ?></textarea> -->
            <textarea name="pesan" class="form-control" rows="6" required><?=
                                                                            "Assalamu'alaikum,\n\n" .
                                                                                "Reminder: Jadwal Pelatihan " . htmlspecialchars($jadwal['nama_pelatihan']) . "\n\n" .
                                                                                "Tanggal   : " . format_tanggal($jadwal['tanggal']) . "\n" .
                                                                                "Jam        : " . substr($jadwal['jam_mulai'], 0, 5) . " - " . substr($jadwal['jam_selesai'], 0, 5) . " WIB\n" .
                                                                                "Ruangan    : " . htmlspecialchars($jadwal['lokasi']) . "\n\n" .
                                                                                "Keterangan:\n" .
                                                                                (!empty($jadwal['keterangan'])
                                                                                    ? htmlspecialchars($jadwal['keterangan'])
                                                                                    : "-") .
                                                                                "\n\nMohon untuk hadir tepat waktu.\nTerima kasih.\n\nWassalamu'alaikum"; ?></textarea>

            <small class="text-muted">Anda dapat mengedit pesan sesuai kebutuhan</small>
        </div>

        <div class="mb-3">
            <label class="form-label"><strong>Pilih Peserta yang akan Dikirim</strong></label>
            <?php if ($total_peserta > 0): ?>
                <div class="border rounded p-3" style="max-height: 250px; overflow-y: auto;">
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                        <label class="form-check-label fw-bold" for="selectAll">
                            Pilih Peserta (<?= $total_peserta; ?> peserta)
                        </label>
                    </div>
                    <hr>
                    <?php $no = 1;
                    while ($p = $peserta_list->fetch_assoc()): ?>
                        <div class="form-check">
                            <input class="form-check-input peserta-checkbox" type="checkbox"
                                name="peserta_id[]" value="<?= $p['id']; ?>" id="peserta_<?= $p['id']; ?>">
                            <label class="form-check-label" for="peserta_<?= $p['id']; ?>">
                                <?= htmlspecialchars($p['nama_lengkap']); ?>
                                <small class="text-muted">(<?= htmlspecialchars($p['telepon']); ?>)</small>
                            </label>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    Tidak ada peserta dengan nomor telepon untuk pelatihan ini.
                </div>
            <?php endif; ?>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                Batal
            </button>
            <?php if ($total_peserta > 0): ?>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-whatsapp"></i> Kirim WhatsApp
                </button>
            <?php endif; ?>
        </div>
    </form>

    <script>
        function toggleSelectAll() {
            const checkboxes = document.querySelectorAll('.peserta-checkbox');
            const selectAll = document.getElementById('selectAll').checked;
            checkboxes.forEach(cb => cb.checked = selectAll);
        }
    </script>
<?php
    $stmt->close();
    $peserta_result->close();
}

// POST: Proses pengiriman
else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pesan = $_POST['pesan'] ?? '';
    $peserta_ids = $_POST['peserta_id'] ?? [];

    if (empty($pesan)) {
        echo '<div class="alert alert-danger"><i class="fas fa-times-circle"></i> Pesan tidak boleh kosong</div>';
        exit;
    }

    if (empty($peserta_ids)) {
        echo '<div class="alert alert-danger"><i class="fas fa-times-circle"></i> Pilih minimal 1 peserta</div>';
        exit;
    }

    $whatsapp_token = '8Eb8GzZxQscNNFJyPnAN';
    $success_count = 0;
    $failed_count = 0;
    $error_messages = [];
    $debug_info = [];

    foreach ($peserta_ids as $peserta_id) {
        $peserta_id = (int)$peserta_id;

        $p_stmt = $conn->prepare("SELECT nama_lengkap, telepon FROM peserta WHERE id = ?");
        $p_stmt->bind_param("i", $peserta_id);
        $p_stmt->execute();
        $peserta = $p_stmt->get_result()->fetch_assoc();

        if (!$peserta || !$peserta['telepon']) {
            $error_messages[] = "Peserta ID {$peserta_id} tidak memiliki nomor telepon";
            $p_stmt->close();
            continue;
        }

        $phone = $peserta['telepon'];
        $phone = preg_replace('/\D/', '', $phone);

        if (strlen($phone) < 10) {
            $error_messages[] = "{$peserta['nama_lengkap']}: Nomor terlalu pendek ({$phone})";
            $p_stmt->close();
            continue;
        }

        if (substr($phone, 0, 2) === '62') {
            // OK
        } elseif (substr($phone, 0, 1) === '0') {
            $phone = '62' . substr($phone, 1);
        } else {
            $phone = '62' . $phone;
        }

        if (!preg_match('/^62\d{9,}$/', $phone)) {
            $error_messages[] = "{$peserta['nama_lengkap']}: Format tidak valid ({$phone})";
            $p_stmt->close();
            continue;
        }

        $debug_info[] = "→ {$peserta['nama_lengkap']} ({$phone})";

        // ✅ Kirim WhatsApp
        $send_status = sendWhatsAppFonnte($phone, $pesan, $whatsapp_token);

        // Simpan log
        $log_stmt = $conn->prepare("
            INSERT INTO notifikasi_log (peserta_id, jadwal_id, tipe, telepon, pesan, status, created_at)
            VALUES (?, ?, 'WhatsApp', ?, ?, ?, NOW())
        ");

        $status = $send_status ? 'sent' : 'failed';
        $log_stmt->bind_param("iisss", $peserta_id, $jadwal_id, $phone, $pesan, $status);
        $log_stmt->execute();
        $log_stmt->close();

        if ($send_status) {
            $success_count++;
            $debug_info[] = "✅ TERKIRIM";
        } else {
            $failed_count++;
            $error_messages[] = "{$peserta['nama_lengkap']}: Gagal terkirim";
            $debug_info[] = "❌ GAGAL";
        }

        $p_stmt->close();
        usleep(300000); // 0.3 detik delay antar pengiriman
    }

    echo '<div class="alert alert-success">';
    echo '<strong><i class="fas fa-check-circle"></i> Hasil Pengiriman WhatsApp</strong><br>';
    echo 'Berhasil: <strong style="color: #10b981; font-size: 1.2em;">' . $success_count . '</strong> | ';
    echo 'Gagal: <strong style="color: #ef4444; font-size: 1.2em;">' . $failed_count . '</strong>';
    echo '</div>';

    if (!empty($debug_info)) {
        echo '<div class="alert alert-secondary"><small><strong>📋 Detail Pengiriman:</strong><br>';
        echo implode('<br>', $debug_info);
        echo '</small></div>';
    }

    if (!empty($error_messages)) {
        echo '<div class="alert alert-warning"><strong>⚠️ Peringatan:</strong><ul class="mb-0">';
        foreach ($error_messages as $err) {
            echo '<li>' . htmlspecialchars($err) . '</li>';
        }
        echo '</ul></div>';
    }

    if ($success_count > 0) {
        echo '<div class="alert alert-info">';
        echo '<i class="fas fa-check"></i> <strong>✅ Notifikasi WhatsApp berhasil dikirim ke ' . $success_count . ' peserta!</strong>';
        echo '</div>';
    } else {
        echo '<div class="alert alert-danger"><strong>❌ Tidak ada pesan yang terkirim</strong></div>';
    }

    $conn->close();
}

/**
 * ✅ Fungsi Kirim WhatsApp - IDENTIK DENGAN test_whatsapp.php
 */
function sendWhatsAppFonnte($phone, $message, $token)
{
    if (empty($token)) {
        return false;
    }

    $data = array(
        'target' => $phone,
        'message' => $message,
    );

    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.fonnte.com/send',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => http_build_query($data),
        CURLOPT_HTTPHEADER => array(
            'Authorization: ' . $token,
            'Content-Type: application/x-www-form-urlencoded'
        ),
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
    ));

    $response = curl_exec($curl);
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($curl);
    curl_close($curl);

    if ($curl_error || empty($response)) {
        return false;
    }

    $result = json_decode($response, true);

    // ✅ SAMA SEPERTI TEST
    if (isset($result['status']) && ($result['status'] === true || $result['status'] === 'success')) {
        return true;
    }

    if ($http_code === 200 && isset($result['detail'])) {
        return true;
    }

    return false;
}

?>