<?php
session_start();

// Giris kontrolu
if (!isset($_SESSION['admin_giris'])) {
    header("Location: login.php");
    exit;
}

require '../db.php';

// --- EKLEME ISLEMI ---
if (isset($_POST['ekle'])) {
    $isim = mysqli_real_escape_string($baglanti, $_POST['isim']);
    $ikon = mysqli_real_escape_string($baglanti, $_POST['ikon']);
    $sira = (int) $_POST['sira'];
    $gorsel = NULL;
    
    // Gorsel yukleme 
    if (isset($_FILES['gorsel']) && $_FILES['gorsel']['error'] == 0) {
        if (!file_exists('../uploads')) mkdir('../uploads', 0777, true);
        
        if ($_FILES['gorsel']['size'] > 5242880) {
            $mesaj = "Görsel boyutu çok büyük! (Max 5MB)";
            $mesaj_tip = "danger";
        } else {
            $ext = strtolower(pathinfo($_FILES['gorsel']['name'], PATHINFO_EXTENSION));
            
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                $mesaj = "Sadece resim dosyası! (JPG, PNG, GIF, WEBP)";
                $mesaj_tip = "danger";
            } else {
                $dosya_adi = time() . '_' . basename($_FILES['gorsel']['name']);
                $hedef = '../uploads/' . $dosya_adi;
                
                if (move_uploaded_file($_FILES['gorsel']['tmp_name'], $hedef)) {
                    $gorsel = 'uploads/' . $dosya_adi;
                } else {
                    $mesaj = "Görsel yüklenirken hata oluştu!";
                    $mesaj_tip = "danger";
                }
            }
        }
    }
    
    // Hata yoksa veritabanına eklenir
    if (!isset($mesaj)) {
        $sql = "INSERT INTO kategoriler (isim, ikon, sira, gorsel) VALUES ('$isim', '$ikon', $sira, " . ($gorsel ? "'$gorsel'" : "NULL") . ")";
        
        if (mysqli_query($baglanti, $sql)) {
            $mesaj = "Kategori başarıyla eklendi!";
            $mesaj_tip = "success";
        } else {
            $mesaj = "Hata: " . mysqli_error($baglanti);
            $mesaj_tip = "danger";
        }
    }
}

// --- SILME ISLEMI ---
if (isset($_GET['sil'])) {
    $id = (int) $_GET['sil'];
    
    // Once kategori gorselini bul ve sil
    $sorgu = mysqli_query($baglanti, "SELECT gorsel FROM kategoriler WHERE id = $id");
    $kategori = mysqli_fetch_assoc($sorgu);
    
    if ($kategori && !empty($kategori['gorsel']) && file_exists('../' . $kategori['gorsel'])) {
        unlink('../' . $kategori['gorsel']);
    }
    
    // Kategorideki ürünlerin görselleri diskten silinir
    $urun_sorgu = mysqli_query($baglanti, "SELECT gorsel FROM urunler WHERE kategori_id = $id");
    while ($urun = mysqli_fetch_assoc($urun_sorgu)) {
        if (!empty($urun['gorsel']) && file_exists('../' . $urun['gorsel'])) {
            unlink('../' . $urun['gorsel']);
        }
    }

    // Kategorideki tüm ürünler silinir
    mysqli_query($baglanti, "DELETE FROM urunler WHERE kategori_id = $id");

    // Kategori silinir
    if (mysqli_query($baglanti, "DELETE FROM kategoriler WHERE id = $id")) {
        $mesaj = "Kategori ve içindeki ürünler silindi!";
        $mesaj_tip = "success";
    }
}

