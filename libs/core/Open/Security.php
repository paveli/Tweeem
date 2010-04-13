<?php
/**
 * Обеспечение безопасности - класс Open_Security
 * @package OpenStruct
 */

/**
 * Подключаем необходимые файлы
 */
require_once CORE_PATH .'Open/Config'. EXT;
require_once CORE_PATH .'Open/Convert'. EXT;

/**
 * Обеспечение безопасности
 */
class Open_Security extends Open_Singleton
{
	/*************
	 * Константы *
	 *************/

	/**
	 * Ключ по умолчанию для простого шифрования
	 */
	const EASY_ENCRYPTION_KEY = 'Ё!0"й№K;ц%X:у?R*к(g)е_N+н~C!г@1#ш$0%щ^L&з*0(х)g_ъ+0`ф1Y2ы3H4в5Q6а7u8п990р-C=о-л0дLжzэQяsчNсCм1и0тYьLбQюt';

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
	 * Простое шифрование:
	 * Строка данных посимвольно преобразуется через xor операцию с ключом
	 * Преобразуется в base64 и переворачивается
	 *
	 * @param string $data Строка произвольной длины
	 * @param string $key Строка произвольной длины
	 * @return string
	 */
	public function easyEncrypt($data, $key=FALSE)
	{
		$key = ( ($key === FALSE) ? self::EASY_ENCRYPTION_KEY : $key );

		$result = '';
		for($i=0; $i<strlen($data); $i++)
		{
			$result .= $data{$i} ^ $key{$i%strlen($key)};
		}

		return strrev(Open_Convert::getInstance()->toBase64($result));
	}

	/**
	 * Дешифрация простого шифрования
	 * Все действия при шифровании в обратном порядке
	 *
	 * @param string $data
	 * @param string $key
	 * @return string
	 */
	public function easyDecrypt($data, $key=FALSE)
	{
		$key = ( ($key === FALSE) ? self::EASY_ENCRYPTION_KEY : $key );

		$data = Open_Convert::getInstance()->fromBase64(strrev($data));

		$result = '';
		for($i=0; $i<strlen($data); $i++)
		{
			$result .= $data{$i} ^ $key{$i%strlen($key)};
		}

		return $result;
	}

	/**
	 * Рекурсивная XSS-очистка массива
	 * Массив передаётся по ссылке
	 *
	 * @param mixed $var
	 * @return bool Успех операции
	 */
	public function xssCleanArray(&$var)
	{
		if( is_array($var) )
		{	foreach($var as &$v)
			{	self::xssCleanArray($v);
			}
		}
		else
		{	$var = self::xssClean($var);
		}

		return TRUE;
	}

