<?php
/**
 * ASSI.CORE V1.0 — МОДУЛЬ ЖУРНАЛА (LOGS)
 * ПИШЕМ ТУТ ВСЁ, ШТО ПРИДЕТ В ГОЛОВУ. ПАТРЕГ ОДОБРЯЕТ.
 */

defined('ASSI_CORE_ACCESS') or die('КЫШ!');
$jou_dir = 'storage/journal/';
if (!is_dir($jou_dir)) { mkdir($jou_dir, 0777, true); }

// 1. АДМИН-ЛОГИКА (ПРАВКА / УДАЛЕНИЕ)
if (isset($_SESSION['auth'])) {
    $id = (int)($_GET['id'] ?? 0);

    // УДАЛЕНИЕ ТОЛЬКО КАРТИНКИ (ХИРУРГИЯ)
    if (isset($_GET['act']) && $_GET['act'] == 'edit' && isset($_GET['del_file']) && $id > 0) {
        $st = $pdo->prepare("SELECT file FROM journal WHERE id=?");
        $st->execute([$id]); $f = $st->fetch();
        if ($f && !empty($f['file'])) {
            if (file_exists($jou_dir . $f['file'])) { unlink($jou_dir . $f['file']); }
            $pdo->prepare("UPDATE journal SET file = '' WHERE id = ?")->execute([$id]);
        }
        header("Location: /?route=journal&act=edit&id=" . $id); exit;
    }

    // ПОЛНОЕ УДАЛЕНИЕ ПОСТА (СЖИГАЕМ МОСТЫ)
    if (isset($_GET['act']) && $_GET['act'] == 'del' && $id > 0) {
        $st = $pdo->prepare("SELECT file FROM journal WHERE id=?");
        $st->execute([$id]); $f = $st->fetch();
        if ($f && !empty($f['file']) && file_exists($jou_dir . $f['file'])) { unlink($jou_dir . $f['file']); }
        $pdo->prepare("DELETE FROM journal WHERE id = ?")->execute([$id]);
        header("Location: /?route=journal"); exit;
    }

    // СОХРАНЕНИЕ (НОВЫЙ ИЛИ АПДЕЙТ)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_btn'])) {
        $t = trim($_POST['title']); $c = trim($_POST['content']); $uploaded_file = "";
        if (!empty($_FILES['file']['name'])) {
            $fn = $_FILES['file']['name'];
            $file_name = time() . "_" . preg_replace("/[^a-zA-Z0-9\._-]/", "", $fn);
            if (move_uploaded_file($_FILES['file']['tmp_name'], $jou_dir . $file_name)) { $uploaded_file = $file_name; }
        }
        if ($id > 0) {
            if (!empty($uploaded_file)) { $pdo->prepare("UPDATE journal SET title=?, content=?, file=? WHERE id=?")->execute([$t, $c, $uploaded_file, $id]); }
            else { $pdo->prepare("UPDATE journal SET title=?, content=? WHERE id=?")->execute([$t, $c, $id]); }
        } else { $pdo->prepare("INSERT INTO journal (title, content, file) VALUES (?, ?, ?)")->execute([$t, $c, $uploaded_file]); }
        header("Location: /?route=journal"); exit;
    }
}

// 2. ИНТЕРФЕЙС ПРАВКИ (МОРДА РЕДАКТОРА)
if (isset($_SESSION['auth']) && isset($_GET['act']) && ($_GET['act'] == 'add' || $_GET['act'] == 'edit')) {
    $id = (int)($_GET['id'] ?? 0);
    $p = ['title' => '', 'content' => '', 'file' => ''];
    if ($id > 0) { $st = $pdo->prepare("SELECT * FROM journal WHERE id=?"); $st->execute([$id]); $p = $st->fetch() ?: $p; }
    echo "<h2 style='text-align:center;'>/ ПРАВКА ЗАМЕТКИ</h2>
    <form method='POST' enctype='multipart/form-data' style='width:100%; box-sizing:border-box;'>
        <input type='text' name='title' value='".htmlspecialchars($p['title'])."' placeholder='ЗАГОЛОВОК' required style='width:100%; border:1px solid #000; padding:12px; margin-bottom:15px; box-sizing:border-box;'>
        <textarea name='content' placeholder='ПИШИ ТУТ СВОИ МЫСЛИ...' required style='width:100%; height:550px; border:1px solid #000; padding:15px; margin-bottom:20px; box-sizing:border-box; font-family:monospace; line-height:1.5;'>".htmlspecialchars($p['content'])."</textarea>
        <div style='border:1px solid #000; padding:20px; margin-bottom:25px; background:#fff;'><b>ВЛОЖЕНИЕ:</b> <input type='file' name='file'></div>
        <button type='submit' name='save_btn' style='width:100%; background:#000; color:#fff; font-weight:900; padding:20px; border:none; cursor:pointer;'>ЗАВАРИТЬ В ЖУРНАЛ</button>
        <p style='text-align:center; margin-top:20px;'><a href='/?route=journal' style='color:#666;'>[ ОТМЕНА ]</a></p>
    </form>"; return;
}

// 3. ВЫВОД ЛОГОВ (БЕСШОВНЫЙ КАТ)
echo "<h2>ЖУРНАЛ</h2><hr>";
$stmt = $pdo->query("SELECT * FROM journal ORDER BY id DESC");

while ($row = $stmt->fetch()) {
    $ch_id = "ch_jou_" . $row['id'];
    $filePath = "/" . $jou_dir . $row['file'];
    
    echo "<article class='post' style='margin-bottom:80px;'>
            <h1 class='title'>/ ".htmlspecialchars($row['title'])."</h1>
            <input type='checkbox' id='{$ch_id}' class='post-check' style='display:none;'>
            <div class='post-cut'>
                <div class='content' style='font-size:1.1em; line-height:1.6;'>";
                if (!empty($row['file'])) {
                    echo "<div class='visual' style='margin: 20px 0;'><img src='{$filePath}' onclick='openMisty(\"{$filePath}\")' style='max-width:100%; border:2px solid #000; cursor:pointer;'></div>";
                }
                echo nl2br(htmlspecialchars($row['content']));
            echo "</div>
            </div>
            <div class='btn-wrapper' style='margin-top:10px;'>
                <label for='{$ch_id}' class='btn-expand' data-open='Свернуть ∧' data-close='Развернуть ∨'></label>
            </div>";

    if (isset($_SESSION['auth'])) {
        echo "<div class='admin-links' style='margin-top:25px; border-top:1px solid #ffb3d9; padding-top:10px;'>
                <a href='/?route=journal&act=edit&id={$row['id']}'>Править</a> | 
                <a href='/?route=journal&act=del&id={$row['id']}' onclick='return confirm(\"Стереть?\")'>Удалить</a>
              </div>";
    }
    echo "</article><hr style='border:1px dashed #ffb3d9; margin:40px 0;'>";
}

