	<TABLE class="swPrpCritBox" cellpadding="0">
<!---->
		<TR id="swPrpCriteriaBody">
			<TD class="swPrpCritEntry">
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
    				<input type="submit" class="prepareMiniMaintain swMiniMaintain" style="margin-right: 30px" title="{$T_EDIT} {$T_EDITCRITERIA}" id="submit_mainquercrit" value="{$T_EDITCRITERIA}" name="mainquercrit_ANY">
{else}
    				<input type="submit" class="{$BOOTSTRAP_STYLE_TOOLBAR_BUTTON}prepareMiniMaintain swMiniMaintain" style="margin-right: 30px" title="{$T_EDIT} {$T_EDITCRITERIA}" id="submit_mainquercrit" value="{$T_EDITCRITERIA}" name="mainquercrit_ANY">
{/if}
</div>
{/if}
                    &nbsp;
			</div>

                <TABLE class="swPrpCritEntryBox">
{php}
$loopct = 0;
{/php}
{if isset($CRITERIA_ITEMS)}
{section name=critno loop=$CRITERIA_ITEMS}
{if $CRITERIA_ITEMS[critno].display_group && ( $CRITERIA_ITEMS[critno].display_group != $CRITERIA_ITEMS[critno].last_display_group ) }
<tr id="swToggleCriteriaDiv{$CRITERIA_ITEMS[critno].display_group_class}">
<td colspan="3">
<a class="swToggleCriteria" id="swToggleCriteria{$CRITERIA_ITEMS[critno].display_group_class}" href="javascript:toggleCriteria('{$CRITERIA_ITEMS[critno].display_group_class}')">+</a>
{$CRITERIA_ITEMS[critno].display_group}
</td>
</tr>
{/if}
{if $CRITERIA_ITEMS[critno].hidden || $CRITERIA_ITEMS[critno].display_group }
{if $CRITERIA_ITEMS[critno].display_group }
                    <tr class="swPrpCritLine  swDisplayGroupLine displayGroup{$CRITERIA_ITEMS[critno].display_group_class}" id="criteria_{$CRITERIA_ITEMS[critno].name}" style="display:none">
{else}
                    <tr class="swPrpCritLine" id="criteria_{$CRITERIA_ITEMS[critno].name}" style="display:none">
{/if}
{else}
                    <tr class="swPrpCritLine" id="criteria_{$CRITERIA_ITEMS[critno].name}">
{/if}
                        <td class='swPrpCritTitle'>
{if $CRITERIA_ITEMS[critno].tooltip }
{if $BOOTSTRAP_STYLES}
{if $BOOTSTRAP_STYLES == "3" || $BOOTSTRAP_STYLES == "joomla3" }
                            <a class='reportico_tooltip' data-toggle="tooltip" data-placement="right" title="{$CRITERIA_ITEMS[critno].tooltip}">
                                    <span class="glyphicon glyphicon-question-sign"></span>
                            </a>
{else}
                            <a class='reportico_tooltip' data-toggle="tooltip" data-placement="right" title="{$CRITERIA_ITEMS[critno].tooltip}">
                                    <span class="icon-question-sign"></span>
                            </a>
{/if}
{else}
                            <div class="swHelpIcon" alt="tab" title = "{$CRITERIA_ITEMS[critno].tooltip}"><img class="swHelpIcon"></img></div>
{/if}
{/if}
{php}
$itemval = str_pad($loopct, 4, '0', STR_PAD_LEFT);
$this->assign('criterianumber', $itemval);
$loopct++;
{/php}
                            {$CRITERIA_ITEMS[critno].title}
                        </td>
                        <td class="swPrpCritSel">
                            {$CRITERIA_ITEMS[critno].entry}
                        </td>
                        <td class="swPrpCritExpandSel">
{if $CRITERIA_ITEMS[critno].expand}
{if $AJAX_ENABLED} 
                            <input class="swPrpCritExpandButton" id="reporticoPerformExpand" type="button" name="EXPAND_{$CRITERIA_ITEMS[critno].name}" value="{$T_EXPAND}">
{else}
                            <input class="swPrpCritExpandButton" type="submit" name="EXPAND_{$CRITERIA_ITEMS[critno].name}" value="{$T_EXPAND}">
{/if}
{/if}
                        </td>
                    </TR>
{/section}
{/if}
                </TABLE>
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
			</td>
			<TD class="swPrpExpand">
				<TABLE class="swPrpExpandBox">
					<TR class="swPrpExpandRow">
						<TD id="swPrpExpandCell" rowspan="0" valign="top">
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
<p>
{if $SHOW_EXPANDED}
							{$T_SEARCH} {$EXPANDED_TITLE} :<br><input  id="expandsearch" type="text" class="{$BOOTSTRAP_STYLE_TEXTFIELD}" name="expand_value" style="width: 50%;display: inline" size="30" value="{$EXPANDED_SEARCH_VALUE}"</input>
									<input id="reporticoSearchExpand" class="{$BOOTSTRAP_STYLE_SMALL_BUTTON}swPrpSubmit" style="margin-bottom: 2px" type="submit" name="EXPANDSEARCH_{$EXPANDED_ITEM}" value="Search"><br>

{$CONTENT}
							<br>
							<input class="{$BOOTSTRAP_STYLE_SMALL_BUTTON}swPrpSubmit" type="submit" name="EXPANDCLEAR_{$EXPANDED_ITEM}" value="Clear">
							<input class="{$BOOTSTRAP_STYLE_SMALL_BUTTON}swPrpSubmit" type="submit" name="EXPANDSELECTALL_{$EXPANDED_ITEM}" value="Select All">
							<input class="{$BOOTSTRAP_STYLE_SMALL_BUTTON}swPrpSubmit" type="submit" name="EXPANDOK_{$EXPANDED_ITEM}" value="OK">
{/if}
{if !$SHOW_EXPANDED}
{if !$REPORT_DESCRIPTION}
{$T_DEFAULT_REPORT_DESCRIPTION}
{else}
						&nbsp;<br>
						{$REPORT_DESCRIPTION}
{/if}
{/if}
{/if}
						</TD>
					</TR>
				</TABLE>
			</TD>
		</TR>
			</TABLE>

