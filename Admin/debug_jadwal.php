<?php
session_start();
require_once '../config.php';
check_login();

$conn = getConnection();

echo '<style>
body { font-family: Arial; padding: 20px; background: #f5f5f5; }
.box { background: white; padding: 20px; margin: 10px 0; border-radius: 8px; }
.success { border-left: 5px solid green; }
.error { border-left: 5px solid red; }
table { width: 100%; border-collapse: collapse; }
th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
th { background: #f0f0f0; }
</style>';

echo '<h1>🔍 Debug Jadwal & Peserta</h1>';

// ===== CEK JADWAL =====
echo '<h2>Jadwal Tersedia</h2>';
$jadwal = $conn->query("SELECT j.*, p.nama_pelatihan FROM jadwal j JOIN pelatihan p ON j.pelatihan_id = p.id ORDER BY j.id");

echo '<div class="box">';
echo '<table>';
echo '<tr><th>ID</th><th>Pelatihan</th><th>Tanggal</th><th>Jam</th><th>Lokasi</th></tr>';
while ($row = $jadwal->fetch_assoc()) {
    echo '<tr>';
    echo '<td>' . $row['id'] . '</td>';
    echo '<td>' . htmlspecialchars($row['nama_pelatihan']) . '</td>';
    echo '<td>' . $row['tanggal'] . '</td>';
    echo '<td>' . $row['jam_mulai'] . ' - ' . $row['jam_selesai'] . '</td>';
    echo '<td>' . htmlspecialchars($row['lokasi']) . '</td>';
    echo '</tr>';
}
echo '</table>';
echo '</div>';

// ===== CEK PESERTA DENGAN TELEPON =====
echo '<h2>Peserta dengan Nomor Telepon</h2>';
$peserta = $conn->query("
    SELECT p.id, p.nama_lengkap, p.telepon, GROUP_CONCAT(rp.pelatihan_id) as pelatihan_ids
    FROM peserta p
    LEFT JOIN riwayat_peserta rp ON p.id = rp.peserta_id
    WHERE p.telepon IS NOT NULL AND p.telepon != ''
    GROUP BY p.id
    ORDER BY p.id
");

echo '<div class="box">';
if ($peserta->num_rows > 0) {
    echo '<table>';
    echo '<tr><th>ID</th><th>Nama</th><th>Telepon (Original)</th><th>Telepon (Format)</th><th>Pelatihan</th></tr>';
    while ($row = $peserta->fetch_assoc()) {
        $phone_original = $row['telepon'];
        $phone_formatted = $phone_original;
        
        // Format seperti di proses_whatsapp.php
        $phone_formatted = preg_replace('/\D/', '', $phone_formatted);
        if (substr($phone_formatted, 0, 1) === '0') {
            $phone_formatted = '62' . substr($phone_formatted, 1);
        }
        
        echo '<tr>';
        echo '<td>' . $row['id'] . '</td>';
        echo '<td>' . htmlspecialchars($row['nama_lengkap']) . '</td>';
        echo '<td>' . htmlspecialchars($phone_original) . '</td>';
        echo '<td><strong>' . $phone_formatted . '</strong></td>';
        echo '<td>' . ($row['pelatihan_ids'] ?: 'Tidak terdaftar') . '</td>';
        echo '</tr>';
    }
    echo '</table>';
} else {
    echo '<strong class="text-danger">⚠️ Tidak ada peserta dengan nomor telepon!</strong>';
}
echo '</div>';

// ===== CEK RIWAYAT PESERTA =====
echo '<h2>Riwayat Peserta & Pelatihan</h2>';
$riwayat = $conn->query("
    SELECT rp.id, p.nama_lengkap, pl.nama_pelatihan, rp.status
    FROM riwayat_peserta rp
    JOIN peserta p ON rp.peserta_id = p.id
    JOIN pelatihan pl ON rp.pelatihan_id = pl.id
    WHERE p.telepon IS NOT NULL AND p.telepon != ''
    ORDER BY p.id
");

echo '<div class="box">';
if ($riwayat->num_rows > 0) {
    echo '<table>';
    echo '<tr><th>Peserta</th><th>Pelatihan</th><th>Status</th></tr>';
    while ($row = $riwayat->fetch_assoc()) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($row['nama_lengkap']) . '</td>';
        echo '<td>' . htmlspecialchars($row['nama_pelatihan']) . '</td>';
        echo '<td>' . $row['status'] . '</td>';
        echo '</tr>';
    }
    echo '</table>';
} else {
    echo '<strong class="text-danger">⚠️ Tidak ada riwayat peserta!</strong>';
}
echo '</div>';

// ===== CEK NOTIFIKASI LOG =====
echo '<h2>Log Notifikasi</h2>';
$log = $conn->query("
    SELECT * FROM notifikasi_log 
    ORDER BY created_at DESC 
    LIMIT 20
");

echo '<div class="box">';
if ($log->num_rows > 0) {
    echo '<table>';
    echo '<tr><th>ID</th><th>Peserta</th><th>Jadwal</th><th>Telepon</th><th>Status</th><th>Tanggal</th></tr>';
    while ($row = $log->fetch_assoc()) {
        $status_color = $row['status'] === 'sent' ? 'green' : ($row['status'] === 'failed' ? 'red' : 'orange');
        echo '<tr>';
        echo '<td>' . $row['id'] . '</td>';
        echo '<td>' . $row['peserta_id'] . '</td>';
        echo '<td>' . $row['jadwal_id'] . '</td>';
        echo '<td>' . htmlspecialchars($row['telepon']) . '</td>';
        echo '<td><span style="color:' . $status_color . ';font-weight:bold;">' . $row['status'] . '</span></td>';
        echo '<td>' . $row['created_at'] . '</td>';
        echo '</tr>';
    }
    echo '</table>';
} else {
    echo '<strong class="text-warning">⚠️ Belum ada log notifikasi</strong>';
}
echo '</div>';

$conn->close();
?>
