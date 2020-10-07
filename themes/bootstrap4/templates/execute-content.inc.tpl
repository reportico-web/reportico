{% autoescape false %}


<script type="text/javascript">
    var reportico_page_size = "{{ PAGE_SIZE }}";
    var reportico_page_orientation = "{{ PAGE_ORIENTATION }}";
    var reportico_page_title_display = "{{ PAGE_TITLE_DISPLAY }}";
    var reportico_page_freeze_columns = "{{ PAGE_FREEZE_COLUMNS }}";
</script>

<style>
@media print {
    body { min-width: initial !important }
    html { zoom: {{ ZOOM_FACTOR }}; }
    @page {
        size: {{ PAGE_SIZE }} {{ PAGE_ORIENTATION }};
    }
}
</style>

{# Begin new page =============================================== #}
<div class="reportico-paginated {{ AUTOPAGINATE }} original-page page-size-{{ PAGE_SIZE }} page-orientation-{{ PAGE_ORIENTATION }}" style="zoom1: {{ ZOOM_FACTOR }}; padding-top: 0px; padding-bottom: {{ PAGE_BOTTOM_MARGIN }}; padding-right: {{ PAGE_RIGHT_MARGIN }}; padding-left: {{ PAGE_LEFT_MARGIN }}; {{ REPORT_PAGE_STYLE }}">

{% set groupcount = 0 %}
{% set pageno = 1 %}

{# Top Page Headers ============================================= #}
{% if ( CONTENT.pageheaderstop ) %}
    {% include 'execute-content-page-headers.inc.tpl' %}
{% endif %}

{# Document Title =============================================== #}
{% include 'execute-content-page-title.inc.tpl' %}

{# Document Criteria  =========================================== #}
{% include 'execute-content-criteria.inc.tpl' %}

{# End Criteria ================================================= #}

{% for page in CONTENT.pages %}

    {% for row in page.rows %}

        {# Page Headers On Group Start ================================== #}
        {% set triggered = 0 %}
        {% for group in row.groupstarts %}

            {# Group change triggers new page #}
            <!--{{ group.before_header  }} triggered {{ triggered }} {{ row.line }}<BR> !-->
            {% if row.line > 1 and group.before_header is defined and group.before_header == "newpage" and triggered == 0 %}

                {# Page Footer on group change ============================================= #}
                {% include 'execute-content-page-footers.inc.tpl' %}

                </div>
                <div class="reportico-paginated {{ AUTOPAGINATE }} original-page page-size-{{ PAGE_SIZE }} page-orientation-{{ PAGE_ORIENTATION }}" style="zoom1: {{ ZOOM_FACTOR }}; padding-top: 0px; padding-bottom: {{ PAGE_BOTTOM_MARGIN }}; padding-right: {{ PAGE_RIGHT_MARGIN }}; padding-left: {{ PAGE_LEFT_MARGIN }}; {{ REPORT_PAGE_STYLE }}">

                {% set triggered = 1 %}

                {% set headersexist = 0 %}
                {% set pageno = pageno + 1 %}

                {# Page Header on group change ============================================= #}
                {% include 'execute-content-page-headers.inc.tpl' %}

                {# Report title group change =============================================== #}
                {% include 'execute-content-page-title.inc.tpl' %}

                {% set groupcount = groupcount + 1 %}

            {% endif %}
        {% endfor %}



        {# Group Headers + Detail ======================================= #}
        <!--div class="reportico-group-section"-->

        {# Custom group headers ======================================== #}
        {% for group in row.groupstarts %}
            <div class="reportico-custom-header-block" >
                {% for header in group.customheaders %}
                    <div class="reportico-custom-header" style="{{ header.styles }}">
                        {% if ( header.image ) %}
                            <img src='{{ header.image}}' style="{{ header.imagestyles }}">
                        {% endif %}
                        {{ header.content }}
                    </div>
                {% endfor %}
            </div>
        {% endfor %}

        {% for group in row.groupstarts %}
            {# Group Headers ================================================ #}
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
            <TR class="reportico-row">
            {% for columnHeader in page.headers %}
                <TH style="{{columnHeader.styles}}">
                {{ columnHeader.content }}
                </TH>
            {% endfor %}
            </TR>
            </THEAD>

        {% endif %}

        {# Report Detail Row  ================================================ #}
        <TR class="reportico-row" style="{{ CONTENT.styles.row }}">
        {% for column in row.data %}
            <TD style="{{column.styles}}">
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
                    <tr class="trailer reportico-row reportico-trailer">
                        {% for column in trailer %}
                            <td style="{{ column.styles }}">{{ column.content }}</td>
                        {% endfor %}
                    </tr>
                {% endfor %}
            {% endfor %}

            </TFOOT>
            {% endif %}
        </table>

        <!--/div-->

        {# Custom group tailers ======================================== #}
        <div class="reportico-custom-trailer-block" >
        {% for group in row.groupends %}
            {% for trailer in group.customtrailers %}
                <div class="reportico-custom-trailer" style="{{ trailer.styles }}">
                    {% if ( trailer.image ) %}
                        <img src='{{ trailer.image}}' style="{{ trailer.imagestyles }}">
                    {% endif %}
                    {{ trailer.content }}
                </div>
            {% endfor %}
        {% endfor %}
         </div>

        {# After Group Charts ========================================== #}
        {% for graph in row.graphs %}
        <div class="reportico-chart {{ PRINT_FORMAT }}">
            {{ graph.url }}
        </div>
        {% endfor %}

        {% endif %}

    {% endfor %}  {# each row #}

{% endfor %}  {# each page #}

{# Page Footer on group change ============================================= #}
{% include 'execute-content-page-footers.inc.tpl' %}

</div>

{% endautoescape %}
