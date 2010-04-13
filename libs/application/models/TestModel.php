<?php
/**
 * Подключаем необходимые файлы
 */
require_once CORE_PATH .'Open/Model'. EXT;
require_once CORE_PATH .'Open/Validator'. EXT;

/**
 * Модель пример
 */
class TestModel extends Open_Model
{

	public function validateQuiz($quiz)
	{
		$q4Callback = create_function('&$var, &$args', '
			if( is_array($var) && count($var)==2 && in_array(1, $var) && in_array(3, $var) )
			{	return TRUE;
			}

			return \'Answer is not correct\';
		');

		$captchaCallback = create_function('&$var, &$args', '
			if( getModel(\'CaptchaModel\')->verify($var) )
			{	return TRUE;
			}

			return \'You haven\\\'t recognized text on the image correctly. Try again.\';
		');

		$rules = array(
			'q1' => 'match:gagarin:"Answer is not correct"',
			'q2' => 'match:2:"Answer is not correct"',
			'q3' => 'match:3:"Answer is not correct"',
			'q4' => 'callback:'. $q4Callback,
			'captcha' => 'callback:'. $captchaCallback,
		);

		$validator = Open_Validator::getInstance();

		return $validator->validate($quiz, $rules, TRUE);
	}
}