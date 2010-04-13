<?php
/**
 * Синглтон - класс Open_Singleton
 * @package OpenStruct
 */

/**
 * Абстрактный класс описывающий структуру синглтона
 * Синглтон - класс, объект которого может быть создан только в единственном экземпляре
 * Создаём такую структуру для упрощения обращения к экземпляру класса из любой точки кода
 */
abstract class Open_Singleton
{
	/************
	 * Свойства *
	 ************/

	/**
	 * Массив с ключами названиями классов, в котором хранятся единственные экземпляры всех классов
	 *
	 * @var array
	 */
	static protected $instances;

	/**********
	 * Методы *
	 **********/

	/**
	 * Защищённый конструктор, чтобы невозможно было создать объект класса извне
	 */
	protected function __construct()
	{
	}

	/**
	 * Деструктор
	 */
	function __destruct()
	{
	}

	/**
	 * Получить ссылку на единственный объект этого класса
	 *
	 * @param string $c Имя класса создаваемого объекта
	 * @return object Ссылка на объект
	 */
	static public function getInstance($c=__CLASS__)
	{
		if( !isset(self::$instances[$c]) )
		{	self::$instances[$c] = new $c;
		}

		return self::$instances[$c];
	}

	/**
	 * Пресекаем войну клонов
	 */
	public function __clone()
	{
		trigger_error(Open_Text::getInstance()->dget('errors', sprintf('Cannot clone %s', __CLASS__)), E_USER_ERROR);
	}
}