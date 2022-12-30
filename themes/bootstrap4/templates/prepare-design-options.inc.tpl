{% autoescape false %}
{% if not IS_ADMIN_SCREEN %}
{% if not REPORTICO_BOOTSTRAP_MODAL %}
    {{ WIDGETS["popup-edit-sql"]["widget"] }}
    {{ WIDGETS["popup-edit-columns"]["widget"] }}
    {{ WIDGETS["popup-edit-assignments"]["widget"] }}
    {{ WIDGETS["popup-edit-groups"]["widget"] }}
    {{ WIDGETS["popup-edit-charts"]["widget"] }}
{% else %}
    <div class="flex-container" style="justify-content: space-between">
        {% if PERMISSIONS["save"] %}
        <div class="flex-container" style="display:inline" id="reportico-bootstrap-collapse-design-options">
                   <div class="" style="display:inline"> {{ T_REPORT_FILE }} </div>
                   <input class="" style="display:inline" type="text" name="xmlout" id="reportico-prepare-save-file" value="{{ XMLFILE }}">
            <input type="submit" class=" btn btn-primary reportico-prepare-save-button" display="inline" type="submit" name="submit_xxx_SAVE" value="{{ T_SAVE }}">
        </div>
        {% endif %}
        <div class="flex-widget" style="justify-content: flex_end">
    		   <div class="input-group">
                   <div class="btn-group" role="group" aria-label="">
                   <button type='submit' class='flex-widget btn btn-sm btn-outline-secondary reportico-edit-link'
                           title='{{ WIDGETS["popup-edit-sql"]["title"] }}' id='{{ WIDGETS["popup-edit-sql"]["id"] }}'
                           name='{{ WIDGETS["popup-edit-sql"]["name"] }}' value='{{ WIDGETS["popup-edit-sql"]["label"] }}' >
                            <i class="fa fa-pen fa-lg"></i>{{ WIDGETS["popup-edit-sql"]["label"] }}
                   </button>
                   <button type='submit' class='flex-widget btn btn-sm btn-outline-secondary reportico-edit-link'
                           title='{{ WIDGETS["popup-edit-columns"]["title"] }}' id='{{ WIDGETS["popup-edit-columns"]["id"] }}'
                           name='{{ WIDGETS["popup-edit-columns"]["name"] }}' value='{{ WIDGETS["popup-edit-columns"]["label"] }}' >
                       {{ WIDGETS["popup-edit-columns"]["label"] }} 
                   </button>
                   <button type='submit' class='flex-widget btn btn-sm btn-outline-secondary reportico-edit-link'
                           title='{{ WIDGETS["popup-edit-assignments"]["title"] }}' id='{{ WIDGETS["popup-edit-assignments"]["id"] }}'
                           name='{{ WIDGETS["popup-edit-assignments"]["name"] }}' value='{{ WIDGETS["popup-edit-assignments"]["label"] }}' >
                       {{ WIDGETS["popup-edit-assignments"]["label"] }}
                   </button>
                   <button type='submit' class='flex-widget btn btn-sm btn-outline-secondary reportico-edit-link'
                           title='{{ WIDGETS["popup-edit-groups"]["title"] }}' id='{{ WIDGETS["popup-edit-groups"]["id"] }}'
                           name='{{ WIDGETS["popup-edit-groups"]["name"] }}' value='{{ WIDGETS["popup-edit-groups"]["label"] }}' >
                       {{ WIDGETS["popup-edit-groups"]["label"] }} 
                   </button>
                   <button type='submit' class='flex-widget btn btn-sm btn-outline-secondary reportico-edit-link'
                           title='{{ WIDGETS["popup-edit-pre-sqls"]["title"] }}' id='{{ WIDGETS["popup-edit-pre-sqls"]["id"] }}'
                           name='{{ WIDGETS["popup-edit-pre-sqls"]["name"] }}' value='{{ WIDGETS["popup-edit-pre-sqls"]["label"] }}' >
                       {{ WIDGETS["popup-edit-pre-sqls"]["label"] }} 
                   </button>
                   </div>
                   <div class="btn-group" role="group" aria-label="">
                   <button type='submit' class='flex-widget ml-5 btn btn-sm btn-outline-secondary reportico-edit-link'
                           title='{{ WIDGETS["popup-edit-page-headers"]["title"] }}' id='{{ WIDGETS["popup-edit-page-headers"]["id"] }}'
                           name='{{ WIDGETS["popup-edit-page-headers"]["name"] }}' value='{{ WIDGETS["popup-edit-page-headers"]["label"] }}' >
                       <i class="fa fa-pen fa-lg"></i>
                       {{ WIDGETS["popup-edit-page-headers"]["label"] }} 
                   </button>
                   <button type='submit' class='flex-widget btn btn-sm btn-outline-secondary reportico-edit-link'
                           title='{{ WIDGETS["popup-edit-page-footers"]["title"] }}' id='{{ WIDGETS["popup-edit-page-footers"]["id"] }}'
                           name='{{ WIDGETS["popup-edit-page-footers"]["name"] }}' value='{{ WIDGETS["popup-edit-page-footers"]["label"] }}' >
                       {{ WIDGETS["popup-edit-page-footers"]["label"] }}
                   </button>
                   <button type='submit' class='flex-widget btn btn-sm btn-outline-secondary reportico-edit-link'
                           title='{{ WIDGETS["popup-edit-display-order"]["title"] }}' id='{{ WIDGETS["popup-edit-display-order"]["id"] }}'
                           name='{{ WIDGETS["popup-edit-display-order"]["name"] }}' value='{{ WIDGETS["popup-edit-display-order"]["label"] }}' >
                       {{ WIDGETS["popup-edit-display-order"]["label"] }}
                   </button>
                   </div>
                   <div class="btn-group" role="group" aria-label="">
                   <button type='submit' class='flex-widget ml-5 btn btn-sm btn-outline-secondary reportico-edit-link'
                           title='{{ WIDGETS["popup-edit-grid"]["title"] }}' id='{{ WIDGETS["popup-edit-grid"]["id"] }}'
                           name='{{ WIDGETS["popup-edit-grid"]["name"] }}' value='{{ WIDGETS["popup-edit-grid"]["label"] }}' >
                       <i class="fa fa-pen fa-lg"></i>
                       {{ WIDGETS["popup-edit-grid"]["label"] }} 
                   </button>
                   <button type='submit' class='flex-widget btn btn-sm btn-outline-secondary reportico-edit-link'
                           title='{{ WIDGETS["popup-edit-code"]["title"] }}' id='{{ WIDGETS["popup-edit-code"]["id"] }}'
                           name='{{ WIDGETS["popup-edit-code"]["name"] }}' value='{{ WIDGETS["popup-edit-code"]["label"] }}' >
                       {{ WIDGETS["popup-edit-code"]["label"] }}
                   </button>
                   <input class='reportico-submit btn btn-outline-secondary' type='submit' name='submit_design_mode' value='{{ T_DESIGN_REPORT }}'>
                   </div>
                </div>
        </div>
</div>
{% endif %}
{% endif %}
{% endautoescape %}

