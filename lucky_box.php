<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];

// Son 7 gün içinde açmış mı kontrol et
$stmt = $db->prepare("SELECT opened_at FROM lucky_box_history WHERE user_id = ? ORDER BY opened_at DESC LIMIT 1");
$stmt->execute([$userId]);
$lastOpen = $stmt->fetch();

$canOpen = true;
$nextOpenDate = null;

if ($lastOpen) {
    $lastDate = strtotime($lastOpen['opened_at']);
    $diff = time() - $lastDate;
    if ($diff < (7 * 24 * 60 * 60)) {
        $canOpen = false;
        $nextOpenDate = date('d.m.Y H:i', $lastDate + (7 * 24 * 60 * 60));
    }
}

$categories = $db->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);
$discounts  = [15, 20, 25, 30, 50];
$hasCategories = count($categories) > 0;
?>
<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Şans Kutusu - Dostum Kafe</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --brand-gold: #c9a076;
            --dark-bg: #0d0b0a;
        }
        body {
            background-color: var(--dark-bg);
            color: #fff;
            font-family: 'Outfit', sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        .case-container {
            width: 100%;
            max-width: 800px;
            text-align: center;
            padding: 20px;
        }
        .case-title {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 30px;
            text-transform: uppercase;
            letter-spacing: 4px;
            background: linear-gradient(to bottom, #fff, var(--brand-gold));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* Slider Style */
        .case-slider-wrapper {
            position: relative;
            width: 100%;
            height: 160px;
            background: rgba(255,255,255,0.03);
            border: 2px solid rgba(201, 160, 118, 0.2);
            border-radius: 15px;
            overflow: hidden;
            margin: 40px 0;
        }
        .case-slider-wrapper::after {
            content: '';
            position: absolute;
            left: 50%;
            top: 0;
            bottom: 0;
            width: 4px;
            background: var(--brand-gold);
            z-index: 10;
            box-shadow: 0 0 20px var(--brand-gold);
            transform: translateX(-50%);
        }
        .slider-inner {
            display: flex;
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            transition: transform 8s cubic-bezier(0.1, 0, 0.1, 1);
        }
        .reward-item {
            min-width: 150px;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            border-right: 1px solid rgba(255,255,255,0.05);
            background: linear-gradient(45deg, rgba(201, 160, 118, 0.05), rgba(255,255,255,0.02));
        }
        .reward-item span { font-size: 1.2rem; font-weight: 600; }
        .reward-item small { color: var(--brand-gold); font-size: 0.8rem; text-transform: uppercase; }

        .btn-open {
            background: var(--brand-gold);
            color: #000;
            border: none;
            padding: 15px 50px;
            font-size: 1.2rem;
            font-weight: 800;
            border-radius: 50px;
            text-transform: uppercase;
            letter-spacing: 2px;
            transition: 0.3s;
            box-shadow: 0 10px 30px rgba(201, 160, 118, 0.3);
        }
        .btn-open:hover {
            transform: scale(1.05);
            background: #fff;
        }
        .btn-open:disabled {
            background: #333;
            color: #666;
            box-shadow: none;
            cursor: not-allowed;
        }

        .result-panel {
            display: none;
            margin-top: 30px;
            animation: fadeIn 1s forwards;
        }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

        .discount-code-wrapper {
            display: inline-flex;
            align-items: center;
            gap: 15px;
            margin: 20px 0;
            cursor: pointer;
            transition: 0.3s;
        }
        .discount-code-wrapper:hover { transform: scale(1.02); }

        .discount-code {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--brand-gold);
            letter-spacing: 8px;
            padding: 10px 30px;
            border: 2px dashed var(--brand-gold);
            background: rgba(201, 160, 118, 0.05);
        }
        
        .copy-btn {
            background: var(--brand-gold);
            color: #000;
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 15px rgba(201, 160, 118, 0.2);
        }
        .copy-toast {
            position: fixed; top: 20px; left: 50%; transform: translateX(-50%);
            background: #2ed573; color: #fff; padding: 10px 25px; border-radius: 50px;
            font-weight: 700; font-size: 14px; display: none; z-index: 9999;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }

        .back-link { color: #888; text-decoration: none; margin-top: 50px; transition: 0.3s; display: inline-block; }
        .back-link:hover { color: #fff; }
        .text-light-muted { color: rgba(255,255,255,0.7) !important; }
    </style>
</head>
<body>

    <div id="copyToast" class="copy-toast">Kupon Kodu Kopyalandı! ✓</div>

    <div class="case-container">
        <h1 class="case-title">Şans Kutusu</h1>
        
        <?php if ($canOpen && $hasCategories): ?>
            <p class="text-light-muted">Haftalık şansını dene, sürpriz indirimini kap!</p>
            
            <div class="case-slider-wrapper">
                <div class="slider-inner" id="slider">
                    <!-- JS will fill this -->
                </div>
            </div>

            <button id="openBtn" class="btn-open">KASAYI AÇ</button>

            <div id="resultPanel" class="result-panel">
                <h2 class="fw-bold">TEBRİKLER!</h2>
                <p id="resultText" class="text-light-muted"></p>
                
                <div class="discount-code-wrapper" onclick="copyCode()">
                    <div id="codeDisplay" class="discount-code">XXXXXX</div>
                    <div class="copy-btn">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                    </div>
                </div>

                <p class="small text-light-muted">Bu kodu garsona ödeme yaparken ilet. (1 hafta geçerli)<br>Koda tıklayarak kopyalayabilirsin.</p>
            </div>
        <?php elseif (!$hasCategories): ?>
            <div class="py-5">
                <h3 class="fw-bold mb-3">Henüz Kategori Yok</h3>
                <p class="text-muted">Admin panelinden önce en az bir kategori eklenmelidir.</p>
            </div>
        <?php else: ?>
            <div class="py-5">
                <h3 class="fw-bold text-brand mb-3">Bu haftalık katılım hakkınızı kullandınız.</h3>
                <p class="text-light-muted">Bir sonraki şans kutusu açılışı için lütfen bekleyin.</p>
                <p class="text-light-muted">Bir sonraki katılım hakkınız: <br> <strong class="text-white"><?php echo $nextOpenDate; ?></strong></p>
            </div>
        <?php endif; ?>

        <br>
        <a href="menu.php" class="back-link small">← Menüye Dön</a>
    </div>

    <script>
        const categories = <?php echo json_encode($categories); ?>;
        const discounts = <?php echo json_encode($discounts); ?>;
        const slider = document.getElementById('slider');
        const openBtn = document.getElementById('openBtn');
        const resultPanel = document.getElementById('resultPanel');
        const resultText = document.getElementById('resultText');
        const codeDisplay = document.getElementById('codeDisplay');

        // Create 100 random items for the slider
        const totalItems = 80;
        let finalItem = null;

        function initSlider() {
            for (let i = 0; i < totalItems; i++) {
                const randomCat = categories[Math.floor(Math.random() * categories.length)];
                const randomDisc = discounts[Math.floor(Math.random() * discounts.length)];
                
                const div = document.createElement('div');
                div.className = 'reward-item';
                div.innerHTML = `<span>%${randomDisc}</span><small>${randomCat.name}</small>`;
                slider.appendChild(div);

                if (i === totalItems - 5) { // The one that will land on center
                    finalItem = { catId: randomCat.id, catName: randomCat.name, disc: randomDisc };
                }
            }
        }

        initSlider();

        openBtn.addEventListener('click', function() {
            openBtn.disabled = true;
            
            const itemWidth    = 150;
            const wrapper      = document.querySelector('.case-slider-wrapper');
            const centerOffset = (wrapper.offsetWidth / 2) - (itemWidth / 2);
            const targetPos    = (totalItems - 5) * itemWidth - centerOffset;
            
            slider.style.transform = `translateX(-${targetPos}px)`;

            setTimeout(() => {
                // Save to DB via AJAX
                fetch('save_discount.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `cat_id=${finalItem.catId}&disc=${finalItem.disc}`
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        resultText.innerText = `${finalItem.catName} kategorisinde %${finalItem.disc} indirim kazandın!`;
                        codeDisplay.innerText = data.code;
                        resultPanel.style.display = 'block';
                        resultPanel.scrollIntoView({ behavior: 'smooth' });
                    }
                });
            }, 8500);
        });

        function copyCode() {
            const code = codeDisplay.innerText;
            if (code === "XXXXXX") return;

            navigator.clipboard.writeText(code).then(() => {
                const toast = document.getElementById('copyToast');
                toast.style.display = 'block';
                setTimeout(() => {
                    toast.style.display = 'none';
                }, 2500);
            });
        }
    </script>
</body>
</html>
