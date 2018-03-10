{% autoescape false %}
{% include 'default/header.inc.tpl' %}

<div id="reportico_container">

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

<FORM class="swPrpForm" id="criteriaform" name="topmenu" method="POST" action="{{ SCRIPT_SELF }}">
<input type="hidden" name="reportico_session_name" value="{{ SESSION_ID }}" />

<!-- Menu Bar -->
{% include 'default/prepare-menu-bar.inc.tpl' %}

<!-- Output options -->
{% include 'default/prepare-design-options.inc.tpl' %}

<!-- Report Title -->
{% include 'default/prepare-title.inc.tpl' %}

<!-- Report Output options -->
<div class="swPrpCritBox" style="background-color: #ffffff" id="critbody">
{% if SHOW_OUTPUT and not IS_ADMIN_SCREEN %}
{% include 'default/prepare-output-table-form.inc.tpl' %}
{% include 'default/prepare-output-formats.inc.tpl' %}
{% include 'default/prepare-output-show-hide-options.inc.tpl' %}
{% endif %}
</div>

<!-- Criteria Items and Expand Box -->
{% if SHOW_CRITERIA %}
<div id="criteriabody">
  <div class="swPrpCritBox" style="display: table">
    <div id="swPrpCriteriaBody" style="display: table-row">
      <div class="swPrpCritEntry" style="float:left;">
         {% include 'default/prepare-criteria-items-header.inc.tpl' %}
         {% include 'default/prepare-criteria-items.inc.tpl' %}
         {% include 'default/prepare-criteria-items-trailer.inc.tpl' %}
      </div>
      <div class="swPrpExpand" style="float:left">
        <div class="swPrpExpandBox">
          <div class="swPrpExpandRow">
            <div id="swPrpExpandCell" valign="top">
               {% include 'default/prepare-expand-contents.inc.tpl' %}
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
{% endif %}

</FORM>
{% include 'default/prepare-modals.inc.tpl' %}
{% include 'default/reportico-banner.inc.tpl' %}
</div>
{% include 'default/footer.inc.tpl' %}

{% endautoescape %}
