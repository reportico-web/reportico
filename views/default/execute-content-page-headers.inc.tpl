{% autoescape false %}
<div class="swPageHeaderBlock {{ PRINT_FORMAT }}" style="min-height: {{ PAGE_TOP_MARGIN }}" >

    <div class="swPageHeaderBody" >

        {% for header in CONTENT.pageheaderstop %}
        <div class="swPageHeader" style="{{ header.styles }}">
        {% if ( header.image ) %}
            <img src='{{ header.image}}' style="{{ header.imagestyles }}">
        {% endif %}
            {{ header.content }}
        </div>
        {% endfor %}

    </div>

</div>
{% endautoescape %}