	/**
	 * Нижеследующий код взят из фрэймворка CodeIgniter
	 * Необходима функция XSS-очистики входных данных хорошо реализованная в CodeIgniter
	 * Изменения:
	 * 1. Имя функции с xss_clean на xssClean
	 * 2. Атрибуты доступа методов, отсутствующие в оригинале, т.к. он предназначен для PHP 4.3.2
	 * 3. Вместо авторской функции _html_entity_decode_callback используется своя функция xss_html_entity_decode
	 * 4. Функции _js_link_removal, _js_img_removal, _attribute_conversion переименованы соотвественно в xss_js_link_removal, xss_js_img_removal, xss_attribute_conversion
	 * 5. Закомментирована запись в лог
	 */
	/****************************************************/
	/**
	 * CodeIgniter
	 *
	 * An open source application development framework for PHP 4.3.2 or newer
	 *
	 * @package		CodeIgniter
	 * @author		Rick Ellis
	 * @copyright	Copyright (c) 2006, EllisLab, Inc.
	 * @license		http://www.codeignitor.com/user_guide/license.html
	 * @link		http://www.codeigniter.com
	 * @since		Version 1.0
	 * @filesource
	 */
	/**
	 * XSS Clean
	 *
	 * Sanitizes data so that Cross Site Scripting Hacks can be
	 * prevented.  This function does a fair amount of work but
	 * it is extremely thorough, designed to prevent even the
	 * most obscure XSS attempts.  Nothing is ever 100% foolproof,
	 * of course, but I haven't been able to get anything passed
	 * the filter.
	 *
	 * Note: This function should only be used to deal with data
	 * upon submission.  It's not something that should
	 * be used for general runtime processing.
	 *
	 * This function was based in part on some code and ideas I
	 * got from Bitflux: http://blog.bitflux.ch/wiki/XSS_Prevention
	 *
	 * To help develop this script I used this great list of
	 * vulnerabilities along with a few other hacks I've
	 * harvested from examining vulnerabilities in other programs:
	 * http://ha.ckers.org/xss.html
	 *
	 * @access	public
	 * @param	string
	 * @return	string
	 */
	public function xssClean($str)
	{
		/*
		 * Remove Null Characters
		 *
		 * This prevents sandwiching null characters
		 * between ascii characters, like Java\0script.
		 *
		 */
		$str = preg_replace('/\0+/', '', $str);
		$str = preg_replace('/(\\\\0)+/', '', $str);

		/*
		 * Validate standard character entities
		 *
		 * Add a semicolon if missing.  We do this to enable
		 * the conversion of entities to ASCII later.
		 *
		 */
		$str = preg_replace('#(&\#?[0-9a-z]+)[\x00-\x20]*;?#i', "\\1;", $str);

		/*
		 * Validate UTF16 two byte encoding (x00)
		 *
		 * Just as above, adds a semicolon if missing.
		 *
		 */
		$str = preg_replace('#(&\#x?)([0-9A-F]+);?#i',"\\1\\2;",$str);

		/*
		 * URL Decode
		 *
		 * Just in case stuff like this is submitted:
		 *
		 * <a href="http://%77%77%77%2E%67%6F%6F%67%6C%65%2E%63%6F%6D">Google</a>
		 *
		 * Note: Normally urldecode() would be easier but it removes plus signs
		 *
		 */
		$str = preg_replace("/(%20)+/", '9u3iovBnRThju941s89rKozm', $str);
		$str = preg_replace("/%u0([a-z0-9]{3})/i", "&#x\\1;", $str);
		$str = preg_replace("/%([a-z0-9]{2})/i", "&#x\\1;", $str);
		$str = str_replace('9u3iovBnRThju941s89rKozm', "%20", $str);

		/*
		 * Convert character entities to ASCII
		 *
		 * This permits our tests below to work reliably.
		 * We only convert entities that are within tags since
		 * these are the ones that will pose security problems.
		 *
		 */

		$str = preg_replace_callback("/[a-z]+=([\'\"]).*?\\1/si", array($this, 'xss_attribute_conversion'), $str);

		$str = preg_replace_callback("/<([\w]+)[^>]*>/si", array($this, 'xss_html_entity_decode'), $str);

		/*

		Old Code that when modified to use preg_replace()'s above became more efficient memory-wise

		if (preg_match_all("/[a-z]+=([\'\"]).*?\\1/si", $str, $matches))
		{
			for ($i = 0; $i < count($matches[0]); $i++)
			{
				if (stristr($matches[0][$i], '>'))
				{
					$str = str_replace(	$matches['0'][$i],
										str_replace('>', '&lt;', $matches[0][$i]),
										$str);
				}
			}
		}

        if (preg_match_all("/<([\w]+)[^>]*>/si", $str, $matches))
        {
			for ($i = 0; $i < count($matches[0]); $i++)
			{
				$str = str_replace($matches[0][$i],
									$this->_html_entity_decode($matches[0][$i], $charset),
									$str);
			}
		}
		*/

		/*
		 * Convert all tabs to spaces
		 *
		 * This prevents strings like this: ja	vascript
		 * NOTE: we deal with spaces between characters later.
		 * NOTE: preg_replace was found to be amazingly slow here on large blocks of data,
		 * so we use str_replace.
		 *
		 */

		$str = str_replace("\t", " ", $str);

		/*
		 * Not Allowed Under Any Conditions
		 */
		$bad = array(
						'document.cookie'	=> '[removed]',
						'document.write'	=> '[removed]',
						'.parentNode'		=> '[removed]',
						'.innerHTML'		=> '[removed]',
						'window.location'	=> '[removed]',
						'-moz-binding'		=> '[removed]',
						'<!--'				=> '&lt;!--',
						'-->'				=> '--&gt;',
						'<!CDATA['			=> '&lt;![CDATA['
					);

		foreach ($bad as $key => $val)
		{
			$str = str_replace($key, $val, $str);
		}

		$bad = array(
						"javascript\s*:"	=> '[removed]',
						"expression\s*\("	=> '[removed]', // CSS and IE
						"Redirect\s+302"	=> '[removed]'
					);

		foreach ($bad as $key => $val)
		{
			$str = preg_replace("#".$key."#i", $val, $str);
		}

		/*
		 * Makes PHP tags safe
		 *
		 *  Note: XML tags are inadvertently replaced too:
		 *
		 *	<?xml
		 *
		 * But it doesn't seem to pose a problem.
		 *
		 */
		$str = str_replace(array('<?php', '<?PHP', '<?', '?'.'>'),  array('&lt;?php', '&lt;?PHP', '&lt;?', '?&gt;'), $str);

		/*
		 * Compact any exploded words
		 *
		 * This corrects words like:  j a v a s c r i p t
		 * These words are compacted back to their correct state.
		 *
		 */
		$words = array('javascript', 'expression', 'vbscript', 'script', 'applet', 'alert', 'document', 'write', 'cookie', 'window');
		foreach ($words as $word)
		{
			$temp = '';
			for ($i = 0; $i < strlen($word); $i++)
			{
				$temp .= substr($word, $i, 1)."\s*";
			}

			// We only want to do this when it is followed by a non-word character
			// That way valid stuff like "dealer to" does not become "dealerto"
			$str = preg_replace('#('.substr($temp, 0, -3).')(\W)#ise', "preg_replace('/\s+/s', '', '\\1').'\\2'", $str);
		}

		/*
		 * Remove disallowed Javascript in links or img tags
		 */
		do
		{
			$original = $str;

			if ((version_compare(PHP_VERSION, '5.0', '>=') === TRUE && stripos($str, '</a>') !== FALSE) OR
				 preg_match("/<\/a>/i", $str))
			{
				$str = preg_replace_callback("#<a.*?</a>#si", array($this, 'xss_js_link_removal'), $str);
			}

			if ((version_compare(PHP_VERSION, '5.0', '>=') === TRUE && stripos($str, '<img') !== FALSE) OR
				 preg_match("/img/i", $str))
			{
				$str = preg_replace_callback("#<img.*?".">#si", array($this, 'xss_js_img_removal'), $str);
			}

			if ((version_compare(PHP_VERSION, '5.0', '>=') === TRUE && (stripos($str, 'script') !== FALSE OR stripos($str, 'xss') !== FALSE)) OR
				 preg_match("/(script|xss)/i", $str))
			{
				$str = preg_replace("#</*(script|xss).*?\>#si", "", $str);
			}
		}
		while($original != $str);

		unset($original);

		/*
		 * Remove JavaScript Event Handlers
		 *
		 * Note: This code is a little blunt.  It removes
		 * the event handler and anything up to the closing >,
		 * but it's unlikely to be a problem.
		 *
		 */
		$event_handlers = array('onblur','onchange','onclick','onfocus','onload','onmouseover','onmouseup','onmousedown','onselect','onsubmit','onunload','onkeypress','onkeydown','onkeyup','onresize', 'xmlns');
		$str = preg_replace("#<([^>]+)(".implode('|', $event_handlers).")([^>]*)>#iU", "&lt;\\1\\2\\3&gt;", $str);

		/*
		 * Sanitize naughty HTML elements
		 *
		 * If a tag containing any of the words in the list
		 * below is found, the tag gets converted to entities.
		 *
		 * So this: <blink>
		 * Becomes: &lt;blink&gt;
		 *
		 */
		$str = preg_replace('#<(/*\s*)(alert|applet|basefont|base|behavior|bgsound|blink|body|embed|expression|form|frameset|frame|head|html|ilayer|iframe|input|layer|link|meta|object|plaintext|style|script|textarea|title|xml|xss)([^>]*)>#is', "&lt;\\1\\2\\3&gt;", $str);

		/*
		 * Sanitize naughty scripting elements
		 *
		 * Similar to above, only instead of looking for
		 * tags it looks for PHP and JavaScript commands
		 * that are disallowed.  Rather than removing the
		 * code, it simply converts the parenthesis to entities
		 * rendering the code un-executable.
		 *
		 * For example:	eval('some code')
		 * Becomes:		eval&#40;'some code'&#41;
		 *
		 */
		$str = preg_replace('#(alert|cmd|passthru|eval|exec|expression|system|fopen|fsockopen|file|file_get_contents|readfile|unlink)(\s*)\((.*?)\)#si', "\\1\\2&#40;\\3&#41;", $str);

		/*
		 * Final clean up
		 *
		 * This adds a bit of extra precaution in case
		 * something got through the above filters
		 *
		 */
		$bad = array(
						'document.cookie'	=> '[removed]',
						'document.write'	=> '[removed]',
						'.parentNode'		=> '[removed]',
						'.innerHTML'		=> '[removed]',
						'window.location'	=> '[removed]',
						'-moz-binding'		=> '[removed]',
						'<!--'				=> '&lt;!--',
						'-->'				=> '--&gt;',
						'<!CDATA['			=> '&lt;![CDATA['
					);

		foreach ($bad as $key => $val)
		{
			$str = str_replace($key, $val, $str);
		}

		$bad = array(
						"javascript\s*:"	=> '[removed]',
						"expression\s*\("	=> '[removed]', // CSS and IE
						"Redirect\s+302"	=> '[removed]'
					);

		foreach ($bad as $key => $val)
		{
			$str = preg_replace("#".$key."#i", $val, $str);
		}

		//log_message('debug', "XSS Filtering completed");
		return $str;
	}

