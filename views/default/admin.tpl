{% autoescape false %}
{% include 'default/header.inc.tpl' %}
<div id="reportico_container">
    <script>
        reportico_criteria_items = [];
{% if CRITERIA_ITEMS is defined %}
{% for critno in CRITERIA_ITEMS %}
        reportico_criteria_items.push("{{ critno.name }}");
{% endfor %}
{% endif %}
    </script>

<FORM class="swMenuForm" name="topmenu" method="POST" action="{{ SCRIPT_SELF }}">
<div style="height: 78px" class="swAdminBanner">
<div style="float: right;">
<img height="78px" src="{{ ASSETS_PATH }}/images/reportico100.png"/>
<div class="smallbanner">Version <a href="http://www.reportico.org/" target="_blank">{{ REPORTICO_VERSION }}</a></div>
</div>
<div style="height: 78px">
<H1 class="swTitle" style="text-align: center; padding-top: 30px; padding-left: 200px;">{{ T_TITLE }}</H1>
</div>
</div>
<input type="hidden" name="reportico_session_name" value="{{ SESSION_ID }}" /> 
{% if SHOW_TOPMENU is defined %}
	<TABLE class="swPrpTopMenu">
		<TR>
			{% if (DB_LOGGEDON) %}
				{% if DBUSER|length >0 %} 
					<TD class="swPrpTopMenuCell">{{ T_LOGGED_ON_AS }} {{ DBUSER }}</TD>
				{% endif %}
				{% if DBUSER|length==0 %} 
					<TD style="width: 15%" class="swPrpTopMenuCell">&nbsp;</TD>
				{% endif %}
			{% endif %}
			{% if MAIN_MENU_URL|length>0 %} 
				<TD style="text-align:center">&nbsp;</TD>
			{% endif %}
			{% if SHOW_LOGOUT %}
				<TD width="15%" align="right" class="swPrpTopMenuCell">
					<input class="{{ BOOTSTRAP_STYLE_PRIMARY_BUTTON }}swPrpSubmit reporticoSubmit" type="submit" name="adminlogout" value="{{ T_LOGOFF }}">
				</TD>
			{% endif %}
			{% if SHOW_OPEN_LOGIN %}
				<TD width="50%"></TD>
					<TD width="98%" align="right" class="swPrpTopMenuCell">
						{{ T_OPEN_ADMIN_INSTRUCTIONS }}
						<br><input class="{{ BOOTSTRAP_STYLE_TEXTFIELD }} inline" style="display: none" type="password" name="admin_password" value="__OPENACCESS__">
						<input class="{{ BOOTSTRAP_STYLE_PRIMARY_BUTTON }}swPrpSubmit reporticoSubmit" type="submit" name="login" value="{{ T_OPEN_LOGIN }}">
						{% if ADMIN_PASSWORD_ERROR|length > 0 %}
							<div style="color: #ff0000;">{{ T_ADMIN_PASSWORD_ERROR }}</div>
						{% endif %}
					</TD>
					<TD width="15%" align="right" class="swPrpTopMenuCell">
					</TD>
			{% else %}
				{% if SHOW_LOGIN %}
					<TD width="50%"></TD>
					<TD width="35%" align="right" class="swPrpTopMenuCell">
						{{ T_ADMIN_INSTRUCTIONS }}
						<br><input style="display: inline not important" class="{{ BOOTSTRAP_STYLE_TEXTFIELD }}" type="password" name="admin_password" value="">
						<input class="{{ BOOTSTRAP_STYLE_PRIMARY_BUTTON }}swPrpSubmit reporticoSubmit" type="submit" name="login" value="{{ T_LOGIN }}">
						{% if ADMIN_PASSWORD_ERROR|length > 0 %}
							<div style="color: #ff0000;">{{ T_ADMIN_PASSWORD_ERROR }}</div>
						{% endif %}
					</TD>
					<TD width="15%" align="right" class="swPrpTopMenuCell">
					</TD>
				{% endif %}
			{% endif %}
		</TR>
	</TABLE>
{% endif %}
{% if SHOW_SET_ADMIN_PASSWORD %}
<div style="text-align:center;">
{% if SET_ADMIN_PASSWORD_ERROR|length > 0 %}
				<div style="color: #ff0000;">{{ SET_ADMIN_PASSWORD_ERROR }}</div>
{% endif %}
				<br>
				<br>
{{ T_SET_ADMIN_PASSWORD_INFO|raw }}
				<br>
{{ T_SET_ADMIN_PASSWORD_NOT_SET|raw }}
				<br>
{{ T_SET_ADMIN_PASSWORD_PROMPT|raw }}
				<br>
				<input type="password" name="new_admin_password" value=""><br>
				<br>
{{ T_SET_ADMIN_PASSWORD_REENTER|raw }} <br><input type="password" name="new_admin_password2" value=""><br>
<br>
<br>
{% if LANGUAGES|length > 0  %}
				<span style="text-align:right;width: 230px; display: inline-block">{{ T_CHOOSE_LANGUAGE }}</span>
				<select class="{{ BOOTSTRAP_STYLE_DROPDOWN }}swPrpDropSelectRegular" name="jump_to_language">
{% for menuitem in LANGUAGES %}

