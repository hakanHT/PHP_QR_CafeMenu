<?php
session_start();
// Sayfa başlığı
$pageTitle = 'Dostum Kafe';

// Güncel yıl
$currentYear = date('Y');
?>
<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Dostum Kafe, kaliteli kahve ve taze tatlılarla dost samimiyetinde bir kafe deneyimi sunar.">
    <title><?php echo $pageTitle; ?></title>

    <!-- Dış kaynaklar -->
    <link href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Josefin+Sans:400,700" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Great+Vibes" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Sayfa tasarımı -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Üst menü -->
    <nav class="navbar navbar-expand-lg navbar-dark site-navbar fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php" aria-label="Dostum Kafe ana sayfa">
                <img src="dostum_images/DOSTUMKAFE_NOBG_logo.png" alt="Dostum Kafe logo" class="brand-logo">
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Menüyü aç">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="mainNavbar">
                <ul class="navbar-nav ms-auto align-items-lg-center">
                    <li class="nav-item"><a class="nav-link active" href="#anasayfa">Ana Sayfa</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php#hakkimizda">Hakkımızda</a></li>
                    <li class="nav-item"><a class="nav-link" href="menu.php">Menü</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php#iletisim">İletişim</a></li>
                    
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item ms-lg-3 dropdown">
                            <a class="nav-link dropdown-toggle text-brand" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <svg class="login-icon me-1" viewBox="0 0 24 24" style="width: 18px; height: 18px;"><path d="M20 21a8 8 0 0 0-16 0"></path><circle cx="12" cy="7" r="4"></circle></svg>
                                <?php echo explode(' ', $_SESSION['fullname'])[0]; ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end" aria-labelledby="userDropdown">
                                <?php if ($_SESSION['is_admin'] == 1): ?>
                                    <li><a class="dropdown-item" href="admin_panel.php">Admin Paneli</a></li>
                                    <li><hr class="dropdown-divider"></li>
