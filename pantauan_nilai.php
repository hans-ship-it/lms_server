<?php
// pantauan_nilai.php
session_start();
require_once 'config/database.php';

// Cek status login
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = '/pantauan_nilai.php';
    header("Location: login.php?error=Silakan login terlebih dahulu untuk mengakses Pantauan Nilai.");
    exit;
}

// Pastikan user adalah siswa. Jika bukan, logout paksa karena melanggar akses portal.
if ($_SESSION['role'] !== 'siswa') {
    session_destroy();
    header("Location: login.php?portal=nilai&error=Akses Ditolak! Portal Pantauan Nilai hanya untuk Siswa. Anda telah dikeluarkan dari sistem.");
    exit;
}

$student_id = $_SESSION['user_id'];
$student_name = $_SESSION['full_name'] ?? 'Siswa';
$student_nis = $_SESSION['nis'] ?? '-'; // Use NIS from session, fallback to '-' if null

// Coba ambil nama kelas (Opsional, jika ada)
$class_name = '-';
try {
    $stmt_class = $pdo->prepare("SELECT c.name FROM users u LEFT JOIN classes c ON u.class_id = c.id WHERE u.id = ?");
    $stmt_class->execute([$student_id]);
    $res = $stmt_class->fetch();
    if ($res && !empty($res['name'])) {
        $class_name = $res['name'];
    }
} catch (PDOException $e) {
    // Abaikan error kelas
}

