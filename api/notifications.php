<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}
require_once '../config/firebase.php';

$activities = firebase_get('/activities');
$users = firebase_get('/users');
$role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];

$counts = [
    'menunggu_persetujuan_laporan' => 0,
    'perlu_revisi' => 0,
    'berjalan' => 0,
    'selesai' => 0,
    'pending_admin' => 0,
    'total' => 0,
];

$counts_staff = [
    'perlu_revisi' => 0,
    'selesai' => 0,
    'tugas_baru' => 0,
];

$recent_reports = [];

if ($activities) {
    foreach ($activities as $key => $act) {
        $status = $act['status'] ?? '';
        
        if ($role === 'admin') {
            $counts['total']++;
            if (isset($counts[$status])) {
                $counts[$status]++;
            }

            // Collect recently submitted reports (last 24 hours)
            if ($status === 'menunggu_persetujuan_laporan' && isset($act['laporan']['submitted_at'])) {
                $submittedAt = strtotime($act['laporan']['submitted_at']);
                if ($submittedAt && (time() - $submittedAt) < 86400) {
                    $userName = isset($users[$act['user_id']]) ? $users[$act['user_id']]['nama_lengkap'] : 'Staff';
                    $recent_reports[] = [
                        'id' => $key,
                        'nama_kegiatan' => $act['nama_kegiatan'] ?? '-',
                        'user_name' => $userName,
                        'submitted_at' => $act['laporan']['submitted_at'],
                    ];
                }
            }
        } elseif ($role === 'staff') {
            if (($act['user_id'] ?? '') === $user_id) {
                if ($status === 'perlu_revisi') {
                    $counts_staff['perlu_revisi']++;
                } elseif ($status === 'selesai') {
                    $counts_staff['selesai']++;
                }
                
                if (!empty($act['is_from_admin']) && $status === 'belum_mulai') {
                    $counts_staff['tugas_baru']++;
                }
            }
        }
    }
}

if ($role === 'admin') {
    // Count pending admin approvals
    if ($users) {
        foreach ($users as $user) {
            if (isset($user['role']) && $user['role'] === 'admin' && empty($user['is_admin_approved'])) {
                $counts['pending_admin']++;
            }
        }
    }

    header('Content-Type: application/json');
    echo json_encode([
        'counts' => $counts,
        'recent_reports' => $recent_reports,
        'total_notifications' => $counts['menunggu_persetujuan_laporan'] + $counts['pending_admin'],
    ]);
} else {
    // For staff, actionable notifications are revisions and new tasks from admin.
    header('Content-Type: application/json');
    echo json_encode([
        'counts' => $counts_staff,
        'total_notifications' => $counts_staff['perlu_revisi'] + $counts_staff['tugas_baru'], 
    ]);
}
