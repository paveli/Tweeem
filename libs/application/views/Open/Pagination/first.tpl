{* Smarty *}
{strip}

{if !$params.pagination|@empty}

	<div class="{if $params.class}{$params.class}{else}pagination-{$params.pattern}{/if}">

		{foreach name=pagination from=$params.pagination item=item}
			{if $item !== true}
				{if $item != $params.current}
					<span><a href="{"[:nav:]"|str_replace:$item:$params.link}">{$item}</a></span>
				{else}
					<span class="current">{$item}</span>
				{/if}
			{else}
				<span class="gap">{if $params.gap}{$params.gap}{else}&#8230;{/if}</span>
			{/if}
		{/foreach}

	</div>

{/if}

{/strip}