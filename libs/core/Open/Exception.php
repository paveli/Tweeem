<?php
/**
 * Исключение - класс Open_Exception
 * @package OpenStruct
 */

/**
 * Класс исключения, а так же работы с ошибками
 * Наследуется от класса Exception - стандартного класса в php5
 *
 */
class Open_Exception extends Exception
{
	/************
	 * Свойства *
	 ************/

	/**
	 * Строковые уровни ошибок
	 *
	 * @var array
	 */
	private $levels = array(
			E_ERROR				=>	'Error',
			E_WARNING			=>	'Warning',
			E_PARSE				=>	'Parsing Error',
			E_NOTICE			=>	'Notice',
			E_CORE_ERROR		=>	'Core Error',
			E_CORE_WARNING		=>	'Core Warning',
			E_COMPILE_ERROR		=>	'Compile Error',
			E_COMPILE_WARNING	=>	'Compile Warning',
			E_USER_ERROR		=>	'User Error',
			E_USER_WARNING		=>	'User Warning',
			E_USER_NOTICE		=>	'User Notice',
			E_STRICT			=>	'Runtime Notice',
			E_RECOVERABLE_ERROR	=>	'Recoverable Error (Catchable Fatal Error)',
			E_DB				=>	'DB Error',
			E_403				=>	'403 Forbidden',
			E_404				=>	'404 Not Found',
			E_500				=>	'500 Internal Server Error',
	);

	/**********
	 * Методы *
	 **********/

	/**
	 * Конструктор
	 * Благодаря вызову конструктора базового класса Exception параметры $file и $line указывать не обязательно
	 * В них автоматически будут подставлены значения файла и строки, где был создан экземпляр класса
	 *
	 * @param string $message Текст ошибки
	 * @param int $code Код ошибки
	 * @param string $file Путь к файлу, где произошла ошибка
	 * @param int $line Номер строки с ошибкой
	 */
	function __construct($message, $code, $file=FALSE, $line=FALSE)
	{
		parent::__construct($message, $code);

		if($file !== FALSE)
		{	$this->file = $file;
		}
		if($line !== FALSE)
		{	$this->line = $line;
		}
	}

	/**
	 * Деструктор
	 */
	function __destruct()
	{
		/**
		 * Не вызываем деструктор базового класса, т.к. он там не объявлен и при попытке вызова будет ошибка
		 */
	}

	/**
	 * Преобразование в строку
	 *
	 */
	function __toString()
	{
		return ("<b>Exception:</b> ". __CLASS__
		."<br/><b>Level</b>: {$this->getLevel()}"
		."<br/><b>Message</b>: {$this->getMessage()}"
		."<br/><b>File</b>: {$this->getSafeFile()}"
		."<br/><b>Line</b>: {$this->getLine()}");
	}

	/**
	 * Получить текстовое значение уровня ошибки по коду
	 *
	 * @return string
	 */
	protected function getLevel()
	{
		return $this->levels[$this->code];
	}

	/**
	 * Получить путь к файлу без основания
	 *
	 * @return string
	 */
	protected function getSafeFile()
	{
		return str_replace(BASE_PATH, '', $this->getFile());
	}

	/**
	 * Получить обратное отслеживание ошибки с урезанными именами файлов и преобразованием nl2br
	 *
	 * @return string
	 */
	protected function getSafeTrace()
	{
		return preg_replace('#(\r\n|\n\r|\r|\n)#u', '<br />', "\n". htmlentities(str_replace(BASE_PATH, '', $this->getTraceAsString())));
	}

	/**
	 * Обработка ошибки
	 */
	public function handle()
	{
		$code = $this->getCode();

		/**
		 * Если ошибка была проигнорирована при помощи оператора @
		 * Или ошибка не попадает под уровень отчёта об ошибках
		 */
		if( error_reporting() == 0 || ($code & error_reporting()) != $code )
			return;

		/**
		 * Получаем необходимые значения
		 */
		$level = $this->getLevel();
		$message = $this->getMessage();
		$file = $this->getSafeFile();
		$line = $this->getLine();
		$trace = $this->getSafeTrace();
		
		/**
		 * Определяем шаблон ошибки
		 */
		if( $code <= E_ALL )
		{	$type = 'php';
		}
		else if( $code == E_DB )
		{	$type = 'db';
		}
		else if( $code == E_403 )
		{	$type = '403';
		}
		else if( $code == E_404 )
		{	$type = '404';
		}
		else if( $code == E_500 )
		{	$type = '500';
		}
		
		/**
		 * Если это PHP-ошибка или БД-ошибка
		 * Записываем в лог
		 */
		if( ($code & error_reporting() == $code) && !($code == E_403 || $code == E_404) )
		{	error_log($level .': '. strip_tags($message) .' in file '. $file .' on line '. $line);
		}

		/**
		 * Если необходимо, отображаем ошибку
		 */
		if( ini_get('display_errors') == TRUE )
		{	include ERRORS_PATH .'error_'. $type . EXT;
		}

		/**
		 * Если необходимо завершаем выполнение
		 */
		switch($code)
		{	case E_ERROR: case E_USER_ERROR: case E_DB: case E_403: case E_404: case E_500:
				exit(0);

			default:
				break;
		}
	}
}