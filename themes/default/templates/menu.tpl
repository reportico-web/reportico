{% autoescape false %}
{% include 'header.inc.tpl' %}
<div id="reportico-container">
<FORM class="reportico-menuForm" name="topmenu" method="POST" action="{{ SCRIPT_SELF }}">
<input type="hidden" name="reportico_session_name" value="{{ SESSION_ID }}" /> 

<!--  BOOTSTRAP VERSION -->
{% if SHOW_HIDE_NAVIGATION_MENU == "show" or SHOW_HIDE_DROPDOWN_MENU == "show" %}
    <div class="navbar navbar-default" role="navigation">
{% else %}
    <div style="display:none" class="navbar navbar-default" role="navigation">
{% endif %}
    <!--div class="navbar navbar-default navbar-static-top" role="navigation"-->
        <div class="container" style="width: 100%">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#reportico-bootstrap-collapse">
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
{% include 'dropdown-brand.inc.tpl' %}
            <div class= "nav-collapse collapse in" id="reportico-bootstrap-collapse">
{% include 'dropdown-menu.inc.tpl' %}
{% if SHOW_HIDE_NAVIGATION_MENU == "show"  %}
            <ul class= "nav navbar-nav pull-right navbar-right">
{% else %}
            <ul style="display:none" class= "nav navbar-nav pull-right navbar-right">
{% endif %}
{% if SHOW_TOPMENU %}
{% include 'menu-bar-logout.inc.tpl' %}
{% include 'menu-bar-project-password.inc.tpl' %}
{% endif %}
{% include 'menu-bar-admin-menu.inc.tpl' %}
{% include 'menu-bar-configure-create-buttons.inc.tpl' %}
</div>
</ul>
        </div>
</div>
<!-- BOOTSTRAP VERSION -->
<H1 class="reportico-title-bar">{{ TITLE }}</H1>
	<TABLE class="reportico-menu">
		<TR> <TD>&nbsp;</TD> </TR>
{% for menuitem in MENU_ITEMS %}
		<TR> 
			<TD class="reportico-menuItem">
{% if  menuitem.label == "TEXT" %}
				{{ menuitem.url|raw }}
{% else %}
{% if  menuitem.label == "BLANKLINE" %}
				&nbsp;
{% else %}
{% if  menuitem.label == "LINE" %}
				<hr>
{% else %}
				<a class="reportico-menu-item-link" href="{{ menuitem.url }}">{{ menuitem.label }}</a>
{% endif %}
{% endif %}
{% endif %}
			</TD>
		</TR>
{% endfor %}
		
		<TR> <TD>&nbsp;</TD> </TR>
	</TABLE>

{% if ERRORMSG|length>0 %} 
			<TABLE class="reportico-error-box">
				<TR>
					<TD>{{ ERRORMSG }}</TD>
				</TR>
			</TABLE>
{% endif %}
</FORM>
{% include 'reportico-banner.inc.tpl' %}
</div>
{% include 'footer.inc.tpl' %}
{% endautoescape %}
