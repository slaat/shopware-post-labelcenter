{extends file="parent:frontend/checkout/error_messages.tpl"}
{block name='frontend_checkout_error_messages_no_shipping'}
    {$smarty.block.parent}
    {if $sNoPostBranchSelected}
        {*{include file="frontend/_includes/messages.tpl" type="warning" content="{s name='ConfirmInfoNoDispatch'}{/s}"}*}
        {* include file="frontend/_includes/messages.tpl" type="error" content="no branch selected" *}
    {/if}
{/block}
