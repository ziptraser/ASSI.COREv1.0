<?php
/**
 * ASSI.CORE V1.0 — МОДУЛЬ БИБЛИОТЕКИ (STORAGE & DISCUSSIONS)
 * ХРАНИМ ТУТ ВСЯКОЕ И ТРЕМ ЗА ЖИЗНЬ. БОТЫ ИДУТ ЛЕСОМ.
 */

defined('ASSI_CORE_ACCESS') or die('КЫШ!');
$lib_dir = 'storage/lib/';
if (!is_dir($lib_dir)) { mkdir($lib_dir, 0777, true); }

// --- [ 1. ХИТРЫЙ СЧЁТЧИК СКАЧИВАНИЙ (DOWNLOADS) ] ---
if (isset($_GET['get_file'])) {
    $id = (int)($_GET['id'] ?? 0);
    if ($id > 0) {
        $st = $pdo->prepare("SELECT file_path FROM lib_topics WHERE id = ?");
        $st->execute([$id]); $f = $st->fetch();
        if ($f && !empty($f['file_path'])) {
            $pdo->prepare("UPDATE lib_topics SET downloads = downloads + 1 WHERE id = ?")->execute([$id]);
            header("Location: /" . $lib_dir . $f['file_path']); exit;
        }
    }
}

// --- [ 2. ПРИЕМ КОММЕНТАРИЯ (ULTRA SECURITY + ANTI-SPAM) ] ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_comm_btn'])) {
    $topic_id = (int)$_POST['topic_id'];
    $name = htmlspecialchars(trim($_POST['user_name']));
    $text = htmlspecialchars(trim($_POST['comm_text']));
    $honeypot = $_POST['mail_recheck'] ?? ''; 

    // СХЛОПЫВАЕМ ТЕКСТ В ТУГУЮ СТРОКУ И ЩЕМИМ РЕКЛАМЩИКОВ
    $clean_check = preg_replace('/[^a-z0-9]/', '', strtolower($name . $text));
    $spam_words = ['http', 'https', 'www', 'ftp', 'com', 'net', 'org', 'biz', 'info', 'xyz', 'top', 'click', 'work', 'site', 'online', 'su', 'vkcom', 'okru', 'tme'];
    
    $is_spam = false;
    foreach ($spam_words as $word) { if (strpos($clean_check, $word) !== false) { $is_spam = true; break; } }

    // В ПЕЧКУ ВСЁ, ШТО ПОХОЖЕ НА СПАМ ИЛИ ПУСТОТУ
    if (empty($honeypot) && !$is_spam && !empty($name) && !empty($text) && $topic_id > 0) {
        $pdo->prepare("INSERT INTO lib_comments (topic_id, user_name, comment_text) VALUES (?, ?, ?)")
            ->execute(); // ФИКС: ТЕПЕРЬ ДАННЫЕ ВЛЕТАЮТ В СЕЙФ
    }
    header("Location: /?route=librares"); exit;
}

