<?php
/**
 * ASSI.CORE V1.0 — АРХИВ (OLD SCHOOL STORAGE)
 * ТУТ ЛЕЖИТ ТО, ШТО УЖЕ ОТГРЕМЕЛО. ПЫЛЬ, ТЛЕН И СТАЛЬ.
 */

defined('ASSI_CORE_ACCESS') or die('КЫШ!');
$lib_dir = 'storage/lib/';

// 1. ШАПКА РАЗДЕЛА (НАВИГАЦИЯ ПО СКЛАДУ)
echo "<div class='module-header' style='display:flex; justify-content:space-between; align-items:center;'>
        <h2>АРХИФ НА СЛЕДИЯ</h2>
        <a href='/?route=librares' style='color:#ff007f; text-decoration:none; font-weight:900;'>← Ф БИБЛИОТЕКУ</a>
      </div><hr style='border:1px dashed #ffb3d9; margin:20px 0;'>";

// 2. ВЫВОД ИЗ СЕЙФА (ТОЛЬКО ТО, ШТО ЗАКОНСЕРВИРОВАНО)
$stmt = $pdo->query("SELECT * FROM lib_topics WHERE is_archive = 1 ORDER BY id DESC");
$count = 0;

while ($row = $stmt->fetch()) {
    $count++;
    $ch_id = "ch_arc_" . $row['id'];

    // КЛАСС post ИЗ ГАРДЕРОБА, НО ЧУТЬ ПРИТУШИМ ЦВЕТА (ОЛДСКУЛ-ТЛЕН)
    echo "<article class='post' style='margin-bottom:60px; opacity:0.8;'>
            <h1 class='title' style='color:#888;'>/ " . htmlspecialchars($row['title']) . " " . ($row['file_path'] ? "📎" : "") . "</h1>";

    // МАГИЯ ЧЕКБОКСА (БЕСШОВНЫЙ КАТ — СИЛЬНЫЙ И НЕЗАВИСИМЫЙ)
    echo "<input type='checkbox' id='{$ch_id}' class='post-check' style='display:none;'>";

    echo "<div class='post-cut'>
            <div class='content' style='font-size:1.1em; line-height:1.6;'>
                " . nl2br(htmlspecialchars($row['content'])) . "";

                if (!empty($row['file_path'])) {
                    echo "<p style='margin-top:20px; padding:15px; border:1px dashed #888; background:#eee;'>
                          📦 <b>СТАРЫЙ ФАЙЛ:</b> 
                          <a href='/{$lib_dir}{$row['file_path']}' download='{$row['file_path']}' style='color:#555;'>СКАЧАТЬ (" . pathinfo($row['file_path'], PATHINFO_EXTENSION) . ")</a>
                          </p>";
                }
    echo "    </div>
          </div>";

    // КНОПКА-ПЕРЕКЛЮЧАТЕЛЬ (LABEL)
    echo "<div class='btn-wrapper' style='margin-top:10px;'>
            <label for='{$ch_id}' class='btn-expand' data-open='СВЕРНУТЬ ∧' data-close='РАЗВЕРНУТЬ ∨'></label>
          </div>";

    if (isset($_SESSION['auth'])) {
        echo "<div class='admin-links' style='margin-top:20px; border-top:1px solid #ccc; padding-top:10px;'>
                <a href='/?route=librares&act=del&id={$row['id']}' style='color:red;' onclick='return confirm(\"УДАЛИТЬ ИЗ ТЛЕНА НАВСЕГДА?\")'>Удалить</a>
              </div>";
    }
    echo "</article><hr style='border:1px dashed #ccc; margin:40px 0;'>";
}

// ЕСЛИ ТУТ ВАЩЕ НИКОГО НЕТ
if ($count == 0) {
    echo "<p style='color:#888; font-style:italic; text-align:center;'>СКЛАД ПУСТ. ФСЯ СТАЛЬ Ф РАБОТЕ.</p>";
}

