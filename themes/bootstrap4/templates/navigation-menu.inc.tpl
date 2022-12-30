{% autoescape false %}
<nav class="navbar navbar-expand-lg navbar-light bg-light reportico-top-menu">

	{# brand #}
	<a href='#' class='navbar-brand'>&emsp;{{ WIDGETS["navigation-menu"]["dropdown-menu"]["brand"] }}</a>
	<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#reportico-bootstrap-collapse-navmenu" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
		<span class="navbar-toggler-icon"></span>
	</button>

    {# Dropdown Menu Items #}
	<div class= 'collapse navbar-collapse' id='reportico-bootstrap-collapse-navmenu'>
		<ul class="navbar-nav mr-auto">
        {% if FLAGS["show-project-dropdown-menu"] %}
		<!--ul class='nav nav-tabs' style='margin-bottom: 0px'-->
		{% for menuitem in WIDGETS["navigation-menu"]["dropdown-menu"]["menu-items"] %}
			<li class='nav-item dropdown'>
                <a class='nav-link dropdown-toggle' data-toggle='dropdown' href='#' role="button" aria-haspopup="true" aria-expanded="false">{{ menuitem.title }}<span class='caret'></span></a>
				<div class='dropdown-menu reportico-dropdown'>
                    {% for subitem in menuitem.items %}
                        {{ subitem["file"] }}
					    {% if subitem.label %}
					        <a class='dropdown-item reportico-dropdown-item' href='{{ subitem.url }}'>{{ subitem.label }}</a>
					    {% endif %}
					{% endfor %}
				</div>
			</li>
		{% endfor %}
		</ul>
		{% endif %}


			<ul class="navbar-nav mr-auto d-flex justify-content-end">

				{# useraccesspanel #}
				{# savereport #}

				{% if PERMISSIONS["design"] %}

				    {# $designbutton only in prepare mode #}
				    {% if FLAGS["run-mode-prepare"] %}
				    {% if not PERMISSIONS["safe"] %}
				    <li class="nav-item">
						<input class='reportico-submit btn' type='submit' name='submit_design_mode' value='{{ T_DESIGN_REPORT }}'>
					</li>
				    {% endif %}

				    {# debuglevel  select box in admin mode #}
				    {% if PERMISSIONS["admin"] %}
				    <li>{{ WIDGETS["navigation-menu"]["debug-level"] }}</li>
				    {% endif %}
				    {% endif %}

				{% endif %}

				{# Project password prompt  only if project password in effect and user is not logged and user is not admin #}
				{% if not PERMISSIONS["access"] %}
				{% if FLAGS["project-password-error"] %}
				<div style='color: #ff0000;'>{{ T_PASSWORD_ERROR }}</div>
				{% endif %}
				<li class="nav-item">
					<div style='inline-block; margin-top: 6px'>{{ T_ENTER_PROJECT_PASSWORD }}<input type='password' name='project_password' value=''>
						{{ include ('button.inc.tpl', {
						    button_type : 'navbar-button',
						    button_style : 'outline-success',
						    button_label : WIDGETS["navigation-menu"]["login-button"]["label"],
						    button_name : WIDGETS["navigation-menu"]["login-button"]["name"],
						    button_id : WIDGETS["navigation-menu"]["login-button"]["id"]
						} ) }}
					</div>
				</li>
				{% endif %}

				{# Admin Menu button #}
				{% if PERMISSIONS["admin-page"] %}
				<li class="nav-item"> <a class='reportico-submit nav-link' href='{{ WIDGETS["navigation-menu"]["admin_menu_url"] }}'>{{ T_ADMIN_MENU }}</a> </li>
				{% endif %}
				{% if PERMISSIONS["admin"] %}
                {# Create Report Option #}
				<li class="nav-item"> <a class='reportico-submit nav-link' href='{{ WIDGETS["navigation-menu"]["create_report_url"] }}'>{{ T_CREATE_REPORT }}</a> </li>
				{% endif %}


				{# $projectmenubutton #}
				{% if PERMISSIONS["project-menu-page"] %}
				<li class="nav-item"> <a class='reportico-submit nav-link' href='{{ WIDGETS["navigation-menu"]["menu_url"] }}'>{{ T_PROJECT_MENU }}</a> </li>
				{% endif %}

				{# Logout button #}
				{% if PERMISSIONS["project"] %}
				{% if PERMISSIONS["admin"] or FLAGS["show-logout-button"] %}
				{{ include ('button.inc.tpl', {
				    button_type : 'navbar-button',
				    button_style : 'outline-success',
					button_label : WIDGETS["navigation-menu"]["logout-button"]["label"],
				    button_name : WIDGETS["navigation-menu"]["logout-button"]["name"],
				    button_id : WIDGETS["navigation-menu"]["logout-button"]["id"]
				} ) }}
				{% endif %}
				{% endif %}
			</ul>

	</div>
</nav>

{% endautoescape %}
