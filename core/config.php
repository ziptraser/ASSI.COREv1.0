<?php
/**
 * ASSI.CORE V1.0 — КЛЮЧИ ОТ СЕЙФА (CONFIG)
 * КТО ПАРОЛЬ ПОТЕРЯЛ — ТОТ САМ ДУРАК.
 */

// ПРОВЕРКА КЛЮЧА — ЧУЖИМ ТУТ НЕ РАДЫ!
defined('ASSI_CORE_ACCESS') or die('КЫШ!');

// 1. НАСТРОЙКИ БАЗЫ (ЗАПОЛНЯЮТСЯ ПРИ ИНСТАЛЛЕ)
// Хост, имя базы, юзер и пасс — всё тут, в одном месте.
define('DB_HOST', 'localhost');
define('DB_NAME', 'DB_NAME_HERE');
define('DB_USER', 'DB_USER_HERE');
define('DB_PASS', 'DB_PASS_HERE');
define('DB_CHAR', 'utf8mb4');

// 2. СЕКРЕТНЫЙ ИНГРЕДИЕНТ (СОЛЬ ДЛЯ ШИФРОВАНИЯ СЕССИЙ)
// Рандомная строка, штоп хрен кто чё подобрал.
define('SALT', 'RANDOM_STRING_HERE');

// 3. СТРУКТУРА ПАПОК (ПУТИ В ПРИСПОДНЮЮ)
// Вычисляем корень и сторадж от текущей директории.
define('BASE_DIR', __DIR__ . '/../');
define('FILE_DIR', BASE_DIR . 'storage/'); 
