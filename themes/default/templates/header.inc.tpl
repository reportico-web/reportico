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

{% endif %}

{% endautoescape %}
