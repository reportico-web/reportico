{% autoescape false %}
{% if not REPORTICO_AJAX_CALLED %}
{% if not EMBEDDED_REPORT %}
<!DOCTYPE html>
<HTML>
<HEAD>
<TITLE>{{ TITLE }}</TITLE>
<LINK id="reportico_css" REL="stylesheet" TYPE="text/css" HREF="{{ THEME_DIR }}/css/reportico_bootstrap.css">
<LINK id="bootstrap_css" REL="stylesheet" TYPE="text/css" HREF="{{ ASSETS_PATH }}/js/bootstrap3/css/bootstrap.min.css">
{{ OUTPUT_ENCODING }}
</HEAD>
<BODY class="swMntBody">
{% else %}
<LINK id="reportico_css" REL="stylesheet" TYPE="text/css" HREF="{{ THEME_DIR }}/css/reportico_bootstrap.css">
{% if BOOTSTRAP_STYLES %}
{% if not REPORTICO_BOOTSTRAP_PRELOADED %}
{% if BOOTSTRAP_STYLES == "2" %}
<LINK id="bootstrap_css" REL="stylesheet" TYPE="text/css" HREF="{{ ASSETS_PATH }}/js/bootstrap2/css/bootstrap.min.css">
{% else %}
<LINK id="bootstrap_css" REL="stylesheet" TYPE="text/css" HREF="{{ ASSETS_PATH }}/js/bootstrap3/css/bootstrap.min.css">
{% endif %}
{% endif %}
{% endif %}
{% endif %}

{% if AJAX_ENABLED %}
{% if not REPORTICO_AJAX_PRELOADED %}
{% if not REPORTICO_JQUERY_PRELOADED %}

<script type="text/javascript" src="{{ ASSETS_PATH }}/js/jquery.js"></script>

{% endif %}

<script type="text/javascript" src="{{ ASSETS_PATH }}/js/ui/jquery-ui.js"></script>


<script type="text/javascript" src="{{ ASSETS_PATH }}/js/reportico.js"></script>

{% if REPORTICO_CSRF_TOKEN %}
<script type="text/javascript">var reportico_csrf_token = "{{ REPORTICO_CSRF_TOKEN }}";</script>
<script type="text/javascript">var ajax_event_handler = "{{ REPORTICO_AJAX_HANDLER }}";</script>
{% endif %}
{% endif %}

{% if BOOTSTRAP_STYLES %}
{% if not REPORTICO_BOOTSTRAP_PRELOADED %}

{% if BOOTSTRAP_STYLES == "2" %}
<script type="text/javascript" src="{{ ASSETS_PATH }}/js/bootstrap2/js/bootstrap.min.js"></script>
{% else %}
<script type="text/javascript" src="{{ ASSETS_PATH }}/js/bootstrap3/js/bootstrap.min.js"></script>
{% endif %}
{% endif %}
{% endif %}
{% endif %}
{% if not REPORTICO_AJAX_PRELOADED %}

<script type="text/javascript" src="{{ ASSETS_PATH }}/js/ui/i18n/jquery.ui.datepicker-{{ AJAX_DATEPICKER_LANGUAGE }}.js"></script>

{% endif %}
{% if not BOOTSTRAP_STYLES %}

<script type="text/javascript" src="{{ ASSETS_PATH }}/js/jquery.jdMenu.js"></script>
<LINK id="reportico_css" REL="stylesheet" TYPE="text/css" HREF="{{ ASSETS_PATH }}/js/jquery.jdMenu.css">

{% endif %}

<LINK id="reportico_css" REL="stylesheet" TYPE="text/css" HREF="{{ ASSETS_PATH }}/js/ui/jquery-ui.css">
<script type="text/javascript">var reportico_datepicker_language = "{{ AJAX_DATEPICKER_FORMAT }}";</script>
<script type="text/javascript">var reportico_this_script = "{{ SCRIPT_SELF }}";</script>
<script type="text/javascript">var reportico_ajax_script = "{{ REPORTICO_AJAX_RUNNER }}";</script>

