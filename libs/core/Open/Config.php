<?php
/**
 * Работа с конфигами - класс Open_Config
 * @package OpenStruct
 */

/**
 * Подключаем необходимые файлы
 */
require_once CORE_PATH .'Open/Benchmark'. EXT;
require_once CORE_PATH .'Open/Singleton'. EXT;

/**
 * Работа с конфигами
 */
class Open_Config extends Open_Singleton
{
	/*************
	 * Константы *
	 *************/

	/**
	 * Конфиг по умолчанию с базовыми параметрами
	 *
	 */
	const DEFAULT_CONFIG = 'config';

	/************
	 * Свойства *
	 ************/

	/**
	 * Здесь хранятся все конфиги
	 *
	 * @var array
	 */
	private $config = array();

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
	 * Загрузить
	 * Формируется в виде ассоциативного массива с ключами - названиями секций
	 * Файл с загружаемым конфигом должен возвращать массив
	 * Аргуметы взяты с подчёркиванием для избежания пересечения имён в методе с именами в файле конфига
	 *
	 * @param string $_name Имя файла конфига без расширения
	 * @param bool $_trigger Выдать ошибку в случае неудачи?
	 * @return bool Успех операции
	 */
	public function load($_name=self::DEFAULT_CONFIG, $_trigger=TRUE)
	{
		/**
		 * Загружали уже конфиг?
		 */
		if( isset($this->config[$_name]) )
		{	return TRUE;
		}

		/**
		 * А файл с конфигом существует? Если нет, показываем предупреждение
		 */
		if( !file_exists($_path = CONFIGS_PATH . $_name . EXT) )
		{
			if($_trigger)
			{	trigger_error(sprintf(Open_Text::getInstance()->dget('errors', 'Config file <b>%s</b> does not exist'), $_name . EXT), E_USER_WARNING);
			}

			return FALSE;
		}

		$this->config[$_name] = include $_path;

		return TRUE;
	}

	/**
	 * Получить параметр из конфига по имени и секции
	 *
	 * @param string $section Имя секции. Если секция не указана, берётся секция по умолчанию
	 * @param string $name Имя параметра либо массив имён параметров. Если не указано, значит имя секции - это имя параметра
	 * @return mixed
	 */
	public function get($section, $name=NULL)
	{
		/**
		 * Если второй параметр не указан при вызове, значит первый это имя, а не секция
		 * Чтобы при вызове с двумя параметрами сначала была секция, а потом имя параметра, по логике вещей
		 */
		if( !isset($name) )
		{	$name = $section;
			$section = self::DEFAULT_CONFIG;
		}

		/**
		 * Если такой секции не существует
		 * То пытаемся подгрузить
		 */
		if( !isset($this->config[$section]) )
		{	$this->load($section, FALSE);
		}

		/**
		 * Если запрашивается массив значений из конфига
		 * Выбираем все по очереди, если чего-то нет, выдаётся ошибка
		 */
		if( is_array($name) )
		{
			$result = array();
			foreach($name as $value)
			{
				if( isset($this->config[$section][$value]) )
				{	$result[$value] = $this->config[$section][$value];
				}
				else
				{	trigger_error(sprintf(Open_Text::getInstance()->dget('errors', 'Config item <b>%s[%s]</b> does not exist'), $section, $name), E_USER_NOTICE);
					return FALSE;
				}
			}

			return $result;
		}

		/**
		 * Если указанный пункт в секции существует, возвращаем
		 */
		if( isset($this->config[$section][$name]) )
		{
			return $this->config[$section][$name];
		}

		/**
		 * Если ничего не найдено, то выдаём ошибку
		 */
		trigger_error(sprintf(Open_Text::getInstance()->dget('errors', 'Config item <b>%s[%s]</b> does not exist'), $section, $name), E_USER_NOTICE);
		return FALSE;
	}

	/**
	 * Сохранить параметр в конфиг
	 * ??? Нужна ли функция? Но пусть будет
	 *
	 * @param string $section Имя секции
	 * @param string $name Имя параметра
	 * @param mixed $value Значение параметра
	 */
	public function set($section, $name, $value)
	{
		$this->config[$section][$name] = $value;
	}
}