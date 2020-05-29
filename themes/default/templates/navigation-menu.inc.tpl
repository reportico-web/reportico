{% autoescape false %}


<div style='margin-bottom: 0px; navstyle' class='navbar navbar-default' role='navigation'>
	<div class='container' style='width: 100%'>
		<button type='button' class='navbar-toggle' data-toggle='collapse' data-target='#reportico-bootstrap-collapse-navmenu'>
			<span class='icon-bar'></span>
			<span class='icon-bar'></span>
			<span class='icon-bar'></span>
		</button>

        {# brand #}
		<a href='#' class='navbar-brand'>{{ WIDGETS["navigation-menu"]["dropdown-menu"]["brand"] }}</a>

		{# Dropdown Menu Items #}
        {% if FLAGS["show-project-dropdown-menu"] %}
		<ul class='nav navbar-nav' style='margin-bottom: 0px'>
		{% for menuitem in WIDGETS["navigation-menu"]["dropdown-menu"]["menu-items"] %}
			<li class='dropdown'>
                <a class='dropdown-toggle' data-toggle='dropdown' href='#'>{{ menuitem.title }}<span class='caret'></span></a>
				<ul class='dropdown-menu reportico-dropdown'>
                    {% for subitem in menuitem.items %}
                        {{ subitem["file"] }}
					    {% if subitem.label %}
					        <li><a class='reportico-dropdown-item' href='{{ subitem.url }}'>{{ subitem.label }}</a></li>
					    {% endif %}
					    {% endfor %}
					</ul>
				</li>
		{% endfor %}
		</ul>
		{% endif %}


		<div class= 'nav-collapse collapse in' id='reportico-bootstrap-collapse-navmenu'>
			<ul style='navstyle' class= 'nav navbar-nav pull-right navbar-right'>

				{# useraccesspanel #}
				{# $savereport #}

				{% if PERMISSIONS["design"] %}

				    {# $designbutton only in prepare mode #}
				    {% if FLAGS["run-mode-prepare"] %}
				    {% if not PERMISSIONS["safe"] %}
				    <li><input style='margin-top: 7px' class='reportico-submit btn' type='submit' name='submit_design_mode' value='{{ T_DESIGN_REPORT }}'></li>
				    {% endif %}

				    {# debuglevel  select box in admin mode #}
				    {% if PERMISSIONS["admin"] %}
				<li>{{ WIDGETS["navigation-menu"]["debug-level"] }}</li>
				    {% endif %}
				    {% endif %}

				{% endif %}

				{# Project password prompt  only if project password in effect and user is not logged and user is not admin #}
				{% if not PERMISSIONS["project"] %}
				{% if FLAGS["project-password-error"] %}
				<div style='color: #ff0000;'>{{ T_PASSWORD_ERROR }}</div>
				{% endif %}
				<li>
					<div style='inline-block; margin-top: 6px'>{{ T_ENTER_PROJECT_PASSWORD }}<input type='password' name='project_password' value=''>
						<input class='span2 reportico-submit btn btn' type='submit' name='login' value='{{ T_LOGIN }}'>
					</div>
				</li>
				{% endif %}

				{# Admin Menu button #}
				{% if PERMISSIONS["admin-page"] %}
				<li> <a class='reportico-submit' href='{{ WIDGETS["navigation-menu"]["admin_menu_url"] }}'>{{ T_ADMIN_MENU }}</a> </li>
				{% endif %}


				{# $projectmenubutton #}
				{% if PERMISSIONS["project-menu-page"] %}
				<li> <a class='reportico-submit' href='{{ WIDGETS["navigation-menu"]["menu_url"] }}'>{{ T_PROJECT_MENU }}</a> </li>
				{% endif %}

				{# Logout button #}
				{% if PERMISSIONS["project"] %}
				{% if PERMISSIONS["admin"] or FLAGS["show-logout-button"] %}
				<li> {{ WIDGETS["navigation-menu"]["logout-button"]["widget"] }} </li>
				{% endif %}
				{% endif %}
			</ul>
		</div>
	</div>

</div>

{% endautoescape %}
