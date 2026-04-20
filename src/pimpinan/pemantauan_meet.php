<?php
session_start();
require_once "../../config/database.php";

if (!isset($_SESSION["user_id"]) || !in_array($_SESSION["role"], ["kepsek", "wakasek"])) {
    header("Location: ../../login.php");
    exit;
}

date_default_timezone_set("Asia/Makassar");
$now = new DateTime();

try {
    $sql = "SELECT 
                m.id, m.meet_link, m.created_at, m.start_time, m.end_time,
                u.full_name AS teacher_name,
                c.name AS class_name,
                COALESCE(s.name, 'Umum') AS subject_name
            FROM meet_links m
            JOIN users u ON m.teacher_id = u.id
            JOIN classes c ON m.class_id = c.id
            LEFT JOIN subjects s ON m.subject_id = s.id
            ORDER BY COALESCE(m.start_time, m.created_at) ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $meet_links = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $grouped_meets = [];
    foreach ($meet_links as $link) {
        $raw_date = $link["start_time"] ? $link["start_time"] : $link["created_at"];
        $date_key = date("Y-m-d", strtotime($raw_date));
        $grouped_meets[$date_key][] = $link;
    }
    krsort($grouped_meets);
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

$available_dates = array_keys($grouped_meets);
$selected_date = isset($_GET["tanggal"]) && isset($grouped_meets[$_GET["tanggal"]])
    ? $_GET["tanggal"]
    : ($available_dates[0] ?? null);

$hari_array  = ["Sunday"=>"Minggu","Monday"=>"Senin","Tuesday"=>"Selasa","Wednesday"=>"Rabu","Thursday"=>"Kamis","Friday"=>"Jumat","Saturday"=>"Sabtu"];
$bulan_array = ["01"=>"Januari","02"=>"Februari","03"=>"Maret","04"=>"April","05"=>"Mei","06"=>"Juni","07"=>"Juli","08"=>"Agustus","09"=>"September","10"=>"Oktober","11"=>"November","12"=>"Desember"];

function formatTanggal($date, $hari_array, $bulan_array) {
    $d = strtotime($date);
    return $hari_array[date("l",$d)] . ", " . date("d",$d) . " " . $bulan_array[date("m",$d)] . " " . date("Y",$d);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pemantauan Google Meet - Pimpinan</title>
    <link rel="stylesheet" href="/public/assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .main-content { background: #f5f7fb !important; padding: 0 !important; }
        .page-hero {
            background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 50%, #1d4ed8 100%);
            padding: 2.5rem 3rem 5rem;
            position: relative; overflow: hidden;
        }
        .page-hero::before {
            content: ''; position: absolute; right: -60px; top: -60px;
            width: 250px; height: 250px; background: rgba(255,255,255,0.07); border-radius: 50%;
        }
        .page-hero h1 { color: #fff; font-size: 1.6rem; font-weight: 700; margin: 0 0 0.4rem; }
        .page-hero p  { color: rgba(255,255,255,0.8); margin: 0; font-size: 0.95rem; }
        .page-content {
            position: relative; margin-top: -2.5rem;
            padding: 0 3rem 3rem; z-index: 10;
        }
        .pm-layout { display: flex; gap: 1.5rem; align-items: flex-start; }
        .pm-main   { flex: 1; min-width: 0; }
        .pm-sidebar {
            width: 230px; flex-shrink: 0;
            background: #fff; border-radius: 14px;
            border: 1px solid #e8edf5; overflow: hidden;
            position: sticky; top: 80px;
        }
        .pm-sidebar-header {
            background: #1d4ed8; color: #fff;
            padding: 14px 16px; font-weight: 700;
            font-size: 0.88rem; display: flex; align-items: center; gap: 8px;
        }
        .pm-sidebar-dates { padding: 8px; }
        .pm-date-btn {
            display: block; width: 100%; text-align: left;
            padding: 10px 12px; border-radius: 9px;
            border: none; background: transparent; cursor: pointer;
            font-family: inherit; font-size: 0.82rem; color: #334155;
            transition: background 0.15s; text-decoration: none;
            margin-bottom: 2px;
        }
        .pm-date-btn:hover { background: #f1f5f9; }
        .pm-date-btn.active { background: #eef2ff; color: #1d4ed8; font-weight: 700; }
        .pm-date-btn .dot { width: 7px; height: 7px; border-radius: 50%; background: #1d4ed8; display: inline-block; margin-right: 7px; }
        /* Meet list rows */
        .db-section { background: #fff; border-radius: 14px; border: 1px solid #e8edf5; overflow: hidden; margin-bottom: 1.2rem; }
        .meet-row { padding: 16px 20px; border-bottom: 1px solid #f1f5f9; }
        .meet-row:last-child { border-bottom: none; }
        .meet-top { display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px; }
        .meet-class { font-weight: 700; font-size: 0.95rem; color: #0f172a; }
        .meet-status { padding: 3px 10px; border-radius: 20px; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; }
        .status-berlangsung { background: #d1fae5; color: #059669; }
        .status-selesai    { background: #fee2e2; color: #b91c1c; }
        .status-belum      { background: #f1f5f9; color: #475569; }
        .status-none       { background: #f8fafc; color: #64748b; }
        .meet-info { font-size: 0.82rem; color: #64748b; margin-bottom: 5px; display: flex; align-items: center; gap: 6px; }
        .meet-time { display: inline-flex; align-items: center; gap: 6px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 7px; padding: 5px 10px; font-size: 0.8rem; font-weight: 500; color: #334155; margin: 6px 0; }
        .btn-meet-link { display: inline-flex; align-items: center; gap: 6px; background: #10b981; color: #fff; padding: 7px 14px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 0.82rem; margin-top: 6px; }
        .btn-meet-link:hover { background: #059669; }
        .btn-meet-link.disabled { background: #e2e8f0; color: #94a3b8; pointer-events: none; }
        .date-header { display: flex; align-items: center; gap: 10px; margin-bottom: 1rem; }
        .date-header h2 { font-size: 1.1rem; font-weight: 700; color: #0f172a; margin: 0; }
        .empty-state { text-align: center; padding: 4rem 2rem; color: #94a3b8; }
        @media (max-width: 768px) {
            .pm-layout { flex-direction: column-reverse; }
            .pm-sidebar { width: 100%; position: static; }
            .page-content { padding: 0 1rem 2rem; }
            .page-hero { padding: 2rem 1.5rem 4.5rem; }
        }
    </style>
</head>
<body>

<div class="app-container">
    <?php include "../templates/sidebar.php"; ?>
    <main class="main-content">
        <div class="page-hero">
            <h1>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle;margin-right:8px;"><path d="M15.6 11.6L22 7v10l-6.4-4.5v-1z"/><path d="M4 5h9a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V7c0-1.1.9-2 2-2z"/></svg>
                Pemantauan Google Meet
            </h1>
            <p>Pilih tanggal untuk melihat meet yang dijadwalkan.</p>
        </div>

        <div class="page-content">
            <?php if (empty($grouped_meets)): ?>
                <div class="db-section">
                    <div class="empty-state">
                        <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom:1rem;opacity:0.4;"><path d="M15.6 11.6L22 7v10l-6.4-4.5v-1z"/><path d="M4 5h9a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V7c0-1.1.9-2 2-2z"/></svg>
                        <p style="font-weight:600; color:#64748b;">Belum ada tautan Google Meet yang tersimpan.</p>
                    </div>
                </div>
            <?php else: ?>
                <div class="pm-layout">
                    <!-- Meet list -->
                    <div class="pm-main">
                        <?php if ($selected_date): ?>
                            <div class="date-header">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#1d4ed8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                <h2><?php echo formatTanggal($selected_date, $hari_array, $bulan_array); ?></h2>
                                <?php if ($selected_date === $now->format("Y-m-d")): ?>
                                    <span style="background:#1d4ed8;color:#fff;font-size:0.72rem;padding:3px 10px;border-radius:20px;">Hari Ini</span>
                                <?php endif; ?>
                            </div>

                            <?php foreach ($grouped_meets[$selected_date] as $link):
                                $start_dt = $link["start_time"] ? new DateTime($link["start_time"]) : null;
                                $end_dt   = $link["end_time"]   ? new DateTime($link["end_time"])   : null;
                                $statusClass = "status-none"; $statusText = "Tidak Ada Waktu"; $active = true;
                                if ($start_dt && $end_dt) {
                                    if ($now < $start_dt)    { $statusClass="status-belum";      $statusText="Belum Mulai";       $active=false; }
                                    elseif ($now > $end_dt)  { $statusClass="status-selesai";    $statusText="Selesai";           $active=false; }
                                    else                     { $statusClass="status-berlangsung"; $statusText="Sedang Berlangsung"; }
                                }
                            ?>
                            <div class="db-section">
                                <div class="meet-row">
                                    <div class="meet-top">
                                        <span class="meet-class"><?php echo htmlspecialchars($link["class_name"]); ?></span>
                                        <span class="meet-status <?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                                    </div>
                                    <div class="meet-info">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                        <?php echo htmlspecialchars($link["teacher_name"]); ?>
                                        &bull;
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                                        <?php echo htmlspecialchars($link["subject_name"]); ?>
                                    </div>
                                    <div class="meet-time">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                        <?php
                                        if ($start_dt && $end_dt)
                                            echo $start_dt->format("H:i") . " – " . $end_dt->format("H:i");
                                        else
                                            echo "Dibuat: " . date("H:i", strtotime($link["created_at"]));
                                        ?>
                                    </div>
                                    <br>
                                    <a href="<?php echo htmlspecialchars($link["meet_link"]); ?>" target="_blank"
                                       class="btn-meet-link <?php echo !$active ? 'disabled' : ''; ?>">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                                        <?php echo $statusText === "Selesai" ? "Ruangan Berakhir" : "Buka Ruangan"; ?>
                                    </a>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Date sidebar -->
                    <div class="pm-sidebar">
                        <div class="pm-sidebar-header">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                            Arsip Tanggal
                        </div>
                        <div class="pm-sidebar-dates">
                            <?php foreach ($grouped_meets as $date => $lnks):
                                $isToday  = ($date === $now->format("Y-m-d"));
                                $isActive = ($date === $selected_date);
                                $count    = count($lnks);
                                $d = strtotime($date);
                                $hari = $hari_array[date("l", $d)];
                                $tgl  = date("d", $d);
                                $bln  = $bulan_array[date("m", $d)];
                            ?>
                            <a href="?tanggal=<?php echo $date; ?>" class="pm-date-btn <?php echo $isActive ? 'active' : ''; ?>">
                                <span class="dot"></span>
                                <?php echo "$hari, $tgl $bln"; ?>
                                <?php if ($isToday): ?><span style="font-size:0.65rem;background:#1d4ed8;color:#fff;border-radius:4px;padding:0 5px;margin-left:4px;">Hari Ini</span><?php endif; ?>
                                <span style="float:right;background:#e2e8f0;border-radius:10px;padding:1px 7px;font-size:0.7rem;color:#475569;"><?php echo $count; ?></span>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

</body>
</html>
