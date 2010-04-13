<?php
/**
 * Работа с БД - класс Open_Db
 * @package OpenStruct
 */

/**
 * Подключаем необходимые файлы
 */
require_once CORE_PATH .'Open/Benchmark'. EXT;
require_once CORE_PATH .'Open/Config'. EXT;
require_once CORE_PATH .'Open/Result'. EXT;
require_once CORE_PATH .'Open/Singleton'. EXT;

/**
 * Работа с MySQL БД
 *
 */
class Open_Db extends Open_Singleton
{
	/*************
	 * Константы *
	 *************/

	const LOGGING = DEBUG;

	/************
	 * Свойства *
	 ************/

	/**
	 * Массив конфигурации
	 * Читается из конфига и сохраняется сюда
	 *
	 * @var array
	 */
	private $config;

	/**
	 * Массив соединений с БД
	 * Ключи - названия профилей
	 *
	 * @var array
	 */
	private $links = array();

	/**
	 * Текущий профиль
	 *
	 * @var string
	 */
	private $profile = FALSE;

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

		$this->config = Open_Config::getInstance()->get('db');

		/**
		 * Если установлен параметр автоматического соединения с БД, то соединяемся
		 */
		if( $this->config['auto_connect'] === TRUE )
		{	$this->connect();
		}
	}

	/**
	 * Деструктор
	 *
	 */
	function __destruct()
	{
		/**
		 * Закрываем все открытые соединения
		 * Постоянные соединения закрыты не будут
		 */
		foreach($this->links as $key => &$value)
		{	mysql_close($value) or triggerError('<i>'. Open_Text::getInstance()->dget('errors', 'Unable to close connection') .' - <b>'. $key .'</b> !<br><b>#'. mysql_errno($value) .'</b> - '. mysql_error($value) .'</i><br />', E_DB);
		}

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
	 * Получить текущий профиль
	 *
	 * @return string
	 */
	public function getProfile()
	{
		return $this->profile;
	}

	/**
	 * Установить профиль
	 *
	 * @param string $profile
	 */
	private function setProfile($profile)
	{
		$this->profile = $profile;
	}

	/**
	 * Переключить текущий профиль
	 *
	 * @param string $profile Новый профиль
	 * @return bool Успех операции
	 */
	public function switchProfile($profile)
	{
		if( !isset($this->links[$profile]) )
		{	return $this->connect($profile);
		}

		$this->setProfile($profile);

		return TRUE;
	}

	/**
	 * Получить идентификатор соединения с БД по профилю
	 * Если профиль не указан, то используется профиль по умолчанию
	 * Если соединения по профилю не существует, то осуществляется попытка подключения
	 * Чтобы была возможность получить идентификатор извне и использовать в других функциях
	 *
	 * @param string $profile
	 * @return mixed
	 */
	public function getLink($profile=FALSE)
	{
		if( $profile === FALSE )
		{
			$profile = $this->getProfile();

			if( $profile === FALSE )
			{	$this->connect();
				$profile = $this->getProfile();
			}
		}

		if( isset($this->links[$profile]) )
		{	return $this->links[$profile];
		}
		else
		{	return ( $this->connect($profile) ? $this->links[$profile] : FALSE );
		}
	}

	/**
	 * Установить соединение с БД
	 * Если параметр профиль не передан, то подключение осуществляется по профилю по умолчанию
	 *
	 * @param string $profile По какому профилю подключиться
	 * @return bool Успех операции
	 */
	public function connect($profile=FALSE)
	{
		$C = Open_Config::getInstance();

		/**
		 * Если профиль не передан, то берём профиль по умолчанию
		 */
		if( $profile === FALSE )
		{	$profile = $this->config['default_profile'];
		}

		/**
		 * Если соединение по этому профилю уже существует, то возвращаем успех операции
		 */
		if( isset($this->links[$profile]) )
		{	return TRUE;
		}

		/**
		 * Если выбранного профиля в конфиге не существует, то возвращаем провал операции
		 */
		if( !isset($this->config['profiles'][$profile]) )
		{
			triggerError(sprintf(Open_Text::getInstance()->dget('errors', 'Profile <b>%s</b> for DB does not exist in config'), $profile), E_DB);
			return FALSE;
		}
		else
		{	$P = $this->config['profiles'][$profile];
		}

		/**
		 * Сохраняем текущий профиль
		 */
		$this->setProfile($profile);

		/**
		 * Соединяемся с базой с учётом выбранного метода - connect или pconnect
		 */
		$this->links[$profile] = (($P['pconnect']) ? @mysql_pconnect($P['host'], $P['user'], $P['password']) : @mysql_connect($P['host'], $P['user'], $P['password']) ) or triggerError("<i>". Open_Text::getInstance()->dget('errors', 'Unable to connect to the server') ." - <b>". $P['host'] ."</b> !<br><b>#". mysql_errno() ."</b> - ". mysql_error() ."</i><br />", E_DB);

		/**
		 * Устанавливаем три переменные сеанса
		 * character_set_client
		 * character_set_connection
		 * character_set_result
		 * Для корректной работы с кодировкой
		 */
		$this->query("SET NAMES '". strtoupper(preg_replace('#[^a-z0-9]#i', '', $C->get('charset'))) ."'");

		/**
		 * Выбираем рабочую базу
		 */
		mysql_select_db($P['db'], $this->links[$profile]) or triggerError("<i>". Open_Text::getInstance()->dget('errors', 'Unable to select DB') ." - <b>" . $P['host'] ."</b> !<br><b>#". mysql_errno($this->links[$profile]) ."</b> - ". mysql_error($this->links[$profile]) ."</i><br />", E_DB);

		return TRUE;
	}

	/**
	 * Закрыть соединение с БД
	 * Если параметр профиль не передан, то отключается подключение по умолчанию
	 *
	 * @param string $profile
	 * @return bool Успех операции
	 */
	public function disconnect($profile=FALSE)
	{
		/**
		 * Если профиль не передан, то берём профиль по умолчанию
		 */
		if( $profile === FALSE )
		{	$profile = $this->getProfile();
		}

		/**
		 * Закрываем соединение и удаляем элемент текущего профиля из массива соединений
		 */
		if( in_array($profile, $this->links) )
		{	mysql_close($this->links[$profile]) or triggerError("<i>". Open_Text::getInstance()->dget('errors', 'Unable to close connection') ." - <b>". $profile ."</b> !<br><b>#". mysql_errno($this->links[$profile]) ."</b> - ". mysql_error($this->links[$profile]) ."</i><br />", E_DB);
			unset($this->links[$profile]);
		}

		return TRUE;
	}

	/**
	 * Отправить запрос, получить результат
	 * Строка запроса НЕ должна заканчиваться точкой с запятой (претензия из мануала)
	 * Не забываем пользоваться методом escape(), перед внесением данных в запрос, чтобы сделать данные безопасными
	 * Просто выполняет запрос и возвращает результат
	 *
	 * @param string $query
	 * @return mixed
	 */
	public function query($query)
	{
		$link = $this->getLink();

		if(self::LOGGING)
		{	$start = microtime(true);
		}

		$result = mysql_query($query, $link) or triggerError('<pre><b>#'. mysql_errno($link) .'</b> - '. mysql_error($link) .'</pre>', E_DB);

		if(self::LOGGING)
		{	$end = microtime(true);
			$log = 'MySQL query: '. $query .' | executed in '. numberFormat($end-$start, 6) .'s';
			error_log($log, 0);
		}

		return $result;
	}

	/**
	 * Отправить запрос, получить результат
	 * Строка запроса НЕ должна заканчиваться точкой с запятой (претензия из мануала)
	 * Не забываем пользоваться методом escape(), перед внесением данных в запрос, чтобы сделать данные безопасными
	 * Если в результате получена ссылка на ресурс, то он преобразовывается в объект Open_Result для удобства дальнейшего использования
	 *
	 * @param string $query
	 * @return mixed
	 */
	public function q($query)
	{
		$result = $this->query($query);

		if( is_resource($result) )
		{	$result = new Open_Result($result);
		}

		return $result;
	}

	/**
	 * Синоним для метода q()
	 *
	 * @param string $query
	 * @return mixed
	 */
	public function result($query)
	{
		return $this->q($query);
	}

	/**
	 * Экранировать значение для добавления в БД
	 * С добавлением окружающих кавычек
	 *
	 * @param mixed $value
	 * @return mixed
	 */
	public function escape($value)
	{
		if( !is_numeric($value) )
		{	$value = "'" . mysql_real_escape_string($value, $this->getLink()) . "'";
	    }

	    return $value;
	}

	/**
	 * Экранировать все значения массива для добавления в БД
	 * С добавлением окружающих кавычек
	 *
	 * @param array $array
	 * @return array
	 */
	public function escapeArray($array)
	{
		$link = $this->getLink();
		foreach($array as &$value)
		{
			if( is_array($value) )
			{	$value = $this->escapeArray($value);
			}
			else if( !is_numeric($value) )
			{	$value = "'" . mysql_real_escape_string($value, $link) . "'";
			}
		}

		return $array;
	}

	/**
	 * Возвращает ID, сгенерированный при последнем INSERT-запросе
	 * Используется прямое обращение к БД вместо PHP-функции mysql_insert_id(), т.к. в документации сказано, что эта функция некоректно работает, если primary key имеет MySQL-тип BIGINT, прямое обращение должно этого избежать
	 *
	 * @return int
	 */
	public function insertID()
	{
		$result = $this->query('SELECT LAST_INSERT_ID()');
		$temp = mysql_fetch_array($result);
		mysql_free_result($result);
		return $temp[0];
	}

	/**
	 * SELECT FOUND_ROWS();
	 * Если предыдущий запрос SELECT был запущен с опцией SQL_CALC_FOUND_ROWS
	 * То этим запросом можно получить результат, т.е. сколько рядов могло бы быть возвращено без указания LIMIT
	 * Если запрос был без опции SQL_CALC_FOUND_ROWS, то возвращённое число будет неверным
	 * Полезно при создании постраничного вывода
	 *
	 * @return int
	 */
	public function foundRows()
	{
		$result = $this->query('SELECT FOUND_ROWS()');
		$temp = mysql_fetch_array($result);
		mysql_free_result($result);
		return $temp[0];
	}

	/**
	 * Возвращает число затронутых рядов предыдущей операцией INSERT, DELETE или UPDATE
	 * Если последний запрос был неудачным, функция вернёт -1
	 * Возвращается только количество обработанных рядов, а не рядов удовлетворяющих условию
	 *
	 * @return int
	 */
	public function affectedRows()
	{
		return mysql_affected_rows($this->getLink());
	}
}