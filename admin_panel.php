<?php
session_start();
require_once 'config.php';

//  Yetki Kontrolü: Giriş yapılmamışsa veya admin değilse at
if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit();
}

$pageTitle = 'Admin Paneli - Dostum Kafe';
?>
<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $pageTitle; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        :root {
            --admin-bg: #f8f5f2;
            --admin-card: #ffffff;
            --admin-sidebar: #1e1814;
            --admin-text: #2d241e;
            --admin-muted: #7d6e66;
            --brand-gold: #c9a076;
        }
        body { 
            background-color: var(--admin-bg); 
            color: var(--admin-text);
            font-family: 'Poppins', sans-serif;
            overflow-x: hidden;
        }
        .admin-sidebar {
            min-height: 100vh;
            background: var(--admin-sidebar);
            padding: 40px 20px;
            position: fixed;
            left: 0;
            top: 0;
            width: 280px;
            z-index: 1050;
            transition: transform 0.3s ease;
            box-shadow: 4px 0 15px rgba(0,0,0,0.1);
        }
        .admin-content { 
            margin-left: 280px;
            padding: 40px; 
            transition: margin 0.3s ease;
        }
        
        /* Mobile Overlay */
        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            z-index: 1040;
            backdrop-filter: blur(3px);
        }

        .nav-link-admin {
            color: #d1c4bc;
            padding: 14px 18px;
            display: flex;
            align-items: center;
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 14px;
            border-radius: 8px;
            margin-bottom: 8px;
        }
        .nav-link-admin:hover, .nav-link-admin.active {
            color: #fff;
            background: var(--brand-gold);
            font-weight: 600;
        }

        .mobile-header {
            display: none;
            background: #fff;
            padding: 15px 20px;
            border-bottom: 1px solid #eee;
            position: sticky;
            top: 0;
            z-index: 1030;
        }

        @media (max-width: 991.98px) {
            .admin-sidebar { transform: translateX(-100%); }
            .admin-sidebar.show { transform: translateX(0); }
            .admin-content { margin-left: 0; padding: 30px 15px; }
            .mobile-header { display: flex; align-items: center; justify-content: space-between; }
            .sidebar-overlay.show { display: block; }
        }

        .stat-card {
            background: var(--admin-card);
            padding: 25px;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.04);
            margin-bottom: 20px;
        }
        .stat-value { font-size: 32px; font-weight: 800; color: var(--brand-gold); }
        .admin-table-card { background: var(--admin-card); border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.04); overflow: hidden; }
    </style>
</head>
<body>

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<div class="mobile-header">
    <button class="btn border-0 p-0" id="sidebarToggle">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
    </button>
    <img src="dostum_images/DOSTUMKAFE_NOBG_logo.png" style="height: 35px;">
    <div style="width: 24px;"></div> <!-- Spacer -->
</div>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-lg-2 admin-sidebar" id="adminSidebar">
            <div class="mb-5 text-center">
                <a href="index.php">
                    <img src="dostum_images/DOSTUMKAFE_NOBG_logo.png" style="width: 100px;">
                </a>
                <div class="mt-3 text-white small fw-bold" style="letter-spacing: 2px;">YÖNETİM PANELİ</div>
            </div>
            <nav>
                <a href="admin_panel.php" class="nav-link-admin active">Dashboard</a>
                <a href="admin_products.php" class="nav-link-admin">Ürün Yönetimi</a>
                <a href="admin_categories.php" class="nav-link-admin">Kategoriler</a>
                <a href="admin_users.php" class="nav-link-admin">Kullanıcılar</a>
                <a href="waiter_panel.php" class="nav-link-admin">Garson Paneli</a>
                <hr style="opacity: 0.1; margin: 20px 0;">
                <a href="index.php" class="nav-link-admin">Siteye Dön</a>
                <hr style="opacity: 0.1; margin: 20px 0;">
                <a href="reset_db.php" class="nav-link-admin text-warning" onclick="return confirm('TÜM indirim kodları ve kutu geçmişi silinecek. Emin misiniz?')">Kuponları ve Kasaları Sıfırla</a>
                <a href="logout.php" class="nav-link-admin text-danger mt-3">Güvenli Çıkış</a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="col-lg-10 admin-content">
            <header class="d-flex justify-content-between align-items-center mb-5">
                <div>
                    <h2 class="fw-bold mb-1">Hoş Geldiniz, <?php echo $_SESSION['fullname']; ?></h2>
                    <p class="text-muted small mb-0">Dostum Kafe işletme verileri burada listelenir.</p>
                </div>
                <div class="text-end">
                    <div class="fw-bold text-brand"><?php echo date('H:i'); ?></div>
                    <div class="text-muted small"><?php echo date('d F Y'); ?></div>
                </div>
            </header>

            <?php if(isset($_GET['reset']) && $_GET['reset'] == 'success'): ?>
                <div class="alert alert-success border-0 shadow-sm mb-4">Sistem başarıyla sıfırlandı! Tüm indirimler ve geçmiş temizlendi.</div>
            <?php endif; ?>
            <?php if(isset($_GET['reset']) && $_GET['reset'] == 'error'): ?>
                <div class="alert alert-danger border-0 shadow-sm mb-4">Sıfırlama sırasında bir hata oluştu.</div>
            <?php endif; ?>

            <?php
            $productCount = $db->query("SELECT COUNT(*) FROM products")->fetchColumn();
            $userCount = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
            $categoryCount = $db->query("SELECT COUNT(*) FROM categories")->fetchColumn();
            
            // Türkçe tarih için
            setlocale(LC_TIME, 'tr_TR.UTF-8', 'tr_TR', 'turkish');
            $dateStr = date('d ') . date('F') . date(' Y');
            ?>
            <div class="row g-4 text-center">
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="stat-label">Toplam Ürün</div>
                        <h3 class="stat-value"><?php echo $productCount; ?></h3>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="stat-label">Kullanıcı Sayısı</div>
                        <h3 class="stat-value"><?php echo $userCount; ?></h3>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="stat-label">Aktif Kategori</div>
                        <h3 class="stat-value"><?php echo $categoryCount; ?></h3>
                    </div>
                </div>
            </div>

            <div class="admin-table-card">
                <div class="admin-table-header">
                    <h5 class="mb-0 fw-bold">Son Kayıt Olan Kullanıcılar</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Ad Soyad</th>
                                <th>E-posta</th>
                                <th>Durum</th>
                                <th>Kayıt Tarihi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $users = $db->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
                            foreach($users as $user):
                            ?>
                            <tr>
                                <td class="text-muted"><?php echo $user['id']; ?></td>
                                <td class="fw-bold"><?php echo htmlspecialchars($user['fullname']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <?php if($user['is_verified']): ?>
                                        <span class="status-badge status-verified">Onaylı</span>
                                    <?php else: ?>
                                        <span class="status-badge status-pending">Bekliyor</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-muted"><?php echo date('d.m.Y', strtotime($user['created_at'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const sidebarToggle = document.getElementById('sidebarToggle');
    const adminSidebar = document.getElementById('adminSidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');

    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', () => {
            adminSidebar.classList.toggle('show');
            sidebarOverlay.classList.toggle('show');
        });
    }

    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', () => {
            adminSidebar.classList.remove('show');
            sidebarOverlay.classList.remove('show');
        });
    }
</script>
</body>
</html>
