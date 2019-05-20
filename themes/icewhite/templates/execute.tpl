{% autoescape false %}

{% if not REPORTICO_AJAX_CALLED %}
{% if not EMBEDDED_REPORT %}
<!DOCTYPE html>
<HTML>
<HEAD>
<TITLE>{{ TITLE|raw }}</TITLE>
{{ASSETS_CSS}}
{{ OUTPUT_ENCODING|raw }}
</HEAD>

{% if REPORTICO_STANDALONE_WINDOW %}
<BODY class="reportico-body reportico-bodyStandalone" style="{{ REPORT_PAGE_STYLE }}">
{% else %}
<BODY class="reportico-body" style="{{ REPORT_PAGE_STYLE }}">



{% endif %}

{% else %}


{{ASSETS_CSS}}

{% endif %}


<style>

table :first-of-type) {
background-color: #900;
}
</style>

{{ASSETS_JS}}
{{ASSETS_INIT}}

{% endif %}

{% if not REPORTICO_AJAX_CALLED %}
<div id="reportico-container">
{% endif %}

<div id="reportico-top-margin" style="z-index: 1; display: none;left: 80px;float: left; border: solid; height: {{ PAGE_TOP_MARGIN }}; width: {{ PAGE_LEFT_MARGIN }};">t</div>
<div id="reportico-bottom-margin" style="z-index: 1; display: none;left: 160;float: right; border: solid; height: {{ PAGE_BOTTOM_MARGIN }}; width: {{ PAGE_RIGHT_MARGIN }};">b</div>

<div style="{{ CONTENT.styles.body }}" class="reportico-output {{ PRINT_FORMAT }}">

    <script>
        reportico_criteria_items = [];
{% if CRITERIA_ITEMS is defined %}
{% for critno in CRITERIA_ITEMS %}
        reportico_criteria_items.push("{{CRITERIA_ITEMS[critno].name}}");
{% endfor %}
{% endif %}
    </script>
<div class="reportico-report-form">
{% if ERRORMSG|length>0 %}
            <div id="reporticoEmbeddedError">
                {{ ERRORMSG|raw }}
            </div>

            <script>
                reportico_jquery(document).ready(function()
                {
                    showParentNoticeModal(reportico_jquery("#reporticoEmbeddedError").html());
                });
            </script>

            <TABLE class="reportico-error-box">
                <TR>
                    <TD>{{ ERRORMSG|raw }}</TD>
                </TR>
            </TABLE>
{% endif %}
{% if STATUSMSG|length>0 %} 
			<TABLE class="reportico-status-block">
				<TR>
					<TD>{{ STATUSMSG|raw }}</TD>
				</TR>
			</TABLE>
{% endif %}
{% if SHOW_LOGIN %}
			<TD width="10%"></TD>
			<TD width="55%" align="left" class="reportico-prepare-top-menuCell">
{% if PROJ_PASSWORD_ERROR|length > 0 %}
                                <div style="color: #ff0000;">{{ PASSWORD_ERROR }}</div>
{% endif %}
				Enter the report project password. <br><input type="password" name="project_password" value=""></div>
				<input class="reportico-ajax-link" type="submit" name="login" value="Login">
			</TD>
{% endif %}

{{ASSETS_RUNTIME}}


<div class="reportico-output-button-block">
{# Navigation Buttons #}
{% for button in CONTENT.buttons %}
{% if button.linkClass is defined %}
    <div class="{{ button.class }}"><a class="{{ button.linkClass }}" href="{{ button.href }}" title="{{ button.title }}">&nbsp;</a></div>
{% else %}
    <div class="{{ button.class }}"><a class="reportico-ajax-link" href="{{ button.href }}" title="{{ button.title }}">&nbsp;</a></div>
{% endif %}

{% endfor %}
</div>

{% if PAGE_LAYOUT == "FORM" %}
    {% include 'execute-content-form.inc.tpl' %}
{% else %}
    {% include 'execute-content.inc.tpl' %}
{% endif %}

{% if not REPORTICO_AJAX_CALLED %}
</div>
{% endif %}
</div>
{% if not REPORTICO_AJAX_CALLED %}
{% if not EMBEDDED_REPORT %}
</BODY>
</HTML>
{% endif %}
{% endif %}

{% if REPORTICO_BOOTSTRAP_MODAL %}
{% if BOOTSTRAP_STYLES == "3"  %}
<div class="modal fade" id="reporticoNoticeModal" tabindex="-1" role="dialog" aria-labelledby="reporticoNoticeModal" aria-hidden="true">
    <div class="modal-dialog modal-lg">
{% else %}
<div class="modal fade" style="width: 500px; margin-left: -450px" id="reporticoNoticeModal" tabindex="-1" role="dialog" aria-labelledby="reporticoModal" aria-hidden="true">
    <div class="modal-dialog">
{% endif %}
        <div class="modal-content">
            <div class="modal-header">
            <button type="button" data-dismiss="modal" class="close" aria-hidden="true">&times;</button>
            <h4 class="modal-title reportico-modal-title" id="reporticoNoticeModalLabel">{{ T_NOTICE }}</h4>
            </div>
            <div class="modal-body" style="padding: 0px" id="reporticoNoticeModalBody">
                <h3>Modal Body</h3>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
                <!--button type="button" class="btn btn-primary" >Close</button-->
        </div>
    </div>
  </div>
</div>
{% else %}
<div id="reporticoModal" tabindex="-1" class="reportico-modal">
    <div class="reportico-modal-dialog">
        <div class="reportico-modal-content">
            <div class="reportico-modal-header">
            <button type="button" class="reportico-modal-close">&times;</button>
            <h4 class="reportico-modal-title" id="reporticoModalLabel">Set Parameter</h4>
            </div>
            <div class="reportico-modal-body" style="padding: 0px" id="reportico-edit-link">
                <h3>Modal Body</h3>
            </div>
            <div class="reportico-modal-footer">
                <!--button type="button" class="btn btn-default" data-dismiss="modal">Close</button-->
                <button type="button" class="reportico-edit-linkSubmit" >Close</button>
        </div>
    </div>
  </div>
</div>
{% endif %}

{% endautoescape %}
