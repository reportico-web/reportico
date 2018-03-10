{% autoescape false %}
{% include 'default/header.inc.tpl' %}
<div id="reportico_container">
<FORM class="swMenuForm" name="topmenu" method="POST" action="{{ SCRIPT_SELF }}">
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
{% include 'default/dropdown-brand.inc.tpl' %}
            <div class= "nav-collapse collapse in" id="reportico-bootstrap-collapse">
{% include 'default/dropdown-menu.inc.tpl' %}
{% if SHOW_HIDE_NAVIGATION_MENU == "show"  %}
            <ul class= "nav navbar-nav pull-right navbar-right">
{% else %}
            <ul style="display:none" class= "nav navbar-nav pull-right navbar-right">
{% endif %}
{% if SHOW_TOPMENU %}
{% include 'default/menu-bar-logout.inc.tpl' %}
{% include 'default/menu-bar-project-password.inc.tpl' %}
{% endif %}
{% include 'default/menu-bar-admin-menu.inc.tpl' %}
{% include 'default/menu-bar-configure-create-buttons.inc.tpl' %}
</div>
</ul>
        </div>
</div>
<!-- BOOTSTRAP VERSION -->
<H1 class="swTitle">{{ TITLE }}</H1>
	<TABLE class="swMenu">
		<TR> <TD>&nbsp;</TD> </TR>
{% for menuitem in MENU_ITEMS %}
		<TR> 
			<TD class="swMenuItem">
{% if  menuitem.label == "TEXT" %}
				{{ menuitem.url|raw }}
{% else %}
{% if  menuitem.label == "BLANKLINE" %}
				&nbsp;
{% else %}
{% if  menuitem.label == "LINE" %}
				<hr>
{% else %}
				<a class="swMenuItemLink" href="{{ menuitem.url }}">{{ menuitem.label }}</a>
{% endif %}
{% endif %}
{% endif %}
			</TD>
		</TR>
{% endfor %}
		
		<TR> <TD>&nbsp;</TD> </TR>
	</TABLE>

{% if ERRORMSG|length>0 %} 
			<TABLE class="swError">
				<TR>
					<TD>{{ ERRORMSG }}</TD>
				</TR>
			</TABLE>
{% endif %}
</FORM>
{% include 'default/reportico-banner.inc.tpl' %}
</div>
{% include 'default/footer.inc.tpl' %}
{% endautoescape %}
