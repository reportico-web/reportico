                <TABLE class="swPrpCritEntryBox">
{if isset($CRITERIA_ITEMS)}
{section name=critno loop=$CRITERIA_ITEMS}
{if $CRITERIA_ITEMS[critno].display_group && ( $CRITERIA_ITEMS[critno].display_group != $CRITERIA_ITEMS[critno].last_display_group ) }
<tr class="swToggleCriteriaDiv" id="swToggleCriteriaDiv{$CRITERIA_ITEMS[critno].display_group_class}">
<td colspan="3">
{if $CRITERIA_ITEMS[critno].visible }
<a class="swToggleCriteria" id="swToggleCriteria{$CRITERIA_ITEMS[critno].display_group_class}" href="javascript:toggleCriteria('{$CRITERIA_ITEMS[critno].display_group_class}')">-</a>
{else}
<a class="swToggleCriteria" id="swToggleCriteria{$CRITERIA_ITEMS[critno].display_group_class}" href="javascript:toggleCriteria('{$CRITERIA_ITEMS[critno].display_group_class}')">+</a>
{/if}
{$CRITERIA_ITEMS[critno].display_group}
</td>
</tr>
{/if}
{if $CRITERIA_ITEMS[critno].hidden || $CRITERIA_ITEMS[critno].display_group }
{if $CRITERIA_ITEMS[critno].display_group }
{if $CRITERIA_ITEMS[critno].visible }
                    <tr class="swPrpCritLine  swDisplayGroupLine displayGroup{$CRITERIA_ITEMS[critno].display_group_class}" id="criteria_{$CRITERIA_ITEMS[critno].name}" >
{else}
                    <tr class="swPrpCritLine  swDisplayGroupLine displayGroup{$CRITERIA_ITEMS[critno].display_group_class}" id="criteria_{$CRITERIA_ITEMS[critno].name}" style="display:none">
{/if}
{else}
                    <tr class="swPrpCritLine" id="criteria_{$CRITERIA_ITEMS[critno].name}" style="display:none">
{/if}
{else}
                    <tr class="swPrpCritLine" id="criteria_{$CRITERIA_ITEMS[critno].name}">
{/if}
                        <td class='swPrpCritTitle'>
{if $CRITERIA_ITEMS[critno].tooltip }
                            <a class='reportico_tooltip' data-toggle="tooltip" data-placement="right" title="{$CRITERIA_ITEMS[critno].tooltip}">
                                    <span class="glyphicon glyphicon-question-sign"></span>
                            </a>
{/if}
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
