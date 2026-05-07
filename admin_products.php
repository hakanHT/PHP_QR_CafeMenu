<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit();
}

$pageTitle = 'Ürün Yönetimi - Dostum Kafe';

function resizeImage($source, $dest, $width) {
    $info = getimagesize($source);
    $type = $info[2];
    if ($type == IMAGETYPE_JPEG)      $img = imagecreatefromjpeg($source);
    elseif ($type == IMAGETYPE_PNG)   $img = imagecreatefrompng($source);
    elseif ($type == IMAGETYPE_GIF)   $img = imagecreatefromgif($source);
    else return false;

    $w = imagesx($img);
    $h = imagesy($img);
    $height = (int)floor($h * ($width / $w));
    $tmp = imagecreatetruecolor($width, $height);

    if ($type == IMAGETYPE_PNG || $type == IMAGETYPE_GIF) {
        imagealphablending($tmp, false);
        imagesavealpha($tmp, true);
    }
    imagecopyresampled($tmp, $img, 0, 0, 0, 0, $width, $height, $w, $h);

    if ($type == IMAGETYPE_JPEG)    imagejpeg($tmp, $dest, 85);
    elseif ($type == IMAGETYPE_PNG) imagepng($tmp, $dest, 8);
    elseif ($type == IMAGETYPE_GIF) imagegif($tmp, $dest);
    return true;
}

// Ürün Ekleme
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_product'])) {
    $name        = trim($_POST['name']);
    $category_id = (int)$_POST['category_id'];
    $description = trim($_POST['description']);
    $price       = (float)$_POST['price'];
    $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

    if (!empty($name) && $price > 0 && isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $mimeType = mime_content_type($_FILES['image']['tmp_name']);
        if (!in_array($mimeType, $allowedMimes)) {
            $error = "Geçersiz dosya türü. Sadece JPG, PNG, GIF ve WEBP kabul edilir.";
        } else {
            $uploadDir  = 'uploads/products/';
            $fileName   = time() . '_' . basename($_FILES['image']['name']);
            $targetPath = $uploadDir . $fileName;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                resizeImage($targetPath, $targetPath, 800);
                $db->prepare("INSERT INTO products (category_id, name, description, price, image_path) VALUES (?, ?, ?, ?, ?)")
                   ->execute([$category_id, $name, $description, $price, $targetPath]);
                $success = "Ürün eklendi.";
            } else {
                $error = "Resim yüklenemedi. Sunucu yazma iznini kontrol edin.";
            }
        }
    } else {
        $error = "Tüm alanları doldurun ve bir resim seçin.";
    }
}

// Ürün Güncelleme
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_product'])) {
    $id          = (int)$_POST['edit_id'];
    $name        = trim($_POST['edit_name']);
    $category_id = (int)$_POST['edit_category_id'];
    $description = trim($_POST['edit_description']);
    $price       = (float)$_POST['edit_price'];
    $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

    if (!empty($name) && $price > 0 && $id > 0) {
        if (isset($_FILES['edit_image']) && $_FILES['edit_image']['error'] == 0) {
            $mimeType = mime_content_type($_FILES['edit_image']['tmp_name']);
            if (!in_array($mimeType, $allowedMimes)) {
                $error = "Geçersiz dosya türü. Sadece JPG, PNG, GIF ve WEBP kabul edilir.";
            } else {
                $old = $db->prepare("SELECT image_path FROM products WHERE id = ?");
                $old->execute([$id]);
                $old = $old->fetchColumn();
                if ($old && file_exists($old)) unlink($old);

                $uploadDir  = 'uploads/products/';
                $fileName   = time() . '_' . basename($_FILES['edit_image']['name']);
                $targetPath = $uploadDir . $fileName;
                move_uploaded_file($_FILES['edit_image']['tmp_name'], $targetPath);
                resizeImage($targetPath, $targetPath, 800);

                $db->prepare("UPDATE products SET category_id=?, name=?, description=?, price=?, image_path=? WHERE id=?")
                   ->execute([$category_id, $name, $description, $price, $targetPath, $id]);
                $success = "Ürün güncellendi.";
            }
        } else {
            $db->prepare("UPDATE products SET category_id=?, name=?, description=?, price=? WHERE id=?")
               ->execute([$category_id, $name, $description, $price, $id]);
            $success = "Ürün güncellendi.";
        }
    }
}

// Ürün Silme
if (isset($_GET['delete'])) {
    $id   = (int)$_GET['delete'];
    $stmt = $db->prepare("SELECT image_path FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $path = $stmt->fetchColumn();
    if ($path && file_exists($path)) unlink($path);
    $db->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);
    $success = "Ürün silindi.";
}

