<?php
// src/guru/view_attendance.php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'guru') {
    header("Location: ../../login.php");
    exit;
}

$teacher_id = $_SESSION['user_id'];
$class_id = $_GET['class_id'] ?? 0;


// Fetch Teacher Class Details
$stmt = $pdo->prepare("
    SELECT tc.*, c.name as school_class_name 
    FROM teacher_classes tc
    JOIN classes c ON tc.class_id = c.id
    WHERE tc.id = ? AND tc.teacher_id = ?
");
$stmt->execute([$class_id, $teacher_id]);
$class = $stmt->fetch();

if (!$class) {
    die("Kelas tidak ditemukan atau Anda tidak memiliki akses.");
}

// Fetch Students
$stmt = $pdo->prepare("SELECT id, full_name, username, gender, nis FROM users WHERE class_id = ? AND role = 'siswa' ORDER BY nis ASC, full_name ASC");
$stmt->execute([$class['class_id']]);
$students = $stmt->fetchAll();

// Fetch all attendance assignments for this class
$stmt = $pdo->prepare("SELECT * FROM assignments WHERE teacher_class_id = ? AND assignment_type = 'absensi' AND teacher_id = ? ORDER BY meeting_number ASC");
$stmt->execute([$class_id, $teacher_id]);
$attendances = $stmt->fetchAll();

// Selected meeting filter
$selected_meeting = $_GET['meeting'] ?? 'all';

// Deduplicate assignments by meeting_number (keep latest ID)
$unique_attendances = [];
foreach ($attendances as $att) {
    $m_num = intval($att['meeting_number']);
    if (!isset($unique_attendances[$m_num]) || $att['id'] > $unique_attendances[$m_num]['id']) {
        $unique_attendances[$m_num] = $att;
    }
}
$attendances = $unique_attendances;

// Build attendance data
$attendance_data = [];
foreach ($attendances as $att) {
    // Modified: Fetch submitted_at as well
    $sub_stmt = $pdo->prepare("SELECT student_id, status, submitted_at FROM submissions WHERE assignment_id = ?");
    $sub_stmt->execute([$att['id']]);
    // FETCH_KEY_PAIR only works for 2 columns. We have 3 now.
    // Let's refetch differently.
    $raw_subs = $sub_stmt->fetchAll(PDO::FETCH_ASSOC);

    $submissions = [];
    $submission_times = [];
    foreach ($raw_subs as $rs) {
        $submissions[$rs['student_id']] = $rs['status'];
        $submission_times[$rs['student_id']] = $rs['submitted_at'];
    }

    $num = $att['meeting_number']; // e.g. 1
    $attendance_data[intval($num)]['info'] = $att;

    foreach ($students as $s) {
        $status = $submissions[$s['id']] ?? null;
        $time = $submission_times[$s['id']] ?? null;

        $attendance_data[intval($num)]['students'][] = [
            'student_name' => $s['full_name'],
            'student_id' => $s['id'],
            'username' => $s['username'],
            'nis' => $s['nis'],
            'status' => $status,
            'time' => $time
        ];
    }
}
ksort($attendance_data);

// Filter logic
$display_data = [];
$show_all = true;

if ($selected_meeting !== 'all') {
    $show_all = false;
    if (isset($attendance_data[intval($selected_meeting)])) {
        $filtered[intval($selected_meeting)] = $attendance_data[intval($selected_meeting)];
    }
    else {
        $filtered = [];
    }
    $display_data = $filtered;
}
else {
    $display_data = $attendance_data;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Absensi - <?php echo htmlspecialchars($class['name']); ?></title>
    <link rel="stylesheet" href="/public/assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', system-ui, -apple-system, sans-serif; }
        .main-content {
            max-width: 100% !important;
            background: #f5f7fb !important;
            padding: 0 !important;
        }

        .att-hero {
            position: relative;
            background: linear-gradient(135deg, #064e3b 0%, #047857 40%, #10b981 100%);
            padding: 2rem 3rem 4.5rem 5rem;
            overflow: hidden;
        }
        .att-hero::before {
            content: '';
            position: absolute; inset: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            pointer-events: none;
        }
        .hero-top {
            position: relative; z-index: 2;
            display: flex; justify-content: space-between; align-items: center;
        }
        .hero-top a.back { color: rgba(255,255,255,0.5); text-decoration: none; font-size: 0.85rem; }
        .hero-top a.back:hover { color: #fff; }
        .hero-info { position: relative; z-index: 2; margin-top: 1rem; }
        .hero-info h1 { color: #fff; font-size: 1.5rem; font-weight: 800; margin-bottom: 0.3rem; }
        .hero-info p { color: rgba(255,255,255,0.5); font-size: 0.88rem; }

        .att-content {
            position: relative;
            margin-top: -2.5rem;
            padding: 0 3rem 3rem;
            z-index: 10;
        }

        .att-panel {
            background: #fff;
            border-radius: 18px;
            padding: 24px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04), 0 6px 24px rgba(0,0,0,0.04);
            margin-bottom: 20px;
            animation: fade-up 0.4s ease-out both;
        }
        @keyframes fade-up {
            from { opacity: 0; transform: translateY(16px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .filter-bar {
            display: flex; align-items: flex-start; gap: 14px;
            margin-bottom: 0;
            flex-wrap: wrap;
        }
        .filter-bar label {
            font-size: 0.82rem; font-weight: 700; color: #475569;
            padding-top: 8px;
            flex-shrink: 0;
        }
        .filter-actions {
            display: flex; align-items: center; gap: 10px;
            flex-wrap: wrap;
            flex: 1;
            min-width: 0;
        }
        .filter-bar select {
            padding: 8px 14px; border: 1px solid #e2e8f0;
            border-radius: 10px; font-size: 0.85rem; font-family: inherit;
            background: #f8fafc;
            min-width: 200px;
            max-width: 100%;
        }
        .att-filter-panel { padding: 16px 24px; }
        .btn-print {
            margin-left: auto;
            background: #eef2ff; color: #4f46e5;
            border: none; padding: 8px 18px;
            border-radius: 10px; font-size: 0.82rem;
            font-weight: 600; cursor: pointer;
            transition: all 0.2s;
        }
        .btn-print:hover { background: #e0e7ff; }

        .att-table-wrap {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            margin-top: 8px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            background: #fff;
        }
        /* Attendance Table */
        .att-table {
            width: 100%;
            min-width: 720px;
            border-collapse: separate;
            border-spacing: 0;
            font-size: 0.85rem;
        }
        .att-table thead th {
            background: #f8fafc;
            padding: 12px 14px;
            text-align: left;
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #64748b;
            border-bottom: 2px solid #e2e8f0;
            position: sticky;
            top: 0;
            z-index: 2;
            box-shadow: 0 1px 0 #e2e8f0;
        }
        .att-table thead th:first-child { border-radius: 10px 0 0 0; }
        .att-table thead th:last-child { border-radius: 0 10px 0 0; }
        .att-table tbody td {
            padding: 11px 14px;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
        }
        .att-table tbody tr:hover { background: #f8fafc; }
        .att-table .num-col { width: 50px; text-align: center; font-weight: 700; color: #94a3b8; }
        .att-table .name-col { font-weight: 600; }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 10px; border-radius: 6px;
            font-size: 0.72rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.04em;
            max-width: 100%;
            box-sizing: border-box;
        }
        .status-badge svg {
            width: 14px;
            height: 14px;
            flex-shrink: 0;
        }
        .st-hadir { background: #dcfce7; color: #166534; }
        .st-sakit { background: #fee2e2; color: #991b1b; }
        .st-izin { background: #fef9c3; color: #854d0e; }
        .st-alpha { background: #f1f5f9; color: #64748b; }
        .st-terlambat { background: #ffedd5; color: #9a3412; }
        .st-none { color: #cbd5e1; font-weight: 500; font-size: 1.2rem; }

        .meeting-title {
            font-size: 1rem; font-weight: 700; color: #1e293b;
            margin-bottom: 4px;
        }
        .meeting-meta {
            font-size: 0.78rem; color: #94a3b8; margin-bottom: 16px;
        }
        .meeting-divider {
            border-top: 2px solid #e2e8f0;
            margin: 24px 0;
        }

        /* Summary row */
        .summary-row {
            display: flex; gap: 15px; margin-bottom: 20px; flex-wrap: wrap;
        }
        .summary-card {
            background: #f8fafc; border-radius: 12px; padding: 12px 18px;
            flex: 1; min-width: 100px; text-align: center;
            border: 1px solid #e2e8f0;
        }
        .summary-card .num { font-size: 1.2rem; font-weight: 900; }
        .summary-card .lbl { font-size: 0.7rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; margin-top: 2px; }
        
        /* Print styles â€” landscape + header tabel berulang, hindari panel utuh pindah halaman */
        @media print {
            @page {
                size: landscape;
                margin: 10mm;
            }
            body { 
                background: #fff; 
                -webkit-print-color-adjust: exact !important; 
                print-color-adjust: exact !important; 
            }
            .app-container { display: block !important; margin: 0 !important; padding: 0 !important; }
            .sidebar, .att-hero, .filter-bar, .btn-print, .hero-top, .no-print { display: none !important; }
            
            .main-content { 
                width: 100% !important; max-width: none !important; 
                margin: 0 !important; padding: 0 !important; 
                background: white !important;
                box-shadow: none !important;
            }
            
            .att-content { 
                margin: 0 !important; padding: 0 !important; 
                width: 100% !important; 
            }
            
            .att-panel {
                border: none !important;
                box-shadow: none !important; 
                margin-bottom: 14px !important;
                padding: 0 !important;
                page-break-inside: auto;
                break-inside: auto;
            }

            .meeting-title {
                page-break-after: avoid;
                break-after: avoid;
                font-size: 11pt;
            }
            .meeting-meta { font-size: 9pt; margin-bottom: 8px !important; }

            .summary-row {
                gap: 8px;
                margin-bottom: 10px !important;
                page-break-inside: avoid;
                break-inside: avoid;
            }
            .summary-card { 
                border: 1px solid #999 !important; 
                box-shadow: none !important;
                padding: 6px 10px;
            }
            .summary-card .num { font-size: 11pt; }
            .summary-card .lbl { font-size: 7pt; }

            .att-table-wrap {
                overflow: visible !important;
                border: none !important;
                margin-top: 0 !important;
            }

            .att-table {
                min-width: 0 !important;
                font-size: 8pt;
                width: 100% !important;
                border-collapse: collapse;
                table-layout: fixed;
            }
            .att-table thead { display: table-header-group; }
            .att-table thead th {
                position: static !important;
                box-shadow: none !important;
                background: #f0f0f0 !important;
                color: #000 !important;
                border: 1px solid #333 !important;
                padding: 5px 4px !important;
                font-size: 7pt;
                vertical-align: middle;
            }
            .att-table th:nth-child(1) { width: 4%; }
            .att-table th:nth-child(2) { width: 28%; }
            .att-table th:nth-child(3) { width: 12%; }
            .att-table th:nth-child(4) { width: 22%; }
            .att-table th:nth-child(5) { width: 10%; }

            .att-table tbody tr {
                page-break-inside: avoid;
                break-inside: avoid;
            }
            .att-table td { 
                border: 1px solid #ccc !important; 
                padding: 4px 4px !important;
                vertical-align: top;
                word-wrap: break-word;
                overflow-wrap: anywhere;
            }
            
            .status-badge {
                border: 1px solid #bbb;
                font-size: 7pt !important;
                padding: 2px 5px !important;
                text-transform: none;
                letter-spacing: 0;
                white-space: normal;
            }
            .status-badge svg { display: none !important; }

            .print-header {
                display: block !important;
                text-align: center;
                margin-bottom: 12px;
                padding-bottom: 8px;
                border-bottom: 2px solid #000;
                page-break-after: avoid;
            }
            .print-header h2 { margin: 0 0 5px; font-size: 14pt; }
            .print-header p { margin: 0; font-size: 9pt; color: #000; }
        }
        .print-header { display: none; }

        @media (max-width: 900px) {
            .att-hero { padding: 1.5rem 1.5rem 4rem; }
            .att-content { padding: 0 1.5rem 2rem; }
        }
    </style>
</head>
<body>

<div class="app-container">
    <?php include '../templates/sidebar.php'; ?>
    
    <main class="main-content">

        <div class="att-hero">
            <div class="hero-top">
                <a href="view_class.php?id=<?php echo $class_id; ?>" class="back">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"/><path d="M12 19l-7-7 7-7"/></svg>
                    Kembali ke Kelas
                </a>
            </div>
            <div class="hero-info">
                <h1>Absensi - <?php echo htmlspecialchars($class['school_class_name']); ?></h1>
                <p><?php echo htmlspecialchars($class['name']); ?></p>
            </div>
        </div>

        <div class="att-content">

            <!-- Print Header (only visible when printing) -->
            <div class="print-header">
                <h2>Rekap Absensi - <?php echo htmlspecialchars($class['name']); ?></h2>
                <p><?php echo htmlspecialchars($class['subject']); ?> Â· <?php echo htmlspecialchars($class['school_class_name']); ?></p>
                <p>Dicetak: <?php echo date('d M Y H:i'); ?></p>
            </div>

            <div class="att-panel no-print att-filter-panel">
                <div class="filter-bar">
                    <label for="meetingFilter">Filter Pertemuan:</label>
                    <div class="filter-actions">
                        <select id="meetingFilter" onchange="window.location.href='view_attendance.php?class_id=<?php echo (int) $class_id; ?>&meeting='+this.value">
                            <option value="all" <?php echo $selected_meeting === 'all' ? 'selected' : ''; ?>>Semua Pertemuan</option>
                            <?php for ($i = 1; $i <= 16; $i++): ?>
                            <option value="<?php echo $i; ?>" <?php echo $selected_meeting == $i ? 'selected' : ''; ?>>
                                Pertemuan <?php echo $i; ?>
                                <?php echo isset($attendance_data[$i]) ? '' : '(belum ada)'; ?>
                            </option>
                            <?php endfor; ?>
                        </select>
                        <a href="export_attendance.php?class_id=<?php echo (int) $class_id; ?>" class="btn-print" style="background:#d1fae5; color:#065f46; text-decoration:none; margin-left:0;">
                            ðŸ“Š Export Excel
                        </a>
                        <button type="button" class="btn-print" onclick="window.print()" style="margin-left:0;"><svg width='20' height='20' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' style='display:inline-block; vertical-align:middle; line-height:1;'><polyline points='6 9 6 2 18 2 18 9'/><path d='M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2'/><rect x='6' y='14' width='12' height='8'/></svg> Cetak / Print</button>
                    </div>
                </div>
            </div>

            <?php if (empty($display_data)): ?>
                <div class="att-panel">
                    <div style="text-align:center; padding:3rem; color:#94a3b8;">
                        <p style="font-size:2.5rem; margin-bottom:8px;"><svg width='20' height='20' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' style='display:inline-block; vertical-align:middle; line-height:1;'><path d='M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2'/><rect x='8' y='2' width='8' height='4' rx='1' ry='1'/></svg></p>
                        <p>Belum ada data absensi untuk pertemuan ini.</p>
                    </div>
                </div>
            <?php
else: ?>

                <?php foreach ($display_data as $meeting_num => $data):
        // Calculate Counts
        $counts = ['hadir' => 0, 'sakit' => 0, 'izin' => 0, 'alpha' => 0, 'terlambat' => 0];
        foreach ($data['students'] as $student) {
            $st = $student['status'];
            if ($st && isset($counts[$st])) {
                $counts[$st]++;
            }
        }
        $total_students = count($students);
?>
                <div class="att-panel">
                    <div class="meeting-title">Pertemuan <?php echo $meeting_num; ?></div>
                    <div class="meeting-meta">
                        Tanggal: <?php echo date('d M Y', strtotime($data['info']['deadline'])); ?>
                    </div>

                    <!-- Summary -->
                    <div class="summary-row">
                        <div class="summary-card" style="border-color:#dcfce7; background:#f0fdf4;">
                            <div class="num" style="color:#166534;"><?php echo $counts['hadir']; ?></div>
                            <div class="lbl">Hadir</div>
                        </div>
                        <div class="summary-card" style="border-color:#fee2e2; background:#fef2f2;">
                            <div class="num" style="color:#991b1b;"><?php echo $counts['sakit']; ?></div>
                            <div class="lbl">Sakit</div>
                        </div>
                        <div class="summary-card" style="border-color:#fef9c3; background:#fefce8;">
                            <div class="num" style="color:#854d0e;"><?php echo $counts['izin']; ?></div>
                            <div class="lbl">Izin</div>
                        </div>
                        <div class="summary-card" style="border-color:#f1f5f9;">
                            <div class="num" style="color:#64748b;"><?php echo $counts['alpha']; ?></div>
                            <div class="lbl">Alpha</div>
                        </div>
                        <div class="summary-card" style="border-color:#ffedd5; background:#fff7ed;">
                            <div class="num" style="color:#9a3412;"><?php echo $counts['terlambat']; ?></div>
                            <div class="lbl">Terlambat</div>
                        </div>
                        <div class="summary-card">
                            <div class="num"><?php echo $total_students; ?></div>
                            <div class="lbl">Total Siswa</div>
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="att-table-wrap">
                    <table class="att-table">
                        <thead>
                            <tr>
                                <th class="num-col">No</th>
                                <th class="name-col">Nama Siswa</th>
                                <th>NIS</th>
                                <th>Status Kehadiran</th>
                                <th>Waktu Absen</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['students'] as $i => $s): ?>
                            <tr>
                                <td class="num-col"><?php echo $i + 1; ?></td>
                                <td class="name-col"><?php echo htmlspecialchars($s['student_name']); ?></td>
                                <td style="color:#64748b;"><?php echo htmlspecialchars($s['nis'] ?? '-'); ?></td>
                                <td>
                                    <?php
            $status = $s['status'];
            if ($status === 'hadir'): ?>
                                        <span class="status-badge st-hadir"><svg width='20' height='20' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' style='display:inline-block; vertical-align:middle; line-height:1;'><polyline points='20 6 9 17 4 12'/></svg> Hadir</span>
                                    <?php
            elseif ($status === 'sakit'): ?>
                                        <span class="status-badge st-sakit"><svg width='20' height='20' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' style='display:inline-block; vertical-align:middle; line-height:1;'><circle cx='12' cy='12' r='10'/><path d='M8 14s1.5 2 4 2 4-2 4-2'/><line x1='9' y1='9' x2='9.01' y2='9'/><line x1='15' y1='9' x2='15.01' y2='9'/></svg> Sakit</span>
                                    <?php
            elseif ($status === 'izin'): ?>
                                        <span class="status-badge st-izin"><svg width='20' height='20' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' style='display:inline-block; vertical-align:middle; line-height:1;'><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg> Izin</span>
                                    <?php
            elseif ($status === 'alpha'): ?>
                                        <span class="status-badge st-alpha"><svg width='20' height='20' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' style='display:inline-block; vertical-align:middle; line-height:1;'><circle cx='12' cy='12' r='10'/><path d='M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3'/><line x1='12' y1='17' x2='12.01' y2='17'/></svg> Alpha</span>
                                    <?php
            elseif ($status === 'terlambat'): ?>
                                        <span class="status-badge st-terlambat">â° Terlambat</span>
                                    <?php
            else: ?>
                                        <span class="status-badge st-none">-</span>
                                    <?php
            endif; ?>
                                </td>
                                <td style="color:#64748b; font-size:0.8rem; font-family:monospace;">
                                    <?php echo $s['time'] ? date('H:i', strtotime($s['time'])) : '-'; ?>
                                </td>
                            </tr>
                            <?php
        endforeach; ?>
                        </tbody>
                    </table>
                    </div>
                </div>

                <?php
    endforeach; ?>

            <?php
endif; ?>

        </div>
    </main>
</div>

</body>
</html>

