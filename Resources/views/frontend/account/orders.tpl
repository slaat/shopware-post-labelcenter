{extends file="parent:frontend/account/orders.tpl"}
{block name="frontend_account_orders_welcome"}
	{if $sOrderReturnMessages}
		<div class="alert is--warning is--rounded {*if !$redirectInfoMessage}is--hidden{/if*}">
			<div class="alert--icon">
				<div class="icon--element icon--warning"></div>
			</div>
			<div class="alert--content">
				{foreach $sOrderReturnMessages as $returnMessage }
					{$returnMessage}
				{/foreach}
				<br>
				<strong><a href="{url controller='ticket' sFid=5}" title="Hilfe und Kontakt" btattached="true">Hilfe und
					Kontakt</a>
				</strong>
			</div>
		</div>
	{/if}
	{$smarty.block.parent}
{/block}