// --- GUNCELLEME ISLEMI ---
if (isset($_POST['guncelle'])) {
    $id = (int) $_POST['id'];
    $isim = mysqli_real_escape_string($baglanti, $_POST['isim']);
    $ikon = mysqli_real_escape_string($baglanti, $_POST['ikon']);
    $sira = (int) $_POST['sira'];
    
    // Mevcut gorseli bul
    $eski_sorgu = mysqli_query($baglanti, "SELECT gorsel FROM kategoriler WHERE id = $id");
    $eski = mysqli_fetch_assoc($eski_sorgu);
    $gorsel = $eski['gorsel'];
    
    // Yeni gorsel yuklendi mi kontrolu
    if (isset($_FILES['gorsel']) && $_FILES['gorsel']['error'] == 0) {
        if (!file_exists('../uploads')) mkdir('../uploads', 0777, true);
        
        if ($_FILES['gorsel']['size'] > 5242880) {
            $mesaj = "Görsel boyutu çok büyük! (Max 5MB)";
            $mesaj_tip = "danger";
        } else {
            $ext = strtolower(pathinfo($_FILES['gorsel']['name'], PATHINFO_EXTENSION));
            
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                $mesaj = "Sadece resim dosyası! (JPG, PNG, GIF, WEBP)";
                $mesaj_tip = "danger";
            } else {
                // Eski gorseli sil
                if (!empty($gorsel) && file_exists('../' . $gorsel)) unlink('../' . $gorsel);
                
                $dosya_adi = time() . '_' . basename($_FILES['gorsel']['name']);
                $hedef = '../uploads/' . $dosya_adi;
                
                if (move_uploaded_file($_FILES['gorsel']['tmp_name'], $hedef)) {
                    $gorsel = 'uploads/' . $dosya_adi;
                } else {
                    $mesaj = "Görsel yüklenirken hata oluştu!";
                    $mesaj_tip = "danger";
                }
            }
        }
    }
    
    $sql = "UPDATE kategoriler SET isim = '$isim', ikon = '$ikon', sira = $sira, gorsel = " . ($gorsel ? "'$gorsel'" : "NULL") . " WHERE id = $id";
    
    if (mysqli_query($baglanti, $sql)) {
        $mesaj = "Kategori güncellendi!";
        $mesaj_tip = "success";
    }
}

