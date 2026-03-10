<?php
session_start();
require_once '../config.php';
check_login();

$conn = getConnection();

// Semua pelatihan
$pelatihan = $conn->query("
    SELECT 
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
            font-size: 12pt;
            margin: 40px;
        }

        h2,
        h3 {
            text-align: center;
            margin-bottom: 5px;
        }

        hr {
            border: 1px solid #000;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        table,
        th,
        td {
            border: 1px solid #000;
        }

        th,
        td {
            padding: 6px;
            text-align: center;
        }

        th {
            background-color: #f0f0f0;
        }

        .text-left {
            text-align: left;
        }

        .ttd {
            width: 100%;
            margin-top: 50px;
        }

        .ttd td {
            border: none;
            text-align: center;
        }
    </style>
</head>

<body onload="window.print()">

    <h2>LAPORAN PELATIHAN</h2>
    <h3><?= APP_NAME; ?></h3>
    <hr>

    <h4>A. Semua Pelatihan</h4>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Pelatihan</th>
                <th>Penyelenggara</th>
                <th>Instruktur</th>
                <th>Tanggal</th>
                <th>Peserta</th>
                <th>Lulus</th>
                <th>Tidak Lulus</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1;
            while ($row = $pelatihan->fetch_assoc()): ?>
                <tr>
                    <td><?= $no++; ?></td>
                    <td class="text-left"><?= htmlspecialchars($row['nama_pelatihan']); ?></td>
                    <td><?= $row['penyelenggara'] ?: '-'; ?></td>
                    <td><?= $row['instruktur'] ?: '-'; ?></td>
                    <td><?= format_tanggal($row['created_at']); ?></td>
                    <td><?= $row['total_peserta']; ?></td>
                    <td><?= $row['total_lulus']; ?></td>
                    <td><?= $row['total_tidak_lulus']; ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <h4 style="margin-top:30px;">B. Pelatihan Telah Selesai</h4>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Pelatihan</th>
                <th>Penyelenggara</th>
                <th>Instruktur</th>
                <th>Tanggal</th>
                <th>Peserta</th>
                <th>Lulus</th>
                <th>Tidak Lulus</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1;
            while ($row = $pelatihan_selesai->fetch_assoc()): ?>
                <tr>
                    <td><?= $no++; ?></td>
                    <td class="text-left"><?= htmlspecialchars($row['nama_pelatihan']); ?></td>
                    <td><?= $row['penyelenggara'] ?: '-'; ?></td>
                    <td><?= $row['instruktur'] ?: '-'; ?></td>
                    <td><?= format_tanggal($row['created_at']); ?></td>
                    <td><?= $row['total_peserta']; ?></td>
                    <td><?= $row['total_lulus']; ?></td>
                    <td><?= $row['total_tidak_lulus']; ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

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