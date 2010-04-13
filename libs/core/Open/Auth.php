<?php
/**
 * Аутентификация - класс Open_Auth
 * @package OpenStruct
 */

/**
 * Подключаем необходимые файлы
 */
require_once CORE_PATH .'Open/Auth/Identifier/Interface'. EXT;
require_once CORE_PATH .'Open/Benchmark'. EXT;
require_once CORE_PATH .'Open/Db'. EXT;
require_once CORE_PATH .'Open/Singleton'. EXT;
require_once CORE_PATH .'Open/Storage/Interface'. EXT;
require_once CORE_PATH .'Open/Text'. EXT;
require_once MODELS_PATH .'UserModel'. EXT;

/**
 * Аутентификация
 */
class Open_Auth extends Open_Singleton
{
	/*************
	 * Константы *
	 *************/

	/**
	 * Имя cookie содержащей хэш для авторизации
	 */
	const COOKIE_NAME = 'T';

	/************
	 * Свойства *
	 ************/

	/**
	 * Объект для работы с хранилищем данных, должен соответствовать интерфейсу Open_Storage_Interface
	 *
	 * @var object
	 */
	private $storage = FALSE;

	/**
	 * Объект идентификатор, должен соответствовать интерфейсу Open_Auth_Identifier_Interface
	 *
	 * @var object
	 */
	private $identifier = FALSE;

	/**
	 * Сюда будет помещена сущность прошедшая аутентификацию
	 *
	 * @var mixed
	 */
	private $identity = FALSE;

	/**
	 * Ключ к данными в хранилище
	 *
	 * @var string
	 */
	private $storageKey;

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

		$this->storageKey = __CLASS__ .'_identity';
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
	 * Установить/получить объект для работы с хранилищем данных
	 * Должен соответствовать Open_Storage_Interface
	 * При получении если объекта не существует, то по умолчанию будет создан объект Open_Session
	 *
	 * @param object $storage
	 * @return object
	 */
	public function storage($storage=NULL)
	{
		if( isset($storage) )
		{
			if( !($storage instanceof Open_Storage_Interface) )
			{	trigger_error(sprintf(Open_Text::getInstance()->dget('errors', 'Storage passed to <b>%s</b> does not implements <b>Open_Storage_Interface</b>'), __CLASS__), E_USER_ERROR);
			}

			$this->storage = $storage;
		}
		else if( $this->storage === FALSE )
		{
			if( !class_exists('Open_Session') )
			{	require_once CORE_PATH .'Open/Session'. EXT;
			}

			$this->storage(Open_Session::getInstance());
		}

		return $this->storage;
	}

	/**
	 * Установить/получить объект идентификатор
	 * Должен соответствовать Open_Auth_Identifier_Interface
	 * При получении если объекта не существует, то по умолчанию будет создан объект Open_Auth_Identifier_Db
	 *
	 * @param object $identifier
	 * @return object
	 */
	public function identifier($identifier=NULL)
	{
		if( isset($identifier) )
		{
			if( !($identifier instanceof Open_Auth_Identifier_Interface) )
			{	trigger_error(Open_Text::getInstance()->dget('errors', 'Identifier passed to <b>Open_Auth</b> does not implements <b>Open_Auth_Identifier_Interface</b>'), E_USER_ERROR);
			}

			$this->identifier = $identifier;
		}
		else if( $this->identifier === FALSE )
		{
			if( !class_exists('Open_Auth_Identifier_Db') )
			{	require_once CORE_PATH .'Open/Auth/Identifier/Db'. EXT;
			}

			$this->identifier(Open_Auth_Identifier_Db::getInstance());
		}

		return $this->identifier;
	}

	/**
	 * Получить сущность прошедшую аутентификацию
	 * Если аутентификация не пройдена будет возвращено FALSE
	 *
	 * @param mixed $identity
	 * @return mixed
	 */
	public function identity($identity=NULL)
	{
		if( isset($identity) )
		{	$this->identity = $identity;
		}

		return $this->identity;
	}

	/**
	 * Войти в систему
	 *
	 * @param string $identity
	 * @param string $credential
	 * @return mixed Успех операции
	 */
	public function authenticate($identity=FALSE, $credential=FALSE)
	{
		$S = $this->storage();

		/**
		 * Если в хранилище сохранены данные по пользователе
		 */
		if( ($storageIdentity = $S->get($this->storageKey)) !== FALSE )
		{
			$this->identity($storageIdentity);

			return TRUE;
		}

		/**
		 * Попытка получения данных из куки
		 *
		 * Эти строки являются внесённым в Open_Auth изменением
		 */
		if( $identity === FALSE && $credential === FALSE && ($data = $this->getCookieData()) !== FALSE )
		{
			$identity = $data['login'];
			$credential = $data['password'];
		}

		/**
		 * Если в хранилище пользователь не найден
		 * И если переданы значения для аутентификации, пытаемся идентифицировать такого пользователя и сохранить в сессию
		 */
		if( $identity !== FALSE && $credential !== FALSE )
		{
			$I = $this->identifier();

			if( $result = $I->identify($identity, $credential) )
			{
				$temp = $I->identity();
				$S->set($this->storageKey, $this->identity($temp));
				//$this->setCookie($temp['hash']);
			}

			return $result;
		}

		/**
		 * Если пользователь так и не найден, возвращаем, что аутентификация не пройдена
		 */
		return FALSE;
	}

	/**
	 * Выйти из системы
	 *
	 * @return bool
	 */
	public function inauthenticate()
	{
		$this->storage()->delete($this->storageKey);
		$this->unsetCookie();
		$this->identity(FALSE);
		$this->identifier()->identity(FALSE);

		return TRUE;
	}

	/**********************
	 * Добавленные методы *
	 **********************/

	/**
	 * Получить данные из cookie аутентификации
	 *
	 * @return mixed Если cookie нет, или там кривые данные возвращается FALSE
	 */
	public function getCookieData()
	{
		if( ($hash = Open_Input::getInstance()->cookie(self::COOKIE_NAME)) === FALSE || ($data = UserModel::getInstance()->hashDecode($hash)) === FALSE )
		{
			return FALSE;
		}

		return $data;
	}

	/**
	 * Установить cookie аутентификации
	 * На десять лет, только для этого поддомена, только в пути '/', только через http
	 *
	 * @param string $hash
	 */
	public function setCookie($hash)
	{
		setcookie(self::COOKIE_NAME, $hash, TIME+315532800, '/', NULL, FALSE, TRUE);
	}

	/**
	 * Удалить cookie аутентификации
	 */
	public function unsetCookie()
	{
		setcookie(self::COOKIE_NAME, NULL, TIME-86400, '/', NULL, FALSE, TRUE);
	}
}