// Mengambil data nilai
try {
    $stmt = $pdo->prepare("
        SELECT 
            sg.academic_year, 
            sg.semester, 
            sg.grade, 
            s.name as subject_name,
            t.full_name as teacher_name
        FROM student_grades sg
        JOIN subjects s ON sg.subject_id = s.id
        JOIN users t ON sg.teacher_id = t.id
        WHERE sg.student_id = ?
        ORDER BY sg.academic_year DESC, sg.semester DESC, s.name ASC
    ");
    $stmt->execute([$student_id]);
    $grades = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Pantauan Nilai Error: " . $e->getMessage());
    $grades = [];
}

// Group data berdasarkan tahun dan semester
$grouped_grades = [];
foreach ($grades as $g) {
    $semLabel = ($g['semester'] == '1' || strtolower($g['semester']) == 'ganjil') ? 'Ganjil' : 'Genap';
    $period = "Tahun Ajaran : " . $g['academic_year'] . " | Semester : " . $semLabel;
    $grouped_grades[$period][] = $g;
}

// Hitung IPK/Rata-rata Global
$total_subjects = count($grades);
$avg_grade = $total_subjects > 0 ? array_sum(array_column($grades, 'grade')) / $total_subjects : 0;

function getPredikat($grade)
{
    if ($grade >= 90)
        return ['label' => 'A', 'color' => '#15803d', 'bg' => '#dcfce7'];
    if ($grade >= 80)
        return ['label' => 'B', 'color' => '#1d4ed8', 'bg' => '#dbeafe'];
    if ($grade >= 70)
        return ['label' => 'C', 'color' => '#b45309', 'bg' => '#fef3c7'];
    return ['label' => 'D', 'color' => '#b91c1c', 'bg' => '#fee2e2'];
}

$overall_info = getPredikat($avg_grade);

$print_date = date('d F Y');
$months = [
    'January' => 'Januari',
    'February' => 'Februari',
    'March' => 'Maret',
    'April' => 'April',
    'May' => 'Mei',
    'June' => 'Juni',
    'July' => 'Juli',
    'August' => 'Agustus',
    'September' => 'September',
    'October' => 'Oktober',
    'November' => 'November',
    'December' => 'Desember'
];
$print_date = str_replace(array_keys($months), array_values($months), $print_date);

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KHS - <?php echo htmlspecialchars($student_name); ?></title>
    <!-- Gunakan Lora (Serif) untuk kesan resmi, dan Inter untuk data -->
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Lora:ital,wght@0,400;0,600;0,700;1,400&display=swap"
        rel="stylesheet">
    <style>
        :root {
            --primary: #1e3a8a;
            /* Deep blue */
            --bg-body: #f1f5f9;
            --paper: #ffffff;
            --text-dark: #1e293b;
            --text-regular: #334155;
            --border: #cbd5e1;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background-color: var(--bg-body);
            color: var(--text-dark);
            font-family: 'Inter', sans-serif;
            line-height: 1.5;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* Top Actions Bar (Screen Only) */
        .top-bar {
            background-color: var(--primary);
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .top-bar .title {
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            letter-spacing: 0.5px;
        }

        .btn-group {
            display: flex;
            gap: 1rem;
        }

        .btn {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.875rem;
            transition: all 0.2s;
        }

        .btn:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .btn-logout {
            background: #fee2e2;
            color: #b91c1c;
            border-color: #fca5a5;
        }

        .btn-logout:hover {
            background: #fca5a5;
        }

        /* A4 Paper Container */
        .paper {
            background: var(--paper);
            max-width: 21cm;
            /* A4 width */
            margin: 2rem auto;
            padding: 2.5cm;
            /* Standar margin dokumen */
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
        }

        /* KOP Surat Header */
        .kop-surat {
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            border-bottom: 4px solid var(--text-dark);
            padding-bottom: 1rem;
            margin-bottom: 2rem;
            position: relative;
        }

        .kop-surat::after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 0;
            right: 0;
            border-bottom: 1px solid var(--text-dark);
        }

        /* Logo Placeholder */
        .kop-logo {
            width: 80px;
            height: 80px;
            position: absolute;
            left: 0;
            top: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .kop-content {
            width: 100%;
            padding-left: 90px;
        }

        .kop-instansi {
            font-family: 'Lora', serif;
            font-size: 1rem;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 0.2rem;
        }

        .kop-nama {
            font-family: 'Lora', serif;
            font-size: 1.75rem;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--text-dark);
            letter-spacing: 1px;
            margin-bottom: 0.2rem;
        }

        .kop-alamat {
            font-size: 0.85rem;
            color: var(--text-regular);
        }

        /* Document Title */
        .doc-title {
            text-align: center;
            margin-bottom: 2rem;
        }

        .doc-title h1 {
            font-family: 'Lora', serif;
            font-size: 1.25rem;
            font-weight: 700;
            text-decoration: underline;
            text-underline-offset: 4px;
            margin-bottom: 0.25rem;
            letter-spacing: 1px;
        }

        .doc-title p {
            font-size: 0.9rem;
            font-style: italic;
            color: var(--text-regular);
        }

        /* Info Profile Table (No Borders) */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2rem;
            font-size: 0.95rem;
        }

        .info-table td {
            padding: 0.3rem 0;
            vertical-align: top;
        }

        .info-table td:first-child {
            width: 120px;
            font-weight: 600;
        }

        .info-table td:nth-child(2) {
            width: 10px;
            text-align: center;
        }

        /* The Grades Table */
        .grades-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9rem;
        }

        .grades-table th,
        .grades-table td {
            border: 1px solid var(--border);
            padding: 0.65rem 1rem;
            vertical-align: middle;
        }

        .grades-table thead th {
            background-color: #f1f5f9;
            color: #374151;
            font-weight: 700;
            text-align: center;
            text-transform: uppercase;
            font-size: 0.78rem;
            letter-spacing: 0.04em;
        }

        .grades-table tbody td {
            color: var(--text-regular);
        }

        .grades-table .col-no {
            text-align: center;
            width: 48px;
            color: #94a3b8;
            font-size: 0.85rem;
        }

        .grades-table .col-mapel {
            font-weight: 600;
            color: var(--text-dark);
        }

        .grades-table .col-nilai {
            text-align: center;
            width: 110px;
            font-variant-numeric: tabular-nums;
            font-weight: 700;
            font-size: 1.05rem;
            color: var(--text-dark);
        }

        .grades-table .col-predikat {
            text-align: center;
            width: 100px;
        }

        .grades-table tbody tr:last-child td {
            border-bottom: none;
        }

        .grades-table tbody tr:hover td {
            background-color: #f8fafc;
        }

        /* Predikat Badge */
        .badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 700;
        }

        /* Period / Semester Row Divider */
        .period-row td {
            background-color: #f8fafc;
            border-top: 2px solid #e2e8f0;
            border-bottom: 1px solid #e2e8f0;
            padding: 0.55rem 1rem;
            font-size: 0.8rem;
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        /* Summary Stats */
        .summary-box {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 1.25rem 2rem;
            display: flex;
            justify-content: space-around;
            align-items: center;
            background-color: #f8fafc;
            margin-top: 1.25rem;
            margin-bottom: 3rem;
            text-align: center;
            gap: 1rem;
        }

        .summary-box .divider {
            width: 1px;
            height: 40px;
            background: #e2e8f0;
        }

        .summary-item h4 {
            font-size: 0.72rem;
            text-transform: uppercase;
            color: #64748b;
            margin-bottom: 0.4rem;
            font-weight: 600;
            letter-spacing: 0.05em;
        }

        .summary-item p {
            font-size: 1.6rem;
            font-weight: 800;
            color: var(--text-dark);
            line-height: 1;
        }

        /* Signatures Section */
        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 3rem;
            font-size: 0.95rem;
        }

        .sig-box {
            text-align: center;
            width: 300px;
        }

        .sig-box .date {
            margin-bottom: 0.5rem;
        }

        .sig-box .title {
            font-weight: 600;
            margin-bottom: 5rem;
        }

        .sig-box .name {
            font-weight: 700;
            text-decoration: underline;
        }

        .sig-box .nip {
            margin-top: 0.25rem;
            font-size: 0.85rem;
        }

        /* Legenda */
        .keterangan {
            font-size: 0.78rem;
            margin-top: 0.5rem;
            margin-bottom: 0;
            color: #94a3b8;
        }

        /* Mobile Responsive Override */
        @media screen and (max-width: 768px) {
            .top-bar {
                flex-direction: column;
                gap: 0.5rem;
                padding: 0.6rem 0.75rem;
                align-items: center;
                text-align: center;
            }

            .top-bar .title {
                justify-content: center;
                font-size: 1.05rem;
            }

            .btn-group {
                flex-direction: row;
                width: 100%;
                gap: 0.4rem;
                justify-content: space-between;
                align-items: flex-start;
            }

            .btn {
                flex: 1;
                flex-direction: column;
                justify-content: flex-start;
                align-items: center;
                padding: 0.6rem 0.25rem;
                font-size: 0.65rem;
                line-height: 1.3;
                gap: 0.35rem;
                text-align: center;
                white-space: normal;
                word-wrap: break-word;
            }

            .btn svg {
                width: 18px;
                height: 18px;
            }

            /* Tighter paper on mobile — maximize visible width */
            .paper {
                margin: 0.5rem;
                padding: 1rem 0.75rem;
                border-radius: 8px;
            }

            .kop-surat {
                flex-direction: column;
                padding-bottom: 1rem;
                margin-bottom: 1rem;
                align-items: center;
            }

            .kop-logo {
                position: static;
                margin-bottom: 8px;
                width: 52px;
                height: 52px;
            }

            .kop-content {
                padding-left: 0;
                text-align: center;
            }

            .kop-instansi {
                font-size: 0.78rem;
            }

            .kop-nama {
                font-size: 1.1rem;
            }

            .kop-alamat {
                font-size: 0.72rem;
            }

            .doc-title h1 {
                font-size: 1rem;
            }

            .doc-title p {
                font-size: 0.8rem;
            }

            /* Responsive Info Table */
            .info-table,
            .info-table tbody,
            .info-table tr,
            .info-table td {
                display: block;
                width: 100%;
                text-align: left;
            }

            .info-table tr {
                margin-bottom: 0.75rem;
                padding-bottom: 0.75rem;
                border-bottom: 1px dashed var(--border);
            }

            .info-table tr:last-child {
                border-bottom: none;
                margin-bottom: 0;
                padding-bottom: 0;
            }

            .info-table td {
                padding: 0.15rem 0;
                width: 100% !important;
            }

            .info-table td:nth-child(2),
            .info-table td:nth-child(5) {
                display: none;
            }

            .info-table td:nth-child(1),
            .info-table td:nth-child(4) {
                font-size: 0.75rem;
                color: var(--text-regular);
                font-weight: 500;
            }

            .info-table td:nth-child(3),
            .info-table td:nth-child(6) {
                font-size: 1rem;
                font-weight: 700;
                color: var(--text-dark);
                margin-bottom: 0.25rem;
            }

            /* TABLE: Horizontal scroll so ALL columns are always fully visible */
            .grades-table-wrapper {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                border: 1px solid var(--border);
                border-radius: 8px;
                margin-bottom: 0.75rem;
            }

            .grades-table-wrapper table {
                min-width: 480px;
                /* Ensures all 4 columns never collapse */
            }

            /* Summary box: stay in a row on mobile — no stacking */
            .summary-box {
                padding: 1rem 0.5rem;
                gap: 0;
                flex-direction: row;
            }

            .summary-box .divider {
                width: 1px;
                height: 32px;
                flex-shrink: 0;
            }

            .summary-item {
                flex: 1;
                padding: 0 0.25rem;
            }

            .summary-item h4 {
                font-size: 0.62rem;
            }

            .summary-item p {
                font-size: 1.15rem;
            }

            /* Keterangan */
            .keterangan {
                font-size: 0.72rem;
            }

            /* Responsive Signatures */
            .signatures {
                flex-direction: column;
                gap: 1.5rem;
                align-items: center;
                margin-top: 2rem;
            }

            .sig-box {
                width: 100%;
                max-width: 220px;
            }
        }

        /* Print Override */
        @media print {
            @page {
                size: A4 portrait;
                margin: 0;
            }

            body {
                background: none;
            }

            .top-bar {
                display: none !important;
            }

            .paper {
                margin: 0;
                padding: 2cm 2.5cm;
                max-width: 100%;
                box-shadow: none;
                border: none;
            }

            .grades-table th {
                background-color: #f1f5f9 !important;
                border-color: #64748b;
            }

            .grades-table td {
                border-color: #64748b;
            }

            .period-row th {
                background-color: #e2e8f0 !important;
                border-color: #64748b;
            }

            .kop-surat {
                border-color: #000;
            }

            .kop-surat::after {
                border-color: #000;
            }

            .summary-box {
                border-color: #64748b;
                background-color: transparent !important;
            }
        }
    </style>