{% if menuitem.active  %}
				<OPTION label="{{ menuitem.label }}" selected value="{{ menuitem.value }}">{{ menuitem.label }}</OPTION>
{% else %}
				<OPTION label="{{ menuitem.label }}" value="{{ menuitem.value }}">{{ menuitem.label }}</OPTION>
{% endif %}
{% endfor %}
                </select>
{% endif %}
<br>
				<br>
				<input class="{{ BOOTSTRAP_STYLE_ADMIN_BUTTON }}swPrpSubmit reporticoSubmit" type="submit" name="submit_admin_password" value="{{ T_SET_ADMIN_PASSWORD }}">
				<br>
				
</div>
{% endif %}
{% if SHOW_REPORT_MENU %}
	<TABLE class="swMenu">
		<TR> <TD>&nbsp;</TD> </TR>
{% if not SHOW_SET_ADMIN_PASSWORD %}
{% if LANGUAGES|length > 0  %}
		<TR> 
			<TD class="swMenuItem" style="width: 30%"><span style="text-align:right;width: 230px; display: inline-block">{{ T_CHOOSE_LANGUAGE }}</span>
				<select class="{{ BOOTSTRAP_STYLE_DROPDOWN }}swPrpDropSelectRegular" name="jump_to_language">
{% for menuitem in LANGUAGES %}
{{ menuitem }}


{% if menuitem.active  %}
				<OPTION label="{{ menuitem.label }}" selected value="{{ menuitem.value }}">{{ menuitem.label }}</OPTION>
{% else %}
				<OPTION label="{{ menuitem.label }}" value="{{ menuitem.value }}">{{ menuitem.label }}</OPTION>
{% endif %}
{% endfor %}
				</select>
				<input class="{{ BOOTSTRAP_STYLE_ADMIN_BUTTON }}swMntButton reporticoSubmit" type="submit" name="submit_language" value="{{ T_GO }}">
			</TD>
		</TR>
{% endif %}

{% if PROJECT_ITEMS|length > 0  %}
		<TR> 
			<TD class="swMenuItem" style="width: 30%"><span style="text-align:right;width: 230px; display: inline-block">{{ T_RUN_SUITE }}</span>
				<select class="{{ BOOTSTRAP_STYLE_DROPDOWN }}swPrpDropSelectRegular" name="jump_to_menu_project">
{% for menuitem in PROJECT_ITEMS %}
				<OPTION label="{{ menuitem.label }}" value="{{ menuitem.label }}">{{ menuitem.label }}</OPTION>
{% endfor %}
				</select>
				<input class="{{ BOOTSTRAP_STYLE_GO_BUTTON }}swMntButton reporticoSubmit" type="submit" name="submit_menu_project" value="{{ T_GO }}">
			</TD>
		</TR>
{% endif %}
{% endif %}

