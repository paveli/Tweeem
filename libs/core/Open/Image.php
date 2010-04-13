<?php
/**
 * Работа с картинками - класс Open_Image
 * @package OpenStruct
 */

/**
 * Подключаем необходимые файлы
 */
require_once CORE_PATH .'Open/Color'. EXT;
require_once CORE_PATH .'Open/Text'. EXT;

/**
 * Преобразования данных
 */
class Open_Image
{
	/*************
	 * Константы *
	 *************/

	/************
	 * Свойства *
	 ************/

	/**
	 * Ссылка на ресурс картинки полученный с помощью GD
	 *
	 * @var resource
	 */
	private $resource = FALSE;

	/**********
	 * Методы *
	 **********/

	/**
	 * Конструктор
	 *
	 * @param mixed $value Ресурс открытой картинки, строка-путь к картинке или с данными, число ширина картинки
	 * @param int $height Если первое значение число, то оно является шириной новой картинки, а это высота. Если высота не передана, то будет создана квадратная картинка
	 */
	function __construct($value, $height=NULL)
	{
		/**
		 * Если ресурс сохраняем
		 */
		if( is_resource($value) )
		{
			$this->resource = $value;
		}
		/**
		 * Если строка путь к файлу, то пытаемся открыть
		 * Иначе предполагаем, что строка представляет собой данные картинки
		 */
		else if( is_string($value) )
		{
			if( file_exists($value) )
			{	switch(pathinfo($value, PATHINFO_EXTENSION))
				{
					case 'png':
						$this->resource = imagecreatefrompng($value);
						break;

					case 'jpg': case 'jpeg':
						$this->resource = imagecreatefromjpeg($value);
						break;

					case 'gif':
						$this->resource = imagecreatefromgif($value);
						break;
				}
			}
			else
			{
				$this->resource = imagecreatefromstring($value);
			}
		}
		/**
		 * Если число, то пытаемся создать новое изображение
		 */
		else if( is_int($value) )
		{
			$width = $value;
			$height = ( isset($height) ? (int)$height : $width );

			$this->resource = imagecreatetruecolor($width, $height);
		}

		/**
		 * Выдаём ошибку
		 */
		if( $this->resource === FALSE )
		{
			triggerError(sprintf(Open_Text::getInstance()->dget('errors', 'The value "<b>%s</b>" cannot be converted to image'), $value), E_USER_WARNING);
		}

		/**
		 * Преобразование к truecolor
		 */
		$this->convertToTruecolor();
	}

	/**
	 * Деструктор
	 */
	function __destruct()
	{
		imagedestroy($this->resource);
	}

	/**
	 * Переопределяем метод клонирования объекта
	 */
	public function __clone()
	{
		/**
		 * Размеры картинки
		 */
		$w = imagesx($this->resource);
		$h = imagesy($this->resource);

		/**
		 * Новое изображение
		 */
		$clone = imagecreatetruecolor($w, $h);

		/**
		 * Копируем целиком старое
		 */
		imagecopy($clone, $this->resource, 0, 0, 0, 0, $w, $h);

		/**
		 * Cохраняем на его место новое изображение
		 */
		$this->resource = $clone;
	}

	/**
	 * Преобразовать текущее изображение в truecolor если оно таковым не является
	 *
	 * @return void
	 */
	private function convertToTruecolor()
	{
		/**
		 * Если изображение не является truecolor создаём новое и копируем туда старое, чтобы сделать его truecolor
		 * Старое удаляем и заменяем новым
		 */
		if( !imageistruecolor($this->resource) )
		{
			/**
			 * Размеры картинки
			 */
			$w = imagesx($this->resource);
			$h = imagesy($this->resource);

			/**
			 * Новое изображение
			 */
			$truecolorImage = imagecreatetruecolor($w, $h);

			/**
			 * Установка прозрачности на изображении
			 */
			$black = imagecolorallocate($this->resource, 0x00, 0x00, 0x00);
			imagecolortransparent($this->resource, $black);

			/**
			 * Копируем целиком старое
			 */
			imagecopy($truecolorImage, $this->resource, 0, 0, 0, 0, $w, $h);

			/**
			 * Удаляем старое и сохраняем на его место новое изображение
			 */
			imagedestroy($this->resource);
			$this->resource = $truecolorImage;
		}
	}

