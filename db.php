<?php

// db.php - کلاس حرفه‌ای کار با دیتابیس

require_once 'config.php';

class Database
{
    private static $instance = null;
    private $connection;

    private function __construct()
    {
        $this->connect();
    }

    // اتصال به دیتابیس (Singleton Pattern)
    private function connect()
    {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $this->connection = new PDO($dsn, DB_USER, DB_PASS);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("خطای اتصال به دیتابیس: " . $e->getMessage());
        }
    }

    // گرفتن نمونه از کلاس (Singleton)
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    // گرفتن اتصال PDO
    public function getConnection()
    {
        return $this->connection;
    }

    // ========== محصولات ==========

    // گرفتن همه محصولات فعال
    public function getAllProducts($onlyInStock = false)
    {
        $sql = "SELECT p.*, c.name as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.status = 'active'";

        if ($onlyInStock) {
            $sql .= " AND p.in_stock = 1 AND p.quantity > 0";
        }

        $sql .= " ORDER BY p.sort_order ASC, p.id DESC";

        $stmt = $this->connection->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // گرفتن یک محصول با آیدی
    public function getProductById($id)
    {
        $sql = "SELECT p.*, c.name as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.id = :id";

        $stmt = $this->connection->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    // گرفتن محصولات یک دسته
    public function getProductsByCategory($categoryId)
    {
        $sql = "SELECT * FROM products 
                WHERE category_id = :category_id AND status = 'active' 
                ORDER BY sort_order ASC";

        $stmt = $this->connection->prepare($sql);
        $stmt->execute([':category_id' => $categoryId]);
        return $stmt->fetchAll();
    }

    // اضافه کردن بازدید محصول
    public function incrementProductViews($productId)
    {
        $sql = "UPDATE products SET views = views + 1 WHERE id = :id";
        $stmt = $this->connection->prepare($sql);
        return $stmt->execute([':id' => $productId]);
    }

    // اضافه کردن کلیک محصول
    public function incrementProductClicks($productId)
    {
        $sql = "UPDATE products SET clicks = clicks + 1 WHERE id = :id";
        $stmt = $this->connection->prepare($sql);
        return $stmt->execute([':id' => $productId]);
    }

    // آپدیت موجودی محصول
    public function updateProductStock($productId, $quantity)
    {
        $sql = "UPDATE products SET quantity = :quantity, 
                in_stock = CASE WHEN :quantity > 0 THEN 1 ELSE 0 END 
                WHERE id = :id";

        $stmt = $this->connection->prepare($sql);
        return $stmt->execute([':quantity' => $quantity, ':id' => $productId]);
    }

    // ========== دسته‌بندی‌ها ==========

    public function getAllCategories()
    {
        $sql = "SELECT * FROM categories WHERE status = 'active' ORDER BY sort_order ASC";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // ========== کمپین‌ها ==========

    public function getActiveCampaigns()
    {
        $sql = "SELECT * FROM campaigns 
                WHERE status = 'active' 
                AND start_date <= NOW() 
                AND end_date >= NOW()";

        $stmt = $this->connection->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function updateCampaignLastRun($campaignId)
    {
        $sql = "UPDATE campaigns SET last_run = NOW(), messages_sent = messages_sent + 1 
                WHERE id = :id";

        $stmt = $this->connection->prepare($sql);
        return $stmt->execute([':id' => $campaignId]);
    }

    // ========== لاگ پیام‌ها ==========

    public function logMessage($campaignId, $productId, $message, $chatId, $status = 'pending')
    {
        $sql = "INSERT INTO messages_log (campaign_id, product_id, message_text, chat_id, status) 
                VALUES (:campaign_id, :product_id, :message, :chat_id, :status)";

        $stmt = $this->connection->prepare($sql);
        return $stmt->execute([
            ':campaign_id' => $campaignId,
            ':product_id' => $productId,
            ':message' => $message,
            ':chat_id' => $chatId,
            ':status' => $status
        ]);
    }

    public function updateMessageStatus($messageId, $status, $error = null)
    {
        $sql = "UPDATE messages_log SET status = :status, error_message = :error, sent_at = NOW() 
                WHERE id = :id";

        $stmt = $this->connection->prepare($sql);
        return $stmt->execute([
            ':id' => $messageId,
            ':status' => $status,
            ':error' => $error
        ]);
    }

    // ========== آمار ==========

    public function updateDailyStats($messagesSent = 0, $productsShared = 0)
    {
        $today = date('Y-m-d');

        $sql = "INSERT INTO daily_stats (stat_date, messages_sent, products_shared, total_views, total_clicks) 
                VALUES (:date, :messages, :products, 0, 0)
                ON DUPLICATE KEY UPDATE 
                messages_sent = messages_sent + :messages,
                products_shared = products_shared + :products";

        $stmt = $this->connection->prepare($sql);
        return $stmt->execute([
            ':date' => $today,
            ':messages' => $messagesSent,
            ':products' => $productsShared
        ]);
    }

    public function getStats($days = 7)
    {
        $sql = "SELECT * FROM daily_stats 
                WHERE stat_date >= DATE_SUB(NOW(), INTERVAL :days DAY) 
                ORDER BY stat_date DESC";

        $stmt = $this->connection->prepare($sql);
        $stmt->execute([':days' => $days]);
        return $stmt->fetchAll();
    }

    // ========== محصولات پربازدید ==========

    public function getTopProducts($limit = 10)
    {
        $sql = "SELECT name, price, views, clicks, 
                CASE WHEN views > 0 THEN ROUND((clicks / views) * 100, 2) ELSE 0 END as conversion_rate
                FROM products 
                WHERE status = 'active' 
                ORDER BY views DESC 
                LIMIT :limit";

        $stmt = $this->connection->prepare($sql);
        $stmt->execute([':limit' => $limit]);
        return $stmt->fetchAll();
    }
}
