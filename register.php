<?php
session_start();
$pageTitle = 'Kayıt Ol - Dostum Kafe';

// Hata mesajlarını yakala
$errorMsg = '';
if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'invalid_name': $errorMsg = 'Hata: Ad Soyad sadece harflerden oluşmalıdır.'; break;
        case 'invalid_email': $errorMsg = 'Hata: Geçersiz e-posta formatı.'; break;
        case 'weak_password': $errorMsg = 'Hata: Şifre en az 8 karakter olmalı; büyük harf, küçük harf ve rakam içermelidir.'; break;
        case 'email_exists': $errorMsg = 'Hata: Bu e-posta adresi zaten kayıtlı.'; break;
        case 'mail_error': $errorMsg = 'Hata: Onay e-postası gönderilemedi. Lütfen tekrar deneyin.'; break;
    }
}
?>
<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $pageTitle; ?></title>

    <!-- Fontlar -->
    <link href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Josefin+Sans:400,700" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Ana stil -->
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .back-home-btn {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 999;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: rgba(255,255,255,0.65);
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: color 0.3s;
        }
        .back-home-btn:hover { color: #fff; }
        .back-home-btn svg { transition: transform 0.3s; }
        .back-home-btn:hover svg { transform: translateX(-4px); }
        @media (max-width: 576px) {
            .back-home-btn { font-size: 11px; top: 14px; left: 14px; }
        }
    </style>
</head>
<body class="login-page">

    <a href="index.php" class="back-home-btn">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        Ana Sayfa
    </a>

    <div class="login-wrapper">
        <div class="login-container">
            <!-- Logo -->
            <div class="login-header text-center">
                <a href="index.php">
                    <img src="dostum_images/DOSTUMKAFE_NOBG_logo.png" alt="Dostum Kafe" class="login-logo">
                </a>
                <h1>Hesap Oluştur</h1>
            </div>

            <!-- Bildirimler -->
            <?php if ($errorMsg): ?>
                <div class="alert alert-danger py-2" style="font-size: 13px;"><?php echo $errorMsg; ?></div>
            <?php endif; ?>

            <!-- Kayıt Formu -->
            <form action="auth_handler.php" method="POST" class="login-form">
                <div class="form-group mb-4">
                    <label for="fullname" class="form-label">Ad Soyad</label>
                    <input type="text" id="fullname" name="fullname" class="form-control custom-input" required>
                </div>

                <div class="form-group mb-4">
                    <label for="email" class="form-label">E-posta Adresi</label>
                    <input type="email" id="email" name="email" class="form-control custom-input" required>
                </div>

                <div class="form-group mb-4">
                    <label for="password" class="form-label">Şifre</label>
                    <input type="password" id="password" name="password" class="form-control custom-input" required>
                </div>

                <button type="submit" class="btn btn-brand w-100 mb-4">Kayıt Ol</button>

                <div class="login-footer text-center">
                    <span>Zaten hesabınız var mı?</span>
                    <a href="login.php" class="signup-link">Giriş Yapın</a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
