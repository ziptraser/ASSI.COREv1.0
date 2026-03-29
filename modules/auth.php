<?php
/**
 * ASSI.CORE V1.0 — КОНТРОЛЬНО-ПРОПУСКНОЙ ПУНКТ (AUTH)
 * КТО ПАРОЛЬ НЕ ПОМНИТ — ТОТ ИДЕТ ЛЕСОМ.
 */

defined('ASSI_CORE_ACCESS') or die('КЫШ!');

$pass_file = ROOT_DIR . 'core/pass.php';

// 1. ДЕЙСТВИЕ: ВЫХОД (СЖИГАЕМ МОСТЫ)
if (isset($_GET['act']) && $_GET['act'] == 'out') {
    $_SESSION = array(); 
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy(); 
    header("Location: /"); 
    exit; 
}

// 2. ОБРАБОТЧИК: СМЕНА ПАРОЛЯ (ПЕРЕКОВКА КЛЮЧЕЙ)
if (isset($_SESSION['auth']) && isset($_POST['change_pass_btn'])) {
    $new_p = trim($_POST['new_pass']);
    if (!empty($new_p)) {
        $hash = password_hash($new_p . SALT, PASSWORD_BCRYPT);
        $content = "<?php defined('ASSI_CORE_ACCESS') or die();\n\$admin_pass = '$hash';";
        
        if (file_put_contents($pass_file, $content)) {
            $info = "ПАРОЛЬ ОБНОВЛЕН. ТЕПЕРЬ ТЫ В ПОЛНОЙ БЕЗОПАСНОСТИ, АДМИН.";
        }
    }
}

// 3. ОБРАБОТЧИК: ВХОД (ШТУРМ КРЕПОСТИ)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_btn'])) {
    $u = trim($_POST['user']);
    $p = trim($_POST['pass']);

    // Если файла нет — создаем дефолтный 'admin' (один раз при установке)
    if (!file_exists($pass_file)) {
        $default_hash = password_hash('admin' . SALT, PASSWORD_BCRYPT);
        $content = "<?php defined('ASSI_CORE_ACCESS') or die();\n\$admin_pass = '$default_hash';";
        file_put_contents($pass_file, $content);
    }

    include $pass_file;

    // Сравнение логина и хэша (пароль + соль)
    if ($u === 'admin' && password_verify($p . SALT, $admin_pass)) {
        $_SESSION['auth'] = true;
        $_SESSION['fingerprint'] = md5($_SERVER['HTTP_USER_AGENT'] . $_SERVER['REMOTE_ADDR'] . SALT);
        header("Location: /");
        exit;
    } else {
        sleep(2); // ТОРМОЗИМ ПЕРЕБОРЩИКОВ
        $error = "ОШИБКА: Доступ закрыт. Попробуй еще раз, юзер.";
    }
}

// 4. ИНТЕРФЕЙС (МОРДА ВХОДА)
if (!isset($_SESSION['auth'])) {
    echo "<h2>ВХОД В СИСТЕМУ</h2>";
    if (isset($error)) echo "<p style='color:red;'>$error</p>";
    echo '<form method="POST" class="auth-form">
            <p>ЛОГИН: <input type="text" name="user" required></p>
            <p>ПАРОЛЬ: <input type="password" name="pass" required></p>
            <button type="submit" name="login_btn" class="btn-main">ВЗОЙТИ</button>
          </form>';
} else {
    echo "<h2>БЕЗОПАСНОСТЬ</h2>";
    if (isset($info)) echo "<p style='color:green;'>$info</p>";
    echo '<form method="POST" class="auth-form">
            <p>НОВЫЙ ПАРОЛЬ: <input type="password" name="new_pass" required></p>
            <button type="submit" name="change_pass_btn" class="btn-main">ИЗМЕНИТЬ ПАРОЛЬ</button>
          </form>
          <p><a href="/?route=auth&act=out">[ ВЫЙТИ ИЗ СИСТЕМЫ ]</a></p>';
}
