<?php
session_start();
require_once 'config.php';

// Güvenlik: Sadece admin sıfırlayabilir
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    die("Yetkisiz erişim!");
}

try {
    $db->beginTransaction();

    // İndirimleri sil
    $db->exec("DELETE FROM discounts");
    // Şans kutusu geçmişini sil
    $db->exec("DELETE FROM lucky_box_history");
    
    // Opsiyonel: Diğer test verilerini de silmek istersen buraya ekleyebilirsin
    // $db->exec("DELETE FROM users WHERE is_admin = 0"); 

    $db->commit();
    header("Location: admin_panel.php?reset=success");
} catch (Exception $e) {
    $db->rollBack();
    header("Location: admin_panel.php?reset=error");
}
exit();
