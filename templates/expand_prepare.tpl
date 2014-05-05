{if strlen($ERRORMSG)>0}
            <TABLE class="swError">
                <TR>
                    <TD>{$ERRORMSG}</TD>
                </TR>
            </TABLE>
{/if}
{if strlen($STATUSMSG)>0} 
			<TABLE class="swStatus">
				<TR>
					<TD>{$STATUSMSG}</TD>
				</TR>
			</TABLE>
{/if}
{if strlen($STATUSMSG)==0 && strlen($ERRORMSG)==0}
<div style="float:right; ">
{if strlen($MAIN_MENU_URL)>0}
<!--a class="swLinkMenu" style="float:left;" href="{$MAIN_MENU_URL}">&lt;&lt; Menu</a-->
{/if}
</div>
{if $SHOW_EXPANDED}
							{$T_SEARCH} {$EXPANDED_TITLE} :<br>
                             <input  type="text" style="width: 50%; display: inline" class="{$BOOTSTRAP_STYLE_TEXTFIELD}" name="expand_value" value="{$EXPANDED_SEARCH_VALUE}">
									<input id="reporticoPerformExpand" class="{$BOOTSTRAP_STYLE_SMALL_BUTTON}swPrpSubmit" style="margin-bottom: 2px" type="button" name="EXPANDSEARCH_{$EXPANDED_ITEM}" value="{$T_SEARCH}"><br>
{$CONTENT}
							<br>
							<input class="{$BOOTSTRAP_STYLE_SMALL_BUTTON}swPrpSubmit" type="button" id="reporticoPerformExpand" name="EXPANDCLEAR_{$EXPANDED_ITEM}" value="{$T_CLEAR}">
							<input class="{$BOOTSTRAP_STYLE_SMALL_BUTTON}swPrpSubmit" type="button" id="reporticoPerformExpand" name="EXPANDSELECTALL_{$EXPANDED_ITEM}" value="{$T_SELECTALL}">
							<input class="{$BOOTSTRAP_STYLE_SMALL_BUTTON}swPrpSubmit" type="button" id="returnFromExpand" name="EXPANDOK_{$EXPANDED_ITEM}" value="{$T_OK}">
{/if}
{if !$SHOW_EXPANDED}
{if !$REPORT_DESCRIPTION}
						&nbsp;<br>
{$T_DEFAULT_REPORT_DESCRIPTION}
{else}
						&nbsp;<br>
						{$REPORT_DESCRIPTION}
{/if}
{/if}
{/if}
