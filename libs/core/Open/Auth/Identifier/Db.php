<?php
/**
 * Идентификатор аутентификации через БД
 * @package OpenStruct
 */

/**
 * Подключаем необходимые файлы
 */
require_once CORE_PATH .'Open/Auth/Identifier/Interface'. EXT;
require_once CORE_PATH .'Open/Config'. EXT;
require_once CORE_PATH .'Open/Db'. EXT;
require_once CORE_PATH .'Open/Singleton'. EXT;

/**
 * Идентификатор аутентификации через БД
 */
class Open_Auth_Identifier_Db extends Open_Singleton implements Open_Auth_Identifier_Interface
{
	/*************
	 * Константы *
	 *************/

	const SUCCESS = 1;
	const FAILURE = 0;
	const FAILURE_IDENTITY_NOT_FOUND = -1;
	const FAILURE_CREDENTIAL_INVALID = -2;

	/************
	 * Свойства *
	 ************/

	/**
	 * Таблица с данными аутентификации
	 *
	 * @var string
	 */
	private $table;

	/**
	 * Поле таблицы для проверки уникальности пользователя (логин, имя пользователя, e-mail и т.д.)
	 *
	 * @var string
	 */
	private $identityField;

	/**
	 * Поле таблицы для проверки личности пользователя (пароль, хэш и т.д.)
	 *
	 * @var string
	 */
	private $credentialField;

	/**
	 * Функция для одностороннего хэширования данных для сверки с паролем
	 *
	 * @var string
	 */
	private $treatmentCallback;

	/**
	 * Сюда будет помещен объект ряда из таблицы прошедший аутентификацию
	 *
	 * @var mixed
	 */
	private $identity = FALSE;

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

		$config = Open_Config::getInstance()->get('auth', array('table', 'identity_field', 'credential_field', 'treatment_callback'));

		$this->table = $config['table'];
		$this->identityField = $config['identity_field'];
		$this->credentialField = $config['credential_field'];
		$this->treatmentCallback = $config['treatment_callback'];
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
	 * Получить сущность прошедшую идентификацию
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
	 * Идентифицировать по значению уникальности и значению удостоверения личности
	 *
	 * @param string $identity
	 * @param string $credential
	 * @return mixed
	 */
	public function identify($identity, $credential)
	{
		$Db = Open_Db::getInstance();

		$q = "SELECT * FROM `{$this->table}` WHERE `{$this->identityField}`={$Db->escape($identity)} LIMIT 1";
		$result = $Db->result($q);

		if( $result->numRows() == 0 )
		{	return self::FAILURE_IDENTITY_NOT_FOUND;
		}
		else
		{	$temp = $result->rowArray();
			if( $temp[$this->credentialField] == (($this->treatmentCallback !== FALSE) ? call_user_func($this->treatmentCallback, $credential) : $credential) )
			{
				$this->identity($temp);
				return self::SUCCESS;
			}
			else
			{	return self::FAILURE_CREDENTIAL_INVALID;
			}
		}
	}
}