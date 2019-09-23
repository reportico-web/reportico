{% autoescape false %}
{% include 'header.inc.tpl' %}

<div id="reportico-container">

	<!-- Begin Form -->
	{{ WIDGETS["criteria-form"]["begin"] }}

	<!-- Menu Bar -->
	{% include 'navigation-menu.inc.tpl' %}

	<!-- Report Title -->
	{{ WIDGETS["title"]["widget"] }}

	{% if WIDGETS["status-message-block"]["status"] %}
	<div class="reportico-status-block">
		{{ WIDGETS["status-message-block"]["status"] }}
	</div>
	{% endif %}
	{{ WIDGETS["status-message-block"]["error"] }}
	{{ WIDGETS["status-message-block"]["debug"] }}

	<div>
		<div class="col-sm-6 col-md-6 col-xl-6 col-lg-6 col-xs-6">
			{% if PERMISSIONS["save"] %}
			    {{ WIDGETS["save-report"] }}
			{% endif %}
			{{ WIDGETS["run-report"]["widget"] }}
			{% if PERMISSIONS["save"] %}
			    {{ WIDGETS["new-report"]["widget"] }}
			    {{ WIDGETS["delete-report"]["widget"] }}
			{% endif %}
		</div>
		<!--div class="col-sm-6 col-md-6 col-xl-6 col-lg-6 col-xs-6">
			{{ WIDGETS["title"] }}
		</div-->
	</div>

	<TABLE class="reportico-maintain-main-box" cellspacing="0" cellpadding="0">
		<TR>
			<TD>
{{ CONTENT }}
			</TD>
		</TR>
	</TABLE>

	{{ WIDGETS["criteria-form"]["end"] }}
	{{ WIDGETS["powered-by-banner"] }}

</div>

{% include 'footer.inc.tpl' %}
{% endautoescape %}