	/**
	 * Инвертировать изображение
	 *
	 * @return object $this
	 */
	public function invert()
	{
		$width = imagesx($this->resource);
		$height = imagesy($this->resource);

		for($x=0; $x<$width; $x++)
		{
			for($y=0; $y<$height; $y++)
			{
				$color = ~imagecolorat($this->resource, $x, $y) & 0xFFFFFF;
				imagesetpixel($this->resource, $x, $y, $color);
			}
		}

		return $this;
	}

	/**
	 * Сделать изображение чёрно-белым
	 *
	 * @return object $this
	 */
	public function grayscale()
	{
		$width = imagesx($this->resource);
		$height = imagesy($this->resource);

		for($x=0; $x<$width; $x++)
		{
			for($y=0; $y<$height; $y++)
			{
				$color = imagecolorat($this->resource, $x, $y);
				$color = round(0.3*(($color >> 16) & 0xFF) + 0.59*(($color >> 8) & 0xFF) + 0.11*($color & 0xFF));
				imagesetpixel($this->resource, $x, $y, $color << 16 | $color << 8 | $color);
			}
		}

		return $this;
	}

	/**
	 * Постеризовать изображение
	 *
	 * @param int $n Количество оттенков от 2 до 255
	 * @return object $this
	 */
	public function posterize($n)
	{
		$width = imagesx($this->resource);
		$height = imagesy($this->resource);

		$n = ($n < 0x02 ? 0x02 : $n);
		$n = ($n > 0xFF ? 0xFF : $n);

		$range = 0x100/$n;
		$colorSpan = 0xFF/($n-1);

		for($x=0; $x<$width; $x++)
		{
			for($y=0; $y<$height; $y++)
			{
				$color = imagecolorat($this->resource, $x, $y);
				$R = round( floor((($color >> 16) & 0xFF)/$range) * $colorSpan );
				$G = round( floor((($color >> 8) & 0xFF)/$range) * $colorSpan );
				$B = round( floor(($color & 0xFF)/$range) * $colorSpan );
				imagesetpixel($this->resource, $x, $y, $R << 16 | $G << 8 | $B);
			}
		}

		return $this;
	}

	/**
	 * Конверт в символы попиксельно
	 *
	 * @param string $chars Набор используемых символов от чёрного к белому цвету
	 * @return array
	 */
	public function toAsciiart($doFormat=TRUE, $isColored=FALSE, $chars='#M@HX$%+/;:=-,. ')
	{
		/**
		 * Размеры
		 */
		$width = imagesx($this->resource);
		$height = imagesy($this->resource);

		/**
		 * Количество символов и число постеризации
		 */
		$length = mb_strlen($chars);

		$range = 0x100/$length;
		$colorSpan = 0xFF/($length-1);

		$result = array();
		if( $isColored )
		{
			for($y=0; $y<$height; $y++)
			{
				$result[$y] = '';
				for($x=0; $x<$width; $x++)
				{
					$color = imagecolorat($this->resource, $x, $y);
					$gray = round(0.3*(($color >> 16) & 0xFF) + 0.59*(($color >> 8) & 0xFF) + 0.11*($color & 0xFF));
					$R = round( floor((($color >> 16) & 0xFF)/$range) * $colorSpan );
					$G = round( floor((($color >> 8) & 0xFF)/$range) * $colorSpan );
					$B = round( floor(($color & 0xFF)/$range) * $colorSpan );
					$result[$y][] = array(mb_substr($chars, floor(($color & 0xFF)/$range), 1), $R << 16 | $G << 8 | $B);
				}
			}
		}
		else
		{
			for($y=0; $y<$height; $y++)
			{
				$result[$y] = '';
				for($x=0; $x<$width; $x++)
				{
					$color = imagecolorat($this->resource, $x, $y);
					$gray = round(0.3*(($color >> 16) & 0xFF) + 0.59*(($color >> 8) & 0xFF) + 0.11*($color & 0xFF));
					$result[$y][] = mb_substr($chars, floor(($color & 0xFF)/$range), 1);
				}
			}
		}

		/**
		 * Форматирование
		 */
		if( $doFormat )
		{
			$format = '<div class="asciiart"><pre>';
			if( $isColored )
			{
				foreach($result as &$string)
				{
					$format .= '<span>';
					$temp = '';
					foreach($string as &$char)
					{
						$temp .= '<span style="color: '. sprintf('#%06x', $char[1]) .';">'. $char[0] .'</span>';
					}
					$format .= $temp . '</span><br/>';
				}
			}
			else
			{
				foreach($result as &$string)
				{
					$format .= '<span>';
					$temp = '';
					foreach($string as &$char)
					{
						$temp .= $char;
					}
					$format .= $temp . '</span><br/>';
				}
			}
			$format .= '</pre></div>';

			return $format;
		}

		return $result;
	}

