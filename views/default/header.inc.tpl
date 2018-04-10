{% autoescape false %}
{% if not REPORTICO_AJAX_CALLED %}
{% if not EMBEDDED_REPORT %}
<!DOCTYPE html>
<HTML>
<HEAD>
<TITLE>{{ TITLE }}</TITLE>
<LINK id="reportico_css" REL="stylesheet" TYPE="text/css" HREF="{{ THEME_DIR }}/css/reportico_bootstrap.css">
<LINK id="bootstrap_css" REL="stylesheet" TYPE="text/css" HREF="{{ ASSETS_PATH }}/js/bootstrap3/css/bootstrap.min.css">
{{ OUTPUT_ENCODING|raw }}
</HEAD>
<BODY class="swMenuBody">
{% else %}
<LINK id="reportico_css" REL="stylesheet" TYPE="text/css" HREF="{{ THEME_DIR }}/css/reportico_bootstrap.css">
{% if not REPORTICO_BOOTSTRAP_PRELOADED %}
<LINK id="bootstrap_css" REL="stylesheet" TYPE="text/css" HREF="{{ ASSETS_PATH }}/js/bootstrap3/css/bootstrap.min.css">
{% endif %}
{% endif %}
{% if AJAX_ENABLED %}
{% if not REPORTICO_AJAX_PRELOADED %}
{% if not REPORTICO_JQUERY_PRELOADED %}

<script type="text/javascript" src="{{ ASSETS_PATH }}/js/jquery.js"></script>

{% endif %}

<script type="text/javascript" src="{{ ASSETS_PATH }}/js/ui/jquery-ui.js"></script>
<script type="text/javascript" src="{{ ASSETS_PATH }}/js/download.js"></script>
<script type="text/javascript" src="{{ ASSETS_PATH }}/js/reportico.js"></script>

{% endif %}
{% if REPORTICO_CSRF_TOKEN %}
<script type="text/javascript">var reportico_csrf_token = "{{ REPORTICO_CSRF_TOKEN }}";</script>
<script type="text/javascript">var ajax_event_handler = "{{ REPORTICO_AJAX_HANDLER }}";</script>
{% endif %}
{% if not REPORTICO_BOOTSTRAP_PRELOADED %}
<script type="text/javascript" src="{{ ASSETS_PATH }}/js/bootstrap3/js/bootstrap.min.js"></script>
{% endif %}
{% endif %}
{% if not REPORTICO_AJAX_PRELOADED %}

<script type="text/javascript" src="{{ ASSETS_PATH }}/js/ui/i18n/jquery.ui.datepicker-{{ AJAX_DATEPICKER_LANGUAGE }}.js"></script>

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

<LINK id="StyleSheet_s2" REL="stylesheet" TYPE="text/css" HREF="{{ ASSETS_PATH }}/js/select2/css/select2.min.css">
<LINK id="StyleSheet_dt" REL="stylesheet" TYPE="text/css" HREF="{{ STYLESHEETDIR }}/jquery.dataTables.css">
{% endif %}
{% if REPORTICO_CHARTING_ENGINE == "NVD3"  %}
{% if not REPORTICO_AJAX_PRELOADED %}

<script type="text/javascript" src="{{ ASSETS_PATH }}/js/nvd3/d3.min.js"></script>
<script type="text/javascript" src="{{ ASSETS_PATH }}/js/nvd3/nv.d3.js"></script>
<LINK id="bootstrap_css" REL="stylesheet" TYPE="text/css" HREF="{{ ASSETS_PATH }}/js/nvd3/nv.d3.css">

{% endif %}
{% endif %}
{% endautoescape %}
