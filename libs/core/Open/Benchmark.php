<?php
/**
 * Определение производительсности - класс Open_Benchmark
 * @package OpenStruct
 */

/**
 * Подключаем необходимые файлы
 */
require_once CORE_PATH .'Open/Singleton'. EXT;

/**
 * Определение производительности скрипта
 * Работа с метками времени
 */
class Open_Benchmark extends Open_Singleton
{
	/*************
	 * Константы *
	 *************/

	/**
	 * Количество знаков после десятичной точки при выводе времени
	 */
	const DECIMALS = 6;

	/**
	 * Количество тестов при временном тесте (по умолчанию)
	 */
	const TIMETEST_DEFAULT_QUANTITY = 16;

	/**
	 * Количество итераций при временном тесте (по умолчанию)
	 */
	const TIMETEST_DEFAULT_ITERATIONS = 128;


	/************
	 * Свойства *
	 ************/

	/**
	 * Ассоциативный массив меток времени
	 *
	 * @var array
	 */
	private $marks = array();

	/**
	 * Флаг необходимости вывода при разрушении объекта
	 *
	 * @var bool
	 */
	private $doDestructOutput = DEBUG;

	/**********
	 * Методы *
	 **********/

	/**
	 * Конструктор
	 * Получаем глобальный массив меток созданный без объекта для него
	 * Сохраняем метку создания объекта
	 */
	protected function __construct()
	{
		parent::__construct();

		$this->marks = $GLOBALS['Open_Benchmark_marks'];
		unset($GLOBALS['Open_Benchmark_marks']);

		$this->mark(__CLASS__ .'_start');
	}

