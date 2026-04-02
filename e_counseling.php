<?php
// e_counseling.php
session_start();
require_once 'config/database.php';

$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama_siswa']) ?: 'Anonim';
    $kelas = trim($_POST['kelas']) ?: '-';
    $kategori = $_POST['kategori'];
    $pesan = trim($_POST['pesan']);

    if (empty($pesan)) {
        $error = "Pesan tidak boleh kosong.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO pengaduan (nama_siswa, kelas, kategori, pesan, status) VALUES (?, ?, ?, ?, 'Pending')");
            $stmt->execute([$nama, $kelas, $kategori, $pesan]);
            $success = true;
        } catch (PDOException $e) {
            $error = "Gagal mengirim tiket: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Counseling & Layanan Pengaduan - SMAN 4 Makassar</title>
    <link rel="stylesheet" href="/public/assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body.login-page {
            font-family: 'Poppins', system-ui, sans-serif;
            background: linear-gradient(135deg, #064e3b 0%, #065f46 50%, #0f172a 100%);
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
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%2310b981' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            pointer-events: none;
        }

        body.login-page::after {
            content: '';
            position: fixed;
            width: 500px; height: 500px;
            top: -150px; right: -100px;
            background: radial-gradient(circle, rgba(16,185,129,0.15) 0%, transparent 60%);
            border-radius: 50%;
            pointer-events: none;
        }

        .login-card {
            background: white;
            width: 100%;
            max-width: 520px;
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
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, #059669, #10b981);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            box-shadow: 0 8px 20px rgba(16,185,129,0.35);
        }

        .brand-title {
            color: #064e3b;
            font-size: 1.4rem;
            font-weight: 800;
            text-align: center;
            margin-bottom: 0.35rem;
            letter-spacing: -0.03em;
        }

        .brand-subtitle {
            color: #64748b;
            text-align: center;
            font-size: 0.875rem;
            margin-bottom: 2rem;
            line-height: 1.55;
        }

        .form-group { margin-bottom: 1.1rem; }

        .form-group label {
            display: block;
            margin-bottom: 0.4rem;
            color: #334155;
            font-weight: 600;
            font-size: 0.875rem;
        }

        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 0.75rem 1rem;
            border-radius: 12px;
            border: 2px solid #e2e8f0;
            font-size: 0.95rem;
            transition: all 0.2s;
            font-family: inherit;
            color: #1e293b;
            background: #f8fafc;
        }

        .form-group input::placeholder, .form-group textarea::placeholder { color: #94a3b8; }

        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            outline: none;
            border-color: #10b981;
            background: white;
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1);
        }

        .btn-submit {
            background: linear-gradient(135deg, #059669, #10b981);
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
            box-shadow: 0 4px 15px rgba(16,185,129,0.35);
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(16,185,129,0.45);
        }

        .btn-submit:active { transform: translateY(0); }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 1.25rem;
            color: #94a3b8;
            text-decoration: none;
            font-size: 0.875rem;
            transition: color 0.2s;
        }

        .back-link:hover { color: #059669; }

        .privacy-note {
            display: flex;
            align-items: flex-start;
            gap: 0.5rem;
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 10px;
            padding: 0.75rem 1rem;
            font-size: 0.8rem;
            color: #166534;
            margin-bottom: 1.25rem;
            line-height: 1.5;
        }
    </style>
</head>
<body class="login-page">

<div class="login-card">
    <div class="brand-icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 9a2 2 0 0 1-2 2H6l-4 4V4c0-1.1.9-2 2-2h8a2 2 0 0 1 2 2v5Z"/><path d="M18 9h2a2 2 0 0 1 2 2v11l-4-4h-6a2 2 0 0 1-2-2v-1"/></svg>
    </div>
    <div class="brand-title">E-Counseling BK</div>
    <div class="brand-subtitle">Sampaikan keluhan atau tiket Anda kepada Guru BK secara aman. Nama bersifat opsional jika ingin anonim.</div>

    <?php if ($success): ?>
        <div style="background: #d1fae5; color: #065f46; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; text-align: center; font-size: 0.95rem; font-weight: 600; display:flex; align-items:center; justify-content:center; gap:8px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            Tiket berhasil dikirim. Terima kasih!
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div style="background: #fee2e2; color: #991b1b; padding: 0.75rem; border-radius: 8px; margin-bottom: 1.5rem; text-align: center; font-size: 0.9rem; font-weight: 500; display:flex; align-items:center; justify-content:center; gap:6px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>
    
    <?php if (!$success): ?>
    <div class="privacy-note">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0; margin-top:1px"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
        <span><strong>Kerahasiaan Terjamin:</strong> Laporan Anda hanya dapat dilihat oleh Guru BK. Nama & kelas bersifat opsional agar Anda dapat melapor secara anonim.</span>
    </div>
    <form action="" method="POST">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label for="nama_siswa">Nama (Opsional)</label>
                <input type="text" id="nama_siswa" name="nama_siswa" placeholder="Boleh dikosongkan">
            </div>
            <div class="form-group">
                <label for="kelas">Kelas (Opsional)</label>
                <input type="text" id="kelas" name="kelas" placeholder="Contoh: X-1">
            </div>
        </div>
        
        <div class="form-group">
            <label for="kategori">Kategori</label>
            <select id="kategori" name="kategori" required>
                <option value="Kendala Belajar">Kendala Belajar</option>
                <option value="Bullying">Laporan Bullying / Perundungan</option>
                <option value="Fasilitas">Masalah Fasilitas</option>
                <option value="Konsultasi Karir">Konsultasi Karir / Masa Depan</option>
                <option value="Lainnya">Lainnya</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="pesan">Pesan / Detail Masalah</label>
            <textarea id="pesan" name="pesan" rows="5" placeholder="Ceritakan detail masalah yang Anda alami secara rinci..." required></textarea>
        </div>
        
        <button type="submit" class="btn-submit">Kirim Tiket Pengaduan</button>
    </form>
    <?php endif; ?>
    
    <a href="index.php" class="back-link">&larr; Kembali ke Portal Utama</a>
</div>

</body>
</html>

