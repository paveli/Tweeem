<?php
/**
 * Основной конфиг
 * В алфавитном порядке
 */

/**
 * Осуществлять XSS-очистку входных данных?
 * $_GET, $_POST, $_COOKIE массивов
 */
$data['auto_xss_clean'] = TRUE;

/**
 * Название приложения
 */
$data['application_name'] = 'Tweeem';

/**
 * Использовать C локаль для чисел вместо основной?
 */
$data['c_numeric_locale'] = TRUE;

/**
 * Кодировка используемая везде в приложении
 */
$data['charset'] = 'UTF-8';

/**
 * css-файлы, которые должны быть подключены по умолчанию на всех страницах в указанном порядке
 * БЕЗ расширения
 */
$data['css'] = array(
	'reset-fonts-base',
	'yui/container-core',
	'yui/resize-core',
	'yui/resize-skin',
	'common',
);

/**
 * Параметры для работы с БД
 */
$data['db'] = array(

	/**
	 * Подключаться к БД по профилю по умолчанию автоматически при создании объекта?
	 */
	'auto_connect' => FALSE,

	/**
	 * Профиль по умолчанию
	 */
	'default_profile' => 'tweeem',

	/**
	 * Профили
	 */
	'profiles' => array(
		/**
		 * Конфиг для соединения с БД
		 * Многомерный массив
		 * Каждый такой массив - это один профиль
		 * Можно создать несколько профилей одновременно и легко между ними переключаться используя параметр 'current_profile'
		 * В будущем будет поддержка использования нескольких профилей одновременно
		 *
		 * $config['db']['example'] = array(
		 * 		'host'				=> 'localhost', 	// Хост
		 * 		'user'				=> 'example',		// Имя пользователя
		 * 		'password'			=> 'example1234',	// Пароль
		 * 		'db'				=> 'example',		// База данных
		 * 		'pconnect'			=> FALSE,			// Использовать pconnect?
		 * );
		 */
		'tweeem' => array(
			'host'		=> 'localhost',
			'user'		=> 'tweeem',
			'password'	=> 'dfg456rd',
			'db'		=> 'tweeem',
			'pconnect'	=> FALSE,
		),
	),
);

/**
 * Сообщение 403-й ошибки по умолчанию
 * Является ID для gettext
 */
$data['default_403_message'] = 'The requested URL is forbidden';

/**
 * Сообщение 404-й ошибки по умолчанию
 * Является ID для gettext
 */
$data['default_404_message'] = 'The requested URL was not found on this server';

/**
 * Сообщение 500-й ошибки по умолчанию
 * Является ID для gettext
 */
$data['default_500_message'] = 'The requested URL could not be retrieved due to internal server error';

/**
 * Используемый по умолчанию шаблон с разметкой и телом страницы БЕЗ расширения
 */
$data['default_body'] = 'body';

/**
 * Контроллер по умолчанию
 * Необходимо, чтобы имя контроллера и вызываемого метода отличались
 * Иначе метод будет воспринят как конструктор контроллера
 * Также необходимо соблюдать за отсутствием пересечения между именами контроллеров и моделей
 */
$data['default_controller'] = 'home';

/**
 * Локаль по умолчанию
 */
$data['default_locale'] = 'ru';

/**
 * Метод по умолчанию
 */
$data['default_method'] = 'index';

/**
 * Title по умолчанию
 */
$data['default_title'] = $data['application_name'];

/**
 * Заголовки
 * Будут отправлены браузеру перед отображением страниц
 */
$data['headers'] = array(
	'Content-Type: text/html; charset='. $data['charset'],
);

/**
 * js-скрипты, которые должны быть подключены по умолчанию на всех страницах в указанном порядке
 * БЕЗ расширения
 */
$data['js'] = array(
	'yui/utilities',
	'yui/container-min',
	'yui/resize-beta-min',
	'tweeem',
);

/**
 * Список доступных локалей
 */
$data['locales'] = array(
	'ru' => array('ru_RU.'. $data['charset'], 'ru_RU', 'RUS'),
	'en' => array('en_US.'. $data['charset'], 'en_US', 'ENG'),
);

/**
 * Допустимые в URI символы
 */
$data['permitted_uri_chars'] = 'a-zA-Z/ 0-9~%.:_\+-';

/**
 * Регулярные выражения маршрутизации
 * !!! Маршруты применяются БЕЗ учёта локали
 * Применяются в порядке следования без учёта регистра
 * Не надо указывать границы регулярного выражения
 * Необходимо учитывать слеши в начале и конце маршрута
 *
 * Пример
 * На входе: http://example.com/qwer/asdf/zxcv/
 * При маршрутизации участвует только: /qwer/asdf/zxcv/
 * Пропускаем через такое преобразование: '^/qwer/([a-z0-9]+)' => '/qwer/helloworld/$1'
 * На выходе: http://example.com/qwer/helloworld/asdf/zxcv/
 */
$data['routes'] = array(
	'^/(login|logout)/' => '/home/$1/',
	//'^/ajax/' => '/ajax/index/',
	//'^/admin/example' => '/admin_example'
	//'^/example/(\d+)?' => '/example/index/$1',
	//'^/example(/index)?' => '/example/index',
	//'^/$' => '/example',
	//'^/home' => '/example',
	//'^/qwer/([a-z0-9]+)' => '/qwer/helloworld/$1',
);

/**
 * Настройки работы с текстом
 */
$data['text'] = array(

	/**
	 * Домен используемый по умолчанию
	 */
	'default_domain' => 'messages',

	/**
	 * Настройки локалей
	 * plural - функция для вычисления формы множественного числа
	 */
	'locales' => array(
		'ru' => array(
			'plural' => create_function('$n', '$n=abs($n); return (($n%10==1 && $n%100!=11) ? 0 : ($n%10>=2 && $n%10<=4 && ($n%100<10 || $n%100>=20)) ? 1 : 2);'),
		),
		'en' => array(
			'plural' => create_function('$n', '$n=abs($n); return (($n<=1) ? 0 : 1);'),
		),
	),
);

return $data;