<?php
// ==========================================================
// SUBLORA - صفحة السلة
// ==========================================================

require_once 'config.php';
require_once 'functions.php';

trackVisitor($_SERVER['REQUEST_URI']);

// معالجة الحذف من السلة
if (isset($_GET['remove'])) {
    $cart = json_decode($_COOKIE['cart'] ?? '[]', true);
    unset($cart[$_GET['remove']]);
    setcookie('cart', json_encode(array_values($cart)), time() + 86400*30, '/');
    header('Location: cart.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>السلة - SUBLORA</title>
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
            --danger: #e74c3c;
            --transition: all 0.3s ease;
        }
        html { scroll-behavior:smooth; }
        body { font-family:'Cairo',sans-serif; background:var(--gray-50); color:var(--primary); line-height:1.7; }
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

        /* Page Title */
        .page-title { padding:100px 0 30px; text-align:center; }
        .page-title h1 { font-size:2.2rem; font-weight:900; }

        /* Cart Layout */
        .cart-layout { display:grid; grid-template-columns:2fr 1fr; gap:30px; margin:40px 0; }

        /* Cart Items */
        .cart-items { background:var(--white); border-radius:12px; padding:20px; }
        .cart-items h2 { margin-bottom:20px; font-size:1.3rem; }
        .empty-cart { text-align:center; padding:60px 20px; }
        .empty-cart-icon { font-size:4rem; color:var(--gray-200); margin-bottom:20px; }
        .empty-cart p { color:var(--gray-600); margin-bottom:20px; }
        .btn-continue { background:var(--primary); color:var(--white); padding:12px 30px; border-radius:8px; text-decoration:none; display:inline-block; transition:var(--transition); }
        .btn-continue:hover { background:var(--secondary); color:var(--primary); }

        .cart-item {
            display:flex; gap:20px; padding:20px; border-bottom:1px solid var(--gray-200);
            align-items:flex-start;
        }
        .cart-item:last-child { border-bottom:none; }
        .item-icon { font-size:3rem; }
        .item-details { flex:1; }
        .item-name { font-weight:700; font-size:1.1rem; margin-bottom:5px; }
        .item-spec { font-size:0.9rem; color:var(--gray-600); margin:3px 0; }
        .item-price { font-size:1.2rem; font-weight:800; color:var(--secondary); margin-top:10px; }

        .item-actions {
            display:flex; gap:10px; align-items:center; margin-top:10px;
        }
        .qty-control { display:flex; gap:5px; align-items:center; }
        .qty-btn { width:30px; height:30px; border:1px solid var(--gray-200); background:var(--white); cursor:pointer; border-radius:4px; }
        .qty-btn:hover { background:var(--gray-100); }
        .qty-display { width:40px; text-align:center; font-weight:700; }
        .btn-remove { background:var(--danger); color:var(--white); border:none; padding:6px 12px; border-radius:4px; cursor:pointer; transition:var(--transition); }
        .btn-remove:hover { opacity:0.8; }

        /* Summary */
        .summary { background:var(--white); border-radius:12px; padding:20px; height:fit-content; }
        .summary h2 { margin-bottom:20px; font-size:1.3rem; }
        .summary-line { display:flex; justify-content:space-between; margin-bottom:15px; padding-bottom:15px; border-bottom:1px solid var(--gray-200); }
        .summary-line:last-of-type { border-bottom:2px solid var(--secondary); }
        .summary-label { color:var(--gray-600); }
        .summary-value { font-weight:700; }
        .summary-total { display:flex; justify-content:space-between; margin-top:20px; font-size:1.4rem; font-weight:900; }
        .summary-total .value { color:var(--secondary); }

        .payment-method {
            background:var(--gray-50); padding:15px; border-radius:8px; margin:20px 0;
            border:2px solid var(--gray-200);
        }
        .payment-method label { display:flex; align-items:center; gap:10px; cursor:pointer; font-weight:600; }
        .payment-method input { cursor:pointer; }

        .btn-checkout {
            width:100%; padding:16px; background:var(--secondary); color:var(--primary);
            border:none; border-radius:8px; font-size:1.1rem; font-weight:800;
            cursor:pointer; transition:var(--transition); margin-top:20px;
        }
        .btn-checkout:hover { background:#d48a1a; }

        /* Responsive */
        @media (max-width:800px) {
            .cart-layout { grid-template-columns:1fr; }
            .page-title h1 { font-size:1.6rem; }
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
        <a href="cart.php" style="color:var(--secondary);">🛒 السلة</a>
    </nav>
</header>

<!-- PAGE TITLE -->
<div class="page-title">
    <div class="container">
        <h1>🛒 سلتك</h1>
    </div>
</div>

<!-- CONTENT -->
<div class="container">
    <div class="cart-layout">
        <!-- CART ITEMS -->
        <div class="cart-items">
            <h2>المنتجات (<span id="itemCount">0</span>)</h2>
            <div id="cartContent">
                <div class="empty-cart">
                    <div class="empty-cart-icon"><i class="fas fa-shopping-cart"></i></div>
                    <h3>السلة فارغة</h3>
                    <p>لم تضف أي منتجات بعد</p>
                    <a href="shop.php" class="btn-continue">تابع التسوق</a>
                </div>
            </div>
        </div>

        <!-- SUMMARY -->
        <div class="summary">
            <h2>ملخص الطلب</h2>
            
            <div class="summary-line">
                <span class="summary-label">المجموع الفرعي:</span>
                <span class="summary-value" id="subtotal">0 DH</span>
            </div>

            <div class="summary-line">
                <span class="summary-label">التوصيل:</span>
                <span class="summary-value" id="shipping">0 DH</span>
            </div>

            <div class="summary-total">
                <span>الإجمالي:</span>
                <span class="value" id="total">0 DH</span>
            </div>

            <div style="background:#d4edda;color:#155724;padding:10px;border-radius:6px;font-size:0.85rem;margin:15px 0;">
                ℹ️ توصيل مجاني للطلبات فوق 300 DH
            </div>

            <div>
                <label style="font-weight:700;margin-bottom:10px;display:block;">طريقة الدفع:</label>
                
                <div class="payment-method">
                    <label>
                        <input type="radio" name="payment" value="cash" checked>
                        💵 الدفع عند الاستلام
                    </label>
                </div>

                <div class="payment-method">
                    <label>
                        <input type="radio" name="payment" value="bank">
                        🏦 التحويل البنكي
                    </label>
                </div>
            </div>

            <button class="btn-checkout" onclick="proceedToCheckout()">
                <i class="fas fa-arrow-left"></i> انتقل إلى الدفع
            </button>

            <a href="shop.php" style="display:block;text-align:center;margin-top:15px;color:var(--secondary);text-decoration:none;font-weight:600;">
                ← تابع التسوق
            </a>
        </div>
    </div>
</div>

<script>
function loadCart() {
    let cart = JSON.parse(localStorage.getItem('cart')) || [];
    let cartHTML = '';
    let subtotal = 0;

    if (cart.length === 0) {
        document.getElementById('cartContent').innerHTML = `
            <div class="empty-cart">
                <div class="empty-cart-icon"><i class="fas fa-shopping-cart"></i></div>
                <h3>السلة فارغة</h3>
                <p>لم تضف أي منتجات بعد</p>
                <a href="shop.php" class="btn-continue">تابع التسوق</a>
            </div>
        `;
        document.getElementById('itemCount').textContent = '0';
        updateSummary(0);
        return;
    }

    cart.forEach((item, index) => {
        let itemTotal = (item.price || 0) * (item.quantity || 1);
        subtotal += itemTotal;
        
        cartHTML += `
            <div class="cart-item">
                <div class="item-icon">${item.icon || '📦'}</div>
                <div class="item-details">
                    <div class="item-name">${item.name}</div>
                    <div class="item-spec">🎨 اللون: ${item.color || 'بدون'}</div>
                    <div class="item-spec">📏 المقاس: ${item.size || 'OneSize'}</div>
                    ${item.customText ? `<div class="item-spec">✏️ النص: ${item.customText}</div>` : ''}
                    ${item.extraPrint ? `<div class="item-spec">🖨️ طباعة إضافية: +${item.extraPrint} DH</div>` : ''}
                    <div class="item-price">${formatPrice(item.price || 0)}</div>
                    <div class="item-actions">
                        <div class="qty-control">
                            <button class="qty-btn" onclick="updateQty(${index}, -1)">-</button>
                            <span class="qty-display" id="qty-${index}">${item.quantity || 1}</span>
                            <button class="qty-btn" onclick="updateQty(${index}, 1)">+</button>
                        </div>
                        <button class="btn-remove" onclick="removeItem(${index})">
                            <i class="fas fa-trash"></i> حذف
                        </button>
                    </div>
                </div>
            </div>
        `;
    });

    document.getElementById('cartContent').innerHTML = cartHTML;
    document.getElementById('itemCount').textContent = cart.length;
    updateSummary(subtotal);
}

function updateQty(index, change) {
    let cart = JSON.parse(localStorage.getItem('cart')) || [];
    let newQty = (cart[index].quantity || 1) + change;
    if (newQty < 1) newQty = 1;
    cart[index].quantity = newQty;
    localStorage.setItem('cart', JSON.stringify(cart));
    document.getElementById('qty-' + index).textContent = newQty;
    loadCart();
}

function removeItem(index) {
    if (confirm('هل تريد حذف هذا المنتج من السلة؟')) {
        let cart = JSON.parse(localStorage.getItem('cart')) || [];
        cart.splice(index, 1);
        localStorage.setItem('cart', JSON.stringify(cart));
        loadCart();
    }
}

function updateSummary(subtotal) {
    let shipping = subtotal >= 300 ? 0 : 20;
    let total = subtotal + shipping;
    
    document.getElementById('subtotal').textContent = formatPrice(subtotal);
    document.getElementById('shipping').textContent = shipping === 0 ? 'مجاني ✅' : formatPrice(shipping);
    document.getElementById('total').textContent = formatPrice(total);
}

function formatPrice(price) {
    return Number(price).toFixed(2) + ' DH';
}

function proceedToCheckout() {
    let cart = JSON.parse(localStorage.getItem('cart')) || [];
    if (cart.length === 0) {
        alert('السلة فارغة!');
        return;
    }

    let payment = document.querySelector('input[name="payment"]:checked').value;
    let message = '🛒 *طلب جديد من SUBLORA*\n\n';
    
    let subtotal = 0;
    cart.forEach(item => {
        message += `📦 ${item.name}\n`;
        message += `   🎨 اللون: ${item.color}\n`;
        message += `   📏 المقاس: ${item.size}\n`;
        message += `   الكمية: ${item.quantity}\n`;
        message += `   السعر: ${item.price * item.quantity} DH\n\n`;
        subtotal += item.price * item.quantity;
    });

    let shipping = subtotal >= 300 ? 0 : 20;
    let total = subtotal + shipping;

    message += `━━━━━━━━━━━━━━━━\n`;
    message += `المجموع الفرعي: ${subtotal} DH\n`;
    message += `التوصيل: ${shipping === 0 ? 'مجاني ✅' : shipping + ' DH'}\n`;
    message += `الإجمالي: ${total} DH\n`;
    message += `━━━━━━━━━━━━━━━━\n`;
    message += `💳 طريقة الدفع: ${payment === 'cash' ? 'الدفع عند الاستلام' : 'التحويل البنكي'}\n`;

    window.open('https://wa.me/212700618383?text=' + encodeURIComponent(message), '_blank');
}

loadCart();
</script>
</body>
</html>