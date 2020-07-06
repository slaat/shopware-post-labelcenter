{extends file="parent:frontend/checkout/confirm.tpl"}

{block name='frontend_checkout_confirm_information_addresses_shipping_panel_actions_select_address'}
    {$smarty.block.parent}
    <br>
    <a href="javascript:void(0)" onclick="resetShippingToPlc()">oder Post Filiale auswählen
    </a>
{/block}

{block name='frontend_checkout_confirm_information_addresses_equal_panel_shipping'}

    {$smarty.block.parent}
    {if $sNoPostBranchSelected}
        <div class="shipping--panel" style="float: left;">
            <div class="subsidiary--field-label"
                 style="margin: 25px 0 10px 0; text-align: left; display: inline-block; width: 290px;">
                <strong>Post Filialfinder</strong>

                <div class="ui-widget">
                    <input id="branches" type="text" autocomplete="off" placeholder="PLZ">
                    <div class="post--label-error is--hidden">PLZ nicht gefunden. Bitte Eingabe prüfen.
                    </div>
                    <div class="branches_load_icon"></div>
                </div>

            </div>
            <div>
                {$subsidiary_ajax_url  = {url module=frontend controller=PostLabelOrderReturn action=get_subsidiary_list}|replace:"http:":""}
                {$subsidiary_ajax_url2 = {url module=frontend controller=PostLabelOrderReturn action=set_user_subsidiary}|replace:"http:":""}
                {$subsidiary_ajax_url3 = {url module=frontend controller=PostLabelOrderReturn action=get_user_branch}|replace:"http:":""}
                {$subsidiary_ajax_url4 = {url module=frontend controller=PostLabelOrderReturn action=remove_user_branch}|replace:"http:":""}

                <input type="hidden" name="subsidiary_ajax_url" id="subsidiary_ajax_url" value="{$subsidiary_ajax_url}">
                <input type="hidden" name="subsidiary_ajax_url2" id="subsidiary_ajax_url2"
                       value="{$subsidiary_ajax_url2}">
                <input type="hidden" name="subsidiary_ajax_url3" id="subsidiary_ajax_url3"
                       value="{$subsidiary_ajax_url3}">
                <input type="hidden" name="subsidiary_ajax_url4" id="subsidiary_ajax_url4"
                       value="{$subsidiary_ajax_url4}">
            </div>
            <div id="selected_subsidiary_wrapper" class="selected_subsidiary_wrapper">
                <div id="subsidiary_branch_selected">{if $userBranch neq ""}Ausgewählte Filiale:
                        <strong>{$userBranch}</strong>{/if}</div>
            </div>

            <div id="subsidiary_alert"></div>
        </div>
    {/if}
{/block}
