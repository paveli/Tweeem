<?php
/**
 * Основной скрипт
 * @package OpenStruct
 */

/**
 * Начало отсчёта времени для определения производительности
 * $Open_Benchmark_marks - глобальный массив меток времени для объекта Open_Benchmark
 * В момент создания объекта Open_Benchmark этот массив копируется в него и удаляется из глобальной области
 * Дальше для создания метки необходимо использовать метод объекта
 */
$Open_Benchmark_marks['the_very_beginning'] = microtime(TRUE);

/**
 * Сохранение начала отсчёта времени работы
 * Для избежания дальнейшего множественного обращения к функции time()
 * В местах, где существует возможность пренебречь временем выполнения
 */
define('TIME', time());
$time = TIME;

/**
 * Флаг дебага
 * Зависимы:
 * - Уровень error_reporting, display_errors
 * - Создание объекта Open_Benchmark и сохранение результатов времени работы
 * - Использование кеширования
 * - Логирование запросов к БД
 * - и прочее
 */
define('DEBUG', TRUE);
define('DEBUG_NEGATIVE', !DEBUG);

/**
 * Создаём новые коды новых ошибок
 * Числа взяты как продолжение последовательности ошибок php
 * Если в дальнейших версиях php будут добавлены ещё типы ошибок, то они могу перекрыться с этими и их надо будет поменять
 */
/**
 * Ошибка БД
 */
define('E_DB', 0x2000);

/**
 * 403 Forbidden
 */
define('E_403', 0x4000);

/**
 * 404 Not Found
 */
define('E_404', 0x8000);

/**
 * 500 Internal Server Error
 */
define('E_500', 0x10000);

/**
 * Уровень отчётности ошибок
 */
define('E_REPORTING', (DEBUG ? (E_ALL|E_STRICT|E_DB|E_403|E_404|E_500) : (E_403|E_404|E_500)));
error_reporting(E_REPORTING);

/**
 * Отображать ошибки?
 */
ini_set('display_errors', DEBUG);

/**
 * Задаём расширения файлов
 */
/**
 * Расширение всех подключаемых скриптов, берётся от файла index.php
 */
define('EXT', '.'. pathinfo(__FILE__, PATHINFO_EXTENSION));

/**
 * Расширение файлов с CSS стилями
 */
define('CSSEXT', '.css');

/**
 * Расширение файлов с JavaScript
 */
define('JSEXT', '.js');

/**
 * Расширение шаблонов
 */
define('TPLEXT', '.tpl');

/**
 * Для xml-шаблонов
 */
define('XTPLEXT', '.xtpl');

/**
 * Задаём константы содержащие базовые пути
 */
/**
 * Корень всего
 */
define('BASE_PATH', dirname(dirname(__FILE__)) .'/');

/**
 * Папка для доступа извне
 */
define('WWWDATA_PATH', dirname(__FILE__) .'/');

/**
 * Папка со всеми рабочими скриптами
 */
define('LIBS_PATH', BASE_PATH .'libs/');

/**
 * Путь к папке с приложения
 */
define('APPLICATION_PATH', LIBS_PATH .'application/');

/**
 * Путь к папке с ядром
 */
define('CORE_PATH', LIBS_PATH .'core/');

/**
 * Путь к папке с библиотеками
 */
define('LIBRARIES_PATH', LIBS_PATH .'libraries/');

/**
 * Путь к папке с конфигами
 */
define('CONFIGS_PATH', APPLICATION_PATH .'configs/');

/**
 * Путь к папке с контроллерами
 */
define('CONTROLLERS_PATH', APPLICATION_PATH .'controllers/');

/**
 * Путь к папке с шаблонами ошибок
 */
define('ERRORS_PATH', APPLICATION_PATH .'errors/');

/**
 * Путь к папке с моделями
 */
define('MODELS_PATH', APPLICATION_PATH .'models/');

/**
 * Путь к рабочей папке Smarty
 */
define('SMARTY_PATH', APPLICATION_PATH .'smarty/');

/**
 * Путь к папке куда можно класть временные файлы
 */
define('TEMP_PATH', APPLICATION_PATH .'temp/');

/**
 * Путь к папке с текстами
 */
define('TEXT_PATH', APPLICATION_PATH .'text/');

/**
 * Путь к папке с шаблонами
 */
define('VIEWS_PATH', APPLICATION_PATH .'views/');

/**
 * Папка со статическими данными (картинки, стили, JS-скрипты, и т.д.)
 */
define('STATIC_DIR', 'static/');

/**
 * Папка со стилями
 */
define('CSS_DIR', STATIC_DIR .'css/');

/**
 * Папка со шрифтами
 */
define('FONTS_DIR', STATIC_DIR .'fonts/');

/**
 * Путь к папке со шрифтами
 */
define('FONTS_PATH', WWWDATA_PATH . FONTS_DIR);

/**
 * Папка с картинками
 */
define('IMG_DIR', STATIC_DIR .'img/');

/**
 * Папка с JavaScripts
 */
define('JS_DIR', STATIC_DIR .'js/');

/**
 * Подключаем ядро
 */
set_include_path('.');
require_once CORE_PATH .'core'. EXT;