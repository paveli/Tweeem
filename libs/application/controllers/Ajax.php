<?php
/**
 * Подключаем необходимые файлы
 */
require_once CORE_PATH .'Open/Cache'. EXT;
require_once CORE_PATH .'Open/Session'. EXT;
require_once LIBRARIES_PATH .'Tweeem/Controller'. EXT;
require_once MODELS_PATH .'UserModel'. EXT;
require_once MODELS_PATH .'TwitterModel'. EXT;

/**
 * Обработка ajax-запросов
 */
class Ajax extends Tweeem_Controller
{
	/*************
	 * Константы *
	 *************/

	/**
	 * Время жизни кеша таймлайна чата
	 * В секундах
	 */
	const CHAT_TIMELINE_LIFETIME = 3600;
	
	/**
	 * Время жизни кеша полученных пользователем прямых сообщений
	 * В секундах
	 */
	const DIRECT_MESSAGES_LIFETIME = 3600;
	
	/**
	 * Время жизни кеша отправленных пользователем прямых сообщений
	 * В секундах
	 */
	const DIRECT_SENT_LIFETIME = 3600;
	
	/**
	 * Время жизни кеша таймлайна прямого общения
	 * В секундах
	 */
	const DIRECT_TIMELINE_LIFETIME = 3600;
	
	/**
	 * Время жизни кеша таймлайна чата
	 * В секундах
	 */
	const LIST_LIFETIME = 3600;
	
	/************
	 * Свойства *
	 ************/

	/**********
	 * Методы *
	 **********/

	/**
	 * Конструктор
	 */
	public function __construct()
	{
		parent::__construct();

		if(DEBUG) Open_Benchmark::getInstance()->doDestructOutput(FALSE);
	}

	/**
	 * Метод для обработки ajax-запросов
	 */
	public function index()
	{

	}
	
	/**
	 * Получение таймлайна для чата
	 */
	public function chatTimeline()
	{
		/**
		 * Если пользователь имеет возможность работать с рабочим пространством
		 */
		if( $this->acl->isAllowed($this->user['role'], ACL_RESOURCE_WORKSPACE, ACL_ACTION_WORK) )
		{
			$S = $this->view->smarty;
			$template = 'Ajax/timeline'. TPLEXT;
			$templateId = $this->user['id'] .'|chat';

			/**
			 * Проверяем наличие в кеше таймлайна для этого пользователя
			 * Если есть отображаем
			 */
			//$S->clear_cache($template, $templateId);
			if( $S->is_cached($template, $templateId) )
			{
				$S->display($template, $templateId);
				return TRUE;
			}
			
			/**
			 * Получаем и аутентифицируемся в модели
			 */
			$M = TwitterModel::getInstance();
			$M->login($this->user['login']);
			$M->password($this->user['password']);
			
			/**
			 * Запрашиваем данные
			 */
			$result = $M->statusFriendsTimeline($this->user['login']);
			
			/**
			 * Если код ответа на запрос более либо равен 300, то считаем это за ошибку
			 */
			if( $M->isError() )
			{	
				trigger500();
			}
			
			/**
			 * Заменяем картинки normal размера на mini
			 * И переворачиваем массив для вывода по дате снизу вверх
			 */
			$timeline = json_decode($result, TRUE);
			foreach($timeline as &$item)
			{
				$item['user']['profile_image_url'] = preg_replace('#_normal(.*?)$#i', '_mini$1', $item['user']['profile_image_url']);
			}
			$timeline = array_reverse($timeline, FALSE);

			/**
			 * Отображаем и сохраняем в кеш
			 */
			$S->assign_by_ref('timeline', $timeline);
			$S->cache_lifetime = self::CHAT_TIMELINE_LIFETIME;
			$S->display($template, $templateId);
		}
		else
		{
			trigger404();
		}
	}
	
	/**
	 * Сделать апдейт таймлайна
	 */
	public function chatUpdate()
	{
		/**
		 * Если пользователь имеет возможность работать с рабочим пространством
		 */
		if( $this->acl->isAllowed($this->user['role'], ACL_RESOURCE_WORKSPACE, ACL_ACTION_WORK) )
		{
			$S = &$this->view->smarty;
			
			/**
			 * Если данные не пришли, либо не верны
			 */
			if( !( ($chat = $this->input->post('chat')) !== FALSE && isset($chat['text']) && !empty($chat['text']) ) )
			{
				trigger500();
			}
			
			/**
			 * Получаем и аутентифицируемся в модели
			 */
			$M = TwitterModel::getInstance();
			$M->login($this->user['login']);
			$M->password($this->user['password']);
			
			/**
			 * Запрос на добавление
			 */
			$result = $M->statusUpdate($chat['text']);
			
			/**
			 * Если код ответа на запрос более либо равен 300, то считаем это за ошибку
			 */
			if( $M->isError() )
			{
				trigger500();
			}
		}
		else
		{
			trigger404();
		}
	}
	
