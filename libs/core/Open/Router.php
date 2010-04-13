<?php
/**
 * Маршрутизатор - класс Open_Router
 * @package OpenStruct
 */

/**
 * Подключаем необходимые файлы
 */
require_once CORE_PATH .'Open/Benchmark'. EXT;
require_once CORE_PATH .'Open/Config'. EXT;
require_once CORE_PATH .'Open/Controller'. EXT;
require_once CORE_PATH .'Open/Input'. EXT;
require_once CORE_PATH .'Open/Singleton'. EXT;
require_once CORE_PATH .'Open/Text'. EXT;

/**
 * Работа с URI
 * Определение локали, контролера, метода
 * Перенаправление
 *
 * Памятка (серверные переменные):
 * REQUEST_URI = SCRIPT_NAME . PATH_INFO . '?' . QUERY_STRING
 *
 */
class Open_Router extends Open_Singleton
{
	/*************
	 * Константы *
	 *************/

	const TRIGGER_NONE = 0x00;
	const TRIGGER_404 = 0x01;
	const TRIGGER_ERROR = 0x02;

	/************
	 * Свойства *
	 ************/

	/**
	 * Массив с секциями до маршрутизации
	 *
	 * @var array
	 */
	private $sections;

	/**
	 * Массив с секциями после маршрутизации
	 *
	 * @var array
	 */
	private $sections_r;

	/**
	 * Необходимая часть URI - путь
	 * Пример
	 * Из http://example.com/qwer/asdf/zxcv/?var1=1&var2=2
	 * Это /qwer/asdf/zxcv/
	 *
	 * @var string
	 */
	private $path;

	/**
	 * Путь после маршрутизации
	 *
	 * @var string
	 */
	private $path_r;

	/**
	 * Текущая локаль
	 *
	 * @var string
	 */
	private $locale;

	/**
	 * Текущий контроллер
	 *
	 * @var string
	 */
	private $controller;

	/**
	 * Вызываемый метод контроллера
	 *
	 * @var string
	 */
	private $method;

	/**
	 * Аргументы
	 * Всё, что осталось после отделения локали, контроллера и метода
	 *
	 * @var array
	 */
	private $arguments;

	/**********
	 * Методы *
	 **********/

	/**
	 * Конструктор
	 *
	 */
	protected function __construct()
	{
		parent::__construct();
		if(DEBUG) Open_Benchmark::getInstance()->mark(__CLASS__ .'_start');

		$this->route();
		$this->parse();
		$this->setLocale();
	}

	/**
	 * Деструктор
	 *
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
	 * Получить секцию по номеру до маршрутизации
	 * Нумерация начинается с ноля
	 *
	 * @param int $n
	 * @return mixed В случае отсутствия секции с заданным номером FALSE
	 */
	public function getSection($n)
	{
		return (isset($this->sections[$n]) ? $this->sections[$n] : FALSE);
	}

	/**
	 * Получить секцию по номеру после маршрутизации
	 * Нумерация начинается с ноля
	 *
	 * @param int $n
	 * @return mixed В случае отсутствия секции с заданным номером FALSE
	 */
	public function getSectionR($n)
	{
		return (isset($this->sections_r[$n]) ? $this->sections_r[$n] : FALSE);
	}

	/**
	 * Получить текущую локаль
	 *
	 * @return string
	 */
	public function getLocale()
	{
		return $this->locale;
	}

	/**
	 * Получить текущий контроллер
	 *
	 * @return string
	 */
	public function getController()
	{
		return $this->controller;
	}

	/**
	 * Получить вызываемый метод контроллера
	 *
	 * @return string
	 */
	public function getMethod()
	{
		return $this->method;
	}

	/**
	 * Получить параметры
	 *
	 * @return array
	 */
	public function getArguments()
	{
		return $this->arguments;
	}

