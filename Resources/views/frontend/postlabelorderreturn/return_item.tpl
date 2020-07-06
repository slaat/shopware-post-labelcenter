<div class="table--tr block-group row--product {if $lastReturnArticle}is--last-row{/if} acl-row--product acl-row-invalid">
    <div class="panel--td column--actions"">
        <input type="checkbox" name="returnArticles[{$orderReturnArticle.articleNumber}][checked]" class="acl-return-checkbox">
    </div>
    <div class="column--product">
        <div class="panel--td column--image">
            <div class="table--media">
                <div class="table--media-outer">
                    <div class="table--media-inner">
                        <img src="{$orderReturnArticle.image}">
                    </div>
                </div>
            </div>
        </div>
        <div class="panel--td table--content">
            <span class="content--title">{$orderReturnArticle.articleName}</span>
            <p class="content--sku content">Artikel-Nr.: {$orderReturnArticle.articleNumber}</p>
        </div>
        {*lieferzeit*}
    </div>
    <div class="panel--td column--quantity">
        <div class="select-field">
            <select name="returnArticles[{$orderReturnArticle.articleNumber}][amount]" class="acl-return-quantity" disabled="disabled">
				{for $amount=1 to $orderReturnArticle.availableReturn}
                    <option value="{$amount}">{$amount}</option>
                {/for}
            </select>
        </div>
    </div>
    <div class="panel--td column--total-price is--align-right">
        <div class="select-field acl-return-reason-field">
            <select name="returnArticles[{$orderReturnArticle.articleNumber}][reason]" class="acl-return-reason has--error" disabled="disabled">
                <option disabled="disabled"
                        value=""
                        selected="selected">
                    Auswahl Retourengrund*
                </option>
                {foreach $returnReasons as $returnReason}
                    <option value="{$returnReason}">{$returnReason}</option>
                {/foreach}
            </select>
        </div>
    </div>
</div>
