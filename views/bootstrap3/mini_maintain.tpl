<FORM class="swMiniMntForm" name="topmenu" method="POST" action="{{ SCRIPT_SELF }}">
	{% if (PARTIALMAINTAIN) %} 
	    <input type="hidden" name="partialMaintain" value="{{ PARTIALMAINTAIN }}" />
	{% endif %}
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
	<input type="hidden" name="reportico_session_name" value="{{ SESSION_ID }}" />
				{{ CONTENT|raw }}
</FORM>
