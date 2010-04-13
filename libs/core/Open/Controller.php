<?php
/**
 * Базовый класс контроллера - класс Open_Controller
 * @package OpenStruct
 */

/**
 * Подключаем необходимые файлы
 */
require_once CORE_PATH .'Open/Config'. EXT;
require_once CORE_PATH .'Open/Input'. EXT;
require_once CORE_PATH .'Open/Router'. EXT;
require_once CORE_PATH .'Open/Singleton'. EXT;
require_once CORE_PATH .'Open/Text'. EXT;
require_once CORE_PATH .'Open/View'. EXT;

/**
 * Базовый класс контроллера
 *
 */
abstract class Open_Controller extends Open_Singleton
{
	/************
	 * Свойства *
	 ************/

	/**
	 * Ссылка на объект конфига
	 *
	 * @var object
	 */
	protected $config;

	/**
	 * Ссылка на объект для работы с входными данными
	 *
	 * @var object
	 */
	protected $input;

	/**
	 * Ссылка на объект маршрутизатора
	 *
	 * @var object
	 */
	protected $router;

	/**
	 * Объект для работы с текстом
	 *
	 * @var object
	 */
	protected $text;

	/**
	 * Ссылка на объект отображения
	 *
	 * @var object
	 */
	protected $view;

	/**
	 * Аргументы из запроса
	 *
	 * @var array
	 */
	protected $arguments;

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

		$this->config = Open_Config::getInstance();
		$this->input = Open_Input::getInstance();
		$this->router = Open_Router::getInstance();
		$this->text = Open_Text::getInstance();
		$this->view = Open_View::getInstance();

		$this->setArguments($this->router->getArguments());
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
	 * Получить аргумент(ы)
	 * Если ничего не передано, то вернётся весь массив аргументов
	 * Иначе вернётся параметр с индексом $index
	 * Если такого не существует, вернётся FALSE
	 *
	 * @param int $index
	 * @return mixed
	 */
	protected function getArguments($index=FALSE)
	{
		if( $index === FALSE )
		{	return $this->arguments;
		}
		else if( isset($this->arguments[$index]) )
		{	return $this->arguments[$index];
		}
		else
		{	return FALSE;
		}
	}

	/**
	 * Задать значение аргументов
	 *
	 * @param array $arguments
	 */
	private function setArguments($arguments)
	{
		$this->arguments = $arguments;
	}
}