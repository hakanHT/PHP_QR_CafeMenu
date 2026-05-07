<?php
session_start();
require_once 'config.php';

// Admin kontrolü
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit();
}

$pageTitle = 'Kullanıcı Yönetimi - Dostum Kafe';

// Kullanıcı Silme
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // Kendini silmeyi engelle
    if ($id == $_SESSION['user_id']) {
        $error = "Hata: Kendi hesabınızı silemezsiniz.";
    } else {
        $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $success = "Kullanıcı silindi.";
    }
}

// Admin Yetkisi Ver/Al
if (isset($_GET['toggle_admin'])) {
    $id = $_GET['toggle_admin'];
    if ($id != $_SESSION['user_id']) {
        $stmt = $db->prepare("UPDATE users SET is_admin = 1 - is_admin WHERE id = ?");
        $stmt->execute([$id]);
        $success = "Kullanıcı yetkisi güncellendi.";
    }
}

$users = $db->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $pageTitle; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" rel="stylesheet">
    <style>
        :root {
            --admin-bg: #f8f5f2;
            --admin-card: #ffffff;
            --admin-sidebar: #1e1814;
            --admin-text: #2d241e;
            --brand-gold: #c9a076;
        }
        body { background-color: var(--admin-bg); color: var(--admin-text); font-family: 'Poppins', sans-serif; overflow-x: hidden; }
        .admin-sidebar { min-height: 100vh; background: var(--admin-sidebar); padding: 40px 20px; position: fixed; left: 0; top: 0; width: 280px; z-index: 1050; transition: transform 0.3s ease; box-shadow: 4px 0 15px rgba(0,0,0,0.1); }
        .admin-content { margin-left: 280px; padding: 40px; transition: margin 0.3s ease; }
        .sidebar-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1040; backdrop-filter: blur(3px); }
        .nav-link-admin { color: #d1c4bc; padding: 14px 18px; display: flex; align-items: center; text-decoration: none; transition: 0.3s; font-size: 14px; border-radius: 8px; margin-bottom: 8px; }
        .nav-link-admin:hover, .nav-link-admin.active { color: #fff; background: var(--brand-gold); font-weight: 600; }
        .mobile-header { display: none; background: #fff; padding: 15px 20px; border-bottom: 1px solid #eee; position: sticky; top: 0; z-index: 1030; }
        @media (max-width: 991.98px) {
            .admin-sidebar { transform: translateX(-100%); }
            .admin-sidebar.show { transform: translateX(0); }
            .admin-content { margin-left: 0; padding: 30px 15px; }
            .mobile-header { display: flex; align-items: center; justify-content: space-between; }
            .sidebar-overlay.show { display: block; }
        }
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
    <div style="width: 24px;"></div>
</div>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-lg-2 admin-sidebar" id="adminSidebar">
            <div class="mb-5 text-center">
                <a href="index.php"><img src="dostum_images/DOSTUMKAFE_NOBG_logo.png" style="width: 100px;"></a>
                <div class="mt-3 text-white small fw-bold" style="letter-spacing: 2px;">YÖNETİM PANELİ</div>
            </div>
            <nav>
                <a href="admin_panel.php" class="nav-link-admin">Dashboard</a>
                <a href="admin_products.php" class="nav-link-admin">Ürün Yönetimi</a>
                <a href="admin_categories.php" class="nav-link-admin">Kategoriler</a>
                <a href="admin_users.php" class="nav-link-admin active">Kullanıcılar</a>
                <a href="waiter_panel.php" class="nav-link-admin">Garson Paneli</a>
                <hr style="opacity: 0.1; margin: 20px 0;">
                <a href="index.php" class="nav-link-admin">Siteye Dön</a>
                <a href="logout.php" class="nav-link-admin text-danger mt-4">Güvenli Çıkış</a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="col-lg-10 admin-content">
            <h2 class="fw-bold mb-4 text-center text-lg-start">Kullanıcı Yönetimi</h2>

            <?php if(isset($success)): ?>
                <div class="alert alert-success border-0 shadow-sm mb-4"><?php echo $success; ?></div>
            <?php endif; ?>
            <?php if(isset($error)): ?>
                <div class="alert alert-danger border-0 shadow-sm mb-4"><?php echo $error; ?></div>
            <?php endif; ?>

            <div class="admin-table-card">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Ad Soyad / E-posta</th>
                                <th>Yetki</th>
                                <th>Durum</th>
                                <th class="text-end">İşlem</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($users as $user): ?>
                            <tr>
                                <td>
                                    <div class="fw-bold"><?php echo htmlspecialchars($user['fullname']); ?></div>
                                    <div class="text-muted small"><?php echo htmlspecialchars($user['email']); ?></div>
                                </td>
                                <td>
                                    <?php if($user['is_admin']): ?>
                                        <span class="badge bg-dark">Admin</span>
                                    <?php else: ?>
                                        <span class="badge bg-light text-dark border">Müşteri</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($user['is_verified']): ?>
                                        <span class="text-success small fw-bold">✓ Onaylı</span>
                                    <?php else: ?>
                                        <span class="text-warning small fw-bold">⚠ Bekliyor</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <?php if($user['id'] != $_SESSION['user_id']): ?>
                                        <a href="?toggle_admin=<?php echo $user['id']; ?>" class="btn btn-sm btn-outline-dark me-1">Yetki Değiştir</a>
                                        <a href="?delete=<?php echo $user['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Silmek istediğine emin misin?')">Sil</a>
                                    <?php else: ?>
                                        <span class="text-muted small italic">Siz</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

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
