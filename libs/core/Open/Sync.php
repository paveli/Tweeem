<?php
/**
 * Средства синхронизации процессов - класс Open_Sync
 * @package OpenStruct
 */

/**
 * Подключаем необходимые файлы
 */
require_once CORE_PATH .'Open/Benchmark'. EXT;
require_once CORE_PATH .'Open/Text'. EXT;

/**
 * Средства синхронизации процессов
 */
class Open_Sync extends Open_Singleton
{
	/*************
	 * Константы *
	 *************/

	/**
	 * Константы типов блокировки
	 */
	const LOCK_TYPE_FILE = 0x01;
	const LOCK_TYPE_SEM = 0x02;

	/************
	 * Свойства *
	 ************/

	/**
	 * Массив идентификаторов используемых файлов для блокировок
	 *
	 * @var array
	 */
	private $files = array();

	/**
	 * Массив идентификаторов используемых семафоров
	 *
	 * @var array
	 */
	private $semaphores = array();

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
		/**
		 * Закрытие всех файлов, также снимается блокировка, если она есть
		 */
		foreach($this->files as $handle)
		{
			fclose($handle);
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
	 * Заблокировать доступ к ресурсу с ключом $key на основе файловой блокировки
	 * Создаётся временный файл, на котором осуществляется эксклюзивная блокировка
	 *
	 * @param string $key
	 * @return bool Успех операции
	 */
	public function fileLock($key)
	{
		/**
    	 * Если нет идентификатора
    	 */
		if( !isset($this->files[$key]) )
		{
			/**
			 * Получаем путь к временному файлу
			 * Если директории не существует, пытаемся создать
			 */
			if( !file_exists($dirpath = TEMP_PATH . str_replace('_', '/', __CLASS__) .'/locks/') )
			{
				$temp = umask(0);
				mkdir($dirpath, 0777, true);
				umask($temp);
			}
			$filepath = $dirpath . Open_Convert::getInstance()->toBase64($key) .'.tmp';

			/**
			 * Открываем файл
			 */
			if( ($this->files[$key] = fopen($filepath, 'w')) === FALSE )
			{
				/**
				 * Выдаётся стандартная ошибка уровня E_WARNING
				 */
				return FALSE;
			}

			//p(strftime('%T', time()) .' '. numberFormat(fmod(microtime(true), 1), 4) ."\t $key file got");
		}

		/**
		 * Пытаемся заблокировать файл
		 */
		if( !($result = flock($this->files[$key], LOCK_EX)) )
		{
			trigger_error(sprintf(Open_Text::getInstance()->dget('errors', 'File to lock with key <b>%s</b> cannot be locked'), $key), E_USER_WARNING);
		}

		//p(strftime('%T', time()) .' '. numberFormat(fmod(microtime(true), 1), 4) ."\t $key file aquired");

		return $result;
	}

	/**
     * Освободить доступ к ресурсу на основе файловой блокировки
     *
     * @param string $key
     * @return bool Успех операции
     */
    public function fileUnlock($key)
    {
    	/**
    	 * Если идентификатор уже есть
    	 */
    	if( isset($this->files[$key]) )
    	{
			//p(strftime('%T', time()) .' '. numberFormat(fmod(microtime(true), 1), 4) ."\t $key file released");
			return flock($this->files[$key], LOCK_UN);
    	}

		return TRUE;
    }

    /**
     * Заблокировать доступ к ресурсу с ключом $key на основе семафора
     * Эксклюзивная блокировка, т.е. семафор в данном случае является мьютексом
     * Желательно, чтобы ключ был целым значением от 0 до 255
     *
     * @param string $key
     * @return bool Успех операции
     */
	public function semLock($key)
	{
    	/**
    	 * Если нет идентификатора
    	 */
		if( !isset($this->semaphores[$key]) )
		{
			/**
			 * Пытаемся получить ключ к файлу и получить идентификатор семафора по ключу
			 * В данном случае семафор является мьютексом
			 */
			if( ($fkey = ftok(__FILE__, ((int)$key & 0xff))) < 0 || ($this->semaphores[$key] = sem_get($fkey, 1, 0664, 1)) === FALSE )
			{
				/**
				 * В случае ошибки при получении ключа в ftok() или при получении семафора sem_get() выдаётся стандартная ошибка
				 */
				trigger_error(sprintf(Open_Text::getInstance()->dget('errors', 'Unable to get semaphore with key <b>%s</b>'), $key), E_USER_ERROR);
			}

			//p(strftime('%T', time()) .' '. numberFormat(fmod(microtime(true), 1), 4) ."\t $key sem got");
		}

		/**
		 * Пытаемся захватить семафор
		 */
		if( !($result = sem_acquire($this->identifiers[$name])) )
		{
			trigger_error(sprintf(Open_Text::getInstance()->dget('errors', 'Semaphore with key <b>%s</b> cannot be acquired'), $name), E_USER_WARNING);
		}

		//p(strftime('%T', time()) .' '. numberFormat(fmod(microtime(true), 1), 4) ."\t $name sem aquired");

		return $result;
    }

    /**
     * Освободить доступ к ресурсу на основе семафора
     *
     * @param string $key
     * @return bool Успех операции
     */
    public function semUnlock($key)
    {
    	if( isset($this->semaphores[$key]) )
    	{
			//p(strftime('%T', time()) .' '. numberFormat(fmod(microtime(true), 1), 4) ."\t $key sem released");
			return sem_release($this->semaphores[$key]);
    	}

		return TRUE;
    }
}