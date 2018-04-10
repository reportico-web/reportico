{% autoescape false %}
<div class="reportico-page-header-block {{ PRINT_FORMAT }}" style="min-height: {{ PAGE_TOP_MARGIN }}" >

    <div class="reportico-page-headerBody" >

        {% for header in CONTENT.pageheaderstop %}
        <div class="reportico-page-header" style="{{ header.styles }}">
        {% if ( header.image ) %}
            <img src='{{ header.image}}' style="{{ header.imagestyles }}">
        {% endif %}
            {{ header.content }}
        </div>
        {% endfor %}

    </div>

</div>
{% endautoescape %}
