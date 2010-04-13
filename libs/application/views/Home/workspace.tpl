{* Smarty *}
{strip}

<!-- home/workspace -->

<script language="JavaScript" type="text/javascript">
	T.User = {literal}{}{/literal};
	T.User.login = '{$user.login}';
	T.User.profile_image = {literal}{{/literal}normal: '{$user.profile_image.normal}', mini: '{$user.profile_image.mini}'{literal}}{/literal};
</script>

<div id="chatPanel" class="panel" style="visibility: hidden;">
	<div class="hd"><span>Chat</span><div class="container-minimize"></div></div>
	<div class="bd">
		{include file="Home/workspace/chat.tpl"}
	</div>
	<div class="ft"><span></span></div>
</div>

<div id="listPanel" class="panel" style="visibility: hidden;">
	<div class="hd"><span>List</span><div class="container-minimize"></div></div>
	<div class="bd">
		{include file="Home/workspace/list.tpl"}
		<!--<span>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Suspendisse nulla. Fusce mauris massa, rutrum eu, imperdiet ut, placerat at, nunc. Vestibulum consequat ligula ut lacus. Nulla nec pede. Fusce consequat, augue et eleifend ornare, nibh mi dapibus lorem, ut lacinia turpis eros at eros. Proin laoreet. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Nulla velit. Fusce id sem sit amet felis porta mollis. Aliquam erat volutpat. Etiam tortor. Donec dui felis, pretium quis, vulputate et, molestie non, nisi.</span>-->
	</div>
	<div class="ft"><span></span></div>
</div>

<div id="minimizedContainer"></div>

<!-- Cache time: {$smarty.now|date_format:"%Y-%m-%d %H:%M:%S"} -->
<!-- Dynamic time: {dynamic}{$smarty.now|date_format:"%Y-%m-%d %H:%M:%S"}{/dynamic} -->
<!-- /home/workspace -->

{/strip}