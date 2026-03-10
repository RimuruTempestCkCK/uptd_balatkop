<?php
session_start();
require_once '../config.php';
check_login();

if ($_SESSION['role'] !== 'Peserta') {
    exit('Akses ditolak');
}

$conn = getConnection();
$riwayat_id = (int) ($_GET['riwayat_id'] ?? 0);

// Ambil data sertifikat
$stmt = $conn->prepare("
    SELECT 
        rp.id AS riwayat_id,
        rp.peserta_id,
        rp.pelatihan_id,
        rp.tanggal_ikut,
        p.nama_pelatihan,
        p.penyelenggara,
        u.nama_lengkap AS instruktur,
        ps.nama_lengkap AS peserta
    FROM riwayat_peserta rp
    JOIN pelatihan p ON rp.pelatihan_id = p.id
    LEFT JOIN users u ON p.instruktur_id = u.id
    JOIN peserta ps ON rp.peserta_id = ps.id
    WHERE rp.id = ? AND rp.status = 'Lulus'
    LIMIT 1
");
$stmt->bind_param("i", $riwayat_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    exit('Sertifikat tidak ditemukan atau Anda belum lulus.');
}

$data = $result->fetch_assoc();
$stmt->close();

// Ambil data evaluasi/nilai
$nilai = null;
$stmt2 = $conn->prepare("
    SELECT 
        ep.nilai_kehadiran,
        ep.nilai_partisipasi,
        ep.nilai_praktik,
        ep.nilai_sikap,
        ep.nilai_akhir,
        ep.keterangan,
        ep.created_at
    FROM evaluasi_pelatihan ep
    WHERE ep.peserta_id = ? AND ep.pelatihan_id = ?
    LIMIT 1
");
$stmt2->bind_param("ii", $data['peserta_id'], $data['pelatihan_id']);
$stmt2->execute();
$result2 = $stmt2->get_result();
if ($result2->num_rows > 0) {
    $nilai = $result2->fetch_assoc();
}
$stmt2->close();
$conn->close();

// Helper warna dan predikat nilai
function warnaNilai($n)
{
    if ($n >= 85) return ['bg' => '#d1fae5', 'color' => '#065f46', 'label' => 'Sangat Baik'];
    if ($n >= 70) return ['bg' => '#dbeafe', 'color' => '#1e40af', 'label' => 'Baik'];
    if ($n >= 55) return ['bg' => '#fef9c3', 'color' => '#854d0e', 'label' => 'Cukup'];
    return ['bg' => '#fee2e2', 'color' => '#991b1b', 'label' => 'Kurang'];
}

// Kode unik sertifikat
$kode_sertifikat = 'CERT-' . str_pad($data['riwayat_id'], 4, '0', STR_PAD_LEFT)
                 . '-' . strtoupper(substr(md5($data['peserta_id'] . $data['pelatihan_id'] . $data['tanggal_ikut']), 0, 8));

// URL verifikasi yang akan di-encode ke QR
// Sesuaikan BASE_URL dengan domain Anda (bisa di config.php atau hardcode di sini)
$base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
          . '://' . $_SERVER['HTTP_HOST']
          . rtrim(dirname(dirname($_SERVER['PHP_SELF'])), '/');
$verify_url = $base_url . '/verify.php?kode=' . urlencode($kode_sertifikat);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Sertifikat & Nilai - <?= htmlspecialchars($data['peserta']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; }
        body {
            background: linear-gradient(135deg, #f3f4f6 0%, #e0e7ff 100%);
            min-height: 100vh;
            padding: 0 0 60px 0;
        }

        /* ===== TOMBOL PRINT ===== */
        .print-btn-wrap {
            display: flex; justify-content: center; gap: 12px; padding: 28px 0 0 0;
        }
        .print-btn-wrap a, .print-btn-wrap button {
            padding: 10px 28px; border-radius: 12px; font-weight: 600;
            font-size: 0.97rem; border: none; cursor: pointer;
            display: flex; align-items: center; gap: 8px; text-decoration: none;
        }
        .btn-print { background: #4f46e5; color: #fff; }
        .btn-print:hover { background: #3730a3; color: #fff; }
        .btn-back  { background: #e0e7ff; color: #3730a3; }
        .btn-back:hover { background: #c7d2fe; color: #3730a3; }

        /* ===== LEMBAR ===== */
        .lembar { padding: 60px 20px 40px 20px; }
        .lembar-sertifikat { background: linear-gradient(135deg, #f3f4f6 0%, #e0e7ff 100%); }
        .lembar-nilai { background: linear-gradient(135deg, #f0f9ff 0%, #e0e7ff 100%); border-top: 4px solid #4f46e5; }

        /* ===== SERTIFIKAT ===== */
        .sertifikat-box {
            border: 10px double #4f46e5; border-radius: 36px;
            padding: 56px 60px 36px 60px; margin: 0 auto; max-width: 1100px;
            background: #fff;
            box-shadow: 0 10px 40px rgba(79,70,229,0.13), 0 2px 8px rgba(0,0,0,0.06);
            text-align: center; position: relative;
        }
        @media (max-width: 900px) { .sertifikat-box { padding: 50px 20px 30px 20px; } }
        @media (max-width: 600px) { .sertifikat-box { padding: 40px 10px 20px 10px; border-width: 6px; } }

        .sertifikat-ribbon {
            position: absolute; top: -44px; left: 50%; transform: translateX(-50%);
            background: linear-gradient(90deg, #4f46e5 0%, #06b6d4 100%);
            color: #fff; padding: 14px 60px; border-radius: 28px;
            font-size: 1.7rem; font-weight: 800; letter-spacing: 2px;
            box-shadow: 0 4px 16px rgba(79,70,229,0.15);
            display: flex; align-items: center; gap: 18px; white-space: nowrap;
        }
        .sertifikat-logo { margin-bottom: 18px; }
        .sertifikat-logo img {
            width: 100px; height: 100px; border-radius: 50%;
            border: 5px solid #4f46e5; background: #fff;
            object-fit: cover; box-shadow: 0 2px 12px rgba(79,70,229,0.10);
        }
        .sertifikat-title {
            font-size: 2.7rem; font-weight: bold; color: #4f46e5;
            margin-bottom: 16px; letter-spacing: 1.5px;
            display: flex; align-items: center; justify-content: center; gap: 16px;
        }
        .sertifikat-detail {
            font-size: 1.18rem; margin-bottom: 10px; color: #444;
            display: flex; align-items: center; gap: 8px; justify-content: center;
        }
        .sertifikat-nama {
            font-size: 2.2rem; font-weight: bold; color: #1e293b;
            margin: 28px 0 14px 0; letter-spacing: 1px;
            display: flex; align-items: center; justify-content: center; gap: 10px;
        }
        .sertifikat-pelatihan {
            font-size: 1.35rem; font-weight: 700; color: #4f46e5; margin-bottom: 10px;
            display: flex; align-items: center; gap: 8px; justify-content: center;
        }
        .sertifikat-meta {
            display: flex; justify-content: center; gap: 40px;
            margin: 32px 0 10px 0; flex-wrap: wrap;
        }
        .sertifikat-meta-item { font-size: 1.08rem; color: #555; display: flex; align-items: center; gap: 7px; }
        .sertifikat-footer {
            margin-top: 40px; font-size: 1.13rem; color: #888;
            border-top: 1px dashed #b3bcf5; padding-top: 18px;
            display: flex; align-items: center; justify-content: space-between;
        }
        .sertifikat-badge { margin-top: 24px; display: flex; justify-content: center; gap: 18px; flex-wrap: wrap; }
        .sertifikat-badge .badge {
            font-size: 1.05rem; padding: 8px 18px; border-radius: 18px;
            font-weight: 600; display: flex; align-items: center; gap: 7px;
        }
        @media (max-width: 700px) {
            .sertifikat-title { font-size: 1.5rem; }
            .sertifikat-nama { font-size: 1.3rem; }
            .sertifikat-pelatihan { font-size: 1rem; }
            .sertifikat-meta { flex-direction: column; gap: 8px; }
            .sertifikat-footer { flex-direction: column; gap: 8px; }
            .sertifikat-ribbon { font-size: 1.1rem; padding: 10px 20px; }
        }

        /* ===== QR CODE AREA ===== */
        .qr-area {
            display: flex; align-items: center; justify-content: space-between;
            margin-top: 32px; gap: 20px;
        }
        .qr-area .sign-wrap { flex: 1; display: flex; align-items: center; gap: 32px; justify-content: flex-end; }
        .sign-label { font-size: 1.08rem; color: #666; display: flex; align-items: center; gap: 6px; }
        .sign-name {
            font-size: 1.18rem; font-weight: bold; color: #4f46e5;
            border-bottom: 2.5px solid #4f46e5; display: inline-block;
            min-width: 180px; padding-bottom: 2px; letter-spacing: 1px;
        }
        .qr-block { display: flex; flex-direction: column; align-items: center; gap: 6px; flex-shrink: 0; }
        .qr-block-inner {
            background: #fff; border: 2px solid #e0e7ff; border-radius: 14px;
            padding: 10px; box-shadow: 0 2px 10px rgba(79,70,229,0.10);
        }
        #qrcode canvas, #qrcode img { display: block; border-radius: 4px; }
        .qr-label { font-size: 0.74rem; color: #64748b; text-align: center; letter-spacing: 0.3px; }
        .qr-kode {
            font-size: 0.7rem; font-weight: 700; color: #4f46e5;
            letter-spacing: 1px; font-family: monospace;
            background: #e0e7ff; padding: 3px 10px; border-radius: 8px;
        }
        @media (max-width: 700px) {
            .qr-area { flex-direction: column; align-items: center; }
            .qr-area .sign-wrap { justify-content: center; }
        }

        /* ===== DETAIL NILAI ===== */
        .nilai-box {
            max-width: 1100px; margin: 0 auto; background: #fff; border-radius: 28px;
            padding: 48px 52px 40px 52px;
            box-shadow: 0 10px 40px rgba(79,70,229,0.11), 0 2px 8px rgba(0,0,0,0.05);
        }
        @media (max-width: 700px) { .nilai-box { padding: 28px 14px; } }

        .nilai-header { text-align: center; margin-bottom: 32px; padding-bottom: 20px; border-bottom: 2px dashed #e0e7ff; }
        .nilai-header h2 { font-size: 2rem; font-weight: 800; color: #4f46e5; margin-bottom: 8px; }
        .nilai-header p { color: #64748b; font-size: 1rem; margin: 0; }

        .nilai-akhir-box {
            background: linear-gradient(135deg, #4f46e5 0%, #06b6d4 100%);
            border-radius: 20px; padding: 28px 36px; color: #fff;
            display: flex; align-items: center; justify-content: space-between;
            flex-wrap: wrap; gap: 16px; margin-bottom: 28px;
        }
        .nilai-akhir-left { display: flex; flex-direction: column; gap: 4px; }
        .nilai-akhir-label { font-size: 0.95rem; font-weight: 600; opacity: 0.85; }
        .nilai-akhir-score { font-size: 4rem; font-weight: 900; line-height: 1; }
        .nilai-akhir-predikat {
            font-size: 1.05rem; font-weight: 700; background: rgba(255,255,255,0.22);
            padding: 10px 26px; border-radius: 20px; align-self: center;
        }
        .nilai-akhir-note { font-size: 0.82rem; opacity: 0.72; align-self: flex-end; }

        .nilai-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; margin-bottom: 28px; }
        @media (max-width: 600px) {
            .nilai-grid { grid-template-columns: 1fr; }
            .nilai-akhir-box { flex-direction: column; text-align: center; }
            .nilai-akhir-note { align-self: center; }
        }

        .nilai-card { border-radius: 16px; padding: 20px 20px 16px 20px; display: flex; align-items: flex-start; gap: 14px; border: 1.5px solid #e0e7ff; }
        .nilai-card-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.35rem; flex-shrink: 0; }
        .nilai-card-info { flex: 1; }
        .nilai-card-label { font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.6px; color: #64748b; margin-bottom: 2px; }
        .nilai-card-score { font-size: 2.2rem; font-weight: 800; line-height: 1.1; }
        .nilai-card-predikat { font-size: 0.8rem; font-weight: 700; margin-top: 2px; }
        .nilai-bar-bg { background: #e0e7ff; border-radius: 8px; height: 7px; overflow: hidden; margin-top: 10px; }
        .nilai-bar-fill { height: 7px; border-radius: 8px; }

        .nilai-keterangan { background: #f8fafc; border-radius: 12px; padding: 16px 20px; border-left: 4px solid #4f46e5; margin-bottom: 22px; }
        .nilai-keterangan .ket-label { font-size: 0.8rem; font-weight: 700; color: #4f46e5; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.5px; }
        .nilai-keterangan p { color: #334155; margin: 0; font-size: 0.96rem; }

        .nilai-info-row {
            display: flex; gap: 20px; flex-wrap: wrap; justify-content: center;
            font-size: 0.9rem; color: #64748b; padding-top: 18px; border-top: 1px dashed #e0e7ff;
        }
        .nilai-info-row span { display: flex; align-items: center; gap: 6px; }

        .no-nilai-box { text-align: center; padding: 60px 20px; color: #94a3b8; }
        .no-nilai-box i { font-size: 3.5rem; margin-bottom: 14px; display: block; color: #cbd5e1; }

        /* ===== PRINT ===== */
        @media print {
            body { background: #fff !important; padding: 0; }
            .print-btn-wrap { display: none !important; }
            .lembar { padding: 40px 30px; }
            .lembar-sertifikat { background: #fff !important; }
            .lembar-nilai { background: #fff !important; border-top: none; page-break-before: always; }
            .sertifikat-box, .nilai-box { box-shadow: none; }
        }
    </style>
</head>
<body>

    <!-- Tombol aksi (tidak tercetak) -->
    <!-- <div class="print-btn-wrap">
        <a href="javascript:history.back()" class="btn-back"><i class="fas fa-arrow-left"></i> Kembali</a>
        <button class="btn-print" onclick="window.print()"><i class="fas fa-print"></i> Cetak / Simpan PDF</button>
    </div> -->

    <!-- ============================================================
         LEMBAR 1 — SERTIFIKAT
    ============================================================ -->
    <div class="lembar lembar-sertifikat">
        <br>
        <div class="sertifikat-box">
            <div class="sertifikat-ribbon">
                <i class="fas fa-certificate"></i> SERTIFIKAT <i class="fas fa-award"></i>
            </div>
            <div class="sertifikat-logo">
                <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" alt="Logo">
            </div>
            <div class="sertifikat-title">
                <i class="fas fa-graduation-cap"></i> Sertifikat Pelatihan <i class="fas fa-graduation-cap"></i>
            </div>
            <div class="sertifikat-detail">
                <i class="fas fa-user"></i> Sertifikat ini diberikan kepada:
            </div>
            <div class="sertifikat-nama"><?= htmlspecialchars($data['peserta']); ?></div>
            <div class="sertifikat-detail">
                <i class="fas fa-check-circle text-success"></i>
                Sebagai peserta yang telah dinyatakan
                <span style="color:#10b981;font-weight:600;">LULUS</span> pada pelatihan:
            </div>
            <div class="sertifikat-pelatihan">
                <i class="fas fa-book-open"></i> <?= htmlspecialchars($data['nama_pelatihan']); ?>
            </div>
            <div class="sertifikat-meta">
                <div class="sertifikat-meta-item">
                    <i class="fas fa-building"></i> Penyelenggara: <b><?= htmlspecialchars($data['penyelenggara'] ?: '-'); ?></b>
                </div>
                <div class="sertifikat-meta-item">
                    <i class="fas fa-chalkboard-teacher"></i> Instruktur: <b><?= htmlspecialchars($data['instruktur'] ?: '-'); ?></b>
                </div>
                <div class="sertifikat-meta-item">
                    <i class="fas fa-calendar-check"></i> Tanggal Lulus: <b><?= format_tanggal($data['tanggal_ikut']); ?></b>
                </div>
            </div>
            <div class="sertifikat-badge">
                <span class="badge bg-success"><i class="fas fa-trophy"></i> Lulus</span>
                <span class="badge bg-info"><i class="fas fa-user-graduate"></i> Peserta</span>
                <span class="badge bg-warning text-dark"><i class="fas fa-crown"></i> UPTD BALATKOP</span>
            </div>

            <!-- Baris bawah: signature kiri + QR kanan -->
            <div class="qr-area">
                <div class="sign-wrap">
                    <div class="sign-label"><i class="fas fa-signature"></i> Instruktur,</div>
                    <div class="sign-name"><?= htmlspecialchars($data['instruktur'] ?: '-'); ?></div>
                </div>
                <div class="qr-block">
                    <div class="qr-block-inner">
                        <div id="qrcode"></div>
                    </div>
                    <div class="qr-label"><i class="fas fa-qrcode me-1"></i> Scan untuk verifikasi</div>
                    <div class="qr-kode"><?= htmlspecialchars($kode_sertifikat) ?></div>
                </div>
            </div>

            <div class="sertifikat-footer">
                <span><i class="fas fa-map-marker-alt"></i> UPTD BALATKOP</span>
                <span><i class="fas fa-calendar"></i> <?= date('Y') ?></span>
            </div>
        </div>
    </div>

    <!-- ============================================================
         LEMBAR 2 — DETAIL NILAI
    ============================================================ -->
    <div class="lembar lembar-nilai">
        <br>
        <div class="nilai-box">
            <div class="nilai-header">
                <h2><i class="fas fa-chart-bar me-2"></i>Laporan Nilai Pelatihan</h2>
                <p>
                    <i class="fas fa-book-open me-1"></i>
                    <strong><?= htmlspecialchars($data['nama_pelatihan']); ?></strong>
                    &nbsp;&mdash;&nbsp;
                    <i class="fas fa-user me-1"></i> <?= htmlspecialchars($data['peserta']); ?>
                </p>
            </div>

            <?php if ($nilai): ?>
                <?php
                $na = (float) $nilai['nilai_akhir'];
                $komponen = [
                    ['label' => 'Kehadiran',   'icon' => 'fas fa-calendar-check', 'val' => (int) $nilai['nilai_kehadiran']],
                    ['label' => 'Partisipasi', 'icon' => 'fas fa-comments',        'val' => (int) $nilai['nilai_partisipasi']],
                    ['label' => 'Praktik',     'icon' => 'fas fa-tools',           'val' => (int) $nilai['nilai_praktik']],
                    ['label' => 'Sikap',       'icon' => 'fas fa-star',            'val' => (int) $nilai['nilai_sikap']],
                ];
                $predikat_akhir = $na >= 85 ? '🏆 Sangat Baik' : ($na >= 70 ? '✅ Baik' : ($na >= 55 ? '⚠️ Cukup' : '❌ Kurang'));
                ?>
                <div class="nilai-akhir-box">
                    <div class="nilai-akhir-left">
                        <div class="nilai-akhir-label"><i class="fas fa-trophy me-1"></i> Nilai Akhir</div>
                        <div class="nilai-akhir-score"><?= number_format($na, 2) ?></div>
                    </div>
                    <div class="nilai-akhir-predikat"><?= $predikat_akhir ?></div>
                    <div class="nilai-akhir-note">Rata-rata dari 4 komponen penilaian</div>
                </div>
                <div class="nilai-grid">
                    <?php foreach ($komponen as $item): $w = warnaNilai($item['val']); ?>
                        <div class="nilai-card" style="background:<?= $w['bg'] ?>; border-color:<?= $w['color'] ?>44;">
                            <div class="nilai-card-icon" style="background:<?= $w['color'] ?>20; color:<?= $w['color'] ?>;"><i class="<?= $item['icon'] ?>"></i></div>
                            <div class="nilai-card-info">
                                <div class="nilai-card-label"><?= $item['label'] ?></div>
                                <div class="nilai-card-score" style="color:<?= $w['color'] ?>;"><?= $item['val'] ?></div>
                                <div class="nilai-card-predikat" style="color:<?= $w['color'] ?>;"><?= $w['label'] ?></div>
                                <div class="nilai-bar-bg"><div class="nilai-bar-fill" style="width:<?= $item['val'] ?>%; background:<?= $w['color'] ?>;"></div></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php if (!empty($nilai['keterangan'])): ?>
                    <div class="nilai-keterangan">
                        <div class="ket-label"><i class="fas fa-comment-alt me-1"></i> Keterangan Instruktur</div>
                        <p><?= htmlspecialchars($nilai['keterangan']); ?></p>
                    </div>
                <?php endif; ?>
                <div class="nilai-info-row">
                    <span><i class="fas fa-chalkboard-teacher"></i> Instruktur: <strong><?= htmlspecialchars($data['instruktur'] ?: '-') ?></strong></span>
                    <span><i class="fas fa-calendar-check"></i> Tanggal Lulus: <strong><?= format_tanggal($data['tanggal_ikut']) ?></strong></span>
                    <span><i class="fas fa-clock"></i> Dievaluasi: <strong><?= date('d M Y', strtotime($nilai['created_at'])) ?></strong></span>
                    <span><i class="fas fa-barcode"></i> Kode: <strong style="font-family:monospace;"><?= htmlspecialchars($kode_sertifikat) ?></strong></span>
                </div>
            <?php else: ?>
                <div class="no-nilai-box">
                    <i class="fas fa-clipboard-list"></i>
                    <p class="fw-semibold mb-1">Nilai evaluasi belum tersedia</p>
                    <small class="text-muted">Silakan hubungi instruktur untuk informasi lebih lanjut.</small>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- QRCode.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
        new QRCode(document.getElementById("qrcode"), {
            text        : <?= json_encode($verify_url) ?>,
            width       : 110,
            height      : 110,
            colorDark   : "#4f46e5",
            colorLight  : "#ffffff",
            correctLevel: QRCode.CorrectLevel.M
        });
    </script>

    <?php if (isset($_GET['print']) && $_GET['print'] == 1): ?>
        <script>
            window.onload = function () { setTimeout(function(){ window.print(); }, 700); }
        </script>
    <?php endif; ?>

</body>
</html>