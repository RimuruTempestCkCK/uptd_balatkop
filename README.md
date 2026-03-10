# 🏛️ UPTD BALATKOP — Training Management System

<div align="center">

![UPTD BALATKOP](https://img.shields.io/badge/UPTD-BALATKOP-0D3B66?style=for-the-badge&labelColor=0D3B66&color=F4A261&logoColor=white)

**Sistem Informasi Manajemen Pelatihan Berbasis Web**

[![Status](https://img.shields.io/badge/Status-🟢_Active-0D3B66?style=for-the-badge)](/)
[![Version](https://img.shields.io/badge/Version-1.0.0-F4A261?style=for-the-badge)](/)
[![License](https://img.shields.io/badge/License-Educational-2EC4B6?style=for-the-badge)](/)
[![Platform](https://img.shields.io/badge/Platform-Web-E84855?style=for-the-badge)](/)

</div>

---

<div align="center">

## 🛠️ Tech Stack

![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![HTML5](https://img.shields.io/badge/HTML5-E34F26?style=for-the-badge&logo=html5&logoColor=white)
![CSS3](https://img.shields.io/badge/CSS3-1572B6?style=for-the-badge&logo=css3&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)
![cURL](https://img.shields.io/badge/cURL-073551?style=for-the-badge&logo=curl&logoColor=white)
![WhatsApp](https://img.shields.io/badge/WhatsApp_API-25D366?style=for-the-badge&logo=whatsapp&logoColor=white)
![XAMPP](https://img.shields.io/badge/XAMPP-FB7A24?style=for-the-badge&logo=xampp&logoColor=white)

</div>

---

## 📖 Tentang Sistem

Sistem Informasi Manajemen Pelatihan berbasis web yang digunakan untuk mengelola kegiatan pelatihan pada **UPTD BALATKOP**. Aplikasi ini membantu proses administrasi pelatihan seperti pendaftaran peserta, pengelolaan instruktur, jadwal pelatihan, ruangan, serta pembuatan laporan pelatihan secara terintegrasi.

---

## ✨ Fitur Utama

<table>
  <tr>
    <td>🔐 <b>Autentikasi Pengguna</b></td>
    <td>🎓 <b>Manajemen Pelatihan</b></td>
    <td>👥 <b>Manajemen Peserta</b></td>
  </tr>
  <tr>
    <td>Login & logout sistem<br>Registrasi akun peserta<br>Verifikasi akun</td>
    <td>Tambah, edit, hapus data<br>Pengelolaan jadwal<br>Penentuan instruktur & ruangan</td>
    <td>Pendaftaran pelatihan<br>Upload dokumen (KTP & KK)<br>Riwayat & sertifikat</td>
  </tr>
  <tr>
    <td>👨‍🏫 <b>Manajemen Instruktur</b></td>
    <td>📊 <b>Laporan</b></td>
    <td>📲 <b>Notifikasi WhatsApp</b></td>
  </tr>
  <tr>
    <td>Lihat jadwal pelatihan<br>Isi evaluasi pelatihan</td>
    <td>Laporan data pelatihan<br>Cetak laporan<br>Akses oleh pimpinan</td>
    <td>Kirim notifikasi otomatis<br>Integrasi WhatsApp API</td>
  </tr>
</table>

---

## 👤 Role Pengguna

| Role | Akses |
|------|-------|
| 🔴 **Admin** | Kelola pelatihan, instruktur, peserta, jadwal, ruangan, notifikasi WA, laporan |
| 🔵 **Instruktur** | Lihat jadwal, isi evaluasi pelatihan |
| 🟢 **Peserta** | Lihat jadwal, daftar pelatihan, kelola profil, riwayat, unduh sertifikat |
| 🟣 **Pimpinan** | Lihat & cetak laporan pelatihan |

---

## 📁 Struktur Folder

```
uptd_balatkop/
│
├── config.php
├── index.php
├── login.php
├── register.php
├── verify.php
│
├── Admin/
│   ├── dashboard_admin.php
│   ├── instruktur.php
│   ├── jadwal.php
│   ├── pelatihan.php
│   ├── peserta.php
│   ├── ruangan.php
│   └── laporan_pelatihan.php
│
├── Instruktur/
│   ├── dashboard_instruktur.php
│   ├── jadwal.php
│   └── evaluasi.php
│
├── Peserta/
│   ├── dashboard_peserta.php
│   ├── pelatihan.php
│   ├── jadwal.php
│   ├── riwayat.php
│   └── sertifikat.php
│
├── Pimpinan/
│   ├── dashboard_pimpinan.php
│   └── laporan_pelatihan.php
│
├── assets/
│   └── css/
│       └── style.css
│
├── includes/
│   ├── navbar.php
│   └── sidebar.php
│
└── uploads/
    └── dokumen/
```

---

## 🚀 Instalasi

**1. Clone repository**
```bash
git clone https://github.com/username/uptd_balatkop.git
```

**2. Pindahkan project ke folder XAMPP**
```
xampp/htdocs/uptd_balatkop
```

**3. Buat database MySQL**
```sql
CREATE DATABASE uptd_balatkop;
```

**4. Import file database**
```
Import file .sql ke database uptd_balatkop
```

**5. Konfigurasi koneksi di `config.php`**
```php
$host = 'localhost';
$db   = 'uptd_balatkop';
$user = 'root';
$pass = '';
```

**6. Jalankan aplikasi**
```
http://localhost/uptd_balatkop
```

---

## 🔑 Akun Default

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@gmail.com | admin123 |
| Peserta | peserta@gmail.com | peserta123 |

---

## 📸 Screenshot

> Tambahkan screenshot aplikasi di sini untuk memperlihatkan tampilan sistem.

---

## 🤝 Kontribusi

Pull request sangat terbuka untuk pengembangan lebih lanjut dari sistem ini.

1. Fork repository ini
2. Buat branch baru (`git checkout -b feature/fitur-baru`)
3. Commit perubahan (`git commit -m 'Tambah fitur baru'`)
4. Push ke branch (`git push origin feature/fitur-baru`)
5. Buat Pull Request

---

## 📄 Lisensi

Project ini digunakan untuk keperluan **pembelajaran dan pengembangan sistem informasi pelatihan**.

---

<div align="center">

Made with ❤️ for **UPTD BALATKOP**

![PHP](https://img.shields.io/badge/Built_with-PHP-777BB4?style=flat-square&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/Database-MySQL-4479A1?style=flat-square&logo=mysql&logoColor=white)

</div>
