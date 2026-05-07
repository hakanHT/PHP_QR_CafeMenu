<?php
session_start();
require_once 'config.php';

// Admin yetkisi kontrolü
if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit();
}

$success = null;
$error = null;
$billData = null;

// Ürünleri ve kategorileri çek
$products = $db->query("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id ORDER BY c.name, p.name")->fetchAll(PDO::FETCH_ASSOC);
$categories = [];
foreach ($products as $p) {
    $categories[$p['category_name']][] = $p;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['items_json'])) {
    $items = json_decode($_POST['items_json'], true);
    $code = trim($_POST['discount_code']);
    
    if (empty($items)) {
        $error = "Lütfen en az bir ürün seçin.";
    } else {
        $totalOriginal = 0;
        $totalDiscount = 0;
        $appliedDiscount = null;
        $discountedCategoryName = "";

        if (!empty($code)) {
            $stmt = $db->prepare("SELECT d.*, c.name as category_name FROM discounts d 
                                 JOIN categories c ON d.category_id = c.id 
                                 WHERE d.code = ? AND d.is_used = 0 AND d.expires_at > NOW()");
            $stmt->execute([$code]);
            $appliedDiscount = $stmt->fetch();
            
            if ($appliedDiscount) {
                $discountedCategoryName = $appliedDiscount['category_name'];
            } else {
                $error = "Geçersiz veya süresi dolmuş indirim kodu.";
            }
        }

        $orderSummary = [];
        foreach ($items as $item) {
            $p_id = $item['id'];
            $qty = $item['qty'];
            
            $stmt = $db->prepare("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.id = ?");
            $stmt->execute([$p_id]);
            $product = $stmt->fetch();
            
            if ($product) {
                $lineTotal = $product['price'] * $qty;
                $lineDiscount = 0;
                
                if ($appliedDiscount && $product['category_id'] == $appliedDiscount['category_id']) {
                    $lineDiscount = ($lineTotal * $appliedDiscount['discount_percent']) / 100;
                }
                
                $totalOriginal += $lineTotal;
                $totalDiscount += $lineDiscount;
                
                $orderSummary[] = [
                    'name' => $product['name'],
                    'qty' => $qty,
                    'price' => $product['price'],
                    'total' => $lineTotal,
                    'is_discounted' => ($lineDiscount > 0)
                ];
            }
        }

        if ($totalOriginal > 0) {
            // İndirim uygulanabilir mi kontrol et
            if ($appliedDiscount && $totalDiscount == 0) {
                $error = "Bu indirim kodu sadece '" . $discountedCategoryName . "' kategorisinde geçerlidir. Sepetinizde bu kategoriden ürün bulunmamaktadır.";
                $appliedDiscount = null; // Fişe yansımasın
            } elseif ($appliedDiscount && !$error) {
                // Kod geçerli ve sepette karşılığı var, yak gitsin
                $db->prepare("UPDATE discounts SET is_used = 1 WHERE id = ?")->execute([$appliedDiscount['id']]);
                $success = "İndirim kodu başarıyla uygulandı!";
            }

            $billData = [
                'items' => $orderSummary,
                'original' => $totalOriginal,
                'discount' => $totalDiscount,
                'final' => $totalOriginal - $totalDiscount,
                'discount_info' => $appliedDiscount ? ['percent' => $appliedDiscount['discount_percent'], 'category' => $discountedCategoryName] : null,
                'date' => date('d.m.Y H:i:s')
            ];
        }
    }
}
?>
<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Garson Paneli - Dostum Kafe</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { background-color: #f8f5f2; font-family: 'Outfit', sans-serif; color: #2d241e; }
        .pos-container { margin-top: 30px; margin-bottom: 50px; }
        .card-custom { border: none; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); background: #fff; }
        .product-item { 
            cursor: pointer; transition: 0.2s; border: 1px solid #eee; border-radius: 12px; padding: 10px; margin-bottom: 10px;
            display: flex; justify-content: space-between; align-items: center;
        }
        .product-item:hover { background: #fdfaf7; border-color: #c9a076; }
        .product-item .price { font-weight: 700; color: #c9a076; }
        .cart-item { display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px dashed #eee; }
        .cart-item:last-child { border-bottom: none; }
        .qty-btn { width: 30px; height: 30px; border-radius: 50%; border: 1px solid #ddd; background: #fff; display: flex; align-items: center; justify-content: center; font-weight: bold; cursor: pointer; }
        .qty-btn:hover { background: #f8f5f2; }
        .btn-brand { background: #c9a076; color: #fff; border: none; font-weight: 700; padding: 12px; border-radius: 12px; }
        .btn-brand:hover { background: #b08b63; color: #fff; }
        .category-title { font-weight: 700; color: #7d6e66; margin-top: 20px; margin-bottom: 10px; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px; }

        .receipt {
            background: #fff; border: 1px solid #eee; padding: 30px; border-radius: 10px;
            font-family: 'Courier New', Courier, monospace; position: relative;
            max-width: 380px; margin: 30px auto; /* Ekranda dar ve ortalı görünmesi için */
        }
        .receipt::before, .receipt::after {
            content: ''; position: absolute; left: 0; right: 0; height: 10px;
            background: linear-gradient(-45deg, transparent 5px, #fff 5px), linear-gradient(45deg, transparent 5px, #fff 5px);
            background-size: 10px 10px;
        }
        .receipt::before { top: -10px; transform: rotate(180deg); }
        .receipt::after { bottom: -10px; }
        .receipt-header { text-align: center; border-bottom: 1px dashed #ccc; padding-bottom: 15px; margin-bottom: 15px; }
        .receipt-line { display: flex; justify-content: space-between; margin-bottom: 5px; font-size: 0.9rem; }
        .receipt-total { border-top: 1px dashed #ccc; margin-top: 15px; padding-top: 15px; font-weight: bold; font-size: 1.1rem; }
        
        @media print {
            body { background: #fff !important; margin: 0; padding: 0; }
            body > *:not(#printableReceipt) { display: none !important; }
            #printableReceipt { 
                display: block !important;
                position: relative;
                margin: 20px auto;
                width: 100%;
                max-width: 350px;
                border: none;
                box-shadow: none;
            }
            @page { margin: 0; }
        }
    </style>
</head>
<body>

<div class="container pos-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold m-0">Garson Paneli</h2>
        <a href="admin_panel.php" class="btn btn-sm btn-outline-secondary rounded-pill px-3">← Panele Dön</a>
    </div>

    <div class="row">
        <div class="col-lg-7">
            <div class="card-custom p-4 mb-4">
                <h5 class="fw-bold mb-3">Menüden Seçin</h5>
                <div style="max-height: 600px; overflow-y: auto; padding-right: 10px;">
                    <?php if (empty($categories)): ?>
                        <div class="text-center py-5">
                            <p class="text-muted">Henüz ürün eklenmemiş.</p>
                            <a href="admin_products.php" class="btn btn-sm btn-brand">Ürün Ekle</a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($categories as $catName => $catProducts): ?>
                            <div class="category-title"><?php echo htmlspecialchars($catName); ?></div>
                            <div class="row g-2">
                                <?php foreach ($catProducts as $p): ?>
                                    <div class="col-md-6">
                                        <div class="product-item" onclick="addToCart(<?php echo $p['id']; ?>, '<?php echo addslashes($p['name']); ?>', <?php echo $p['price']; ?>)">
                                            <div>
                                                <div class="fw-bold"><?php echo htmlspecialchars($p['name']); ?></div>
                                                <small class="text-muted"><?php echo number_format($p['price'], 2); ?> TL</small>
                                            </div>
                                            <div class="price">+</div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card-custom p-4 sticky-top" style="top: 20px; z-index: 100;">
                <h5 class="fw-bold mb-4">Sipariş Özeti</h5>
                <div id="cartItems" class="mb-4">
                    <p class="text-muted text-center py-3">Henüz ürün seçilmedi.</p>
                </div>
                <form id="orderForm" method="POST">
                    <input type="hidden" name="items_json" id="itemsJson">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">İNDİRİM KODU</label>
                        <input type="text" name="discount_code" id="discountCodeInput" class="form-control" placeholder="DOSTUMXXX">
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-4 pt-3 border-top">
                        <span class="fw-bold">Tahmini Toplam:</span>
                        <span id="cartTotal" class="fs-4 fw-bold text-brand">0.00 TL</span>
                    </div>
                    <button type="submit" class="btn btn-brand w-100 py-3 shadow-sm">HESABI HESAPLA & FİŞ KES</button>
                </form>
                <?php if($error): ?><div class="alert alert-danger border-0 mt-3 small"><?php echo $error; ?></div><?php endif; ?>
                <?php if($success): ?><div class="alert alert-success border-0 mt-3 small"><?php echo $success; ?></div><?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php if($billData): ?>
    <div id="printableReceipt" class="receipt shadow-sm">
        <div class="receipt-header">
            <h5 class="fw-bold m-0">DOSTUM KAFE</h5>
            <small><?php echo $billData['date']; ?></small>
        </div>
        <div class="mb-3">
            <?php foreach($billData['items'] as $item): ?>
                <div class="receipt-line">
                    <span><?php echo $item['qty']; ?>x <?php echo htmlspecialchars($item['name']); ?></span>
                    <span><?php echo number_format($item['total'], 2); ?> TL</span>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="receipt-line border-top pt-2">
            <span>Ara Toplam:</span>
            <span><?php echo number_format($billData['original'], 2); ?> TL</span>
        </div>
        <?php if($billData['discount'] > 0): ?>
            <div class="receipt-line text-success fw-bold">
                <span>İndirim (%<?php echo $billData['discount_info']['percent']; ?> - <?php echo htmlspecialchars($billData['discount_info']['category']); ?>):</span>
                <span>-<?php echo number_format($billData['discount'], 2); ?> TL</span>
            </div>
        <?php endif; ?>
        <div class="receipt-total">
            <span>TOPLAM:</span>
            <span><?php echo number_format($billData['final'], 2); ?> TL</span>
        </div>
        <div class="text-center mt-4 small text-muted" style="font-family: sans-serif;">
            <p class="m-0">Bizi Tercih Ettiğiniz İçin Teşekkürler!</p>
            <p class="m-0">Afiyet Olsun.</p>
        </div>
    </div>
    <div class="text-center mt-3 no-print mb-5">
        <button class="btn btn-dark rounded-pill px-4" onclick="window.print()">Fişi Yazdır</button>
        <button class="btn btn-outline-secondary rounded-pill px-4" onclick="window.location.href='waiter_panel.php'">Yeni İşlem</button>
    </div>
<?php endif; ?>

<script>
    let cart = [];
    function addToCart(id, name, price) {
        let existing = cart.find(i => i.id === id);
        if (existing) { existing.qty++; } else { cart.push({ id, name, price, qty: 1 }); }
        renderCart();
    }
    function updateQty(id, delta) {
        let item = cart.find(i => i.id === id);
        if (item) {
            item.qty += delta;
            if (item.qty <= 0) { cart = cart.filter(i => i.id !== id); }
        }
        renderCart();
    }
    function renderCart() {
        const container = document.getElementById('cartItems');
        const totalEl = document.getElementById('cartTotal');
        const itemsJsonInput = document.getElementById('itemsJson');
        if (cart.length === 0) {
            container.innerHTML = '<p class="text-muted text-center py-3">Henüz ürün seçilmedi.</p>';
            totalEl.innerText = '0.00 TL';
            itemsJsonInput.value = '';
            return;
        }
        let html = '';
        let total = 0;
        cart.forEach(item => {
            let lineTotal = item.price * item.qty;
            total += lineTotal;
            html += `<div class="cart-item">
                <div style="flex: 1;">
                    <div class="fw-bold small">${item.name}</div>
                    <small class="text-muted">${item.price.toFixed(2)} TL</small>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <div class="qty-btn" onclick="updateQty(${item.id}, -1)">-</div>
                    <span class="fw-bold">${item.qty}</span>
                    <div class="qty-btn" onclick="updateQty(${item.id}, 1)">+</div>
                </div>
                <div class="ms-3 fw-bold small" style="min-width: 70px; text-align: right;">${lineTotal.toFixed(2)} TL</div>
            </div>`;
        });
        container.innerHTML = html;
        totalEl.innerText = total.toFixed(2) + ' TL';
        itemsJsonInput.value = JSON.stringify(cart);
    }
    document.getElementById('orderForm').addEventListener('submit', function(e) {
        if (cart.length === 0) {
            e.preventDefault();
            alert('Lütfen en az bir ürün seçin.');
        }
    });
</script>
</body>
</html>
