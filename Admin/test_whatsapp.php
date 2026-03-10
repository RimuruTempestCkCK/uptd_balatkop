<?php
session_start();
require_once '../config.php';
check_login();

echo '<style>
body { font-family: Arial; padding: 20px; background: #f5f5f5; }
.box { background: white; padding: 20px; margin: 10px 0; border-radius: 8px; }
.success { border-left: 5px solid green; }
.error { border-left: 5px solid red; }
.warning { border-left: 5px solid orange; }
pre { background: #f0f0f0; padding: 10px; overflow-x: auto; }
</style>';

echo '<h1>🧪 Test Pengiriman WhatsApp</h1>';

$token = '8Eb8GzZxQscNNFJyPnAN';
$test_phone = '6285157558469';
$test_message = 'Halo Anak Kucing';

echo '<div class="box warning">';
echo '<strong>⚠️ Info Test:</strong><br>';
echo 'Token: ' . substr($token, 0, 5) . '...' . substr($token, -5) . '<br>';
echo 'Phone: ' . $test_phone . '<br>';
echo 'Message: ' . $test_message;
echo '</div>';

// ===== FUNGSI TEST =====
function sendWhatsAppFontteTest($phone, $message, $token) {
    echo '<div class="box">';
    echo '<strong>📤 Request Details:</strong><br>';
    echo 'URL: https://api.fonnte.com/send<br>';
    echo 'Method: POST<br>';
    echo 'Phone: ' . $phone . '<br>';
    echo 'Message: ' . $message . '<br>';
    echo 'Token: ' . substr($token, 0, 10) . '...<br>';
    echo '</div>';

    if (empty($token)) {
        echo '<div class="box error"><strong>❌ Error:</strong> Token kosong</div>';
        return false;
    }

    $data = array(
        'target' => $phone,
        'message' => $message,
    );

    echo '<div class="box">';
    echo '<strong>📦 POST Data:</strong><br>';
    echo '<pre>' . http_build_query($data) . '</pre>';
    echo '</div>';

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
        CURLOPT_VERBOSE => true,
    ));

    $response = curl_exec($curl);
    $curl_error = curl_error($curl);
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    echo '<div class="box">';
    echo '<strong>📡 Response:</strong><br>';
    echo 'HTTP Code: ' . $http_code . '<br>';
    if ($curl_error) {
        echo '<strong class="text-danger">cURL Error:</strong> ' . $curl_error . '<br>';
    }
    echo '<strong>Response Body:</strong><br>';
    echo '<pre>' . htmlspecialchars($response) . '</pre>';
    echo '</div>';

    if (!empty($response)) {
        $result = json_decode($response, true);
        
        echo '<div class="box">';
        echo '<strong>📋 Parsed JSON:</strong><br>';
        echo '<pre>' . json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . '</pre>';
        echo '</div>';

        // Cek success
        if (isset($result['status']) && ($result['status'] === true || $result['status'] === 'success')) {
            echo '<div class="box success"><strong>✅ SUCCESS:</strong> Pesan terkirim!</div>';
            return true;
        }

        if ($http_code === 200 && isset($result['detail'])) {
            echo '<div class="box success"><strong>✅ SUCCESS:</strong> ' . $result['detail'] . '</div>';
            return true;
        }

        if (isset($result['error'])) {
            echo '<div class="box error"><strong>❌ API Error:</strong> ' . $result['error'] . '</div>';
            return false;
        }
    }

    if ($curl_error) {
        echo '<div class="box error"><strong>❌ cURL Error:</strong> ' . $curl_error . '</div>';
        return false;
    }

    echo '<div class="box error"><strong>❌ Unknown Error:</strong> HTTP ' . $http_code . '</div>';
    return false;
}

// ===== RUN TEST =====
echo '<h2>🚀 Menjalankan Test...</h2>';
$result = sendWhatsAppFontteTest($test_phone, $test_message, $token);

echo '<hr>';

if ($result) {
    echo '<div class="box success">';
    echo '<h3>✅ TEST BERHASIL!</h3>';
    echo 'Token dan koneksi API Fonnte berfungsi dengan baik.';
    echo '</div>';
} else {
    echo '<div class="box error">';
    echo '<h3>❌ TEST GAGAL!</h3>';
    echo '<strong>Kemungkinan masalah:</strong><br>';
    echo '1. Token Fonnte tidak valid atau sudah expired<br>';
    echo '2. Nomor WhatsApp belum terdaftar di Fonnte<br>';
    echo '3. API Fonnte sedang down<br>';
    echo '4. Firewall/Network memblokir request<br>';
    echo '</div>';
}

?>
