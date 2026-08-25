<?php
// ==========================================================
// SUBLORA - صفحة تفاصيل المنتج
// ==========================================================

require_once 'config.php';
require_once 'functions.php';

trackVisitor($_SERVER['REQUEST_URI']);

$productId = $_GET['id'] ?? 1;

$products = [
    1 => [
        'id' => 1,
        'name' => 'T-shirt',
        'icon' => '👕',
        'price' => 89,
        'description' => 'تيشيرت عالي الجودة مع طباعة DTF احترافية',
        'fullDescription' => 'تيشيرتنا مصنوع من أجود أنواع القطن 100% مع طباعة DTF متقدمة توفر ألوان زاهية وثابتة. متوفر بجميع الأحجام والألوان.',
        'colors' => ['أسود', 'أبيض', 'أحمر', 'أزرق', 'أخضر'],
        'sizes' => ['XS', 'S', 'M', 'L', 'XL', 'XXL'],
        'features' => ['مادة 100% قطن', 'طباعة DTF عالية الجودة', 'ألوان ثابتة', 'متوفر بـ 5 ألوان', 'توصيل سريع']
    ],
    2 => [
        'id' => 2,
        'name' => 'Mug',
        'icon' => '☕',
        'price' => 49,
        'description' => 'كوب سيراميك مع طباعة مخصصة',
        'fullDescription' => 'كوب سيراميك عالي الجودة مع طباعة مخصصة تدوم طويلاً. مثالي كهدية أو للاستخدام الشخصي.',
        'colors' => ['أبيض'],
        'sizes' => ['250ml', '350ml'],
        'features' => ['سيراميك عالي الجودة', 'طباعة دائمة', 'آمن غسالة الأطباق', 'مناسب كهدية', 'تصاميم حصرية']
    ],
    3 => [
        'id' => 3,
        'name' => 'Casquette',
        'icon' => '🧢',
        'price' => 59,
        'description' => 'كابتة رياضية مع تطريز وطباعة',
        'fullDescription' => 'كابتة رياضية مريحة مع خيارات تطريز وطباعة عالية الجودة.',
        'colors' => ['أسود', 'أبيض', 'أحمر', 'أزرق'],
        'sizes' => ['OneSize'],
        'features' => ['مادة تنفس جيدة', 'تطريز حرفي', 'مريحة وخفيفة', 'حماية من الشمس', 'تصاميم عصرية']
    ],
    4 => [
        'id' => 4,
        'name' => 'Tote Bag',
        'icon' => '🛍️',
        'price' => 69,
        'description' => 'شنطة قماش مع طباعة مخصصة',
        'fullDescription' => 'شنطة قماش متينة مع طباعة مخصصة. مثالية للتسوق أو الاستخدام اليومي.',
        'colors' => ['أسود', 'أبيض', 'بني', 'رمادي'],
        'sizes' => ['OneSize'],
        'features' => ['قماش متين', 'طباعة واضحة', 'حمل مريح', 'صديقة للبيئة', 'قابلة للغسيل']
    ]
];