	/**
	 * Деструктор
	 * Выводим время жизни объекта и приложения
	 */
	function __destruct()
	{
		/**
		 * Если необходимо выводим информацию о времени работы
		 */
		if( $this->doDestructOutput )
		{
			/**
			 * Вывод всего массива меток
			 */
			reset($this->marks);
			echo "\n\n<!-- The very beginning occured at ". current($this->marks) ." (". strftime('%c', current($this->marks)) .") -->";
			next($this->marks);
			while($mark = each($this->marks))
			{	$temp = numberFormat($mark['value'] - $this->marks['the_very_beginning'], self::DECIMALS);
				echo "\n<!-- {$temp}s elapsed as {$mark['key']} occurred -->";
			}

			/**
			 * Жизнь объекта и приложения
			 */
			echo "\n\n<!-- ". __CLASS__ ." lived for {$this->elapsed('construct_'. __CLASS__)}s since construction until destruction -->";
			echo "\n<!-- Application lived for {$this->elapsed()}s since the very beginning until ". __CLASS__ ." destruction -->";
		}

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
	 * Получить/установить значение флага необходимости вывода на экран при разрушеннии объекта
	 *
	 * @param bool $value
	 * @return bool
	 */
	public function doDestructOutput($value=NULL)
	{
		if( isset($value) )
		{
			$this->doDestructOutput = (bool)$value;
		}

		return $this->doDestructOutput;
	}

	/**
	 * Создание метки по имени
	 * Можно делать вот так, например:
	 * Создать метку 'example_start'
	 * Создать метку 'example_end'
	 * И получить время прошедшее между двумя этими метками по обращению elapsed('example'); вместо elapsed('example_start', 'example_end');
	 *
	 * @param string $name
	 */
	public function mark($name)
	{
		$this->marks[$name] = microtime(TRUE);
	}

	/**
	 * Получить время прошедшее между двумя метками
	 * Если $end не указан, то вычисляется время от метки $start до текущего момента
	 * Если $start не указан, то вычисляется время от самого начала до текущего момента
	 * Если существует метка $start.'_start', и не передан $end, то считается время до такой же метки с окончанием '_end'
	 *
	 * @param string $start Начальная метка
	 * @param string $end Конечная метка
	 * @return float
	 */
	public function elapsed($start='', $end='')
	{
		if( isset($this->marks[$start .'_start']) && $end == '')
		{	$start = $start .'_start';
		}

		$start = ($start == '' || !isset($this->marks[$start])) ? $this->marks['the_very_beginning'] : $this->marks[$start];

		if($end == '' && ($temp = preg_replace('#_start$#i', '', $start)) != $start)
		{	$end = $temp .'_end';
		}

		$end = ( !isset($this->marks[$end]) ? microtime(TRUE) : $this->marks[$end] );

		return numberFormat($end-$start, self::DECIMALS);
	}

	/**
	 * Выдать прошедшее время как html-комментарий
	 * Аналогично методу elapsed()
	 *
	 * @param string $start
	 * @param end $end
	 */
	public function display($start='', $end='')
	{
		$elapsed = $this->elapsed($start, $end);

		if( isset($this->marks[$start .'_start']) && $end == '')
		{	$start = $start .'_start';
		}

		if( $start == '' || !isset($this->marks[$start]) )
		{	$start = 'the very beginning';
		}

		if( $end == '' && ($temp = preg_replace('#_start$#i', '', $start)) != $start )
		{	$end = $temp .'_end';
		}

		if( !isset($this->marks[$end]) )
		{	$end = 'now';
		}

		echo "\n<!-- {$elapsed}s elapsed since $start until $end -->";
	}

	/**
	 * Протестировать время выполнения функции
	 *
	 * Аргумент $function может быть:
	 * - Строка-название вызываемой функции
	 * - Массив функций и аргументов вида: array(string, array), где string - имя функции, array - массив аргументов функции
	 * - Массив функций и аргументов произвольного вида: array(string, string, string, array, string, array, string)
	 * Каждой функции будет поставлен в соответствие следующий массив аргументов, если массива нет, функция вызывается без аргументов
	 *
	 * Результат выводится в виде таблицы следующим образом:
	 * <div class="timetest">
	 * 		<table>...</table>
	 * </div>
	 * Соответственно вы можете изменить внешний вид таблицы
	 *
	 * @param mixed $function
	 * @param int $quantity Количество тестов
	 * @param int $iterations Количество итераций в одном тесте
	 * @param bool $doDisplay Выводить результат на экран?
	 * @return array Результаты
	 */
	public function timetest($function, $quantity=self::TIMETEST_DEFAULT_QUANTITY, $iterations=self::TIMETEST_DEFAULT_ITERATIONS, $doDisplay=TRUE)
	{
		/**
		 * Если передана строка в качестве имени функции, то преобразуем это в массив
		 */
		if( !is_array($function) )
		{	$function = array($function, array());
		}

		/**
		 * Тестирование
		 */
		$result = $names = array();
		$temp = count($function);
		for($i=0, $n=0; $i<$temp; $n++)
		{
			/**
			 * Аргументы функции переданы или нет?
			 */
			$cond = isset($function[$i+1]) && is_array($function[$i+1]) && !is_callable($function[$i+1]);

			/**
			 * Получение массива аргументов
			 */
			$args = ( $cond ? $function[$i+1] : array() );

			/**
			 * Проход по количеству тестов
			 */
			for($j=0; $j<$quantity; $j++)
			{
				/**
				 * Проход по количеству итераций и тайминг
				 */
				$start = microtime(TRUE);
				for($k=0; $k<$iterations; $k++)
				{
					call_user_func_array($function[$i], $args);
				}
				$end = microtime(TRUE);

				$result[$n][] = $end-$start;
			}
			$names[$n] = ( (!is_array($function[$i]) && is_callable($function[$i])) ? $function[$i] : implode('::', $function[$i]) );

			/**
			 * Переход к следующей функции
			 */
			$i += ( $cond ? 2 : 1 );
		}

		/**
		 * Если необходимо отображаем результат
		 * Генерация таблицы
		 */
		if( $doDisplay )
		{
			$header = '<tr><th>Test&nbsp;#</th>';
			$average = '<tr><th>Average</th>';
			$total = '<tr><th>Total</th>';

			$rows = array();
			$overall = 0;
			//$last = count($result)-1;
			foreach($result as $key => &$temp)
			{
				$header .= '<th>'. $names[$key] .'</th>';
				$average .= '<th>'. numberFormat(array_sum($temp)/count($temp), self::DECIMALS) .'</th>';
				$total .= '<th>'. numberFormat(array_sum($temp), self::DECIMALS) .'</th>';
				$overall += array_sum($temp);

				foreach($temp as $ntest => &$value)
				{
					if( !isset($rows[$ntest]) )
					{	$rows[$ntest] = '<td>'. floor($ntest+1) .'</td>';
					}

					$rows[$ntest] .= '<td>'. numberFormat($value, self::DECIMALS) .'</td>';
				}
			}

			$header .= '</tr>';
			$average .= '</tr>';
			$total .= '</tr>';

			echo '<div class="timetest" align="left"><table>'. $header . ('<tr>'. implode('</tr><tr>', $rows) .'</tr>') . $average . $total . ('<tr><th>Overall</th><th colspan="99%">'. numberFormat($overall, self::DECIMALS) .'</th></tr>') .'</table></div>';
		}

		return $result;
	}
}