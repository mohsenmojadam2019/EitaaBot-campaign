<?php

// install.php - نصب و راه‌اندازی اولیه

echo "\n";
echo "═══════════════════════════════════════\n";
echo "🔧 نصب ربات تبلیغ محصولات\n";
echo "═══════════════════════════════════════\n\n";

// بررسی فایل config.php
if (!file_exists('config.php')) {
    echo "❌ فایل config.php یافت نشد!\n";
    echo "📝 لطفاً config.example.php را کپی کرده و نام آن را به config.php تغییر دهید.\n";
    exit;
}

require_once 'config.php';

// اتصال به دیتابیس
try {
    $pdo = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ اتصال به MySQL موفق\n";
} catch (PDOException $e) {
    echo "❌ خطا در اتصال به MySQL: " . $e->getMessage() . "\n";
    exit;
}

// خواندن فایل SQL
$sqlFile = file_get_contents('database.sql');
if (!$sqlFile) {
    echo "❌ فایل database.sql یافت نشد!\n";
    exit;
}

// اجرای دستورات SQL
try {
    $pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME);
    $pdo->exec("USE " . DB_NAME);

    // تکه تکه اجرا کردن (برای جلوگیری از خطا)
    $queries = explode(';', $sqlFile);
    foreach ($queries as $query) {
        $query = trim($query);
        if (!empty($query)) {
            $pdo->exec($query);
        }
    }

    echo "✅ ساختار دیتابیس با موفقیت ایجاد شد\n";
    echo "✅ اطلاعات نمونه درج شد\n";

} catch (PDOException $e) {
    echo "❌ خطا در اجرای SQL: " . $e->getMessage() . "\n";
    exit;
}

echo "\n═══════════════════════════════════════\n";
echo "🎉 نصب با موفقیت انجام شد!\n";
echo "═══════════════════════════════════════\n\n";
echo "🔑 اطلاعات ورود به پنل مدیریت:\n";
echo "   کاربر: admin\n";
echo "   رمز: admin123\n\n";
echo "🚀 برای اجرای ربات:\n";
echo "   php bot.php\n\n";
echo "📊 برای مشاهده پنل مدیریت:\n";
echo "   php admin.php\n";
