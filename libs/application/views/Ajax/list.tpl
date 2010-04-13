{* Smarty *}
{strip}

<!-- home/workspace/list -->

{if isset($list)}
	{foreach name=list from=$list item=item}
		<div class="item{if $smarty.foreach.list.first} first{elseif $smarty.foreach.list.last} last{/if}">
			<img src="{$item.profile_image_url}" alt="{$item.name}" align="top"/>
			<div class="text">
				<span class="name">{$item.name}</span>
				<span class="screenName">{$item.screen_name}</span>
			</div>
		</div>
		
		{if !$smarty.foreach.list.last}
			<div class="line"></div>
		{/if}
	{/foreach}
{/if}

<!-- Cache time: {$smarty.now|date_format:"%Y-%m-%d %H:%M:%S"} -->
<!-- Dynamic time: {dynamic}{$smarty.now|date_format:"%Y-%m-%d %H:%M:%S"}{/dynamic} -->
<!-- /home/workspace/list -->

{/strip}