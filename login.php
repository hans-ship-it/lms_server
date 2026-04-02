<?php
// login.php
session_start();
require_once 'config/database.php';

$portal = $_GET['portal'] ?? 'lms';

if (isset($_SESSION['user_id'])) {
    $role = $_SESSION['role'] ?? '';
    
    // Safety check status from database to prevent stale session data
    $stmt = $pdo->prepare("SELECT status FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $u = $stmt->fetch();
    $status = $u['status'] ?? 'active';

    // STRICT PORTAL VALIDATION
    if ($portal === 'tracer') {
        if ($role !== 'siswa' || $status !== 'graduated') {
            session_destroy();
            header("Location: login.php?portal=tracer&error=Akses Ditolak! Portal Tracer khusus untuk Siswa Alumni.");
            exit;
        }
        header("Location: tracer_form.php");
        exit;
    } elseif ($portal === 'nilai') {
        if ($role !== 'siswa') {
            session_destroy();
            header("Location: login.php?portal=nilai&error=Akses Ditolak! Portal Nilai khusus untuk Siswa aktif dan Alumni.");
            exit;
        }
        header("Location: pantauan_nilai.php");
        exit;
    } elseif ($portal === 'lms') {
        if ($role === 'siswa' && $status === 'graduated') {
            session_destroy();
            header("Location: login.php?portal=lms&error=Akses Ditolak! Alumni tidak dapat mengakses Portal LMS (Gunakan Portal Tracer).");
            exit;
        }
    }

    if (in_array($role, ['kepsek', 'wakasek'], true)) {
        header("Location: src/pimpinan/dashboard.php");
    }
    elseif (in_array($role, ['admin', 'guru', 'siswa', 'osis', 'bk'], true)) {
        header("Location: src/$role/dashboard.php");
    }
    else {
        error_log('[LMS login.php] Sesi memiliki role tidak dikenal, mengarahkan ke logout (role=' . $role . ')');
        session_destroy();
        header("Location: login.php?error=Sesi tidak valid. Silakan login kembali.");
    }
    exit;
}


$p_title = 'Portal Akademik';
$p_subtitle = 'SMA Negeri 4 Makassar<br>Silakan masuk dengan akun yang telah terdaftar';
$p_bg = 'linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #0f172a 100%)';
$p_btn = 'linear-gradient(135deg, #4f46e5, #6d28d9)';
$p_icon_bg = 'linear-gradient(135deg, #4f46e5, #7c3aed)';
$p_svg = '<path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/>';

if ($portal === 'ecounseling') {
    $p_title = 'E-Counseling BK';
    $p_subtitle = 'Portal Layanan BK<br>Silakan masuk untuk berkonsultasi secara privat';
    $p_bg = 'linear-gradient(135deg, #064e3b 0%, #065f46 50%, #0f172a 100%)';
    $p_btn = 'linear-gradient(135deg, #059669, #10b981)';
    $p_icon_bg = 'linear-gradient(135deg, #059669, #10b981)';
    $p_svg = '<path d="M14 9a2 2 0 0 1-2 2H6l-4 4V4c0-1.1.9-2 2-2h8a2 2 0 0 1 2 2v5Z"/><path d="M18 9h2a2 2 0 0 1 2 2v11l-4-4h-6a2 2 0 0 1-2-2v-1"/>';
} elseif ($portal === 'tracer') {
    $p_title = 'Portal Tracer Alumni';
    $p_subtitle = 'SMAN 4 Makassar<br>Silakan masuk untuk mengisi data penelusuran lulusan';
    $p_bg = 'linear-gradient(135deg, #831843 0%, #9d174d 50%, #0f172a 100%)';
    $p_btn = 'linear-gradient(135deg, #ec4899, #db2777)';
    $p_icon_bg = 'linear-gradient(135deg, #ec4899, #db2777)';
    $p_svg = '<path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/>';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SMA Negeri 4 Makassar</title>
    <link rel="stylesheet" href="/public/assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body.login-page {
            font-family: 'Poppins', system-ui, sans-serif;
            background: <?php echo $p_bg; ?>;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            padding: 40px 20px;
            position: relative;
            overflow-x: hidden;
            overflow-y: auto;
        }

        body.login-page::before {
            content: '';
            position: fixed;
            inset: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%234f46e5' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            pointer-events: none;
        }

        body.login-page::after {
            content: '';
            position: fixed;
            width: 600px; height: 600px;
            top: -200px; left: -150px;
            background: radial-gradient(circle, rgba(99,102,241,0.15) 0%, transparent 60%);
            border-radius: 50%;
            pointer-events: none;
        }

        .login-card {
            background: white;
            width: 100%;
            max-width: 440px;
            padding: 2.75rem 2.5rem;
            border-radius: 24px;
            box-shadow: 0 25px 60px rgba(0,0,0,0.4), 0 0 0 1px rgba(255,255,255,0.05);
            animation: slideUp 0.5s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
            z-index: 10;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px) scale(0.97); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        .brand-icon {
            width: 54px;
            height: 54px;
            background: <?php echo $p_icon_bg; ?>;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.1rem;
            box-shadow: 0 8px 20px rgba(79,70,229,0.35);
        }

        .brand-title {
            color: #0f172a;
            font-size: 1.5rem;
            font-weight: 800;
            text-align: center;
            margin-bottom: 0.35rem;
            letter-spacing: -0.03em;
        }

        .brand-subtitle {
            color: #64748b;
            text-align: center;
            font-size: 0.9rem;
            margin-bottom: 2rem;
            line-height: 1.5;
        }

        .form-group { margin-bottom: 1.25rem; }

        .form-group label {
            display: block;
            margin-bottom: 0.45rem;
            color: #334155;
            font-weight: 600;
            font-size: 0.875rem;
        }

        .form-group input {
            width: 100%;
            padding: 0.8rem 1rem;
            border-radius: 12px;
            border: 2px solid #e2e8f0;
            font-size: 0.95rem;
            transition: all 0.2s;
            font-family: inherit;
            color: #1e293b;
            background: #f8fafc;
        }

        .form-group input::placeholder { color: #94a3b8; }

        .form-group input:focus {
            outline: none;
            border-color: #4f46e5;
            background: white;
            box-shadow: 0 0 0 4px rgba(79,70,229,0.1);
        }

        .btn-login {
            background: <?php echo $p_btn; ?>;
            color: white;
            width: 100%;
            padding: 0.9rem;
            border-radius: 12px;
            border: none;
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.2s;
            font-family: inherit;
            letter-spacing: 0.02em;
            box-shadow: 0 4px 15px rgba(79,70,229,0.35);
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(79,70,229,0.45);
        }

        .btn-login:active { transform: translateY(0); }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 1.5rem;
            color: #94a3b8;
            text-decoration: none;
            font-size: 0.875rem;
            transition: color 0.2s;
        }

        .back-link:hover { color: #4f46e5; }

        .divider {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin: 1.5rem 0;
            color: #cbd5e1;
            font-size: 0.8rem;
        }

        .divider::before, .divider::after {
            content: '';
            flex: 1;
            border-top: 1px solid #e2e8f0;
        }
    </style>
</head>
<body class="login-page">

<div class="login-card">
    <div class="brand-icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><?php echo $p_svg; ?></svg>
    </div>
    <div class="brand-title"><?php echo $p_title; ?></div>
    <div class="brand-subtitle"><?php echo $p_subtitle; ?></div>

    <?php if (isset($_GET['error'])): ?>
        <div style="background: #fee2e2; color: #991b1b; padding: 0.75rem; border-radius: 8px; margin-bottom: 1.5rem; text-align: center; font-size: 0.9rem; font-weight: 500; display:flex; align-items:center; justify-content:center; gap:6px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            <?php echo htmlspecialchars($_GET['error']); ?>
        </div>
    <?php
endif; ?>
    
    <form action="src/auth/auth_process.php" method="POST">
        <input type="hidden" name="portal" value="<?php echo htmlspecialchars($portal); ?>">
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" placeholder="Masukkan username Anda" required>
        </div>
        <div class="form-group" style="position: relative;">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Masukkan password" required style="padding-right: 48px;">
            <button type="button" onclick="toggleLoginPassword()" style="position: absolute; right: 14px; top: 40px; background: none; border: none; cursor: pointer; color: #64748b; padding: 0; display:flex; align-items:center;">
                <svg id="eyeIconLogin" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            </button>
        </div>
        <button type="submit" class="btn-login">Masuk ke Portal</button>
    </form>
    
    <a href="index.php" class="back-link">&larr; Kembali ke Beranda</a>
</div>

<script>
function toggleLoginPassword() {
    const input = document.getElementById('password');
    const icon = document.getElementById('eyeIconLogin');
    if (input.type === 'password') {
        input.type = 'text';
        icon.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>';
    } else {
        input.type = 'password';
        icon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
    }
}
</script>

</body>
</html>

