<!  -- Error and status message -->
{% include 'bootstrap3/message-error.inc.tpl' %}
{% include 'bootstrap3/message-status.inc.tpl' %}

{% if STATUSMSG|length==0 and ERRORMSG|length==0 %}
<p>
{% if SHOW_EXPANDED %}
{% include 'bootstrap3/prepare-criteria-expand-lookup.inc.tpl' %}
{% else %}
{% include 'bootstrap3/prepare-report-description.inc.tpl' %}
{% endif %}
{% endif %}
