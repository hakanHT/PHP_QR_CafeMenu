<?php
session_start();

// Zaten giris yapilmissa panele yonlendir
if (isset($_SESSION['admin_giris'])) {
    header("Location: panel.php");
    exit;
}

// Form gonderildi mi kontrolu
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require '../db.php'; // Ust klasordeki db.php
    
    $kullanici = mysqli_real_escape_string($baglanti, $_POST['kullanici_adi']);
    $sifre = $_POST['sifre'];
    
    // Veritabanindan admin bilgisini cek
    $sorgu = mysqli_query($baglanti, "SELECT * FROM admin_kullanicilar WHERE kullanici_adi = '$kullanici'");
    
    if (mysqli_num_rows($sorgu) == 1) {
        $admin = mysqli_fetch_assoc($sorgu);
        
        // Sifre kontrol
        if (password_verify($sifre, $admin['sifre'])) {
            // Giris basarili
            $_SESSION['admin_giris'] = true;
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_kullanici'] = $admin['kullanici_adi'];
            header("Location: panel.php");
            exit;
        } else {
            $hata = "Kullanıcı adı veya şifre hatalı!";
        }
    } else {
        $hata = "Kullanıcı adı veya şifre hatalı!";
    }
    
    mysqli_close($baglanti);
}
?>
<!doctype html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Giriş | Dostum Kafe</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="admin.css">
</head>
<body class="login-body">

    <!-- Ana container -->
    <div class="login-container">

        <!-- Sol - Logo -->
        <div class="logo-section">
            <img src="../dostum_images/DOSTUMKAFE_NOBG_logo.png" alt="Dostum Kafe Logo">
            <span class="logo-altyazi">Yönetim Paneli</span>
        </div>

        <!-- Orta - Dikey cizgi -->
        <div class="login-divider"></div>

        <!-- Sag - Form -->
        <div class="form-section">
            <h2 class="login-baslik">Giriş Yap</h2>
            <p class="login-altyazi">Yönetim paneline erişmek için kimliğini doğrula</p>

            <?php if (isset($hata)) : ?>
                <div class="login-hata"><?php echo $hata; ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="login-input-grup">
                    <span class="login-input-ikon">👤</span>
                    <input type="text"
                           name="kullanici_adi"
                           class="login-input"
                           placeholder="Kullanıcı Adı"
                           required
                           autocomplete="username">
                </div>
                <div class="login-input-grup">
                    <span class="login-input-ikon">🔒</span>
                    <input type="password"
                           name="sifre"
                           class="login-input"
                           placeholder="Şifre"
                           required
                           autocomplete="current-password">
                </div>
                <button type="submit" class="btn-login">Giriş Yap →</button>
            </form>
        </div>

    </div>

</body>
</html>
