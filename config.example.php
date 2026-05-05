<?php

// config.example.php - کپی این فایل رو به config.php تغییر بده و توکن خودت رو وارد کن

// ========== تنظیمات دیتابیس ==========
define('DB_HOST', 'localhost');      // سرور دیتابیس
define('DB_NAME', 'product_bot');    // نام دیتابیس
define('DB_USER', 'root');           // نام کاربری
define('DB_PASS', '');               // رمز عبور

// ========== تنظیمات ربات ==========
// پلتفرم: 'eitaa' یا 'telegram'
define('PLATFORM', 'eitaa');

// توکن ربات (از @BotFather بگیر)
define('BOT_TOKEN', 'YOUR_BOT_TOKEN_HERE');

// آیدی کانال یا گروه (مثلاً @my_channel یا -100123456789)
define('CHANNEL_ID', '@my_channel');

// ========== تنظیمات فروشگاه ==========
define('SHOP_NAME', 'فروشگاه آنلاین من');
define('SUPPORT_PHONE', '09123456789');
define('SUPPORT_ID', '@shop_admin');

// ========== تنظیمات زمانی ==========
define('SLEEP_INTERVAL', 2);          // ثانیه بین هر پیام
define('CAMPAIGN_INTERVAL', 86400);    // ثانیه بین کمپین‌ها (86400 = 1 روز)

// ========== تنظیمات تبلیغ ==========
define('ONLY_IN_STOCK', true);         // فقط محصولات موجود
define('MAX_ADS_PER_DAY', 10);         // حداکثر تبلیغ در روز

// ========== تنظیمات امنیتی ==========
define('ADMIN_PASSWORD', 'admin123');   // رمز پنل مدیریت