	/**
	 * Получение списка контактов
	 */
	public function contactList()
	{
		/**
		 * Если пользователь имеет возможность работать с рабочим пространством
		 */
		if( $this->acl->isAllowed($this->user['role'], ACL_RESOURCE_WORKSPACE, ACL_ACTION_WORK) )
		{
			$S = $this->view->smarty;
			$template = 'Ajax/list'. TPLEXT;
			$templateId = $this->user['id'] .'|list';

			/**
			 * Проверяем наличие в кеше списка контактов для этого пользователя
			 * Если есть отображаем
			 */
			//$S->clear_cache($template, $templateId);
			if( $S->is_cached($template, $templateId) )
			{
				$S->display($template, $templateId);
				return TRUE;
			}
			
			/**
			 * Получаем модель
			 */
			$M = TwitterModel::getInstance();
					
			/**
			 * Запрашиваем данные для этого пользователя
			 */
			$friends = json_decode($M->userFriends($this->user['login']), TRUE);
			
			/**
			 * Если код ответа на запрос более либо равен 300, то считаем это за ошибку
			 */
			if( $M->isError() )
			{	
				trigger500();
			}
			
			/**
			 * Перед следующим запросом необходима аутентификация
			 */
			$M->login($this->user['login']);
			$M->password($this->user['password']);
			
			/**
			 * Запрашиваем данные
			 */
			$followers = json_decode($M->userFollowers(), TRUE);
			
			/**
			 * Если код ответа на запрос более либо равен 300, то считаем это за ошибку
			 */
			if( $M->isError() )
			{	
				trigger500();
			}
			
			/**
			 * Находим пересечение списков друзей и тех, кто за пользователем следит
			 * Это и будет список контактов
			 */
			foreach($friends as $friendKey => &$friend)
			{
				$areMutual = FALSE;
				foreach($followers as &$follower)
				{
					if( $friend['name'] === $follower['name'] )
					{	$areMutual = TRUE;
						break;
					}
				}
				
				if( !$areMutual )
				{	unset($friends[$friendKey]);
				}
			}
			$list = &$friends;
			
			/**
			 * Заменяем картинки normal размера на mini
			 */
			foreach($list as &$item)
			{
				$item['profile_image_url'] = preg_replace('#_normal(.*?)$#i', '_mini$1', $item['profile_image_url']);
			}
			
			/**
			 * Отображаем и сохраняем в кеш
			 */
			$S->assign_by_ref('list', $list);
			$S->cache_lifetime = self::LIST_LIFETIME;
			$S->display($template, $templateId);
		}
		else
		{
			trigger404();
		}
	}
	
