{% autoescape false %}
{% include 'header.inc.tpl' %}

<div id="reportico-container">

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
            <h4 class="flex-widget" style="width: 100%;border-bottom: solid 1px #aaaaaa">
            {{ WIDGETS["title"]["title"] }}
            {% if PERMISSIONS["design"] %}
            <button type='submit' class='flex-widget btn btn-sm btn-outline-secondary reportico-edit-link'
               title='{{ WIDGETS["popup-edit-title"]["title"] }}' id='{{ WIDGETS["popup-edit-title"]["id"] }}'
               name='{{ WIDGETS["popup-edit-title"]["name"] }}' value='{{ WIDGETS["popup-edit-title"]["label"] }}' >
               <i class="fa fa-pen fa-lg"></i>
            </button>
            {% endif %}
            </h4>
    </div>

    <!-- Report Output options -->
    {% if not FLAGS["admin-report-selected"] %}
    <div class="row" >
        <div class="col">
            <div class="flex-container">
                    <div class="flex-item">
                        {% if FLAGS["show_hide_prepare_print_html_button"] %} {{ WIDGETS["output-html-new-window"] }} {% endif %}
                        {% if FLAGS["show_hide_prepare_html_button"] %} {{ WIDGETS["output-html-inline"] }} {% endif %}
                        {% if FLAGS["show_hide_prepare_pdf_button"] %} {{ WIDGETS["output-pdf"] }} {% endif %}
                        {% if FLAGS["show_hide_prepare_csv_button"] %} {{ WIDGETS["output-csv"] }} {% endif %}

                            {# {{ WIDGETS["popup-page-setup"]["widget"] }} #}
                        <input type='submit'
                                class='flex-widget btn btn-primary reportico-edit-link'
                                title='{{ WIDGETS["popup-page-setup"]["title"] }}'
                                id='{{ WIDGETS["popup-page-setup"]["id"] }}'
                                name='{{ WIDGETS["popup-page-setup"]["name"] }}'
                                value='Page Setup'
                        >

                    </div>

                    <div class="flex-container" style="display:inline" id="reportico-template-options">
                        {# Save Template #}
                        <label class='' style="display:inline" aria-label='Text input with checkbox'>{{ WIDGETS["template"]["label"] }}:</label>
                        <input type='text' class="flex-inline-widget " id='saveTemplate' value='{{ WIDGETS["template"]["file"] }}' >
                        <input type='submit' class='flex-inline-widget btn btn-sm btn-secondary'  name='submitSaveTemplate' id='submitSaveTemplate' value='Save'>
                        &nbsp;
                        {# Load Template #}
                        <SELECT id='loadTemplate' class='flex-inline-widget btn-outline-secondary' style="width: 150px" name='template_selection'>";
                            {{ WIDGETS["template"]["load-options"] }}
                        </SELECT>
                            <input type='submit' class='btn btn-sm btn-secondary'  name='submitLoadTemplate' id='submitLoadTemplate' value='Load'>
                        <button type='submit' class='flex-inline-widget btn btn-sm btn-danger'  name='submitDeleteTemplate' id='submitDeleteTemplate'>
                            <i class="fa fa-trash-alt fa-lg"></i>
                        </button>
                    </div>
            </div>
        </div>

    </div>
    {% endif %}


  {# Criteria Midsection Main Selection Block #}
  <div id="criteria-block" class="flex-container" style="padding: 8px; border-top: 1px solid #d0ccc9;">

      {# Left hand side Criteria Entry Blocks #}
      <div class="col-6" >

          {% if FLAGS["admin-report-selected"] or FLAGS["show_hide_prepare_go_buttons"] %}
          {{ WIDGETS["submit-go"] }}
          {% endif %}

          {% if PERMISSIONS["design"] %}
          {# Criteria Edit Button #}
          <button type='submit'
                 class='flex-widget btn btn-sm btn-outline-primary reportico-edit-link'
                 title='{{ WIDGETS["popup-edit-criteria"]["title"] }}'
                 id='{{ WIDGETS["popup-edit-criteria"]["id"] }}'
                 name='{{ WIDGETS["popup-edit-criteria"]["name"] }}'
                 value='{{ WIDGETS["popup-edit-criteria"]["label"] }}'
          >
              {{ WIDGETS["popup-edit-criteria"]["label"] }} <i class="fa fa-pen fa-lg"></i>
          </button>
          {% endif %}

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
                          <div class='d-flex' style="padding: 3px 0px;">
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
              {% set tabActive = "active" %}
              {% set tab_count = 0 %}
{% for criterion in CRITERIA_BLOCK %}
{% if criterion.tab and not last_tab and not tabs_exist  %}
{% set tabs_exist = true %}
                  <ul class="nav nav-tabs" role="tablist">
{% endif %}
{% if tabs_exist and criterion.tab and criterion.tab != last_tab  %}
{% set tab_count = tab_count + 1 %}
{% set tab_id = criterion.tab | replace({'\ ': '_'}) %}
{% if not criterion.tabhidden %}
                          <li class="nav-item"><a class="nav-link reportico-criteria-tab {{ tabActive }}" data-toggle="tab" href="#tab-content-{{ tab_id }}-{{ tab_count }}">{{ criterion.tab }}</a></li>
{% else %}
                          <li class="nav-item"><a class="nav-link reportico-criteria-tab {{ tabActive }}" data-toggle="tab" href="#tab-content-{{ tab_id }}-{{ tab_count }}">{{ criterion.tab }}</a></li>
{% endif %}
{% endif %}
{% set last_tab = criterion.tab %}
{% set firstCriteria = false %}
{% set tabActive = "" %}
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
              {% if criterion.tab and ( not last_tab )  %}
                  {% set tabs_exist = true %}
                  <div id="tab-content-{{ tab_id }}-{{ tab_count }}" class="tab-pane fade in active">
                      {% set tab_count = tab_count + 1 %}
              {% else %}
                  {% if ( last_tab != criterion.tab )  %}
                  </div>
                  {% endif %}
                  {% if ( last_tab != criterion.tab ) and criterion.tab %}
                  <div id="tab-content-{{ tab_id }}-{{ tab_count }}" class="tab-pane fade">
                      {% set tab_count = tab_count + 1 %}
                  {% endif %}
              {% endif %}

              {# Criteria grouped into collapsible tabs #}
              {% if criterion.tab and ( criterion.tab != last_tab )  %}
              <!--div class="row reportico-toggleCriteriaDiv" id="reportico-toggleCriteriaDiv{{ criterion.id }}">
                      {% if criterion.hidden  %}
                      <a class="reportico-toggleCriteria" id="reportico-toggleCriteria{{ criterion.id }}" href="javascript:toggleCriteria('{{ criterion.id }}')">+</a>
                      {% else %}
                      <a class="reportico-toggleCriteria" id="reportico-toggleCriteria{{ criterion.id }}" href="javascript:toggleCriteria('{{ criterion.id }}')">-</a>
                      {% endif %}
                      {{ criterion.tab }}
              </div-->
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
          <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#reporticoModal">
              Launch demo modal
          </button>

          <div class="modal fade show" id="reporticoModal" tabindex="-1" role="dialog" aria-labelledby="reporticoModal" aria-hidden="true">
              <div class="modal-dialog modal-lg" role="document">
                  <div class="modal-content">
                      <div class="modal-header">
                          <h5 class="modal-title" id="exampleModalLabel">Modal title</h5>
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
                          <button type="button" data-dismiss="modal" class="close" aria-hidden="true">&times;</button>
                          <h4 class="modal-title reportico-notice-modal-title" id="reporticoNoticeModalLabel">'.$notice.'</h4>
                      </div>
                      <div class="modal-body" style="overflow-y: auto; padding: 0px" id="reporticoNoticeModalBody">
                          <h3>Modal Body</h3>
                      </div>
                      <div class="modal-footer">
                          <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
                      </div>
                  </div>
              </div>
          </div>
{# include 'prepare-modals.inc.tpl' #}

{# After running inline HTML criteria block hides, this widget allows unhiding of it after running report #}

      {# Toggle criteria switch #}
      <div class='reportico-show-criteria' style='display:none'>
          <a href='#'>
              <i class="fa fa-chevron-down fa-lg"></i>
          </a>
      </div>
      <div class='reportico-hide-criteria' style='display:none'>
          <a href='#'>
              <i class="fa fa-chevron-up fa-lg"></i>
          </a>
      </div>
      <div id='reportico-report-output'>

{{ WIDGETS["powered-by-banner"] }}
</div>

{% include 'footer.inc.tpl' %}

{% endautoescape %}
