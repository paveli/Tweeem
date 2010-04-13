<?php
/**
 * Подключаем необходимые файлы
 */
require_once CORE_PATH .'Open/Controller'. EXT;
require_once CORE_PATH .'Open/Convert'. EXT;
require_once CORE_PATH .'Open/Color'. EXT;
require_once CORE_PATH .'Open/Image'. EXT;

/**
 * Тестовый контроллер
 */
class Test extends Open_Controller
{
	public function index($isAjax=false)
	{
		$I = $this->input;
		$R = $this->router;
		$V = $this->view;

		if(DEBUG) $B = Open_Benchmark::getInstance();
		p(htmlentities('Decora S.A., ul Pr?dzy?skiego 24a, 63-000 ?roda Wielkopolska', ENT_QUOTES, CHARSET));

//		$M = getModel('TwitterModel');
//		$M->login('matros');
//		$M->password('ZZ6f86rftcwjmr');
//
//		$B->mark('tweeem_start');
//		$temp = $M->accountVerifyCredentials();
//		$B->display('tweeem');
//
//		p(json_decode($temp));


		/*--------------------------------*/

		/**
		 * Если данные пришли
		 */
//		if( ($quiz = $I->post('quiz')) !== FALSE )
//		{
//			$M = getModel('TestModel');

//			$result = $M->validateQuiz($quiz);

//			if( $isAjax )
//			{	echo '<span id="validationResult">'. json_encode($result) .'</span>';
//				return;
//			}
//			else if( $result === TRUE )
//			{	$R->redirect('/test/congratulations/');
//			}
//			else
//			{	$V->smarty->assign('quizErrors', $result);
//			}
//		}

//		$V->addJs('yui/connection-min');
//		$V->addJs('yui/json-min');
//		$V->addJs('captcha');
//		$V->smarty->assign('quiz', $quiz);
//		$V->show('Test/index');
	}

	public function congratulations()
	{
		p('Grats! You can take a cookie!');
		p($_POST);
	}

//	static public function t1($table, $value)
//	{
//		for($i=0; $i<4; $i++)
//		{	$q = substr('====', $i);
//		}
//	}
//
//	static public function t2($table, $value)
//	{
//		for($i=0; $i<4; $i++)
//		{
//			$q = '';
//			for($j=0; $j<$i; $j++)
//			$q .= '=';
//		}
//	}
}