<?php
/**
 * Работа с входными данными - класс Open_Input
 * @package OpenStruct
 */

/**
 * Подключаем необходимые файлы
 */
require_once CORE_PATH .'Open/Benchmark'. EXT;
require_once CORE_PATH .'Open/Config'. EXT;
require_once CORE_PATH .'Open/Security'. EXT;
require_once CORE_PATH .'Open/Singleton'. EXT;

/**
 * Работа с входными данными
 * Проверка, очистка
 *
 */
class Open_Input extends Open_Singleton
{
	/*************
	 * Константы *
	 *************/

	/**
	 * Константы источников
	 */
	const SOURCE_GET = 0x01;
	const SOURCE_POST = 0x02;
	const SOURCE_COOKIE = 0x03;

	/************
	 * Свойства *
	 ************/

	/**
	 * Свойства для хранения результатов функций
	 */
	private $ip = FALSE;
	private $base = FALSE;
	private $path = FALSE;
	private $url = FALSE;
	private $uri = FALSE;
	private $locale = FALSE;
	private $pathNoLocale = FALSE;


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


		if( Open_Config::getInstance()->get('auto_xss_clean') === TRUE )
		{
			$S = Open_Security::getInstance();
			$S->xssCleanArray($_GET);
			$S->xssCleanArray($_POST);
			$S->xssCleanArray($_COOKIE);
		}
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
	 * Получить данные из _GET массива
	 *
	 * @param string $key Имя переменной
	 * @param bool $xssClean Делать XSS-очистку?
	 * @return mixed
	 */
	public function get($key, $xssClean=TRUE)
	{
		return $this->GetPostCookie(self::SOURCE_GET, $key, $xssClean);
	}

	/**
	 * Получить данные из _POST массива
	 *
	 * @param string $key Имя переменной
	 * @param bool $xssClean Делать XSS-очистку?
	 * @return mixed
	 */
	public function post($key, $xssClean=TRUE)
	{
		return $this->GetPostCookie(self::SOURCE_POST, $key, $xssClean);
	}

	/**
	 * Получить данные из _COOKIE массива
	 *
	 * @param string $key Имя переменной
	 * @param bool $xssClean Делать XSS-очистку?
	 * @return mixed
	 */
	public function cookie($key, $xssClean=TRUE)
	{
		return $this->GetPostCookie(self::SOURCE_COOKIE, $key, $xssClean);
	}

	/**
	 * Получить данные из _GET, _POST, _COOKIE массивов
	 *
	 * @param string $source Константа источника
	 * @param string $key Имя переменной
	 * @param bool $xssClean Делать XSS-очистку?
	 * @return mixed
	 */
	private function GetPostCookie($source, $key, $xssClean=TRUE)
	{
		switch($source)
		{	case self::SOURCE_GET: $source = &$_GET; break;
			case self::SOURCE_POST: $source = &$_POST; break;
			case self::SOURCE_COOKIE: $source = &$_COOKIE; break;
			default: return FALSE;
		}

		if( !isset($source[$key]) )
		{	return FALSE;
		}

		if($xssClean === TRUE)
		{	$temp = $source[$key];
			Open_Security::getInstance()->xssCleanArray($temp);
			return $temp;
		}

		return $source[$key];
	}

	/**
	 * Получить данные из _SERVER массива
	 *
	 * @param string $key Имя переменной
	 * @return mixed
	 */
	public function server($key)
	{
		if( !isset($_SERVER[$key]) )
		{	return FALSE;
		}

		return $_SERVER[$key];
	}

	/**
	 * Возвращает значение переменной с именем $name
	 * Проверяется существование переменной в $_SERVER массиве, возвращается, если найдена
	 * Иначе проверяется в массиве переменных окружения, возвращается, если найдена
	 * Иначе прямое обращение к переменным окружения через функцию getenv()
	 *
	 * @param string $name
	 * @return string
	 */
	private function getServerEnvVar($name)
	{
		if( !empty($_SERVER) && isset($_SERVER[$name]) )
		{	return $_SERVER[$name];
		}
		else if( !empty($_ENV) && isset($_ENV[$name]) )
		{	return $_ENV[$name];
		}
		else if( $temp = @getenv($name) )
		{	return $temp;
		}

		return FALSE;
	}

