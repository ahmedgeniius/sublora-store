<?php
// ==========================================================
// SUBLORA - Admin Dashboard
// ==========================================================

session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once '../config.php';
require_once '../functions.php';

$stats = getStats();
$orders = getOrders(50);
$visitors = getVisitors(30);
$products = getAllProducts();

if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $action = $_GET['action'];
    
    switch ($action) {
        case 'processing': updateOrderStatus($id, 'processing'); break;
        case 'completed': updateOrderStatus($id, 'completed'); break;
        case 'cancelled': updateOrderStatus($id, 'cancelled'); break;
        case 'delete': deleteOrder($id); break;
    }
    header('Location: dashboard.php');
    exit;
}

$tab = $_GET['tab'] ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>لوحة التحكم - SUBLORA</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&display=swap" rel="stylesheet" />
    
    <style>
        :root {
            --primary: #0a0a1a;
            --secondary: #f5a623;
            --success: #1cc88a;
            --danger: #e74a3b;
            --warning: #f6c23e;
            --info: #36b9cc;
        }
        * { box-sizing:border-box; margin:0; padding:0; }
        body { font-family:'Cairo',sans-serif; background:#f8f9fc; color:#444; direction:rtl; }
        a { text-decoration:none; color:inherit; }

        .admin-wrapper { display:flex; min-height:100vh; }

        /* Sidebar */
        .sidebar {
            width:260px; background:var(--primary); color:#fff;
            padding:20px; flex-shrink:0; position:sticky; top:0;
            height:100vh; overflow-y:auto;
        }
        .sidebar-brand { text-align:center; margin-bottom:40px; }
        .brand-icon { font-size:24px; color:var(--secondary); }
        .brand-name { font-size:24px; font-weight:800; margin-top:5px; }
        .brand-name span { color:var(--secondary); }
        .sidebar-nav a {
            display:flex; align-items:center; gap:10px; color:#ddd;
            padding:12px 15px; border-radius:8px; margin-bottom:5px;
            transition:all 0.3s;
        }
        .sidebar-nav a:hover, .sidebar-nav a.active {
            background:var(--secondary); color:var(--primary); font-weight:700;
        }
        .sidebar-nav .logout { margin-top:20px; color:#ff8a8a; }
        .sidebar-nav .badge {
            background:var(--danger); color:#fff;
            border-radius:50px; padding:2px 8px; font-size:12px; margin-right:auto;
        }

        /* Main */
        .main-content { flex:1; padding:30px; overflow-x:hidden; }
        .admin-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:30px; }
        .admin-header h1 { font-size:22px; }

        /* Stats Grid */
        .stats-grid {
            display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr));
            gap:20px; margin-bottom:30px;
        }
        .stat-card {
            background:#fff; border-radius:10px; padding:20px;
            display:flex; justify-content:space-between; align-items:center;
            box-shadow:0 2px 8px rgba(0,0,0,0.1); border-right:5px solid var(--secondary);
        }
        .stat-card:nth-child(2) { border-right-color:var(--success); }
        .stat-card:nth-child(3) { border-right-color:var(--warning); }
        .stat-card:nth-child(4) { border-right-color:var(--danger); }
        .stat-icon {
            width:50px; height:50px; border-radius:50%;
            display:flex; align-items:center; justify-content:center; color:#fff; font-size:20px;
        }
        .stat-card:nth-child(1) .stat-icon { background:var(--secondary); }
        .stat-card:nth-child(2) .stat-icon { background:var(--success); }
        .stat-card:nth-child(3) .stat-icon { background:var(--warning); }
        .stat-card:nth-child(4) .stat-icon { background:var(--danger); }
        .stat-number { font-size:28px; font-weight:800; }

        /* Tables */
        .table-wrapper {
            background:#fff; border-radius:10px; padding:20px;
            box-shadow:0 2px 8px rgba(0,0,0,0.1); margin-bottom:30px;
        }
        .table-header { margin-bottom:20px; }
        .table-header h3 { font-size:18px; margin-bottom:10px; }
        table { width:100%; border-collapse:collapse; text-align:right; }
        th {
            background:#f8f9fc; color:#555; padding:12px;
            font-weight:700; border-bottom:2px solid #e3e6f0;
        }
        td { padding:12px; border-bottom:1px solid #eee; }
        tr:hover { background:#f8f9fc; }

        /* Status Badges */
        .status {
            display:inline-block; padding:4px 12px; border-radius:20px; font-size:12px; font-weight:700;
        }
        .status-pending { background:#fff3cd; color:#856404; }
        .status-processing { background:#d1ecf1; color:#0c5460; }
        .status-completed { background:#d4edda; color:#155724; }
        .status-cancelled { background:#f8d7da; color:#721c24; }

        /* Actions */
        .actions { display:flex; gap:5px; }
        .action-btn {
            width:32px; height:32px; border-radius:6px;
            display:flex; align-items:center; justify-content:center;
            color:#fff; font-size:13px; border:none; cursor:pointer;
        }
        .action-btn.processing { background:var(--info); }
        .action-btn.completed { background:var(--success); }
        .action-btn.cancelled { background:var(--warning); }
        .action-btn.delete { background:var(--danger); }

        @media (max-width:768px) {
            .admin-wrapper { flex-direction:column; }
            .sidebar { width:100%; height:auto; position:static; }
            .main-content { padding:15px; }
        }
    </style>
</head>
<body>
<div class="admin-wrapper">
    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="sidebar-brand">
            <div class="brand-icon"><i class="fas fa-print"></i></div>
            <div class="brand-name">SUB<span>LORA</span></div>
        </div>
        <nav class="sidebar-nav">
            <a href="?tab=dashboard" class="<?php echo $tab === 'dashboard' ? 'active' : ''; ?>">
                <i class="fas fa-chart-line"></i> لوحة التحكم
            </a>
            <a href="?tab=orders" class="<?php echo $tab === 'orders' ? 'active' : ''; ?>">
                <i class="fas fa-shopping-cart"></i> الطلبات
                <span class="badge"><?php echo count($orders); ?></span>
            </a>
            <a href="?tab=products" class="<?php echo $tab === 'products' ? 'active' : ''; ?>">
                <i class="fas fa-box"></i> المنتجات
                <span class="badge"><?php echo count($products); ?></span>
            </a>
            <a href="?tab=visitors" class="<?php echo $tab === 'visitors' ? 'active' : ''; ?>">
                <i class="fas fa-users"></i> الزوار
                <span class="badge"><?php echo $stats['total_visitors'] ?? 0; ?></span>
            </a>
            <a href="logout.php" class="logout">
                <i class="fas fa-sign-out-alt"></i> تسجيل الخروج
            </a>
        </nav>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="main-content">
        <header class="admin-header">
            <h1>
                <i class="fas fa-chart-line"></i>
                <?php
                    $titles = [
                        'dashboard' => 'لوحة التحكم',
                        'orders' => 'إدارة الطلبات',
                        'products' => 'إدارة المنتجات',
                        'visitors' => 'الزوار'
                    ];
                    echo $titles[$tab] ?? 'لوحة التحكم';
                ?>
            </h1>
        </header>

        <!-- DASHBOARD -->
        <?php if ($tab === 'dashboard'): ?>
        <div class="stats-grid">
            <div class="stat-card">
                <div>
                    <div style="font-size:28px;font-weight:800;"><?php echo $stats['total_orders'] ?? 0; ?></div>
                    <div style="color:#666;margin-top:5px;">إجمالي الطلبات</div>
                </div>
                <div class="stat-icon"><i class="fas fa-shopping-cart"></i></div>
            </div>
            <div class="stat-card">
                <div>
                    <div style="font-size:28px;font-weight:800;"><?php echo $stats['total_customers'] ?? 0; ?></div>
                    <div style="color:#666;margin-top:5px;">العملاء</div>
                </div>
                <div class="stat-icon"><i class="fas fa-users"></i></div>
            </div>
            <div class="stat-card">
                <div>
                    <div style="font-size:28px;font-weight:800;"><?php echo $stats['total_visitors'] ?? 0; ?></div>
                    <div style="color:#666;margin-top:5px;">الزيارات</div>
                </div>
                <div class="stat-icon"><i class="fas fa-eye"></i></div>
            </div>
            <div class="stat-card">
                <div>
                    <div style="font-size:28px;font-weight:800;"><?php echo count($products); ?></div>
                    <div style="color:#666;margin-top:5px;">المنتجات</div>
                </div>
                <div class="stat-icon"><i class="fas fa-box"></i></div>
            </div>
        </div>

        <div class="table-wrapper">
            <div class="table-header">
                <h3><i class="fas fa-clock-rotate-left"></i> آخر الطلبات</h3>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>رقم الطلب</th>
                        <th>العميل</th>
                        <th>الهاتف</th>
                        <th>الحالة</th>
                        <th>التاريخ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (array_slice($orders, 0, 8) as $order): ?>
                    <tr>
                        <td><strong><?php echo $order['order_id']; ?></strong></td>
                        <td><?php echo $order['customer_name']; ?></td>
                        <td dir="ltr"><?php echo $order['phone']; ?></td>
                        <td><span class="status status-<?php echo $order['status']; ?>">
                            <?php $labels = ['pending'=>'قيد الانتظار', 'processing'=>'قيد المعالجة', 'completed'=>'مكتمل', 'cancelled'=>'ملغي'];
                            echo $labels[$order['status']] ?? $order['status']; ?>
                        </span></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <!-- ORDERS -->
        <?php if ($tab === 'orders'): ?>
        <div class="table-wrapper">
            <div class="table-header">
                <h3><i class="fas fa-list"></i> جميع الطلبات</h3>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>رقم الطلب</th>
                        <th>المنتج</th>
                        <th>العميل</th>
                        <th>الهاتف</th>
                        <th>الحالة</th>
                        <th>التاريخ</th>
                        <th>إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><strong><?php echo $order['order_id']; ?></strong></td>
                        <td><?php echo $order['product_type']; ?></td>
                        <td><?php echo $order['customer_name']; ?></td>
                        <td dir="ltr"><?php echo $order['phone']; ?></td>
                        <td><span class="status status-<?php echo $order['status']; ?>">
                            <?php $labels = ['pending'=>'قيد الانتظار', 'processing'=>'قيد المعالجة', 'completed'=>'مكتمل', 'cancelled'=>'ملغي'];
                            echo $labels[$order['status']] ?? $order['status']; ?>
                        </span></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                        <td>
                            <div class="actions">
                                <a href="?action=processing&id=<?php echo $order['id']; ?>" class="action-btn processing" title="قيد المعالجة"><i class="fas fa-spinner"></i></a>
                                <a href="?action=completed&id=<?php echo $order['id']; ?>" class="action-btn completed" title="مكتمل"><i class="fas fa-check"></i></a>
                                <a href="?action=delete&id=<?php echo $order['id']; ?>" class="action-btn delete" title="حذف" onclick="return confirm('متأكد؟')"><i class="fas fa-trash"></i></a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <!-- PRODUCTS -->
        <?php if ($tab === 'products'): ?>
        <div class="table-wrapper">
            <div class="table-header">
                <h3><i class="fas fa-box"></i> المنتجات</h3>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>المنتج</th>
                        <th>الفئة</th>
                        <th>السعر</th>
                        <th>المخزون</th>
                        <th>الحالة</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                    <tr>
                        <td><?php echo $product['id']; ?></td>
                        <td><?php echo $product['icon'] . ' ' . $product['name']; ?></td>
                        <td><?php echo $product['category']; ?></td>
                        <td><?php echo formatPrice($product['price']); ?></td>
                        <td><?php echo $product['stock']; ?></td>
                        <td><span class="status" style="background:#d4edda;color:#155724;">مفعل ✓</span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <!-- VISITORS -->
        <?php if ($tab === 'visitors'): ?>
        <div class="table-wrapper">
            <div class="table-header">
                <h3><i class="fas fa-users"></i> الزوار</h3>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>IP</th>
                        <th>الجهاز</th>
                        <th>نظام التشغيل</th>
                        <th>المتصفح</th>
                        <th>التاريخ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($visitors as $visitor):
                        $device = is_array($visitor['device_info']) ? $visitor['device_info'] : json_decode($visitor['device_info'], true);
                        if (!is_array($device)) $device = ['device' => 'Unknown', 'os' => 'Unknown', 'browser' => 'Unknown'];
                    ?>
                    <tr>
                        <td dir="ltr"><?php echo $visitor['ip_address']; ?></td>
                        <td><?php echo $device['device'] ?? 'Unknown'; ?></td>
                        <td><?php echo $device['os'] ?? 'Unknown'; ?></td>
                        <td><?php echo $device['browser'] ?? 'Unknown'; ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($visitor['created_at'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </main>
</div>
</body>
</html>
