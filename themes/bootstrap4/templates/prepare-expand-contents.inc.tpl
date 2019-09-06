{% autoescape false %}
{# Status Message #}
{% if WIDGETS["status-message-block"]["error"] %}
<div class="reportico-status-block" style="color: #ff000">
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

{% if not WIDGETS["status-message-block"]["error"]
and not WIDGETS["status-message-block"]["status"]
and not WIDGETS["status-message-block"]["debug"]
%}
<p>
    {% if WIDGETS["criteria-lookup"] %}

        {{ WIDGETS["lookup-search"] }} <BR>
        {{ WIDGETS["criteria-lookup"] }} <BR>
        {{ WIDGETS["lookup-clear"] }}
        {{ WIDGETS["lookup-select-all"] }}
        {{ WIDGETS["lookup-ok"] }}

    {% else %}

        {% if PERMISSIONS["design"] %}
        <button type='submit' class='flex-widget btn btn-sm btn-outline-secondary reportico-edit-link'
            title='{{ WIDGETS["popup-edit-description"]["title"] }}' id='{{ WIDGETS["popup-edit-description"]["id"] }}'
            name='{{ WIDGETS["popup-edit-description"]["name"] }}' value='{{ WIDGETS["popup-edit-description"]["label"] }}' >
            <i class="fa fa-pen fa-lg"></i>
        </button>
        {% endif %}
        {{ WIDGETS["description"] }}

    {% endif %}

{% endif %}
{% endautoescape%}
