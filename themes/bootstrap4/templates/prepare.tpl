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
               title='{{ WIDGETS["popup-edit-description"]["title"] }}' id='{{ WIDGETS["popup-edit-description"]["id"] }}'
               name='{{ WIDGETS["popup-edit-description"]["name"] }}' value='{{ WIDGETS["popup-edit-description"]["label"] }}' >
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
  <div id="criteria-block" class="row" style="padding: 8px; border-top: 1px solid #d0ccc9;">

      {# Left hand side Criteria Entry Blocks #}
      <div class="col-xs-6 col-sm-6 col-lg-6 col-md-6" >

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

          <div class="col-lg-12 container">

              {# Tab Menu Headers #}

              {# Display each criteria item #}
              {% set last_tab = "" %}
              {% set tabs_exist = false %}
              {% set firstCriteria = true %}
              {% set tab_count = 0 %}
{% for criterion in CRITERIA_BLOCK %}
{% if criterion.tab and not last_tab and not tabs_exist  %}
{% set tabs_exist = true %}
                  <ul class="nav nav-tabs">
{% endif %}
{% if tabs_exist and criterion.tab and criterion.tab != last_tab  %}
{% set tab_count = tab_count + 1 %}
{% set tab_id = criterion.tab | replace({'\ ': '_'}) %}
{% if not criterion.tabhidden %}
                          <li class="active"><a class="reportico-criteria-tab" data-toggle="tab" href="#tab-content-{{ tab_id }}-{{ tab_count }}">{{ criterion.tab }}</a></li>
{% else %}
                          <li><a class="reportico-criteria-tab" data-toggle="tab" href="#tab-content-{{ tab_id }}-{{ tab_count }}">{{ criterion.tab }}</a></li>
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
              <div class='row {{ criterion.tabclass }}' style="padding: 3px 0px; display: inline">
              {% else %}
              <div class='row {{ criterion.tabclass }}' style="padding: 3px 0px">
              {% endif %}

                  {# Criteria Title and Tooltip #}
                  <div class='col-xs-3 col-sm-3 col-lg-3 col-md-3' style='font-weight: bold'>
                      {% if criterion.tooltip %}
                      <a class='reportico_tooltip' data-toggle="tooltip" data-placement="right" title="{{ criterion.tooltip }}">
                          <span class="fas fa-question-circle"></span>
                      </a>
                      {% endif %}
                      {{ criterion.title }}
                  </div>

                  {# Criteria Selection Widget - text field, datepicker etc #}
                  <div class="col-xs-8 col-sm-8 col-lg-8 col-md-8">
                      {{ criterion.selection }}
                  </div>

                  {# Criteria Expand Button #}
                  <div class="col-xs-1 col-sm-1 col-lg-1 col-md-1">
                      {{ criterion.lookup }}
                  </div>

              </div>



              {% endfor %}
{% if  ( last_tab )  %}
              </div>
{% endif %}

          </div>

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

{% include 'prepare-modals.inc.tpl' %}

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
