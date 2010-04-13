{* Smarty *}
{strip}

<!-- body -->

<div id="doc">
	<div id="hd">
		{include file="header`$smarty.const.TPLEXT`"}
	</div>

	<div id="bd">
		{* Переменная $body устанавливается при вызове метода show класс Open_View *}
		{include file=$body}
	</div>

	<div id="ft">
		{include file="footer`$smarty.const.TPLEXT`"}
	</div>
</div>

<!-- /body -->

{/strip}