	private function formatAsciiart($colored=FALSE, $array)
	{
		$result = '<div class="asciiart"><pre>';
		foreach($array as &$string)
		{
			$result .= '<span>'. $string .'</span><br/>';
		}
		$result .= '</pre></div>';

		return $result;
	}

	/**
	 * Получить прямую ресурс-ссылку на изображение
	 *
	 * @return resource
	 */
	public function getResource()
	{
		return $this->resource;
	}

	/**
	 * Получить ширину изображения
	 *
	 * @return int
	 */
	public function getWidth()
	{
		return imagesx($this->resource);
	}

	/**
	 * Получить высоту изображения
	 *
	 * @return int
	 */
	public function getHeight()
	{
		return imagesy($this->resource);
	}

	/**
	 * Получить размеры изображения
	 *
	 * @return int
	 */
	public function getSize()
	{
		return array(imagesx($this->resource), imagesy($this->resource));
	}

	/**
	 * Получить цвет пикселя с координатами $x, $y
	 *
	 * @param int $x
	 * @param int $y
	 * @return object
	 */
	public function getColorXY($x, $y)
	{
		return new Open_Color(imagecolorat($this->resource, $x, $y));
	}

	/**
	 * Установить цвет пикселя с координатами $x, $y
	 *
	 * @param int $x
	 * @param int $y
	 * @return object
	 */
	public function setColorXY($x, $y, Open_Color $color)
	{
		$color = $color->toArray();
		$color = imagecolorallocate($this->resource, $color[0], $color[1], $color[2]);
		imagesetpixel($this->resource, $x, $y, $color);
	}

	/**
	 * Отобразить, либо сохранить в файл изображение
	 *
	 * @param int $type Тип сохраняемой картинки
	 * @param string $filepath Путь к файлу, в который сохранить картинку, БЕЗ расширения
	 * @param int $quality Качество изображения для PNG и JPG
	 * @param int $filters Фильтры для PNG изображения
	 */
	public function output($type=IMAGETYPE_PNG, $filepath=NULL, $quality=NULL, $filters=NULL)
	{
		/**
		 * Если не передано имя файла, то выводим картинку на экран
		 */
		if( isset($filepath) )
		{
			$filepath .= image_type_to_extension($type);
		}
		else
		{
			header('Content-type: '. image_type_to_mime_type($type));
		}

		switch($type)
		{
			case IMAGETYPE_PNG:
				imagepng($this->resource, $filepath, $quality, $filters);
				break;

			case IMAGETYPE_JPEG:
				imagejpeg($this->resource, $filepath, $quality);
				break;

			case IMAGETYPE_GIF:
				imagegif($this->resource, $filepath);
				break;

			default:
				trigger_error(Open_Text::getInstance()->dget('errors', 'Wrong or unsupported image type specified for output image'), E_USER_ERROR);
				break;
		}
	}
}