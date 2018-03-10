{% autoescape false %}
{% if ERRORMSG|length>0 %}
            <TABLE class="swError">
                <TR>
                    <TD>{{ ERRORMSG }}</TD>
                </TR>
            </TABLE>
{% endif %}
{% endautoescape %}
