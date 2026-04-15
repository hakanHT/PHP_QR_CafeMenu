<?php
session_start();

// Giris kontrolu
if (!isset($_SESSION['admin_giris'])) {
    header("Location: login.php");
    exit;
}

require '../db.php';

// Kategori sayisi
$kat_sorgu = mysqli_query($baglanti, "SELECT COUNT(*) as toplam FROM kategoriler");
$kat_sayisi = mysqli_fetch_assoc($kat_sorgu)['toplam'];

// Aktif ve toplam ürün sayısı
$urun_sorgu = mysqli_query($baglanti, "SELECT COUNT(*) as toplam, SUM(aktif) as aktif FROM urunler");
$urun_data = mysqli_fetch_assoc($urun_sorgu);
$urun_sayisi = $urun_data['toplam'];
$aktif_urun = (int)($urun_data['aktif'] ?? 0);

mysqli_close($baglanti);
?>
<!doctype html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel | Dostum Kafe</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="admin.css">
</head>
<body class="admin-panel-body">

    <div class="admin-panel-container">
        <!-- Logo + Hosgeldin -->
        <div class="text-center mb-5">
            <img src="../dostum_images/DOSTUMKAFE_NOBG_logo.png" alt="Logo" class="admin-panel-logo mb-3">
            <h2 class="admin-panel-welcome">Hoşgeldin, <?php echo $_SESSION['admin_kullanici']; ?>!</h2>
            <p class="admin-panel-subtitle">Ne yapmak istersin?</p>
        </div>

        <!-- Menu butonlari -->
        <div class="admin-menu-grid">
            <!-- Kategoriler -->
            <a href="kategoriler.php" class="admin-menu-btn">
                <div class="admin-menu-icon">📂</div>
                <div class="admin-menu-title">Kategoriler</div>
                <div class="admin-menu-count"><?php echo $kat_sayisi; ?> kategori</div>
            </a>

            <!-- Urunler -->
            <a href="urunler.php" class="admin-menu-btn">
                <div class="admin-menu-icon">🍽️</div>
                <div class="admin-menu-title">Ürünler</div>
                <div class="admin-menu-count"><?php echo $urun_sayisi; ?> ürün &bull; <?php echo $aktif_urun; ?> aktif</div>
            </a>

            <!-- Menuyu Gor -->
            <a href="../menu.php" target="_blank" class="admin-menu-btn">
                <div class="admin-menu-icon">👁️</div>
                <div class="admin-menu-title">Menüyü Görüntüle</div>
                <div class="admin-menu-count">Canlı önizleme</div>
            </a>

            <!-- Cikis -->
            <a href="cikis.php" class="admin-menu-btn admin-menu-btn-danger">
                <div class="admin-menu-icon">🚪</div>
                <div class="admin-menu-title">Çıkış Yap</div>
                <div class="admin-menu-count">Oturumu kapat</div>
            </a>
        </div>
    </div>

</body>
</html>
