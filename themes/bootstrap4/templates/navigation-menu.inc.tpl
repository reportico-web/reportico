{% autoescape false %}

<!--nav-- class="navbar navbar-expand-lg navbar-light bg-light">
	<a class="navbar-brand" href="#">Navbar</a>
	<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
		<span class="navbar-toggler-icon"></span>
	</button>

	<div class="collapse navbar-collapse" id="navbarSupportedContent">
		<ul class="navbar-nav mr-auto">
			<li class="nav-item active">
				<a class="nav-link" href="#">Home <span class="sr-only">(current)</span></a>
			</li>
			<li class="nav-item">
				<a class="nav-link" href="#">Link</a>
			</li>
			<li class="nav-item"> <a class='nav-link' href='{{ WIDGETS["navigation-menu"]["admin_menu_url"] }}'>{{ T_ADMIN_MENU }}</a> </li>
			<li class="nav-item dropdown">
				<a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
					Dropdown
				</a>
				<div class="dropdown-menu" aria-labelledby="navbarDropdown">
					<a class="dropdown-item" href="#">Action</a>
					<a class="dropdown-item" href="#">Another action</a>
					<div class="dropdown-divider"></div>
					<a class="dropdown-item" href="#">Something else here</a>
				</div>
			</li>
			<li class="nav-item">
				<a class="nav-link disabled" href="#">Disabled</a>
			</li>
		</ul>
		<form class="form-inline my-2 my-lg-0">
			<input class="form-control mr-sm-2" type="search" placeholder="Search" aria-label="Search">
			<button class="btn btn-outline-success my-2 my-sm-0" type="submit">Search</button>
		</form>
	</div>
</nav-->

<nav class="navbar navbar-expand-lg navbar-light bg-light">

	{# brand #}
	<a href='#' class='navbar-brand'>{{ WIDGETS["navigation-menu"]["dropdown-menu"]["brand"] }}</a>
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
				{% if not PERMISSIONS["project"] %}
				{% if FLAGS["project-password-error"] %}
				<div style='color: #ff0000;'>{{ T_PASSWORD_ERROR }}</div>
				{% endif %}
				<li class="nav-item">
					<div style='inline-block; margin-top: 6px'>{{ T_ENTER_PROJECT_PASSWORD }}<input type='password' name='project_password' value=''>
						<input class='span2 reportico-submit btn btn' type='submit' name='login' value='{{ T_LOGIN }}'>
					</div>
				</li>
				{% endif %}

				{# Admin Menu button #}
				{% if PERMISSIONS["admin-page"] %}
				<li class="nav-item"> <a class='reportico-submit nav-link' href='{{ WIDGETS["navigation-menu"]["admin_menu_url"] }}'>{{ T_ADMIN_MENU }}</a> </li>
				{% endif %}


				{# $projectmenubutton #}
				{% if PERMISSIONS["project-menu-page"] %}
				<li class="nav-item"> <a class='reportico-submit nav-link' href='{{ WIDGETS["navigation-menu"]["menu_url"] }}'>{{ T_PROJECT_MENU }}</a> </li>
				{% endif %}

				{# Logout button #}
				{% if PERMISSIONS["admin"] or FLAGS["show-logout-button"] %}
				<li class="nav-item"> {{ WIDGETS["navigation-menu"]["logout-button"] }} </li>
				{% endif %}
			</ul>

	</div>
</nav>

{% endautoescape %}
