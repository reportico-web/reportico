{% autoescape false %}
<div>
    {% if WIDGETS["status-message-block"]["error"] %}
    <div class="reportico-status-block" style="color:#ff0000">
        {{ WIDGETS["status-message-block"]["error"] }}
    </div>
    {% endif %}

    {% if WIDGETS["status-message-block"]["status"] %}
    <div class="reportico-status-block">
        {{ WIDGETS["status-message-block"]["status"] }}
    </div>
    {% endif %}

    {% if WIDGETS["status-message-block"]["debug"] %}
    <div class="reportico-status-block">
        {{ WIDGETS["status-message-block"]["debug"] }}
    </div>
    {% endif %}
</div>
{% endautoescape %}
