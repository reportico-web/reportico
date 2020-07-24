{% autoescape false %}
{% include 'header.inc.tpl' %}

<div id="reportico-container">

    <script type="text/javascript">var reportico_datepicker_language = "{{ AJAX_DATEPICKER_FORMAT }}";</script>
    <script>
        reportico_criteria_items = [];
{% if CRITERIA_ITEMS is defined %}
{% for critno in CRITERIA_ITEMS %}
        reportico_criteria_items.push("{{critno.name}}");
{% endfor %}
{% endif %}
    </script>
{% if PDF_DELIVERY_MODE is defined %}
<script type="text/javascript">var reportico_pdf_delivery_mode = "{{ PDF_DELIVERY_MODE }}";</script>
{% endif %}

<FORM class="reportico-prepare-form non-printable" id="criteriaform" name="topmenu" method="POST" action="{{ SCRIPT_SELF }}">
<input type="hidden" name="reportico_session_name" value="{{ SESSION_ID }}" />

<!-- Menu Bar -->
{% include 'prepare-menu-bar.inc.tpl' %}

<!-- Output options -->
{% include 'prepare-design-options.inc.tpl' %}

<!-- Report Title -->
{% include 'prepare-title.inc.tpl' %}

<!-- Report Output options -->
<div class="reportico-prepare-crit-output-options" style="background-color: #ffffff" id="critbody">
{% if SHOW_OUTPUT and not IS_ADMIN_SCREEN %}
{% include 'prepare-output-table-form.inc.tpl' %}
{% include 'prepare-output-formats.inc.tpl' %}
{% include 'prepare-output-setup-options.inc.tpl' %}
{% include 'prepare-output-show-hide-options.inc.tpl' %}
{% endif %}
</div>

<!-- Criteria Items and Expand Box -->
{% if SHOW_CRITERIA %}
<div id="criteriabody">
  <div class="reportico-prepare-crit-box" style="display: table">
    <div id="reportico-prepare-crit-body" style="display: table-row">
      <div class="reportico-prepare-crit-entry" style="float:left;">
         {% include 'prepare-criteria-items-header.inc.tpl' %}
         {% include 'prepare-criteria-items.inc.tpl' %}
         {% include 'prepare-criteria-items-trailer.inc.tpl' %}
      </div>
      <div class="reportico-prepare-expand" style="float:left">
        <div class="reportico-prepare-expandBox">
          <div class="reportico-prepare-expandRow">
            <div id="reportico-prepare-expandCell" valign="top">
               {% include 'prepare-expand-contents.inc.tpl' %}
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>


{% endif %}

</FORM>
{% include 'prepare-modals.inc.tpl' %}

<div class="reportico-show-criteria" style="display:none">
    <a href="#"><span class="glyphicon glyphicon-chevron-down icon-chevron-down" aria-hidden="true"></span></a>
</div>
<div class="reportico-hide-criteria" style="display:none">
    <a href="#"><span class="glyphicon glyphicon-chevron-up icon-chevron-up" aria-hidden="true"></span></a>
</div>

<div id="reportico-report-output">
</div>

</div>

{% include 'footer.inc.tpl' %}

{% endautoescape %}
