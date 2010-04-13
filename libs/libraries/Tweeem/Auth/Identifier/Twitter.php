<?php
/**
 * Идентификатор аутентификации через БД с использованием средств Twitter
 * @package OpenStruct
 */

/**
 * Подключаем необходимые файлы
 */
require_once CORE_PATH .'Open/Auth/Identifier/Interface'. EXT;
require_once CORE_PATH .'Open/Singleton'. EXT;
require_once MODELS_PATH .'UserModel'. EXT;

/**
 * Идентификатор аутентификации через БД
 */
class Tweeem_Auth_Identifier_Twitter extends Open_Singleton implements Open_Auth_Identifier_Interface
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
		$M = UserModel::getInstance();
		
		/**
		 * Если в самом Twitter существует запись
		 */
		if( $M->isAuthorized($identity, $credential) )
		{
			/**
			 * Ищем запись в нашей БД
			 * Если запись не найдена, то добавляем
			 * Иначе обновляем дату существующей и
			 */
			if( ($row = $M->getByLogin($identity)) === FALSE )
			{
				$id = $M->insert($identity, $credential);
			}
			else
			{
				$id = $M->refresh($row, $credential);
			}
			
			/**
			 * Получение
			 */
			$user = $M->getById($id);
			
			/**
			 * Получение URL картинок профиля пользователя
			 */
			$user['profile_image'] = $M->getProfileImage($identity);
			
			$this->identity($user);
			return self::SUCCESS;
		}

		return self::FAILURE;
	}
}