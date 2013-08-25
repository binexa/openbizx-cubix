<div class="menu_title">
<h2>{$widget.title}</h2>
</div>
<ul class="toplevel {$widget.css} left_menu">
	{foreach item=item from=$widget.menu}
	<li>
	    {assign var='current' value='0'}
	    {foreach item=bc from=$widget.breadcrumb}
			{if $item->recordId == $bc->recordId}
	    		{assign var='current' value='1'}
			{/if}
	    {/foreach}
		{if $current == 1 }
			{assign var=menu_class value="current" }	
		{else}
			{assign var=menu_class value="" }
	    {/if}	
		<a onclick="show_submenu(this)" class="{$menu_class}" href="{if $item->m_URL and false}{$item->m_URL}{else}javascript:{/if}">
			<img class="{$item->m_IconCSSClass}" src="{$image_url}/{if $item->m_IconImage!=''}{$item->m_IconImage}{else}spacer.gif{/if}" />{$item->objectName}
		</a>	
		{if $item->childNodes|@count > 0}
		<ul class="secondlevel module" {if $menu_class eq 'current'}style="display:block;"{/if}>
		{foreach item=subitem from=$item->childNodes}
    		{assign var='current' value='0'}
    	    {foreach item=bc from=$widget.breadcrumb}
    			{if $subitem->recordId == $bc->recordId}
    	    		{assign var='current' value='1'}
    			{/if}
    	    {/foreach}
			{if $current == 1 }
				{assign var=submenu_class value="current" }	
			{else}
				{assign var=submenu_class value="" }
		    {/if}					
				<li><a class="{$submenu_class}" href="{if $subitem->m_URL}{$subitem->m_URL}{else}javascript:{/if}">{$subitem->objectName}</a></li>						
		{/foreach}	
		</ul>
		{/if}
	</li>
	{/foreach}	
</ul>
<div class="v_spacer"></div>