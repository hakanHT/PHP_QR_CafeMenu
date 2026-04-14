<?php
// Veritabani baglanti dosyasi 

$sunucu   = "127.0.0.1";   
$kullanici = "root";        
$sifre     = "";            
$veritabani = "dostum_kafe"; 

// Baglantiyi kur
$baglanti = mysqli_connect($sunucu, $kullanici, $sifre, $veritabani);

// Baglanti hatasi kontrolu
if (!$baglanti) {
    die("Veritabani baglanti hatasi: " . mysqli_connect_error());
}

// Turkce karakter destegi
mysqli_set_charset($baglanti, "utf8mb4");
?>


