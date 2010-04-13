<?php
/**
 * Преобразования данных - класс Open_Convert
 * @package OpenStruct
 */

/**
 * Преобразования данных
 */
class Open_Convert extends Open_Singleton
{
	/*************
	 * Константы *
	 *************/

	/************
	 * Свойства *
	 ************/

	/**********
	 * Методы *
	 **********/

	/**
	 * Конструктор
	 */
	protected function __construct()
	{
		parent::__construct();
		if(DEBUG) Open_Benchmark::getInstance()->mark(__CLASS__ .'_start');
	}

	/**
	 * Деструктор
	 */
	function __destruct()
	{
		if(DEBUG) Open_Benchmark::getInstance()->mark(__CLASS__ .'_end');
		parent::__destruct();
	}

	/**
	 * Получить ссылку на единственный объект этого класса
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
	 * base64 преобразование безопасное для подстановки в url или использования в имени файла
	 * Заменяются символы:
	 * '+' => '-'
	 * '/' => '_'
	 * '=' => ''
	 * Символы '=' используются только для дополнения, чтобы длина строки была кратна 4, поэтому их можно легко исключить
	 *
	 * @param string $string
	 * @return string
	 */
	public function toBase64($value)
	{
	    return str_replace(array('+','/','='), array('-','_',''), base64_encode($value));
	}

	/**
	 * Обратное base64 преобразование безопасное для подстановки в url или использования в имени файла
	 *
	 * @param string $string
	 * @return string
	 */
	public function fromBase64($value)
	{
		$value = str_replace(array('-','_'), array('+','/'), $value);
		$mod4 = strlen($value) % 4;
		if($mod4)
		{	$value .= substr('====', $mod4);
		}
		return base64_decode($value);
	}

	/**
	 * Преобразовать строку вотТакогоВида или ВотТакогоВида или ВотТАКогоВИДА в вот_такого_вида
	 * !!! Не самый лучший способ решения проблемы, но лучше пока найден не был
	 *
	 * @param string $str
	 * @return string
	 */
	public function camelToUnderscore($str)
	{
		$str = trim($str, ' _');

		/**
		 * Заменяем все разделители ' ' и '_' на один '_', для ускорения процесса
		 * И разбираем строку в 2 регулярных выражения
		 */
		$str = preg_replace('#[^a-zA-Z0-9\x7f-\xff]+#', '_', $str);
		preg_match('#^([a-z0-9_\x7f-\xff]*)?(([A-Z_\x7f-\xff]+[a-z0-9_\x7f-\xff]+)*)([A-Z_\x7f-\xff]*)?$#', $str, $temp1);
		preg_match_all('#([A-Z_\x7f-\xff]+[a-z0-9_\x7f-\xff]+)#', $temp1[2], $temp2);

		/**
		 * Создаём массив из того, что нам надо
		 */
		$temp3 = array_merge((!empty($temp1[1])) ? array($temp1[1]) : array() , $temp2[0], (!empty($temp1[4])) ? array($temp1[4]) : array());

		/**
		 * Все элементы массива преобразуются к нижнему регистру
		 * От использования array_walk() в паре с lambda-функцией пришлось отказаться, т.к. это работает примерно в два раза медленее, чем простой foreach
		 */
		foreach($temp3 as &$value)
		{	$value = trim(strtolower($value), '_');
		}

		/**
		 * Всё склеивается через '_'
		 */
		return implode('_', $temp3);
	}

	/**
	 * Преобразовать строку вот_такого_вида в вотТакойВот
	 *
	 * @param string $str
	 * @return string
	 */
	public function underscoreToCamel($str)
	{
		$str = trim($str, ' _');
		$result = '';
		foreach(preg_split('#_+#', $str, 0, PREG_SPLIT_NO_EMPTY) as $key => $value)
		{	$value = strtolower($value);
			$result .= ($key>0) ? ucwords($value) : $value;
		}

		return $result;
	}
}