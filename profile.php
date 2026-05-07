<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$user = $db->prepare("SELECT * FROM users WHERE id = ?");
$user->execute([$userId]);
$user = $user->fetch(PDO::FETCH_ASSOC);

// Son açılan kasa
$lastBox = $db->prepare("SELECT opened_at FROM lucky_box_history WHERE user_id = ? ORDER BY opened_at DESC LIMIT 1");
$lastBox->execute([$userId]);
$lastBox = $lastBox->fetch();

// Aktif indirim kodları
$codes = $db->prepare("SELECT d.*, c.name AS category_name FROM discounts d JOIN categories c ON d.category_id = c.id WHERE d.user_id = ? ORDER BY d.created_at DESC");
$codes->execute([$userId]);
$codes = $codes->fetchAll(PDO::FETCH_ASSOC);

$canOpen = true;
$nextDate = "";
if ($lastBox) {
    $lastOpenedTime = strtotime($lastBox['opened_at']);
    $nextAvailableTime = $lastOpenedTime + (7 * 24 * 60 * 60);
    if (time() < $nextAvailableTime) {
        $canOpen = false;
        $nextDate = date('d.m.Y', $nextAvailableTime);
    }
}

// İstatistikler
$activeCount = 0;
foreach($codes as $c) if(!$c['is_used'] && strtotime($c['expires_at']) > time()) $activeCount++;
?>
<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Profilim - Dostum Kafe</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #0c0b0a;
            --card: #1a1612;
            --brand-gold: #c9a076;
            --text: #f8f4ee;
            --muted: #9c8e83;
            --glass: rgba(255, 255, 255, 0.03);
            --border: rgba(255, 255, 255, 0.06);
        }
        body { 
            background: var(--bg); 
            color: var(--text); 
            font-family: 'Outfit', sans-serif; 
            min-height: 100vh; 
            background-image: radial-gradient(circle at 50% -20%, #2a2118 0%, #0c0b0a 100%);
            padding-bottom: 50px;
        }
        .container { max-width: 800px; }
        
        .navbar-custom { padding: 30px 0; }
        .back-link {
            color: var(--muted); text-decoration: none; font-size: 14px; 
            display: flex; align-items: center; gap: 8px; transition: 0.3s;
        }
        .back-link:hover { color: var(--brand-gold); }

        .profile-header {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 24px;
            padding: 40px;
            text-align: center;
            margin-bottom: 30px;
            position: relative;
            overflow: hidden;
        }
        .profile-header::before {
            content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 4px; background: var(--brand-gold);
        }

        .avatar-wrapper {
            width: 100px; height: 100px; margin: 0 auto 20px;
            background: linear-gradient(135deg, var(--brand-gold) 0%, #a67c52 100%);
            border-radius: 30px; transform: rotate(5deg);
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 10px 20px rgba(0,0,0,0.3);
        }
        .avatar-text { font-size: 2.5rem; font-weight: 800; color: #1a1612; transform: rotate(-5deg); }

        .profile-name { font-size: 1.8rem; font-weight: 700; margin-bottom: 5px; }
        .profile-email { color: var(--muted); font-size: 0.95rem; margin-bottom: 20px; }

        .badge-custom {
            padding: 6px 16px; border-radius: 100px; font-size: 12px; font-weight: 600;
            text-transform: uppercase; letter-spacing: 1px; border: 1px solid rgba(255,255,255,0.1);
        }
        .badge-admin { background: rgba(201,160,118,0.15); color: var(--brand-gold); border-color: var(--brand-gold); }
        .badge-member { background: rgba(255,255,255,0.05); color: #fff; }

        .stats-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 30px; }
        .stat-box { background: var(--glass); border: 1px solid var(--border); padding: 20px; border-radius: 16px; text-align: center; }
        .stat-val { font-size: 1.5rem; font-weight: 700; color: #fff; display: block; }
        .stat-lbl { font-size: 12px; color: rgba(255,255,255,0.6); text-transform: uppercase; }

        .btn-action {
            background: var(--brand-gold); color: #000; border: none;
            padding: 14px 40px; border-radius: 16px; font-weight: 700;
            text-decoration: none; display: inline-block; transition: 0.3s;
            margin-top: 25px; box-shadow: 0 4px 15px rgba(201,160,118,0.2);
        }
        .btn-action:hover { background: #fff; transform: translateY(-2px); box-shadow: 0 8px 25px rgba(201,160,118,0.3); }
        .btn-disabled { background: #222; color: #666; cursor: not-allowed; box-shadow: none; }
        .btn-disabled:hover { transform: none; background: #222; }

        .section-title { font-weight: 700; font-size: 1.1rem; margin-bottom: 20px; color: var(--brand-gold); display: flex; align-items: center; gap: 10px; }
        .section-title::after { content: ''; height: 1px; flex: 1; background: var(--border); }

        .voucher-card {
            background: var(--card); border: 1px solid var(--border); border-radius: 18px; padding: 20px; margin-bottom: 15px;
            display: flex; justify-content: space-between; align-items: center; transition: 0.3s;
        }
        .voucher-card:hover { border-color: rgba(201,160,118,0.3); background: #1f1a15; }
        .voucher-info { display: flex; flex-direction: column; }
        .voucher-code { font-family: 'Courier New', monospace; font-weight: 700; font-size: 1.2rem; letter-spacing: 2px; color: var(--brand-gold); }
        .voucher-meta { font-size: 13px; color: rgba(255,255,255,0.6); margin-top: 4px; }
        .voucher-status { font-size: 12px; font-weight: 700; padding: 4px 12px; border-radius: 6px; }
        .status-active { background: rgba(46, 213, 115, 0.1); color: #2ed573; }
        .status-used { background: rgba(255, 71, 87, 0.1); color: #ff4757; opacity: 0.6; }
        .used-item { opacity: 0.6; filter: grayscale(0.5); }

        .logout-link { color: #ff4757; text-decoration: none; font-size: 14px; font-weight: 600; transition: 0.3s; }
        .logout-link:hover { color: #ff6b81; text-decoration: underline; }
        .text-light-muted { color: rgba(255,255,255,0.5) !important; }
    </style>
</head>
<body>

<div class="container">
    <div class="navbar-custom">
        <a href="menu.php" class="back-link">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            Menüye Dön
        </a>
    </div>

    <div class="profile-header">
        <div class="avatar-wrapper">
            <span class="avatar-text"><?php echo mb_substr($user['fullname'], 0, 1); ?></span>
        </div>
        <h1 class="profile-name"><?php echo htmlspecialchars($user['fullname']); ?></h1>
        <p class="profile-email"><?php echo htmlspecialchars($user['email']); ?></p>
        
        <div class="d-flex justify-content-center gap-2 mb-2">
            <?php if ($user['is_admin']): ?>
                <span class="badge-custom badge-admin">Yönetici</span>
            <?php endif; ?>
            <span class="badge-custom badge-member">Onaylı Üye</span>
        </div>

        <div class="stats-grid">
            <div class="stat-box">
                <span class="stat-val"><?php echo count($codes); ?></span>
                <span class="stat-lbl">Toplam Kupon</span>
            </div>
            <div class="stat-box">
                <span class="stat-val"><?php echo $activeCount; ?></span>
                <span class="stat-lbl">Aktif İndirim</span>
            </div>
        </div>

        <?php if ($canOpen): ?>
            <a href="lucky_box.php" class="btn-action">Haftalık Kasayı Aç 🎁</a>
        <?php else: ?>
            <button class="btn-action btn-disabled" disabled>Kasa Hazırlanıyor (<?php echo $nextDate; ?>)</button>
        <?php endif; ?>
    </div>

    <div class="section-title">İndirim Kuponlarım</div>

    <?php if (empty($codes)): ?>
        <div class="text-center py-5" style="background: var(--card); border-radius: 20px; border: 1px dashed var(--border);">
            <p class="text-light-muted mb-0">Henüz hiç kupon kazanmadınız.<br>Kasayı açarak şansınızı deneyebilirsiniz!</p>
        </div>
    <?php else: ?>
        <?php foreach ($codes as $c): 
            $isExpired = strtotime($c['expires_at']) < time();
            $displayStatus = $c['is_used'] ? 'Kullanıldı' : ($isExpired ? 'Süresi Doldu' : 'Aktif');
            $statusClass = ($c['is_used'] || $isExpired) ? 'status-used' : 'status-active';
        ?>
            <div class="voucher-card <?php echo ($c['is_used'] || $isExpired) ? 'used-item' : ''; ?>">
                <div class="voucher-info">
                    <span class="voucher-code"><?php echo $c['code']; ?></span>
                    <span class="voucher-meta">
                        <strong>%<?php echo $c['discount_percent']; ?> İndirim</strong> &bull; 
                        <?php echo htmlspecialchars($c['category_name']); ?> &bull; 
                        Son: <?php echo date('d.m.Y', strtotime($c['expires_at'])); ?>
                    </span>
                </div>
                <div class="voucher-status <?php echo $statusClass; ?>">
                    <?php echo $displayStatus; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <div class="text-center mt-5">
        <a href="logout.php" class="logout-link">Oturumu Kapat</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
