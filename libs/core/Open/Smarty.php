<?php
/**
 * Расширение Smarty - класс Open_Smarty
 * @package OpenStruct
 */

/**
 * Подключаем необходимые файлы
 */
require_once LIBRARIES_PATH .'Smarty/Smarty.class.php';
require_once CORE_PATH .'Open/Benchmark'. EXT;
require_once CORE_PATH .'Open/Config'. EXT;
require_once CORE_PATH .'Open/Text'. EXT;

/**
 * Наследуем класс Smarty с целью его дополнения
 * Все настройки следует делать из этого файла
 * Регистрировать новые модификаторы, функции, блоки и т.д.
 *
 */
class Open_Smarty extends Smarty
{
	/************
	 * Свойства *
	 ************/

	/**********
	 * Методы *
	 **********/

	/**
	 * Конструктор
	 *
	 */
	function __construct()
	{
		parent::__construct();
		if(DEBUG) Open_Benchmark::getInstance()->mark(__CLASS__ .'_start');

		$this->template_dir = VIEWS_PATH;
		$this->compile_dir = SMARTY_PATH .'templates_c/';
		$this->config_dir = SMARTY_PATH .'config/';
		$this->cache_dir = SMARTY_PATH .'cache/';

		$this->debugging = false;
		$this->compile_check = DEBUG;
		$this->caching = 2;//(DEBUG ? false : 2);

		/**
		 * Модификаторы применяемые неявно ко всем переменным внутри шаблонов
		 * Необходимо учитывать, что модификаторы по умолчанию применяются до модификаторов указанных непосредственно в шаблоне
		 */
		$this->default_modifiers = array(
			//'htmlentities',	// Применяя неявно этот модификатор можно практически не беспокоиться о преобразовании символов
		);

		$this->register_block('dynamic', 'smarty_block_dynamic', false);
		$this->register_modifier('empty', 'smarty_modifier_empty');
		$this->register_modifier('htmlentities', 'smarty_modifier_htmlentities');
		$this->register_function('pagination', 'smarty_function_pagination');
	}

	/**
	 * Деструктор
	 *
	 */
	function __destruct()
	{
		if(DEBUG) Open_Benchmark::getInstance()->mark(__CLASS__ .'_end');
	}
}

/**
 * Блок для отмены кеширования в Smarty
 *
 * @param array $param
 * @param mixed $content
 * @param object $smarty
 * @return mixed
 */
function smarty_block_dynamic($param, $content, &$smarty)
{
    return $content;
}

/**
 * Модификатор empty
 *
 * @param mixed $var
 * @return bool
 */
function smarty_modifier_empty($var)
{
	return empty($var);
}

/**
 * Замена модификатору 'escape:"htmlall"'
 * Пытается экранировать только строки и с учётом используемой нами кодировки
 *
 * @param mixed $var
 * @return mixed
 */
function smarty_modifier_htmlentities($var)
{
	if( is_string($var) )
	{	return htmlentities($var, ENT_QUOTES, Open_Config::getInstance()->get('charset'));
	}
	else
	{	return $var;
	}
}

/**
 * Функция для вставки постраничной навигации
 *
 * Параметры:
 *
 * 1. Обязательные
 *
 * 1.1. Для всех шаблонов
 * 1.1.1. link - ссылка для навигации, где подстрока [:nav:] заменяется на параметр навигации (e.g. страницу, номер объекта)
 * 1.1.2. amount - количество объектов по которым производится навигация
 * 1.1.3. span - количество объектов выводимых на одной странице
 * 1.1.4. current - текущий параметр навигации (e.g. текущая страница, номер объекта)
 *
 * 1.2. Шаблон first
 *
 * 2. Необязательные
 *
 * 2.1. Для всех шаблонов
 * 2.1.1. pattern - шаблон постраничной навигации. По умолчанию берётся значение 'default_pagination_pattern' из конфига
 * 2.1.2. class - имя класса для <div> навигации. По умолчанию будет присвоен класс 'pagination-'. $params['pattern']
 *
 * 2.2. Шаблон first
 * 2.2.1. around - страниц рядом с текущей
 * 2.2.2. gaps - максимальное количество промежутков
 * 2.2.3. threshold - порог промежутка
 * 2.2.4. gap - обозначение промежутка, по умолчанию строка '&#8230;', что есть многоточие
 *
 * @param array $params
 * @param object $smarty
 * @return string
 */
function smarty_function_pagination($params, &$smarty)
{
	/**
	 * Проверка все ли необходимые аргументы переданы
	 */
	if( !isset($params['link']) || empty($params['link']) )
	{	trigger_error(sprintf(Open_Text::getInstance()->dget('errors', 'Parameter <b>%s</b> for Smarty function <b>pagination</b> is not set'), 'link'), E_USER_ERROR);
	}
	else if( !isset($params['amount']) || empty($params['amount']) )
	{	trigger_error(sprintf(Open_Text::getInstance()->dget('errors', 'Parameter <b>%s</b> for Smarty function <b>pagination</b> is not set'), 'amount'), E_USER_ERROR);
	}
	else if( !isset($params['span']) || empty($params['span']) )
	{	trigger_error(sprintf(Open_Text::getInstance()->dget('errors', 'Parameter <b>%s</b> for Smarty function <b>pagination</b> is not set'), 'span'), E_USER_ERROR);
	}
	else if( !isset($params['current']) || empty($params['current']) )
	{	trigger_error(sprintf(Open_Text::getInstance()->dget('errors', 'Parameter <b>%s</b> for Smarty function <b>pagination</b> is not set'), 'current'), E_USER_ERROR);
	}

	/**
	 * Если шаблон не передан, берём значение по умолчанию из конфига
	 */
	if( !isset($params['pattern']) || empty($params['pattern']) )
	{	$params['pattern'] = Open_Config::getInstance()->get('default_pagination_pattern');
	}

	/**
	 * Если класс ещё не объявлен, подключается исходный файл
	 */
	if( !class_exists('Open_Pagination') )
	{	require_once CORE_PATH .'Open/Pagination'. EXT;
	}

	/**
	 * Выполнение действий для выбранного шаблона
	 */
	switch($params['pattern'])
	{
		case Open_Pagination::FIRST:

			/**
			 * Установка значений по умолчанию, если необходимо
			 */
			$params['around'] = ( isset($params['around']) ? $params['around'] : Open_Pagination::FIRST_DEFAULT_AROUND );
			$params['gaps'] = ( isset($params['gaps']) ? $params['gaps'] : Open_Pagination::FIRST_DEFAULT_GAPS );
			$params['threshold'] = ( isset($params['threshold']) ? $params['threshold'] : Open_Pagination::FIRST_DEFAULT_THRESHOLD );

			/**
			 * Получение массива с вычисленными значениями
			 */
			$params['pagination'] = Open_Pagination::getInstance()->patternFirst($params['amount'], $params['span'], $params['current'], $params['around'], $params['gaps'], $params['threshold']);
			break;

		default:
			trigger_error(sprintf(Open_Text::getInstance()->dget('errors', 'Pagination pattern <b>%s</b> is not available'), $params['pattern']), E_USER_ERROR);
			break;
	}

	/**
	 * Получение значения переменной params из Smarty
	 * Устанавливается своё значение
	 */
	$paramsBackup = $smarty->get_template_vars('params');
	$smarty->assign('params', $params);

	$result = $smarty->fetch('Open/Pagination/'. $params['pattern'] . TPLEXT);

	/**
	 * Восстановление значения переменной params
	 */
	$smarty->assign('params', $paramsBackup);

	return $result;
}