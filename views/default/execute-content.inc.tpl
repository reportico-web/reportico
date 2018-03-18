{% autoescape false %}
<h1 class="reportico-title">{{ CONTENT.title }}</h1>

{% if ( CONTENT.criteria ) %}
<table class="reportico-criteria" style="{{ CONTENT.styles.criteria }}">
    <tbody>
        {% for criterium in CONTENT.criteria %}
        <tr class="reportico-group-header-row"><td class="reportico-group-header-label">{{criterium.label }}</td><td class="reportico-group-header-value">{{ criterium.value }}</td></tr>
        {% endfor %}
    </tbody>
</table>
{% endif %}

{% for page in CONTENT.pages %}
    {% for row in page.rows %}

        {# Group Headers ================================================ #}
        {% for group in row.groupstarts %}
        <table class="reportico-group-header-box">
            <tbody>
                {% for header in group.headers %}
                <tr class="reportico-group-header-row">
                    <td class="reportico-group-header-label" style="{{ CONTENT.styles.group_header_label }}">{{ header.label }}</td>
                    <td class="reportico-group-header-value" style="{{ CONTENT.styles.group_header_value }}">{{ header.value }}</td>
                </tr>
                {% endfor %}
            </tbody>
        </table>
        {% endfor %}

        {# Start of group/report - new detail block  ======= #}
        {% if row.openrowsection %}
        <TABLE class="{{ CONTENT.classes.page }} reportico-page" style="{{ CONTENT.styles.page }}">

            {# Column Headers #}
            <THEAD>
            <TR>
            {% for columnHeader in page.headers %}
                <TH style="{{columnHeader.style}}">
                {{ columnHeader.content }}
                </TH>
            {% endfor %}
            </TR>
            </THEAD>

        {% endif %}

        {# Report Detail Row  ================================================ #}
        <TR class="reportico-row" style="{{ row.style }}">
        {% for column in row.data %}
            <TD style="{{column.style}}">
            {{ column.content }}
            </TD>
        {% endfor %}
        </TR>

        {# End of group/report - close detail section do trailers/graphs ===== #}
        {% if row.closerowsection %}
            {% if row.groupends %}
            </TBODY>
            <TFOOT>

            {% for group in row.groupends %}
                {% for trailer in group.trailers %}
                    <tr class="trailer">
                        {% for column in trailer %}
                            <td style="{{ column.style }}">{{ column.content }}</td>
                        {% endfor %}
                    </tr>
                {% endfor %}
            {% endfor %}

            </TFOOT>
            {% endif %}
        </table>

        {# After Group Charts #}
        {% for graph in row.graphs %}
        <div class="reportico-chart {{ PRINT_FORMAT }}">
            {{ graph.url }}
        </div>
        {% endfor %}

        {% endif %}

    {% endfor %}  {# each row #}

{% endfor %}  {# each page #}

{% endautoescape %}
