<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit();
}

$pageTitle = 'Kategori Yönetimi - Dostum Kafe';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_category'])) {
    $name = trim($_POST['category_name']);
    if (!empty($name)) {
        $db->prepare("INSERT INTO categories (name) VALUES (?)")->execute([$name]);
        $success = "Kategori eklendi.";
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_category'])) {
    $id   = (int)$_POST['edit_id'];
    $name = trim($_POST['edit_name']);
    if (!empty($name) && $id > 0) {
        $db->prepare("UPDATE categories SET name = ? WHERE id = ?")->execute([$name, $id]);
        $success = "Kategori güncellendi.";
    }
}

if (isset($_GET['delete'])) {
    $id    = (int)$_GET['delete'];
    $check = $db->prepare("SELECT id FROM products WHERE category_id = ?");
    $check->execute([$id]);
    if ($check->rowCount() > 0) {
        $error = "Bu kategoriye bağlı ürünler var. Önce ürünleri silin.";
    } else {
        $db->prepare("DELETE FROM categories WHERE id = ?")->execute([$id]);
        $success = "Kategori silindi.";
    }
}

$categories = $db->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $pageTitle; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --admin-bg:#f8f5f2; --admin-card:#fff; --admin-sidebar:#1e1814; --admin-text:#2d241e; --admin-muted:#7d6e66; --brand-gold:#c9a076; }
        body { background:var(--admin-bg); color:var(--admin-text); font-family:'Poppins',sans-serif; overflow-x:hidden; }
        .admin-sidebar { min-height:100vh; background:var(--admin-sidebar); padding:40px 20px; position:fixed; left:0; top:0; width:280px; z-index:1050; transition:transform .3s; box-shadow:4px 0 15px rgba(0,0,0,.1); }
        .admin-content { margin-left:280px; padding:40px; transition:margin .3s; }
        .sidebar-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:1040; backdrop-filter:blur(3px); }
        .nav-link-admin { color:#d1c4bc; padding:14px 18px; display:flex; align-items:center; text-decoration:none; transition:.3s; font-size:14px; border-radius:8px; margin-bottom:8px; }
        .nav-link-admin:hover, .nav-link-admin.active { color:#fff; background:var(--brand-gold); font-weight:600; }
        .mobile-header { display:none; background:#fff; padding:15px 20px; border-bottom:1px solid #eee; position:sticky; top:0; z-index:1030; }
        @media (max-width:991.98px) {
            .admin-sidebar { transform:translateX(-100%); }
            .admin-sidebar.show { transform:translateX(0); }
            .admin-content { margin-left:0; padding:30px 15px; }
            .mobile-header { display:flex; align-items:center; justify-content:space-between; }
            .sidebar-overlay.show { display:block; }
        }
        .admin-table-card { background:var(--admin-card); border-radius:16px; box-shadow:0 4px 20px rgba(0,0,0,.04); overflow:hidden; }
        .btn-brand { background:var(--brand-gold); color:#fff; border:none; padding:10px 25px; border-radius:8px; font-weight:600; }
        .btn-brand:hover { background:#b08b63; color:#fff; }
    </style>
</head>
<body>

<div class="sidebar-overlay" id="sidebarOverlay"></div>
<div class="mobile-header">
    <button class="btn border-0 p-0" id="sidebarToggle">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
    </button>
    <img src="dostum_images/DOSTUMKAFE_NOBG_logo.png" style="height:35px;">
    <div style="width:24px;"></div>
</div>

<div class="container-fluid">
<div class="row">
    <div class="col-lg-2 admin-sidebar" id="adminSidebar">
        <div class="mb-5 text-center">
            <a href="index.php"><img src="dostum_images/DOSTUMKAFE_NOBG_logo.png" style="width:100px;"></a>
            <div class="mt-3 text-white small fw-bold" style="letter-spacing:2px;">YÖNETİM PANELİ</div>
        </div>
        <nav>
            <a href="admin_panel.php" class="nav-link-admin">Dashboard</a>
            <a href="admin_products.php" class="nav-link-admin">Ürün Yönetimi</a>
            <a href="admin_categories.php" class="nav-link-admin active">Kategoriler</a>
            <a href="admin_users.php" class="nav-link-admin">Kullanıcılar</a>
            <a href="waiter_panel.php" class="nav-link-admin">Garson Paneli</a>
            <hr style="opacity:.1;margin:20px 0;">
            <a href="index.php" class="nav-link-admin">Siteye Dön</a>
            <a href="logout.php" class="nav-link-admin text-danger mt-4">Güvenli Çıkış</a>
        </nav>
    </div>

    <div class="col-lg-10 admin-content">
        <h2 class="fw-bold mb-4">Kategori Yönetimi</h2>

        <?php if (isset($success)): ?><div class="alert alert-success border-0 shadow-sm mb-4"><?php echo $success; ?></div><?php endif; ?>
        <?php if (isset($error)): ?><div class="alert alert-danger border-0 shadow-sm mb-4"><?php echo $error; ?></div><?php endif; ?>

        <div class="row g-4">
            <div class="col-md-5 col-lg-4">
                <div class="admin-table-card p-4">
                    <h5 class="fw-bold mb-3">Yeni Kategori Ekle</h5>
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Kategori Adı</label>
                            <input type="text" name="category_name" class="form-control" required>
                        </div>
                        <button type="submit" name="add_category" class="btn btn-brand w-100">Ekle</button>
                    </form>
                </div>
            </div>

            <div class="col-md-7 col-lg-8">
                <div class="admin-table-card">
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Kategori Adı</th>
                                    <th class="text-end">İşlem</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($categories as $cat): ?>
                                <tr>
                                    <td class="text-muted"><?php echo $cat['id']; ?></td>
                                    <td class="fw-bold"><?php echo htmlspecialchars($cat['name']); ?></td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-outline-secondary me-1"
                                            data-bs-toggle="modal" data-bs-target="#editModal"
                                            data-id="<?php echo $cat['id']; ?>"
                                            data-name="<?php echo htmlspecialchars($cat['name'], ENT_QUOTES); ?>">
                                            Düzenle
                                        </button>
                                        <a href="?delete=<?php echo $cat['id']; ?>" class="btn btn-sm btn-outline-danger"
                                            onclick="return confirm('Bu kategoriyi silmek istediğinize emin misiniz?')">Sil</a>
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
</div>
</div>

<!-- Düzenleme Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">Kategori Düzenle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="edit_id" id="editId">
                    <label class="form-label small fw-bold">Yeni Kategori Adı</label>
                    <input type="text" name="edit_name" id="editName" class="form-control" required>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Vazgeç</button>
                    <button type="submit" name="edit_category" class="btn btn-brand">Kaydet</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById('editModal').addEventListener('show.bs.modal', function(e) {
        document.getElementById('editId').value   = e.relatedTarget.dataset.id;
        document.getElementById('editName').value = e.relatedTarget.dataset.name;
    });

    const toggle  = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('adminSidebar');
    const overlay = document.getElementById('sidebarOverlay');
    if (toggle) {
        toggle.addEventListener('click',  () => { sidebar.classList.toggle('show'); overlay.classList.toggle('show'); });
        overlay.addEventListener('click', () => { sidebar.classList.remove('show'); overlay.classList.remove('show'); });
    }
</script>
</body>
</html>
