<?php
// ==========================================================
// SUBLORA - صفحة الدفع والتوصيل
// ==========================================================

require_once 'config.php';
require_once 'functions.php';

trackVisitor($_SERVER['REQUEST_URI']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // التحقق من CSRF
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        die('خطأ في الأمان');
    }

    // تنظيف البيانات
    $customerName = sanitizeInput($_POST['customer_name'] ?? '');
    $customerEmail = filter_var($_POST['customer_email'] ?? '', FILTER_SANITIZE_EMAIL);
    $customerPhone = sanitizeInput($_POST['customer_phone'] ?? '');
    $city = sanitizeInput($_POST['city'] ?? '');
    $address = sanitizeInput($_POST['address'] ?? '');
    $paymentMethod = sanitizeInput($_POST['payment_method'] ?? '');

    // التحقق
    $errors = [];
    if (empty($customerName)) $errors[] = 'أدخل الاسم الكامل';
    if (empty($customerEmail)) $errors[] = 'أدخل البريد الإلكتروني';
    if (empty($customerPhone)) $errors[] = 'أدخل رقم الهاتف';
    if (empty($city)) $errors[] = 'أدخل المدينة';
    if (empty($address)) $errors[] = 'أدخل العنوان';

    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        header('Location: checkout.php');
        exit;
    }

    // إرسال الطلب عبر واتس
    $cart = json_decode($_POST['cart'] ?? '[]', true);
    $message = "🛒 *طلب جديد*\n\n";
    $message .= "*بيانات العميل:*\n";
    $message .= "👤 الاسم: $customerName\n";
    $message .= "📧 البريد: $customerEmail\n";
    $message .= "📱 الهاتف: $customerPhone\n";
    $message .= "🏙️ المدينة: $city\n";
    $message .= "📍 العنوان: $address\n\n";

    $message .= "*المنتجات:*\n";
    $subtotal = 0;
    
    if (!empty($cart)) {
        foreach ($cart as $item) {
            $itemTotal = ($item['price'] ?? 0) * ($item['quantity'] ?? 1);
            $subtotal += $itemTotal;
            $message .= "📦 {$item['name']}\n";
            $message .= "   الكمية: {$item['quantity']}\n";
            $message .= "   السعر: " . ($item['price'] ?? 0) . " DH\n\n";
        }
    }

    $shipping = $subtotal >= FREE_SHIPPING_MIN ? 0 : SHIPPING_COST;
    $total = $subtotal + $shipping;

    $message .= "━━━━━━━━━━━━━━━━\n";
    $message .= "المجموع الفرعي: $subtotal DH\n";
    $message .= "التوصيل: " . ($shipping === 0 ? 'مجاني ✅' : "$shipping DH") . "\n";
    $message .= "الإجمالي: $total DH\n";
    $message .= "━━━━━━━━━━━━━━━━\n";
    $message .= "💳 طريقة الدفع: " . (PAYMENT_METHODS[$paymentMethod] ?? 'غير معروفة') . "\n";

    $_SESSION['order_success'] = '✅ تم إرسال طلبك! سيتم الرد عليك قريباً';
    $_SESSION['success'] = true;

    // إعادة التوجيه إلى WhatsApp
    header('Location: https://wa.me/' . WHATSAPP_NUMBER . '?text=' . urlencode($message));
    exit;
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>الدفع والتوصيل - SUBLORA</title>
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
        .container { max-width:1000px; margin:0 auto; padding:0 24px; }

        /* Header */
        .header {
            position:fixed; top:0; left:0; right:0; z-index:1000;
            padding:14px 24px; background:rgba(10,10,26,0.95);
            display:flex; justify-content:space-between; align-items:center;
        }
        .logo { font-size:1.5rem; font-weight:900; color:var(--white); text-decoration:none; }
        .logo span { color:var(--secondary); }

        /* Page Title */
        .page-title { padding:100px 0 30px; text-align:center; }
        .page-title h1 { font-size:2.2rem; font-weight:900; margin-bottom:10px; }
        .page-title p { color:var(--gray-600); }

        /* Steps */
        .steps { display:flex; justify-content:space-around; margin:40px 0; flex-wrap:wrap; }
        .step {
            text-align:center; flex:1; min-width:150px; padding:20px;
            position:relative;
        }
        .step::after {
            content:''; position:absolute; top:40px; left:-30px; width:60px; height:3px;
            background:var(--secondary); display:none;
        }
        .step:last-child::after { display:none; }
        .step:not(:last-child)::after { display:block; }

        .step-number {
            width:50px; height:50px; background:var(--secondary); color:var(--primary);
            border-radius:50%; display:flex; align-items:center; justify-content:center;
            font-size:1.5rem; font-weight:900; margin:0 auto 10px;
        }
        .step.active .step-number { background:var(--primary); color:var(--secondary); }
        .step-label { font-weight:700; }
        .step-sublabel { font-size:0.85rem; color:var(--gray-600); }

        /* Form */
        .form-wrapper {
            background:var(--white); padding:40px; border-radius:12px;
            box-shadow:0 2px 12px rgba(0,0,0,0.08); margin-bottom:30px;
        }
        .form-wrapper h2 { font-size:1.3rem; font-weight:800; margin-bottom:20px; }
        .form-group { margin-bottom:20px; }
        .form-group label { display:block; font-weight:700; margin-bottom:8px; font-size:0.95rem; }
        .form-group input, .form-group select, .form-group textarea {
            width:100%; padding:12px; border:2px solid var(--gray-200);
            border-radius:8px; font-size:1rem; font-family:inherit;
            transition:var(--transition);
        }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            outline:none; border-color:var(--secondary); box-shadow:0 0 0 3px rgba(245,166,35,0.1);
        }
        .form-row { display:grid; grid-template-columns:1fr 1fr; gap:20px; }

        /* Payment Methods */
        .payment-methods { display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:15px; margin:20px 0; }
        .payment-option {
            border:2px solid var(--gray-200); padding:20px; border-radius:8px;
            text-align:center; cursor:pointer; transition:var(--transition);
        }
        .payment-option:hover { border-color:var(--secondary); background:var(--gray-50); }
        .payment-option input { display:none; }
        .payment-option input:checked + .payment-label {
            color:var(--secondary); font-weight:900;
        }
        .payment-label { font-size:3rem; margin-bottom:10px; display:block; }
        .payment-name { font-weight:700; }
        .payment-desc { font-size:0.85rem; color:var(--gray-600); margin-top:5px; }

        /* Summary */
        .summary {
            background:var(--primary); color:var(--white); padding:20px;
            border-radius:8px; margin:30px 0;
        }
        .summary h3 { margin-bottom:15px; }
        .summary-line {
            display:flex; justify-content:space-between; padding:8px 0;
            border-bottom:1px solid rgba(255,255,255,0.1);
        }
        .summary-line:last-child { border-bottom:none; }
        .summary-total {
            display:flex; justify-content:space-between; font-size:1.3rem;
            font-weight:900; margin-top:15px; color:var(--secondary);
        }

        /* Errors */
        .error-box {
            background:#f8d7da; color:#721c24; padding:15px; border-radius:8px;
            margin-bottom:20px; border-left:4px solid var(--danger);
        }

        /* Buttons */
        .btn-submit {
            width:100%; padding:16px; background:var(--secondary); color:var(--primary);
            border:none; border-radius:8px; font-size:1.1rem; font-weight:800;
            cursor:pointer; transition:var(--transition); margin-top:20px;
            display:flex; align-items:center; justify-content:center; gap:10px;
        }
        .btn-submit:hover { background:#d48a1a; }

        .btn-back {
            display:inline-block; color:var(--secondary); text-decoration:none;
            font-weight:600; margin-top:15px;
        }

        @media (max-width:600px) {
            .form-row { grid-template-columns:1fr; }
            .step::after { display:none !important; }
        }
    </style>
</head>
<body>

<!-- HEADER -->
<header class="header">
    <a href="index.php" class="logo"><i class="fas fa-print"></i> SUB<span>LORA</span></a>
</header>

<!-- PAGE TITLE -->
<div class="page-title">
    <div class="container">
        <h1>💳 إكمال الدفع</h1>
        <p>أدخل بيانات التوصيل واختر طريقة الدفع</p>
    </div>
</div>

<!-- STEPS -->
<div class="container">
    <div class="steps">
        <div class="step">
            <div class="step-number">1</div>
            <div class="step-label">السلة</div>
            <div class="step-sublabel">review products</div>
        </div>
        <div class="step active">
            <div class="step-number">2</div>
            <div class="step-label">الدفع</div>
            <div class="step-sublabel">billing info</div>
        </div>
        <div class="step">
            <div class="step-number">3</div>
            <div class="step-label">التأكيد</div>
            <div class="step-sublabel">confirmation</div>
        </div>
    </div>
</div>

<!-- CONTENT -->
<div class="container">
    <?php if (isset($_SESSION['errors'])): ?>
        <div class="error-box">
            <strong><i class="fas fa-exclamation-circle"></i> حدثت أخطاء:</strong>
            <ul style="margin-top:10px;">
                <?php foreach ($_SESSION['errors'] as $error): ?>
                    <li>• <?php echo $error; ?></li>
                <?php endforeach; unset($_SESSION['errors']); ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" id="checkoutForm">
        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
        <input type="hidden" name="cart" value='<?php echo json_encode(json_decode($_POST['cart'] ?? '[]')); ?>'>

        <!-- CUSTOMER INFO -->
        <div class="form-wrapper">
            <h2><i class="fas fa-user"></i> بيانات العميل</h2>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="name">الاسم الكامل *</label>
                    <input type="text" id="name" name="customer_name" required placeholder="أحمد محمد">
                </div>
                <div class="form-group">
                    <label for="email">البريد الإلكتروني *</label>
                    <input type="email" id="email" name="customer_email" required placeholder="user@example.com">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="phone">رقم الهاتف *</label>
                    <input type="tel" id="phone" name="customer_phone" required placeholder="+212 6XX XXX XXX">
                </div>
                <div class="form-group">
                    <label for="city">المدينة *</label>
                    <input type="text" id="city" name="city" required placeholder="الرباط">
                </div>
            </div>

            <div class="form-group">
                <label for="address">العنوان الكامل *</label>
                <textarea id="address" name="address" required placeholder="الشارع، الحي، رقم المنزل..." rows="3"></textarea>
            </div>
        </div>

        <!-- PAYMENT METHOD -->
        <div class="form-wrapper">
            <h2><i class="fas fa-credit-card"></i> طريقة الدفع</h2>
            
            <div class="payment-methods">
                <?php foreach (PAYMENT_METHODS as $key => $method): ?>
                <label class="payment-option">
                    <input type="radio" name="payment_method" value="<?php echo $key; ?>" <?php echo $key === DEFAULT_PAYMENT_METHOD ? 'checked' : ''; ?>>
                    <span class="payment-label">
                        <?php echo $key === 'cash' ? '💵' : '🏦'; ?>
                    </span>
                    <div class="payment-name"><?php echo $method; ?></div>
                    <div class="payment-desc">
                        <?php echo $key === 'cash' ? 'ادفع عند استقبال الطلب' : 'تحويل بنكي آمن'; ?>
                    </div>
                </label>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- SUMMARY -->
        <div class="summary" id="summaryBox">
            <h3>📋 ملخص الطلب</h3>
            <div class="summary-line">
                <span>المجموع الفرعي:</span>
                <span id="summarySubtotal">0 DH</span>
            </div>
            <div class="summary-line">
                <span>التوصيل:</span>
                <span id="summaryShipping">20 DH</span>
            </div>
            <div class="summary-total">
                <span>الإجمالي:</span>
                <span id="summaryTotal">0 DH</span>
            </div>
        </div>

        <button type="submit" class="btn-submit">
            <i class="fab fa-whatsapp"></i> إكمال الطلب عبر WhatsApp
        </button>

        <a href="cart.php" class="btn-back">← العودة للسلة</a>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    updateSummary();
});

function updateSummary() {
    let cart = JSON.parse(localStorage.getItem('cart')) || [];
    let subtotal = 0;

    cart.forEach(item => {
        subtotal += (item.price || 0) * (item.quantity || 1);
    });

    let shipping = subtotal >= <?php echo FREE_SHIPPING_MIN; ?> ? 0 : <?php echo SHIPPING_COST; ?>;
    let total = subtotal + shipping;

    document.getElementById('summarySubtotal').textContent = subtotal.toFixed(2) + ' DH';
    document.getElementById('summaryShipping').textContent = shipping === 0 ? 'مجاني ✅' : shipping + ' DH';
    document.getElementById('summaryTotal').textContent = total.toFixed(2) + ' DH';

    // تحديث قيمة السلة المخفية
    document.querySelector('input[name="cart"]').value = JSON.stringify(cart);
}

document.getElementById('checkoutForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    let cart = JSON.parse(localStorage.getItem('cart')) || [];
    if (cart.length === 0) {
        alert('السلة فارغة!');
        window.location.href = 'cart.php';
        return;
    }

    // إرسال البيانات
    this.submit();
});
</script>
</body>
</html>