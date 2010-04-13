{* Smarty *}
{strip}

<!-- home/workspace/timeline -->

{if isset($timeline)}
	{foreach name=timeline from=$timeline item=item}
		<div class="item{if $smarty.foreach.timeline.last} last{/if}">
			<img src="{$item.user.profile_image_url}" alt="{$item.user.name}" align="top"/>
			<div class="text">
				<span class="name">{$item.user.name}</span>&nbsp;<span class="msg">{$item.text}</span>
			</div>
		</div>
	{/foreach}
{/if}

<!-- Cache time: {$smarty.now|date_format:"%Y-%m-%d %H:%M:%S"} -->
<!-- Dynamic time: {dynamic}{$smarty.now|date_format:"%Y-%m-%d %H:%M:%S"}{/dynamic} -->
<!-- /home/workspace/timeline -->

{/strip}