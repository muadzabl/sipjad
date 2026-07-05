<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    die("Akses ditolak. Silakan login terlebih dahulu.");
}

require_once 'config/firebase.php';

$activityId = $_GET['id'] ?? '';
if (empty($activityId)) {
    die("ID Kegiatan tidak valid.");
}

$activity = firebase_get("/activities/$activityId");
if (!$activity || $activity['status'] !== 'selesai' || empty($activity['laporan'])) {
    die("Data kegiatan tidak ditemukan atau laporan belum disetujui (selesai).");
}

// Get user data
$users = firebase_get("/users");
$userName = isset($users[$activity['user_id']]) ? $users[$activity['user_id']]['nama_lengkap'] : 'Unknown User';
$lap = $activity['laporan'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Laporan - <?php echo htmlspecialchars($activity['nama_kegiatan']); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        
        body {
            font-family: 'Outfit', 'Times New Roman', serif;
            background: #f8fafc;
            color: #1e293b;
            padding: 30px;
        }

        /* Print Button - disappears when printing */
        .print-btn-wrap {
            position: fixed;
            top: 24px;
            right: 24px;
            display: flex;
            gap: 12px;
            z-index: 100;
        }
        .print-btn {
            padding: 12px 24px;
            background: linear-gradient(135deg, #4f46e5, #4338ca);
            color: #fff;
            border: none;
            cursor: pointer;
            border-radius: 12px;
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
            font-size: 14px;
            box-shadow: 0 4px 14px rgba(79, 70, 229, 0.4);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .print-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(79, 70, 229, 0.5);
        }
        .back-btn {
            padding: 12px 24px;
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
            cursor: pointer;
            border-radius: 12px;
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
            font-size: 14px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }

        .document {
            max-width: 900px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(15, 23, 42, 0.1);
        }

        /* Document Header */
        .doc-header {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            padding: 40px 50px;
            color: white;
            position: relative;
            overflow: hidden;
        }
        .doc-header::before {
            content: '';
            position: absolute;
            top: -60%;
            right: -10%;
            width: 300px;
            height: 300px;
            background: rgba(255,255,255,0.08);
            border-radius: 50%;
        }
        .doc-header::after {
            content: '';
            position: absolute;
            bottom: -50%;
            right: 10%;
            width: 200px;
            height: 200px;
            background: rgba(255,255,255,0.05);
            border-radius: 50%;
        }
        .doc-header-inner {
            position: relative;
            z-index: 1;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        .doc-header-title h1 {
            font-size: 28px;
            font-weight: 800;
            letter-spacing: -0.5px;
            margin-bottom: 6px;
        }
        .doc-header-title p {
            font-size: 14px;
            opacity: 0.8;
            font-weight: 500;
        }
        .doc-header-badge {
            background: rgba(255,255,255,0.2);
            border: 1px solid rgba(255,255,255,0.3);
            border-radius: 10px;
            padding: 10px 20px;
            text-align: right;
            backdrop-filter: blur(8px);
        }
        .doc-header-badge span {
            display: block;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
            opacity: 0.8;
            margin-bottom: 4px;
        }
        .doc-header-badge strong {
            font-size: 13px;
            font-weight: 700;
        }

        /* Status Badge */
        .status-badge {
            display: inline-flex;
            align-items: center;
            background: rgba(16, 185, 129, 0.2);
            border: 1px solid rgba(16, 185, 129, 0.4);
            color: #d1fae5;
            border-radius: 100px;
            padding: 4px 14px;
            font-size: 12px;
            font-weight: 700;
            margin-top: 12px;
            letter-spacing: 0.5px;
        }
        .status-badge::before {
            content: '';
            width: 7px;
            height: 7px;
            background: #10b981;
            border-radius: 50%;
            margin-right: 8px;
        }

        /* Document Body */
        .doc-body {
            padding: 40px 50px;
        }

        /* Info Grid */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 36px;
        }
        .info-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 16px 20px;
        }
        .info-card-label {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #94a3b8;
            margin-bottom: 4px;
        }
        .info-card-value {
            font-size: 15px;
            font-weight: 600;
            color: #1e293b;
        }
        .info-card.full-width {
            grid-column: 1 / -1;
        }

        /* Sections */
        .section {
            margin-bottom: 28px;
        }
        .section-header {
            display: flex;
            align-items: center;
            margin-bottom: 12px;
        }
        .section-letter {
            width: 30px;
            height: 30px;
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            color: white;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 13px;
            margin-right: 12px;
            flex-shrink: 0;
        }
        .section-title-text {
            font-size: 15px;
            font-weight: 700;
            color: #334155;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .section-content {
            padding: 18px 20px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            font-size: 14.5px;
            line-height: 1.7;
            color: #374151;
            text-align: justify;
        }
        .section-content.warning {
            background: #fff7ed;
            border-color: #fed7aa;
            color: #9a3412;
        }
        .section-content.success {
            background: #f0fdf4;
            border-color: #bbf7d0;
            color: #166534;
        }

        /* Lampiran / Attachment */
        .attachment-img {
            width: 100%;
            max-height: 400px;
            object-fit: contain;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            margin-top: 4px;
        }
        .attachment-link {
            display: inline-flex;
            align-items: center;
            color: #4f46e5;
            font-weight: 600;
            font-size: 14px;
            text-decoration: none;
            background: #eef2ff;
            padding: 10px 18px;
            border-radius: 10px;
            border: 1px solid #c7d2fe;
            margin-top: 4px;
        }

        /* Divider */
        .divider {
            border: none;
            border-top: 1px solid #e2e8f0;
            margin: 30px 0;
        }

        /* Signature Area */
        .signature-area {
            display: flex;
            justify-content: flex-end;
            margin-top: 50px;
        }
        .signature-box {
            text-align: center;
            width: 260px;
        }
        .signature-box p {
            font-size: 13px;
            color: #64748b;
            margin-bottom: 70px;
        }
        .signature-line {
            border-top: 2px solid #334155;
            padding-top: 8px;
        }
        .signature-line span {
            font-size: 13px;
            font-weight: 600;
            color: #334155;
        }

        /* Footer */
        .doc-footer {
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            padding: 16px 50px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .doc-footer span {
            font-size: 11px;
            color: #94a3b8;
            font-weight: 500;
        }

        @media print {
            body { background: #fff; padding: 0; }
            .print-btn-wrap { display: none !important; }
            .document { box-shadow: none; border-radius: 0; }
            .doc-header { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .section-letter { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>
    <div class="print-btn-wrap">
        <a href="javascript:history.back()" class="back-btn">← Kembali</a>
        <button class="print-btn" onclick="window.print()">
            🖨️ Cetak / Simpan PDF
        </button>
    </div>

    <div class="document">
        <!-- Header -->
        <div class="doc-header">
            <div class="doc-header-inner">
                <div class="doc-header-title">
                    <h1>LAPORAN PELAKSANAAN KEGIATAN</h1>
                    <p>Sistem Informasi Penjadwalan & Pelaporan (SIPJAD)</p>
                    <div class="status-badge">✓ DISETUJUI</div>
                </div>
                <div class="doc-header-badge">
                    <span>Tanggal Cetak</span>
                    <strong><?php echo date('d M Y'); ?></strong>
                </div>
            </div>
        </div>

        <!-- Body -->
        <div class="doc-body">
            <!-- Info Grid -->
            <div class="info-grid">
                <div class="info-card">
                    <div class="info-card-label">Nama Pegawai / Staff</div>
                    <div class="info-card-value"><?php echo htmlspecialchars($userName); ?></div>
                </div>
                <div class="info-card">
                    <div class="info-card-label">Status Laporan</div>
                    <div class="info-card-value" style="color: #059669;">✓ Selesai & Disetujui</div>
                </div>
                <div class="info-card full-width">
                    <div class="info-card-label">Nama Kegiatan</div>
                    <div class="info-card-value" style="font-size: 17px;"><?php echo htmlspecialchars($activity['nama_kegiatan']); ?></div>
                </div>
                <div class="info-card">
                    <div class="info-card-label">Lokasi Kegiatan</div>
                    <div class="info-card-value"><?php echo htmlspecialchars($activity['lokasi'] ?? '-'); ?></div>
                </div>
                <div class="info-card">
                    <div class="info-card-label">Waktu Pelaksanaan</div>
                    <div class="info-card-value" style="font-size: 13px;">
                        <?php echo date('d F Y, H:i', strtotime($activity['waktu_mulai'])); ?><br>
                        s/d <?php echo date('d F Y, H:i', strtotime($activity['waktu_selesai'])); ?>
                    </div>
                </div>
                <?php if (!empty($activity['deskripsi'])): ?>
                <div class="info-card full-width">
                    <div class="info-card-label">Deskripsi Kegiatan</div>
                    <div class="info-card-value" style="font-weight: 400; line-height: 1.6;"><?php echo nl2br(htmlspecialchars($activity['deskripsi'])); ?></div>
                </div>
                <?php endif; ?>
            </div>

            <hr class="divider">

            <!-- Section A: Hasil -->
            <div class="section">
                <div class="section-header">
                    <div class="section-letter">A</div>
                    <div class="section-title-text">Hasil Kegiatan</div>
                </div>
                <div class="section-content">
                    <?php echo nl2br(htmlspecialchars($lap['hasil_kegiatan'])); ?>
                </div>
            </div>

            <!-- Section B: Kendala -->
            <div class="section">
                <div class="section-header">
                    <div class="section-letter">B</div>
                    <div class="section-title-text">Kendala yang Dihadapi</div>
                </div>
                <div class="section-content <?php echo !empty($lap['kendala']) ? 'warning' : ''; ?>">
                    <?php echo !empty($lap['kendala']) ? nl2br(htmlspecialchars($lap['kendala'])) : '—  Tidak ada kendala yang berarti.'; ?>
                </div>
            </div>

            <!-- Section C: Solusi -->
            <div class="section">
                <div class="section-header">
                    <div class="section-letter">C</div>
                    <div class="section-title-text">Solusi / Tindak Lanjut</div>
                </div>
                <div class="section-content <?php echo !empty($lap['solusi']) ? 'success' : ''; ?>">
                    <?php echo !empty($lap['solusi']) ? nl2br(htmlspecialchars($lap['solusi'])) : '—  Tidak ada tindak lanjut khusus.'; ?>
                </div>
            </div>

            <!-- Section D: Lampiran -->
            <div class="section">
                <div class="section-header">
                    <div class="section-letter">D</div>
                    <div class="section-title-text">Lampiran Bukti</div>
                </div>
                <?php
                $url = $lap['url_bukti'] ?? '';
                $ext = strtolower(pathinfo(parse_url($url, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION));
                if (!empty($url)):
                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])): ?>
                        <img src="<?php echo htmlspecialchars($url); ?>" alt="Lampiran Bukti" class="attachment-img">
                    <?php else: ?>
                        <a href="<?php echo htmlspecialchars($url); ?>" target="_blank" class="attachment-link">
                            📎 <?php echo basename(htmlspecialchars($url)); ?>
                        </a>
                    <?php endif;
                else: ?>
                    <div class="section-content">— Tidak ada lampiran bukti.</div>
                <?php endif; ?>
            </div>

            <hr class="divider">

            <!-- Signature -->
            <div class="signature-area">
                <div class="signature-box">
                    <p>Mengetahui / Menyetujui,<br>Admin Sistem SIPJAD</p>
                    <div class="signature-line">
                        <span>( ................................ )</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Document Footer -->
        <div class="doc-footer">
            <span>SIPJAD — Sistem Informasi Penjadwalan & Pelaporan</span>
            <span>Dicetak pada: <?php echo date('d M Y, H:i'); ?> WIB</span>
        </div>
    </div>
</body>
</html>
