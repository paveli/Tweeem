<?php
/**
 * Основной скрипт
 * @package OpenStruct
 */

/**
 * Подключаем необходимые файлы
 */
require_once CORE_PATH .'common'. EXT;
require_once CORE_PATH .'Open/Benchmark'. EXT;
require_once CORE_PATH .'Open/Exception'. EXT;
require_once CORE_PATH .'Open/Router'. EXT;

/**
 * Задаём свой обработчик ошибок
 */
set_error_handler('errorHandler');

/**
 * Отключаем добавление кавычек входным данным на лету
 * Во избежание ситуаций, когда кавычки могут добавиться дважды мы будем делать это всегда самостоятельно
 * К тому же использование этой возможности объявлено нежелательным и будет исключено из PHP6
 */
set_magic_quotes_runtime(0);

try
{	/**
	 * Если режим дебага, создаём объект для работы с метками времени
	 * При уничтожении объекта он выведет сколько времени отработало приложение
	 */
	if(DEBUG) Open_Benchmark::getInstance();

	/**
	 * Объект маршрутизации
	 * Все действия выполняются в конструкторе, поэтому нам необходимо просто создать объект
	 * Определяются язык, локаль и устанавливаются
	 * Определяются контроллер и метод
	 * Вызов метода контроллера
	 */
	Open_Router::getInstance()->invoke();
}
catch(Exception $E)
{
	$R = new ReflectionObject($E);
	if( $R->hasMethod('handle') )
	{	$E->handle();
	}
	else
	{	p($E->__toString());
	}
}