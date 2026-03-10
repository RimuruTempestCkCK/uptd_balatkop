<?php
/**
 * verify.php — Halaman publik, tidak perlu login
 * Letakkan di: C:\xampp\htdocs\ode\verify.php
 * QR di view_sertifikat.php mengarah ke:
 *   http://localhost/ode/verify.php?kode=CERT-0001-XXXXXXXX
 */
require_once 'config.php';
// ⚠️ Tidak ada session_start() / check_login() — halaman ini publik

$kode  = strtoupper(trim($_GET['kode'] ?? ''));
$data  = null;
$nilai = null;

if ($kode !== '' && preg_match('/^CERT-(\d{4})-([A-F0-9]{8})$/', $kode, $m)) {
    $riwayat_id = (int) $m[1];
    $hash_input = strtolower($m[2]);

    $conn = getConnection();

    $stmt = $conn->prepare("
        SELECT
            rp.id           AS riwayat_id,
            rp.peserta_id,
            rp.pelatihan_id,
            rp.tanggal_ikut,
            p.nama_pelatihan,
            p.penyelenggara,
            u.nama_lengkap  AS instruktur,
            ps.nama_lengkap AS peserta
        FROM riwayat_peserta rp
        JOIN pelatihan p  ON rp.pelatihan_id = p.id
        LEFT JOIN users u ON p.instruktur_id = u.id
        JOIN peserta ps   ON rp.peserta_id   = ps.id
        WHERE rp.id = ? AND rp.status = 'Lulus'
        LIMIT 1
    ");
    $stmt->bind_param("i", $riwayat_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($row) {
        // Verifikasi hash agar kode tidak bisa dipalsukan
        $expected = strtolower(substr(md5($row['peserta_id'] . $row['pelatihan_id'] . $row['tanggal_ikut']), 0, 8));
        if ($hash_input === $expected) {
            $data = $row;

            $stmt2 = $conn->prepare("
                SELECT nilai_kehadiran, nilai_partisipasi, nilai_praktik,
                       nilai_sikap, nilai_akhir, keterangan, created_at
                FROM evaluasi_pelatihan
                WHERE peserta_id = ? AND pelatihan_id = ?
                LIMIT 1
            ");
            $stmt2->bind_param("ii", $row['peserta_id'], $row['pelatihan_id']);
            $stmt2->execute();
            $nilai = $stmt2->get_result()->fetch_assoc();
            $stmt2->close();
        }
    }
    $conn->close();
}

function warnaNilai($n) {
    if ($n >= 85) return ['bg' => '#d1fae5', 'color' => '#065f46', 'label' => 'Sangat Baik'];
    if ($n >= 70) return ['bg' => '#dbeafe', 'color' => '#1e40af', 'label' => 'Baik'];
    if ($n >= 55) return ['bg' => '#fef9c3', 'color' => '#854d0e', 'label' => 'Cukup'];
    return ['bg' => '#fee2e2', 'color' => '#991b1b', 'label' => 'Kurang'];
}

$kode_display = $data
    ? 'CERT-' . str_pad($data['riwayat_id'], 4, '0', STR_PAD_LEFT)
      . '-' . strtoupper(substr(md5($data['peserta_id'] . $data['pelatihan_id'] . $data['tanggal_ikut']), 0, 8))
    : $kode;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $data ? 'Sertifikat — ' . htmlspecialchars($data['peserta']) : 'Sertifikat Tidak Valid' ?> | UPTD BALATKOP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; }
        body {
            background: linear-gradient(135deg, #f3f4f6 0%, #e0e7ff 100%);
            min-height: 100vh;
            padding: 0 0 60px 0;
            font-family: 'Segoe UI', sans-serif;
        }

        /* TOP BAR */
        .topbar {
            background: linear-gradient(90deg, #4f46e5 0%, #06b6d4 100%);
            color: #fff; padding: 13px 24px;
            display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;
        }
        .topbar-brand { display: flex; align-items: center; gap: 10px; }
        .topbar-brand .t-icon {
            width: 36px; height: 36px; border-radius: 10px;
            background: rgba(255,255,255,0.2);
            display: flex; align-items: center; justify-content: center; font-size: 1rem;
        }
        .topbar-brand .t-name { font-weight: 800; font-size: 0.97rem; line-height: 1.2; }
        .topbar-brand .t-sub  { font-size: 0.7rem; opacity: 0.8; }
        .verified-chip {
            background: rgba(255,255,255,0.2); border-radius: 20px;
            padding: 5px 14px; font-size: 0.78rem; font-weight: 700;
            display: flex; align-items: center; gap: 6px;
        }

        /* NOT FOUND */
        .not-found { max-width: 460px; margin: 80px auto; text-align: center; padding: 0 20px; }
        .nf-icon {
            width: 80px; height: 80px; border-radius: 50%;
            background: #fee2e2; color: #ef4444;
            display: flex; align-items: center; justify-content: center;
            font-size: 2rem; margin: 0 auto 20px;
        }
        .not-found h2 { font-size: 1.4rem; font-weight: 800; color: #1e293b; margin-bottom: 10px; }
        .not-found p  { color: #64748b; font-size: 0.95rem; }

        /* LEMBAR */
        .lembar { padding: 60px 20px 40px 20px; }
        .lembar-sertifikat { background: linear-gradient(135deg, #f3f4f6 0%, #e0e7ff 100%); }
        .lembar-nilai { background: linear-gradient(135deg, #f0f9ff 0%, #e0e7ff 100%); border-top: 4px solid #4f46e5; }

        /* SERTIFIKAT BOX */
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
        @media (max-width: 600px) { .sertifikat-ribbon { font-size: 1.1rem; padding: 10px 20px; top: -38px; } }

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
            display: flex; align-items: center; gap: 8px; justify-content: center; flex-wrap: wrap;
        }
        .sertifikat-nama {
            font-size: 2.2rem; font-weight: bold; color: #1e293b;
            margin: 28px 0 14px 0; letter-spacing: 1px;
        }
        .sertifikat-pelatihan {
            font-size: 1.35rem; font-weight: 700; color: #4f46e5; margin-bottom: 10px;
            display: flex; align-items: center; gap: 8px; justify-content: center; flex-wrap: wrap;
        }
        .sertifikat-meta {
            display: flex; justify-content: center; gap: 40px;
            margin: 32px 0 10px 0; flex-wrap: wrap;
        }
        .sertifikat-meta-item { font-size: 1.08rem; color: #555; display: flex; align-items: center; gap: 7px; }
        .sertifikat-badge { margin-top: 24px; display: flex; justify-content: center; gap: 18px; flex-wrap: wrap; }
        .sertifikat-badge .badge {
            font-size: 1.05rem; padding: 8px 18px; border-radius: 18px;
            font-weight: 600; display: flex; align-items: center; gap: 7px;
        }

        /* TTD + STAMP */
        .qr-area {
            display: flex; align-items: center; justify-content: space-between;
            margin-top: 32px; gap: 20px; flex-wrap: wrap;
        }
        .sign-wrap { flex: 1; display: flex; align-items: center; gap: 32px; justify-content: flex-end; flex-wrap: wrap; }
        .sign-label { font-size: 1.08rem; color: #666; display: flex; align-items: center; gap: 6px; }
        .sign-name {
            font-size: 1.18rem; font-weight: bold; color: #4f46e5;
            border-bottom: 2.5px solid #4f46e5; display: inline-block;
            min-width: 180px; padding-bottom: 2px; letter-spacing: 1px;
        }
        .stamp-verified {
            width: 110px; height: 110px; border-radius: 50%;
            border: 4px solid #10b981; color: #10b981;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            font-size: 0.6rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px;
            transform: rotate(-15deg); flex-shrink: 0;
            box-shadow: 0 0 0 2px #10b98130;
        }
        .stamp-verified i { font-size: 1.8rem; margin-bottom: 2px; }

        .sertifikat-footer {
            margin-top: 40px; font-size: 1.05rem; color: #888;
            border-top: 1px dashed #b3bcf5; padding-top: 18px;
            display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;
        }
        .kode-chip {
            font-family: monospace; font-size: 0.75rem; font-weight: 700;
            color: #4f46e5; background: #e0e7ff; padding: 4px 12px; border-radius: 8px;
        }

        /* DETAIL NILAI */
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
            .sertifikat-title { font-size: 1.5rem; }
            .sertifikat-nama  { font-size: 1.4rem; }
            .sertifikat-meta  { flex-direction: column; gap: 8px; }
            .sertifikat-footer { flex-direction: column; }
            .qr-area  { justify-content: center; }
            .sign-wrap { justify-content: center; }
        }

        .nilai-card { border-radius: 16px; padding: 20px 20px 16px 20px; display: flex; align-items: flex-start; gap: 14px; border: 1.5px solid #e0e7ff; }
        .nilai-card-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.35rem; flex-shrink: 0; }
        .nilai-card-label { font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.6px; color: #64748b; margin-bottom: 2px; }
        .nilai-card-score { font-size: 2.2rem; font-weight: 800; line-height: 1.1; }
        .nilai-card-predikat { font-size: 0.8rem; font-weight: 700; margin-top: 2px; }
        .nilai-bar-bg { background: #e0e7ff; border-radius: 8px; height: 7px; overflow: hidden; margin-top: 10px; }
        .nilai-bar-fill { height: 7px; border-radius: 8px; }

        .nilai-keterangan { background: #f8fafc; border-radius: 12px; padding: 16px 20px; border-left: 4px solid #4f46e5; margin-bottom: 22px; }
        .nilai-keterangan .ket-label { font-size: 0.8rem; font-weight: 700; color: #4f46e5; margin-bottom: 6px; text-transform: uppercase; }
        .nilai-keterangan p { color: #334155; margin: 0; font-size: 0.96rem; }

        .nilai-info-row {
            display: flex; gap: 20px; flex-wrap: wrap; justify-content: center;
            font-size: 0.9rem; color: #64748b; padding-top: 18px; border-top: 1px dashed #e0e7ff;
        }
        .nilai-info-row span { display: flex; align-items: center; gap: 6px; }

        .no-nilai-box { text-align: center; padding: 60px 20px; color: #94a3b8; }
        .no-nilai-box i { font-size: 3.5rem; margin-bottom: 14px; display: block; color: #cbd5e1; }

        @media print {
            body { background: #fff !important; padding: 0; }
            .topbar { display: none; }
            .lembar { padding: 40px 30px; }
            .lembar-sertifikat { background: #fff !important; }
            .lembar-nilai { background: #fff !important; border-top: none; page-break-before: always; }
            .sertifikat-box, .nilai-box { box-shadow: none; }
        }
    </style>
</head>
<body>

<!-- TOP BAR -->
<div class="topbar">
    <div class="topbar-brand">
        <div class="t-icon"><i class="fas fa-shield-alt"></i></div>
        <div>
            <div class="t-name">UPTD BALATKOP</div>
            <div class="t-sub">Balai Pelatihan Koperasi</div>
        </div>
    </div>
    <?php if ($data): ?>
        <div class="verified-chip"><i class="fas fa-check-circle"></i> Sertifikat Terverifikasi</div>
    <?php endif; ?>
</div>

<?php if (!$data): ?>
<!-- ===== TIDAK VALID ===== -->
<div class="not-found">
    <div class="nf-icon"><i class="fas fa-times-circle"></i></div>
    <h2>Sertifikat Tidak Ditemukan</h2>
    <p>QR code tidak valid, kode telah dimanipulasi, atau sertifikat tidak terdaftar dalam sistem UPTD BALATKOP.</p>
    <?php if ($kode): ?>
        <p style="margin-top:10px;font-size:0.82rem;color:#94a3b8;">Kode: <code><?= htmlspecialchars($kode) ?></code></p>
    <?php endif; ?>
</div>

<?php else: ?>

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
        <div class="sertifikat-nama"><?= htmlspecialchars($data['peserta']) ?></div>
        <div class="sertifikat-detail">
            <i class="fas fa-check-circle text-success"></i>
            Sebagai peserta yang telah dinyatakan
            <span style="color:#10b981;font-weight:600;">LULUS</span> pada pelatihan:
        </div>
        <div class="sertifikat-pelatihan">
            <i class="fas fa-book-open"></i> <?= htmlspecialchars($data['nama_pelatihan']) ?>
        </div>
        <div class="sertifikat-meta">
            <div class="sertifikat-meta-item">
                <i class="fas fa-building"></i> Penyelenggara: <b><?= htmlspecialchars($data['penyelenggara'] ?: '-') ?></b>
            </div>
            <div class="sertifikat-meta-item">
                <i class="fas fa-chalkboard-teacher"></i> Instruktur: <b><?= htmlspecialchars($data['instruktur'] ?: '-') ?></b>
            </div>
            <div class="sertifikat-meta-item">
                <i class="fas fa-calendar-check"></i> Tanggal Lulus: <b><?= format_tanggal($data['tanggal_ikut']) ?></b>
            </div>
        </div>
        <div class="sertifikat-badge">
            <span class="badge bg-success"><i class="fas fa-trophy"></i> Lulus</span>
            <span class="badge bg-info"><i class="fas fa-user-graduate"></i> Peserta</span>
            <span class="badge bg-warning text-dark"><i class="fas fa-crown"></i> UPTD BALATKOP</span>
        </div>

        <!-- TTD + Stamp Verifikasi -->
        <div class="qr-area">
            <div class="stamp-verified">
                <i class="fas fa-check-double"></i>
                <span>Terverifikasi</span>
                <span>BALATKOP</span>
            </div>
            <div class="sign-wrap">
                <div>
                    <div class="sign-label"><i class="fas fa-signature"></i> Instruktur,</div>
                    <br>
                    <div class="sign-name"><?= htmlspecialchars($data['instruktur'] ?: '-') ?></div>
                </div>
            </div>
        </div>

        <div class="sertifikat-footer">
            <span><i class="fas fa-map-marker-alt"></i> UPTD BALATKOP</span>
            <span class="kode-chip"><?= htmlspecialchars($kode_display) ?></span>
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
                <strong><?= htmlspecialchars($data['nama_pelatihan']) ?></strong>
                &mdash;
                <i class="fas fa-user me-1"></i><?= htmlspecialchars($data['peserta']) ?>
            </p>
        </div>

        <?php if ($nilai):
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
            <div>
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
                    <div>
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
                <p><?= htmlspecialchars($nilai['keterangan']) ?></p>
            </div>
        <?php endif; ?>

        <div class="nilai-info-row">
            <span><i class="fas fa-chalkboard-teacher"></i> Instruktur: <strong><?= htmlspecialchars($data['instruktur'] ?: '-') ?></strong></span>
            <span><i class="fas fa-calendar-check"></i> Tanggal Lulus: <strong><?= format_tanggal($data['tanggal_ikut']) ?></strong></span>
            <span><i class="fas fa-clock"></i> Dievaluasi: <strong><?= date('d M Y', strtotime($nilai['created_at'])) ?></strong></span>
            <span><i class="fas fa-barcode"></i> Kode: <strong style="font-family:monospace;"><?= htmlspecialchars($kode_display) ?></strong></span>
        </div>

        <?php else: ?>
        <div class="no-nilai-box">
            <i class="fas fa-clipboard-list"></i>
            <p class="fw-semibold mb-1">Nilai evaluasi belum tersedia</p>
            <small>Silakan hubungi instruktur untuk informasi lebih lanjut.</small>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php endif; ?>
</body>
</html>