$product = $products[$productId] ?? $products[1];
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo $product['name']; ?> - SUBLORA</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&display=swap" rel="stylesheet" />
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        :root {
            --primary: #0a0a1a;
            --secondary: #f5a623;
            --accent: #25d366;
            --white: #ffffff;
            --gray-50: #faf8f5;
            --gray-100: #f0eee8;
            --gray-200: #e0ddd5;
            --gray-600: #7a7670;
            --transition: all 0.3s ease;
        }
        html { scroll-behavior:smooth; }
        body { font-family:'Cairo',sans-serif; background:var(--gray-50); color:var(--primary); }
        .container { max-width:1200px; margin:0 auto; padding:0 24px; }

        /* Header */
        .header {
            position:fixed; top:0; left:0; right:0; z-index:1000;
            padding:14px 24px; background:rgba(10,10,26,0.95);
            display:flex; justify-content:space-between; align-items:center;
        }
        .logo { font-size:1.5rem; font-weight:900; color:var(--white); text-decoration:none; }
        .logo span { color:var(--secondary); }
        .nav { display:flex; gap:20px; align-items:center; }
        .nav a { color:var(--white); text-decoration:none; transition:var(--transition); }
        .nav a:hover { color:var(--secondary); }

        /* Breadcrumb */
        .breadcrumb { padding:80px 0 20px; }
        .breadcrumb a { color:var(--secondary); text-decoration:none; }

        /* Product Section */
        .product-section { display:grid; grid-template-columns:1fr 1fr; gap:50px; align-items:start; margin:40px 0; }
        .product-image {
            width:100%; max-width:400px; aspect-ratio:1/1;
            background:linear-gradient(135deg, var(--primary), #14142e);
            border-radius:20px; display:flex; align-items:center; justify-content:center;
            font-size:6rem; box-shadow:0 8px 30px rgba(0,0,0,0.15);
        }

        .product-details h1 { font-size:2.2rem; font-weight:900; margin-bottom:10px; }
        .product-details .price { font-size:2rem; font-weight:900; color:var(--secondary); margin-bottom:20px; }
        .product-details .rating { margin-bottom:20px; color:var(--gray-600); }
        .product-details p { color:var(--gray-600); line-height:1.8; margin-bottom:20px; }

        .options-group { margin:30px 0; }
        .options-group label { display:block; font-weight:700; margin-bottom:10px; }
        .options-group select, .options-group input {
            width:100%; padding:12px; border:2px solid var(--gray-200);
            border-radius:8px; font-size:1rem; margin-bottom:15px;
        }

        .quantity-selector { display:flex; gap:10px; margin-bottom:30px; align-items:center; }
        .qty-btn { width:40px; height:40px; border:2px solid var(--gray-200); background:var(--white); cursor:pointer; border-radius:6px; font-weight:700; transition:var(--transition); }
        .qty-btn:hover { border-color:var(--secondary); }
        .qty-input { width:60px; text-align:center; border:2px solid var(--gray-200); padding:10px; border-radius:6px; }

        .btn-add-cart {
            width:100%; padding:16px; background:var(--secondary); color:var(--primary);
            border:none; border-radius:8px; font-size:1.1rem; font-weight:800;
            cursor:pointer; transition:var(--transition); margin-bottom:15px;
        }
        .btn-add-cart:hover { background:#d48a1a; }

        .btn-whatsapp {
            width:100%; padding:16px; background:var(--accent); color:var(--white);
            border:none; border-radius:8px; font-size:1.1rem; font-weight:800;
            cursor:pointer; transition:var(--transition);
            display:flex; align-items:center; justify-content:center; gap:10px;
        }
        .btn-whatsapp:hover { background:#1ebe5a; }

        .features {
            background:var(--white); padding:20px; border-radius:12px; margin-top:30px;
            border-left:4px solid var(--secondary);
        }
        .features ul { list-style:none; }
        .features li { padding:8px 0; }
        .features li:before { content:"✓ "; color:var(--secondary); font-weight:bold; margin-left:8px; }

        @media (max-width:800px) {
            .product-section { grid-template-columns:1fr; gap:30px; }
            .product-details h1 { font-size:1.6rem; }
        }
    </style>
</head>
<body>

<!-- HEADER -->
<header class="header">
    <a href="index.php" class="logo"><i class="fas fa-print"></i> SUB<span>LORA</span></a>
    <nav class="nav">
        <a href="index.php">الرئيسية</a>
        <a href="shop.php">المتجر</a>
        <a href="cart.php"><i class="fas fa-shopping-cart"></i></a>
    </nav>
</header>

<!-- BREADCRUMB -->
<div class="breadcrumb">
    <div class="container">
        <a href="index.php">الرئيسية</a> / <a href="shop.php">المتجر</a> / <span><?php echo $product['name']; ?></span>
    </div>
</div>

<!-- PRODUCT -->
<div class="container">
    <div class="product-section">
        <div class="product-image"><?php echo $product['icon']; ?></div>
        
        <div class="product-details">
            <h1><?php echo $product['name']; ?></h1>
            <div class="price"><?php echo formatPrice($product['price']); ?></div>
            <div class="rating">⭐⭐⭐⭐⭐ (47 تقييم)</div>
            <p><?php echo $product['fullDescription']; ?></p>

            <form id="customizationForm">
                <div class="options-group">
                    <label for="color">🎨 اختر اللون:</label>
                    <select id="color" required>
                        <option value="">-- اختر اللون --</option>
                        <?php foreach ($product['colors'] as $color): ?>
                            <option value="<?php echo $color; ?>"><?php echo $color; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <?php if ($product['sizes'][0] !== 'OneSize'): ?>
                <div class="options-group">
                    <label for="size">📏 اختر المقاس:</label>
                    <select id="size" required>
                        <option value="">-- اختر المقاس --</option>
                        <?php foreach ($product['sizes'] as $size): ?>
                            <option value="<?php echo $size; ?>"><?php echo $size; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>

                <div class="options-group">
                    <label for="customText">✏️ نص مخصص (اختياري):</label>
                    <input type="text" id="customText" placeholder="أضف نصاً أو لوجو" maxlength="50">
                </div>

                <div class="options-group">
                    <label for="extraPrint">🖨️ طباعة إضافية:</label>
                    <select id="extraPrint">
                        <option value="">بدون إضافة</option>
                        <option value="10">طباعة إضافية +10 DH</option>
                        <option value="20">طباعة متعددة الألوان +20 DH</option>
                    </select>
                </div>

                <div class="quantity-selector">
                    <span style="font-weight:700;">الكمية:</span>
                    <button type="button" class="qty-btn" onclick="decreaseQty()">-</button>
                    <input type="number" id="quantity" value="1" min="1" max="100" class="qty-input" readonly>
                    <button type="button" class="qty-btn" onclick="increaseQty()">+</button>
                </div>

                <button type="button" class="btn-add-cart" onclick="addToCart()">
                    <i class="fas fa-shopping-cart"></i> أضف إلى السلة
                </button>

                <button type="button" class="btn-whatsapp" onclick="orderViaWhatsApp()">
                    <i class="fab fa-whatsapp"></i> اطلب عبر واتس
                </button>
            </form>

            <div class="features">
                <h3 style="margin-bottom:15px;">✨ مميزات المنتج</h3>
                <ul>
                    <?php foreach ($product['features'] as $feature): ?>
                        <li><?php echo $feature; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
function increaseQty() {
    let qty = parseInt(document.getElementById('quantity').value);
    document.getElementById('quantity').value = qty + 1;
}

function decreaseQty() {
    let qty = parseInt(document.getElementById('quantity').value);
    if (qty > 1) document.getElementById('quantity').value = qty - 1;
}

function addToCart() {
    let color = document.getElementById('color').value;
    let size = document.getElementById('size') ? document.getElementById('size').value : 'OneSize';
    let customText = document.getElementById('customText').value;
    let extraPrint = parseInt(document.getElementById('extraPrint').value) || 0;
    let qty = parseInt(document.getElementById('quantity').value);

    if (!color) {
        alert('يرجى اختيار اللون');
        return;
    }
    if (document.getElementById('size') && !size) {
        alert('يرجى اختيار المقاس');
        return;
    }

    let cart = JSON.parse(localStorage.getItem('cart')) || [];
    let price = <?php echo $product['price']; ?> + extraPrint;
    
    cart.push({
        id: <?php echo $product['id']; ?>,
        name: '<?php echo $product['name']; ?>',
        price: price,
        color: color,
        size: size,
        customText: customText,
        extraPrint: extraPrint,
        quantity: qty
    });

    localStorage.setItem('cart', JSON.stringify(cart));
    alert('✅ تم إضافة المنتج إلى السلة');
    window.location.href = 'cart.php';
}

function orderViaWhatsApp() {
    let color = document.getElementById('color').value;
    let size = document.getElementById('size') ? document.getElementById('size').value : 'OneSize';
    let customText = document.getElementById('customText').value;
    let qty = parseInt(document.getElementById('quantity').value);

    if (!color) {
        alert('يرجى اختيار اللون');
        return;
    }

    let message = `أريد طلب ${qty} ${document.querySelector('h1').textContent}\\n`;
    message += `اللون: ${color}\\n`;
    message += `المقاس: ${size}\\n`;
    if (customText) message += `النص المخصص: ${customText}\\n`;
    message += `السعر: <?php echo formatPrice($product['price']); ?>`;

    window.open('https://wa.me/212700618383?text=' + encodeURIComponent(message), '_blank');
}
</script>
</body>
</html>