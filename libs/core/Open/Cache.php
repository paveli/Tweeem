<?php
/**
 * Работа с кешем в памяти - класс Open_Cache
 * @package OpenStruct
 */

/**
 * Подключаем необходимые файлы
 */
require_once CORE_PATH .'Open/Benchmark'. EXT;
require_once CORE_PATH .'Open/Config'. EXT;
require_once CORE_PATH .'Open/Singleton'. EXT;
require_once CORE_PATH .'Open/Storage/Interface'. EXT;
require_once CORE_PATH .'Open/Sync'. EXT;

/**
 * Обёртка для удобства использования кеша в оперативной памяти
 */
class Open_Cache extends Open_Singleton implements Open_Storage_Interface
{
	/*************
	 * Константы *
	 *************/

	/**
	 * Флаг включения кеша
	 * Если DEBUG включен, кеш выключен и наоборот
	 */
	const ENABLED = TRUE;//DEBUG_NEGATIVE;

	/**
	 * Перечисление доступных движков кеширования
	 */
	const ENGINE_XCACHE = 0x01;
	//const ENGINE_MEMCACHED = 0x02;

	/**
	 * Текущий движок кеширования
	 */
	const ENGINE = self::ENGINE_XCACHE;

	/**
	 * Используемый механизм блокировки
	 */
	const LOCK_TYPE = Open_Sync::LOCK_TYPE_FILE;

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
	 * Если кеш включен
	 * Получить значение под именем $name из кеша если оно существует
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function get($name)
	{
		if(self::ENABLED)
		switch(self::ENGINE)
		{
//			case self::ENGINE_MEMCACHED:
//				break;

			case self::ENGINE_XCACHE: default:
				return ( xcache_isset($name) ? xcache_get($name) : FALSE );
		}

		return FALSE;
	}

	/**
	 * Перегрузка метода get, чтобы можно было обращаться к переменным в кеше вот так:
	 * $cache->foo;
	 * Где $cache - объект Open_Cache
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function __get($name)
	{
		return $this->get($name);
	}

	/**
	 * Если кеширование включено
	 * Сохранить $value в кеш под именем $name на время $lifetime
	 *
	 * @param string $name
	 * @param mixed $value
	 * @param mixed $lifetime Время жизни кеша в секундах, если передано false сохраняется без времени
	 * @return void
	 */
	public function set($name, $value, $lifetime=FALSE)
	{
		if(self::ENABLED)
		switch(self::ENGINE)
		{
//			case self::ENGINE_MEMCACHED:
//				break;

			case self::ENGINE_XCACHE: default:
	    		return ( ($lifetime !== FALSE) ? xcache_set($name, $value, $lifetime) : xcache_set($name, $value) );
		}

		return FALSE;
	}

	/**
	 * Перегрузка метода set, чтобы можно было сохранять переменные в кеш вот так:
	 * $cache->foo = 'bar';
	 * Где $cache - объект Open_Cache
	 * Но без времени жизни кеша
	 *
	 * @param string $name
	 * @param mixed $value
	 * @return void
	 */
	public function __set($name, $value)
	{
		return $this->set($name, $value);
	}

	/**
	 * Проверить существование значения под именем $name в кеше
	 *
	 * @param string $name
	 * @return bool
	 */
	public function exists($name)
	{
		if(self::ENABLED)
		switch(self::ENGINE)
		{
//			case self::ENGINE_MEMCACHED:
//				break;

			case self::ENGINE_XCACHE: default:
				return xcache_isset($name);
		}

		return FALSE;
	}

	/**
	 * Перегрузка метода isset, чтобы можно было проверять существование переменных в кеше вот так:
	 * isset($cache->foo);
	 * Где $cache - объект Open_Cache
	 *
	 * @param string $name
	 * @return bool
	 */
	public function __isset($name)
	{
		return $this->exists($name);
	}

	/**
	 * Если значение под именем $name существует в кеше
	 * Удалить его
	 *
	 * @param mixed $name
	 * @return bool
	 */
	public function delete($name)
	{
		if(self::ENABLED)
		switch(self::ENGINE)
		{
//			case self::ENGINE_MEMCACHED:
//			break;

			case self::ENGINE_XCACHE: default:
				return ( xcache_isset($name) ? xcache_unset($name) : FALSE );
		}

		return FALSE;
	}

	/**
	 * Перегрузка метода unset, чтобы можно было удалять переменные из кеша вот так:
	 * unset($cache->foo);
	 * Где $cache - объект Open_Cache
	 *
	 * @param string $name
	 * @return bool
	 */
	public function __unset($name)
	{
		return $this->delete($name);
	}

	/**
	 * Заблокировать доступ к переменной $name
	 *
	 * @param string $name
	 * @return bool Успех операции
	 */
	public function lock($name)
	{
		if( !self::ENABLED )
		{	return FALSE;
		}

		$S = Open_Sync::getInstance();
		$key = $name . self::ENGINE . __CLASS__;

		switch(self::LOCK_TYPE)
		{
			case Open_Sync::LOCK_TYPE_FILE:
				return $S->fileLock($key);

			case Open_Sync::LOCK_TYPE_SEM:
				return $S->semLock($key);

			default:
				return FALSE;
		}
	}

	/**
	 * Освободить доступ к переменной $name
	 *
	 * @param string $name
	 * @return bool Успех операции
	 */
	public function unlock($name)
	{
		if( !self::ENABLED )
		{	return FALSE;
		}

		$S = Open_Sync::getInstance();
		$key = $name . self::ENGINE . __CLASS__;

		switch(self::LOCK_TYPE)
		{
			case Open_Sync::LOCK_TYPE_FILE:
				return $S->fileUnlock($key);

			case Open_Sync::LOCK_TYPE_SEM:
				return $S->semUnlock($key);

			default:
				return FALSE;
		}
	}
}