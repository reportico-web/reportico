{% autoescape false %}
{% if ( CONTENT.criteria ) %}
<table class="reportico-criteria" style="{{ CONTENT.styles.criteria }}">
    <tbody>
        {% for criterium in CONTENT.criteria %}
        <tr class="reportico-group-header-row"><td class="reportico-group-header-label">{{criterium.label }}</td><td class="reportico-group-header-value">{{ criterium.value }}</td></tr>
        {% endfor %}
    </tbody>
</table>
{% endif %}
{% endautoescape %}