$categories = $db->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
$products   = $db->query("SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC")->fetchAll(PDO::FETCH_ASSOC);
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
        :root { --admin-bg:#f8f5f2; --admin-card:#fff; --admin-sidebar:#1e1814; --admin-text:#2d241e; --brand-gold:#c9a076; }
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
        .product-thumb { width:50px; height:50px; object-fit:cover; border-radius:8px; background:#f0ebe6; }
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
            <a href="admin_products.php" class="nav-link-admin active">Ürün Yönetimi</a>
            <a href="admin_categories.php" class="nav-link-admin">Kategoriler</a>
            <a href="admin_users.php" class="nav-link-admin">Kullanıcılar</a>
            <a href="waiter_panel.php" class="nav-link-admin">Garson Paneli</a>
            <hr style="opacity:.1;margin:20px 0;">
            <a href="index.php" class="nav-link-admin">Siteye Dön</a>
            <a href="logout.php" class="nav-link-admin text-danger mt-4">Güvenli Çıkış</a>
        </nav>
    </div>

    <div class="col-lg-10 admin-content">
        <h2 class="fw-bold mb-4">Ürün Yönetimi</h2>

        <?php if (isset($success)): ?><div class="alert alert-success border-0 shadow-sm mb-4"><?php echo $success; ?></div><?php endif; ?>
        <?php if (isset($error)):   ?><div class="alert alert-danger border-0 shadow-sm mb-4"><?php echo $error; ?></div><?php endif; ?>

        <div class="row g-4">
            <!-- Ekleme Formu -->
            <div class="col-md-5 col-lg-4">
                <div class="admin-table-card p-4">
                    <h5 class="fw-bold mb-3">Yeni Ürün Ekle</h5>
                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Ürün Adı *</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Kategori *</label>
                            <select name="category_id" class="form-select" required>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Fiyat (TL) *</label>
                            <input type="number" step="0.01" min="0" name="price" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Açıklama *</label>
                            <textarea name="description" class="form-control" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Ürün Resmi *</label>
                            <input type="file" name="image" class="form-control" accept="image/*" required>
                        </div>
                        <button type="submit" name="add_product" class="btn btn-brand w-100">Ürünü Kaydet</button>
                    </form>
                </div>
            </div>

            <!-- Ürün Listesi -->
            <div class="col-md-7 col-lg-8">
                <div class="admin-table-card">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Ürün</th>
                                    <th>Fiyat</th>
                                    <th class="text-end">İşlem</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($products as $p): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <?php if (!empty($p['image_path'])): ?>
                                                <img src="<?php echo $p['image_path']; ?>" class="product-thumb">
                                            <?php else: ?>
                                                <div class="product-thumb"></div>
                                            <?php endif; ?>
                                            <div>
                                                <div class="fw-bold"><?php echo htmlspecialchars($p['name']); ?></div>
                                                <div class="text-muted small"><?php echo htmlspecialchars($p['category_name']); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="fw-bold"><?php echo number_format($p['price'], 2); ?> TL</td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-outline-secondary me-1"
                                            data-bs-toggle="modal" data-bs-target="#editProductModal"
                                            data-id="<?php echo $p['id']; ?>"
                                            data-name="<?php echo htmlspecialchars($p['name'], ENT_QUOTES); ?>"
                                            data-cat="<?php echo $p['category_id']; ?>"
                                            data-price="<?php echo $p['price']; ?>"
                                            data-desc="<?php echo htmlspecialchars($p['description'], ENT_QUOTES); ?>">
                                            Düzenle
                                        </button>
                                        <a href="?delete=<?php echo $p['id']; ?>" class="btn btn-sm btn-outline-danger"
                                            onclick="return confirm('Bu ürünü silmek istediğinize emin misiniz?')">Sil</a>
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
<div class="modal fade" id="editProductModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">Ürün Düzenle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="edit_id" id="eId">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Ürün Adı *</label>
                        <input type="text" name="edit_name" id="eName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Kategori *</label>
                        <select name="edit_category_id" id="eCat" class="form-select" required>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Fiyat (TL) *</label>
                        <input type="number" step="0.01" min="0" name="edit_price" id="ePrice" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Açıklama *</label>
                        <textarea name="edit_description" id="eDesc" class="form-control" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Yeni Resim (isteğe bağlı)</label>
                        <input type="file" name="edit_image" class="form-control" accept="image/*">
                        <div class="form-text">Boş bırakılırsa mevcut resim korunur.</div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Vazgeç</button>
                    <button type="submit" name="edit_product" class="btn btn-brand">Kaydet</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById('editProductModal').addEventListener('show.bs.modal', function(e) {
        const b = e.relatedTarget;
        document.getElementById('eId').value    = b.dataset.id;
        document.getElementById('eName').value  = b.dataset.name;
        document.getElementById('ePrice').value = b.dataset.price;
        document.getElementById('eDesc').value  = b.dataset.desc;
        const sel = document.getElementById('eCat');
        for (let o of sel.options) o.selected = (o.value == b.dataset.cat);
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