<?php endif; ?>
                                <li><a class="dropdown-item text-danger" href="logout.php">Çıkış Yap</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item ms-lg-3">
                            <a class="nav-login" href="login.php" aria-label="Giriş yap">
                                <svg class="login-icon" viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M20 21a8 8 0 0 0-16 0"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg>
                                <span>Giriş Yap</span>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero -->
    <header id="anasayfa" class="hero">
        <div id="heroCarousel" class="carousel slide carousel-fade h-100" data-bs-ride="carousel">
            <div class="carousel-indicators">
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Birinci kare"></button>
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1" aria-label="İkinci kare"></button>
            </div>

            <div class="carousel-inner h-100">
                <div class="carousel-item active h-100">
                    <div class="hero-slide" style="background-image: url('assets/images/coffebean.jpg');">
                        <div class="hero-overlay"></div>
                        <div class="container hero-content">
                            <span class="hero-subtitle">Hoş Geldiniz</span>
                            <h1>Kahveyle başlayan dostluklar</h1>
                            <p>Dostum Kafe; taze kahvesi, günlük tatlıları ve sakin atmosferiyle şehir içinde kendinize ayırdığınız keyifli bir durak.</p>
                            <div class="d-flex flex-wrap gap-3 justify-content-center">
                                <a href="menu.php" class="btn btn-brand btn-lg">Menüyü Keşfet</a>
                                <a href="#hakkimizda" class="btn btn-outline-light btn-lg">Bizi Keşfedin</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="carousel-item h-100">
                    <div class="hero-slide" style="background-image: url('assets/images/hero-2.jpg');">
                        <div class="hero-overlay"></div>
                        <div class="container hero-content">
                            <span class="hero-subtitle">Dostum Kafe</span>
                            <h1>İyi kahve, sıcak ortam, tanıdık bir his</h1>
                            <p>Kısa bir mola, uzun bir sohbet ya da tek başına geçirilen sakin bir saat. Burada her ziyaretin kendine ait bir tadı var.</p>
                            <div class="d-flex flex-wrap gap-3 justify-content-center">
                                <a href="menu.php" class="btn btn-brand btn-lg">Menüyü Keşfet</a>
                                <a href="#iletisim" class="btn btn-outline-light btn-lg">Bize Ulaşın</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Bilgi alanı -->
    <section id="iletisim" class="info-strip">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="info-item">
                        <svg class="info-icon" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.8 19.8 0 0 1-8.63-3.07 19.4 19.4 0 0 1-6-6A19.8 19.8 0 0 1 2.12 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.12.91.33 1.8.62 2.65a2 2 0 0 1-.45 2.11L8 9.76a16 16 0 0 0 6.24 6.24l1.28-1.28a2 2 0 0 1 2.11-.45c.85.29 1.74.5 2.65.62A2 2 0 0 1 22 16.92z"></path>
                        </svg>
                        <div>
                            <h2>Telefon</h2>
                            <p>0999 999 99 99</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-item">
                        <svg class="info-icon" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M21 10c0 7-9 12-9 12S3 17 3 10a9 9 0 1 1 18 0z"></path>
                            <circle cx="12" cy="10" r="3"></circle>
                        </svg>
                        <div>
                            <h2>Adres</h2>
                            <p>Örnek Caddesi No: 128, Kadıköy / İstanbul</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-item">
                        <svg class="info-icon" viewBox="0 0 24 24" aria-hidden="true">
                            <circle cx="12" cy="12" r="10"></circle>
                            <path d="M12 6v6l4 2"></path>
                        </svg>
                        <div>
                            <h2>Çalışma Saatleri</h2>
                            <p>Hafta içi 08:30 - 23:00, hafta sonu 09:30 - 00:00.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Hakkımızda -->
    <section id="hakkimizda" class="about-section">
        <div class="container-fluid px-0">
            <div class="row g-0 align-items-stretch">
                <div class="col-lg-6">
                    <div class="about-image"></div>
                </div>
                <div class="col-lg-6">
                    <div class="about-content">
                        <div class="story-panel">
                            <span class="section-kicker">Keşfet</span>
                            <h2>Dostum Kafe'nin hikayesi.</h2>
                            <p>Dostum Kafe, iyi kahveyi güzel sohbetlerle buluşturma fikrinden doğdu. Her şeyin hızlı aktığı şehir hayatında, kendinize ayırdığınız küçük anları değerli kılmak istiyoruz.</p>
                            <p>Bu yüzden menümüzde taze kahveler, günlük tatlılar ve rahat hissettiren lezzetler var. Kapıdan giren herkesin kısa da olsa iyi hislerle ayrılmasını önemsiyoruz.</p>
                            <a href="menu.php" class="btn btn-outline-brand">Menüyü Keşfet</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Menü tanıtımı -->
    <section id="menu" class="menu-section">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-5">
                    <span class="section-kicker">Menü</span>
                    <h2>Dostum Kafe favorileri.</h2>
                    <p>Menüde güne eşlik eden klasik kahveler, serinleten soğuk içecekler ve kahvenin yanına yakışan tatlılar var. Tanıdık lezzetler, Dostum Kafe dokunuşuyla sunulur.</p>
                    <a href="menu.php" class="btn btn-brand">Menüyü Keşfet</a>
                </div>
                <div class="col-lg-7">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <article class="menu-card">
                                <img src="assets/images/menu-1.jpg" alt="Dostum latte">
                                <div>
                                    <h3>Dostum Latte</h3>
                                    <p>Yumuşak içimli, sütlü ve günün her saatine yakışan dengeli bir kahve.</p>
                                </div>
                            </article>
                        </div>
                        <div class="col-sm-6">
                            <article class="menu-card">
                                <img src="assets/images/menu-2.jpg" alt="Filtre kahve">
                                <div>
                                    <h3>Filtre Kahve</h3>
                                    <p>Taze çekilmiş kahvenin sade, net ve güçlü hali.</p>
                                </div>
                            </article>
                        </div>
                        <div class="col-sm-6">
                            <article class="menu-card">
                                <img src="assets/images/menu-3.jpg" alt="Cold brew">
                                <div>
                                    <h3>Cold Brew</h3>
                                    <p>Uzun demlenen kahvenin ferah, yoğun aromalı ve serinleten hali.</p>
                                </div>
                            </article>
                        </div>
                        <div class="col-sm-6">
                            <article class="menu-card">
                                <img src="assets/images/menu-4.jpg" alt="San Sebastian cheesecake">
                                <div>
                                    <h3>San Sebastian</h3>
                                    <p>Yanık üst dokusu ve yumuşak iç kıvamıyla kahve molasını tamamlar.</p>
                                </div>
                            </article>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Sayaç -->
    <section class="stats-section">
        <div class="container">
            <div class="row g-4 text-center">
                <div class="col-6 col-lg-3">
                    <div class="stat-item">
                        <strong>22</strong>
                        <span>Özel Lezzet</span>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="stat-item">
                        <strong>8</strong>
                        <span>Kahve Çeşidi</span>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="stat-item">
                        <strong>120</strong>
                        <span>Günlük Misafir</span>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="stat-item">
                        <strong>15</strong>
                        <span>Konforlu Masa</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="site-footer">
        <div class="container">
            <div class="row g-4 align-items-start">
                <div class="col-lg-4 footer-brand">
                    <img src="dostum_images/DOSTUMKAFE_NOBG_logo.png" alt="Dostum Kafe logo" class="footer-logo">
                    <p>Dostum Kafe, iyi kahveyi sıcak bir atmosferle buluşturan modern ve samimi bir buluşma noktasıdır.</p>
                </div>
                <div class="col-md-4 col-lg-3">
                    <div class="footer-info">
                        <svg class="footer-icon" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M21 10c0 7-9 12-9 12S3 17 3 10a9 9 0 1 1 18 0z"></path>
                            <circle cx="12" cy="10" r="3"></circle>
                        </svg>
                        <div>
                            <h2>Adres</h2>
                            <p>Örnek Caddesi No: 128, Kadıköy / İstanbul</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-lg-2">
                    <div class="footer-info">
                        <svg class="footer-icon" viewBox="0 0 24 24" aria-hidden="true">
                            <circle cx="12" cy="12" r="10"></circle>
                            <path d="M12 6v6l4 2"></path>
                        </svg>
                        <div>
                            <h2>Saatler</h2>
                            <p>Hafta içi 08:30 - 23:00</p>
                            <p>Hafta sonu 09:30 - 00:00</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-lg-3">
                    <h2>Sosyal Medya</h2>
                    <div class="social-links">
                        <a href="#" aria-label="Instagram">
                            <svg class="social-icon" viewBox="0 0 24 24" aria-hidden="true">
                                <rect x="3" y="3" width="18" height="18" rx="5"></rect>
                                <circle cx="12" cy="12" r="4"></circle>
                                <circle cx="17.5" cy="6.5" r="1"></circle>
                            </svg>
                            Instagram
                        </a>
                        <a href="#" aria-label="X">
                            <svg class="social-icon" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M4 4l16 16"></path>
                                <path d="M20 4L4 20"></path>
                            </svg>
                            X
                        </a>
                        <a href="#" aria-label="Facebook">
                            <svg class="social-icon" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M14 8h3V4h-3c-3 0-5 2-5 5v3H6v4h3v4h4v-4h3l1-4h-4V9c0-.6.4-1 1-1z"></path>
                            </svg>
                            Facebook
                        </a>
                        <a href="#" aria-label="TikTok">
                            <svg class="social-icon" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M14 4v10.5a4.5 4.5 0 1 1-4.5-4.5"></path>
                                <path d="M14 4c1 3 3 4.5 6 4.8"></path>
                            </svg>
                            TikTok
                        </a>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <span>&copy; <?php echo $currentYear; ?> Dostum Kafe. Tüm hakları saklıdır.</span>
            </div>
        </div>
    </footer>

    <!-- Sayfa sonu -->
    <div class="page-end"></div>

    <!-- Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
