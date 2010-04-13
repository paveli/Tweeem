<?php
/**
 * Подключаем необходимые файлы
 */
require_once CORE_PATH .'Open/Model'. EXT;

/**
 * Модель Twitter
 */
class TwitterModel extends Open_Model
{
	/*************
	 * Константы *
	 *************/

	/**
	 * Перечисление возможных форматов данных
	 */
	const FORMAT_NA		= 0x00;
	const FORMAT_TEXT	= 0x01;
	const FORMAT_XML	= 0x02;
	const FORMAT_JSON	= 0x04;
	const FORMAT_RSS	= 0x08;
	const FORMAT_ATOM	= 0x10;
	const FORMAT_NONE	= 0x20;

	/**
	 * Формат по умолчанию
	 */
	const DEFAULT_FORMAT = self::FORMAT_JSON;

	/**
	 * Префикс URL
	 */
	const URL_PREFIX = 'http://twitter.com/';

	/**
	 * Формат даты для подстановки в вызов функции date()
	 */
	const DATE_FORMAT = 'D M d H:i:s O Y';
	
	/**
	 * Максимальная длина сообщения
	 */
	const MESSAGE_LENGTH_LIMIT = 160;

	/************
	 * Свойства *
	 ************/

	/**
	 * Рабочий формат
	 *
	 * @var int
	 */
	private $format;

	/**
	 * Логин текущего пользователя
	 *
	 * @var string
	 */
	private $login = NULL;

	/**
	 * Пароль пользователя
	 *
	 * @var string
	 */
	private $password = NULL;

	/**
	 * Идентификатор последнего используемого CURL запроса
	 *
	 * @var resource
	 */
	private $handle = NULL;

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