// --- [ 3. АДМИН-ЛОГИКА (УДАЛЕНИЕ / АРХИВ) ] ---
if (isset($_SESSION['auth'])) {
    $id = (int)($_GET['id'] ?? 0);
    
    // ТРЁМ ЛИШНИЙ БАЗАР В КОММЕНТАХ
    if (isset($_GET['act']) && $_GET['act'] == 'del_comm') {
        $comm_id = (int)($_GET['comm_id'] ?? 0);
        $pdo->prepare("DELETE FROM lib_comments WHERE id = ?")->execute([$comm_id]);
        header("Location: /?route=librares"); exit;
    }

    // ПОЛНОЕ УДАЛЕНИЕ ПРОЕКТА (С КОНЦАМИ)
    if (isset($_GET['act']) && $_GET['act'] == 'del' && $id > 0) {
        $st = $pdo->prepare("SELECT file_path FROM lib_topics WHERE id=?");
        $st->execute([$id]); $f = $st->fetch();
        if ($f && !empty($f['file_path']) && file_exists($lib_dir . $f['file_path'])) { unlink($lib_dir . $f['file_path']); }
        $pdo->prepare("DELETE FROM lib_topics WHERE id=?")->execute([$id]);
        $pdo->prepare("DELETE FROM lib_comments WHERE topic_id=?")->execute([$id]); 
        header("Location: /?route=librares"); exit;
    }

    // В АРХИВ — ГЛАЗА МОИ БЫ НЕ ВИДЕЛИ
    if (isset($_GET['act']) && $_GET['act'] == 'to_archive' && $id > 0) {
        $pdo->prepare("UPDATE lib_topics SET is_archive = 1 WHERE id = ?")->execute([$id]);
        header("Location: /?route=librares"); exit;
    }

    // СОХРАНЕНИЕ / ОБНОВЛЕНИЕ
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['save_lib']))) {
        $t = trim($_POST['title']); $c = trim($_POST['content']); $uploaded_file = "";
        if (!empty($_FILES['lib_file']['name'])) {
            $fn = $_FILES['lib_file']['name'];
            $file_name = time() . "_" . preg_replace("/[^a-zA-Z0-9\._-]/", "", $fn);
            if (move_uploaded_file($_FILES['lib_file']['tmp_name'], $lib_dir . $file_name)) { $uploaded_file = $file_name; }
        }
        if ($id > 0) {
            if (!empty($uploaded_file)) { $pdo->prepare("UPDATE lib_topics SET title=?, content=?, file_path=? WHERE id=?")->execute([$t, $c, $uploaded_file, $id]); }
            else { $pdo->prepare("UPDATE lib_topics SET title=?, content=? WHERE id=?")->execute([$t, $c, $id]); }
        } else { $pdo->prepare("INSERT INTO lib_topics (title, content, file_path, is_archive, downloads) VALUES (?, ?, ?, 0, 0)")->execute([$t, $c, $uploaded_file]); }
        header("Location: /?route=librares"); exit;
    }
}

// --- [ 4. ИНТЕРФЕЙС ПРАВКИ (РЕДАКТОР) ] ---
if (isset($_SESSION['auth']) && isset($_GET['act']) && ($_GET['act'] == 'add' || $_GET['act'] == 'edit')) {
    $id = (int)($_GET['id'] ?? 0);
    $p = ['title' => '', 'content' => '', 'file_path' => ''];
    if ($id > 0) { $st = $pdo->prepare("SELECT * FROM lib_topics WHERE id=?"); $st->execute([$id]); $p = $st->fetch() ?: $p; }
    echo "<h2 style='text-align:center;'>/ ПРАВКА ПРОЕКТА</h2>
    <form method='POST' enctype='multipart/form-data' style='width:100%; box-sizing:border-box;'>
        <input type='text' name='title' value='".htmlspecialchars($p['title'])."' placeholder='НАЗВАНИЕ' required style='width:100%; border:1px solid #000; padding:12px; margin-bottom:15px; box-sizing:border-box;'>
        <textarea name='content' placeholder='ОПИСАНИЕ И ПОДРОБНОСТИ...' required style='width:100%; height:550px; border:1px solid #000; padding:15px; margin-bottom:20px; box-sizing:border-box; font-family:monospace; line-height:1.5;'>".htmlspecialchars($p['content'])."</textarea>
        <div style='border:1px solid #000; padding:20px; margin-bottom:25px; background:#fff;'><b>ФАЙЛ:</b> <input type='file' name='lib_file'></div>
        <button type='submit' name='save_lib' style='width:100%; background:#000; color:#fff; font-weight:900; padding:20px; border:none; cursor:pointer;'>ОТПРАВИТЬ В ХРАНИЛИЩЕ</button>
        <p style='text-align:center; margin-top:20px;'><a href='/?route=librares' style='color:#666;'>[ ОТМЕНА ]</a></p>
    </form>"; return;
}

// --- [ 5. ВЫВОД СПИСКА ПРОЕКТОВ (БЕСШОВНЫЙ КАТ) ] ---
echo "<h2>БИБЛИОТЕКА</h2><hr>";
$topics = $pdo->query("SELECT * FROM lib_topics WHERE is_archive = 0 ORDER BY id DESC");

