{* Smarty *}
{strip}

<!-- header -->

<div class="left">
	<span>{$config->get('application_name')}</span>
</div>

<div class="center">
	<div id="message">
		<span>&nbsp;</span>
	</div>
</div>

<div class="right">
	{dynamic}
		{if $acl->isAllowed($user.role, $smarty.const.ACL_RESOURCE_WORKSPACE, $smarty.const.ACL_ACTION_WORK)}
			<a id="logout" href="/logout/">Logout</a>
		{/if}
	{/dynamic}
</div>

<div id="loading">Loading...</div>

<!-- /header -->

{/strip}