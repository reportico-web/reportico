{% autoescape false %}
{% include 'header.inc.tpl' %}

<div id="reportico-container">

	<!-- Begin Form -->
	{{ WIDGETS["criteria-form"]["begin"] }}

	<!-- Menu Bar -->
    {% include 'navigation-menu.inc.tpl' %}


	{# Menu Title #}
	<H1 class="reportico-title-bar"> {{ WIDGETS["menu-page"]["project-menu"]["project-title"] }} </H1>



	{# Menu Items #}
    {% if PERMISSIONS["access"] %}
	{% for menuitem in  WIDGETS["menu-page"]["project-menu"]["project-menu-items"]  %}
	<div class="flex-container" style="text-align: center; padding: 4px">
			{% if menuitem.url %}
		    <div style="text-align:center;">
			    <a class="reportico-menu-item-link" href="{{ menuitem.url }}" class="">{{ menuitem.label }}</a>
			{% else %}
			<div style="text-align:center;">
			{{ menuitem.label }}
			{% endif %}
		</div>
	</div>
	{% endfor %}
	{% endif %}

{% if ERRORMSG|length>0 %}
			<TABLE class="reportico-error-box">
				<TR>
					<TD>{{ ERRORMSG }}</TD>
				</TR>
			</TABLE>
{% endif %}

    <!-- End Form -->
    {{ WIDGETS["criteria-form"]["end"] }}

	{# WIDGETS["powered-by-banner"] #}
</div>
{% include 'footer.inc.tpl' %}
{% endautoescape %}
