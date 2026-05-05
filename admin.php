<?php

// admin.php - پنل مدیریت ساده (برای استفاده در ترمینال)

require_once 'db.php';

$db = Database::getInstance();

// احراز هویت
echo "🔐 پنل مدیریت\n";
echo "رمز عبور: ";
$password = trim(fgets(STDIN));

if ($password !== ADMIN_PASSWORD) {
    echo "❌ رمز اشتباه است!\n";
    exit;
}

while (true) {
    echo "\n";
    echo "═══════════════════════════════════════\n";
    echo "📋 منوی مدیریت\n";
    echo "═══════════════════════════════════════\n";
    echo "1️⃣ لیست محصولات\n";
    echo "2️⃣ افزودن محصول جدید\n";
    echo "3️⃣ ویرایش محصول\n";
    echo "4️⃣ حذف محصول\n";
    echo "5️⃣ مشاهده آمار\n";
    echo "6️⃣ مشاهده لاگ پیام‌ها\n";
    echo "0️⃣ خروج\n";
    echo "\n➡️ انتخاب: ";

    $choice = trim(fgets(STDIN));

    switch ($choice) {
        case '1':
            $products = $db->getAllProducts();
            echo "\n📦 لیست محصولات:\n";
            echo str_repeat("-", 50) . "\n";
            foreach ($products as $p) {
                $stock = $p['in_stock'] ? "✅" : "❌";
                echo "{$stock} ID:{$p['id']} - {$p['name']} - " . number_format($p['price']) . " تومان\n";
            }
            break;

        case '2':
            echo "\n➕ افزودن محصول جدید\n";
            echo "نام محصول: ";
            $name = trim(fgets(STDIN));
            echo "قیمت (تومان): ";
            $price = trim(fgets(STDIN));
            echo "توضیحات: ";
            $desc = trim(fgets(STDIN));

            $conn = $db->getConnection();
            $sql = "INSERT INTO products (name, price, description, slug) VALUES (:name, :price, :desc, :slug)";
            $slug = str_replace(' ', '-', $name);

            $stmt = $conn->prepare($sql);
            if ($stmt->execute([':name' => $name, ':price' => $price, ':desc' => $desc, ':slug' => $slug])) {
                echo "✅ محصول با موفقیت اضافه شد (ID: " . $conn->lastInsertId() . ")\n";
            } else {
                echo "❌ خطا در افزودن محصول\n";
            }
            break;

        case '3':
            echo "\n✏️ ویرایش محصول\n";
            echo "آیدی محصول: ";
            $id = trim(fgets(STDIN));

            $product = $db->getProductById($id);
            if (!$product) {
                echo "❌ محصول یافت نشد\n";
                break;
            }

            echo "نام جدید (فعلی: {$product['name']}): ";
            $name = trim(fgets(STDIN));
            echo "قیمت جدید (فعلی: {$product['price']}): ";
            $price = trim(fgets(STDIN));
            echo "موجودی (0 یا 1): ";
            $stock = trim(fgets(STDIN));

            $conn = $db->getConnection();
            $sql = "UPDATE products SET name = :name, price = :price, in_stock = :stock WHERE id = :id";
            $stmt = $conn->prepare($sql);

            if ($stmt->execute([':name' => $name ?: $product['name'], ':price' => $price ?: $product['price'], ':stock' => $stock, ':id' => $id])) {
                echo "✅ محصول بروز شد\n";
            }
            break;

        case '4':
            echo "\n🗑️ حذف محصول\n";
            echo "آیدی محصول: ";
            $id = trim(fgets(STDIN));
            echo "آیا مطمئنی؟ (y/n): ";
            $confirm = trim(fgets(STDIN));

            if ($confirm == 'y') {
                $conn = $db->getConnection();
                $stmt = $conn->prepare("DELETE FROM products WHERE id = :id");
                if ($stmt->execute([':id' => $id])) {
                    echo "✅ محصول حذف شد\n";
                }
            }
            break;

        case '5':
            echo "\n" . getStatsReport() . "\n";
            break;

        case '6':
            $conn = $db->getConnection();
            $stmt = $conn->query("SELECT * FROM messages_log ORDER BY id DESC LIMIT 20");
            $logs = $stmt->fetchAll();

            echo "\n📝 آخرین لاگ‌ها:\n";
            foreach ($logs as $log) {
                $status = $log['status'] == 'sent' ? "✅" : "❌";
                echo "{$status} محصول ID:{$log['product_id']} - {$log['status']} - {$log['sent_at']}\n";
            }
            break;

        case '0':
            echo "خروج از پنل مدیریت...\n";
            exit;

        default:
            echo "❌ انتخاب نامعتبر\n";
            break;
    }
}
