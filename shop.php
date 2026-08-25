<?php
// ==========================================================
// SUBLORA - صفحة المتجر
// ==========================================================

require_once 'config.php';
require_once 'functions.php';

trackVisitor($_SERVER['REQUEST_URI']);

// جلب جميع المنتجات
$products = getAllProducts();
$category = $_GET['category'] ?? 'all';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>المتجر - SUBLORA</title>
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
        body { font-family:'Cairo',sans-serif; background:var(--gray-50); color:var(--primary); line-height:1.7; }
        .container { max-width:1200px; margin:0 auto; padding:0 24px; }

        /* Header */
        .header {
            position:fixed; top:0; left:0; right:0; z-index:1000;
            padding:14px 0; background:rgba(10,10,26,0.95);
            display:flex; justify-content:space-between; align-items:center;
            padding:14px 24px;
        }
        .logo { font-size:1.5rem; font-weight:900; color:var(--white); text-decoration:none; display:flex; align-items:center; gap:8px; }
        .logo i { color:var(--secondary); }
        .logo span { color:var(--secondary); }
        .nav { display:flex; gap:20px; align-items:center; }
        .nav a { color:var(--white); text-decoration:none; transition:var(--transition); }
        .nav a:hover, .nav a.active { color:var(--secondary); }
        .cart-icon { position:relative; font-size:1.3rem; cursor:pointer; }
        .cart-count { position:absolute; top:-8px; right:-8px; background:var(--secondary); color:var(--primary); width:20px; height:20px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:0.75rem; font-weight:bold; }

        /* Page Title */
        .page-title { text-align:center; padding:120px 0 40px; margin-bottom:40px; }
        .page-title h1 { font-size:2.5rem; font-weight:900; margin-bottom:10px; }
        .page-title p { color:var(--gray-600); }

        /* Filters */
        .filters { display:flex; gap:10px; margin-bottom:30px; flex-wrap:wrap; }
        .filter-btn {
            padding:10px 20px; border:2px solid var(--gray-200); background:var(--white);
            border-radius:50px; cursor:pointer; transition:var(--transition); font-weight:600;
            font-family:inherit; font-size:1rem;
        }
        .filter-btn:hover { background:var(--secondary); color:var(--primary); border-color:var(--secondary); }
        .filter-btn.active {
            background:var(--secondary); color:var(--primary); border-color:var(--secondary);
        }

        /* Products Grid */
        .products-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(250px,1fr)); gap:24px; margin-bottom:50px; }
        .product-card {
            background:var(--white); border-radius:16px; overflow:hidden;
            box-shadow:0 2px 12px rgba(0,0,0,0.08); transition:var(--transition);
            cursor:pointer;
        }
        .product-card:hover { transform:translateY(-8px); box-shadow:0 8px 24px rgba(0,0,0,0.12); }
        .product-image {
            width:100%; height:200px; background:linear-gradient(135deg, var(--primary), #14142e);
            display:flex; align-items:center; justify-content:center; font-size:4rem; color:var(--white);
        }
        .product-info { padding:20px; }
        .product-name { font-size:1.1rem; font-weight:700; margin-bottom:8px; }
        .product-desc { font-size:0.9rem; color:var(--gray-600); margin-bottom:12px; }
        .product-price { font-size:1.4rem; font-weight:900; color:var(--secondary); margin-bottom:16px; direction:ltr; }
        .btn-add {
            width:100%; padding:12px; background:var(--primary); color:var(--white);
            border:none; border-radius:8px; cursor:pointer; font-weight:700;
            transition:var(--transition); font-family:inherit; font-size:1rem;
        }
        .btn-add:hover { background:#14142e; }

        /* Empty State */
        .empty-state { text-align:center; padding:60px 20px; }
        .empty-state-icon { font-size:4rem; color:var(--gray-200); margin-bottom:20px; }
        .empty-state h3 { font-size:1.5rem; color:var(--gray-600); margin-bottom:10px; }

        /* Footer */
        .footer { background:var(--white); padding:40px 0; text-align:center; margin-top:50px; border-top:1px solid var(--gray-200); }

        /* Floating Cart */
        .floating-cart {
            position:fixed; bottom:30px; left:30px; background:var(--accent);
            color:var(--white); width:60px; height:60px; border-radius:50%;
            display:flex; align-items:center; justify-content:center; font-size:1.5rem;
            box-shadow:0 4px 20px rgba(37,211,102,0.4); z-index:999; cursor:pointer;
            transition:var(--transition);
        }
        .floating-cart:hover { transform:scale(1.1); }

        @media (max-width:600px) {
            .products-grid { grid-template-columns:1fr; }
            .page-title h1 { font-size:1.8rem; }
            .nav { display:none; }
        }
    </style>
</head>
<body>

<!-- HEADER -->
<header class="header">
    <a href="index.php" class="logo"><i class="fas fa-print"></i> SUB<span>LORA</span></a>
    <nav class="nav">
        <a href="index.php">الرئيسية</a>
        <a href="shop.php" class="active">المتجر</a>
        <a href="cart.php" class="cart-icon">
            <i class="fas fa-shopping-cart"></i>
            <span class="cart-count" id="cartCount">0</span>
        </a>
    </nav>
</header>

<!-- PAGE TITLE -->
<div class="page-title" style="padding-top:100px;">
    <div class="container">
        <h1>🛍️ متجر SUBLORA</h1>
        <p>اختر منتجك واخصصه حسب رغبتك</p>
    </div>
</div>

<!-- CONTENT -->
<div class="container">
    <div class="filters">
        <button class="filter-btn active" onclick="filterCategory('all')">جميع المنتجات</button>
        <button class="filter-btn" onclick="filterCategory('shirts')">👕 تيشيرتات</button>
        <button class="filter-btn" onclick="filterCategory('accessories')">🎁 إكسسوارات</button>
    </div>

    <div class="products-grid" id="productsGrid">
        <?php if (empty($products)): ?>
            <div class="empty-state" style="grid-column:1/-1;">
                <div class="empty-state-icon"><i class="fas fa-box"></i></div>
                <h3>لا توجد منتجات</h3>
                <p>عذراً، لم نجد أي منتجات حالياً</p>
            </div>
        <?php else: ?>
            <?php foreach ($products as $product): ?>
            <div class="product-card" onclick="goToProduct(<?php echo $product['id']; ?>)">
                <div class="product-image"><?php echo $product['icon']; ?></div>
                <div class="product-info">
                    <div class="product-name"><?php echo $product['name']; ?></div>
                    <div class="product-desc"><?php echo $product['description']; ?></div>
                    <div class="product-price"><?php echo formatPrice($product['price']); ?></div>
                    <button class="btn-add" onclick="event.stopPropagation(); addToCart(<?php echo $product['id']; ?>, '<?php echo $product['name']; ?>', <?php echo $product['price']; ?>)">
                        <i class="fas fa-shopping-cart"></i> أضف للسلة
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- FLOATING CART -->
<div class="floating-cart" onclick="window.location.href='cart.php'" title="عرض السلة">
    <i class="fas fa-shopping-bag"></i>
</div>

<!-- FOOTER -->
<footer class="footer">
    <div class="container">
        <p><strong>SUBLORA</strong> © 2024 - جميع الحقوق محفوظة</p>
    </div>
</footer>

<script>
function goToProduct(id) {
    window.location.href = 'product.php?id=' + id;
}

function addToCart(id, name, price) {
    let cart = JSON.parse(localStorage.getItem('cart')) || [];
    let item = cart.find(i => i.id === id);
    if (item) {
        item.quantity++;
    } else {
        cart.push({ id, name, price, quantity: 1, icon: '📦' });
    }
    localStorage.setItem('cart', JSON.stringify(cart));
    updateCartCount();
    alert('✅ تمت إضافة ' + name + ' إلى السلة');
}

function updateCartCount() {
    let cart = JSON.parse(localStorage.getItem('cart')) || [];
    let count = cart.reduce((sum, item) => sum + (item.quantity || 1), 0);
    document.getElementById('cartCount').textContent = count;
}

function filterCategory(category) {
    document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
    event.target.classList.add('active');
    // هنا يمكن إضافة تصفية المنتجات حسب الفئة
}

updateCartCount();
</script>
</body>
</html>
