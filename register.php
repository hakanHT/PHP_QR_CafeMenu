<?php
$pageTitle = 'Kayıt Ol - Dostum Kafe';
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
</head>
<body class="login-page">

    <div class="login-wrapper">
        <div class="login-container">
            <!-- Logo -->
            <div class="login-header text-center">
                <a href="index.php">
                    <img src="dostum_images/DOSTUMKAFE_NOBG_logo.png" alt="Dostum Kafe" class="login-logo">
                </a>
                <h1>Hesap Oluştur</h1>
            </div>

            <!-- Kayıt Formu -->
            <form action="#" method="POST" class="login-form">
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