{% if REPORTICO_BOOTSTRAP_MODAL %}
<script type="text/javascript">var reportico_bootstrap_styles = "{{ BOOTSTRAP_STYLES }}";</script>
<script type="text/javascript">var reportico_bootstrap_modal = true;</script>
{% else %}
<script type="text/javascript">var reportico_bootstrap_modal = false;</script>
<script type="text/javascript">var reportico_bootstrap_styles = false;</script>
{% endif %}

<script type="text/javascript">var reportico_ajax_mode = "{{ REPORTICO_AJAX_MODE }}";</script>

{% if REPORTICO_DYNAMIC_GRIDS %}
<script type="text/javascript">var reportico_dynamic_grids = true;</script>
{% if REPORTICO_DYNAMIC_GRIDS_SORTABLE %}
<script type="text/javascript">var reportico_dynamic_grids_sortable = true;</script>
{% else %}
<script type="text/javascript">var reportico_dynamic_grids_sortable = false;</script>
{% endif %}
{% if REPORTICO_DYNAMIC_GRIDS_SEARCHABLE %}
<script type="text/javascript">var reportico_dynamic_grids_searchable = true;</script>
{% else %}
<script type="text/javascript">var reportico_dynamic_grids_searchable = false;</script>
{% endif %}
{% if REPORTICO_DYNAMIC_GRIDS_PAGING %}
<script type="text/javascript">var reportico_dynamic_grids_paging = true;</script>
{% else %}
<script type="text/javascript">var reportico_dynamic_grids_paging = false;</script>
{% endif %}
<script type="text/javascript">var reportico_dynamic_grids_page_size = {{ REPORTICO_DYNAMIC_GRIDS_PAGE_SIZE }};</script>
{% else %}
<script type="text/javascript">var reportico_dynamic_grids = false;</script>
{% endif %}
{% endif %}
{% if not REPORTICO_AJAX_PRELOADED %}

<script type="text/javascript" src="{{ ASSETS_PATH }}/js/select2/js/select2.min.js"></script>
<script type="text/javascript" src="{{ ASSETS_PATH }}/js/jquery.dataTables.js"></script>

<LINK id="PRP_StyleSheet_s2" REL="stylesheet" TYPE="text/css" HREF="{{ ASSETS_PATH }}/js/select2/css/select2.min.css">
<LINK id="PRP_StyleSheet_dt" REL="stylesheet" TYPE="text/css" HREF="{{ STYLESHEETDIR }}/jquery.dataTables.css">
{% endif %}
{% if REPORTICO_CHARTING_ENGINE == "NVD3"  %}
{% if not REPORTICO_AJAX_PRELOADED %}

<script type="text/javascript" src="{{ ASSETS_PATH }}/js/nvd3/d3.min.js"></script>
<script type="text/javascript" src="{{ ASSETS_PATH }}/js/nvd3/nv.d3.js"></script>
<LINK id="bootstrap_css" REL="stylesheet" TYPE="text/css" HREF="{{ ASSETS_PATH }}/js/nvd3/nv.d3.css">

{% endif %}
{% endif %}
<div id="reportico_container">
    <script>
        reportico_criteria_items = [];
{% if CRITERIA_ITEMS is defined %}
{% for critno in CRITERIA_ITEMS %}
        reportico_criteria_items.push("{CRITERIA_ITEMS[critno].name}");
{% endfor %}
{% endif %}
    </script>
