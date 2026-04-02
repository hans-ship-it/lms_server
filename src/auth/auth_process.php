<?php
// src/auth/auth_process.php
session_start();
require_once '../../config/database.php';

/**
 * Cocokkan password input dengan nilai di DB: hash (password_hash) atau legacy plain text.
 */
function lms_password_matches(string $plain, string $stored): bool
{
    if ($stored === '') {
        return false;
    }
    $info = password_get_info($stored);
    if (($info['algo'] ?? 0) !== 0) {
        return password_verify($plain, $stored);
    }
    return hash_equals($stored, $plain);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        header("Location: ../../login.php?error=Username dan password wajib diisi.");
        exit;
    }

    // Fetch user including GENDER by NIP, NIS, or Username
    $stmt = $pdo->prepare("SELECT * FROM users WHERE nip = ? OR nis = ? OR username = ?");
    $stmt->execute([$username, $username, $username]);
    $user = $stmt->fetch();

    if ($user) {
        if (lms_password_matches($password, (string) $user['password'])) {

            $known_roles = ['admin', 'guru', 'siswa', 'osis', 'kepsek', 'wakasek', 'bk'];
            if (!in_array($user['role'], $known_roles, true)) {
                error_log('[LMS auth] Login ditolak: role tidak dikenal di aplikasi (user_id=' . (int) $user['id'] . ', role=' . $user['role'] . ')');
                header("Location: ../../login.php?error=Role akun tidak didukung. Hubungi administrator.");
                exit;
            }

            $portal = trim($_POST['portal'] ?? 'lms');
            $u_role = $user['role'];
            $u_status = $user['status'] ?? 'active';

            // STRICT PORTAL VALIDATION
	if ($portal === 'tracer') {
		if (!(($u_role === 'siswa' && $u_status === 'graduated') || $u_role === 'guru' || $u_role === 'kepsek')) {
                    error_log('[LMS auth] Strict Block: Non-authorized user tried to login to Tracer');
                    header("Location: ../../login.php?portal=tracer&error=Akses Ditolak! Portal Tracer khusus untuk Alumni, Guru, dan Pimpinan.");	
                    exit;
                }
            } elseif ($portal === 'nilai') {
                if ($u_role !== 'siswa') {
                    error_log('[LMS auth] Strict Block: Non-siswa tried to login to Nilai');
                    header("Location: ../../login.php?portal=nilai&error=Akses Ditolak! Portal Nilai khusus untuk Siswa aktif dan Alumni.");
                    exit;
                }
            } elseif ($portal === 'lms') {
                if ($u_role === 'siswa' && $u_status === 'graduated') {
                    error_log('[LMS auth] Strict Block: Alumni tried to login to LMS');
                    header("Location: ../../login.php?portal=lms&error=Akses Ditolak! Alumni tidak dapat mengakses Portal LMS (Gunakan Portal Tracer).");
                    exit;
                }
            }

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['gender'] = $user['gender']; // Added Gender to Session
            $_SESSION['nip'] = $user['nip'];
            $_SESSION['nis'] = $user['nis'];

            $info = password_get_info((string) $user['password']);
            if (($info['algo'] ?? 0) !== 0) {
                error_log('[LMS auth] Login sukses dengan password ter-hash (user_id=' . (int) $user['id'] . ', role=' . $user['role'] . ')');
            }

            // Target Redirect based on portal
            if ($portal === 'tracer') {
                if ($user['role'] === 'guru' || $user['role'] === 'kepsek') {
                    header("Location: ../../tracer_directory.php");
                } else {
                    header("Location: ../../tracer_form.php");
                }                exit;
            } elseif ($portal === 'nilai') {
                header("Location: ../../pantauan_nilai.php");
                exit;
            } elseif ($portal === 'ecounseling') {
                header("Location: ../../e_counseling.php");
                exit;
            }

            // Redirect based on role if portal is LMS
            if (isset($_SESSION['redirect_after_login'])) {
                $redirect = $_SESSION['redirect_after_login'];
                unset($_SESSION['redirect_after_login']);
                header("Location: " . $redirect);
                exit;
            }

            if ($user['role'] === 'admin') {
                header("Location: ../admin/dashboard.php");
            }
            elseif ($user['role'] === 'guru') {
                header("Location: ../guru/dashboard.php");
            }
            elseif ($user['role'] === 'siswa') {
                if (($user['status'] ?? 'active') === 'graduated') {
                    header("Location: ../../tracer_form.php");
                } else {
                    header("Location: ../siswa/dashboard.php");
                }
            }
            elseif ($user['role'] === 'osis') {
                header("Location: ../osis/dashboard.php");
            }
            elseif (in_array($user['role'], ['kepsek', 'wakasek'])) {
                header("Location: ../pimpinan/dashboard.php");
            }
            elseif ($user['role'] === 'bk') {
                header("Location: ../bk/dashboard.php");
            }
            exit;
        }
        else {
            error_log('[LMS auth] Login gagal: password salah (username=' . $username . ')');
            header("Location: ../../login.php?error=Password salah!");
            exit;
        }
    }
    else {
        error_log('[LMS auth] Login gagal: pengguna tidak ditemukan (input=' . $username . ')');
        header("Location: ../../login.php?error=Username tidak ditemukan!");
        exit;
    }
}
?>
