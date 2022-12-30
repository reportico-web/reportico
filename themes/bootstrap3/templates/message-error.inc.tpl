{% autoescape false %}
{% if ERRORMSG|length>0 %}
            <TABLE class="reportico-error-box">
                <TR>
                    <TD>{{ ERRORMSG }}</TD>
                </TR>
            </TABLE>
{% endif %}
{% endautoescape %}
