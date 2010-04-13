<?php
/**
 * Подключаем необходимые файлы
 */
require_once CORE_PATH .'Open/Acl'. EXT;
require_once CORE_PATH .'Open/Auth'. EXT;
require_once CORE_PATH .'Open/Controller'. EXT;
require_once LIBRARIES_PATH .'Tweeem/Auth/Identifier/Twitter'. EXT;
require_once MODELS_PATH .'UserModel'. EXT;

/**
 * Базовый класс контроллера для текущего приложения
 *
 */
abstract class Tweeem_Controller extends Open_Controller
{
	/**
	 * Объект для работы со списками прав доступа
	 *
	 * @var object
	 */
	protected $acl;

	/**
	 * Объект аутентификации
	 *
	 * @var object
	 */
	protected $auth;

	/**
	 * Объект текущего пользователя
	 *
	 * @var object
	 */
	protected $user;

	/**
	 * Конструктор
	 */
	protected function __construct()
	{
		parent::__construct();

		/**
		 * Получение объекта для работы со списками разделения доступа
		 */
		$this->acl = Open_Acl::getInstance();
		$this->view->smarty->assign('acl', $this->acl);

		/**
		 * Создание объекта аутентификации
		 */
		$A = &$this->auth;
		$A = Open_Auth::getInstance();
		$A->identifier(Tweeem_Auth_Identifier_Twitter::getInstance());

		/**
		 * Аутентификация и задание юзера
		 */
		$A->authenticate();
		$this->user = ( ($temp = $A->identity()) !== FALSE ? $temp : UserModel::getInstance()->getGuest() );
		$this->view->smarty->assign_by_ref('user', $this->user);
	}
}