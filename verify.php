<?php
require_once 'config.php';
$pageTitle = 'Doğrulama - Dostum Kafe';
$email = $_GET['email'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = trim($_POST['code']);
    $userEmail = $_POST['email'];
    $now = date("Y-m-d H:i:s");
    
    // Kod ve süre kontrolü (PHP tarafındaki $now değişkenini kullanıyoruz)
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ? AND verification_code = ? AND verification_expires_at > ?");
    $stmt->execute([$userEmail, $code, $now]);
    
    if ($stmt->rowCount() > 0) {
        // Kod doğru ve süresi geçmemiş
        $update = $db->prepare("UPDATE users SET is_verified = 1, verification_code = NULL, verification_expires_at = NULL WHERE email = ?");
        $update->execute([$userEmail]);
        
        header("Location: login.php?status=verified");
        exit();
    } else {
        // Kod yanlış veya süresi dolmuş olabilir
        $checkExpiry = $db->prepare("SELECT id FROM users WHERE email = ? AND verification_expires_at <= ? AND verification_code = ?");
        $checkExpiry->execute([$userEmail, $now, $code]);
        
        if ($checkExpiry->rowCount() > 0) {
            $error = "Hata: Doğrulama kodunun süresi dolmuş (5 dk). Lütfen tekrar kayıt olun.";
        } else {
            $error = "Hata: Geçersiz doğrulama kodu.";
        }
    }
}
?>
<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $pageTitle; ?></title>
    <link href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Josefin+Sans:400,700" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
        <div class="login-container text-center">
            <div class="login-header">
                <img src="dostum_images/DOSTUMKAFE_NOBG_logo.png" alt="Dostum Kafe" class="login-logo">
                <h1>E-posta Doğrulama</h1>
                <p><?php echo htmlspecialchars($email); ?> adresine bir kod gönderdik.</p>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger py-2" style="font-size: 13px;"><?php echo $error; ?></div>
            <?php endif; ?>

            <form action="verify.php" method="POST" class="login-form">
                <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
                
                <div class="form-group mb-4 text-start">
                    <label for="code" class="form-label">Doğrulama Kodu</label>
                    <input type="text" id="code" name="code" class="form-control custom-input text-center" style="letter-spacing: 5px; font-weight: bold;" maxlength="6" required>
                </div>

                <button type="submit" class="btn btn-brand w-100 mb-4">Onayla</button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