<FORM class="swMntForm" name="topmenu" method="POST" action="{{ SCRIPT_SELF }}">
<H1 class="swTitle">{{ TITLE }}</H1>
{% if STATUSMSG|length>0 %} 
			<TABLE class="swStatus">
				<TR>
					<TD>{{ STATUSMSG|raw }}</TD>
				</TR>
			</TABLE>
{% endif %}
{% if ERRORMSG|length>0 %} 
			<TABLE class="swError">
				<TR>
					<TD>{{ ERRORMSG|raw }}</TD>
				</TR>
			</TABLE>
{% endif %}
<input type="hidden" name="reportico_session_name" value="{{ SESSION_ID }}" />
{% if SHOW_TOPMENU %}
	<TABLE class="swMntTopMenu">
		<TR>
{% if (DB_LOGGEDON) %} 
			<TD class="swPrpTopMenuCell">
{% if (DBUSER) %}
Logged On As {{ DBUSER }}
{% else %}
&nbsp;
{% endif %}
			</TD>
{% endif %}
{% if MAIN_MENU_URL|length>0 %} 
			<TD style="text-align: left;">
				<a class="{{ BOOTSTRAP_STYLE_ADMIN_BUTTON }}reportico-ajax-link" href="{{ MAIN_MENU_URL }}">{{ T_PROJECT_MENU }}</a>
{% if (SHOW_ADMIN_BUTTON) %}
				&nbsp;<a class="{{ BOOTSTRAP_STYLE_ADMIN_BUTTON }}reportico-ajax-link" href="{{ ADMIN_MENU_URL }}">{{ T_ADMIN_MENU }}</a>
{% endif %}
				&nbsp;<a class="{{ BOOTSTRAP_STYLE_ADMIN_BUTTON }}reportico-ajax-link" href="{{ RUN_REPORT_URL }}">{{ T_RUN_REPORT }}</a>
				&nbsp;<input class="{{ BOOTSTRAP_STYLE_ADMIN_BUTTON }}reportico-ajax-link" type="submit" name="submit_prepare_mode" style="display:none" onclick="return(false);" value="Do nothing on enter">
                <input class="swMntButton reporticoSubmit swNoSubmit" style="width: 0px; color: transparent; background-color: transparent; border-color: transparent; cursor: default;" type="submit" name="submit_dummy_SET" value="Ok">
			</TD>
{% endif %}
{% if SHOW_MODE_MAINTAIN_BOX and 0 %}
			<TD style="text-align: left;">
				<input class="swMntButton" type="submit" name="submit_genws_mode" value="{{ T_GEN_WEB_SERVICE }}">
			</TD>
			<TD style="text-align: right;">
			</TD>
{% endif %}
{% if SHOW_LOGOUT %}
			<TD style="width:15%; text-align: right; padding-right: 10px;" align="right" class="swPrpTopMenuCell">
				<input class="{{ BOOTSTRAP_STYLE_ADMIN_BUTTON }}reportico-ajax-link" type="submit" name="logout" value="{{ T_LOGOFF }}">
			</TD>
{% endif %}
{% if SHOW_LOGIN %}
			<TD style="width: 50%"></TD>
			<TD style="width: 35%" align="right" class="swPrpTopMenuCell">
{% if PROJ_PASSWORD_ERROR|length > 0 %}
                                <div style="color: #ff0000;">{{ PASSWORD_ERROR }}</div>
{% endif %}
				{{ T_DESIGN_PASSWORD_PROMPT }} <input type="password" name="project_password" value="">
			</TD>
			<TD style="width: 15%" align="right" class="swPrpTopMenuCell">
				<input class="btn btn-sm btn-default swPrpSubmit" type="submit" name="login" value="{{ T_LOGIN }}">
			</TD>
{% endif %}
		</TR>
	</TABLE>
{% endif %}
	<TABLE class="swMntMainBox" cellspacing="0" cellpadding="0">
		<TR>
			<TD>
{{ CONTENT }}
			</TD>
		</TR>
	</TABLE>
</FORM>
<div class="smallbanner">Powered by <a href="http://www.reportico.org/" target="_blank">reportico {{ REPORTICO_VERSION }}</a></div>
</div>
{% if not REPORTICO_AJAX_CALLED %}
{% if not EMBEDDED_REPORT %}
</BODY>
</HTML>
{% endif %}
{% endif %}
{% endautoescape %}