	/**
	 * Получение таймлайна для окна прямого общения
	 * 
	 * @param string $name
	 */
	public function directTimeline($name)
	{
		/**
		 * Если пользователь имеет возможность работать с рабочим пространством
		 */
		if( $this->acl->isAllowed($this->user['role'], ACL_RESOURCE_WORKSPACE, ACL_ACTION_WORK) )
		{
			$S = $this->view->smarty;
			$template = 'Ajax/timeline'. TPLEXT;
			$templateId = $this->user['id'] .'|direct|'. $name;

			/**
			 * Проверяем наличие в кеше таймлайна для этого пользователя
			 * Если есть отображаем
			 */
			$S->clear_cache($template, $templateId);
			if( $S->is_cached($template, $templateId) )
			{
				$S->display($template, $templateId);
				return TRUE;
			}
			
			/**
			 * Получаем и аутентифицируемся в модели
			 */
			$M = TwitterModel::getInstance();
			$M->login($this->user['login']);
			$M->password($this->user['password']);
			
			$C = Open_Cache::getInstance();

			/**
			 * Проверяем наличие массива в кеше
			 * Если есть берём, иначе получаем от Твиттера
			 */
			$messagesCacheId = $this->user['id'] .'_directMessages';
			$C->lock($messagesCacheId);
			//$C->delete($messagesCacheId);
			if( $C->exists($messagesCacheId) )
			{
				$messages = $C->get($messagesCacheId);
			}
			else
			{
				/**
				 * Запрашиваем данные
				 */
				$messages = $M->directMessages();
							
				/**
				 * Если код ответа на запрос более либо равен 300, то считаем это за ошибку
				 */
				if( $M->isError() )
				{	
					trigger500();
				}
				
				/**
				 * Декодируем в виде массива
				 */
				$messages = json_decode($messages, TRUE);
				
				/**
				 * Кладём данные в кеш
				 */
				$C->set($messagesCacheId, $messages, self::DIRECT_MESSAGES_LIFETIME);
			}
			$C->unlock($messagesCacheId);
								
			/**
			 * Оставляем только те сообщения которые отправлены от запрашиваемого пользователя
			 */
			foreach($messages as $key => &$item)
			{
				if( $item['sender']['screen_name'] !== $name )
				{	unset($messages[$key]);
				}
			}
			
			/**
			 * Проверяем наличие массива в кеше
			 * Если есть берём, иначе получаем от Твиттера
			 */
			$sentCacheId = $this->user['id'] .'_directSent';
			$C->lock($sentCacheId);
			//$C->delete($sentCacheId);
			if( $C->exists($sentCacheId) )
			{
				$sent = $C->get($sentCacheId);
			}
			else
			{
				/**
				 * Запрашиваем данные
				 */
				$sent = $M->directSent();
				
				/**
				 * Если код ответа на запрос более либо равен 300, то считаем это за ошибку
				 */
				if( $M->isError() )
				{	
					trigger500();
				}
				
				/**
				 * Декодируем в массив
				 */
				$sent = json_decode($sent, TRUE);
				
				/**
				 * Кладём данные в кеш
				 */
				$C->set($sentCacheId, $sent, self::DIRECT_SENT_LIFETIME);
			}
			$C->unlock($sentCacheId);
			
			/**
			 * Оставляем только те сообщения которые отправлены запрашиваемому пользователю
			 */
			foreach($sent as $key => &$item)
			{
				if( $item['recipient']['screen_name'] !== $name )
				{	unset($sent[$key]);
				}
			}

			$timeline = array_merge($messages, $sent);
			usort($timeline, create_function('$a, $b', '$aCreated = strtotime($a[\'created_at\']); $bCreated = strtotime($b[\'created_at\']); if($aCreated == $bCreated) return 0; return ($aCreated < $bCreated ? 1 : -1);'));
			
			/**
			 * Заменяем картинки normal размера на mini
			 * И переворачиваем массив для вывода по дате снизу вверх
			 */
			foreach($timeline as &$item)
			{
				$item['sender']['profile_image_url'] = preg_replace('#_normal(.*?)$#i', '_mini$1', $item['sender']['profile_image_url']);
				
				/**
				 * Делаем вот такую ссылку для шаблона
				 */
				$item['user'] = &$item['sender'];
			}
			$timeline = array_reverse($timeline, FALSE);

			/**
			 * Отображаем и сохраняем в кеш
			 */
			$S->assign_by_ref('timeline', $timeline);
			$S->cache_lifetime = self::DIRECT_TIMELINE_LIFETIME;
			$S->display($template, $templateId);
		}
		else
		{
			trigger404();
		}
	}
	
	/**
	 * Сделать апдейт таймлайна прямого общения
	 * 
	 * @param string $name
	 */
	public function directUpdate($name)
	{
		/**
		 * Если пользователь имеет возможность работать с рабочим пространством
		 */
		if( $this->acl->isAllowed($this->user['role'], ACL_RESOURCE_WORKSPACE, ACL_ACTION_WORK) )
		{
			$S = &$this->view->smarty;
			
			/**
			 * Если данные не пришли, либо не верны
			 */
			if( !( ($direct = $this->input->post('direct')) !== FALSE && isset($direct['text']) && !empty($direct['text']) ) )
			{
				trigger500();
			}
			
			/**
			 * Получаем и аутентифицируемся в модели
			 */
			$M = TwitterModel::getInstance();
			$M->login($this->user['login']);
			$M->password($this->user['password']);
			
			/**
			 * Запрос на добавление
			 */
			$result = $M->directNew($name, $direct['text']);
			
			/**
			 * Если код ответа на запрос более либо равен 300, то считаем это за ошибку
			 */
			if( $M->isError() )
			{
				trigger500();
			}
		}
		else
		{
			trigger404();
		}
	}
}