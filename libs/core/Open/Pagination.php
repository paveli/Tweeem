<?php
/**
 * Создание постраничной навигации - класс Open_Pagination
 * @package OpenStruct
 */

/**
 * Подключаем необходимые файлы
 */
require_once CORE_PATH .'Open/Singleton'. EXT;

/**
 * Создание постраничной навигации
 */
class Open_Pagination extends Open_Singleton
{
	/*************
	 * Константы *
	 *************/

	const FIRST = 'first';
	const FIRST_DEFAULT_AROUND = 3;
	const FIRST_DEFAULT_GAPS = 4;
	const FIRST_DEFAULT_THRESHOLD = 5;

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
	 * Вычисление ссылок постраничной навигации шаблон номер один
	 * Пример
	 * Вход: (800, 5, 40, 3, 4, 10)
	 * Выход: array(1, 10, 20, true, 37, 38, 39, 40, 41, 42, 43, true, 72, 102, 131, 160)
	 * Вид навигации: 1 10 20 ... 37 38 39 40 41 42 43 ... 72 102 131 160
	 *
	 * @param int $amount Количество объектов
	 * @param int $span Объектов на странице
	 * @param int $current Текущая страница
	 * @param int $around Страниц рядом с текущей
	 * @param int $gaps Максимальное количество промежутков
	 * @param int $threshold Порог промежутка
	 * @return array
	 */
	public function patternFirst($amount, $span, $current, $around=self::FIRST_DEFAULT_AROUND, $gaps=self::FIRST_DEFAULT_GAPS, $threshold=self::FIRST_DEFAULT_THRESHOLD)
	{
		$result = array();

		/**
		 * Количество страниц
		 */
		$pages = ceil($amount/$span);

		/**
		 * Если текущая страница меньше либо равна общему количеству страниц
		 * И если объекты помещаются более, чем на одной странице
		 */
		if( $current <= $pages && $pages !== 1 )
		{
			$left = $middle = $right = array();

			/**
			 * Формирование средней части вывода
			 * Вычисляются страницы слева и справа от текущей
			 */
			for($i=-$around; $i<=$around; $i++)
			{
				$temp = $current + $i;
				if( $temp >= 1 && $temp <= $pages )
				{
					$middle[] = $temp;
				}
			}

			/**
			 * Если необходимо формируется левая часть с промежутком
			 */
			if( ($min = min($middle)) > 1 )
			{
				/**
				 * Первая страница
				 */
				$left = array(1);

				/**
				 * Если промежуток между минимальной страницей в средней части и началом больше порогового значения
				 */
				if( $min > $threshold+1 && $gaps != 0 )
				{
					$step = ( ($temp = $min < $threshold*$gaps) ? $threshold : $min/$gaps );
					$leftGaps = ( $temp ? floor($min/$threshold) : $gaps );

					for($i=1; $i<$leftGaps; $i++)
					{	$left[] = round($i*$step);
					}
				}

				/**
				 * Обозначение промежутка (e.g. многоточие)
				 */
				if( $min > 2 )
				{	$left[] = true;
				}
			}

			/**
			 * Если необходимо формируется правая часть с промежутком
			 */
			if( ($max = max($middle)) < $pages )
			{
				/**
				 * Обозначение промежутка (e.g. многоточие)
				 */
				if( $max < $pages-1 )
				{	$right = array(true);
				}

				/**
				 * Если промежуток между максимальной страницей в средней части и концом больше порогового значения
				 */
				if( ($pages - $max) > $threshold+1 && $gaps != 0 )
				{
					$step = ( ($temp = ($pages - $max) < $threshold*$gaps) ? $threshold : ($pages - $max)/$gaps );
					$rightGaps = ( $temp ? floor(($pages - $max)/$threshold) : $gaps );

					for($i=$rightGaps-1; $i>0; $i--)
					{	$right[] = round($pages - $i*$step);
					}
				}

				/**
				 * Последняя страница
				 */
				$right[] = $pages;
			}

			$result = array_merge($left, $middle, $right);
		}

		return $result;
	}
}