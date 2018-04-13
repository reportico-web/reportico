{% autoescape false %}
{% if ( PAGE_TITLE_DISPLAY == "off" ) %}
    {# Dont show any title #}
{% else %}
    <h1 class="reportico-title">{{ CONTENT.title }}</h1>
{% endif %}
{% endautoescape %}
