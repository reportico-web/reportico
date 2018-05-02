                <TABLE class="reportico-prepare-crit-entryBox">
{% if CRITERIA_ITEMS is defined %}
{% for critno in CRITERIA_ITEMS %}
{% if critno.display_group and ( critno.display_group != critno.last_display_group )  %}
<tr class="reportico-toggleCriteriaDiv" id="reportico-toggleCriteriaDiv{{ critno.display_group_class }}">
<td colspan="3">
{% if critno.visible  %}
<a class="reportico-toggleCriteria" id="reportico-toggleCriteria{{ critno.display_group_class }}" href="javascript:toggleCriteria('{{ critno.display_group_class }}')">-</a>
{% else %}
<a class="reportico-toggleCriteria" id="reportico-toggleCriteria{{ critno.display_group_class }}" href="javascript:toggleCriteria('{{ critno.display_group_class }}')">+</a>
{% endif %}
{{ critno.display_group }}
</td>
</tr>
{% endif %}
{% if critno.hidden or critno.display_group %}
{% if critno.display_group %}
{% if critno.visible  %}
                    <tr class="reportico-prepare-crit-line  reportico-display-group-line displayGroup{{ critno.display_group_class }}" id="criteria_{{ critno.name }}" >
{% else %}
                    <tr class="reportico-prepare-crit-line  reportico-display-group-line displayGroup{{ critno.display_group_class }}" id="criteria_{{ critno.name }}" style="display:none">
{% endif %}
{% else %}
                    <tr class="reportico-prepare-crit-line" id="criteria_{{ critno.name }}" style="display:none">
{% endif %}
{% else %}
                    <tr class="reportico-prepare-crit-line" id="criteria_{{ critno.name }}">
{% endif %}
                        <td class='reportico-prepare-crit-title'>
{% if critno.tooltip %}
                            <a class='reportico_tooltip' data-toggle="tooltip" data-placement="right" title="{{ critno.tooltip }}">
                                    <span class="glyphicon glyphicon-question-sign"></span>
                            </a>
{% endif %}
                            {{ critno.title|raw }}
                        </td>
                        <td class="reportico-prepare-crit-sel">
                            {{ critno.entry|raw }}
                        </td>
                        <td class="reportico-prepare-crit-expand-sel">
{% if critno.expand %}
{% if AJAX_ENABLED %} 
                            <input class="reportico-prepare-crit-expand-button" id="reporticoPerformExpand" type="button" name="EXPAND_{{ critno.name }}" value="{{ T_EXPAND }}">
{% else %}
                            <input class="reportico-prepare-crit-expand-button" type="submit" name="EXPAND_{{ critno.name }}" value="{{ T_EXPAND }}">
{% endif %}
{% endif %}
                        </td>
                    </TR>
{% endfor %}
{% endif %}
                </TABLE>
