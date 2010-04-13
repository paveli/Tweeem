{* Smarty *}
{* Объявляем тип документа *}
{* <?xml version="1.0" encoding="{$config->get('charset')|strtolower}" ?> *}
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

{* Для того, чтобы страница меньше "весила" удаляем везде все пробелы и переходы на новые строки во всех шаблонах *}
{* На этапе разработки это можно не делать, чтобы видеть код размеченным *}
{* В конечном результате, если из всех шаблонов удалено всё лишнее, код страницы будет в виде одной строки *}
{* К тому же сторонним людям сложнее будет понять код страницы *}
{* А так же будут исключены раздражающие ситуации, когда влезают пробелы между двумя тегами там, где их не должно быть *}
{strip}

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>{$view->getTitle()}</title>

	{* Тип содержимого и кодировка, запасной вариант *}
	<meta http-equiv="Content-Type" content="text/html; charset={$config->get('charset')}"/>

	{* Указываем, что во всех script тегах у нас сидит JavaScript *}
	<meta http-equiv="Content-Script-Type" content="text/javascript" />

	{* Подключаем все css-скрипты *}
	{foreach from=$view->getCss() item=item}
		<link rel="stylesheet" type="text/css" href="/{$smarty.const.CSS_DIR}{$item}{$smarty.const.CSSEXT}" />
	{/foreach}

	{* Подключаем все js-скрипты *}
	{foreach from=$view->getJs() item=item}
		<script language="JavaScript" type="text/javascript" src="/{$smarty.const.JS_DIR}{$item}{$smarty.const.JSEXT}"></script>
	{/foreach}
</head>
<body>
	{include file=$view->getBody()|cat:$smarty.const.TPLEXT}
</body>
</html>

{/strip}