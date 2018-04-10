{% autoescape false %}
<!  -- Error and status message -->
{% include 'message-error.inc.tpl' %}
{% include 'message-status.inc.tpl' %}

{% if STATUSMSG|length==0 and ERRORMSG|length==0 %}
<p>
{% if SHOW_EXPANDED %}
{% include 'prepare-criteria-expand-lookup.inc.tpl' %}
{% else %}
{% include 'prepare-report-description.inc.tpl' %}
{% endif %}
{% endif %}
{% endautoescape%}
