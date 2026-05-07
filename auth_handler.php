<?php
/**
 * Kayıt ve Giriş İşlemleri Beyni
 */
session_start();
require_once 'config.php';

// PHPMailer Dosyalarını Dahil Et
require 'libs/PHPMailer/Exception.php';
require 'libs/PHPMailer/PHPMailer.php';
require 'libs/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // --- GİRİŞ İŞLEMİ ---
    if (isset($_POST['login_email']) && isset($_POST['login_password'])) {
        $email = trim($_POST['login_email']);
        $password = $_POST['login_password'];

        $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // Şifre doğru, onay kontrolü yap
            if ($user['is_verified'] == 0) {
                header("Location: login.php?error=unverified&email=" . urlencode($email));
                exit();
            }

            // Giriş başarılı, session bilgilerini ata
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['fullname'] = $user['fullname'];
            $_SESSION['is_admin'] = $user['is_admin'];

            // Son giriş tarihini güncelle
            $updateLogin = $db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            $updateLogin->execute([$user['id']]);

            // Admin ise panele, değilse ana sayfaya
            if ($user['is_admin'] == 1) {
                header("Location: admin_panel.php");
            } else {
                header("Location: index.php");
            }
            exit();
        } else {
            header("Location: login.php?error=invalid");
            exit();
        }
    }
    
    // --- KAYIT İŞLEMİ ---
    if (isset($_POST['fullname']) && isset($_POST['email']) && isset($_POST['password'])) {
        
        $fullname = trim($_POST['fullname']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        
        // 1. Ad Soyad Kontrolü
        if (!preg_match("/^[a-zA-ZçğıöşüÇĞİÖŞÜ\s]+$/u", $fullname) || empty($fullname)) {
            header("Location: register.php?error=invalid_name");
            exit();
        }
        
        // 2. E-posta Kontrolü
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            header("Location: register.php?error=invalid_email");
            exit();
        }
        
        // 3. Şifre Kontrolü
        if (strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password)) {
            header("Location: register.php?error=weak_password");
            exit();
        }

        // 4. Şifreyi Hashle (e-posta kontrolünden önce — her iki yol da kullanır)
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // 5. E-posta Kayıtlı mı?
        $checkEmail = $db->prepare("SELECT id, is_verified FROM users WHERE email = ?");
        $checkEmail->execute([$email]);
        $existingUser = $checkEmail->fetch(PDO::FETCH_ASSOC);

        if ($existingUser) {
            if ($existingUser['is_verified'] == 1) {
                // Onaylı hesap — zaten kayıtlı
                header("Location: register.php?error=email_exists");
                exit();
            }
            // Doğrulanmamış hesap: yeni kod üret ve güncelle
            $verificationCode = rand(100000, 999999);
            $expiresAt = date("Y-m-d H:i:s", strtotime('+5 minutes'));
            $db->prepare("UPDATE users SET fullname=?, password=?, verification_code=?, verification_expires_at=? WHERE email=?")
               ->execute([$fullname, $hashedPassword, $verificationCode, $expiresAt, $email]);
        } else {
            // Yeni kullanıcı: veritabanına kaydet
            $verificationCode = rand(100000, 999999);
            $expiresAt = date("Y-m-d H:i:s", strtotime('+5 minutes'));
            try {
                $db->prepare("INSERT INTO users (fullname, email, password, verification_code, verification_expires_at) VALUES (?, ?, ?, ?, ?)")
                   ->execute([$fullname, $email, $hashedPassword, $verificationCode, $expiresAt]);
            } catch (PDOException $e) {
                die("Veritabanı hatası: " . $e->getMessage());
            }
        }

        // Her iki durumda da e-posta gönder
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = SMTP_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = SMTP_USER;
            $mail->Password   = SMTP_PASS;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = SMTP_PORT;
            $mail->CharSet    = 'UTF-8';

            $mail->setFrom(SMTP_USER, 'Dostum Kafe');
            $mail->addAddress($email, $fullname);

            $mail->isHTML(true);
            $mail->Subject = 'Dostum Kafe - Doğrulama Kodunuz';
            
            $mail->Body = '
            <div style="background-color: #f4f1ea; padding: 50px 20px; font-family: \'Segoe UI\', Tahoma, Geneva, Verdana, sans-serif;">
                <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                    <div style="background-color: #171210; padding: 30px; text-align: center;">
                        <h2 style="color: #c9a076; margin: 0; font-family: serif; letter-spacing: 2px;">DOSTUM KAFE</h2>
                    </div>
                    <div style="padding: 40px; text-align: center;">
                        <h1 style="color: #171210; font-size: 24px; margin-bottom: 20px;">Hesabınızı Doğrulayın</h1>
                        <p style="color: #555; line-height: 1.6; font-size: 16px;">
                            Merhaba <strong>' . $fullname . '</strong>,<br>
                            Dostum Kafe dünyasına hoş geldiniz! Kayıt işlemini tamamlamak için aşağıdaki 6 haneli doğrulama kodunu kullanabilirsiniz.
                        </p>
                        <div style="margin: 40px 0;">
                            <span style="background-color: #f4f1ea; color: #543000; font-size: 36px; font-weight: bold; letter-spacing: 8px; padding: 15px 30px; border: 2px dashed #c9a076; border-radius: 4px; display: inline-block;">
                                ' . $verificationCode . '
                            </span>
                        </div>
                        <p style="color: #999; font-size: 14px; margin-bottom: 30px;">
                            Bu kod güvenlik nedeniyle <strong>5 dakika</strong> sonra geçerliliğini yitirecektir.
                        </p>
                        <div style="border-top: 1px solid #eee; padding-top: 30px;">
                            <p style="color: #171210; font-size: 12px; margin: 0;">
                                © ' . date("Y") . ' Dostum Kafe. Tüm hakları saklıdır.
                            </p>
                        </div>
                    </div>
                </div>
            </div>';

            $mail->send();
            header("Location: verify.php?email=" . urlencode($email));
            exit();
            
        } catch (Exception $e) {
            header("Location: register.php?error=mail_error");
            exit();
        }
    }
}
?>
