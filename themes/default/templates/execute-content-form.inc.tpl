{% autoescape false %}


<script type="text/javascript">
    var reportico_page_size = "{{ PAGE_SIZE }}";
    var reportico_page_orientation = "{{ PAGE_ORIENTATION }}";
    var reportico_page_title_display = "{{ PAGE_TITLE_DISPLAY }}";
</script>

<style>
@media print {
    html {
        zoom: {{ ZOOM_FACTOR }};
    }
}

.reportico-page {
    width: 100%;
    border: solid 1px #bbb;
};

.reportico-page td {
    text-align: left !important;
}
.reportico-page td:first-child {
    width: 20%;
    text-align: left !important;
}
</style>
</style>


{# Begin new page =============================================== #}
<div class="reportico-paginated {{ AUTOPAGINATE }} original-page page-size-{{ PAGE_SIZE }} page-orientation-{{ PAGE_ORIENTATION }}" style="zoom1: {{ ZOOM_FACTOR }}; padding-top: 0px; padding-bottom: {{ PAGE_BOTTOM_MARGIN }}; padding-right: {{ PAGE_RIGHT_MARGIN }}; padding-left: {{ PAGE_LEFT_MARGIN }};">

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
            {% if row.line > 1 %}

                {# Page Footer on group change ============================================= #}
                {% include 'execute-content-page-footers.inc.tpl' %}
                
                </div>
                <div class="reportico-paginated {{ AUTOPAGINATE }} original-page page-size-{{ PAGE_SIZE }} page-orientation-{{ PAGE_ORIENTATION }}" style="zoom1: {{ ZOOM_FACTOR }}; padding-top: 0px; padding-bottom: {{ PAGE_BOTTOM_MARGIN }}; padding-right: {{ PAGE_RIGHT_MARGIN }}; padding-left: {{ PAGE_LEFT_MARGIN }};">

                {% set triggered = 1 %}

                {% set headersexist = 0 %}
                {% set pageno = pageno + 1 %}

                {# Page Header on group change ============================================= #}
                {% include 'execute-content-page-headers.inc.tpl' %}

                {# Report title group change =============================================== #}
                {% include 'execute-content-page-title.inc.tpl' %}

                {% set groupcount = groupcount + 1 %}

            {% endif %}


        {# Group Headers + Detail ======================================= #}
        <!--div class="reportico-group-section"--> 

        {# Custom group headers ======================================== #}
        <div class="reportico-custom-header-block" >
        {% for group in row.groupstarts %}
            {% for header in group.customheaders %}
                <div class="reportico-custom-header" style="{{ header.styles }}">
                    {% if ( header.image ) %}
                        <img src='{{ header.image}}' style="{{ header.imagestyles }}">
                    {% endif %}
                    {{ header.content }}
                </div>
            {% endfor %}
        {% endfor %}
         </div>

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

            {# Column Headers #}
            <!--THEAD>
            <TR>
            {% for columnHeader in page.headers %}
                <TH style="{{columnHeader.styles}}">
                {{ columnHeader.content }}
                </TH>
            {% endfor %}
            </TR>
            </THEAD-->

        {% endif %}

        {# Report Detail Block/Page  ================================================ #}
        <TABLE class="{{ CONTENT.classes.page }} reportico-page" style="{{ CONTENT.styles.page }}">

        {# Report Detail Row  ================================================ #}
        {% for column in row.data %}
        <TR class="reportico-row" style="{{ CONTENT.styles.row }}">
            <TD>
                {% set ptr = loop.index - 1 %}
                {{ page.headers[ptr].content }}
            </TD>
            <TD>
            {{ column.content }}
            </TD>
        </TR>
        {% endfor %}
        </TABLE>

        <!--/div-->

    {% endfor %}  {# each row #}

{% endfor %}  {# each page #}

{# Page Footer on group change ============================================= #}
{% include 'execute-content-page-footers.inc.tpl' %}

</div>

{% endautoescape %}
