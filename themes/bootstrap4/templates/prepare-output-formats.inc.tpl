{% autoescape false %}
<div class="reportico-prepare-toolbar-pane" style="padding: 0px 5px; float: left;vertical-align: bottom;text-align: center; border-right: solid 1px #bbb">
    {% if WIDGETS["output-csv"] is defined %} {{ WIDGETS["output-csv"] }} {% endif %}
    {% if WIDGETS["output-pdf"] is defined %} {{ WIDGETS["output-pdf"] }} {% endif %}
    {% if WIDGETS["output-html-inline"] is defined %} {{ WIDGETS["output-html-inline"] }} {% endif %}
    {% if WIDGETS["output-html-new-window"] is defined %} {{ WIDGETS["output-html-new-window"] }} {% endif %}
</div>
{% endautoescape %}
