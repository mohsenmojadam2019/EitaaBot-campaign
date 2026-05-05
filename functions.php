<?php

// functions.php - توابع ارتباط با پلتفرم‌ها

require_once 'config.php';
require_once 'db.php';

$db = Database::getInstance();

/**
 * ارسال پیام به ایتا یا تلگرام
 */
function sendMessage($chat_id, $message)
{
    $platform = PLATFORM;

    if ($platform == 'eitaa') {
        $url = "https://eitaa.com/bot" . BOT_TOKEN . "/sendMessage";
    } else {
        $url = "https://api.telegram.org/bot" . BOT_TOKEN . "/sendMessage";
    }

    $data = [
        'chat_id' => $chat_id,
        'text' => $message,
        'parse_mode' => 'HTML'
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return $http_code == 200;
}

/**
 * ساخت متن تبلیغ برای محصول
 */
function makeAdMessage($product)
{
    // قیمت با تخفیف
    $priceDisplay = number_format($product['price']) . " تومان";
    if ($product['price_old'] && $product['price_old'] > $product['price']) {
        $oldPrice = number_format($product['price_old']) . " تومان";
        $priceDisplay = "<del>{$oldPrice}</del> → <b>" . number_format($product['price']) . " تومان</b>";
        $saleBadge = "🔥 <b>حراج ویژه</b> 🔥\n";
    } else {
        $saleBadge = "";
    }

    // موجودی
    $stockText = $product['in_stock'] ? "✅ موجود در انبار" : "❌ ناموجود";

    // دسته‌بندی
    $categoryText = isset($product['category_name']) ? "📁 {$product['category_name']}" : "";

    // ساخت پیام
    $message = "════════════════════════════\n";
    $message .= "🛍️ <b>" . SHOP_NAME . "</b>\n";
    $message .= "════════════════════════════\n\n";
    $message .= $saleBadge;
    $message .= "📦 <b>محصول:</b> {$product['name']}\n";
    $message .= $categoryText ? $categoryText . "\n" : "";
    $message .= "🎨 <b>رنگ:</b> " . ($product['color'] ?? 'نامشخص') . "\n";
    $message .= "💰 <b>قیمت:</b> {$priceDisplay}\n";
    $message .= "📊 <b>وضعیت:</b> {$stockText}\n\n";
    $message .= "📝 <b>توضیحات:</b>\n";
    $message .= "{$product['description']}\n\n";
    $message .= "────────────────────────────────\n";
    $message .= "🔸 سفارش: " . SUPPORT_ID . "\n";
    $message .= "📞 پشتیبانی: " . SUPPORT_PHONE . "\n";
    $message .= "════════════════════════════";

    return $message;
}

/**
 * ارسال تبلیغ همه محصولات
 */
function sendCampaign()
{
    global $db;

    $products = $db->getAllProducts(ONLY_IN_STOCK);

    if (empty($products)) {
        echo "⚠️ هیچ محصولی برای تبلیغ وجود ندارد\n";
        return 0;
    }

    echo "📢 شروع کمپین تبلیغاتی...\n";
    echo "تعداد محصولات: " . count($products) . "\n";
    echo "-----------------------------------\n";

    $sent = 0;
    foreach ($products as $product) {
        $message = makeAdMessage($product);

        echo "📤 {$product['name']}... ";

        if (sendMessage(CHANNEL_ID, $message)) {
            echo "✅\n";
            $sent++;

            // ثبت لاگ
            $db->logMessage(null, $product['id'], $message, CHANNEL_ID, 'sent');
        } else {
            echo "❌\n";
            $db->logMessage(null, $product['id'], $message, CHANNEL_ID, 'failed');
        }

        sleep(SLEEP_INTERVAL);
    }

    // بروزرسانی آمار
    $db->updateDailyStats($sent, count($products));

    echo "-----------------------------------\n";
    echo "✅ ارسال شد: $sent از " . count($products) . "\n";

    return $sent;
}

/**
 * گرفتن آمار محصولات
 */
function getStatsReport()
{
    global $db;

    $topProducts = $db->getTopProducts(5);
    $stats = $db->getStats(7);

    $report = "📊 گزارش آمار فروشگاه\n";
    $report .= "════════════════════════\n\n";
    $report .= "🏆 محصولات پربازدید:\n";

    foreach ($topProducts as $p) {
        $report .= "• {$p['name']}\n";
        $report .= "  👁️ {$p['views']} بازدید | 🖱️ {$p['clicks']} کلیک\n";
        $report .= "  📈 نرخ تبدیل: {$p['conversion_rate']}%\n\n";
    }

    return $report;
}
