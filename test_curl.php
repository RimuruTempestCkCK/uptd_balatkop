<?php
echo '<style>
body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
.box { background: white; padding: 20px; margin: 10px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
.success { border-left: 5px solid green; }
.error { border-left: 5px solid red; }
.warning { border-left: 5px solid orange; }
pre { background: #f0f0f0; padding: 10px; border-radius: 4px; overflow-x: auto; }
</style>';

echo '<h1>🔧 Diagnostik Fonnte WhatsApp API</h1>';

// 1. Cek cURL
echo '<div class="box success">';
if (extension_loaded('curl')) {
    echo '✓ <strong>cURL Extension AKTIF</strong>';
} else {
    echo '✗ <strong>cURL Extension TIDAK AKTIF</strong><br>Silakan aktifkan di php.ini';
}
echo '</div>';

// 2. Token Fonnte
$token = '8Eb8GzZxQscNNFJyPnAN';
echo '<div class="box">';
echo '<strong>Token Fonnte:</strong> ' . substr($token, 0, 5) . '...' . substr($token, -5);
echo '</div>';

// 3. Test API Info
echo '<h2>1️⃣ Test: GET /info</h2>';
$curl = curl_init();
curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://api.fonnte.com/info',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 10,
    CURLOPT_CUSTOMREQUEST => 'GET',
    CURLOPT_HTTPHEADER => array(
        'Authorization: ' . $token
    ),
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
));

$response = curl_exec($curl);
$http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
$err = curl_error($curl);
curl_close($curl);

echo '<div class="box ' . ($err ? 'error' : 'success') . '">';
echo '<strong>HTTP Code:</strong> ' . $http_code . '<br>';
if ($err) {
    echo '<strong>cURL Error:</strong> ' . $err;
} else {
    echo '<strong>Response:</strong><pre>' . (empty($response) ? '(kosong)' : json_encode(json_decode($response, true), JSON_PRETTY_PRINT)) . '</pre>';
}
echo '</div>';

// 4. Test Send Message (fake)
echo '<h2>2️⃣ Test: POST /send (Fake Number)</h2>';
$curl = curl_init();
curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://api.fonnte.com/send',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 10,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => http_build_query(array(
        'target' => '62999999999',
        'message' => 'Test pesan dari server',
    )),
    CURLOPT_HTTPHEADER => array(
        'Authorization: ' . $token,
        'Content-Type: application/x-www-form-urlencoded'
    ),
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
));

$response = curl_exec($curl);
$http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
$err = curl_error($curl);
curl_close($curl);

echo '<div class="box ' . ($err ? 'error' : 'warning') . '">';
echo '<strong>HTTP Code:</strong> ' . $http_code . '<br>';
if ($err) {
    echo '<strong>cURL Error:</strong> ' . $err;
} else {
    echo '<strong>Response:</strong><pre>' . (empty($response) ? '(kosong)' : json_encode(json_decode($response, true), JSON_PRETTY_PRINT)) . '</pre>';
}
echo '</div>';

// 5. Kesimpulan
echo '<h2>📋 Kesimpulan</h2>';
echo '<div class="box">';
if ($http_code === 200) {
    echo '✓ <strong>Token valid dan API responsif</strong>';
} elseif ($http_code === 401 || $http_code === 403) {
    echo '✗ <strong>Token tidak valid atau tidak aktif</strong><br>';
    echo 'Silakan:<br>';
    echo '1. Cek kembali token di https://panel.fonnte.com<br>';
    echo '2. Pastikan nomor WhatsApp sudah terdaftar<br>';
    echo '3. Pastikan subscription masih aktif';
} elseif ($http_code === 0) {
    echo '⚠️ <strong>Tidak bisa terhubung ke API</strong><br>';
    echo 'Kemungkinan:<br>';
    echo '1. SSL verification error (sudah disabled)<br>';
    echo '2. Network/Firewall issue<br>';
    echo '3. API Fonnte sedang down';
} else {
    echo '⚠️ <strong>Response tidak terduga (HTTP ' . $http_code . ')</strong>';
}
echo '</div>';

// 6. Info Format Nomor
echo '<h2>📱 Format Nomor WhatsApp yang Benar</h2>';
echo '<div class="box">';
echo '<strong>Format yang BENAR:</strong><br>';
echo '• <code>62812345678</code> (paling umum)<br>';
echo '• <code>6281234567890</code> (dengan leading zero dihapus)<br><br>';
echo '<strong>Format yang SALAH:</strong><br>';
echo '• <code>081234567890</code> (dimulai dengan 0)<br>';
echo '• <code>+6281234567890</code> (dengan +)<br>';
echo '</div>';

?>
