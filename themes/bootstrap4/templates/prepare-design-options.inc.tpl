{% autoescape false %}
{% if not REPORTICO_BOOTSTRAP_MODAL %}
lll
    {{ WIDGETS["popup-edit-sql"]["widget"] }}
    {{ WIDGETS["popup-edit-columns"]["widget"] }}
    {{ WIDGETS["popup-edit-assignments"]["widget"] }}
    {{ WIDGETS["popup-edit-groups"]["widget"] }}
    {{ WIDGETS["popup-edit-charts"]["widget"] }}
{% else %}
    <div class="flex-container" style="justify-content: flex-start space-around">
        {% if PERMISSIONS["save"] %}
        <div class="flex-item" id="reportico-bootstrap-collapse-design-options">
               <div class="input-group">
                    <div class="flex-widget form-control">
                        {{ T_REPORT_FILE }}
                    </div>
                   <input class="form-control flex-widget" type="text" name="xmlout" id="reportico-prepare-save-file" value="{{ XMLFILE }}">
                   <input type="submit" class=" flex-widget form-control btn btn-primary reportico-prepare-save-button" type="submit" name="submit_xxx_SAVE" value="{{ T_SAVE }}">
               </div>
        </div>
        {% endif %}
        <div class="flex-widget" >
    		   <div class="111input-group">
                   <button type='submit' class='flex-widget btn btn-sm btn-outline-secondary reportico-edit-link'
                           title='{{ WIDGETS["popup-edit-sql"]["title"] }}' id='{{ WIDGETS["popup-edit-sql"]["id"] }}'
                           name='{{ WIDGETS["popup-edit-sql"]["name"] }}' value='{{ WIDGETS["popup-edit-sql"]["label"] }}' >
                            {{ WIDGETS["popup-edit-sql"]["label"] }} <i class="fa fa-pen fa-lg"></i>
                   </button>
                   <button type='submit' class='flex-widget btn btn-sm btn-outline-secondary reportico-edit-link'
                           title='{{ WIDGETS["popup-edit-columns"]["title"] }}' id='{{ WIDGETS["popup-edit-columns"]["id"] }}'
                           name='{{ WIDGETS["popup-edit-columns"]["name"] }}' value='{{ WIDGETS["popup-edit-columns"]["label"] }}' >
                       {{ WIDGETS["popup-edit-columns"]["label"] }} <i class="fa fa-pen fa-lg"></i>
                   </button>
                   <button type='submit' class='flex-widget btn btn-sm btn-outline-secondary reportico-edit-link'
                           title='{{ WIDGETS["popup-edit-assignments"]["title"] }}' id='{{ WIDGETS["popup-edit-assignments"]["id"] }}'
                           name='{{ WIDGETS["popup-edit-assignments"]["name"] }}' value='{{ WIDGETS["popup-edit-assignments"]["label"] }}' >
                       {{ WIDGETS["popup-edit-assignments"]["label"] }}
                       <i class="fa fa-pen fa-lg"></i>
                   </button>
                   <button type='submit' class='flex-widget btn btn-sm btn-outline-secondary reportico-edit-link'
                           title='{{ WIDGETS["popup-edit-groups"]["title"] }}' id='{{ WIDGETS["popup-edit-groups"]["id"] }}'
                           name='{{ WIDGETS["popup-edit-groups"]["name"] }}' value='{{ WIDGETS["popup-edit-groups"]["label"] }}' >
                       {{ WIDGETS["popup-edit-groups"]["label"] }} <i class="fa fa-pen fa-lg"></i>
                   </button>
                   <button type='submit' class='flex-widget btn btn-sm btn-outline-secondary reportico-edit-link'
                           title='{{ WIDGETS["popup-edit-charts"]["title"] }}' id='{{ WIDGETS["popup-edit-charts"]["id"] }}'
                           name='{{ WIDGETS["popup-edit-charts"]["name"] }}' value='{{ WIDGETS["popup-edit-charts"]["label"] }}' >
                       {{ WIDGETS["popup-edit-charts"]["label"] }} <i class="fa fa-pen fa-lg"></i>
                   </button>
                    <div class="btn btn-default reportico-edit-linkGroupWithDropDown" role="group">
                        <li class="dropdown"><a class="btn dropdown-toggle reportico-edit-linkGroupDropDown" data-toggle="dropdown" href="#">{{ T_MORE }}<span class="caret"></span></a>
                            <ul class="dropdown-menu reportico-dropdown">
                                {{ WIDGETS["popup-edit-page-headers"]["widget"] }}
                                {{ WIDGETS["popup-edit-page-footers"]["widget"] }}
                                {{ WIDGETS["popup-edit-display-order"]["widget"] }}
                                {{ WIDGETS["popup-edit-pre-sqls"]["widget"] }}
                                {{ WIDGETS["popup-edit-grid"]["widget"] }}
                                {{ WIDGETS["popup-edit-code"]["widget"] }}
                            </ul>
                        </li>
                    </div>
                </div>
        </div>
</div>
{% endif %}
{% endautoescape %}

