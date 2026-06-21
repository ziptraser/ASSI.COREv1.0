<?php
/**
 * ASSI.CORE V1.0 — РОЖДЕНИЕ МОНОЛИТА (INSTALLER)
 * ПРИШЁЛ, УВИДЕЛ, ИНСТАЛЛИРОВАЛ. ПАТРЕГ СМОТРИТ И УЛЫБАЕТСЯ.
 */
error_reporting(0); 

$config_file = __DIR__ . '/core/config.php';

// БЛОКИРОВКА ИНСТАЛЛЕРА (ПАРАНОЙЯ УРОВЕНЬ 1)
if (file_exists($config_file)) {
    die("<div style='background:#000; color:red; padding:50px; font-family:monospace;'>СИСТЕМА УЖЕ УСТАНОВЛЕНА. УДАЛИ install.php ОТ ГРЕХА ПОДАЛЬШЕ.</div>");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['install_btn'])) {
    $db_h = $_POST['db_host'] ?? 'localhost'; 
    $db_n = $_POST['db_name'] ?? '';
    $db_u = $_POST['db_user'] ?? ''; 
    $db_p = $_POST['db_pass'] ?? '';
    $salt = md5(time() . "assi_p_salt"); 

    try {
        // 1. КОННЕКТ К СЕРВЕРУ
        $pdo = new PDO("mysql:host=$db_h;charset=utf8mb4", $db_u, $db_p);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // 2. СОЗДАЕМ БАЗУ (ДЛЯ ТЕХ, КТО НЕ ВКУРИВАЕТ) И ТАБЛИЦЫ
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_n` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `$db_n` ");

        // ТАБЛИЦЫ (СТРОИМ БУНКЕР)
        $pdo->exec("CREATE TABLE IF NOT EXISTS `journal` (id INT AUTO_INCREMENT PRIMARY KEY, title VARCHAR(255), content TEXT, file VARCHAR(255))");
        $pdo->exec("CREATE TABLE IF NOT EXISTS `lib_topics` (id INT AUTO_INCREMENT PRIMARY KEY, title VARCHAR(255), content TEXT, file_path VARCHAR(255), is_archive TINYINT(1) DEFAULT 0, downloads INT DEFAULT 0)");
        $pdo->exec("CREATE TABLE IF NOT EXISTS `lib_comments` (id INT AUTO_INCREMENT PRIMARY KEY, topic_id INT, user_name VARCHAR(100), comment_text TEXT, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
        $pdo->exec("CREATE TABLE IF NOT EXISTS `online_users` (session_id VARCHAR(255) PRIMARY KEY, last_activity INT)");
        $pdo->exec("CREATE TABLE IF NOT EXISTS `site_stats` (id INT PRIMARY KEY, total_views INT DEFAULT 0)");
        
        // 3. ИНИЦИАЛИЗАЦИЯ (ПЕРВЫЙ ВДОХ)
        $pdo->exec("INSERT IGNORE INTO site_stats (id, total_views) VALUES (1, 1)");
        $pdo->prepare("INSERT INTO journal (title, content) VALUES (?, ?)")
            ->execute(['HELLO WORLD!', 'ASSI.CORE v1.0 успешно заведен. Напиши этот первый пост, и он станет лицом твоего сайта.']);

        // 4. СТРУКТУРА ПАПОК
        $storage_dirs = ['storage/journal', 'storage/lib', 'core'];
        foreach ($storage_dirs as $dir) {
            $path = __DIR__ . '/' . $dir;
            if (!is_dir($path)) { mkdir($path, 0755, true); }
            if ($dir !== 'core') {
                $htaccess = "Options -ExecCGI -Indexes\nphp_flag engine off\n<FilesMatch \"\.(php|phtml|php3|php4|php5|php7|php8|cgi|pl|py|asp|jsp)$\">\nOrder allow,deny\nDeny from all\n</FilesMatch>";
                file_put_contents($path . '/.htaccess', $htaccess);
            }
        }

        // 5. ГЕНЕРАЦИЯ КОНФИГА
        $config_file = __DIR__ . '/core/config.php';
        $config_data = "<?php\ndefined('ASSI_CORE_ACCESS') or die('Access Denied');\n\ndefine('DB_HOST', '$db_h');\ndefine('DB_NAME', '$db_n');\ndefine('DB_USER', '$db_u');\ndefine('DB_PASS', '$db_p');\ndefine('DB_CHAR', 'utf8mb4');\n\ndefine('SALT', '$salt');\ndefine('BASE_DIR', __DIR__ . '/../');\ndefine('FILE_DIR', BASE_DIR . 'storage/');\n";
        
        if (file_put_contents($config_file, $config_data)) {
            
            // --- [ МАЯК: ОДНОКРАТНЫЙ ПИНГ НА ГЛАВНЫЙ СЕРВЕР ] ---
            $opts = ["http" => ["method" => "GET", "header" => "User-Agent: ASSI_CORE_INSTALLER\r\n"]];
            $context = stream_context_create($opts);
            @file_get_contents("https://zassyha.ru", false, $context);

            echo "<div style='text-align:center; padding:50px; font-family:monospace; background:#000; color:#00ff41; height:100vh;'>";
            echo "<h2>SYSTEM INSTALLED SUCCESSFULLY</h2>";
            echo "<p style='color:#ff007f;'>ВНИМАНИЕ: УДАЛИ install.php НЕМЕДЛЕННО!</p>";
            echo "<br><a href='index.php' style='color:#00ff41; border:1px solid #00ff41; padding:10px 20px; text-decoration:none;'>[ ВОЙТИ В МОНОЛИТ ]</a>";
            echo "</div>";
            exit;
        }

    } catch (PDOException $e) { 
        die("ОШИБКА УСТАНОВКИ: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>ASSI.CORE v1.0 — Installer</title>
    <style>
        body { background: #111; color: #ccc; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; font-family: monospace; }
        form { background: #222; width: 350px; padding: 40px; border: 1px solid #333; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        h2 { text-align: center; margin-top: 0; color: #00ff41; text-shadow: 0 0 5px #00ff41; }
        input { width: 100%; box-sizing: border-box; margin-bottom: 20px; padding: 12px; background: #000; border: 1px solid #444; color: #00ff41; outline: none; }
        input:focus { border-color: #00ff41; }
        button { width: 100%; padding: 15px; background: #00ff41; color: #000; border: none; cursor: pointer; font-weight: bold; text-transform: uppercase; }
        label { font-size: 0.8em; color: #888; display: block; margin-bottom: 5px; }
    </style>
</head>
<body>
    <form method="POST">
        <h2>ASSI.CORE v1.0</h2>
        <label>DATABASE HOST</label>
        <input type="text" name="db_host" value="localhost" required>
        <label>DATABASE NAME</label>
        <input type="text" name="db_name" required placeholder="base_name">
        <label>DATABASE USER</label>
        <input type="text" name="db_user" required placeholder="root">
        <label>DATABASE PASSWORD</label>
        <input type="password" name="db_pass" placeholder="********">
        <button type="submit" name="install_btn">DEPLOY MONOLITH</button>
    </form>
</body>
</html>
