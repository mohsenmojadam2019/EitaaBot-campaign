<?php

// bot.php - اجرای اصلی ربات

require_once 'functions.php';

echo "\n";
echo "═══════════════════════════════════════\n";
echo "🤖 ربات تبلیغ محصولات\n";
echo "📱 پلتفرم: " . strtoupper(PLATFORM) . "\n";
echo "═══════════════════════════════════════\n\n";

echo "چه کاری می‌خواهید انجام دهید؟\n";
echo "1️⃣ ارسال کمپین تبلیغاتی (یکبار)\n";
echo "2️⃣ اجرای خودکار (حلقه بی‌نهایت)\n";
echo "3️⃣ نمایش آمار محصولات\n";
echo "0️⃣ خروج\n";
echo "\n➡️ انتخاب: ";

$choice = trim(fgets(STDIN));

switch ($choice) {
    case '1':
        sendCampaign();
        break;

    case '2':
        echo "\n🔄 اجرای خودکار...\n";
        echo "برای توقف Ctrl+C بزنید\n\n";

        while (true) {
            echo "[" . date('Y-m-d H:i:s') . "] اجرای کمپین...\n";
            sendCampaign();
            echo "\n⏳ کمپین بعدی در " . (CAMPAIGN_INTERVAL / 3600) . " ساعت\n";
            sleep(CAMPAIGN_INTERVAL);
        }
        break;

    case '3':
        echo "\n" . getStatsReport() . "\n";
        break;

    default:
        echo "خروج\n";
        break;
}