{% if PROJECT_ITEMS|length > 0  %}
		<TR> 
			<TD class="swMenuItem" style="width: 30%"><span style="text-align:right;width: 230px; display: inline-block">{{ T_CREATE_REPORT }}</span>
				<select class="{{ BOOTSTRAP_STYLE_DROPDOWN }}swPrpDropSelectRegular" name="jump_to_design_project">
{% for menuitem in PROJECT_ITEMS %}
				<OPTION label="{{ menuitem.label }}" value="{{ menuitem.label }}">{{ menuitem.label }}</OPTION>
{% endfor %}
				</select>
				<input class="{{ BOOTSTRAP_STYLE_ADMIN_BUTTON }}swMntButton reporticoSubmit" type="submit" name="submit_design_project" value="{{ T_GO }}">
			</TD>
		</TR>
		<TR> 
			<TD class="swMenuItem" style="width: 30%"><span style="text-align:right;width: 230px; display: inline-block">{{ T_CONFIG_PARAM }}</span>
				<select class="{{ BOOTSTRAP_STYLE_DROPDOWN }}swPrpDropSelectRegular" name="jump_to_configure_project">
					{% for menuitem in PROJECT_ITEMS %}
							<OPTION label="{{ menuitem.label }}" value="{{ menuitem.label }}">{{ menuitem.label }}</OPTION>
					{% endfor %}
				</select>
				<input class="{{ BOOTSTRAP_STYLE_ADMIN_BUTTON }}swMntButton reporticoSubmit" type="submit" name="submit_configure_project" value="{{ T_GO }}">
			</TD>
		</TR>
		<TR> 
			<TD class="swMenuItem" style="width: 30%"><span style="text-align:right;width: 230px; display: inline-block">{{ T_DELETE_PROJECT }}</span>
				<select class="{{ BOOTSTRAP_STYLE_DROPDOWN }}swPrpDropSelectRegular" name="jump_to_delete_project">
					{% for menuitem in PROJECT_ITEMS %}
							<OPTION label="{{ menuitem.label }}" value="{{ menuitem.label }}">{{ menuitem.label }}</OPTION>
					{% endfor %}
				</select>
				<input class="{{ BOOTSTRAP_STYLE_ADMIN_BUTTON }}swMntButton reporticoSubmit" type="submit" name="submit_delete_project" value="{{ T_GO }}">
			</TD>
		</TR>
{% endif %}
{% for menuitem in MENU_ITEMS %}
		<TR> 
			<TD class="swMenuItem">
{% if menuitem.label == "BLANKLINE" %}
                                &nbsp;
{% else %}
{% if menuitem.label == "LINE" %}
                                <hr>
{% else %}
                                <a class="swMenuItemLink" href="{{ menuitem.url }}">{{ menuitem.label }}</a>
{% endif %}
{% endif %}
			</TD>
		</TR>
{% endfor %}
		
		<TR> <TD>&nbsp;</TD> </TR>
		<TR> 
			<TD class="swMenuItem" style="width: 30%"><a target="_blank" href="{{ REPORTICO_SITE }}documentation/{{ REPORTICO_VERSION }}">{{ T_DOCUMENTATION }}</a>
			</TD>
		</TR>
	</TABLE>
{% else %}
	<TABLE class="swMenu">
		<TR> <TD>&nbsp;</TD> </TR>
{% if not SHOW_SET_ADMIN_PASSWORD %}
{% if LANGUAGES|length > 1  %}
		<TR> 
			<TD class="swMenuItem" style="width: 30%"><span style="text-align:right;width: 230px; display: inline-block">{{ T_CHOOSE_LANGUAGE }}</span>
				<select class="{{ BOOTSTRAP_STYLE_DROPDOWN }}swPrpDropSelectRegular" name="jump_to_language">
{% for menuitem in LANGUAGES %}

	{% if menuitem.active  %}
		<OPTION label="{{ menuitem.label }}" selected value="{{ menuitem.value }}">{{ menuitem.label }}</OPTION>
	{% else %}
		<OPTION label="{{ menuitem.label }}" value="{{ menuitem.value }}">{{ menuitem.label }}</OPTION>
	{% endif %}
{% endfor %}
				</select>
				<input class="{{ BOOTSTRAP_STYLE_ADMIN_BUTTON }}swMntButton reporticoSubmit" type="submit" name="submit_language" value="{{ T_GO }}">
			</TD>
		</TR>
{% endif %}
{% if PROJECT_ITEMS|length > 0  %}
		<TR> 
			<TD class="swMenuItem" style="width: 30%"><span style="text-align:right;width: 230px; display: inline-block">{{ T_RUN_SUITE }}</span>
				<select class="{{ BOOTSTRAP_STYLE_DROPDOWN }}swPrpDropSelectRegular" name="jump_to_menu_project">
{% for menuitem in PROJECT_ITEMS %}
				<OPTION label="{{ menuitem.label }}" value="{{ menuitem.label }}">{{ menuitem.label }}</OPTION>
{% endfor %}
				</select>
				<input class="{{ BOOTSTRAP_STYLE_ADMIN_BUTTON }}swMntButton reporticoSubmit" type="submit" name="submit_menu_project" value="{{ T_GO }}">
			</TD>
		</TR>
{% endif %}
{% endif %}
		<TR> <TD>&nbsp;</TD> </TR>
	</TABLE>
{% endif %}

{% include 'default/message-error.inc.tpl' %}
</FORM>



</div>
{% include 'default/footer.inc.tpl' %}
{% endautoescape %}
