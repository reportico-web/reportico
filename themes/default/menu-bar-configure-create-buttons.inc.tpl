{% if (SHOW_DESIGN_BUTTON) %}
{% if not DEMO_MODE %}
            <li style="float:right">
			    <a class="reportico-ajax-link2" href="{{ CONFIGURE_PROJECT_URL|raw }}">{{ T_CONFIG_PROJECT }}</a>
            </li>
            <li style="float:right">
			    <a class="reportico-ajax-link2" href="{{ CREATE_REPORT_URL|raw }}">{{ T_CREATE_REPORT }}</a>
            </li>
{% endif %}
{% endif %}
