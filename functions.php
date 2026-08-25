<?php
/**
 * SUBLORA - Functions File
 * دوال قاعدة البيانات والإدارة والمتجر
 */

require_once 'config.php';

// ==========================================================
// إنشاء اتصال بقاعدة البيانات
// ==========================================================

function getDBConnection() {
    try {
        $dbDir = dirname(DB_PATH);
        if (!is_dir($dbDir)) {
            mkdir($dbDir, 0755, true);
        }
        
        $db = new PDO('sqlite:' . DB_PATH);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        
        createTables($db);
        return $db;
    } catch (PDOException $e) {
        logError('Database connection error', $e->getMessage());
        return null;
    }
}

// ==========================================================
// إنشاء الجداول
// ==========================================================

function createTables($db) {
    // جدول الطلبات
    $db->exec("
        CREATE TABLE IF NOT EXISTS orders (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            order_id TEXT UNIQUE NOT NULL,
            product_type TEXT NOT NULL,
            size TEXT NOT NULL,
            phone TEXT NOT NULL,
            customer_name TEXT NOT NULL,
            city TEXT NOT NULL,
            design_description TEXT,
            file_name TEXT,
            file_path TEXT,
            status TEXT DEFAULT 'pending',
            ip_address TEXT,
            device_info TEXT,
            geo_info TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");

    // جدول المنتجات
    $db->exec("
        CREATE TABLE IF NOT EXISTS products (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            slug TEXT UNIQUE NOT NULL,
            description TEXT,
            price REAL NOT NULL,
            category TEXT,
            icon TEXT,
            colors TEXT,
            sizes TEXT,
            features TEXT,
            stock INTEGER DEFAULT 999,
            active BOOLEAN DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");

    // جدول السلة (اختياري - للتخزين في DB بدلاً من LocalStorage)
    $db->exec("
        CREATE TABLE IF NOT EXISTS cart_items (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            session_id TEXT NOT NULL,
            product_id INTEGER NOT NULL,
            color TEXT,
            size TEXT,
            custom_text TEXT,
            extra_print INTEGER DEFAULT 0,
            quantity INTEGER DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");

    // جدول الزوار
    $db->exec("
        CREATE TABLE IF NOT EXISTS visitors (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            ip_address TEXT NOT NULL,
            device_info TEXT,
            geo_info TEXT,
            page TEXT,
            referer TEXT,
            session_id TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");

    // جدول الإحصائيات
    $db->exec("
        CREATE TABLE IF NOT EXISTS stats (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            stat_key TEXT UNIQUE NOT NULL,
            stat_value INTEGER DEFAULT 0,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");

    // إضافة إحصائيات افتراضية
    $defaultStats = ['total_orders', 'total_customers', 'total_visitors', 'total_views', 'total_products'];
    foreach ($defaultStats as $stat) {
        $db->exec("
            INSERT OR IGNORE INTO stats (stat_key, stat_value) 
            VALUES ('$stat', 0)
        ");
    }

    // إضافة المنتجات الافتراضية
    insertDefaultProducts($db);
}

// ==========================================================
// إضافة المنتجات الافتراضية
// ==========================================================

function insertDefaultProducts($db) {
    $products = [
        [
            'name' => 'T-shirt',
            'slug' => 't-shirt',
            'description' => 'تيشيرت عالي الجودة مع طباعة DTF',
            'price' => 89,
            'category' => 'shirts',
            'icon' => '👕',
            'colors' => json_encode(['أسود', 'أبيض', 'أحمر', 'أزرق', 'أخضر']),
            'sizes' => json_encode(['XS', 'S', 'M', 'L', 'XL', 'XXL']),
            'features' => json_encode(['مادة 100% قطن', 'طباعة DTF عالية الجودة', 'ألوان ثابتة'])
        ],
        [
            'name' => 'Mug',
            'slug' => 'mug',
            'description' => 'كوب سيراميك مع طباعة مخصصة',
            'price' => 49,
            'category' => 'accessories',
            'icon' => '☕',
            'colors' => json_encode(['أبيض']),
            'sizes' => json_encode(['250ml', '350ml']),
            'features' => json_encode(['سيراميك عالي الجودة', 'طباعة دائمة', 'آمن غسالة الأطباق'])
        ],
        [
            'name' => 'Casquette',
            'slug' => 'casquette',
            'description' => 'كابتة رياضية مع تطريز وطباعة',
            'price' => 59,
            'category' => 'accessories',
            'icon' => '🧢',
            'colors' => json_encode(['أسود', 'أبيض', 'أحمر', 'أزرق']),
            'sizes' => json_encode(['OneSize']),
            'features' => json_encode(['مادة تنفس جيدة', 'تطريز حرفي', 'مريحة وخفيفة'])
        ],
        [
            'name' => 'Tote Bag',
            'slug' => 'tote-bag',
            'description' => 'شنطة قماش مع طباعة مخصصة',
            'price' => 69,
            'category' => 'accessories',
            'icon' => '🛍️',
            'colors' => json_encode(['أسود', 'أبيض', 'بني', 'رمادي']),
            'sizes' => json_encode(['OneSize']),
            'features' => json_encode(['قماش متين', 'طباعة واضحة', 'حمل مريح'])
        ]
    ];

    foreach ($products as $product) {
        try {
            $db->exec("
                INSERT OR IGNORE INTO products (
                    name, slug, description, price, category, icon, colors, sizes, features
                ) VALUES (
                    '{$product['name']}',
                    '{$product['slug']}',
                    '{$product['description']}',
                    {$product['price']},
                    '{$product['category']}',
                    '{$product['icon']}',
                    '{$product['colors']}',
                    '{$product['sizes']}',
                    '{$product['features']}'
                )
            ");
        } catch (Exception $e) {
            logError('Error inserting product', $e->getMessage());
        }
    }
}

// ==========================================================
// دوال المنتجات
// ==========================================================

function getAllProducts() {
    $db = getDBConnection();
    if (!$db) return [];

    try {
        $stmt = $db->query("SELECT * FROM products WHERE active = 1 ORDER BY created_at DESC");
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        logError('Get all products error', $e->getMessage());
        return [];
    }
}

function getProductById($id) {
    $db = getDBConnection();
    if (!$db) return null;

    try {
        $stmt = $db->prepare("SELECT * FROM products WHERE id = :id AND active = 1");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        logError('Get product error', $e->getMessage());
        return null;
    }
}

function getProductsByCategory($category) {
    $db = getDBConnection();
    if (!$db) return [];

    try {
        $stmt = $db->prepare("SELECT * FROM products WHERE category = :category AND active = 1");
        $stmt->execute([':category' => $category]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        logError('Get products by category error', $e->getMessage());
        return [];
    }
}

// ==========================================================
// دوال الطلبات
// ==========================================================

function saveOrder($data) {
    $db = getDBConnection();
    if (!$db) return false;

    try {
        $orderId = generateOrderId();
        $ip = getVisitorIP();
        $device = json_encode(getDeviceInfo());
        $geo = json_encode(getGeoLocation($ip));
        
        $stmt = $db->prepare("
            INSERT INTO orders (
                order_id, product_type, size, phone, customer_name, city,
                design_description, file_name, file_path, ip_address, device_info, geo_info
            ) VALUES (
                :order_id, :product_type, :size, :phone, :customer_name, :city,
                :design_description, :file_name, :file_path, :ip_address, :device_info, :geo_info
            )
        ");

        $stmt->execute([
            ':order_id' => $orderId,
            ':product_type' => $data['product_type'],
            ':size' => $data['size'],
            ':phone' => $data['phone'],
            ':customer_name' => $data['customer_name'],
            ':city' => $data['city'],
            ':design_description' => $data['design_description'] ?? '',
            ':file_name' => $data['file_name'] ?? '',
            ':file_path' => $data['file_path'] ?? '',
            ':ip_address' => $ip,
            ':device_info' => $device,
            ':geo_info' => $geo
        ]);

        $db->exec("UPDATE stats SET stat_value = stat_value + 1, updated_at = CURRENT_TIMESTAMP WHERE stat_key = 'total_orders'");
        $db->exec("UPDATE stats SET stat_value = stat_value + 1, updated_at = CURRENT_TIMESTAMP WHERE stat_key = 'total_customers'");

        return [
            'success' => true,
            'order_id' => $orderId,
            'id' => $db->lastInsertId()
        ];
    } catch (PDOException $e) {
        logError('Save order error', $e->getMessage());
        return false;
    }
}

function getOrders($limit = 100, $offset = 0) {
    $db = getDBConnection();
    if (!$db) return [];

    try {
        $stmt = $db->prepare("SELECT * FROM orders ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        logError('Get orders error', $e->getMessage());
        return [];
    }
}

function updateOrderStatus($id, $status) {
    $db = getDBConnection();
    if (!$db) return false;

    try {
        $stmt = $db->prepare("UPDATE orders SET status = :status, updated_at = CURRENT_TIMESTAMP WHERE id = :id");
        return $stmt->execute([':status' => $status, ':id' => $id]);
    } catch (PDOException $e) {
        logError('Update order status error', $e->getMessage());
        return false;
    }
}

function deleteOrder($id) {
    $db = getDBConnection();
    if (!$db) return false;

    try {
        $order = getOrderById($id);
        if ($order && !empty($order['file_path'])) {
            $filePath = UPLOAD_PATH . $order['file_path'];
            if (file_exists($filePath)) @unlink($filePath);
        }
        
        $stmt = $db->prepare("DELETE FROM orders WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    } catch (PDOException $e) {
        logError('Delete order error', $e->getMessage());
        return false;
    }
}

function getOrderById($id) {
    $db = getDBConnection();
    if (!$db) return null;

    try {
        $stmt = $db->prepare("SELECT * FROM orders WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        logError('Get order by id error', $e->getMessage());
        return null;
    }
}

// ==========================================================
// دوال الزوار والإحصائيات
// ==========================================================

function trackVisitor($page = '') {
    $db = getDBConnection();
    if (!$db) return;

    try {
        $ip = getVisitorIP();
        $device = json_encode(getDeviceInfo());
        $geo = json_encode(getGeoLocation($ip));
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        $sessionId = session_id() ?: '';
        
        $stmt = $db->prepare("
            INSERT INTO visitors (ip_address, device_info, geo_info, page, referer, session_id)
            VALUES (:ip, :device, :geo, :page, :referer, :session_id)
        ");
        $stmt->execute([
            ':ip' => $ip,
            ':device' => $device,
            ':geo' => $geo,
            ':page' => $page,
            ':referer' => $referer,
            ':session_id' => $sessionId
        ]);
        
        $db->exec("UPDATE stats SET stat_value = stat_value + 1, updated_at = CURRENT_TIMESTAMP WHERE stat_key = 'total_visitors'");
        $db->exec("UPDATE stats SET stat_value = stat_value + 1, updated_at = CURRENT_TIMESTAMP WHERE stat_key = 'total_views'");
    } catch (PDOException $e) {
        logError('Track visitor error', $e->getMessage());
    }
}

function getStats() {
    $db = getDBConnection();
    if (!$db) return [];

    try {
        $stmt = $db->query("SELECT stat_key, stat_value FROM stats");
        $stats = [];
        while ($row = $stmt->fetch()) {
            $stats[$row['stat_key']] = $row['stat_value'];
        }
        return $stats;
    } catch (PDOException $e) {
        logError('Get stats error', $e->getMessage());
        return [];
    }
}

function getVisitors($limit = 50, $offset = 0) {
    $db = getDBConnection();
    if (!$db) return [];

    try {
        $stmt = $db->prepare("
            SELECT * FROM visitors 
            ORDER BY created_at DESC 
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $results = $stmt->fetchAll();
        
        foreach ($results as &$row) {
            if (is_string($row['geo_info'])) {
                $decoded = json_decode($row['geo_info'], true);
                $row['geo_info'] = is_array($decoded) ? $decoded : ['country' => 'Unknown', 'city' => 'Unknown'];
            }
            if (is_string($row['device_info'])) {
                $decoded = json_decode($row['device_info'], true);
                $row['device_info'] = is_array($decoded) ? $decoded : ['device' => 'Unknown', 'os' => 'Unknown', 'browser' => 'Unknown'];
            }
        }
        
        return $results;
    } catch (PDOException $e) {
        logError('Get visitors error', $e->getMessage());
        return [];
    }
}

function getDailyVisitors($days = 7) {
    $db = getDBConnection();
    if (!$db) return [];

    try {
        $stmt = $db->prepare("
            SELECT DATE(created_at) as date, COUNT(*) as count 
            FROM visitors 
            WHERE created_at >= DATE('now', '-' || :days || ' days')
            GROUP BY DATE(created_at)
            ORDER BY date DESC
        ");
        $stmt->bindValue(':days', $days, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        logError('Get daily visitors error', $e->getMessage());
        return [];
    }
}

// ==========================================================
// دوال رفع الملفات
// ==========================================================

function uploadFile($file) {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'لم يتم رفع أي ملف'];
    }

    if ($file['size'] > MAX_FILE_SIZE) {
        return ['success' => false, 'message' => 'حجم الملف كبير جداً (الحد الأقصى 5MB)'];
    }

    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, ALLOWED_EXTENSIONS)) {
        return ['success' => false, 'message' => 'صيغة الملف غير مدعومة'];
    }

    if (!is_dir(UPLOAD_PATH)) mkdir(UPLOAD_PATH, 0755, true);

    $fileName = date('Ymd_His') . '_' . uniqid() . '.' . $extension;
    $filePath = UPLOAD_PATH . $fileName;

    if (move_uploaded_file($file['tmp_name'], $filePath)) {
        return [
            'success' => true,
            'file_name' => $file['name'],
            'file_path' => $fileName
        ];
    }

    return ['success' => false, 'message' => 'فشل في رفع الملف'];
}

function getMediaFiles() {
    $files = [];
    if (!is_dir(UPLOAD_PATH)) return $files;
    
    $allowedImages = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
    $allowedVideos = ['mp4', 'webm', 'avi', 'mov'];
    
    $dir = scandir(UPLOAD_PATH);
    foreach ($dir as $file) {
        if ($file === '.' || $file === '..') continue;
        
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        $type = 'other';
        
        if (in_array($ext, $allowedImages)) $type = 'image';
        elseif (in_array($ext, $allowedVideos)) $type = 'video';
        
        $files[] = [
            'name' => $file,
            'url' => SITE_URL . 'uploads/' . $file,
            'type' => $type,
            'size' => filesize(UPLOAD_PATH . $file),
            'modified' => date('Y-m-d H:i:s', filemtime(UPLOAD_PATH . $file))
        ];
    }
    
    return $files;
}

function deleteMediaFile($filename) {
    $filePath = UPLOAD_PATH . $filename;
    if (file_exists($filePath)) return @unlink($filePath);
    return false;
}

// ==========================================================
// دوال مساعدة
// ==========================================================

function getVisitorIP() {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $headers = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED'];
    foreach ($headers as $header) {
        if (!empty($_SERVER[$header])) {
            $ips = explode(',', $_SERVER[$header]);
            $ip = trim($ips[0]);
            break;
        }
    }
    if (!filter_var($ip, FILTER_VALIDATE_IP)) $ip = '0.0.0.0';
    return $ip;
}

function getDeviceInfo() {
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    $device = 'Desktop';
    if (strpos($userAgent, 'Mobile') !== false || strpos($userAgent, 'Android') !== false) {
        $device = 'Mobile';
    } elseif (strpos($userAgent, 'iPad') !== false || strpos($userAgent, 'Tablet') !== false) {
        $device = 'Tablet';
    }
    
    $os = 'Unknown';
    if (strpos($userAgent, 'Windows NT 10.0') !== false) $os = 'Windows 10';
    elseif (strpos($userAgent, 'Mac OS X') !== false) $os = 'macOS';
    elseif (strpos($userAgent, 'Android') !== false) $os = 'Android';
    elseif (strpos($userAgent, 'Linux') !== false) $os = 'Linux';
    
    $browser = 'Unknown';
    if (strpos($userAgent, 'Chrome') !== false && strpos($userAgent, 'Edg') === false) {
        $browser = 'Chrome';
    } elseif (strpos($userAgent, 'Firefox') !== false) {
        $browser = 'Firefox';
    } elseif (strpos($userAgent, 'Safari') !== false && strpos($userAgent, 'Chrome') === false) {
        $browser = 'Safari';
    }
    
    return ['device' => $device, 'os' => $os, 'browser' => $browser];
}

function getGeoLocation($ip) {
    $default = ['country' => 'Unknown', 'countryCode' => 'UN', 'city' => 'Unknown'];
    $privateIPs = ['127.0.0.1', '::1', '0.0.0.0'];
    if (in_array($ip, $privateIPs)) return $default;
    return $default;
}

function getCountryFlag($countryCode) {
    $flags = ['MA' => '🇲🇦', 'FR' => '🇫🇷', 'US' => '🇺🇸', 'GB' => '🇬🇧'];
    return $flags[$countryCode] ?? '🌍';
}
?>
