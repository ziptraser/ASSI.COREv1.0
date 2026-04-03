<?php
/**
 * ASSI.CORE V1.0 — ГЛАВНЫЙ МОСК СИСТЕМЫ [PUBLIC_EDITION]
 * СДЕЛАНО ПО ХАРДКОРУ. ПАТРЕГ СМОТРИТ НА ТЕБЯ КАК НА ГОВНО.
 */

// 1. СТАРТ СЕКУНДОМЕРА И ГЛУШИЛКА ВАРНИНГОВ
$start_time = microtime(true);
error_reporting(0); // Юзерам нех смотреть в потроха.

// ПРОВЕРКА КЛЮЧА — КТО НЕ С НАМИ, ТОТ КЫШ!
defined('ASSI_CORE_ACCESS') or die('КЫШ!');

// ПУТИ ДО БАЙТА
require_once __DIR__ . '/config.php';
define('ROOT_DIR', dirname(__DIR__) . '/');

// 2. ФИНГЕРПРИНТ СЕССИЙ (ШТОП НЕ УГНАЛИ)
session_start();
$fingerprint = md5($_SERVER['HTTP_USER_AGENT'] . $_SERVER['REMOTE_ADDR'] . SALT);
if (isset($_SESSION['auth']) && ($_SESSION['fingerprint'] ?? '') !== $fingerprint) {
    session_destroy(); 
    header("Location: /?route=auth"); 
    exit;
}

// 3. КОННЕКТ К СЕЙФУ (PDO)
try {
    $dsn = "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=".DB_CHAR;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) { 
    die("СЕЙФ ЗАКЛИНИЛО. ПИШИ АДМИНУ."); 
}

// 4. СТАТИСТИКА И ОНЛАЙН
$stats = $pdo->query("SELECT total_views FROM site_stats WHERE id = 1")->fetch();
$total_views = $stats['total_views'] ?? 0;

if (empty($_GET['route'])) {
    $pdo->query("UPDATE site_stats SET total_views = total_views + 1 WHERE id = 1");
}

$session_id = session_id(); $current_time = time();
$pdo->prepare("REPLACE INTO online_users (session_id, last_activity) VALUES (?, ?)")
    ->execute([$session_id, $current_time]);
$pdo->prepare("DELETE FROM online_users WHERE last_activity < ?")
    ->execute([$current_time - 300]);
$online_count = $pdo->query("SELECT SQL_NO_CACHE COUNT(*) FROM online_users")->fetchColumn();

// 5. ДЕФОЛТНЫЕ МЕТА-ДАННЫЕ (МЕНЯЙ ПОД СЕБЯ)
$page_title = "ASSI.CORE — МОНОЛИТ НАСЛЕДИЯ";
$page_desc = "ЛЕГКИЙ И БЫСТРЫЙ ДВИЖОК НА ЧИСТОМ PHP.";

// --- [ ПУЛЬТ УПРАВЛЕНИЯ МОДУЛЯМИ (РУБИЛЬНИК) ] ---
$modules = [
    'journal'   => 'ЛОГИ',
    'librares'  => 'БИБЛИОТЕКА',
    'archive'   => 'АРХИВ',
    'auth'      => '' 
];

// 6. ОБРАБОТКА РОУТИНГА И ВЫВОД СМЫСЛА
$route = preg_replace('/[^a-z0-9_]/', '', $_GET['route'] ?? '');
$module_output = '';

// ПЫТАЕМСЯ ПОДЦЕПИТЬ МОДУЛЬ
if (!empty($route) && isset($modules[$route])) {
    $module_file = ROOT_DIR . 'modules/' . $route . '.php';
    if (file_exists($module_file)) {
        ob_start();
        require_once $module_file;
        $module_output = ob_get_clean();
    }
}

// ЕСЛИ ПУСТО (ГЛАВНАЯ ИЛИ МОДУЛЬ В ТУМАНЕ) — ПОКАЗЫВАЕМ ПЕРВЫЙ ПОСТ
if (empty($module_output)) {
    $stmt = $pdo->query("SELECT * FROM journal ORDER BY id ASC LIMIT 1");
    $post = $stmt->fetch();
    if ($post) {
        $page_title = htmlspecialchars($post['title']) . " | ASSI.CORE";
        $module_output = "<article class='welcome-post'>";
        if (!empty($post['file'])) {
            $f_path = "/storage/journal/" . $post['file'];
            $module_output .= "<div class='visual'><img src='{$f_path}' onclick='openMisty(\"{$f_path}\")' style='max-width:100%; cursor:pointer; border:1px solid #444;'></div>";
        }
        $module_output .= "<h1>" . htmlspecialchars($post['title']) . "</h1>
            <div class='content'>" . nl2br(htmlspecialchars($post['content'])) . "</div>
        </article>";
    } else {
        $module_output = "<article><h1>HELLO WORLD!</h1><p>СИСТЕМА ЧИСТА. НАПИШИ ПЕРВЫЙ ПОСТ В ЖУРНАЛ, И ОН СТАНЕТ ГЛАВНОЙ.</p></article>";
    }
}

// 7. ВЫВОД ГАРДЕРОБА (ФИНАЛЬНЫЙ ШТРИХ)
require_once ROOT_DIR . 'templates/main.php';
