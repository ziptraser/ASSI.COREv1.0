<?php
/**
 * ASSI.CORE V1.0 — ТОЧКА ВХОДА (FRONT CONTROLLER)
 * ФИЛОСОФИЯ: ПРИШЁЛ, УВИДЕЛ, ЗАВЕЛСЯ.
 */

$start_time = microtime(true); // ХОП — СЕКУНДОМЕР ПОШЁЛ! ГРЕЕМ МОТОРЫ.

// ГЛАВНЫЙ КЛЮЧ ДОСТУПА — КТО НЕ ЗНАЕТ, ТОТ КЫШ!
define('ASSI_CORE_ACCESS', true);

// ПОГНАЛИ В ПОТРОХА: ЦЕПЛЯЕМ ЯДРО
require_once __DIR__ . '/core/core.php';

