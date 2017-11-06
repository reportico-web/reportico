                <TABLE class="swPrpCritEntryBox">
{% if CRITERIA_ITEMS is defined %}
{% for critno in CRITERIA_ITEMS %}
{% if critno.display_group and ( critno.display_group != critno.last_display_group )  %}
<tr class="swToggleCriteriaDiv" id="swToggleCriteriaDiv{{ critno.display_group_class }}">
<td colspan="3">
{% if critno.visible  %}
<a class="swToggleCriteria" id="swToggleCriteria{{ critno.display_group_class }}" href="javascript:toggleCriteria('{{ critno.display_group_class }}')">-</a>
{% else %}
<a class="swToggleCriteria" id="swToggleCriteria{{ critno.display_group_class }}" href="javascript:toggleCriteria('{{ critno.display_group_class }}')">+</a>
{% endif %}
{{ critno.display_group }}
</td>
</tr>
{% endif %}
{% if critno.hidden or critno.display_group %}
{% if critno.display_group %}
{% if critno.visible  %}
                    <tr class="swPrpCritLine  swDisplayGroupLine displayGroup{{ critno.display_group_class }}" id="criteria_{{ critno.name }}" >
{% else %}
                    <tr class="swPrpCritLine  swDisplayGroupLine displayGroup{{ critno.display_group_class }}" id="criteria_{{ critno.name }}" style="display:none">
{% endif %}
{% else %}
                    <tr class="swPrpCritLine" id="criteria_{{ critno.name }}" style="display:none">
{% endif %}
{% else %}
                    <tr class="swPrpCritLine" id="criteria_{{ critno.name }}">
{% endif %}
                        <td class='swPrpCritTitle'>
{% if critno.tooltip %}
                            <a class='reportico_tooltip' data-toggle="tooltip" data-placement="right" title="{{ critno.tooltip }}">
                                    <span class="glyphicon glyphicon-question-sign"></span>
                            </a>
{% endif %}
                            {{ critno.title|raw }}
                        </td>
                        <td class="swPrpCritSel">
                            {{ critno.entry|raw }}
                        </td>
                        <td class="swPrpCritExpandSel">
{% if critno.expand %}
{% if AJAX_ENABLED %} 
                            <input class="swPrpCritExpandButton" id="reporticoPerformExpand" type="button" name="EXPAND_{{ critno.name }}" value="{{ T_EXPAND }}">
{% else %}
                            <input class="swPrpCritExpandButton" type="submit" name="EXPAND_{{ critno.name }}" value="{{ T_EXPAND }}">
{% endif %}
{% endif %}
                        </td>
                    </TR>
{% endfor %}
{% endif %}
                </TABLE>
