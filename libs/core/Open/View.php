<?php
/**
 * Работа со Smarty - класс Open_View
 * @package OpenStruct
 */

/**
 * Подключаем необходимые файлы
 */
require_once CORE_PATH .'Open/Benchmark'. EXT;
require_once CORE_PATH .'Open/Config'. EXT;
require_once CORE_PATH .'Open/Input'. EXT;
require_once CORE_PATH .'Open/Router'. EXT;
require_once CORE_PATH .'Open/Singleton'. EXT;
require_once CORE_PATH .'Open/Smarty'. EXT;

/**
 * Класс для работы с выводом и со Smarty
 *
 */
class Open_View extends Open_Singleton
{
	/************
	 * Свойства *
	 ************/

	/**
	 * Здесь находится объект Smarty
	 * Переменная сделана public, чтобы была возможность снаружи обращаться к Smarty в случае необходимости
	 *
	 * @var object
	 */
	public $smarty;

	/**
	 * Массив заголовков, которые необходимо отправить до отображения страницы
	 *
	 * @var array
	 */
	private $headers;

	/**
	 * Title страницы, хранится здесь до отображения
	 *
	 * @var string
	 */
	private $title;

	/**
	 * Список подключаемых скриптов
	 *
	 * @var array
	 */
	private $js;

	/**
	 * Список подключаемых стилей
	 *
	 * @var array
	 */
	private $css;

	/**
	 * Имя шаблона с разметкой и телом страницы БЕЗ расширения
	 *
	 * @var string
	 */
	private $body;

	/**********
	 * Методы *
	 **********/

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
		if(DEBUG) Open_Benchmark::getInstance()->mark(__CLASS__ .'_start');

		/**
		 * Создаём объект Smarty
		 */
		$this->smarty = new Open_Smarty();

		/**
		 * Передаём Smarty ссылки на экземпляры объектов, для удобства их использования изнутри
		 * Использовать функцию Smarty assign_by_ref не надо, т.к. мы и так имеем дело со ссылками на объекты, иначе получится двойная ссылка
		 */
		$C = Open_Config::getInstance();
		$this->smarty->assign('config', $C);
		$this->smarty->assign('input', Open_Input::getInstance());
		$this->smarty->assign('router', Open_Router::getInstance());
		$this->smarty->assign('text', Open_Text::getInstance());
		$this->smarty->assign('view', $this);

		/**
		 * Устанавливаем значения по умолчанию
		 */
		$config = $C->get(array('headers', 'default_title', 'js', 'css', 'default_body'));
		$this->setHeaders($config['headers']);
		$this->setTitle($config['default_title']);
		$this->setJs($config['js']);
		$this->setCss($config['css']);
		$this->setBody($config['default_body']);
	}

	/**
	 * Деструктор
	 */
	function __destruct()
	{
		$this->smarty = null;

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
	 * Получить массив заголовков
	 *
	 * @return array
	 */
	public function getHeaders()
	{
		return $this->headers;
	}

	/**
	 * Установить массив заголовков
	 *
	 * @param mixed $headers
	 */
	public function setHeaders($headers)
	{
		$this->headers = (is_array($headers)) ? $headers : array($headers);
	}

	/**
	 * Добавить заголовки
	 * Массив или одна строка с заголовком добавляются в конец массива
	 *
	 * @param mixed $headers
	 */
	public function addHeaders($headers)
	{
		if( is_array($headers) )
		{	$this->headers = array_merge($this->headers, $headers);
		}
		else
		{	$this->headers[] = $headers;
		}
	}

	/**
	 * Отправить заголовки браузеру
	 * Отправляются только один раз
	 */
	public function sendHeaders()
	{
		foreach($this->getHeaders() as $header)
		{	header($header);
		}
	}

	/**
	 * Получить значение тега title
	 *
	 * @return string
	 */
	public function getTitle()
	{
		return $this->title;
	}

	/**
	 * Установить тег title
	 *
	 * @param string $title
	 */
	public function setTitle($title)
	{
		$this->title = $title;
	}

	/**
	 * Получить массив подключаемых js-скриптов
	 *
	 * @return array
	 */
	public function getJs()
	{
		return $this->js;
	}

	/**
	 * Установить массив подключаемых js-скриптов
	 *
	 * @param mixed $js
	 */
	public function setJs($js)
	{
		$this->js = (is_array($js)) ? $js : array($js);
	}

	/**
	 * Добавить js-скрипты
	 * Массив или одна строка добавляются в конец массива
	 *
	 * @param mixed $js
	 */
	public function addJs($js)
	{
		if( is_array($js) )
		{	$this->js = array_merge($this->js, $js);
		}
		else
		{	$this->js[] = $js;
		}
	}

	/**
	 * Получить массив подключаемых css стилей
	 *
	 * @return array
	 */
	public function getCss()
	{
		return $this->css;
	}

	/**
	 * Установить массив подключаемых css стилей
	 *
	 * @param mixed $css
	 */
	public function setCss($css)
	{
		$this->css = (is_array($css)) ? $css : array($css);
	}

	/**
	 * Добавить css стили
	 * Массив или одна строка с заголовком добавляются в конец массива
	 *
	 * @param mixed $css
	 */
	public function addCss($css)
	{
		if( is_array($css) )
		{	$this->css = array_merge($this->css, $css);
		}
		else
		{	$this->css[] = $css;
		}
	}

	/**
	 * Получить имя шаблона с разметкой и телом страницы
	 *
	 * @return string
	 */
	public function getBody()
	{
		return $this->body;
	}

	/**
	 * Установить имя шаблона с разметкой и телом страницы
	 *
	 * @param string $body
	 */
	public function setBody($body)
	{
		$this->body = $body;
	}

	/**
	 * Отобразить шаблон
	 * Метод должен быть вызван один раз в конце работы метода контроллера
	 * Имя шаблона присваивается переменной $body и шаблон подключается в index.tpl
	 *
	 * @param string $template Имя шаблона без расширения
	 * @param string $id Идентификатор, который будет передан функции Smarty для управления кешированием
	 * @param int $lifetime Время жизни кеша
	 */
	public function show($template, $id=NULL, $lifetime=NULL)
	{
		/**
		 * Отправляем заголовки
		 */
		$this->sendHeaders();

		/**
		 * Имя отображаемого шаблона
		 */
		$this->smarty->assign('body', $template . TPLEXT);

		/**
		 * Устанавливаем время жизни
		 * По умолчанию 3600 секунд
		 */
		if( isset($lifetime) )
		{
			$this->smarty->cache_lifetime = $lifetime;
		}

		/**
		 * Вызов отображения с идентификатором
		 */
		$this->smarty->display('index'. TPLEXT, $template . ( isset($id) ? '|'. $id : '' ));
	}

	/**
	 * Закеширован ли вывод шаблона $template с идентификатором $id
	 *
	 * @param string $template Имя шаблона без расширения
	 * @param string $id Идентификатор, который будет передан функции Smarty для управления кешированием
	 * @return bool
	 */
	public function isCached($template, $id=NULL)
	{
		return $this->smarty->is_cached('index'. TPLEXT, $template . ( isset($id) ? '|'. $id : '' ));
	}

	/**
	 * Очистить кеш шаблона $template с идентификатором $id
	 *
	 * @param string $template Имя шаблона без расширения
	 * @param string $id Идентификатор, который будет передан функции Smarty для управления кешированием
	 * @return bool
	 */
	public function clearCache($template, $id=NULL)
	{
		return $this->smarty->clear_cache('index'. TPLEXT, $template . ( isset($id) ? '|'. $id : '' ));
	}
}