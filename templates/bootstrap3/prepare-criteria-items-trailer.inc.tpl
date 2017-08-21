{if isset($CRITERIA_ITEMS)}
{if count($CRITERIA_ITEMS) > 1}
<div id="{$BOOTSTRAP_STYLE_SMALL_BUTTON}swPrpSubmitPane">
{if !$IS_ADMIN_SCREEN}
	<input type="submit" class="{$BOOTSTRAP_STYLE_GO_BUTTON}prepareAjaxExecute swHTMLGoBox" id="prepareAjaxExecute" name="submitPrepare" value="{$T_GO}">
{/if}
    <!--input type="submit" class="reporticoSubmit" name="clearform" value="{$T_RESET}"-->
</div>
{/if}
{/if}
