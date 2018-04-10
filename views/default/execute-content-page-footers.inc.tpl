                {% autoescape false %}
                <div class="swPageFooterBlock {{ PRINT_FORMAT }}" style="min-height: {{ PAGE_BOTTOM_MARGIN }}; overflow: hidden; bottom: 10;" >
                
                    <div class="swPageFooterBody" style="min-height: {{ PAGE_BOTTOM_MARGIN }};  margin-left: {{ PAGE_LEFT_MARGIN }}; margin-right: {{ PAGE_RIGHT_MARGIN }};">

                        {% for footer in CONTENT.pagefooters %}
                        <div class="swPageFooter" style="{{ footer.styles }}">
                            {% if ( footer.image ) %}
                            <img src='{{ footer.image}}' style="{{ footer.imagestyles }}">
                            {% endif %}
                            {{ footer.content }}
                        </div>
                        {% endfor %}
                
                    </div>
                </div>
                {% endautoescape %}
