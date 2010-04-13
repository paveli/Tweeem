<?php
/**
 * Результат запроса - класс Open_Result
 * @package OpenStruct
 */

/**
 * Класс для удобства работы с результатами запросов
 * Не имеет резервной переменной для хранения результатов, соответсвенно если вы хотите работать с результатом, получите его и сохраните в переменной
 * Удобные методы для получения результатов
 */
class Open_Result
{
	/************
	 * Свойства *
	 ************/

	/**
	 * Указатель на результат запроса
	 *
	 * @var resource
	 */
	private $result;

	/**********
	 * Методы *
	 **********/

	/**
	 * Конструктор
	 *
	 */
	function __construct($result)
	{
		$this->result = $result;
	}

	/**
	 * Деструктор
	 *
	 */
	function __destruct()
	{
		$this->free();
	}

	/**
	 * Получить содержимое результата в виде объекта
	 * Если передан аргумент, то результат будет помещён в эту переменную
	 * Иначе возвращён
	 *
	 * @param mixed $r
	 * @return mixed
	 */
	public function get(&$r=NULL)
	{
		$isReturn = ( !isset($r) ? TRUE : FALSE );

		$r = array();
		while( $row = mysql_fetch_object($this->result) )
		{	$r[] = $row;
		}

		if( $isReturn )
		{	return $r;
		}

		return TRUE;
	}

	/**
	 * Получить следующий ряд результата запроса в виде объекта
	 * Если передан аргумент, то результат будет помещён в эту переменную
	 * Иначе возвращён
	 *
	 * @param mixed $r
	 * @return mixed
	 */
	public function row(&$r=NULL)
	{
		$isReturn = ( !isset($r) ? TRUE : FALSE );

		$r = mysql_fetch_object($this->result);

		if( $isReturn )
		{	return $r;
		}

		return TRUE;
	}

	/**
	 * Получить содержимое результата в виде ассоциативного массива
	 * Если передан аргумент, то результат будет помещён в эту переменную
	 * Иначе возвращён
	 *
	 * @param mixed $r
	 * @return mixed
	 */
	public function getArray(&$r=NULL)
	{
		$isReturn = ( !isset($r) ? TRUE : FALSE );

		$r = array();
		while( $row = mysql_fetch_assoc($this->result) )
		{	$r[] = $row;
		}

		if( $isReturn )
		{	return $r;
		}

		return TRUE;
	}

	/**
	 * Получить следующий ряд результата запроса в виде массива
	 * Если передан аргумент, то результат будет помещён в эту переменную
	 * Иначе возвращён
	 *
	 * @param mixed $r
	 * @return mixed
	 */
	public function rowArray(&$r=NULL)
	{
		$isReturn = ( !isset($r) ? TRUE : FALSE );

		$r = mysql_fetch_assoc($this->result);

		if( $isReturn )
		{	return $r;
		}

		return TRUE;
	}

	/**
	 * Возвращает количество рядов результата запроса
	 *
	 * @return int
	 */
	public function numRows()
	{
		return mysql_num_rows($this->result);
	}

	/**
	 * Возвращает количество полей результата запроса
	 *
	 * @return int
	 */
	public function numFields()
	{
		return mysql_num_fields($this->result);
	}

	/**
	 * Очистить память занимаемую результатом запроса
	 */
	private function free()
	{
		if( is_resource($this->result) )
		{	mysql_free_result($this->result);
		}
	}
}