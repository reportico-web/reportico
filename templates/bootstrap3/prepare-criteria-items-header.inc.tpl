			<div id="swPrpSubmitPane">
{if !$IS_ADMIN_SCREEN}
{if $SHOW_HIDE_PREPARE_GO_BUTTONS == "show"}
    				<input type="submit" class="{$BOOTSTRAP_STYLE_GO_BUTTON}prepareAjaxExecute swHTMLGoBox" id="prepareAjaxExecute" name="submitPrepare" value="{$T_GO}">
{/if}
{if $SHOW_HIDE_PREPARE_RESET_BUTTONS == "show"}
    				<input type="submit" class="{$BOOTSTRAP_STYLE_RESET_BUTTON}reporticoSubmit" name="clearform" value="{$T_RESET}">
{/if}
{else}
    				<input type="submit" class="{$BOOTSTRAP_STYLE_GO_BUTTON}prepareAjaxExecute swHTMLGoBox" id="prepareAjaxExecute" name="submitPrepare" value="{$T_GO}">
{/if}
{if $SHOW_MINIMAINTAIN} 
<div style="float: left">
{if !$REPORTICO_BOOTSTRAP_MODAL}
                    <button type="submit" class="btn btn-default prepareMiniMaintain swMiniMaintain" style="margin-right: 30px" title="{$T_EDIT} {$T_EDITCRITERIA}" id="submit_mainquercrit" value="{$T_EDITCRITERIA}" name="mainquercrit_ANY">
                        <span class="glyphicon glyphicon-pencil"></span>{$T_EDITCRITERIA}
                    </button>
{else}
                    <button type="submit" class="btn btn-default prepareMiniMaintain swMiniMaintain" style="margin-right: 30px" title="{$T_EDIT} {$T_EDITCRITERIA}" id="submit_mainquercrit" value="{$T_EDITCRITERIA}" name="mainquercrit_ANY">
                        <span class="glyphicon glyphicon-pencil"></span>{$T_EDITCRITERIA}
                    </button>
{/if}
</div>
{/if}
                    &nbsp;
			</div>
