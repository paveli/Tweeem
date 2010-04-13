<?php
/**
 * Базовый класс модели - класс Open_Model
 * @package OpenStruct
 */

/**
 * Подключаем необходимые файлы
 */
require_once CORE_PATH .'Open/Config'. EXT;
require_once CORE_PATH .'Open/Db'. EXT;
require_once CORE_PATH .'Open/Input'. EXT;
require_once CORE_PATH .'Open/Router'. EXT;
require_once CORE_PATH .'Open/Singleton'. EXT;
require_once CORE_PATH .'Open/View'. EXT;

/**
 * Базовый класс модели
 *
 */
abstract class Open_Model extends Open_Singleton
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
	 * Ссылка на объект для работы с БД
	 *
	 * @var object
	 */
	protected $db;

	/**
	 * Ссылка на объект для ввода данных
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

		$this->config = Open_Config::getInstance();
		$this->db = Open_Db::getInstance();
		$this->input = Open_Input::getInstance();
		$this->router = Open_Router::getInstance();
		$this->text = Open_Text::getInstance();
		$this->view = Open_View::getInstance();
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
}