while ($row = $topics->fetch()) {
    $downs = (int)($row['downloads'] ?? 0);
    $ch_id = "ch_lib_" . $row['id'];
    
    echo "<article class='post' style='margin-bottom:80px;'>
            <h1 class='title'>/ ".htmlspecialchars($row['title'])." " . ($row['file_path'] ? "📎<small>({$downs})</small>" : "") . "</h1>
            <input type='checkbox' id='{$ch_id}' class='post-check' style='display:none;'>
            <div class='post-cut'>
                <div class='content' style='font-size:1.1em; line-height:1.6;'>
                    ".nl2br(htmlspecialchars($row['content']))."";
                    if (!empty($row['file_path'])) {
                        echo "<p style='margin-top:20px; padding:15px; border:1px dashed #ff007f; background:#fff;'>
                              📦 <b>ФАЙЛ:</b> <a href='/?route=librares&get_file=1&id={$row['id']}' style='color:#ff007f; font-weight:bold;'>СКАЧАТЬ</a>
                              <span style='font-size:0.8em; color:#999; margin-left:10px;'>[ ЗАГРУЗОК: {$downs} ]</span>
                              </p>";
                    }
                echo "</div>";

                // ОБСУЖДЕНИЯ (ВНУТРИ КАТА)
                echo "<div class='discussions' style='margin-top:40px; border-top:2px solid #000; padding-top:20px;'>
                        <h3 style='color:#ff007f; font-size:1.1em;'>ОБСУЖДЕНИЕ:</h3>";
                $comms = $pdo->prepare("SELECT * FROM lib_comments WHERE topic_id = ? ORDER BY id ASC");
                $comms->execute([$row['id']]);
                while($c = $comms->fetch()) {
                    echo "<div style='margin-bottom:15px; padding:10px; background:rgba(255,255,255,0.4); border-left:3px solid #ff007f;'>
                            <b style='font-size:0.8em; text-transform:uppercase;'>{$c['user_name']}:</b>
                            <div style='font-size:0.95em;'>".nl2br($c['comment_text'])."</div>";
                    if (isset($_SESSION['auth'])) { echo "<a href='/?route=librares&act=del_comm&comm_id={$c['id']}' style='font-size:0.75em; color:red; text-decoration:none;'>[ СТЕРЕТЬ ]</a>"; }
                    echo "</div>";
                }
                // ФОРМА КОММЕНТА
                echo "<form method='POST' style='margin-top:20px; background:rgba(255,255,255,0.6); padding:15px; border:1px solid #ffb3d9;'>
                        <input type='hidden' name='topic_id' value='{$row['id']}'>
                        <input type='text' name='mail_recheck' style='display:none;'>
                        <input type='text' name='user_name' placeholder='ИМЯ' required style='width:100%; margin-bottom:10px; padding:8px; border:1px solid #000; box-sizing:border-box;'>
                        <textarea name='comm_text' placeholder='МЫСЛИ ПО ТЕМЕ...' required style='width:100%; height:80px; padding:8px; border:1px solid #000; box-sizing:border-box;'></textarea>
                        <button type='submit' name='add_comm_btn' style='margin-top:10px; background:#000; color:#fff; border:none; padding:12px 25px; cursor:pointer; font-weight:bold; text-transform:uppercase;'>СКАЗАТЬ</button>
                      </form>
                </div>
            </div>
            <div class='btn-wrapper' style='margin-top:10px;'>
                <label for='{$ch_id}' class='btn-expand' data-open='СВЕРНУТЬ ∧' data-close='РАЗВЕРНУТЬ ∨'></label>
            </div>";

    if (isset($_SESSION['auth'])) {
        echo "<div class='admin-links' style='margin-top:25px; border-top:1px solid #ffb3d9; padding-top:10px;'>
                <a href='/?route=librares&act=edit&id={$row['id']}'>Править</a> | 
                <a href='/?route=librares&act=to_archive&id={$row['id']}'>В архив</a> | 
                <a href='/?route=librares&act=del&id={$row['id']}' onclick='return confirm(\"СТЕРЕТЬ ПРОЕКТ?\")'>Удалить</a>
              </div>";
    }
    echo "</article><hr style='border:1px dashed #ffb3d9; margin:40px 0;'>";
}