	// --------------------------------------------------------------------

	/**
	 * JS Link Removal
	 *
	 * Callback function for xss_clean() to sanitize links
	 * This limits the PCRE backtracks, making it more performance friendly
	 * and prevents PREG_BACKTRACK_LIMIT_ERROR from being triggered in
	 * PHP 5.2+ on link-heavy strings
	 *
	 * @access	private
	 * @param	array
	 * @return	string
	 */
	private function xss_js_link_removal($match)
	{
		return preg_replace("#<a.+?href=.*?(alert\(|alert&\#40;|javascript\:|window\.|document\.|\.cookie|<script|<xss).*?\>.*?</a>#si", "", $match[0]);
	}

	/**
	 * JS Image Removal
	 *
	 * Callback function for xss_clean() to sanitize image tags
	 * This limits the PCRE backtracks, making it more performance friendly
	 * and prevents PREG_BACKTRACK_LIMIT_ERROR from being triggered in
	 * PHP 5.2+ on image tag heavy strings
	 *
	 * @access	private
	 * @param	array
	 * @return	string
	 */
	private function xss_js_img_removal($match)
	{
		return preg_replace("#<img.+?src=.*?(alert\(|alert&\#40;|javascript\:|window\.|document\.|\.cookie|<script|<xss).*?\>#si", "", $match[0]);
	}

	// --------------------------------------------------------------------

	/**
	 * Attribute Conversion
	 *
	 * Used as a callback for XSS Clean
	 *
	 * @access	public
	 * @param	array
	 * @return	string
	 */
	private function xss_attribute_conversion($match)
	{
		return str_replace('>', '&lt;', $match[0]);
	}

	// --------------------------------------------------------------------

	/**
	 * Callback функция для preg_replace_callback
	 * С определением текущей кодировки
	 *
	 * @param array $match
	 * @return string
	 */
	static private function xss_html_entity_decode($match)
	{
		return html_entity_decode($match[0], ENT_COMPAT, strtoupper(Open_Config::getInstance()->get('charset')) );
	}

	/****************************************************/
}