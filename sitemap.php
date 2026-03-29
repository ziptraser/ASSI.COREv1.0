<?php
/**
 * ASSI.CORE V1.0 — КАРТА САЙТА (SITEMAP XML)
 * ШТОП ГУГЛ И ЯНДЕКС НЕ ТУПИЛИ И ВИДЕЛИ ВСЕ НАШИ НИШТЯКИ.
 */

header("Content-Type: application/xml; charset=utf-8");
require_once __DIR__ . '/core/config.php';

// ОПРЕДЕЛЯЕМ БАЗОВЫЙ АДРЕС НА ЛЕТУ (ШТОП РАБОТАЛО НА ЛЮБОМ ДОМЕНЕ)
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$base_url = $protocol . $_SERVER['HTTP_HOST'];

// КОННЕКТ К СЕЙФУ (PDO)
try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=".DB_CHAR, DB_USER, DB_PASS);
} catch (PDOException $e) { 
    header("HTTP/1.1 500 Internal Server Error"); 
    die(); 
}

echo '<?xml version="1.0" encoding="UTF-8"?>';
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

// 1. СТАТИЧЕСКИЕ РАЗДЕЛЫ (ГЛАВНЫЕ ВХОДЫ)
$static = ['journal', 'librares', 'archive'];
foreach($static as $s) {
    echo "<url><loc>{$base_url}/?route={$s}</loc><changefreq>daily</changefreq><priority>0.8</priority></url>";
}

// 2. ДИНАМИЧЕСКИЕ ССЫЛКИ (ЖУРНАЛ — ВСАСЫВАЕМ ИЗ JOURNAL)
$stmt = $pdo->query("SELECT id FROM journal ORDER BY id DESC");
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "<url><loc>{$base_url}/?route=journal&amp;id={$row['id']}</loc><changefreq>weekly</changefreq><priority>0.5</priority></url>";
}

// 3. ДИНАМИЧЕСКИЕ ССЫЛКИ (БИБЛИОТЕКА — ВСАСЫВАЕМ ИЗ LIB_TOPICS)
$stmt_lib = $pdo->query("SELECT id FROM lib_topics WHERE is_archive = 0 ORDER BY id DESC");
while($lib = $stmt_lib->fetch(PDO::FETCH_ASSOC)) {
    echo "<url><loc>{$base_url}/?route=librares&amp;id={$lib['id']}</loc><changefreq>weekly</changefreq><priority>0.6</priority></url>";
}

echo '</urlset>';