// Kategorileri listele
// Kategoriler ürün sayısıyla birlikte çekilir
$kategoriler = mysqli_query($baglanti, "SELECT k.*, COUNT(u.id) as urun_sayisi FROM kategoriler k LEFT JOIN urunler u ON u.kategori_id = k.id GROUP BY k.id ORDER BY k.sira ASC");
?>
<!doctype html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kategori Yönetimi | Dostum Kafe</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="admin.css">
</head>
<body class="admin-crud-body">

    <div class="admin-crud-container">
        <!-- Ust kisim - Baslik + Butonlar -->
        <div class="admin-crud-header">
            <h2 class="admin-crud-title">Kategoriler</h2>
            <div class="d-flex gap-2">
                <button class="btn-admin btn-admin-success" data-bs-toggle="modal" data-bs-target="#ekleModal">+ Yeni Ekle</button>
                <a href="panel.php" class="btn-admin btn-admin-white">← Geri</a>
            </div>
        </div>

        <?php if (isset($mesaj)) : ?>
            <div class="alert alert-<?php echo $mesaj_tip; ?> alert-dismissible fade show">
                <?php echo $mesaj; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Tablo -->
        <div class="admin-table-container">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Görsel</th>
                        <th>İsim</th>
                        <th>İkon</th>
                        <th>Sıra</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($kat = mysqli_fetch_assoc($kategoriler)) : ?>
                        <tr>
                            <td>
                                <?php if (!empty($kat['gorsel']) && file_exists('../' . $kat['gorsel'])) : ?>
                                    <img src="../<?php echo $kat['gorsel']; ?>" class="admin-table-img">
                                <?php else : ?>
                                    <span style="font-size: 2rem;"><?php echo $kat['ikon']; ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="fw-bold"><?php echo $kat['isim']; ?></td>
                            <td><?php echo $kat['ikon']; ?></td>
                            <td><?php echo $kat['sira']; ?></td>
                            <td>
                                <button class="btn-admin btn-admin-primary btn-sm" onclick="duzenle(<?php echo htmlspecialchars(json_encode($kat)); ?>)">Düzenle</button>
                                <a href="?sil=<?php echo $kat['id']; ?>" class="btn-admin btn-admin-danger btn-sm" onclick="return silOnay(<?php echo $kat['urun_sayisi']; ?>)">Sil</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- EKLEME MODAL -->
    <div class="modal fade" id="ekleModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content admin-modal">
                <div class="modal-header">
                    <h5 class="modal-title">Yeni Kategori Ekle</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Kategori İsmi</label>
                            <input type="text" name="isim" class="form-control admin-input" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">İkon <small class="text-muted">(opsiyonel)</small></label>
                            <div class="d-flex gap-2 align-items-center">
                                <input type="text" name="ikon" id="ekle_ikon" class="form-control admin-input" placeholder="Boş bırakılabilir">
                                <button type="button" class="btn-admin btn-admin-white btn-sm text-nowrap" onclick="emojiToggle('ekle_emoji_grid', this)">😊 Seç</button>
                            </div>
                            <div id="ekle_emoji_grid" class="emoji-grid mt-2" style="display:none;">
                                <?php foreach(['☕','🍵','🧃','🥤','🍺','🍷','🥛','🍕','🍔','🌮','🌯','🥗','🍜','🍣','🥩','🍰','🎂','🍩','🍪','🍫'] as $e): ?>
                                    <span class="emoji-btn" onclick="emojiSec('ekle_ikon', '<?php echo $e; ?>', 'ekle_emoji_grid')"><?php echo $e; ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Sıra</label>
                            <input type="number" name="sira" class="form-control admin-input" value="0">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Görsel</label>
                            <input type="file" name="gorsel" class="form-control admin-input" accept="image/*" onchange="onizle(this, 'ekle_onizleme')">
                            <img id="ekle_onizleme" class="gorsel-onizleme" alt="Önizleme">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn-admin btn-admin-white" data-bs-dismiss="modal">İptal</button>
                        <button type="submit" name="ekle" class="btn-admin btn-admin-success">Ekle</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- DUZENLEME MODAL -->
    <div class="modal fade" id="duzenleModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content admin-modal">
                <div class="modal-header">
                    <h5 class="modal-title">Kategoriyi Düzenle</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Kategori İsmi</label>
                            <input type="text" name="isim" id="edit_isim" class="form-control admin-input" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">İkon <small class="text-muted">(opsiyonel)</small></label>
                            <div class="d-flex gap-2 align-items-center">
                                <input type="text" name="ikon" id="edit_ikon" class="form-control admin-input" placeholder="Boş bırakılabilir">
                                <button type="button" class="btn-admin btn-admin-white btn-sm text-nowrap" onclick="emojiToggle('edit_emoji_grid', this)">😊 Seç</button>
                            </div>
                            <div id="edit_emoji_grid" class="emoji-grid mt-2" style="display:none;">
                                <?php foreach(['☕','🍵','🧃','🥤','🍺','🍷','🥛','🍕','🍔','🌮','🌯','🥗','🍜','🍣','🥩','🍰','🎂','🍩','🍪','🍫'] as $e): ?>
                                    <span class="emoji-btn" onclick="emojiSec('edit_ikon', '<?php echo $e; ?>', 'edit_emoji_grid')"><?php echo $e; ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Sıra</label>
                            <input type="number" name="sira" id="edit_sira" class="form-control admin-input">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Yeni Görsel (opsiyonel)</label>
                            <input type="file" name="gorsel" class="form-control admin-input" accept="image/*" onchange="onizle(this, 'edit_onizleme')">
                            <img id="edit_onizleme" class="gorsel-onizleme" alt="Önizleme">
                            <small class="text-muted">Boş bırakırsanız mevcut görsel korunur</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn-admin btn-admin-white" data-bs-dismiss="modal">İptal</button>
                        <button type="submit" name="guncelle" class="btn-admin btn-admin-primary">Güncelle</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function duzenle(kat) {
            document.getElementById('edit_id').value = kat.id;
            document.getElementById('edit_isim').value = kat.isim;
            document.getElementById('edit_ikon').value = kat.ikon;
            document.getElementById('edit_sira').value = kat.sira;
            // Düzenleme modalı açılırken önizleme ve emoji grid sıfırlanır
            document.getElementById('edit_onizleme').style.display = 'none';
            document.getElementById('edit_emoji_grid').style.display = 'none';
            new bootstrap.Modal(document.getElementById('duzenleModal')).show();
        }

        // Emoji gridini açıp kapatır
        function emojiToggle(gridId, btn) {
            const grid = document.getElementById(gridId);
            const acik = grid.style.display !== 'none';
            grid.style.display = acik ? 'none' : 'flex';
            btn.textContent = acik ? '😊 Seç' : '✕ Kapat';
        }

        // Emoji seçilince input'a yazar ve grid kapanır
        function emojiSec(inputId, emoji, gridId) {
            document.getElementById(inputId).value = emoji;
            document.getElementById(gridId).style.display = 'none';
            // Toggle butonunu sıfırla
            const btn = document.querySelector('[onclick*="' + gridId + '"]');
            if (btn) btn.textContent = '😊 Seç';
        }

        // İçinde ürün varsa uyarılı silme onayı
        function silOnay(urunSayisi) {
            if (urunSayisi > 0) {
                return confirm('Bu kategoride ' + urunSayisi + ' ürün var. Kategoriyle birlikte tüm ürünler de silinecek. Devam edilsin mi?');
            }
            return confirm('Bu kategoriyi silmek istiyor musunuz?');
        }

        // Seçilen görselin önizlemesini gösterir
        function onizle(input, hedefId) {
            const img = document.getElementById(hedefId);
            if (input.files && input.files[0]) {
                img.src = URL.createObjectURL(input.files[0]);
                img.style.display = 'block';
            }
        }
    </script>
</body>
</html>
<?php mysqli_close($baglanti); ?>
