{* Smarty *}
{strip}

<!-- home/workspace/chat -->

<div id="chatTimeline" class="timeline"></div>

<div id="chatUpdate" class="update">
	<form action="/ajax/chatUpdate/" name="chat">
		<div class="text">
			<div class="area">
				<textarea id="chatTextarea" name="chat[text]"></textarea>
			</div>
			<div class="counter">
				<span id="chatUpdateCounter">{$config->get('tweeem', 'message_length_limit')}</span>
			</div>
			<div class="button" id="chatUpdateBtn"/>UPDATE</div>
		</div>
	</form>
</div>

<!-- Cache time: {$smarty.now|date_format:"%Y-%m-%d %H:%M:%S"} -->
<!-- Dynamic time: {dynamic}{$smarty.now|date_format:"%Y-%m-%d %H:%M:%S"}{/dynamic} -->
<!-- /home/workspace/chat -->

{/strip}