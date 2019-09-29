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
<BODY class="reportico-menuBody">
{% else %}
{{ASSETS_CSS}}
{% endif %}


{{ASSETS_JS}}
{{ASSETS_INIT}}
{{ASSETS_RUNTIME}}
{% if REPORTICO_CSRF_TOKEN %}
<script type="text/javascript">var reportico_csrf_token = "{{ REPORTICO_CSRF_TOKEN }}";</script>
<script type="text/javascript">var ajax_event_handler = "{{ REPORTICO_AJAX_HANDLER }}";</script>
{% endif %}

{# Spinner Control #}
<div class="overlay" id="reportico-spinner-overlay">
    <div id="reportico-full-page-spinner" class="" style="color: #0a0; position: fixed; width: 100%; z-index:9999">
    </div>
</div>

{% endif %}

{% endautoescape %}
