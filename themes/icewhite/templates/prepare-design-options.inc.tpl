{% autoescape false %}
{% if not REPORTICO_BOOTSTRAP_MODAL %}
    {{ WIDGETS["popup-edit-sql"] }}
    {{ WIDGETS["popup-edit-columns"] }}
    {{ WIDGETS["popup-edit-assignments"] }}
    {{ WIDGETS["popup-edit-groups"] }}
    {{ WIDGETS["popup-edit-charts"] }}
{% else %}
<div class="navbar navbar-default" style="margin-bottom: 0px" role="navigation">
        <div class="container" style="width: 100%">
            <div class="nav-collapse collapse in" id="reportico-bootstrap-collapse">
                <ul class="nav navbar-nav pull-right navbar-right">
                    {% if PERMISSIONS["save"] %}
    				    <li style="margin-right: 40px">
{{ T_REPORT_FILE }} <input type="text" name="xmlout" id="reportico-prepare-save-file" value="{{ XMLFILE }}"> <input type="submit" class="btn btn-primary reportico-prepare-save-button" type="submit" name="submit_xxx_SAVE" value="{{ T_SAVE }}">
                        </li>
    				   <div class="btn-group" role="group">
                    {% endif %}
                    {{ WIDGETS["popup-edit-sql"] }}
                    {{ WIDGETS["popup-edit-columns"] }}
                    {{ WIDGETS["popup-edit-assignments"] }}
                    {{ WIDGETS["popup-edit-groups"] }}
                    {{ WIDGETS["popup-edit-charts"] }}
                    <div class="btn btn-default reportico-edit-linkGroupWithDropDown" role="group">
                        <li class="dropdown"><a class="btn dropdown-toggle reportico-edit-linkGroupDropDown" data-toggle="dropdown" href="#">{{ T_MORE }}<span class="caret"></span></a>
                            <ul class="dropdown-menu reportico-dropdown">
                                {{ WIDGETS["popup-edit-page-headers"] }}
                                {{ WIDGETS["popup-edit-page-footers"] }}
                                {{ WIDGETS["popup-edit-display-order"] }}
                                {{ WIDGETS["popup-edit-pre-sqls"] }}
                                {{ WIDGETS["popup-edit-grid"] }}
                                {{ WIDGETS["popup-edit-code"] }}
                            </ul>
                        </li>
                    </div>
            </div>
            </ul>
        </div>
</div>
</div>
{% endif %}
{% endautoescape %}
