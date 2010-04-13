{* Smarty *}
{strip}

<!-- home/index -->

<div id="loginPanel" class="panel" style="visibility: hidden;">
	<div class="hd"><span>Login</span></div>
	<div class="bd">
		<form action="/login/" name="login" method="post">
		<div>
			<span>Login</span>
			<input type="text" name="login[login]" value="{dynamic}{if isset($login.login)}{$login.login}{/if}{/dynamic}"/>
			{dynamic}{if isset($loginErrors.login)}<span class="error">{$loginErrors.login}</span>{/if}{/dynamic}
		</div>

		<div>
			<span>Password</span>
			<input type="password" name="login[password]" value=""/>
			{dynamic}{if isset($loginErrors.password)}<span class="error">{$loginErrors.password}</span>{/if}{/dynamic}
		</div>

		<div>
			<input type="checkbox" id="login[remember]" name="login[remember]" value="true"{dynamic}{if isset($login.remember) && $login.remember} checked="checked"{/if}{/dynamic}/>
			<label for="login[remember]">Remember me on this computer</label>
		</div>

		<div>
			<input type="submit" value="Login"/>
			{dynamic}{if isset($loginErrors.invalid)}<span class="error">{$loginErrors.invalid}</span>{/if}{/dynamic}
		</div>
		</form>
	</div>
	<div class="ft"></div>
</div>

<!-- Cache time: {$smarty.now|date_format:"%Y-%m-%d %H:%M:%S"} -->
<!-- Dynamic time: {dynamic}{$smarty.now|date_format:"%Y-%m-%d %H:%M:%S"}{/dynamic} -->
<!-- /home/index -->

{/strip}