<?php
/**
 * Класс для работы со стандартной сессией PHP - Open_Session
 * Соответствует интерфейсу хранилища данных и может быть использован как сессия для класса Open_Auth
 * @package OpenStruct
 */

/**
 * Подключаем необходимые файлы
 */
require_once CORE_PATH .'Open/Config'. EXT;
require_once CORE_PATH .'Open/Input'. EXT;
require_once CORE_PATH .'Open/Singleton'. EXT;
require_once CORE_PATH .'Open/Storage/Interface'. EXT;

/**
 * Работа со стандартной сессией PHP
 */
class Open_Session extends Open_Singleton implements Open_Storage_Interface
{
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

		$C = Open_Config::getInstance();
		$I = Open_Input::getInstance();

		/**
		 * Открываем сессию
		 * Имя куки зависит от названия приложения, текущего ip пользователя и агента пользователя
		 * Соответственно сменив ip или используя другой агент откроется и другая сессия
		 * Выполняется "хитрое" преобразование, чтобы никто не догадался :-)
		 * Об этом никто не должен знать
		 */
		session_name( strrev( md5(str_rot13($C->get('application_name') . $I->ip() . $I->server('HTTP_USER_AGENT')) & !md5($I->ip())) ) );
		session_start();
		//session_regenerate_id(TRUE);
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
	 * Получить значение переменной $name
	 *
	 * @param string $name Имя переменной
	 * @return mixed
	 */
	public function get($name)
	{
		return ( isset($_SESSION[$name]) ? $_SESSION[$name] : FALSE );
	}

	/**
     * Перегрузка метода get, чтобы можно было обращаться к переменным в сессии вот так:
     * $session->foo;
     * Где $session - объект Open_Session
     *
     * @param string $name Имя переменной
     * @return mixed
     */
	public function __get($name)
	{
		return $this->get($name);
	}

	/**
	 * Установить переменную $name со значением $value
	 *
	 * @param string $name
	 * @param mixed $value
	 * @return void
	 */
	public function set($name, $value)
	{
		$_SESSION[$name] = $value;

		return TRUE;
	}

	/**
	 * Перегрузка метода set, чтобы можно было сохранять переменные в сессию вот так:
	 * $session->foo = 'bar';
	 * Где $session - объект Open_Session
     *
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function __set($name, $value)
    {
    	return $this->set($name, $value);
    }

    /**
	 * Проверить существование переменной $name в сессии
	 *
	 * @param string $name
	 * @return bool
	 */
	public function exists($name)
	{
		return isset($_SESSION[$name]);
	}

	/**
     * Перегрузка метода isset, чтобы можно было проверять переменные в сессии вот так:
	 * isset($session->foo);
	 * Где $session - объект Open_Session
     *
     * @param mixed $name
     * @return bool
     */
	public function __isset($name)
	{
    	return $this->exists($name);
	}

    /**
	 * Удалить переменную $name из сессии
	 *
	 * @param string $name
	 * @return bool
	 */
	public function delete($name)
	{
		unset($_SESSION[$name]);

		return TRUE;
	}

	/**
     * Перегрузка метода unset, чтобы можно было удалять переменные из сессии вот так:
	 * unset($session->foo);
	 * Где $session - объект Open_Session
     *
     * @param mixed $name
     * @return bool
     */
	public function __unset($name)
	{
    	return $this->delete($name);
	}
}