		$this->format(self::DEFAULT_FORMAT);
	}

	/**
	 * Деструктор
	 */
	function __destruct()
	{
		/**
		 * Закрываем идентификатор
		 */
		if( isset($this->handle) )
		{	curl_close($this->handle);
		}

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
	 * Получить, установить формат данных
	 *
	 * @param int $format
	 * @return int
	 */
	public function format($format=NULL)
	{
		if( isset($format) )
		{
			$this->format = $format;
		}

		return $this->format;
	}

	/**
	 * Получить, установить логин
	 *
	 * @param string $login
	 * @return string
	 */
	public function login($login=NULL)
	{
		if( isset($login) )
		{
			$this->login = $login;
		}

		return $this->login;
	}

	/**
	 * Получить, установить пароль
	 *
	 * @param string $password
	 * @return string
	 */
	public function password($password=NULL)
	{
		if( isset($password) )
		{
			$this->password = $password;
		}

		return $this->password;
	}

	/**
	 * Получить строковое значение формата
	 *
	 * @param int $format
	 * @return string
	 */
	private function formatToString($format=NULL)
	{
		static $strings = array(
			self::FORMAT_NA		=> '',
			self::FORMAT_TEXT	=> '.text',
			self::FORMAT_XML	=> '.xml',
			self::FORMAT_JSON	=> '.json',
			self::FORMAT_RSS	=> '.rss',
			self::FORMAT_ATOM	=> '.atom',
			self::FORMAT_NONE	=> '.none',
		);

		$format = ( isset($format) ? $format : $this->format() );

		return ( isset($strings[$format]) ? $strings[$format] : self::FORMAT_NA );
	}

	/**
	 * Проверка поддерживается ли заданый формат методом
	 *
	 * @param int $supportedFormats Перечисленные через битовое ИЛИ поддерживаемые форматы
	 * @param int $format Константа формата
	 * @return bool
	 */
	private function checkSupportedFormat($supportedFormats, $format)
	{
		if( ($supportedFormats & $format) !== $format )
		{
			trigger_error(sprintf(Open_Text::getInstance()->dget('errors', 'Format <b>%s</b> is not supported'), $format), E_USER_ERROR);
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * Отправка запроса
	 *
	 * @param string $url
	 * @param string $user
	 * @param string $password
	 * @return mixed
	 */
	private function query($url, $postdata=NULL)
	{
		/**
		 * Получение идентификатора для запроса
		 */
		$this->handle = curl_init($url);

		/**
		 * Вернуть результат, а не отобразить
		 */
		curl_setopt($this->handle, CURLOPT_RETURNTRANSFER, TRUE);
		//curl_setopt($this->handle, CURLOPT_HEADER, TRUE);

		/**
		 * Имя пользователя и пароль
		 */
		if( isset($this->login) && isset($this->password) )
		{
			curl_setopt($this->handle, CURLOPT_USERPWD, $this->login .':'. $this->password);
		}

		/**
		 * Если необходимо POST запрос
		 */
		if( isset($postdata) )
		{
			curl_setopt($this->handle, CURLOPT_POST, TRUE);
			curl_setopt($this->handle, CURLOPT_POSTFIELDS, $postdata);
		}

		/**
		 * Выполнение запроса
		 */
		$result = curl_exec($this->handle);

		return $result;
	}

	/**
	 * Получить http-код последней операции
	 *
	 * @return mixed
	 */
	public function getCode()
	{
		if( isset($this->handle) )
		{
			return curl_getinfo($this->handle, CURLINFO_HTTP_CODE);
		}

		return FALSE;
	}
	
	/**
	 * Если код последней операции более либо равен 300 это считается ошибкой и возвращается TRUE
	 *
	 * @return bool
	 */
	public function isError()
	{
		return ($this->getCode() >= 300);
	}

	/**
	 * Returns the 20 most recent statuses from non-protected users who have set a custom user icon. Does not require authentication.
	 *
	 * @param int $sinceId Returns only public statuses with an ID greater than (that is, more recent than) the specified ID
	 * @return mixed
	 */
	public function statusPublicTimeline($sinceId=NULL)
	{
		if( !$this->checkSupportedFormat(self::FORMAT_XML | self::FORMAT_JSON | self::FORMAT_RSS | self::FORMAT_ATOM, $this->format()) )
		{
			return FALSE;
		}

		$url = sprintf(self::URL_PREFIX .'statuses/public_timeline%s', $this->formatToString());

		$temp = http_build_query(array('since_id' => $sinceId));
		$url .= ( empty($temp) ? '' : '?'. $temp );

		return $this->query($url);
	}

	/**
	 * Returns the 20 most recent statuses posted in the last 24 hours from the authenticating user and that user's friends.  It's also possible to request another user's friends_timeline via the id parameter below.
	 *
	 * @param int $id Specifies the ID or screen name of the user for whom to return the friends_timeline.
	 * @param string $since Narrows the returned results to just those statuses created after the specified HTTP-formatted date.  The same behavior is available by setting an If-Modified-Since header in your HTTP request.
	 * @param int $page Gets the 20 next most recent statuses from the authenticating user and that user's friends. TEMPORARILY DISABLED
	 * @return mixed
	 */
	public function statusFriendsTimeline($id=NULL, $since=NULL, $page=NULL)
	{
		if( !$this->checkSupportedFormat(self::FORMAT_XML | self::FORMAT_JSON | self::FORMAT_RSS | self::FORMAT_ATOM, $this->format()) )
		{
			return FALSE;
		}

		if( isset($id) )
		{
			$url = sprintf(self::URL_PREFIX .'statuses/friends_timeline/%s%s', $id, $this->formatToString());
		}
		else
		{
			$url = sprintf(self::URL_PREFIX .'statuses/friends_timeline%s', $this->formatToString());
		}
		
		if( isset($since) )
		{
			$since = date(self::DATE_FORMAT, $since);
		}

		$temp = http_build_query(array('since' => $since, 'page' => $page));
		$url .= ( empty($temp) ? '' : '?'. $temp );

		return $this->query($url);
	}

	/**
	 * Returns the 20 most recent statuses posted in the last 24 hours from the authenticating user.  It's also possible to request another user's timeline via the id parameter below.
	 *
	 * @param int $id Specifies the ID or screen name of the user for whom to return the friends_timeline.
	 * @param int $count Specifies the number of statuses to retrieve.  May not be greater than 20 for performance purposes.
	 * @param string $since Narrows the returned results to just those statuses created after the specified HTTP-formatted date.  The same behavior is available by setting an If-Modified-Since header in your HTTP request.
	 * @param int $sinceId Returns only statuses with an ID greater than (that is, more recent than) the specified ID.
	 * @return mixed
	 */
	public function statusUserTimeline($id=NULL, $count=NULL, $since=NULL, $sinceId=NULL)
	{
		if( !$this->checkSupportedFormat(self::FORMAT_XML | self::FORMAT_JSON | self::FORMAT_RSS | self::FORMAT_ATOM, $this->format()) )
		{
			return FALSE;
		}

		if( isset($since) )
		{
			$since = date(self::DATE_FORMAT, $since);
		}

		$url = sprintf(self::URL_PREFIX .'statuses/user_timeline%s', $this->formatToString());

		$temp = http_build_query(array('id' => $id, 'count' => $count, 'since' => $since, 'since_id' => $sinceId));
		$url .= ( empty($temp) ? '' : '?'. $temp );

		return $this->query($url);
	}

	/**
	 * Returns a single status, specified by the id parameter below.  The status's author will be returned inline.
	 *
	 * @param int $id The numerical ID of the status you're trying to retrieve.
	 * @return mixed
	 */
	public function statusShow($id)
	{
		if( !$this->checkSupportedFormat(self::FORMAT_XML | self::FORMAT_JSON, $this->format()) )
		{
			return FALSE;
		}

		$url = sprintf(self::URL_PREFIX .'statuses/show/%s%s', $id, $this->formatToString());

		return $this->query($url);
	}

	/**
	 * Updates the authenticating user's status. Requires the status parameter specified below. Request must be a POST.
	 *
	 * @param string $status The text of your status update. Be sure to URL encode as necessary. Must not be more than 160 characters and should not be more than 140 characters to ensure optimal display.
	 * @return mixed
	 */
	public function statusUpdate($status)
	{
		if( !$this->checkSupportedFormat(self::FORMAT_XML | self::FORMAT_JSON, $this->format()) )
		{
			return FALSE;
		}
		
		$status = mb_substr($status, 0, self::MESSAGE_LENGTH_LIMIT);

		$url = sprintf(self::URL_PREFIX .'statuses/update%s', $this->formatToString());

		$postdata = http_build_query(array('status' => $status));

		return $this->query($url, $postdata);
	}

	/**
	 * Returns the 20 most recent replies (status updates prefixed with @username posted by users who are friends with the user being replied to) to the authenticating user.
	 * Replies are only available to the authenticating user; you can not request a list of replies to another user whether public or protected.
	 *
	 * @param int $page Retrieves the 20 next most recent replies.
	 * @param string $since Narrows the returned results to just those replies created after the specified HTTP-formatted date. The same behavior is available by setting an If-Modified-Since header in your HTTP request.
	 * @param int $sinceId Returns only statuses with an ID greater than (that is, more recent than) the specified ID.
	 * @return mixed
	 */
	public function statusReplies($page=NULL, $since=NULL, $sinceId=NULL)
	{
		if( !$this->checkSupportedFormat(self::FORMAT_XML | self::FORMAT_JSON | self::FORMAT_RSS | self::FORMAT_ATOM, $this->format()) )
		{
			return FALSE;
		}

		if( isset($since) )
		{
			$since = date(self::DATE_FORMAT, $since);
		}

		$url = sprintf(self::URL_PREFIX .'statuses/replies%s', $this->formatToString());

		$temp = http_build_query(array('page' => $page, 'since' => $since, 'since_id' => $sinceId));
		$url .= ( empty($temp) ? '' : '?'. $temp );

		return $this->query($url);
	}

	/**
	 * Destroys the status specified by the required ID parameter. The authenticating user must be the author of the specified status.
	 *
	 * @param int $id The ID of the status to destroy.
	 * @return mixed
	 */
	public function statusDestroy($id)
	{
		if( !$this->checkSupportedFormat(self::FORMAT_XML | self::FORMAT_JSON, $this->format()) )
		{
			return FALSE;
		}

		$url = sprintf(self::URL_PREFIX .'statuses/destroy/%s%s', $id, $this->formatToString());

		return $this->query($url);
	}

	/**
	 * Returns up to 100 of the authenticating user's friends who have most recently updated, each with current status inline. It's also possible to request another user's recent friends list via the id parameter below.
	 *
	 * @param int $id The ID or screen name of the user for whom to request a list of friends.
	 * @param int $page Retrieves the next 100 friends.
	 * @param bool $lite Prevents the inline inclusion of current status.  Must be set to a value of true.
	 * @param string $since Narrows the returned results to just those friendships created after the specified HTTP-formatted date.  The same behavior is available by setting an If-Modified-Since header in your HTTP request.
	 * @return mixed
	 */
	public function userFriends($id=NULL, $page=NULL, $lite=NULL, $since=NULL)
	{
		if( !$this->checkSupportedFormat(self::FORMAT_XML | self::FORMAT_JSON, $this->format()) )
		{
			return FALSE;
		}

		if( isset($id) )
		{
			$url = sprintf(self::URL_PREFIX .'statuses/friends/%s%s', $id, $this->formatToString());
		}
		else
		{
			$url = sprintf(self::URL_PREFIX .'statuses/friends%s', $this->formatToString());
		}
		
		if( isset($since) )
		{
			$since = date(self::DATE_FORMAT, $since);
		}

		$temp = http_build_query(array('page' => $page, 'lite' => $lite, 'since' => $since));
		$url .= ( empty($temp) ? '' : '?'. $temp );

		return $this->query($url);
	}

	/**
	 * Returns the authenticating user's followers, each with current status inline.
	 *
	 * @param int $page Retrieves the next 100 followers.
	 * @param bool $lite Prevents the inline inclusion of current status.  Must be set to a value of true.
	 * @return mixed
	 */
	public function userFollowers($page=NULL, $lite=NULL)
	{
		if( !$this->checkSupportedFormat(self::FORMAT_XML | self::FORMAT_JSON, $this->format()) )
		{
			return FALSE;
		}

		$url = sprintf(self::URL_PREFIX .'statuses/followers%s', $this->formatToString());

		$temp = http_build_query(array('page' => $page, 'lite' => $lite));
		$url .= ( empty($temp) ? '' : '?'. $temp );

		return $this->query($url);
	}

	/**
	 * Returns a list of the users currently featured on the site with their current statuses inline.
	 *
	 * @return mixed
	 */
	public function userFeatured()
	{
		if( !$this->checkSupportedFormat(self::FORMAT_XML | self::FORMAT_JSON, $this->format()) )
		{
			return FALSE;
		}

		$url = sprintf(self::URL_PREFIX .'statuses/featured%s', $this->formatToString());

		return $this->query($url);
	}

	/**
	 * Returns extended information of a given user, specified by ID or screen name as per the required id parameter below.
	 * This information includes design settings, so third party developers can theme their widgets according to a given user's preferences.
	 *
	 * @param int $id The ID or screen name of a user.
	 * @param string $email The email address of a user.
	 * @return mixed
	 */
	public function userShow($id=NULL, $email=NULL)
	{
		if( !$this->checkSupportedFormat(self::FORMAT_XML | self::FORMAT_JSON, $this->format()) )
		{
			return FALSE;
		}

		if( isset($id) )
		{
			$url = sprintf(self::URL_PREFIX .'users/show/%s%s', $id, $this->formatToString());
		}
		else if( isset($email) )
		{
			$url = sprintf(self::URL_PREFIX .'users/show%s', $this->formatToString());

			$temp = http_build_query(array('email' => $email));
			$url .= ( empty($temp) ? '' : '?'. $temp );
		}
		else
		{
			return FALSE;
		}

		return $this->query($url);
	}

	/**
	 * Returns a list of the 20 most recent direct messages sent TO the authenticating user.
	 * The XML and JSON versions include detailed information about the sending and recipient users.
	 *
	 * @param string $since Narrows the resulting list of direct messages to just those sent after the specified HTTP-formatted date.  The same behavior is available by setting the If-Modified-Since parameter in your HTTP request.
	 * @param int $sinceId Returns only direct messages with an ID greater than (that is, more recent than) the specified ID.
	 * @param int $page Retrieves the 20 next most recent direct messages.
	 * @return mixed
	 */
	public function directMessages($since=NULL, $sinceId=NULL, $page=NULL)
	{
		if( !$this->checkSupportedFormat(self::FORMAT_XML | self::FORMAT_JSON | self::FORMAT_RSS | self::FORMAT_ATOM, $this->format()) )
		{
			return FALSE;
		}

		if( isset($since) )
		{
			$since = date(self::DATE_FORMAT, $since);
		}

		$url = sprintf(self::URL_PREFIX .'direct_messages%s', $this->formatToString());

		$temp = http_build_query(array('since' => $since, 'sinceId' => $sinceId, 'page' => $page));
		$url .= ( empty($temp) ? '' : '?'. $temp );

		return $this->query($url);
	}

	/**
	 * Returns a list of the 20 most recent direct messages sent BY the authenticating user.
	 * The XML and JSON versions include detailed information about the sending and recipient users.
	 *
	 * @param string $since Narrows the resulting list of direct messages to just those sent after the specified HTTP-formatted date.  The same behavior is available by setting the If-Modified-Since parameter in your HTTP request.
	 * @param int $sinceId Returns only sent direct messages with an ID greater than (that is, more recent than) the specified ID.
	 * @param int $page Retrieves the 20 next most recent direct messages sent.
	 * @return mixed
	 */
	public function directSent($since=NULL, $sinceId=NULL, $page=NULL)
	{
		if( !$this->checkSupportedFormat(self::FORMAT_XML | self::FORMAT_JSON, $this->format()) )
		{
			return FALSE;
		}

		if( isset($since) )
		{
			$since = date(self::DATE_FORMAT, $since);
		}

		$url = sprintf(self::URL_PREFIX .'direct_messages/sent%s', $this->formatToString());

		$temp = http_build_query(array('since' => $since, 'sinceId' => $sinceId, 'page' => $page));
		$url .= ( empty($temp) ? '' : '?'. $temp );

		return $this->query($url);
	}

	/**
	 * Sends a new direct message to the specified user from the authenticating user. Requires both the user and text parameters below. Request must be a POST.
	 * Returns the sent message in the requested format when successful.
	 *
	 * @param mixed $user The ID or screen name of the recipient user.
	 * @param string $text The text of your direct message. Be sure to URL encode as necessary, and keep it under 140 characters.
	 * @return mixed
	 */
	public function directNew($user, $text)
	{
		if( !$this->checkSupportedFormat(self::FORMAT_XML | self::FORMAT_JSON, $this->format()) )
		{
			return FALSE;
		}
		
		$url = sprintf(self::URL_PREFIX .'direct_messages/new%s', $this->formatToString());
		
		$text = mb_substr($text, 0, self::MESSAGE_LENGTH_LIMIT);

		$postdata = http_build_query(array('user' => $user, 'text' => $text));

		return $this->query($url, $postdata);
	}

	/**
	 * Destroys the direct message specified in the required ID parameter.
	 * The authenticating user must be the recipient of the specified direct message.
	 *
	 * @param int $id The ID of the direct message to destroy.
	 * @return mixed
	 */
	public function directDestroy($id)
	{
		if( !$this->checkSupportedFormat(self::FORMAT_XML | self::FORMAT_JSON, $this->format()) )
		{
			return FALSE;
		}

		$url = sprintf(self::URL_PREFIX .'direct_messages/destroy/%s%s', $id, $this->formatToString());

		return $this->query($url);
	}

	/**
	 * Befriends the user specified in the ID parameter as the authenticating user.
	 * Returns the befriended user in the requested format when successful.
	 * Returns a string describing the failure condition when unsuccessful.
	 *
	 * @param mixed $id The ID or screen name of the user to befriend.
	 * @return mixed
	 */
	public function friendshipCreate($id)
	{
		if( !$this->checkSupportedFormat(self::FORMAT_XML | self::FORMAT_JSON, $this->format()) )
		{
			return FALSE;
		}

		$url = sprintf(self::URL_PREFIX .'friendships/create/%s%s', $id, $this->formatToString());

		return $this->query($url);
	}

	/**
	 * Discontinues friendship with the user specified in the ID parameter as the authenticating user.
	 * Returns the un-friended user in the requested format when successful.
	 * Returns a string describing the failure condition when unsuccessful.
	 *
	 * @param mixed $id The ID or screen name of the user with whom to discontinue friendship.
	 * @return mixed
	 */
	public function friendshipDestroy($id)
	{
		if( !$this->checkSupportedFormat(self::FORMAT_XML | self::FORMAT_JSON, $this->format()) )
		{
			return FALSE;
		}

		$url = sprintf(self::URL_PREFIX .'friendships/destroy/%s%s', $id, $this->formatToString());

		return $this->query($url);
	}

	/**
	 * Tests if friendship exists between the two users specified in the parameter specified below.
	 *
	 * @param mixed $userA The ID or screen_name of the first user to test friendship for.
	 * @param mixed $userB The ID or screen_name of the second user to test friendship for.
	 * @return mixed
	 */
	public function friendshipExists($userA, $userB)
	{
		if( !$this->checkSupportedFormat(self::FORMAT_XML | self::FORMAT_JSON | self::FORMAT_NONE, $this->format()) )
		{
			return FALSE;
		}

		$url = sprintf(self::URL_PREFIX .'friendships/exists%s', $this->formatToString());

		$temp = http_build_query(array('user_a' => $userA, 'user_b' => $userB));
		$url .= ( empty($temp) ? '' : '?'. $temp );

		return $this->query($url);
	}

	/**
	 * Returns an HTTP 200 OK response code and a format-specific response if authentication was successful.
	 * Use this method to test if supplied user credentials are valid with minimal overhead.
	 *
	 * @return mixed
	 */
	public function accountVerifyCredentials()
	{
		if( !$this->checkSupportedFormat(self::FORMAT_TEXT | self::FORMAT_XML | self::FORMAT_JSON, $this->format()) )
		{
			return FALSE;
		}

		$url = sprintf(self::URL_PREFIX .'account/verify_credentials%s', $this->formatToString());

		return $this->query($url);
	}

	/**
	 * Ends the session of the authenticating user, returning a null cookie.
	 * Use this method to sign users out of client-facing applications like widgets.
	 *
	 * @return mixed
	 */
	public function accountEndSession()
	{
		$url = self::URL_PREFIX .'account/end_session';

		return $this->query($url);
	}

	/**
	 * Returns 80 statuses per page for the authenticating user, ordered by descending date of posting.
	 * Use this method to rapidly export your archive of statuses.
	 *
	 * @param int $page Retrieves the 80 next most recent statuses.
	 * @param string $since Narrows the resulting list of statuses to just those sent after the specified HTTP-formatted date. The same behavior is available by setting the If-Modified-Since parameter in your HTTP request.
	 * @param int $sinceId Returns only statuses with an ID greater than (that is, more recent than) the specified ID.
	 * @return mixed
	 */
	public function accountArchive($page=NULL, $since=NULL, $sinceId=NULL)
	{
		if( !$this->checkSupportedFormat(self::FORMAT_XML | self::FORMAT_JSON, $this->format()) )
		{
			return FALSE;
		}

		if( isset($since) )
		{
			$since = date(self::DATE_FORMAT, $since);
		}

		$url = sprintf(self::URL_PREFIX .'account/archive%s', $this->formatToString());

		$temp = http_build_query(array('page' => $page, 'since' => $since, 'sinceId' => $sinceId));
		$url .= ( empty($temp) ? '' : '?'. $temp );

		return $this->query($url);
	}

	/**
	 * Updates the location attribute of the authenticating user, as displayed on the side of their profile and returned in various API methods.
	 * Works as either a POST or a GET.
	 *
	 * @param string $location The location of the user. Please note this is not normalized, geocoded, or translated to latitude/longitude at this time.
	 * @return mixed
	 */
	public function accountUpdateLocation($location)
	{
		if( !$this->checkSupportedFormat(self::FORMAT_XML | self::FORMAT_JSON, $this->format()) )
		{
			return FALSE;
		}

		$url = sprintf(self::URL_PREFIX .'account/update_location%s', $this->formatToString());

		$temp = http_build_query(array('location' => $location));
		$url .= ( empty($temp) ? '' : '?'. $temp );

		return $this->query($url);
	}

	/**
	 * Sets which device Twitter delivers updates to for the authenticating user.  Sending none as the device parameter will disable IM or SMS updates.
	 *
	 * @param string $device Must be one of: sms, im, none.
	 * @return mixed
	 */
	public function accountUpdateDeliveryDevice($device)
	{
		if( !$this->checkSupportedFormat(self::FORMAT_XML | self::FORMAT_JSON, $this->format()) )
		{
			return FALSE;
		}

		$url = sprintf(self::URL_PREFIX .'account/update_delivery_device%s', $this->formatToString());

		$temp = http_build_query(array('device' => $device));
		$url .= ( empty($temp) ? '' : '?'. $temp );

		return $this->query($url);
	}

	/**
	 * Returns the 20 most recent favorite statuses for the authenticating user or user specified by the ID parameter in the requested format.
	 *
	 * @param int $id The ID or screen name of the user for whom to request a list of favorite statuses.
	 * @param int $page Retrieves the 20 next most recent favorite statuses.
	 * @return mixed
	 */
	public function favorites($id=NULL, $page=NULL)
	{
		if( !$this->checkSupportedFormat(self::FORMAT_XML | self::FORMAT_JSON | self::FORMAT_RSS | self::FORMAT_ATOM, $this->format()) )
		{
			return FALSE;
		}

		$url = sprintf(self::URL_PREFIX .'favorites%s', $this->formatToString());

		$temp = http_build_query(array('id' => $id, 'page' => $page));
		$url .= ( empty($temp) ? '' : '?'. $temp );

		return $this->query($url);
	}

	/**
	 * Favorites the status specified in the ID parameter as the authenticating user.
	 * Returns the favorite status when successful.
	 *
	 * @param int $id The ID of the status to favorite.
	 * @return mixed
	 */
	public function favoriteCreate($id)
	{
		if( !$this->checkSupportedFormat(self::FORMAT_XML | self::FORMAT_JSON, $this->format()) )
		{
			return FALSE;
		}

		$url = sprintf(self::URL_PREFIX .'favorites/create/%s%s', $id, $this->formatToString());

		return $this->query($url);
	}

	/**
	 * Un-favorites the status specified in the ID parameter as the authenticating user.
	 * Returns the un-favorited status in the requested format when successful.
	 *
	 * @param int $id The ID of the status to un-favorite.
	 * @return mixed
	 */
	public function favoriteDestroy($id)
	{
		if( !$this->checkSupportedFormat(self::FORMAT_XML | self::FORMAT_JSON, $this->format()) )
		{
			return FALSE;
		}

		$url = sprintf(self::URL_PREFIX .'favorites/destroy/%s%s', $id, $this->formatToString());

		return $this->query($url);
	}

	/**
	 * Enables notifications for updates from the specified user to the authenticating user.
	 * Returns the specified user when successful.
	 *
	 * @param int $id The ID or screen name of the user to follow.
	 * @return mixed
	 */
	public function notificationFollow($id)
	{
		if( !$this->checkSupportedFormat(self::FORMAT_XML | self::FORMAT_JSON, $this->format()) )
		{
			return FALSE;
		}

		$url = sprintf(self::URL_PREFIX .'notifications/follow/%s%s', $id, $this->formatToString());

		return $this->query($url);
	}

	/**
	 * Disables notifications for updates from the specified user to the authenticating user.
	 * Returns the specified user when successful.
	 *
	 * @param int $id The ID or screen name of the user to leave.
	 * @return mixed
	 */
	public function notificationLeave($id)
	{
		if( !$this->checkSupportedFormat(self::FORMAT_XML | self::FORMAT_JSON, $this->format()) )
		{
			return FALSE;
		}

		$url = sprintf(self::URL_PREFIX .'notifications/leave/%s%s', $id, $this->formatToString());

		return $this->query($url);
	}

	/**
	 * Blocks the user specified in the ID parameter as the authenticating user.
	 * Returns the blocked user in the requested format when successful.
	 * You can find out more about blocking in the Twitter Support Knowledge Base.
	 *
	 * @param int $id The ID or screen_name of the user to block.
	 * @return mixed
	 */
	public function blockCreate($id)
	{
		if( !$this->checkSupportedFormat(self::FORMAT_XML | self::FORMAT_JSON, $this->format()) )
		{
			return FALSE;
		}

		$url = sprintf(self::URL_PREFIX .'blocks/create/%s%s', $id, $this->formatToString());

		return $this->query($url);
	}

	/**
	 * Un-blocks the user specified in the ID parameter as the authenticating user.
	 * Returns the un-blocked user in the requested format when successful.
	 *
	 * @param int $id The ID or screen_name of the user to un-block.
	 * @return mixed
	 */
	public function blockDestroy($id)
	{
		if( !$this->checkSupportedFormat(self::FORMAT_XML | self::FORMAT_JSON, $this->format()) )
		{
			return FALSE;
		}

		$url = sprintf(self::URL_PREFIX .'blocks/destroy/%s%s', $id, $this->formatToString());

		return $this->query($url);
	}

	/**
	 * Returns the string "ok" in the requested format with a 200 OK HTTP status code.
	 *
	 * @return mixed
	 */
	public function helpTest()
	{
		if( !$this->checkSupportedFormat(self::FORMAT_XML | self::FORMAT_JSON, $this->format()) )
		{
			return FALSE;
		}

		$url = sprintf(self::URL_PREFIX .'help/test%s', $this->formatToString());

		return $this->query($url);
	}

	/**
	 * Returns the same text displayed on http://twitter.com/home when a maintenance window is scheduled, in the requested format.
	 *
	 * @return mixed
	 */
	public function helpDowntimeSchedule()
	{
		if( !$this->checkSupportedFormat(self::FORMAT_XML | self::FORMAT_JSON, $this->format()) )
		{
			return FALSE;
		}

		$url = sprintf(self::URL_PREFIX .'help/downtime_schedule.%s', $this->formatToString());

		return $this->query($url);
	}
}