	/**
	 * Создать ссылку
	 * Если какая-либо из переменных пропущена или ей присвоено значение FALSE, то на это место ничего не будет добавлено
	 * Если переменная имеет значение TRUE, то на это место будет добавлено значение переданное при обращении БЕЗ маршрутизации
	 * Будте аккуратны с этой функцией, т.к. можно сгенерировать ссылку, которая приведёт к 404
	 * Особенно если оставляете FALSE контроллер и метод, но аргументы переданы, убедитесь, что роутер правильно это поймёт
	 * Используя функцию из Smarty-шаблонов булево значение необходимо указывать маленькими буквами - прихоть Smarty
	 *
	 * @param mixed $locale
	 * @param mixed $controller
	 * @param mixed $method
	 * @param mixed $arguments
	 * @param mixed $query_string
	 * @return string
	 */
	public function link($locale=FALSE, $controller=FALSE, $method=FALSE, $arguments=FALSE, $query_string=FALSE)
	{
		$l = '/';
		$i = 0;

		/**
		 * Проверяем локаль
		 */
		if( $locale === TRUE && isset($this->sections[$i]) && in_array(@$this->sections[$i], array_keys(Open_Config::getInstance()->get('locales'))) )
		{	$l .= $this->sections[$i] .'/';
			$i++;
		}
		else if( !is_bool($locale) )
		{	$l .= $locale .'/';
		}

		/**
		 * Проверяем контроллер
		 */
		if( $controller === TRUE && isset($this->sections[$i]) )
		{	$l .= $this->sections[$i] .'/';
		}
		else if( !is_bool($controller) )
		{	$l .= $controller .'/';
		}
		$i++;

		/**
		 * Проверяем метод
		 */
		if( $method === TRUE && isset($this->sections[$i]) )
		{	$l .= $this->sections[$i] .'/';
		}
		else if( !is_bool($method) )
		{	$l .= $method .'/';
		}
		$i++;

		/**
		 * Проверяем аргументы
		 */
		$temp = array();
		if( $arguments === TRUE )
		{	$temp = array_slice($this->sections, $i);
		}
		else if( !is_bool($arguments) )
		{	$temp = $arguments;
		}

		if( !empty($temp) )
		{	$l .= implode('/', $temp) .'/';
		}

		/**
		 * Проверяем query_string
		 */
		$temp = '';
		if( $query_string === TRUE )
		{	$temp = Open_Input::getInstance()->server('QUERY_STRING');
		}
		else if( !is_bool($query_string) )
		{	$temp = $query_string;
		}

		if( !empty($temp) )
		{	$l .= '?'. $temp;
		}

		return $l;
	}

	/**
	 * Разбить путь на секции. Маршрутизация.
	 */
	private function route()
	{
		$C = Open_Config::getInstance();
		$I = Open_Input::getInstance();

		/**
		 * Берём путь
		 */
		$this->path = $I->path();

		/**
		 * Для маршрутизации берём путь БЕЗ локали
		 */
		$this->path_r = $I->pathNoLocale();


		/**
		 * Проверяем URI на наличие только допустимых символов
		 */
		if( !preg_match('#^['. $C->get('permitted_uri_chars') .']*$#', $this->path) )
		{	trigger404(Open_Text::getInstance()->dget('errors', 'Disallowed characters presented in the URI'));
		}

		/**
		 * Разбиваем путь на секции
		 */
		$this->sections = preg_split('#/#', $this->path, 0, PREG_SPLIT_NO_EMPTY);

		/**
		 * Применяем все маршруты по порядку
		 */
		$routes = $C->get('routes');
		foreach($routes as $regexp => &$replace)
		{	$regexp = '#'. $regexp .'#i';
			$this->path_r = preg_replace($regexp, $replace, $this->path_r);
		}

		/**
		 * Возвращаем назад отнятую локаль по завершении маршрутизации
		 */
		$this->path_r = $I->locale() . $this->path_r;

		/**
		 * Разбиваем путь на секции после маршрутизации
		 */
		$this->sections_r = preg_split('#/#', $this->path_r, 0, PREG_SPLIT_NO_EMPTY);
	}

	/**
	 * Распознать секции
	 *
	 */
	private function parse()
	{
		/**
		 * Берём из конфига массив используемых локалей
		 */
		$C = Open_Config::getInstance();
		$locales = array_keys($C->get('locales'));

		/**
		 * Индекс текущей распознаваемой секции
		 */
		$i = 0;

		/**
		 * Если содержимое первой секции содержится в массиве доступных локалей, значит это локаль
		 * Иначе за локаль принимается значение по умолчанию
		 */
		if( isset($this->sections_r[$i]) && in_array($this->sections_r[$i], $locales) )
		{	$this->locale = $this->sections_r[$i];
			$i++;
		}
		else
		{	$this->locale = $C->get('default_locale');
		}

		/**
		 * Если секция $i существует, значит это контроллер
		 * Иначе принимаем контроллер и метод по умолчанию
		 */
		if( isset($this->sections_r[$i]) )
		{
			$this->controller = $this->sections_r[$i];
			$i++;

			/**
			 * Если секция $i существует, значит это метод
			 * Иначе метод по умолчанию
			 */
			if( isset($this->sections_r[$i]) )
			{	$this->method = $this->sections_r[$i];
				$i++;
			}
			else
			{	$this->method = $C->get('default_method');
			}
		}
		else
		{	$this->controller = $C->get('default_controller');
			$this->method = $C->get('default_method');
		}

		/**
		 * Все секции, которые остались передаются в контроллер как массив параметров
		 * Дальше о них должен заботиться контроллер
		 */
		$this->arguments = array_slice($this->sections_r, $i);
	}

