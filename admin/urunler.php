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
    $kategori_id = (int) $_POST['kategori_id'];
    $isim = mysqli_real_escape_string($baglanti, $_POST['isim']);
    $aciklama = mysqli_real_escape_string($baglanti, $_POST['aciklama']);
    $fiyat = (float) $_POST['fiyat'];
    $aktif = isset($_POST['aktif']) ? 1 : 0;
    $gorsel = NULL;
    
    // Fiyat kontrolu
    if ($fiyat < 0) {
        $mesaj = "Fiyat negatif olamaz!";
        $mesaj_tip = "danger";
    } elseif ($fiyat > 9999.99) {
        $mesaj = "Fiyat çok yüksek! (Max 9999.99₺)";
        $mesaj_tip = "danger";
    } elseif (!is_numeric($_POST['fiyat'])) {
        $mesaj = "Fiyat sadece sayı olabilir!";
        $mesaj_tip = "danger";
    } else {
    
        // Gorsel yukleme 
        if (isset($_FILES['gorsel']) && $_FILES['gorsel']['error'] == 0) {
            if (!file_exists('../uploads')) {
                mkdir('../uploads', 0777, true);
            }
            
            if ($_FILES['gorsel']['size'] > 5242880) {
                $mesaj = "Görsel boyutu çok büyük! (Max 5MB)";
                $mesaj_tip = "danger";
            } else {
                $dosya_uzanti = strtolower(pathinfo($_FILES['gorsel']['name'], PATHINFO_EXTENSION));
                $izin_verilen = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                
                if (!in_array($dosya_uzanti, $izin_verilen)) {
                    $mesaj = "Sadece resim dosyası yükleyebilirsiniz! (JPG, PNG, GIF, WEBP)";
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
        
        // Hata yoksa veritabanina ekle
        if (!isset($mesaj)) {
            $sql = "INSERT INTO urunler (kategori_id, isim, aciklama, fiyat, gorsel, aktif) VALUES ($kategori_id, '$isim', '$aciklama', $fiyat, " . ($gorsel ? "'$gorsel'" : "NULL") . ", $aktif)";
            
            if (mysqli_query($baglanti, $sql)) {
                $mesaj = "Ürün başarıyla eklendi!";
                $mesaj_tip = "success";
            } else {
                $mesaj = "Hata: " . mysqli_error($baglanti);
                $mesaj_tip = "danger";
            }
        }
    } // Fiyat kontrolu kapanisi
}

// --- SILME ISLEMI ---
if (isset($_GET['sil'])) {
    $id = (int) $_GET['sil'];
    
    // Once urun gorselini bul ve sil
    $sorgu = mysqli_query($baglanti, "SELECT gorsel FROM urunler WHERE id = $id");
    $urun = mysqli_fetch_assoc($sorgu);
    
    if ($urun && !empty($urun['gorsel']) && file_exists('../' . $urun['gorsel'])) {
        unlink('../' . $urun['gorsel']);
    }
    
    // Urunu sil
    if (mysqli_query($baglanti, "DELETE FROM urunler WHERE id = $id")) {
        $mesaj = "Ürün silindi!";
        $mesaj_tip = "success";
    }
}

// --- GUNCELLEME ISLEMI ---
if (isset($_POST['guncelle'])) {
    $id = (int) $_POST['id'];
    $kategori_id = (int) $_POST['kategori_id'];
    $isim = mysqli_real_escape_string($baglanti, $_POST['isim']);
    $aciklama = mysqli_real_escape_string($baglanti, $_POST['aciklama']);
    $fiyat = (float) $_POST['fiyat'];
    $aktif = isset($_POST['aktif']) ? 1 : 0;
    
    // Fiyat kontrolu
    if ($fiyat < 0) {
        $mesaj = "Fiyat negatif olamaz!";
        $mesaj_tip = "danger";
    } elseif ($fiyat > 9999.99) {
        $mesaj = "Fiyat çok yüksek! (Max 9999.99₺)";
        $mesaj_tip = "danger";
    } elseif (!is_numeric($_POST['fiyat'])) {
        $mesaj = "Fiyat sadece sayı olabilir!";
        $mesaj_tip = "danger";
    } else {
    
        // Mevcut gorseli bul
        $eski_sorgu = mysqli_query($baglanti, "SELECT gorsel FROM urunler WHERE id = $id");
        $eski = mysqli_fetch_assoc($eski_sorgu);
        $gorsel = $eski['gorsel'];
        
        // Yeni gorsel yuklendi mi?
        if (isset($_FILES['gorsel']) && $_FILES['gorsel']['error'] == 0) {
            if (!file_exists('../uploads')) {
                mkdir('../uploads', 0777, true);
            }
            
            if ($_FILES['gorsel']['size'] > 5242880) {
                $mesaj = "Görsel boyutu çok büyük! (Max 5MB)";
                $mesaj_tip = "danger";
            } else {
                $dosya_uzanti = strtolower(pathinfo($_FILES['gorsel']['name'], PATHINFO_EXTENSION));
                $izin_verilen = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                
                if (!in_array($dosya_uzanti, $izin_verilen)) {
                    $mesaj = "Sadece resim dosyası yükleyebilirsiniz! (JPG, PNG, GIF, WEBP)";
                    $mesaj_tip = "danger";
                } else {
                    // Eski gorseli sil
                    if (!empty($gorsel) && file_exists('../' . $gorsel)) {
                        unlink('../' . $gorsel);
                    }
                    
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
        
        // Hata yoksa guncelle
        if (!isset($mesaj)) {
            $sql = "UPDATE urunler SET kategori_id = $kategori_id, isim = '$isim', aciklama = '$aciklama', fiyat = $fiyat, gorsel = " . ($gorsel ? "'$gorsel'" : "NULL") . ", aktif = $aktif WHERE id = $id";
            
            if (mysqli_query($baglanti, $sql)) {
                $mesaj = "Ürün güncellendi!";
                $mesaj_tip = "success";
            }
        }
    } // Fiyat kontrolu kapanisi
}

// --- AKTIF/PASIF TOGGLE ---
if (isset($_GET['toggle'])) {
    $toggle_id = (int) $_GET['toggle'];
    mysqli_query($baglanti, "UPDATE urunler SET aktif = 1 - aktif WHERE id = $toggle_id");
}

// Urunleri kategorileriyle beraber listele
$urunler = mysqli_query($baglanti, "SELECT u.*, k.isim as kategori_adi FROM urunler u LEFT JOIN kategoriler k ON u.kategori_id = k.id ORDER BY u.id DESC");

// Kategorileri cek 
$kategoriler = mysqli_query($baglanti, "SELECT * FROM kategoriler ORDER BY sira ASC");
?>
<!doctype html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ürün Yönetimi | Dostum Kafe</title>
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
            <h2 class="admin-crud-title">Ürünler</h2>
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
                        <th>Ürün Adı</th>
                        <th>Kategori</th>
                        <th>Fiyat</th>
                        <th>Durum</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($urun = mysqli_fetch_assoc($urunler)) : ?>
                        <tr>
                            <td>
                                <?php if (!empty($urun['gorsel']) && file_exists('../' . $urun['gorsel'])) : ?>
                                    <img src="../<?php echo $urun['gorsel']; ?>" class="admin-table-img">
                                <?php else : ?>
                                    <div class="admin-table-placeholder">📷</div>
                                <?php endif; ?>
                            </td>
                            <td class="fw-bold"><?php echo $urun['isim']; ?></td>
                            <td><?php echo $urun['kategori_adi']; ?></td>
                            <td><?php echo number_format($urun['fiyat'], 2); ?> ₺</td>
                            <td>
                                <a href="?toggle=<?php echo $urun['id']; ?>" 
                                   class="btn-admin btn-sm <?php echo $urun['aktif'] ? 'btn-admin-success' : 'btn-admin-warning'; ?>"
                                   onclick="return confirm('Ürün durumu değiştirilsin mi?')">
                                    <?php echo $urun['aktif'] ? 'Aktif' : 'Pasif'; ?>
                                </a>
                            </td>
                            <td>
                                <button class="btn-admin btn-admin-primary btn-sm" onclick="duzenle(<?php echo htmlspecialchars(json_encode($urun)); ?>)">Düzenle</button>
                                <a href="?sil=<?php echo $urun['id']; ?>" class="btn-admin btn-admin-danger btn-sm" onclick="return confirm('Bu ürünü silmek istediğinize emin misiniz?')">Sil</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- EKLEME MODAL -->
    <div class="modal fade" id="ekleModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content admin-modal">
                <div class="modal-header">
                    <h5 class="modal-title">Yeni Ürün Ekle</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Kategori</label>
                                <select name="kategori_id" class="form-select admin-input" required>
                                    <option value="">Seçiniz...</option>
                                    <?php
                                    mysqli_data_seek($kategoriler, 0);
                                    while ($kat = mysqli_fetch_assoc($kategoriler)) :
                                    ?>
                                        <option value="<?php echo $kat['id']; ?>"><?php echo $kat['isim']; ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Ürün Adı</label>
                                <input type="text" name="isim" class="form-control admin-input" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Açıklama</label>
                            <textarea name="aciklama" class="form-control admin-input" rows="3"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Fiyat (₺)</label>
                                <input type="number" step="0.01" min="0" max="9999.99" name="fiyat" class="form-control admin-input" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Görsel</label>
                                <input type="file" name="gorsel" class="form-control admin-input" accept="image/*" onchange="onizle(this, 'ekle_urun_onizleme')">
                                <img id="ekle_urun_onizleme" class="gorsel-onizleme" alt="Önizleme">
                            </div>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" name="aktif" class="form-check-input" id="aktif_ekle" checked>
                            <label class="form-check-label" for="aktif_ekle">Aktif</label>
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
        <div class="modal-dialog modal-lg">
            <div class="modal-content admin-modal">
                <div class="modal-header">
                    <h5 class="modal-title">Ürünü Düzenle</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Kategori</label>
                                <select name="kategori_id" id="edit_kategori_id" class="form-select admin-input" required>
                                    <?php
                                    mysqli_data_seek($kategoriler, 0);
                                    while ($kat = mysqli_fetch_assoc($kategoriler)) :
                                    ?>
                                        <option value="<?php echo $kat['id']; ?>"><?php echo $kat['isim']; ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Ürün Adı</label>
                                <input type="text" name="isim" id="edit_isim" class="form-control admin-input" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Açıklama</label>
                            <textarea name="aciklama" id="edit_aciklama" class="form-control admin-input" rows="3"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Fiyat (₺)</label>
                                <input type="number" step="0.01" min="0" max="9999.99" name="fiyat" id="edit_fiyat" class="form-control admin-input" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Yeni Görsel (opsiyonel)</label>
                                <input type="file" name="gorsel" class="form-control admin-input" accept="image/*" onchange="onizle(this, 'edit_urun_onizleme')">
                                <img id="edit_urun_onizleme" class="gorsel-onizleme" alt="Önizleme">
                                <small class="text-muted">Boş bırakırsanız mevcut görsel korunur</small>
                            </div>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" name="aktif" class="form-check-input" id="edit_aktif">
                            <label class="form-check-label" for="edit_aktif">Aktif</label>
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
        function duzenle(urun) {
            document.getElementById('edit_id').value = urun.id;
            document.getElementById('edit_kategori_id').value = urun.kategori_id;
            document.getElementById('edit_isim').value = urun.isim;
            document.getElementById('edit_aciklama').value = urun.aciklama;
            document.getElementById('edit_fiyat').value = urun.fiyat;
            document.getElementById('edit_aktif').checked = urun.aktif == 1;
            // Düzenleme modalı açılırken önizleme sıfırlanır
            const onizleme = document.getElementById('edit_urun_onizleme');
            onizleme.style.display = 'none';
            new bootstrap.Modal(document.getElementById('duzenleModal')).show();
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
