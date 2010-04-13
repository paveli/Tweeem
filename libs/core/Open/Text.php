<?php
/**
 * Замена gettext - класс Open_Text
 * @package OpenStruct
 */

/**
 * Подключаем необходимые файлы
 */
require_once CORE_PATH .'Open/Benchmark'. EXT;
require_once CORE_PATH .'Open/Config'. EXT;
require_once CORE_PATH .'Open/Singleton'. EXT;

/**
 * Замена gettext
 */
class Open_Text extends Open_Singleton
{
	/*************
	 * Константы *
	 *************/

	/**
	 * Генерировать ошибки в случае если домен или сообщение в домене не найдены?
	 */
	const TRIGGER = DEBUG;

	/************
	 * Свойства *
	 ************/

	/**
	 * Рабочая локаль
	 *
	 * @var string
	 */
	static private $locale = FALSE;

	/**
	 * Домен по умолчанию
	 *
	 * @var string
	 */
	static private $domain = FALSE;

	/**
	 * Конфиг
	 *
	 * @var array
	 */
	private $config;

	/**
	 * Загруженные тексты
	 *
	 * @var array
	 */
	private $data;

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

		$this->config = $C->get('text');

		/**
		 * Установка рабочей локали, если она всё ещё не указана
		 * Локаль устанавливается на этапе роутинга
		 */
		if(self::$locale === FALSE)
		{	self::locale($C->get('default_locale'));
		}

		/**
		 * Установка домена по умолчанию
		 */
		self::domain($this->config['default_domain']);
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
	 * Получить или установить рабочую локаль
	 *
	 * @param mixed $locale
	 * @return mixed
	 */
	static public function locale($locale=FALSE)
	{
		if( $locale !== FALSE )
		{	self::$locale = $locale;
		}

		return self::$locale;
	}

	/**
	 * Получить или установить домен по умолчанию
	 *
	 * @param mixed $domain
	 * @return mixed
	 */
	static public function domain($domain=FALSE)
	{
		if( $domain !== FALSE )
		{	self::$domain = $domain;
		}

		return self::$domain;
	}

	/**
	 * Загрузка домена
	 * Файл с загружаемым доменом должен возвращать массив
	 * Аргуметы взяты с подчёркиванием для избежания пересечения имён в методе с именами в файле домена
	 *
	 * @param string $_domain
	 * @param bool $_trigger
	 * @return bool Успех операции
	 */
	private function load($_domain)
	{
		/**
		 * Если рабочая локаль неверна возвращаем провал операции
		 */
		if( self::$locale === FALSE )
		{	return FALSE;
		}

		/**
		 * Загружали уже домен?
		 */
		if( isset($this->data[self::$locale][$_domain]) )
		{	return TRUE;
		}

		/**
		 * А файл с доменом существует? Если нет, показываем предупреждение
		 */
		if( !file_exists($_path = TEXT_PATH . self::$locale .'/'. $_domain . EXT) )
		{
			if( self::TRIGGER )
			{	trigger_error(sprintf('Domain file <b>%s</b> for <b>%s</b> locale does not exist', $_domain . EXT, self::$locale), E_USER_WARNING);
			}

			return FALSE;
		}

		$this->data[self::$locale][$_domain] = include $_path;

		return TRUE;
	}

	/**
	 * Получить перевод сообщения из домена по умолчанию
	 *
	 * @param string $message
	 * @return string
	 */
	public function get($message)
	{
		return ( self::$domain !== FALSE ? $this->dget(self::$domain, $message) : $message );
	}

	/**
	 * Получить перевод сообщения из указанного домена
	 *
	 * @param string $domain
	 * @param string $message
	 * @return string
	 */
	public function dget($domain, $message)
	{
		/**
		 * Если домен не загружен, попытка загрузить
		 */
		if( !isset($this->data[self::$locale][$domain]) && !$this->load($domain) )
		{
			return $message;
		}

		/**
		 * Если сообщение существует, возвращаем
		 */
		if( isset($this->data[self::$locale][$domain][$message]) )
		{
			$message = $this->data[self::$locale][$domain][$message];
		}
		else if( self::TRIGGER )
		{
			trigger_error(sprintf('Message "<b>%s</b>" in domain <b>%s</b> for <b>%s</b> locale does not exist', htmlentities($message), $domain, self::$locale), E_USER_WARNING);
		}


		return $message;
	}

	/**
	 * Получить перевод соообщения с учётом множественной формы из домена по умолчанию
	 *
	 * @param string $message
	 * @param int $n
	 * @return string
	 */
	public function nget($message, $n)
	{
		return ( self::$domain !== FALSE ? $this->dnget(self::$domain, $message, $n) : $message );
	}

	/**
	 * Получить перевод соообщения с учётом множественной формы из указанного домена
	 *
	 * @param string $domain
	 * @param string $message
	 * @param int $n
	 * @return string
	 */
	public function dnget($domain, $message, $n)
	{
		/**
		 * Получение множественной формы слова
		 */
		$form = ( is_callable($this->config['locales'][self::$locale]['plural']) ? call_user_func($this->config['locales'][self::$locale]['plural'], $n) : 0 );

		/**
		 * Если домен не загружен, попытка загрузить
		 */
		if( !isset($this->data[self::$locale][$domain]) && !$this->load($domain))
		{
			return $message;
		}

		/**
		 * Если сообщение существует, возвращаем
		 */
		if( isset($this->data[self::$locale][$domain][$message]) && is_array($this->data[self::$locale][$domain][$message]) && isset($this->data[self::$locale][$domain][$message][$form]) )
		{
			$message = $this->data[self::$locale][$domain][$message][$form];
		}
		else if( self::TRIGGER )
		{
			trigger_error(sprintf('Message "<b>%s</b>" in domain <b>%s</b> for <b>%s</b> locale does not exist', htmlentities($message), $domain, self::$locale), E_USER_WARNING);
		}

		return $message;
	}

	/**
	 * Получить перевод сообщения из домена по умолчанию
	 *
	 * @param string $message
	 * @return string
	 */
	public function gettext($message)
	{
		return $this->get($message);
	}

	/**
	 * Получить перевод сообщения из указанного домена
	 *
	 * @param string $domain
	 * @param string $message
	 * @return string
	 */
	public function dgettext($domain, $message)
	{
		return $this->dget($domain, $message);
	}

	/**
	 * Получить перевод соообщения с учётом множественной формы из домена по умолчанию
	 *
	 * @param string $message
	 * @param int $n
	 * @return string
	 */
	public function ngettext($message, $n)
	{
		return $this->nget($message, $n);
	}

	/**
	 * Получить перевод соообщения с учётом множественной формы из указанного домена
	 *
	 * @param string $domain
	 * @param string $message
	 * @param int $n
	 * @return string
	 */
	public function dngettext($domain, $message, $n)
	{
		return $this->dnget($domain, $message, $n);
	}
}