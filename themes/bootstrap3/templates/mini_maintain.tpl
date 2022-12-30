{% autoescape false %}
{{ WIDGETS["design-form"]["begin"] }}

	{% if WIDGETS["design-page"]["partial-section"] %}
	    <input type="hidden" name="partialMaintain" value="{{ WIDGETS["design-page"]["partial-section"] }}" />
	{% endif %}


    {% include 'error.tpl' %}
    {% include 'message-error.inc.tpl' %}

	{% if STATUSMSG|length>0 %} 
		<div class="alert alert-info" role="alert">
            {{ STATUSMSG|raw }}
        </div>
	{% endif %}
	{% if ERRORMSG|length>0 %} 
		<div class="alert alert-danger" role="alert">
            {{ ERRORMSG|raw }}
        </div>
	{% endif %}
	{{ CONTENT|raw }}

{{ WIDGETS["form"]["end"] }}
{% endautoescape %}