	/**
	 * Задание локали и настройка gettext
	 *
	 */
	private function setLocale()
	{
		$C = Open_Config::getInstance();
		$locales = $C->get('locales');

		/**
		 * Задаём локаль
		 * Если надо, для чисел устанавливаем С локаль
		 */
		setlocale(LC_ALL, $locales[$this->locale]);
		if( $C->get('c_numeric_locale') )
		{	setlocale(LC_NUMERIC, 'C');
		}

		/**
		 * Установка рабочей локали для Open_Text
		 */
		Open_Text::locale($this->locale);

		/**
		 * Задание рабочей кодировки для mb функций
		 */
		mb_internal_encoding($C->get('charset'));
	}

	/**
	 * Подключить контроллер, проверить доступность метода, и вызвать
	 *
	 * @param string $controller Контроллер
	 * @param string $method Метод
	 * @param array $arguments Аргументы
	 * @return mixed Возвращённое методом контроллера значение
	 */
	private function callControllerMethod($controller, $method, $arguments=array(), $trigger=self::TRIGGER_ERROR)
	{
		/**
		 * Получение имени контроллера и метода
		 * Заменяются псевдоразделители '_' на '/' и первая буква каждой части делается заглавной
		 */
		$temp = explode('_', $controller);
		foreach($temp as &$value)
		{	$value = ucwords(strtolower($value));
		}
		$controller = implode('_', $temp);

		/**
		 * Проверка объявлен ли класс с таким именем
		 * Если нет, выдаётся ошибка если надо
		 */
		if( !class_exists($controller) )
		{
			/**
			 * Проверка существует ли файл с контроллером и его подключение
			 */
			if( file_exists($temp = CONTROLLERS_PATH . str_replace('_', '/', $controller) . EXT) )
			{	require_once $temp;
			}
			else switch($trigger)
			{
				case self::TRIGGER_404:
					trigger404();
					break;

				case self::TRIGGER_ERROR:
					trigger_error(sprintf(Open_Text::getInstance()->dget('errors', 'Controller <b>%s</b> was not found'), $controller), E_USER_ERROR);

				default: case self::TRIGGER_NONE:
					break;
			}
		}

		/**
		 * Получение ссылки на объект контроллера
		 */
		$C = call_user_func(array($controller, 'getInstance'), $controller);

		/**
		 * Проверка возможно ли вызвать метод котроллера
		 * Если нет, выдаётся ошибка если надо
		 */
		if( !is_callable(array($C, $method)) )
		{
			switch($trigger)
			{
				case self::TRIGGER_404:
					trigger404();
					break;

				case self::TRIGGER_ERROR:
					trigger_error(sprintf(Open_Text::getInstance()->dget('errors', 'Method <b>%s</b> of controller <b>%s</b> cannot be called'), $method, $controller), E_USER_ERROR);

				default: case self::TRIGGER_NONE:
					break;
			}
		}

		/**
		 * Вызов метода
		 */
		return call_user_func_array(array($C, $method), $arguments);
	}

	/**
	 * Вызвать запрошенный метод контроллера
	 * Необходимо, чтобы метод был public, однако нет необходимости вызывать его дважды
	 * Поэтому введён статический флаг вызова
	 *
	 * @return mixed
	 */
	public function invoke()
	{
		static $called = FALSE;

		if( !$called )
		{	$called = TRUE;
			return $this->callControllerMethod($this->getController(), $this->getMethod(), $this->getArguments(), self::TRIGGER_404);
		}

		return FALSE;
	}

	/**
	 * Вызвать метод контроллера с аргументами
	 * Для того чтобы прекратить выполнение текущего метода котроллера из которого выполняется перенаправление
	 * Необходимо вернуть значение возвращаемое методом перенаправления
	 *
	 * @param string $controller Контроллер
	 * @param string $method Метод
	 * @param array $arguments Аргументы
	 * @return mixed
	 */
	public function call($controller, $method, $arguments=array())
	{
		return $this->callControllerMethod($controller, $method, $arguments, self::TRIGGER_ERROR);
	}

	/**
	 * Перенаправление на URL
	 * Если передан полный URL с хостом, то перенаправление чётко на него
	 * Если передан URL без хоста, то к нему добавляется текущая локаль (если её нет) и текущий хост
	 *
	 * @param string $url
	 */
	public function redirect($url)
	{
		if( !parse_url($url, PHP_URL_HOST) )
		{
			$I = Open_Input::getInstance();

			$locales = array_keys(Open_Config::getInstance()->get('locales'));
			if( !preg_match('#^/('. implode('|', $locales) .')#i', $url) )
			{	$url = $I->locale() . $url;
			}

			$url = $I->base() . $url;
		}

		header('Location: '. $url);
		exit(0);
	}
}