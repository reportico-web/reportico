{% autoescape false %}
{% if not REPORTICO_BOOTSTRAP_MODAL %}
    {{ WIDGETS["popup-edit-sql"]["widget"] }}
    {{ WIDGETS["popup-edit-columns"]["widget"] }}
    {{ WIDGETS["popup-edit-assignments"]["widget"] }}
    {{ WIDGETS["popup-edit-groups"]["widget"] }}
    {{ WIDGETS["popup-edit-charts"]["widget"] }}
{% else %}
<div class="navbar navbar-default" style="margin-bottom: 0px" role="navigation">
    <div class="container" style="width: 100%">
        <div class="nav-collapse collapse in" id="reportico-bootstrap-collapse-design-options">
            <ul class="nav navbar-nav pull-right navbar-right">
                {% if PERMISSIONS["save"] %}
                <li style="margin-right: 40px">
{{ T_REPORT_FILE }} <input type="text" name="xmlout" id="reportico-prepare-save-file" value="{{ XMLFILE }}"> <input type="submit" class="btn btn-primary reportico-prepare-save-button" type="submit" name="submit_xxx_SAVE" value="{{ T_SAVE }}">
                </li>
                <li>
                    <div class="btn-group" role="group">
                        {% endif %}
                        {{ WIDGETS["popup-edit-sql"]["widget"] }}
                        {{ WIDGETS["popup-edit-columns"]["widget"] }}
                        {{ WIDGETS["popup-edit-assignments"]["widget"] }}
                        {{ WIDGETS["popup-edit-groups"]["widget"] }}
                        {{ WIDGETS["popup-edit-charts"]["widget"] }}
                        <div class="btn btn-default reportico-edit-linkGroupWithDropDown" role="group">
                            <div class="dropdown"><a class="btn dropdown-toggle reportico-edit-linkGroupDropDown" data-toggle="dropdown" href="#">{{ T_MORE }}<span class="caret"></span></a>
                                <ul class="dropdown-menu reportico-dropdown">
                                    {{ WIDGETS["popup-edit-page-headers"]["widget"] }}
                                    {{ WIDGETS["popup-edit-page-footers"]["widget"] }}
                                    {{ WIDGETS["popup-edit-display-order"]["widget"] }}
                                    {{ WIDGETS["popup-edit-pre-sqls"]["widget"] }}
                                    {{ WIDGETS["popup-edit-grid"]["widget"] }}
                                    {{ WIDGETS["popup-edit-code"]["widget"] }}
                                </ul>
                            </div>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</div>
{% endif %}
{% endautoescape %}