</head>

<body>

    <!-- Top Action Bar for Application Context (Hides on Print) -->
    <div class="top-bar">
        <div class="title">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                <polyline points="14 2 14 8 20 8" />
                <line x1="16" y1="13" x2="8" y2="13" />
                <line x1="16" y1="17" x2="8" y2="17" />
                <polyline points="10 9 9 9 8 9" />
            </svg>
            Laporan Nilai Siswa
        </div>
        <div class="btn-group">
            <a href="/keaktifan_siswa.php" class="btn">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                    <circle cx="9" cy="7" r="4" />
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                    <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                </svg>
                <span>Keaktifan Siswa</span>
            </a>
            <button class="btn" onclick="window.print()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6 9 6 2 18 2 18 9" />
                    <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2" />
                    <rect x="6" y="14" width="12" height="8" />
                </svg>
                <span>Print / Simpan PDF</span>
            </button>
            <a href="/src/auth/logout.php" class="btn btn-logout"
                onclick="return confirm('Apakah Anda yakin ingin keluar?');">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                    <polyline points="16 17 21 12 16 7" />
                    <line x1="21" y1="12" x2="9" y2="12" />
                </svg>
                <span>Keluar</span>
            </a>
        </div>
    </div>

    <!-- A4 Paper Document -->
    <div class="paper">

        <!-- KOP STRUKTUR RESMI -->
        <div class="kop-surat">
            <div class="kop-logo">
                <img src="/public/assets/images/Logo_SMAPAT.png" alt="Logo SMA Negeri 4 Makassar" width="80"
                    height="80">
            </div>
            <div class="kop-content">
                <div class="kop-instansi">Kementerian Pendidikan dan Kebudayaan</div>
                <div class="kop-nama">SMA Negeri 4 Makassar</div>
                <div class="kop-alamat">Jl. Cakalang No.3, Tallo, Kota Makassar, Sulawesi Selatan, Kode Pos 90214</div>
            </div>
        </div>

        <div class="doc-title">
            <h1>KARTU HASIL STUDI (KHS)</h1>
            <p>Ringkasan Nilai Evaluasi Belajar Siswa</p>
        </div>

        <!-- Student Info -->
        <table class="info-table">
            <tr>
                <td>Nama Peserta Didik</td>
                <td>:</td>
                <td style="font-weight: 600; text-transform: uppercase;"><?php echo htmlspecialchars($student_name); ?>
                </td>

                <!-- Optional 2nd col alignment -->
                <td style="width: 100px;">Kelas</td>
                <td style="width: 10px;">:</td>
                <td style="font-weight: 600;"><?php echo htmlspecialchars($class_name); ?></td>
            </tr>
            <tr>
                <td>Nomor Induk Siswa</td>
                <td>:</td>
                <td><?php echo htmlspecialchars($student_nis); ?></td>
            </tr>
        </table>

        <!-- Grades Content -->
        <?php if (empty($grouped_grades)): ?>
            <div
                style="border: 1px dashed #cbd5e1; padding: 3rem; text-align: center; color: #64748b; margin-bottom: 2rem; border-radius: 6px;">
                Tidak ada riwayat nilai untuk siswa ini pada semester berjalan.
            </div>
        <?php else: ?>
            <div class="grades-table-wrapper">
                <table class="grades-table">
                    <thead>
                        <tr>
                            <th class="col-no">No</th>
                            <th class="col-mapel" style="text-align:left;">Mata Pelajaran</th>
                            <th class="col-nilai">Nilai Akhir</th>
                            <th class="col-predikat">Predikat</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($grouped_grades as $period => $period_grades): ?>
                            <tr class="period-row">
                                <td colspan="4"><?php echo htmlspecialchars($period); ?></td>
                            </tr>
                            <?php
                            $no = 1;
                            foreach ($period_grades as $pg):
                                $info = getPredikat($pg['grade']);
                                ?>
                                <tr>
                                    <td class="col-no"><?php echo $no++; ?></td>
                                    <td class="col-mapel">
                                        <span style="display:block;"><?php echo htmlspecialchars($pg['subject_name']); ?></span>
                                        <span
                                            style="font-size: 0.75rem; color: #94a3b8; font-weight: 400;"><?php echo htmlspecialchars($pg['teacher_name']); ?></span>
                                    </td>
                                    <td class="col-nilai"><?php echo number_format($pg['grade'], 1); ?></td>
                                    <td class="col-predikat">
                                        <span class="badge"
                                            style="background-color: <?php echo $info['bg']; ?>; color: <?php echo $info['color']; ?>;">
                                            <?php echo $info['label']; ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <p class="keterangan">A &ge; 90 (Sangat Baik) &nbsp;|&nbsp; B = 80–89 (Baik) &nbsp;|&nbsp; C = 70–79 (Cukup)
                &nbsp;|&nbsp; D &lt; 70 (Kurang)</p>

            <!-- Summary -->
            <div class="summary-box">
                <div class="summary-item">
                    <h4>Total Mapel</h4>
                    <p><?php echo $total_subjects; ?></p>
                </div>
                <div class="divider"></div>
                <div class="summary-item">
                    <h4>Rata-rata Nilai</h4>
                    <p><?php echo number_format($avg_grade, 2); ?></p>
                </div>
                <div class="divider"></div>
                <div class="summary-item">
                    <h4>Predikat Rata-rata</h4>
                    <p style="color: <?php echo $overall_info['color']; ?>;"><?php echo $overall_info['label']; ?></p>
                </div>
            </div>
        <?php endif; ?>

        <!-- Signature Block -->
        <div class="signatures">
            <div class="sig-box" style="visibility: hidden;">
                <div class="date">Makassar, <?php echo $print_date; ?></div>
                <div class="title">Wali Kelas</div>
                <div class="name">______________________</div>
                <div class="nip">NIP.</div>
            </div>
            <div class="sig-box">
                <div class="date">Makassar, <?php echo $print_date; ?></div>
                <div class="title">Orang Tua / Wali Siswa,</div>
                <div class="name">______________________</div>
            </div>
        </div>

    </div>

</body>

</html>