<?php 
/**
 * ASSI.CORE V1.0 — ГЛАВНЫЙ ШАБЛОН (LAYOUT)
 * МОРДА САЙТА. КТО ТРОНЕТ ВЕРСТКУ БЕЗ ЗНАНИЯ ДЕЛА — ТОТ КРИВОРУКИЙ ЮЗЕР.
 */
defined('ASSI_CORE_ACCESS') or die('КЫШ!'); 
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?></title>
    <meta name="description" content="<?= htmlspecialchars($page_desc) ?>">
    <!-- ЦЕПЛЯЕМ ГАРДЕРОБ (СТИЛИ) С ХАКОМ ПРОТИВ КЭША -->
    <link rel="stylesheet" href="/templates/style.css?v=<?=time()?>">
</head>
<body>

<div class="container">
    <header>
        <!-- ВМЕСТО ЛОГО — ТВОЙ ХОСТ В ВЕРХНЕМ РЕГИСТРЕ. КРУТО И ПО-АДМИНСКИ. -->
        <a href="/" class="logo"><?= strtoupper($_SERVER['HTTP_HOST']) ?></a>
        
        <nav>
            <?php foreach ($modules as $m_route => $m_name): ?>
                <?php if (empty($m_name)) continue; ?>
                <a href="/?route=<?= $m_route ?>"><?= $m_name ?></a>
            <?php endforeach; ?>
            
            <?php if(isset($_SESSION['auth'])): ?> 
                <?php 
                    $curr_route = $_GET['route'] ?? 'journal';
                    // ДИНАМИЧЕСКИЕ КНОПКИ ДЛЯ АДМИНА — ДОБАВЛЯЕМ КОНТЕНТ НА ЛЕТУ
                    if ($curr_route == 'journal' && array_key_exists('journal', $modules)): ?>
                        <a href="/?route=journal&act=add" class="admin-btn">[ + ДОБАВИТЬ ]</a>
                    <?php elseif ($curr_route == 'librares' && array_key_exists('librares', $modules)): ?>
                        <a href="/?route=librares&act=add" class="admin-btn">[ + НОВЫЙ ПРОЕКТ ]</a>
                    <?php endif; ?>
                    
                <a href="/?route=auth&act=out" class="exit-btn">[ ВЫХОД ]</a> 
            <?php endif; ?>
            <!-- МАЛЕНЬКИЙ ЗАМОК ДЛЯ ТЕХ, КТО В ТЕМЕ -->
            <a href="/?route=auth" class="auth-icon">🔒</a> 
        </nav>
    </header>

    <main>
        <!-- ТУТ ВЫВАЛИВАЕТСЯ ВЕСЬ СМЫСЛ ИЗ МОДУЛЕЙ -->
        <?php echo $module_output; ?>
    </main>

    <footer> 
        <div class="footer-left">
            &copy; <?= date('Y') ?> POWERED BY ASSI.CORE v1.0
        </div>
        <div class="footer-right">
            ОНЛАЙН: <?=($online_count ?? 1)?> | 
            ГЕНЕРАЦИЯ: <?=sprintf("%.4f СЕК.", (microtime(true) - $start_time))?>
        </div>
    </footer>
</div>

<!-- МАГИЯ ПРОСМОТРА КАРТИНОК (MISTY-OVERLAY) -->
<div id="misty-overlay" onclick="closeMisty()" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.9); z-index:10001; cursor:pointer;">
    <div style="display:flex; align-items:center; justify-content:center; height:100%;">
        <img id="misty-full" src="" style="max-width:96%; max-height:96%; border:1px solid #444;">
    </div>
</div>

<script>
    // ФУНКЦИИ ПРОСМОТРА — ЧИСТАЯ ВАНИЛА, НИКАКИХ БИБЛИОТЕК
    function openMisty(src) { document.getElementById('misty-full').src = src; document.getElementById('misty-overlay').style.display = 'block'; document.body.style.overflow = 'hidden'; }
    function closeMisty() { document.getElementById('misty-overlay').style.display = 'none'; document.body.style.overflow = 'auto'; }
</script>

</body>
</html>

