{% if not REPORT_DESCRIPTION %}
{{ T_DEFAULT_REPORT_DESCRIPTION|raw }}
{% else %}
&nbsp;<br>{{ REPORT_DESCRIPTION|raw }}
{% endif %}
