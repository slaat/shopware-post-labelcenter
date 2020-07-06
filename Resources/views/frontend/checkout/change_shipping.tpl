{extends file="parent:frontend/checkout/change_shipping.tpl"}

{block name='frontend_checkout_shipping_fieldset_description'}
    {$smarty.block.parent}
    {*
    <br>
    <div class="method--description">
        {foreach from=$dispatchShippingSets item="dispatchShippingSet" name="shippingSetsLoop"}
            {if $dispatchShippingSet.dispatchID == $dispatch.id && $dispatchShippingSet.shippingSets|@count > 0 }
                <div class="js--fancy-select select-field">
                    <select name="shippingSets">
                        {foreach from=$dispatchShippingSet.shippingSets item="shippingSet" name="shippingSetLoop"}
                            <option value="{$shippingSet.name}">{$shippingSet.name}</option>
                        {/foreach}
                    </select>
                </div>
            {/if}
        {/foreach}
    </div>
    *}
{/block}

