{% autoescape false %}
{% include 'header.inc.tpl' %}

<div id="reportico-container">

	<!-- Begin Form -->
	{{ WIDGETS["criteria-form"]["begin"] }}

	<!-- Menu Bar -->
	{% include 'navigation-menu.inc.tpl' %}

	<!-- Report Title -->
    <h2 class="flex-widget" style="width: 100%;border-bottom: solid 1px #aaaaaa">
	{{ WIDGETS["title"]["title"] }}
    </h2>

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
            <input type='submit'
                class='flex-widget btn btn-sm btn-outline-secondary reportico-edit-link'
                title='{{ WIDGETS["run-report"]["title"] }}'
                id='{{ WIDGETS["run-report"]["id"] }}'
                name='{{ WIDGETS["run-report"]["name"] }}'
                value='{{ WIDGETS["run-report"]["label"] }}'
            >
			{% if PERMISSIONS["save"] %}
            <input type='submit'
                class='flex-widget btn btn-sm btn-outline-secondary reportico-edit-link'
                title='{{ WIDGETS["new-report"]["title"] }}'
                id='{{ WIDGETS["new-report"]["id"] }}'
                name='{{ WIDGETS["new-report"]["name"] }}'
                value='{{ WIDGETS["new-report"]["label"] }}'
            >
            <input type='submit'
                class='flex-widget btn btn-sm btn-outline-danger reportico-edit-link'
                title='{{ WIDGETS["delete-report"]["title"] }}'
                id='{{ WIDGETS["delete-report"]["id"] }}'
                name='{{ WIDGETS["delete-report"]["name"] }}'
                value='{{ WIDGETS["delete-report"]["label"] }}'
            >
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
	{# WIDGETS["powered-by-banner"] #}

</div>

{% include 'footer.inc.tpl' %}
{% endautoescape %}
