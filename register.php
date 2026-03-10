<?php
session_start();
require_once 'config.php';

// Jika sudah login, redirect ke dashboard
if (isset($_SESSION['user_id'])) {
    redirect('index.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_lengkap = clean_input($_POST['nama_lengkap']);
    $username     = clean_input($_POST['username']);
    $password     = $_POST['password'];

    if (empty($nama_lengkap) || empty($username) || empty($password)) {
        $error = 'Semua field wajib diisi!';
    } else {
        $conn = getConnection();

        // Cek username sudah ada
        $cek = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $cek->bind_param("s", $username);
        $cek->execute();
        $cek->store_result();

        if ($cek->num_rows > 0) {
            $error = 'Username sudah terdaftar!';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare(
                "INSERT INTO users (nama_lengkap, username, password) VALUES (?, ?, ?)"
            );
            $stmt->bind_param("sss", $nama_lengkap, $username, $hash);

            if ($stmt->execute()) {
                $success = 'Registrasi berhasil! Silakan login.';
            } else {
                $error = 'Registrasi gagal. Silakan coba lagi.';
            }
            $stmt->close();
        }

        $cek->close();
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- CSS TIDAK DIUBAH -->
    <style>
        /* === CSS ASLI TETAP === */
        :root {
            --primary: #4f46e5;
            --primary-dark: #4338ca;
            --secondary: #06b6d4;
            --success: #10b981;
            --danger: #ef4444;
            --dark: #1e293b;
            --light: #f8fafc;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            max-width: 450px;
            width: 100%;
        }

        .login-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .login-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }

        .login-body {
            padding: 40px 30px;
        }

        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            border: none;
            border-radius: 10px;
            color: white;
            font-weight: 600;
        }
    </style>
</head>

<body>

    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <i class="fas fa-user-plus fa-3x"></i>
                <h2><?php echo APP_NAME; ?></h2>
                <p>Registrasi Akun Baru</p>
            </div>

            <div class="login-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-times-circle"></i> <?= $error ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?= $success ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" name="nama_lengkap" placeholder="Nama Lengkap" required>
                        <label><i class="fas fa-id-card"></i> Nama Lengkap</label>
                    </div>

                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" name="username" placeholder="Username" required>
                        <label><i class="fas fa-user"></i> Username</label>
                    </div>

                    <div class="form-floating mb-3">
                        <input type="password" class="form-control" name="password" placeholder="Password" required>
                        <label><i class="fas fa-lock"></i> Password</label>
                    </div>

                    <button type="submit" class="btn btn-login">
                        <i class="fas fa-user-plus"></i> Daftar Sekarang
                    </button>
                </form>
                <br>
                <a href="login.php" class="btn btn-login">
                    <i class="fas fa-sign-in-alt"></i> Masuk ke Akun
                </a>
            </div>
        </div>
    </div>

</body>

</html>