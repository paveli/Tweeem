{* Smarty *}
{strip}

<!-- error -->

{dynamic}

{if isset($message)}
	<div id="error">
		{$message}
	</div>
{/if}

{/dynamic}

<!-- /error -->

{/strip}