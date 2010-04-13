<?php
/**
 * Валидатор - класс Open_Validator
 * @package OpenStruct
 */

/**
 * Подключаем необходимые файлы
 */
require_once CORE_PATH .'Open/Singleton'. EXT;
require_once CORE_PATH .'Open/Benchmark'. EXT;
require_once CORE_PATH .'Open/Text'. EXT;

/**
 * Класс для валидации данных
 */
class Open_Validator extends Open_Singleton
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
	protected function __construct()
	{
		parent::__construct();
		if(DEBUG) Open_Benchmark::getInstance()->mark(__CLASS__ .'_start');
	}

	/**
	 * Деструктор
	 *
	 */
	function __destruct()
	{
		if(DEBUG) Open_Benchmark::getInstance()->mark(__CLASS__ .'_end');
		parent::__destruct();
	}

	/**
	 * Переопределяем метод унаследованный от синглтона
	 * Необходимо для корректного создания объекта потомка
	 *
	 * @param string $c Имя класса создаваемого объекта
	 * @return object Ссылка на объект
	 */
	static public function getInstance($c=__CLASS__)
	{
		return parent::getInstance($c);
	}


	/**
	 * Проверка
	 * '|' - Разделитель правил
	 * ':' - Разделитель аргументов
	 * Аргументы могут быть занесены в кавычки, если необходимо использовать в качестве аргумента строку содержащую кавычки (в этом случае их надо экранировать) либо символы ':' или '|'
	 * Числа заносить в кавычки нет необходимости в любой форме записи
	 * Пример строки правил
	 * require|email|regexp:"#^(\d*)$#i"|minLength:12
	 *
	 * @param array $data Данные для проверки
	 * @param array $rules Правила
	 * @param bool $onlyError Проверять все правила, либо до первой ошибки?
	 * @return mixed Если проверка пройдена - TRUE, иначе массив с ошибками
	 */
	public function validate($data, $rules, $onlyError=FALSE)
	{
		$result = array();

		/**
		 * Проход по всем правилам
		 */
		foreach($rules as $field => &$rule)
		{
			/**
			 * Если элемент является массивом, то рекурсивно вызываем проверку
			 */
			if( is_array($rule) )
			{
				if( ($rule_result = $this->validate($data[$field], $rules[$field])) !== TRUE )
				{	$result[$field] = $rule_result;
				}
			}
			else
			{	/**
				 * Разбор всей строки правил на отдельные правила и аргументы
				 */
				preg_match_all('#([a-z]+)((:("((.*?)[^\\\])??"|(.*?)(?=:|\||$)))*)(?=\||$)#i', $rule, $rule);
				$arguments = $rule[2];
				$rule = $rule[1];

				/**
				 * Для каждого отдельного правила
				 */
				foreach($rule as $key => &$rule_name)
				{
					$args = $arguments[$key];

					/**
					 * Разделение аргументов
					 */
					preg_match_all('#(:("((.*?)[^\\\])??"|(.*?)(?=:|$)))(?=:|$)#i', $args, $args);
					$args = $args[2];

					/**
					 * Распознавание каждого аргумента и убирание обратных слешей перед экранированными двойными кавычками
					 */
					foreach($args as $key => $arg)
					{	if( preg_match('#^"((.*?)[^\\\])??"$#i', $args[$key], $arg) )
						{	$args[$key] = ( isset($arg[1]) ? str_replace('\"', '"', $arg[1]) : '' );
						}
					}

					/**
					 * Проверярем существование правила и вызываем его
					 */
					if( method_exists($this, $rule_name) )
					{
						/**
						 * Если ошибка
						 */
						if( ($rule_result = $this->$rule_name((isset($data[$field]) ? $data[$field] : NULL), $args)) !== TRUE )
						{
							/**
							 * Если до первой ошибки
							 */
							if( $onlyError )
							{
								$result[$field] = $rule_result;
								break;
							}
							else
							{	$result[$field][] = $rule_result;
							}
						}
					}
					else
					{	$result[$field][$rule_name] = FALSE;
						trigger_error(Open_Text::getInstance()->dget('errors', sprintf('Unknown rule <b>%s</b> for <b>%s</b> field', $rule_name, $field)), E_USER_ERROR);
					}
				}
			}
		}

		return ( empty($result) ? TRUE : $result );
	}

	/**
	 * Проверка заданности значения
	 * В случае ошибки возвращается 0-й аргумент, если он передан
	 * Остальные игнорируются
	 *
	 * @param mixed $var
	 * @param array $args
	 * @return mixed
	 */
	private function required($var, $args)
	{
		if( !empty($var) )
		{	return TRUE;
		}
		else
		{	return ( isset($args[0]) ? $args[0] : Open_Text::getInstance()->dget('validator', 'The field must be set') );
		}
	}

	/**
	 * Проверка минимальной длины строки или массива
	 * Должен быть передан один числовой аргумент
	 * В случае ошибки возвращается 1-й аргумент, если он передан
	 * Остальные игнорируются
	 *
	 * @param mixed $var
	 * @param array $args
	 * @return mixed
	 */
	private function minLength($var, $args)
	{
		if( !isset($args[0]) || !is_numeric($args[0]) )
		{	$result = FALSE;
		}
		else if( is_string($var) )
		{	$result = (strlen($var) >= $args[0]);
		}
		else if( is_array($var) )
		{	$result = (count($var) >= $args[0]);
		}
		else
		{	$result = FALSE;
		}

		if($result)
		{	return TRUE;
		}
		else
		{	return ( isset($args[1]) ? $args[1] : Open_Text::getInstance()->dget('validator', 'The field data length does not reach required minimum value') );
		}
	}

	/**
	 * Проверка максимальной длины строки или массива
	 * Должен быть передан один числовой аргумент
	 * В случае ошибки возвращается 1-й аргумент, если он передан
	 * Остальные игнорируются
	 *
	 * @param string $var
	 * @param array $args
	 * @return mixed
	 */
	private function maxLength($var, $args)
	{
		if( !isset($args[0]) || !is_numeric($args[0]) )
		{	$result = FALSE;
		}
		else if( is_string($var) )
		{	$result = (strlen($var) <= $args[0]);
		}
		else if( is_array($var) )
		{	$result = (count($var) <= $args[0]);
		}
		else
		{	$result = FALSE;
		}

		if($result)
		{	return TRUE;
		}
		else
		{	return ( isset($args[1]) ? $args[1] : Open_Text::getInstance()->dget('validator', 'The field data length exceeds acceptable maximum value') );
		}
	}

	/**
	 * Проверка точной длины строки или массива
	 * Должен быть передан один числовой аргумент
	 * В случае ошибки возвращается 1-й аргумент, если он передан
	 * Остальные игнорируются
	 *
	 * @param mixed $var
	 * @param array $args
	 * @return mixed
	 */
	private function length($var, $args)
	{
		if( !isset($args[0]) || !is_numeric($args[0]) )
		{	$result = FALSE;
		}
		else if( is_string($var) )
		{	$result = (mb_strlen($var) == $args[0]);
		}
		else if( is_array($var) )
		{	$result = (count($var) == $args[0]);
		}
		else
		{	$result = FALSE;
		}

		if($result)
		{	return TRUE;
		}
		else
		{	return ( isset($args[1]) ? $args[1] : Open_Text::getInstance()->dget('validator', 'The field data length does not match required exact length') );
		}
	}

	/**
	 * Проверка строки на соответствие шаблону email
	 * В случае ошибки возвращается 0-й аргумент, если он передан
	 * Остальные игнорируются
	 *
	 * @param string $var
	 * @param array $args
	 * @return mixed
	 */
	private function email($var, $args)
	{
		/**
		 * Регулярное выражение взято из Microsoft Visual Studio 2003
		 */
		$result = ( preg_match("#^\w+([-+.']\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$#i", (string)$var) ? TRUE : FALSE );

		if($result)
		{	return TRUE;
		}
		else
		{	return ( isset($args[0]) ? $args[0] : Open_Text::getInstance()->dget('validator', 'The field data must be a valid email address') );
		}
	}

	/**
	 * Проверка на соответствие строки регулярному выражению
	 * В случае ошибки возвращается 1-й аргумент, если он передан
	 * Остальные игнорируются
	 *
	 * @param string $var
	 * @param array $args
	 * @return mixed
	 */
	private function regexp($var, $args)
	{
		$result = ( isset($args[0]) ? (bool)preg_match($args[0], (string)$var) : FALSE );

		if($result)
		{	return TRUE;
		}
		else
		{	return ( isset($args[1]) ? $args[1] : Open_Text::getInstance()->dget('validator', 'The field data does not correspond to regular expression') );
		}
	}

	/**
	 * Проверка при помощи функции обратного вызова
	 * Первый аргумент должен быть именем функции или класс и метода через '::'
	 * Т.е. 'function' или 'Class::method'
	 * Метод класса должен иметь возможность быть вызванным статически
	 * Все остальные аргументы передаются функции обратного вызова
	 * Требования к функции обратного вызова:
	 * - Принимает 2 аргумента - проверяемое значение и массив аргументов
	 * - Возвращает TRUE в случае успеха, другое значение в случае неудачи
	 * function callback_example(&$var, &$args) {return TRUE;}
	 *
	 * @param mixed $var
	 * @param array $args
	 * @return mixed
	 */
	private function callback($var, $args)
	{
		if( !isset($args[0]) )
		{	return FALSE;
		}

		$callback = ( (strpos($args[0], '::') === FALSE) ? $args[0] : explode('::', $args[0], 2) );
		$args = array_slice($args, 1);

		return call_user_func($callback, $var, $args);
	}

	/**
	 * Является ли поле числом
	 * В случае ошибки возвращается 0-й аргумент, если он передан
	 * Остальные игнорируются
	 *
	 * @param mixed $var
	 * @param array $args
	 * @return mixed
	 */
	private function numeric($var, $args)
	{
		if( is_numeric($var) )
		{	return TRUE;
		}
		else
		{	return ( isset($args[0]) ? $args[0] : Open_Text::getInstance()->dget('validator', 'The field data must be a numeric value') );
		}
	}

	/**
	 * Совпадает ли поле с переданным значением
	 * В случае ошибки возвращается 1-й аргумент, если он передан
	 * Остальные игнорируются
	 *
	 * @param mixed $var
	 * @param array $args
	 * @return mixed
	 */
	private function match($var, $args)
	{
		$result = ( isset($args[0]) ? ($var == $args[0]) : FALSE );

		if( $result )
		{	return TRUE;
		}
		else
		{	return ( isset($args[1]) ? $args[1] : Open_Text::getInstance()->dget('validator', 'The field data does not match a given value') );
		}
	}
}