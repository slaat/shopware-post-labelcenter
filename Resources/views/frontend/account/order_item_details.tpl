{extends file="parent:frontend/account/order_item_details.tpl"}

{block name="frontend_account_order_item_repeat_order"}
    {$smarty.block.parent}

    {assign var="currentTime" value=$smarty.now|date_format:"d-m-Y"|strtotime}
    {assign var="maxReturnTime" value=$offerPosition.maxReturnTime}

    {if $offerPosition.status == '7' && $currentTime && $maxReturnTime &&  $offerPosition.returnByQuantityAllowed}
        {if $currentTime <= $maxReturnTime}
            {assign var="returnAllowed" value=true}
        {/if}
    {/if}

    <div class="panel--tr is--odd is--align-center">
        {if $returnAllowed && $postReturnOrderAllowed}
            <form action="{url controller='postlabelorderreturn' action='index'}" method="POST">
                <input type="hidden" name="orderid" value="{$offerPosition.id}">
                <input type="hidden" name="ordernumber" value="{$offerPosition.ordernumber}">
                <button class="btn is--secondary is--small post--account-return">
                    Bestellung retournieren
                </button>
            </form>
        {/if}
        {if $offerPosition.trackingcode}
            <button class="btn is--primary is--small post--load-tracking"
                    data-collapse-panel="true"
                    data-collapsetarget="#posttracking{$offerPosition.ordernumber}"
                {$tmpTRackingUrl = {url controller=PostOrderTracking action=getTrackingStatusAjax orderid=$offerPosition.ordernumber}|replace:"http:":""}
                    data-url="{$tmpTRackingUrl}">
                Post Tracking
            </button>
        {/if}
    </div>

    {if $offerPosition.trackingcode}
        <div id="posttracking{$offerPosition.ordernumber}" class="post--tracking-details panel--table" data-shop-url="{url controller="index"}">
            {$postTrackingCodes = $offerPosition.trackingcode|trim:"; "}
            {$postTrackingCodes = "; "|explode:$postTrackingCodes}
            <div class="tab-menu--product js--tab-menu">
                <div class="tab--navigation">
                    <div class="post--tracking-loader"></div>
                    {foreach from=$postTrackingCodes item="postTrackingCode" name="postTrackingCodeLoop"}
                        {if $postTrackingCode}
                            <a href="#" class="tab--link has--content post--tracking-link post--tracking-tab-link{$smarty.foreach.postTrackingCodeLoop.index}
                                {if $smarty.foreach.postTrackingCodeLoop.first}is--active{/if}" title="{$postTrackingCode}"
                               data-url="{{url controller=PostOrderTracking action=getTrackingStatusAjax orderid=$offerPosition.ordernumber}|replace:"http:":""}"
                               data-tracking-target=".post--tracking-tab-content{$smarty.foreach.postTrackingCodeLoop.index}"
                               data-tracking-parent="#posttracking{$offerPosition.ordernumber}"
                               data-tracking-code="{$postTrackingCode}">
                                Paket: {$postTrackingCode}
                            </a>
                        {/if}
                    {/foreach}
                </div>

                <div class="tab--container-list">
                    {foreach from=$postTrackingCodes item="postTrackingCode" name="postTrackingCodeLoop"}
                        <div class="tab--container is--active {if $smarty.foreach.postTrackingCodeLoop.first}is--active{/if}">
                            <div class="tab--header">
                                <a href="#" class="tab--title post--tracking-link-header" title="{$postTrackingCode}"
                                   data-url="{{url controller=PostOrderTracking action=getTrackingStatusAjax orderid=$offerPosition.ordernumber}|replace:"http:":""}"
                                   data-tracking-target=".post--tracking-tab-content{$smarty.foreach.postTrackingCodeLoop.index}"
                                   data-tracking-parent="#posttracking{$offerPosition.ordernumber}"
                                   data-tracking-code="{$postTrackingCode}">
                                    Paket {$postTrackingCode}
                                </a>
                            </div>
                            <div class="tab--content post--tracking-tab-content{$smarty.foreach.postTrackingCodeLoop.index}">

                                <div class="buttons--off-canvas">
                                        <a href="#" title="Tracking schliessen" class="close--off-canvas">
                                            <i class="icon--arrow-left"></i>
                                            Tracking schliessen
                                        </a>
                                </div>

                                <div class="tracking--container">
                                    <div class="tracking-cell-head post--tracking-no-data" style="display: none;">
                                        <span class="tracking-red-notice-text">Es konnten keine Daten für Ihre Abfrage gefunden werden.</span>
                                    </div>

                                    <div class="post--tracking-tracking-data">
                                        {*$postTrackingCode*}
                                    </div>
                                </div>
                            </div>
                        </div>
                    {/foreach}
                </div>

            </div>
        </div>
    {/if} {*end if tracking code exists*}
{/block}
