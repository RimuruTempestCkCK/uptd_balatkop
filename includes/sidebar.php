<?php
$current_page = basename($_SERVER['PHP_SELF']);
$role = $_SESSION['role'] ?? '';
?>

<nav class="col-md-3 col-lg-2 d-md-block sidebar">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">

            <!-- DASHBOARD (SEMUA ROLE) -->
            <li class="nav-section">Dashboard</li>
            <li class="nav-item">
                <a class="nav-link
                    <?php
                    // Tandai aktif jika dashboard sesuai role
                    if (
                        ($role === 'Admin'      && $current_page == 'dashboard_admin.php') ||
                        ($role === 'Peserta'    && $current_page == 'dashboard_peserta.php') ||
                        ($role === 'Instruktur' && $current_page == 'dashboard_instruktur.php') ||
                        ($role === 'Pimpinan'   && $current_page == 'dashboard_pimpinan.php')
                    ) echo 'active';
                    ?>"
                    href="<?php
                            // Arahkan ke dashboard sesuai role
                            if ($role === 'Admin') {
                                echo 'dashboard_admin.php';
                            } elseif ($role === 'Peserta') {
                                echo 'dashboard_peserta.php';
                            } elseif ($role === 'Instruktur') {
                                echo 'dashboard_instruktur.php';
                            } elseif ($role === 'Pimpinan') {
                                echo 'dashboard_pimpinan.php';
                            } else {
                                echo 'index.php';
                            }
                            ?>">
                    <i class="fas fa-home"></i>
                    Dashboard
                </a>
            </li>

            <?php if ($role === 'Admin'): ?>
                <!-- ================= ADMIN ================= -->
                <li class="nav-section">Master Data</li>
                <li class="nav-item">
                    <a class="nav-link <?= $current_page == 'peserta.php' ? 'active' : ''; ?>" href="peserta.php">
                        <i class="fas fa-users"></i>
                        Data Peserta Pelatihan
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $current_page == 'instruktur.php' ? 'active' : ''; ?>" href="instruktur.php">
                        <i class="fas fa-chalkboard-teacher"></i>
                        Data Instruktur Pelatihan
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $current_page == 'pelatihan.php' ? 'active' : ''; ?>" href="pelatihan.php">
                        <i class="fas fa-book"></i>
                        Pelatihan
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $current_page == 'ruangan.php' ? 'active' : ''; ?>" href="ruangan.php">
                        <i class="fas fa-door-open"></i>
                        Ruangan
                    </a>
                </li>
                <li class="nav-section">Jadwal</li>
                <li class="nav-item">
                    <a class="nav-link <?= $current_page == 'jadwal.php' ? 'active' : ''; ?>" href="jadwal.php">
                        <i class="fas fa-calendar-alt"></i>
                        Jadwal Pelatihan
                    </a>
                </li>
                <li class="nav-section">Laporan</li>
                <li class="nav-item">
                    <a class="nav-link <?= $current_page == 'laporan_pelatihan.php' ? 'active' : ''; ?>" href="laporan_pelatihan.php">
                        <i class="fas fa-file-alt"></i>
                        Laporan Pelatihan
                    </a>
                </li>
            <?php endif; ?>

            <?php if ($role === 'Instruktur'): ?>
                <!-- ================= INSTRUKTUR ================= -->
                <li class="nav-section">Jadwal</li>
                <li class="nav-item">
                    <a class="nav-link <?= $current_page == 'jadwal.php' ? 'active' : ''; ?>" href="jadwal.php">
                        <i class="fas fa-calendar-alt"></i>
                        Jadwal Mengajar
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $current_page == 'evaluasi.php' ? 'active' : ''; ?>" href="evaluasi.php">
                        <i class="fas fa-star"></i>
                        Evaluasi Peserta
                    </a>
                </li>
                <!-- <li class="nav-section">Laporan</li>
                <li class="nav-item">
                    <a class="nav-link <?= $current_page == 'laporan.php' ? 'active' : ''; ?>" href="laporan.php">
                        <i class="fas fa-file-alt"></i>
                        Laporan Pelatihan
                    </a>
                </li> -->
            <?php endif; ?>

            <?php if ($role === 'Peserta'): ?>
                <!-- ================= PESERTA ================= -->
                <li class="nav-section">Pelatihan</li>
                <li class="nav-item">
                    <a class="nav-link <?= $current_page == 'jadwal.php' ? 'active' : ''; ?>" href="jadwal.php">
                        <i class="fas fa-calendar-alt"></i>
                        Jadwal Pelatihan
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $current_page == 'pelatihan.php' ? 'active' : ''; ?>" href="pelatihan.php">
                        <i class="fas fa-book"></i>
                        Pelatihan
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $current_page == 'riwayat.php' ? 'active' : ''; ?>" href="riwayat.php">
                        <i class="fas fa-book"></i>
                        Riwayat Pelatihan
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $current_page == 'sertifikat.php' ? 'active' : ''; ?>" href="sertifikat.php">
                        <i class="fas fa-certificate"></i>
                        Sertifikat
                    </a>
                </li>
                <li class="nav-section">Data Diri Peserta</li>
                <li class="nav-item">
                    <a class="nav-link <?= ($current_page == 'edit_profil.php' || strpos($_SERVER['REQUEST_URI'], 'edit_profil.php') !== false) ? 'active' : ''; ?>" href="./edit_profil.php">
                        <i class="fas fa-user-edit"></i>
                        Edit Profil
                    </a>
                </li>
            <?php endif; ?>

            <?php if ($role === 'Pimpinan'): ?>
                <!-- ================= PIMPINAN ================= -->
                <li class="nav-section">Laporan</li>
                <li class="nav-item">
                    <a class="nav-link <?= $current_page == 'laporan_pelatihan.php' ? 'active' : ''; ?>" href="laporan_pelatihan.php">
                        <i class="fas fa-file-alt"></i>
                        Laporan Pelatihan
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </div>
</nav>