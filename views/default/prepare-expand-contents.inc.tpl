{% autoescape false %}
<!  -- Error and status message -->
{% include 'default/message-error.inc.tpl' %}
{% include 'default/message-status.inc.tpl' %}

{% if STATUSMSG|length==0 and ERRORMSG|length==0 %}
<p>
{% if SHOW_EXPANDED %}
{% include 'default/prepare-criteria-expand-lookup.inc.tpl' %}
{% else %}
{% include 'default/prepare-report-description.inc.tpl' %}
{% endif %}
{% endif %}
{% endautoescape%}
