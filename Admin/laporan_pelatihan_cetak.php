<?php
session_start();
require_once '../config.php';
check_login();

$conn = getConnection();

// Semua pelatihan
$pelatihan = $conn->query("
    SELECT 
        p.id,
        p.nama_pelatihan,
        p.penyelenggara,
        u.nama_lengkap AS instruktur,
        p.created_at,
        COUNT(rp.id) AS total_peserta,
        SUM(CASE WHEN rp.status = 'Lulus' THEN 1 ELSE 0 END) AS total_lulus,
        SUM(CASE WHEN rp.status = 'Tidak Lulus' THEN 1 ELSE 0 END) AS total_tidak_lulus
    FROM pelatihan p
    LEFT JOIN users u ON p.instruktur_id = u.id
    LEFT JOIN riwayat_peserta rp ON p.id = rp.pelatihan_id
    GROUP BY p.id
    ORDER BY p.created_at DESC
");

// Pelatihan selesai
$pelatihan_selesai = $conn->query("
    SELECT 
        p.id,
        p.nama_pelatihan,
        p.penyelenggara,
        u.nama_lengkap AS instruktur,
        p.created_at,
        COUNT(rp.id) AS total_peserta,
        SUM(CASE WHEN rp.status = 'Lulus' THEN 1 ELSE 0 END) AS total_lulus,
        SUM(CASE WHEN rp.status = 'Tidak Lulus' THEN 1 ELSE 0 END) AS total_tidak_lulus
    FROM pelatihan p
    LEFT JOIN users u ON p.instruktur_id = u.id
    LEFT JOIN riwayat_peserta rp ON p.id = rp.pelatihan_id
    WHERE rp.status IN ('Lulus','Tidak Lulus')
    GROUP BY p.id
    HAVING total_lulus > 0 OR total_tidak_lulus > 0
    ORDER BY p.created_at DESC
");

// Peserta per pelatihan
$peserta_query = $conn->query("
    SELECT 
        rp.pelatihan_id,
        ps.id AS peserta_id,
        ps.nama_lengkap,
        ps.email,
        ps.telepon,
        rp.tanggal_ikut,
        rp.status
    FROM riwayat_peserta rp
    JOIN peserta ps ON rp.peserta_id = ps.id
    ORDER BY rp.pelatihan_id, ps.nama_lengkap
");

$peserta_per_pelatihan = [];
while ($p = $peserta_query->fetch_assoc()) {
    $peserta_per_pelatihan[$p['pelatihan_id']][] = $p;
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Laporan Pelatihan</title>
    <style>
        body {
            font-family: "Times New Roman", serif;
            font-size: 11pt;
            margin: 40px;
        }
        h2, h3 { text-align: center; margin-bottom: 5px; }
        h4 { margin-top: 25px; margin-bottom: 5px; }
        h5 { margin: 8px 0 4px 0; font-size: 11pt; }
        hr { border: 1px solid #000; }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }
        table, th, td { border: 1px solid #000; }
        th, td { padding: 5px 6px; text-align: center; font-size: 10pt; }
        th { background-color: #f0f0f0; }
        .text-left { text-align: left; }

        /* Sub-tabel peserta */
        .sub-tabel {
            margin: 6px 0 10px 20px;
            width: calc(100% - 20px);
        }
        .sub-tabel th { background-color: #e0f0ff; font-size: 9.5pt; }
        .sub-tabel td { font-size: 9.5pt; }
        .label-peserta {
            font-style: italic;
            font-size: 10pt;
            margin: 4px 0 2px 20px;
        }

        /* TTD */
        .ttd { width: 100%; margin-top: 50px; }
        .ttd td { border: none; text-align: center; }

        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">

    <h2>LAPORAN PELATIHAN</h2>
    <h3><?= APP_NAME; ?></h3>
    <hr>

    <!-- A. SEMUA PELATIHAN -->
    <h4>A. Semua Pelatihan</h4>
    <table>
        <thead>
            <tr>
                <th width="30">No</th>
                <th>Nama Pelatihan</th>
                <th>Penyelenggara</th>
                <th>Instruktur</th>
                <th>Tanggal</th>
                <th width="55">Peserta</th>
                <th width="45">Lulus</th>
                <th width="70">Tidak Lulus</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1;
            $rows_a = [];
            while ($row = $pelatihan->fetch_assoc()) {
                $rows_a[] = $row;
            }
            foreach ($rows_a as $row): ?>
                <tr>
                    <td><?= $no++; ?></td>
                    <td class="text-left"><strong><?= htmlspecialchars($row['nama_pelatihan']); ?></strong></td>
                    <td><?= htmlspecialchars($row['penyelenggara'] ?: '-'); ?></td>
                    <td><?= htmlspecialchars($row['instruktur'] ?: '-'); ?></td>
                    <td><?= format_tanggal($row['created_at']); ?></td>
                    <td><?= $row['total_peserta']; ?></td>
                    <td><?= $row['total_lulus'] ?: 0; ?></td>
                    <td><?= $row['total_tidak_lulus'] ?: 0; ?></td>
                </tr>

                <!-- Sub-tabel peserta -->
                <?php if (!empty($peserta_per_pelatihan[$row['id']])): ?>
                <tr>
                    <td colspan="8" style="padding: 0; border-top: none;">
                        <table class="sub-tabel">
                            <thead>
                                <tr>
                                    <th width="30">No</th>
                                    <th width="45">ID</th>
                                    <th>Nama Lengkap</th>
                                    <th>Email</th>
                                    <th>Telepon</th>
                                    <th>Tgl Ikut</th>
                                    <th width="70">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $np = 1;
                                foreach ($peserta_per_pelatihan[$row['id']] as $p): ?>
                                <tr>
                                    <td><?= $np++; ?></td>
                                    <td><?= $p['peserta_id']; ?></td>
                                    <td class="text-left"><?= htmlspecialchars($p['nama_lengkap']); ?></td>
                                    <td><?= htmlspecialchars($p['email'] ?: '-'); ?></td>
                                    <td><?= htmlspecialchars($p['telepon'] ?: '-'); ?></td>
                                    <td><?= $p['tanggal_ikut'] ? format_tanggal($p['tanggal_ikut']) : '-'; ?></td>
                                    <td><?= $p['status']; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </td>
                </tr>
                <?php endif; ?>

            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- B. PELATIHAN SELESAI -->
    <h4 style="margin-top:30px;">B. Pelatihan Telah Selesai</h4>
    <table>
        <thead>
            <tr>
                <th width="30">No</th>
                <th>Nama Pelatihan</th>
                <th>Penyelenggara</th>
                <th>Instruktur</th>
                <th>Tanggal</th>
                <th width="55">Peserta</th>
                <th width="45">Lulus</th>
                <th width="70">Tidak Lulus</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1;
            $rows_b = [];
            while ($row = $pelatihan_selesai->fetch_assoc()) {
                $rows_b[] = $row;
            }
            foreach ($rows_b as $row): ?>
                <tr>
                    <td><?= $no++; ?></td>
                    <td class="text-left"><strong><?= htmlspecialchars($row['nama_pelatihan']); ?></strong></td>
                    <td><?= htmlspecialchars($row['penyelenggara'] ?: '-'); ?></td>
                    <td><?= htmlspecialchars($row['instruktur'] ?: '-'); ?></td>
                    <td><?= format_tanggal($row['created_at']); ?></td>
                    <td><?= $row['total_peserta']; ?></td>
                    <td><?= $row['total_lulus'] ?: 0; ?></td>
                    <td><?= $row['total_tidak_lulus'] ?: 0; ?></td>
                </tr>

                <?php if (!empty($peserta_per_pelatihan[$row['id']])): ?>
                <tr>
                    <td colspan="8" style="padding: 0; border-top: none;">
                        <table class="sub-tabel">
                            <thead>
                                <tr>
                                    <th width="30">No</th>
                                    <th width="45">ID</th>
                                    <th>Nama Lengkap</th>
                                    <th>Email</th>
                                    <th>Telepon</th>
                                    <th>Tgl Ikut</th>
                                    <th width="70">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $np = 1;
                                foreach ($peserta_per_pelatihan[$row['id']] as $p): ?>
                                <tr>
                                    <td><?= $np++; ?></td>
                                    <td><?= $p['peserta_id']; ?></td>
                                    <td class="text-left"><?= htmlspecialchars($p['nama_lengkap']); ?></td>
                                    <td><?= htmlspecialchars($p['email'] ?: '-'); ?></td>
                                    <td><?= htmlspecialchars($p['telepon'] ?: '-'); ?></td>
                                    <td><?= $p['tanggal_ikut'] ? format_tanggal($p['tanggal_ikut']) : '-'; ?></td>
                                    <td><?= $p['status']; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </td>
                </tr>
                <?php endif; ?>

            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- TTD -->
    <table class="ttd">
        <tr>
            <td width="60%"></td>
            <td>
                Padang, <?= date('d F Y'); ?><br>
                Pimpinan<br><br><br><br>
                <b>(__________________)</b>
            </td>
        </tr>
    </table>

</body>
</html>