	/**
	 * Получить IP адрес пользователя
	 *
	 * @return string
	 */
	public function ip()
	{
		if( $this->ip === FALSE )
		{
			/**
			 * Получаем IP посылаемый пользователем
			 */
			$direct_ip = $this->getServerEnvVar('REMOTE_ADDR');

			/**
			 * Получаем IP прокси сервера
			 */
			if( ($proxy_ip = $this->getServerEnvVar('HTTP_X_FORWARDED_FOR')) !== FALSE ) {}
			else if( ($proxy_ip = $this->getServerEnvVar('HTTP_X_FORWARDED')) !== FALSE ) {}
			else if( ($proxy_ip = $this->getServerEnvVar('HTTP_FORWARDED_FOR')) !== FALSE ) {}
			else if( ($proxy_ip = $this->getServerEnvVar('HTTP_FORWARDED')) !== FALSE ) {}
			else if( ($proxy_ip = $this->getServerEnvVar('HTTP_VIA')) !== FALSE ) {}
			else if( ($proxy_ip = $this->getServerEnvVar('HTTP_X_COMING_FROM')) !== FALSE ) {}
			else if( ($proxy_ip = $this->getServerEnvVar('HTTP_COMING_FROM')) !== FALSE ){}
			else
			{	$proxy_ip = '';
			}

			/**
			 * Возвращаем действительный IP, если прокси не существует
			 * Либо IP прокси в паре с действительным IP разделённые '|'
			 */
			$regs = array();
			if( empty($proxy_ip) )
			{
				/**
				 * IP без прокси
				 */
				$this->ip = $direct_ip;
			}
			else if( preg_match('#^([0-9]{1,3}\.){3,3}[0-9]{1,3}#', $proxy_ip, $regs) && (count($regs) > 0) )
			{
				/**
				 * IP прокси и IP за прокси разделённые через '|'
				 */
				$this->ip = $direct_ip .'|'. $regs[0];
			}
			else
			{	/**
				 * Прокси существует, но действительный адрес за прокси не известен
				 * Поэтому вместо него подставляем несуществующий адрес 0.0.0.0
				 */
				$this->ip = $direct_ip . '|' . '0.0.0.0';
			}
		}

		return $this->ip;
	}

	/**
	 * Получить базу URL
	 * Т.е. http://www.example.com/
	 *
	 * @return string
	 */
	public function base()
	{
		if($this->base === FALSE)
		{	$this->base = 'http://'. $this->server('HTTP_HOST');
		}

		return $this->base;
	}

	/**
	 * Получить путь С локалью
	 *
	 * @return string
	 */
	public function path()
	{
		if($this->path === FALSE)
		{	$this->path = preg_replace('#^/index'. EXT .'#i', '', parse_url($this->server('REQUEST_URI'), PHP_URL_PATH));
			$this->path = (!isset($this->path{0}) || $this->path{0} != '/' ? '/'. $this->path : $this->path);
			$this->path .= ($this->path{strlen($this->path)-1} != '/' ? '/' : '');
		}

		return $this->path;
	}

	/**
	 * Получить URL
	 * Т.е. http://www.example.com/ru/hello/world/
	 *
	 * @param string $locale
	 * @return string
	 */
	public function url()
	{
		if($this->url === FALSE )
		{	$this->url = $this->base() . $this->path();
		}

		return $this->url;
	}

	/**
	 * Получить URI
	 * Т.е. http://www.example.com/ru/hello/world/?foo=bar
	 *
	 * @param string $locale
	 * @return string
	 */
	public function uri()
	{
		if($this->uri === FALSE)
		{	$this->uri = $this->base() . $this->path() . ( ($this->server('QUERY_STRING')) ? '?'. $this->server('QUERY_STRING') : '');
		}

		return $this->uri;
	}

	/**
	 * Получить локаль переданную в запросе
	 * Если локаль передана в запросе, то возвращается с предшествующим слешем
	 * Если не передана, то возвращается пустая строка
	 *
	 * @return string
	 */
	public function locale()
	{
		if($this->locale === FALSE)
		{	$locales = array_keys(Open_Config::getInstance()->get('locales'));
			preg_match('#^/('. implode('|', $locales) .')#i', $this->path(), $temp);
			$this->locale = (!empty($temp) ? $temp[0] : '');
		}

		return $this->locale;
	}

	/**
	 * Получить путь БЕЗ локали в начале
	 *
	 * @return string
	 */
	public function pathNoLocale()
	{
		if($this->pathNoLocale === FALSE)
		{	$locales = array_keys(Open_Config::getInstance()->get('locales'));
			$this->pathNoLocale = preg_replace('#^/('. implode('|', $locales) .')#i', '', $this->path());
		}

		return $this->pathNoLocale;
	}
}