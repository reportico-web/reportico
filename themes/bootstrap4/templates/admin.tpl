{% autoescape false %}
{% include './header.inc.tpl' %}

<div id="reportico-container">

<!-- Begin Form -->
{{ WIDGETS["criteria-form"]["begin"] }}

    {# Header Banner #}
	<div class="container-fluid">
		<div style="float: right;">
			<img height="78px" src="{{ WIDGETS["admin-page"]["admin-header"]["logo"] }}">
			<div class="smallbanner">Version <a href="http://www.reportico.org/" target="_blank">{{ WIDGETS["admin-page"]["admin-header"]["version"] }}</a></div>
		</div>
		<div style="height: 78px">
			<h1 class="reportico-title-bar" style="text-align: center; padding-top: 30px; padding-left: 200px;">Reportico Administration Page</h1>
		</div>
	</div>

	{# Handle User logout or login ========================================= #}
	{# Prompt for login #}
	{% if PERMISSIONS["user-logged-in"] %}
	<div>
		{{ WIDGETS["admin-login"]["current-user"] }}
	</div>
	{% endif %}
	{% if not PERMISSIONS["design-fiddle"] %}
	<div class="container mt-3">
		<div class="flex-widget align-items-end" style="text-align: right">
			{{ include ('button.inc.tpl', {
			button_type : 'navbar-button',
			button_style : 'primary',
			button_label : WIDGETS["admin-page"]["admin-login"]["logout-button"]["label"],
			button_name : WIDGETS["admin-page"]["admin-login"]["logout-button"]["name"],
			button_id : WIDGETS["admin-page"]["admin-login"]["logout-button"]["id"]
			} ) }}
		</div>
		<div class="flex-row row align-content-center">
			<div class="" style="width: 35%; margin-left: 50%; text-align: right">
				{{ WIDGETS["admin-page"]["admin-login"]["instructions"] }}
				<br>
                <div style="margin: 10px 0px 4px 0px">
				{{ WIDGETS["admin-page"]["admin-login"]["login-prompt"] }}
				</div>
				<div style="margin: 10px 0px 4px 0px">
					{{ include ('button.inc.tpl', {
					button_type : 'navbar-button',
					button_style : 'primary',
					button_label : WIDGETS["admin-page"]["admin-login"]["login-submit"]["label"],
					button_name : WIDGETS["admin-page"]["admin-login"]["login-submit"]["name"],
					button_id : WIDGETS["admin-page"]["admin-login"]["login-submit"]["id"]
					} ) }}
			    </div>
				<br>
				{% if FLAGS["admin-password-error"] %}
				<span style="color: #ff0000">{{ WIDGETS["admin-page"]["admin-login"]["login-error"] }}</span>
				{% endif %}
			</div>
		</div>
	</div>
	{% endif %}

	{% if not FLAGS["show-set-admin-password"] %}
	<div class="flex-container">

	    {% if WIDGETS["admin-page"]["admin-menu"]["project-options"] %}
            {# Run Project Option #}
		    <div class="flex-item col" style="text-align: center; padding: 4px">
			    <div style="width: 230px; text-align: right; display: inline-block">{{ T_RUN_SUITE }}</div>
			    <select class='form-control reportico-drop-select-200' name='jump_to_menu_project'>
                    {{ WIDGETS["admin-page"]["admin-menu"]["project-options"] }}
			    </select>
			    {{ include ('button.inc.tpl', {
			    button_type : 'navbar-button',
			    button_style : 'success',
			    button_label : WIDGETS["admin-page"]["admin-menu"]["run-project-button"]["label"],
			    button_name : WIDGETS["admin-page"]["admin-menu"]["run-project-button"]["name"],
			    button_id : WIDGETS["admin-page"]["admin-menu"]["run-project-button"]["id"]
			    } ) }}
		    </div>
	    {% endif %}

		{% if PERMISSIONS["admin"] %}

	    {% if WIDGETS["admin-page"]["admin-menu"]["project-options"] %}
		{# Create Report Option #}
		<div class="flex-item col" style="text-align: center; padding: 4px">
			<div style="width: 230px; text-align: right; display:inline-block">{{ T_CREATE_REPORT }}</div>
			<select class='form-control reportico-drop-select-200' name='jump_to_create_report'>
				{{ WIDGETS["admin-page"]["admin-menu"]["project-options"] }}
			</select>
			{{ include ('button.inc.tpl', {
			button_type : 'navbar-button',
			button_style : 'outline-success',
			button_label : WIDGETS["admin-page"]["admin-menu"]["create-report-button"]["label"],
			button_name : WIDGETS["admin-page"]["admin-menu"]["create-report-button"]["name"],
			button_id : WIDGETS["admin-page"]["admin-menu"]["create-report-button"]["id"]
			} ) }}
		</div>

		{# Configure Project Option #}
		<div class="flex-item col align-content-center" style="text-align: center; padding: 4px">
			<div style="width: 230px; text-align: right; display:inline-block">{{ T_CONFIG_PARAM }}</div>
			<select class='form-control reportico-drop-select-200' name='jump_to_configure_project'>
			    {{ WIDGETS["admin-page"]["admin-menu"]["project-options"] }}
			</select>
			{{ include ('button.inc.tpl', {
			button_type : 'navbar-button',
			button_style : 'outline-success',
			button_label : WIDGETS["admin-page"]["admin-menu"]["configure-project-button"]["label"],
			button_name : WIDGETS["admin-page"]["admin-menu"]["configure-project-button"]["name"],
			button_id : WIDGETS["admin-page"]["admin-menu"]["configure-project-button"]["id"]
			} ) }}
		</div>

        {# Delete Project Option #}
		<div class="flex-item col align-content-center" style="text-align: center; padding: 4px">
			<div style="width: 230px; text-align: right; display:inline-block">{{ T_DELETE_PROJECT }}</div>
		    <select class='form-control reportico-drop-select-200' name='jump_to_delete_project'>
			    {{ WIDGETS["admin-page"]["admin-menu"]["project-options"] }}
            </select>
			{{ include ('button.inc.tpl', {
			button_type : 'navbar-button',
			button_style : 'danger',
			button_label : WIDGETS["admin-page"]["admin-menu"]["delete-project-button"]["label"],
			button_name : WIDGETS["admin-page"]["admin-menu"]["delete-project-button"]["name"],
			button_id : WIDGETS["admin-page"]["admin-menu"]["delete-project-button"]["id"]
			} ) }}
		</div>
	    {% endif %}


        {# Menu Items #}
        {% for menuitem in  WIDGETS["admin-page"]["admin-menu"]["project-menu-items"]  %}
		<div class="flex-item col align-content-center" style="text-align: center; padding: 4px">
                {% if menuitem.url %}
			    <div style='text-align:center;'>
				    <a href="{{menuitem.url}}"  class="reportico-menu-item-link">{{menuitem.label}}</a>
				</div>
				{% else %}
			         <div style='text-align:center;'>
						 {{ menuitem.label }}
			         </div>
                {% endif %}
        </div>
        {% endfor %}

		{# Delete Project Option #}
		<div class="flex-row row align-content-center" style="text-align: center; padding: 4px">
			<div style='text-align:center;'>
				{{ WIDGETS["admin-page"]["admin-menu"]["documentation"] }}
			</div>
		</div>

		{% endif %} {# Admin options #}

    </div>
	{% endif %}

	{# Set Admin Password on first use #}
	{% if FLAGS["show-set-admin-password"] %}
	<div style='text-align:center;'>

		{% if WIDGETS["admin-page"]["admin-password-set"]["set-admin-password-status-message"] %}
		<div style='color: #ff0000;'>
		{{ WIDGETS["admin-page"]["admin-password-set"]["set-admin-password-status-message"] }}
		</div>
		{% endif %}
	<br><br>
	{{ WIDGETS["admin-page"]["admin-password-set"]["set-admin-password-info"] }}
	<br>
	<br>
	{{ WIDGETS["admin-page"]["admin-password-set"]["set-admin-password-prompt"] }}
	<br>
	<input type='password' name='new_admin_password' value=''><br>
	<br>
	{{ WIDGETS["admin-page"]["admin-password-set"]["set-admin-password-reenter"] }}
	<br>
	<input type='password' name='new_admin_password2' value=''>
	<br><br>
	{{ WIDGETS["admin-page"]["admin-password-set"]["set-language-selector"] }}
	<br><br>
	{{ include ('button.inc.tpl', {
	button_type : 'navbar-button',
	button_style : 'primary',
	button_label : WIDGETS["admin-page"]["admin-password-set"]["set-admin-password-button"]["label"],
	button_name : WIDGETS["admin-page"]["admin-password-set"]["set-admin-password-button"]["name"],
	button_id : WIDGETS["admin-page"]["admin-password-set"]["set-admin-password-button"]["id"]
	} ) }}
	</div>
	{% endif %}

	{# Admin menu items #}
	{# WIDGETS["admin-page"]["admin-menu"]["complete"] #}


	{# General messages #}
    {% include 'message-error.inc.tpl' %}

{{ WIDGETS["criteria-form"]["end"] }}



</div>
{% include 'footer.inc.tpl' %}
{% endautoescape %}
