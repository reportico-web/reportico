{% autoescape false %}
{% include 'header.inc.tpl' %}

<div id="reportico-container" class="flex-container">

<!-- Widget Initialisation -->
{{ASSETS_INIT}}

<!-- Begin Form -->
{{ WIDGETS["criteria-form"]["begin"] }}

    <!-- Menu Bar -->
    {% include 'navigation-menu.inc.tpl' %}

    {% if PERMISSIONS["design"] %}
    <!-- Output options -->
    {% include 'prepare-design-options.inc.tpl' %}
    {% endif %}

    {% if PERMISSIONS["execute"] %}

    <!-- Report Title -->
    <div class="flex-container" style="justify-content: center">
            <h2 class="flex-widget" style="width: 100%;border-bottom: solid 1px #aaaaaa">
            {{ WIDGETS["title"]["title"] }}
            {% if PERMISSIONS["design"] and WIDGETS["popup-edit-title"]["id"] %}
            <button type='submit' class='flex-widget btn btn-sm btn-outline-secondary reportico-edit-link'
               title='{{ WIDGETS["popup-edit-title"]["title"] }}' id='{{ WIDGETS["popup-edit-title"]["id"] }}'
               name='{{ WIDGETS["popup-edit-title"]["name"] }}' value='{{ WIDGETS["popup-edit-title"]["label"] }}' >
               <i class="fa fa-pen fa-lg"></i>
            </button>
            {% endif %}
            </h2>
    </div>

    <!-- Report Output options -->
    {% if not FLAGS["admin-report-selected"] %}
    <div class="flex-row" >
        <div class="col">
            <div class="flex-container">
                    <div class="flex-item">
                        {% if FLAGS["show_hide_prepare_print_html_button"] %} {{ WIDGETS["output-html-new-window"]["widget"] }} {% endif %}
                        {% if FLAGS["show_hide_prepare_html_button"] %} {{ WIDGETS["output-html-inline"]["widget"] }} {% endif %}
                        {% if FLAGS["show_hide_prepare_pdf_button"] %} {{ WIDGETS["output-pdf"]["widget"] }} {% endif %}
                        {% if FLAGS["show_hide_prepare_csv_button"] %} {{ WIDGETS["output-csv"]["widget"] }} {% endif %}

                        <input type='submit'
                                class='flex-widget btn btn-sm btn-outline-secondary reportico-edit-link'
                                title='{{ WIDGETS["popup-page-setup"]["title"] }}'
                                id='{{ WIDGETS["popup-page-setup"]["id"] }}'
                                name='{{ WIDGETS["popup-page-setup"]["name"] }}'
                                value='Page Setup'
                        >

                    </div>

                    {% if not FLAGS["non-project-operation"] %}
                    <div class="flex-container" style="display:inline" id="reportico-template-options">
                        {# Save Template #}
                        <label class='' style="display:inline" aria-label='Text input with checkbox'>{{ WIDGETS["template"]["label"] }}:</label>
                        <input type='text' class="flex-inline-widget " id='saveTemplate' value='{{ WIDGETS["template"]["file"] }}' >
                        <input type='submit' class='flex-inline-widget btn btn-sm btn-outline-secondary'  name='submitSaveTemplate' id='submitSaveTemplate' value='Save'>
                        &nbsp;
                        {# Load Template #}
                        <SELECT id='loadTemplate' class='flex-inline-widget ' style="width: 150px" name='template_selection'>";
                            {{ WIDGETS["template"]["load-options"] }}
                        </SELECT>
                            <input type='submit' class='btn btn-sm btn-outline-secondary'  name='submitLoadTemplate' id='submitLoadTemplate' value='Load'>
                        <button type='submit' class='flex-inline-widget btn btn-sm btn-danger'  name='submitDeleteTemplate' id='submitDeleteTemplate'>
                            <i class="fa fa-trash-alt fa-lg"></i>
                        </button>
                    </div>
                    {% endif %}
            </div>
        </div>

    </div>
    {% endif %}


  {# Criteria Midsection Main Selection Block #}
  <div id="criteria-block" class="flex-container" style="padding: 8px; border-top: 1px solid #d0ccc9;">

      {# Left hand side Criteria Entry Blocks #}
      <div class="col-6" >

          <div class="d-flex" style="width: 100%">

              {% if PERMISSIONS["design"] and WIDGETS["popup-edit-criteria"]["id"] %}
              {# Criteria Edit Button #}
              <div >
              <button type='submit'
                 class='flex-widget btn btn-sm btn-outline-primary reportico-edit-link'
                 title='{{ WIDGETS["popup-edit-criteria"]["title"] }}'
                 id='{{ WIDGETS["popup-edit-criteria"]["id"] }}'
                 name='{{ WIDGETS["popup-edit-criteria"]["name"] }}'
                 value='{{ WIDGETS["popup-edit-criteria"]["label"] }}'
              >
              {{ WIDGETS["popup-edit-criteria"]["label"] }} <i class="fa fa-pen fa-lg"></i>
              </button>
              </div>
              {% endif %}
              <div class="ml-auto ">
              {% if FLAGS["admin-report-selected"] or FLAGS["show_hide_prepare_go_buttons"] %}
              {{ WIDGETS["submit-go"]["widget"] }}
              {% endif %}
              </div>
          </div>

          <!-div class="d-flex bg-secondary"-->

          {# Tab Menu Contents #}
          {# Display each criteria item #}
          {% set last_tab = "" %}
          {% set tabs_exist = false %}
          {% set tab_count = 1 %}

              {% for criterion in CRITERIA_BLOCK %}

                  {% if not criterion.tab %}

                      {# Criteria entry selection #}
                      {% if criterion.hidden %}
                          <div class='d-flex' style="padding: 3px 0px; display: none !important">
                      {% else %}
                          <div class='d-flex' style="padding: 3px 0px;">
                      {% endif %}

                      {# Criteria Title and Tooltip #}
                          <div class="d-inline-block col-3 p-0">
                              {% if criterion.tooltip %}
                              <a class='reportico_tooltip' data-toggle="tooltip" data-placement="right" title="{{ criterion.tooltip }}">
                                  <span class="fas fa-question-circle"></span>
                              </a>
                              {% endif %}
                              {{ criterion.title }}
                          </div>

                          {# Criteria Selection Widget - text field, datepicker etc #}
                          <div class="d-inline-block flex-grow-1" >
                              {{ criterion.selection }}
                          </div>

                          {# Criteria Expand Button #}
                          <div class="d-inline-block col-1 ml-auto">
                              {{ criterion.lookup }}
                          </div>

                      </div>

                  {% endif %}


                  {% endfor %}

              {# Tab Menu Headers #}

              {# Display each criteria item #}
              {% set last_tab = "" %}
              {% set tabs_exist = false %}
              {% set firstCriteria = true %}
              {% set tabActive = "show active" %}
              {% set tab_count = 0 %}
{% set activeTab = "XXX" %}
{% for criterion in CRITERIA_BLOCK %}
{% if criterion.tab and not last_tab and not tabs_exist  %}
{% set tabs_exist = true %}
                  <ul class="nav nav-pills" role="tablist">
{% endif %}
{% if tabs_exist and criterion.tab and criterion.tab != last_tab  %}

{% if tab_count == 0  %}
{% set activeTab = criterion.tab %}
{% for criterion2 in CRITERIA_BLOCK %}
{% if not criterion2.tabhidden %}
                      {% set activeTab = criterion2.tab %}
{% endif %}
{% endfor %}
{% endif %}

{% set tab_count = tab_count + 1 %}
{% set tab_id = criterion.tab | replace({'\ ': '_'}) %}
{% if criterion.tab == activeTab %}
                          <li class="nav-item"><a id="{{ criterion.tab }}" class="nav-link reportico-criteria-tab {{ tabActive }}" data-toggle="tab" href="#tab-content-{{ tab_id }}-{{ tab_count }}">{{ criterion.tab }}</a></li>
{% else %}
                          <li class="nav-item" ><a id="{{ criterion.tab }}" class="nav-link reportico-criteria-tab " data-toggle="tab" href="#tab-content-{{ tab_id }}-{{ tab_count }}">{{ criterion.tab }}</a></li>
{% endif %}
{% endif %}
{% set last_tab = criterion.tab %}
{% set firstCriteria = false %}
{% endfor %}

              {% if ( tabs_exist )  %}
                </ul>
              {% endif %}

              {# Tab Menu Contents #}
              {# Display each criteria item #}
              {% set last_tab = "" %}
              {% set tabs_exist = false %}
              {% set tab_count = 1 %}

              <div class="tab-content" style="padding-top: 10px">

              {% for criterion in CRITERIA_BLOCK %}
              {% if criterion.tab %}

              {% set tab_id = criterion.tab | replace({'\ ': '_'}) %}
              {% set tabActive = "" %}
              {% if criterion.tab and criterion.tab == activeTab %}
                  {% set tabActive = "active show" %}
              {% endif %}
              {% if criterion.tab and ( not last_tab )  %}
                  {% set tabs_exist = true %}
                  <div id="tab-content-{{ tab_id }}-{{ tab_count }}" class="tab-pane fade {{ tabActive }}">
                      {% set tab_count = tab_count + 1 %}
              {% else %}
                  {% if ( last_tab != criterion.tab )  %}
                  </div>
                  {% endif %}
                  {% if ( last_tab != criterion.tab ) and criterion.tab %}
                  <div id="tab-content-{{ tab_id }}-{{ tab_count }}" class="tab-pane fade {{ tabActive }}">
                      {% set tab_count = tab_count + 1 %}
                  {% endif %}
              {% endif %}

              {% set last_tab = criterion.tab %}

              {# Criteria entry selection #}
              {% if criterion.hidden %}
              <div class='d-flex {{ criterion.tabclass }}' style="padding: 3px 0px; display: inline">
              {% else %}
              <div class='d-flex {{ criterion.tabclass }}' style="padding: 3px 0px">
              {% endif %}

                  {# Criteria Title and Tooltip #}
                  <div class="d-inline-block col-3 p-0">
                      {% if criterion.tooltip %}
                      <a class='reportico_tooltip' data-toggle="tooltip" data-placement="right" title="{{ criterion.tooltip }}">
                          <span class="fas fa-question-circle"></span>
                      </a>
                      {% endif %}
                      {{ criterion.title }}
                  </div>

                  {# Criteria Selection Widget - text field, datepicker etc #}
                  <div class="d-inline-block flex-grow-1" >
                      {{ criterion.selection }}
                  </div>

                  {# Criteria Expand Button #}
                  <div class="d-inline-block ml-auto col-1">
                      {{ criterion.lookup }}
                  </div>

              </div>



                  {% endif %}
              {% endfor %}
{% if  ( last_tab )  %}
              </div>
{% endif %}

          <!--/div--->

          </div>

          {% if not FLAGS["admin-report-selected"] and FLAGS["show_hide_prepare_reset_buttons"] %}
          {{ WIDGETS["submit-reset"] }}
          {% endif %}

      </div>

      {# Right hand side - Report Description and Lookup area #}
      <div class="col-sm-6 col-lg-6 col-md-6" id="reportico-prepare-expand-cell" >

           {% include 'prepare-expand-contents.inc.tpl' %}
      </div>
    </div>

          {% endif %}

{{ WIDGETS["criteria-form"]["end"] }}
<!-- End Form -->
          <!-- Button trigger modal -->
          <div class="modal fade show" id="reporticoModal" tabindex="-1" role="dialog" aria-labelledby="reporticoModal" aria-hidden="true">
              <div class="modal-dialog modal-lg" role="document">
                  <div class="modal-content">
                      <div class="modal-header">
                          <h4 class="modal-title reportico-modal-title" id="reporticoModalLabel">Edit Parameter</h4>
                          <button type="button" class="close reportico-bootstrap-modal-close" data-dismiss="modal" aria-label="Close">
                              <span aria-hidden="true">&times;</span>
                          </button>
                      </div>
                      <div class="modal-body" style="overflow-y: auto; padding: 0px" id="reporticoModalBody">
                          <h3>Modal Body</h3>
                      </div>
                      <div class="modal-footer">
                          <button type="button" data-dismiss="modal" class="btn btn-primary reportico-edit-linkSubmit" >Close</button>
                      </div>
                  </div>
              </div>
          </div>
          <a id="a_reporticoNoticeModal" href="#reporticoNoticeModal" role="button" class="btn" data-target="#reporticoNoticeModal" data-toggle="modal" style="display:none">B2</a>
          <div class="modal fade show" id="reporticoNoticeModal" tabindex="-1" role="dialog" aria-labelledby="reporticoNoticeModal" aria-hidden="true">
              <div class="modal-dialog modal-lg">
                  <div class="modal-content">
                      <div class="modal-header">
                          <button type="button" data-dismiss="modal" class="close reportico-notice-modal-close" aria-hidden="true">&times;</button>
                          <h4 class="modal-title reportico-notice-modal-title" id="reporticoNoticeModalLabel"></h4>
                      </div>
                      <div class="modal-body" style="overflow-y: auto; padding: 0px" id="reporticoNoticeModalBody">
                          <h3>Modal Body</h3>
                      </div>
                      <div class="modal-footer">
                          <button type="button" class="btn btn-primary reportico-notice-modal-button" data-dismiss="modal">Close</button>
                      </div>
                  </div>
              </div>
          </div>
{# include 'prepare-modals.inc.tpl' #}

{# After running inline HTML criteria block hides, this widget allows unhiding of it after running report #}

      {# Toggle criteria switch #}
      <div class='reportico-show-criteria non-printable' style='display:none; margin:1px'>
          <a href='#' title='Show Criteria'>
              <i class="fa fa-chevron-down fa-lg btn btn-success" ></i>
          </a>
      </div>
      <div class='reportico-hide-criteria non-printable' style='display:none; margin:1px'>
          <a href='#' title='Hide Criteria'>
              <i class="fa fa-chevron-up fa-lg  btn btn-success"></i>
          </a>
      </div>
      <div id='reportico-report-output'>
          {# WIDGETS["powered-by-banner"] #}
      </div>
</div>

{% include 'footer.inc.tpl' %}

{% endautoescape %}
