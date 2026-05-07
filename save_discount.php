<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Giriş gerekli']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $userId = $_SESSION['user_id'];

    // Whitelist kontrolü — sadece tanımlı oranlar kabul edilir
    $allowedDiscounts = [15, 20, 25, 30, 50];
    $discPercent = (int)$_POST['disc'];
    if (!in_array($discPercent, $allowedDiscounts)) {
        echo json_encode(['success' => false, 'error' => 'Geçersiz indirim oranı.']);
        exit();
    }

    // Kategori DB'de gerçekten var mı?
    $catId = (int)$_POST['cat_id'];
    $catCheck = $db->prepare("SELECT id FROM categories WHERE id = ?");
    $catCheck->execute([$catId]);
    if ($catCheck->rowCount() === 0) {
        echo json_encode(['success' => false, 'error' => 'Geçersiz kategori.']);
        exit();
    }

    // Haftalık kontrol (Güvenlik için tekrar)
    $stmt = $db->prepare("SELECT opened_at FROM lucky_box_history WHERE user_id = ? ORDER BY opened_at DESC LIMIT 1");
    $stmt->execute([$userId]);
    $lastOpen = $stmt->fetch();

    if ($lastOpen) {
        $lastDate = strtotime($lastOpen['opened_at']);
        if ((time() - $lastDate) < (7 * 24 * 60 * 60)) {
            echo json_encode(['success' => false, 'error' => 'Haftalık katılım hakkınızı zaten kullandınız.']);
            exit();
        }
    }

    // Rastgele Benzersiz Kod Üret (DOSTUM + 4 karakter)
    $chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $randomPart = "";
    for ($i = 0; $i < 4; $i++) {
        $randomPart .= $chars[rand(0, strlen($chars) - 1)];
    }
    $code = "DOSTUM" . $discPercent . $randomPart;

    $expiresAt = date('Y-m-d H:i:s', strtotime('+7 days'));

    try {
        $db->beginTransaction();

        // İndirimi kaydet
        $ins1 = $db->prepare("INSERT INTO discounts (user_id, category_id, code, discount_percent, expires_at) VALUES (?, ?, ?, ?, ?)");
        $ins1->execute([$userId, $catId, $code, $discPercent, $expiresAt]);

        // Geçmişe işle
        $ins2 = $db->prepare("INSERT INTO lucky_box_history (user_id) VALUES (?)");
        $ins2->execute([$userId]);

        $db->commit();
        echo json_encode(['success' => true, 'code' => $code]);
    } catch (Exception $e) {
        $db->rollBack();
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
