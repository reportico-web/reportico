{% autoescape false %}
{# Status Message #}
{% if WIDGETS["status-message-block"]["error"] %}
<div class="reportico-status-block" style="color: #ff0000">
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

        {{ include ('button.inc.tpl', {
        button_type : 'navbar-button',
        button_style : 'outline-secondary',
        button_label : WIDGETS["lookup-search"]["label"],
        button_name : WIDGETS["lookup-search"]["name"],
        button_id : WIDGETS["lookup-search"]["id"]
        } ) }}
        <br>
        {{ WIDGETS["criteria-lookup"] }} <BR>

        {{ include ('button.inc.tpl', {
        button_type : 'navbar-button',
        button_style : 'outline-secondary',
        button_label : WIDGETS["lookup-clear"]["label"],
        button_name : WIDGETS["lookup-clear"]["name"],
        button_id : WIDGETS["lookup-clear"]["id"]
        } ) }}

        {{ include ('button.inc.tpl', {
        button_type : 'navbar-button',
        button_style : 'outline-secondary',
        button_label : WIDGETS["lookup-select-all"]["label"],
        button_name : WIDGETS["lookup-select-all"]["name"],
        button_id : WIDGETS["lookup-select-all"]["id"]
        } ) }}

        {{ include ('button.inc.tpl', {
        button_type : 'navbar-button',
        button_style : 'outline-success',
        button_label : WIDGETS["lookup-ok"]["label"],
        button_name : WIDGETS["lookup-ok"]["name"],
        button_id : WIDGETS["lookup-ok"]["id"]
        } ) }}

    {% else %}

        {% if PERMISSIONS["design"] and WIDGETS["popup-edit-description"]["id"] %}
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
