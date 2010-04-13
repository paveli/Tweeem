<?php
/**
 * Список прав доступа - класс Open_Acl
 * @package OpenStruct
 */

/**
 * Подключаем необходимые файлы
 */
require_once CORE_PATH .'Open/Benchmark'. EXT;
require_once CORE_PATH .'Open/Cache'. EXT;
require_once CORE_PATH .'Open/Config'. EXT;
require_once CORE_PATH .'Open/Singleton'. EXT;

/**
 * Список прав доступа
 */
class Open_Acl extends Open_Singleton
{
	/************
	 * Свойства *
	 ************/

	/**
	 * Массив ролей
	 *
	 * @var array
	 */
	private $roles;

	/**
	 * Массив иерархии ролей
	 *
	 * @var array
	 */
	private $hierarchy;

	/**
	 * Массив ресурсов
	 *
	 * @var array
	 */
	private $resources;

	/**
	 * Массив предоставления доступа
	 *
	 * @var array
	 */
	private $allow;

	/**
	 * Массив запрещения доступа
	 *
	 * @var array
	 */
	private $deny;

	/**
	 * Объект для работы с хранилищем
	 *
	 * @var object
	 */
	private $storage = FALSE;

	/**
	 * Роль с полным доступом
	 *
	 * @var string
	 */
	private $complete_access_role;

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

		$config = Open_Config::getInstance()->get('acl', array('roles', 'hierarchy', 'resources', 'allow', 'deny', 'complete_access_role'));

		$this->roles = $config['roles'];
		$this->hierarchy = $config['hierarchy'];
		$this->resources = $config['resources'];
		$this->allow = $config['allow'];
		$this->deny = $config['deny'];
		$this->complete_access_role = $config['complete_access_role'];
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
	 * При получении если объекта не существует, то по умолчанию будет создан объект Open_Cache
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
			if( !class_exists('Open_Cache') )
			{	require_once CORE_PATH .'Open/Cache'. EXT;
			}

			$this->storage(Open_Cache::getInstance());
		}

		return $this->storage;
	}

	/**
	 * Получить список разрешения доступа с уже вычтеным из него списком запрещения доступа
	 * Метод вызывается рекурсивно и способна свалиться в цикл, если существует цикл в исходных массивах
	 *
	 * @param mixed $role
	 * @return array
	 */
	private function getAllow($role)
	{
		$storageKey = __CLASS__ .'_'. __METHOD__ .'_'. $role;

		/**
		 * Если в сессии не сохранён список, то вычисляем и сохраняем в сессию
		 */
		if( ($allow = $this->storage()->get($storageKey)) === FALSE )
		{
			/**
			 * Получение прямых списков разрешения и запрещения доступа для переданной роли
			 */
			$allow = ((isset($this->allow[$role])) ? $this->allow[$role] : array());
			$deny = ((isset($this->deny[$role])) ? $this->deny[$role] : array());

			/**
			 * Разворачивание всей иерархии, если она есть
			 */
			if( isset($this->hierarchy[$role]) )
			{	foreach($this->hierarchy[$role] as &$parent)
				{	$temp = $this->getAllow($parent);
					$allow = arrayMergeUniqueRecursive($allow, $temp);
				}
			}

			/**
			 * Вычитание из списка разрешения доступа списка запрета доступа
			 */
			$allow = arrayDiffRecursive($allow, $deny);

			/**
			 * Сохранение списка разрешения доступа в сессию
			 */
			$this->storage()->set($storageKey, $allow);
		}

		return $allow;
	}

	/**
	 * Проверить доступ роли $role к ресурсу $resource по действию $action
	 * По умолчанию доступ запрещён
	 *
	 * @param array $role
	 * @param array $resource
	 * @param array $action
	 * @return bool
	 */
	public function isAllowed($role, $resource, $action)
	{
		/**
		 * Если роли, ресурса или действия не существует возвращается запрещение доступа и выдаётся ошибка
		 */
		if( !in_array($role, $this->roles) )
		{	trigger_error(sprintf(Open_Text::getInstance()->dget('errors', 'Unknown role <b>%s</b>'), $role), E_USER_WARNING);
			return FALSE;
		}
		else if( !in_array($resource, array_keys($this->resources)) )
		{	trigger_error(sprintf(Open_Text::getInstance()->dget('errors', 'Unknown resource <b>%s</b>'), $resource), E_USER_WARNING);
			return FALSE;
		}
		else if( !in_array($action, $this->resources[$resource]) )
		{	trigger_error(sprintf(Open_Text::getInstance()->dget('errors', 'Unknown action <b>%s</b>'), $action), E_USER_WARNING);
			return FALSE;
		}

		/**
		 * Даём безоговорочный доступ роли с полным доступом (админу)
		 */
		if( $this->complete_access_role === $role )
		{	return TRUE;
		}

		/**
		 * Запрещение доступа по умолчанию
		 */
		$result = FALSE;

		/**
		 * Проверка разрешения в массиве допуска
		 * Массив допуска формируется с учётом всех родителей роли
		 */
		$allow = $this->getAllow($role);
		if( !in_array($resource, array_keys($allow)) ) {}
		else if( !in_array($action, $allow[$resource]) ) {}
		else
		{	$result = TRUE;
		}

		return $result;
	}

	/**
	 * Проверить запрещение доступа роли $role к ресурсу $resource по действию $action
	 *
	 * @param array $role
	 * @param array $resource
	 * @param array $action
	 * @return bool
	 */
	public function isDenied($role, $resource, $action)
	{	return !$this->isAllowed($role, $resource, $action);
	}
}