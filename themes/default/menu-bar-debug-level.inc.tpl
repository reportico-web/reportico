{% if OUTPUT_SHOW_DEBUG %}
{% if SHOW_DESIGN_BUTTON %}
                <li>
                <div style="margin: 6px 8px 0px 8px">
                {{ T_DEBUG_LEVEL }}
                <SELECT class="span2 {{ BOOTSTRAP_STYLE_DROPDOWN }}" style="margin-bottom: 1px; display:inline; width: auto" name="debug_mode">';
                    <OPTION {{ DEBUG_NONE }} label="None" value="0">{{ T_DEBUG_NONE }}</OPTION>
                    <OPTION {{ DEBUG_LOW }} label="Low" value="1">{{ T_DEBUG_LOW }}</OPTION>
                    <OPTION {{ DEBUG_MEDIUM }} label="Medium" value="2">{{ T_DEBUG_MEDIUM }}</OPTION>
                    <OPTION {{ DEBUG_HIGH }} label="High" value="3">{{ T_DEBUG_HIGH }}</OPTION>
                </SELECT>
                </div>
                </li>
{% endif %}
{% endif %}
