{% autoescape false %}
{% if button_label %}
{% if button_type == 'navbar-button' %}
    <input type='submit'
        class='flex-widget btn btn-{{ button_style }} reportico-edit-link'
        title='{{ button_label }}'
        id='{{ button_id }}'
        name='{{ button_name }}'
        value='{{ button_label }}'
        >
{% endif %}
{% endif %}
{% endautoescape %}
