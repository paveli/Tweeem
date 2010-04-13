<?php
/**
 * Подключаем необходимые файлы
 */
require_once LIBRARIES_PATH .'Tweeem/Controller'. EXT;
require_once MODELS_PATH .'TwitterModel'. EXT;
require_once MODELS_PATH .'UserModel'. EXT;

/**
 * Контроллер по умолчанию
 * Главная страница
 */
class Home extends Tweeem_Controller
{
	/*************
	 * Константы *
	 *************/

	/**
	 * Время жизни кеша рабочего пространства
	 * Сутки = 24*60*60
	 */
	const WORKSPACE_LIFETIME = 86400;

	/************
	 * Свойства *
	 ************/

	/**********
	 * Методы *
	 **********/

	/**
	 * Главная страница
	 */
	public function index()
	{
		/**
		 * Если пользователь имеет возможность работать с рабочим пространством
		 * Иначе отправляем его на вход в систему
		 */
		if( $this->acl->isAllowed($this->user['role'], ACL_RESOURCE_WORKSPACE, ACL_ACTION_WORK) )
		{
			$templateId = $this->user['id'] .'|workspace';
			$this->view->show('Home/workspace', $templateId, self::WORKSPACE_LIFETIME);
		}
		else
		{
			$this->router->call(__CLASS__, 'login');
		}
	}

	/**
	 * Вход в систему
	 */
	public function login()
	{
		/**
		 * Если пришли данный из формы
		 */
		if( ($data = $this->input->post('login')) !== FALSE )
		{
			/**
			 * Если данные верны
			 */
			if( ($result = UserModel::getInstance()->validateLogin($data)) === TRUE )
			{
				$result = array();

				/**
				 * Попытка аутентификации
				 */
				$this->auth->inauthenticate();
				switch( $this->auth->authenticate($data['login'], $data['password']) )
				{
					/**
					 * Если прошли, отправляемся на главную страницу
					 */
					case Tweeem_Auth_Identifier_Twitter::SUCCESS:
						if($data['remember'])
						{	$identity = $this->auth->identity();
							$this->auth->setCookie($identity['hash']);
						}
						$this->router->redirect('/');
						break;

					/**
					 * Если не прошли выдаём ошибку
					 */
					case Tweeem_Auth_Identifier_Twitter::FAILURE:
						$result['invalid'] = $this->text->dget('validator', 'Login or password is invalid');
						break;
				}
			}

			$this->view->smarty->assign('loginErrors', $result);
			$this->view->smarty->assign('login', $data);
		}

		$this->view->show('Home/index');
	}

	/**
	 * Выход из системы
	 */
	public function logout()
	{
		/**
		 * Выход с удалением куки и записи в сессии
		 * Редирект на главную страницу
		 */
		$this->auth->inauthenticate();
		$this->router->redirect('/');
	}
}