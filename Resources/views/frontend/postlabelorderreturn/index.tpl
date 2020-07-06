{extends file="frontend/index/index.tpl"}
{block name="frontend_index_content"}
	<div class="content">
		<div class="postReturnArticles--container">
            {$postReturnUrl = {url controller=postLabelOrderReturn action=returnArticles}|replace:"http:":""}
            {$orderReturnUrl = {url controller=account action=orders}|replace:"http:":""}
            {$fileOpenUrl = {url controller=postLabelOrderReturn action=getReturnLabel}|replace:"http:":""}
            <input type="hidden" class="postReturnUrl" name="postReturnUrl" value="" data-url="{$postReturnUrl}"/>
            <input type="hidden" class="postFileOpenUrl" value="" data-url="{$fileOpenUrl}"/>
			<input type="hidden" name="orderid" value="{$orderid}">
			<div class="product--table">
				<div class="table--actions">
					<div class="main--actions">
						{block name="orderReturnBackAction" }
							<a href="{$orderReturnUrl}"
							   class="btn btn--checkout-continue left continue-shopping--action is--icon-left is--large">
								<i class="icon--arrow-left"> </i>Zurück zur Bestellübersicht
							</a>
						{/block}
						{block name="orderReturnConfirmAction"}
							<button class="btn is--primary is--large right is--icon-right is--disabled acl-return-button"
									type="submit">
								Weiter zum Retourenlabel<i class="icon--arrow-right"></i>
							</button>
						{/block}
					</div>
				</div>
				<div class="panel has--border">
					<div class="panel--body is--rounded">
						<div class="table--header block-group">
							<div class="panel--th column--product block">Auswahl</div>
							<div class="panel--th column--quantity block is--align-right">Anzahl</div>
							<div class="panel--th panel--th column--total-price block is--align-right">Retourengrund
							</div>
						</div>

						{foreach from=$orderReturnArticles item=orderReturnArticle name='articleReturnLoop'}
							{if $smarty.foreach.articleReturnLoop.last}
								{assign var="lastReturnArticle" value=true}
							{/if}
							{if $orderReturnArticle.availableReturn > 0 && $orderReturnArticle.esdArticle != 1}
								{include file="frontend/postlabelorderreturn/return_item.tpl" lastReturnArticle=$lastReturnArticle}
							{/if}
						{/foreach}

					</div>
				</div>

				<div class="table--actions table--actions-bottom">
					<div class="main--actions">
						{block name="orderReturnBackAction" }
							<a href="{$orderReturnUrl}"
							   title=""
							   class="btn btn--checkout-continue left continue-shopping--action is--icon-left is--large">
								<i class="icon--arrow-left"> </i>Zurück zur Bestellübersicht
							</a>
						{/block}
						{block name="orderReturnConfirmAction"}
							<button class="btn is--primary is--large right is--icon-right is--disabled acl-return-button"
									form="return--form" data-preloader-button="true">
								Weiter zum Retourenlabel<i class="icon--arrow-right"></i>
							</button>
						{/block}
					</div>
				</div>

			</div>
		</div>
	</div>
{/block}
{block name='frontend_index_left_categories_inner'}{*$smarty.block.parent  MEPTY CATEGORY MENU*}{/block}
