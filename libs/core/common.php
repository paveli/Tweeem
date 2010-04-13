<?php
/**
 * Общие глобальные функции
 * @package OpenStruct
 */

/**
 * Объявление необходимых глобальных функций
 */

/**
 * Автоматическая загрузка библиотек, если они не подключены явно
 * В соответствии с правилами именования классов
 * Ищет файлы последовательно в папках libs/core/, libs/libraries/, libs/application/models/, libs/application/controllers/
 *
 * @param string $class_name
 */
//function __autoload($class_name)
//{
//	$file_path = str_replace('_', '/', $class_name) . EXT;
//
//	if( file_exists($path = CORE_PATH . $file_path) ) {}
//	else if( file_exists($path = LIBRARIES_PATH . $file_path) ) {}
//	else if( file_exists($path = MODELS_PATH . $file_path) ) {}
//	else if( file_exists($path = CONTROLLERS_PATH . $file_path) ) {}
//	else
//	{	trigger_error(sprintf(Open_Text::getInstance()->dget('errors', 'Cannot load <b>%s</b> class. Source file does not exist.'), $class_name), E_USER_ERROR);
//	}
//
//	require_once($path);
//}

/**
 * Вывести переменную окруженную тегом <pre>
 * Только для процесса разработки
 * Функция принимает неограниченное количество аргументов
 *
 * @param mixed $var
 */
function p($var)
{
	$nargs = func_num_args();

	$str = '<div align="left" style="width: 100%; background-color: black; color: lime;">';
	for($i=0; $i<$nargs; $i++)
	{	$temp = func_get_arg($i);
		$str .= '<pre>'. preg_replace('#(\r\n|\n\r|\r|\n)#u', '<br />', print_r($temp, true)) .'</pre>';
	}
	$str .= '</div>';

	echo $str;
}

/**
 * Отформатировать число в соответствии с текущей локалью
 * !!! Я не нашёл более простого способа, чем этот, с учётом того, что функция number_format сама по себе локаль не учитывает
 *
 * @param number $num
 * @param int $decimals Количество десятичных знаков
 * @return string
 */
function numberFormat($num, $decimals)
{
	$locale_info = localeconv();

	return number_format($num, $decimals, $locale_info['decimal_point'], $locale_info['thousands_sep']);
}

/**
 * Рекурсивно соединить два массива без повторения одинаковых элементов
 *
 * @param array $array1
 * @param array $array2
 * @return array
 */
function &arrayMergeUniqueRecursive(&$array1, &$array2)
{
	$result = $array1;

	foreach($array2 as $key2 => &$value2)
	{
		if( !is_array($value2) && in_array($value2, $result) )
		{	continue;
		}

		if( isset($result[$key2]) )
		{	if( is_array($result[$key2]) && is_array($value2) )
			{	$result[$key2] = arrayMergeUniqueRecursive($result[$key2], $value2);
			}
			else if( !is_array($result[$key2]) && is_array($value2) )
			{	$temp = array($result[$key2]);
				$result[$key2] = arrayMergeUniqueRecursive($temp, $value2);
			}
			else if( is_array($result[$key2]) && !is_array($value2) )
			{	$temp = array($value2);
				$result[$key2] = arrayMergeUniqueRecursive($result[$key2], $temp);
			}
			else
			{	$result[] = $value2;
			}
		}
		else
		{	$result[$key2] = $value2;
		}
	}

	return $result;
}

/**
 * Рекурсивно вычесть массив $array2 из массива $array1
 *
 * @param array $array1
 * @param array $array2
 * @return array
 */
function &arrayDiffRecursive(&$array1, &$array2)
{
	$result = $array1;

	foreach($array2 as $key2 => &$value2)
	{
		if( !is_array($value2) && ($key_result = array_search($value2, $result)) !== FALSE )
		{	unset($result[$key_result]);
			continue;
		}

		if( isset($result[$key2]) && is_array($result[$key2]) && is_array($value2) )
		{	$result[$key2] = arrayDiffRecursive($result[$key2], $value2);
		}
	}

	return $result;
}

/**
 * Обработчик ошибок, чтобы заменить стандартный
 * Следующие типы ошибок не могут быть обработаны этой функцией:
 * E_ERROR, E_PARSE, E_CORE_ERROR, E_CORE_WARNING, E_COMPILE_ERROR, E_COMPILE_WARNING, и большая часть E_STRICT, если они появляются в файле, где set_error_handler вызван
 *
 * @param int $code Код ошибки
 * @param string $message Текст ошибки
 * @param string $file Путь к файлу, где произошла ошибка
 * @param int $line Номер строки с ошибкой
 * @return bool TRUE - отменить стандартную обработку ошибок, FALSE - не отменять
 */
function errorHandler($code, $message, $file, $line)
{
	$E = new Open_Exception($message, $code, $file, $line);
	$E->handle();

	/**
	 * В релизе необходимо возвращать значение TRUE
	 */
	return TRUE;
}

/**
 * Функция, которую следует использовать для генерации своих ошибок, таких как 403-я или 404-я и т.д.
 *
 * @param string $message Текст ошибки
 * @param int $code Код ошибки
 * @param string $file Путь к файлу, где произошла ошибка
 * @param int $line Номер строки с ошибкой
 */
function triggerError($message, $code)
{
	/**
	 * Не по адресу
	 */
	if($code <= E_ALL)
		return;

	$E = new Open_Exception($message, $code);
	$E->handle();
}

/**
 * Показать 403-ю
 *
 * @param string $message Текст ошибки
 */
function trigger403($message=FALSE)
{
	if($message === FALSE)
	{	$message = Open_Text::getInstance()->dget('errors', Open_Config::getInstance()->get('default_403_message'));
	}
	triggerError($message, E_403);
}

/**
 * Показать 404-ю
 *
 * @param string $message Текст ошибки
 */
function trigger404($message=FALSE)
{
	if($message === FALSE)
	{	$message = Open_Text::getInstance()->dget('errors', Open_Config::getInstance()->get('default_404_message'));
	}
	triggerError($message, E_404);
}

/**
 * Показать 500-ю
 *
 * @param string $message Текст ошибки
 */
function trigger500($message=FALSE)
{
	if($message === FALSE)
	{	$message = Open_Text::getInstance()->dget('errors', Open_Config::getInstance()->get('default_500_message'));
	}
	triggerError($message, E_500);
}

/**
 * Загрузить модель
 * Имя модели и имя файла должны быть в соответствии с соглашением
 * Базовый класс модели наследован от Open_Singleton
 * Таким образом легко получить доступ к модели из любой точки кода
 *
 * @param string $name Имя класса модели. Поиск будет осуществлён в папке libs/application/models/
 * @return object Ссылка на объект модели
 */
function getModel($name)
{
	if( !class_exists($name) )
	{	require_once MODELS_PATH . str_replace('_', '/', $name) . EXT;
	}

	/**
	 * Получаем и возвращаем ссылку на объект
	 */
	return call_user_func(array($name, 'getInstance'), $name);
}