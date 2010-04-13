<?php
/**
 * Подключаем необходимые файлы
 */
require_once CORE_PATH .'Open/Convert'. EXT;
require_once CORE_PATH .'Open/Model'. EXT;
require_once CORE_PATH .'Open/Security'. EXT;
require_once MODELS_PATH .'TwitterModel'. EXT;

/**
 * Модель User
 */
class UserModel extends Open_Model
{
	/*************
	 * Константы *
	 *************/

	/**
	 * Рабочая таблица
	 */
	const TABLE = 'users';

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
	 * Авторизован ли пользователь
	 *
	 * @param string $login
	 * @param String $password
	 * @return bool
	 */
	public function isAuthorized($login, $password)
	{
		$M = TwitterModel::getInstance();
		$M->login($login);
		$M->password($password);
		$M->accountVerifyCredentials();

		switch($M->getCode())
		{
			case 200:
				return TRUE;

			case 401: default:
				return FALSE;
		}
	}

	/**
	 * Сгенерировать зашифрованый хэш
	 *
	 * @param int $id Идентификатор в БД
	 * @param string $login Имя пользователя
	 * @param string $password Пароль
	 * @return string
	 */
	public function hashEncode($id, $login, $password)
	{
		static $delimiters = '|#$%@^&*';
		$i = strlen($delimiters)-1;

		$C = Open_Convert::getInstance();
		$data =
			mt_rand(0, 999) . $delimiters{mt_rand(0, $i)} .
			$C->toBase64($password) . $delimiters{mt_rand(0, $i)} .
			$C->toBase64($login) . $delimiters{mt_rand(0, $i)} .
			$id;

		return Open_Security::getInstance()->easyEncrypt($data);
	}

	/**
	 * Расшифровать хэш
	 *
	 * @param string $data
	 * @return array
	 */
	public function hashDecode($data)
	{
		static $delimiters = '|#$%@^&*';

		$C = Open_Convert::getInstance();
		$temp = preg_split('/['. preg_quote($delimiters) .']+/i', Open_Security::getInstance()->easyDecrypt($data), 0, PREG_SPLIT_NO_EMPTY);

		if( count($temp) < 4 )
		{
			return FALSE;
		}

		return array('id' => $temp[3], 'login' => $C->fromBase64($temp[2]), 'password' => $C->fromBase64($temp[1]));
	}

	/**
	 * Зашифровать пароль
	 *
	 * @param string $password
	 * @return string
	 */
	public function treatPassword($password)
	{
		//return md5(strrev($password));
		return $password;
	}

	/**
	 * Получить юзера по ID
	 *
	 * @param int $id
	 * @return mixed Если найден массив ряда, иначе FALSE
	 */
	public function getById($id)
	{
		$q = "SELECT * FROM `". self::TABLE ."` WHERE `id`={$this->db->escape($id)} LIMIT 1";
		$result = $this->db->result($q);

		return ($result->numRows() != 0 ? $result->rowArray() : FALSE);
	}

	/**
	 * Получить юзера по логину
	 *
	 * @param string $login
	 * @return mixed Если найден массив ряда, иначе FALSE
	 */
	public function getByLogin($login)
	{
		$q = "SELECT * FROM `". self::TABLE ."` WHERE `login`={$this->db->escape($login)} LIMIT 1";
		$result = $this->db->result($q);

		return ($result->numRows() != 0 ? $result->rowArray() : FALSE);
	}

	/**
	 * Добавить юзера
	 * По умолчанию присваивается текущая дата и роль простого юзера
	 *
	 * @param string $login Логин пользователя
	 * @param string $password НЕзашифрованый пароль
	 * @return int ID добавленной записи
	 */
	public function insert($login, $password)
	{
		$this->config->load('acl');

		$treatedPassword = $this->treatPassword($password);

		/**
		 * Добавление
		 */
		$q = "INSERT INTO `". self::TABLE ."` (login, password, hash, role, date) VALUES ({$this->db->escape($login)}, {$this->db->escape($treatedPassword)}, '', ". ACL_ROLE_USER .", ". TIME .")";
		$this->db->query($q);
		$id = $this->db->insertID();

		/**
		 * Обновление со сгенерированным hash
		 */
		$hash = $this->hashEncode($id, $login, $password);
		$q = "UPDATE `". self::TABLE ."` SET hash={$this->db->escape($hash)} WHERE id=$id LIMIT 1";
		$this->db->query($q);

		return $id;
	}

	/**
	 * Обновить существующего пользователя
	 * Обновляется дата
	 * Проверяет пароль существующего пользователя с предоставленным, если необходимо изменяется с перегенерацией хэша
	 *
	 * @param array $row Массив существующего пользователя
	 * @param string $password НЕзашифрованый пароль
	 * @return int ID обновляемой записи
	 */
	public function refresh(&$row, $password)
	{
		$treatedPassword = $this->treatPassword($password);

		/**
		 * Обновление с установкой текущей даты
		 */
		$q = "UPDATE `". self::TABLE ."` SET date=". TIME;
		
		/**
		 * Если необходимо также обновляется пароль и хэш
		 */
		if( $row['password'] != $treatedPassword )
		{
			$hash = $this->hashEncode($row['id'], $row['login'], $password);
			$q .= ", password={$this->db->escape($treatedPassword)}, hash={$this->db->escape($hash)}";
		}

		$q .= " WHERE id={$row['id']} LIMIT 1";
		$this->db->query($q);

		return $row['id'];
	}

	/**
	 * Получить массив характеризующий пользователя гостя
	 *
	 * @return array
	 */
	public function getGuest()
	{
		$this->config->load('acl');

		return array(
			'id' => 0,
			'login' => '',
			'password' => '',
			'role' => ACL_ROLE_GUEST,
			'hash' => '',
			'date' => TIME,
			'profile_image' => array('normal' => '', 'mini' => ''),
		);
	}

	/**
	 * Проверить данные из формы логина
	 *
	 * @param array $data
	 * @return mixed
	 */
	public function validateLogin($data)
	{
		if( !class_exists('Open_Validator') )
		{
			require_once CORE_PATH .'Open/Validator'. EXT;
		}

		$rules = array(
			'login' => 'required',
			'password' => 'required',
		);

		return Open_Validator::getInstance()->validate($data, $rules, TRUE);
	}
	
	/**
	 * Получить URL-картинки профиля пользователя с заданым размером
	 * 
	 * @param string $login
	 * @param string $password
	 * @param string $size Размер картинки ('normal', 'mini')
	 * @return string
	 */
	public function getProfileImage($login)
	{
		/**
		 * Получаем модель
		 */
		$M = TwitterModel::getInstance();
		
		/**
		 * Запрашиваем данные
		 */
		$result = json_decode($M->userShow($login));
		
		/**
		 * Если код ответа на запрос более либо равен 300, то считаем это за ошибку
		 */
		if( $M->isError() )
		{
			return array('normal' => '', 'mini' => '');
		}
		
		$imageUrl = $result->profile_image_url;
		
		/**
		 * Заменяем размер
		 */
		return array(
			'normal' => $imageUrl,
			'mini' => preg_replace('#_normal(.*?)$#i', '_mini$1', $imageUrl),
		);
	}
}