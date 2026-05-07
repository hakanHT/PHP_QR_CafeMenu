<?php
session_start();
require_once 'config.php';

$pageTitle = 'QR Menü - Dostum Kafe';

// Kategorileri A'dan Z'ye çek
$categories = $db->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
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
        :root {
            --menu-bg: #090807;
            --menu-card: #15110e;
            --menu-hover: #1c1613;
            --brand-gold: #c9a076;
            --text-main: #f8f4ee;
            --text-muted: #8b7d72;
        }
        
        body { 
            background-color: var(--menu-bg); 
            color: var(--text-main); 
            font-family: 'Poppins', sans-serif; 
            padding-bottom: 50px;
        }
        
        /* Modern Back Button */
        .modern-back-btn {
            position: absolute;
            top: 30px;
            left: 30px;
            z-index: 10;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            color: rgba(255, 255, 255, 0.7);
            font-weight: 500;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .modern-back-btn svg {
            width: 20px;
            height: 20px;
            transition: transform 0.3s ease;
        }
        
        .modern-back-btn:hover {
            color: #fff;
        }
        
        .modern-back-btn:hover svg {
            transform: translateX(-5px);
        }

        /* Top Right Area (Profile / Login) */
        .top-right-area {
            position: absolute;
            top: 25px;
            right: 30px;
            z-index: 10;
        }
        .btn-login-hero {
            color: #fff;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(201, 160, 118, 0.15);
            padding: 9px 18px;
            border-radius: 30px;
            border: 1px solid rgba(201, 160, 118, 0.4);
            transition: 0.3s;
            letter-spacing: 0.5px;
        }
        .btn-login-hero:hover {
            background: var(--brand-gold);
            color: #000;
            border-color: var(--brand-gold);
        }
        .profile-link {
            color: #fff;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
            background: rgba(255,255,255,0.05);
            padding: 8px 15px;
            border-radius: 30px;
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255,255,255,0.1);
            transition: 0.3s;
        }
        .profile-link:hover {
            background: rgba(201, 160, 118, 0.2);
            border-color: var(--brand-gold);
        }

        /* Hero Header */
        .menu-hero {
            position: relative;
            padding: 100px 0 60px;
            background: url('assets/images/about.jpg') center center / cover no-repeat;
            border-bottom: none;
            text-align: center;
            overflow: hidden;
        }
        
        /* Gradient overlay — tam kapatan, sızıntı yok */
        .menu-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(to bottom,
                rgba(9, 8, 7, 0.75) 0%,
                rgba(9, 8, 7, 0.95) 85%,
                rgba(9, 8, 7, 1) 100%
            );
            z-index: 1;
        }
        
        .menu-hero .container {
            position: relative;
            z-index: 2;
        }
        
        /* Hero ile içerik arasında sert bir hat kalmasın */
        .menu-body {
            background: var(--menu-bg);
            position: relative;
            z-index: 2;
        }
        
        .menu-hero h1 { 
            font-family: 'Josefin Sans', sans-serif; 
            font-size: 48px; 
            font-weight: 700; 
            color: #fff;
            letter-spacing: 8px; 
            margin-bottom: 15px;
            text-transform: uppercase;
        }
        
        .menu-hero p { 
            color: var(--brand-gold); 
            font-weight: 300; 
            font-size: 15px; 
            letter-spacing: 3px; 
            text-transform: uppercase;
        }

        /* Premium Lootbox (Only for Logged In) */
        .premium-lootbox {
            background: linear-gradient(135deg, rgba(201, 160, 118, 0.08), rgba(21, 17, 14, 0.9));
            border: 1px solid rgba(201, 160, 118, 0.3);
            border-radius: 16px;
            padding: 30px;
            text-align: center;
            margin-top: 40px;
            margin-bottom: 30px;
            transition: transform 0.3s ease, border-color 0.3s ease;
        }

        /* Guest Login Teaser */
        .guest-teaser {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            background: rgba(255,255,255,0.02);
            border: 1px solid rgba(255,255,255,0.06);
            border-radius: 14px;
            padding: 20px 28px;
            margin-top: 40px;
            margin-bottom: 30px;
        }
        .guest-teaser p {
            margin: 0;
            color: var(--text-muted);
            font-size: 14px;
        }
        .guest-teaser p strong {
            display: block;
            color: var(--text-main);
            font-size: 15px;
            margin-bottom: 4px;
        }
        .btn-teaser {
            white-space: nowrap;
            background: var(--brand-gold);
            color: #0d0b0a;
            font-weight: 700;
            font-size: 13px;
            padding: 10px 24px;
            border-radius: 30px;
            text-decoration: none;
            transition: 0.3s;
            letter-spacing: 0.5px;
        }
        .btn-teaser:hover { background: #fff; color: #000; }
        @media (max-width: 576px) {
            .guest-teaser { flex-direction: column; text-align: center; }
        }
        
        .premium-lootbox:hover {
            transform: translateY(-5px);
            border-color: var(--brand-gold);
        }
        
        .premium-lootbox h4 {
            color: #fff;
            font-family: 'Josefin Sans', sans-serif;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .premium-lootbox p {
            color: var(--text-muted);
            font-size: 14px;
            margin-bottom: 20px;
        }
        
        .loot-btn {
            background: var(--brand-gold);
            color: #15110e;
            border: none;
            padding: 10px 30px;
            border-radius: 30px;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }
        
        .loot-btn:hover {
            background: #fff;
            color: #000;
            box-shadow: 0 0 20px rgba(201, 160, 118, 0.4);
        }

        /* Sleek Accordion */
        .menu-container {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .accordion-item { 
            background: transparent; 
            border: none; 
            margin-bottom: 20px; 
        }
        
        .accordion-button {
            background: var(--menu-card);
            color: var(--text-main);
            padding: 25px 30px;
            font-family: 'Josefin Sans', sans-serif;
            font-size: 22px;
            font-weight: 600;
            letter-spacing: 1px;
            border: 1px solid rgba(255, 255, 255, 0.03);
            border-radius: 12px !important;
            box-shadow: none !important;
            transition: all 0.3s ease;
        }
        
        .accordion-button:hover {
            background: var(--menu-hover);
        }
        
        .accordion-button:not(.collapsed) {
            background: var(--menu-card);
            color: var(--brand-gold);
            border-color: rgba(201, 160, 118, 0.2);
            border-bottom-left-radius: 0 !important;
            border-bottom-right-radius: 0 !important;
        }
        
        .accordion-button::after { filter: invert(1); opacity: 0.5; }
        .accordion-button:not(.collapsed)::after { filter: invert(0.8) sepia(1) saturate(3) hue-rotate(340deg); opacity: 1; }

        .accordion-body {
            background: rgba(21, 17, 14, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.03);
            border-top: none;
            border-bottom-left-radius: 12px;
            border-bottom-right-radius: 12px;
            padding: 30px 20px;
        }

        /* Elegant Product Cards */
        .product-card {
            display: flex;
            align-items: center;
            padding: 15px;
            border-radius: 12px;
            transition: all 0.3s ease;
            margin-bottom: 15px;
            border: 1px solid transparent;
        }
        
        .product-card:last-child {
            margin-bottom: 0;
        }
        
        .product-card:hover { 
            background: rgba(255, 255, 255, 0.02);
            border-color: rgba(201, 160, 118, 0.1);
        }
        
        .product-img { 
            width: 90px; 
            height: 90px; 
            border-radius: 10px; 
            object-fit: cover; 
            box-shadow: 0 4px 10px rgba(0,0,0,0.3);
        }
        
        .product-info { 
            padding-left: 20px; 
            flex: 1; 
        }
        
        .product-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 5px;
        }
        
        .product-name { 
            font-family: 'Josefin Sans', sans-serif; 
            font-size: 18px; 
            font-weight: 600;
            margin: 0; 
            color: #fff; 
        }
        
        .product-price { 
            color: var(--brand-gold); 
            font-weight: 700; 
            font-size: 16px; 
            background: rgba(201, 160, 118, 0.1);
            padding: 4px 12px;
            border-radius: 20px;
        }
        
        .product-desc { 
            font-size: 13px; 
            color: var(--text-muted); 
            margin: 0; 
            line-height: 1.5; 
        }

        /* Clean Footer */
        .menu-footer {
            text-align: center;
            padding-top: 60px;
            color: rgba(255, 255, 255, 0.2);
            font-size: 12px;
            font-family: 'Josefin Sans', sans-serif;
            letter-spacing: 1px;
        }
        
        @media (max-width: 768px) {
            .modern-back-btn { top: 15px; left: 15px; font-size: 12px; }
            .menu-hero { padding: 80px 0 40px; }
            .menu-hero h1 { font-size: 32px; letter-spacing: 4px; }
            .premium-lootbox { margin: -20px 15px 40px; padding: 20px; }
            .product-header { flex-direction: column; }
            .product-price { margin-top: 5px; display: inline-block; }
        }
    </style>
</head>
<body>

    <!-- Hero — back btn ve profil bu içindedir, overflow:hidden ile resim sınırlanır -->
    <header class="menu-hero">

        <!-- Back Button -->
        <a href="index.php" class="modern-back-btn">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            <span>ANA SAYFA</span>
        </a>

        <!-- Top Right: Giriş yapılmışsa profil, yapılmamışsa buton -->
        <div class="top-right-area">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="profile.php" class="profile-link">
                    <span><?php echo explode(' ', $_SESSION['fullname'])[0]; ?></span>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                </a>
            <?php else: ?>
                <a href="login.php" class="btn-login-hero">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path><polyline points="10 17 15 12 10 7"></polyline><line x1="15" y1="12" x2="3" y2="12"></line></svg>
                    Giriş Yap
                </a>
            <?php endif; ?>
        </div>

        <div class="container">
            <h1>MENÜMÜZ</h1>
            <p>Dost Samimiyetinde Lezzetler</p>
        </div>
    </header>

    <div class="menu-body">
    <div class="container">
        <div class="menu-container">
            
            <?php if (isset($_SESSION['user_id'])): ?>
            <!-- Giriş yapan kullanıcıya özel şans kutusu -->
            <div class="premium-lootbox">
                <h4>Haftalık Dostum Kasan Hazır!</h4>
                <p>Bu haftaki kasanı aç, sürpriz indirimini kap. Kazandığın kodu ödeme sırasında garsona ilet.</p>
                <a href="lucky_box.php" class="loot-btn">Kasayı Aç</a>
            </div>
            <?php else: ?>
            <!-- Misafir kullanıcıya teaser -->
            <div class="guest-teaser">
                <p>
                    <strong>Haftalık Dostum Kasalarını Açın!</strong>
                    Her hafta 1 kasa açma hakkı kazanın, sürpriz indirimler sizi bekliyor.
                </p>
                <a href="login.php" class="btn-teaser">Giriş Yap</a>
            </div>
            <?php endif; ?>

            <div class="accordion mt-5" id="qrMenu">
                <?php foreach ($categories as $index => $category): ?>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button <?php echo $index === 0 ? '' : 'collapsed'; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $category['id']; ?>">
                                <?php echo htmlspecialchars($category['name']); ?>
                            </button>
                        </h2>
                        <div id="collapse<?php echo $category['id']; ?>" class="accordion-collapse collapse <?php echo $index === 0 ? 'show' : ''; ?>" data-bs-parent="#qrMenu">
                            <div class="accordion-body">
                                <?php
                                $catId = $category['id'];
                                $stmt = $db->prepare("SELECT * FROM products WHERE category_id = ? ORDER BY name ASC");
                                $stmt->execute([$catId]);
                                $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                
                                if(count($products) > 0):
                                    foreach ($products as $product):
                                ?>
                                    <div class="product-card">
                                        <?php if (!empty($product['image_path'])): ?>
                                        <img src="<?php echo $product['image_path']; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-img">
                                        <?php endif; ?>
                                        <div class="product-info">
                                            <div class="product-header">
                                                <h4 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h4>
                                                <span class="product-price"><?php echo number_format($product['price'], 0); ?> TL</span>
                                            </div>
                                            <p class="product-desc"><?php echo htmlspecialchars($product['description']); ?></p>
                                        </div>
                                    </div>
                                <?php 
                                    endforeach; 
                                else:
                                    echo '<p class="text-center text-muted m-0 py-3" style="font-size: 14px;">Bu kategoride henüz ürün bulunmuyor.</p>';
                                endif;
                                ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="menu-footer">
                &copy; <?php echo date("Y"); ?> Dostum Kafe. Tüm hakları saklıdır.
            </div>
            
        </div>
    </div>
    </div><!-- /menu-body -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
