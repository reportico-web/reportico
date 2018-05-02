{% if SHOW_MINIMAINTAIN %}

{% if not REPORTICO_BOOTSTRAP_MODAL %}
                    <button type="submit" class="prepareMiniMaintain reportico-edit-link" style="margin-right: 30px" title="{{ T_EDIT }} {{ T_EDITDESCRIPTION }}" id="submit_mainquerform_SHOW" value="" name="mainquerform_ReportDescription">
                                <span class="glyphicon glyphicon-pencil"></span>
                            </button>
{% else %}
                    <button type="submit" class="btn btn-default prepareMiniMaintain reportico-edit-link" style="margin-right: 30px" title="{{ T_EDIT }} {{ T_EDITDESCRIPTION }}" id="submit_mainquerform_SHOW" value="" name="mainquerform_ReportDescription">
                            <span class="glyphicon glyphicon-pencil"></span>
                     </button>
{% endif %}

{% endif %}


{% if not REPORT_DESCRIPTION %}
{{ T_DEFAULT_REPORT_DESCRIPTION|raw }}
{% else %}
&nbsp;<br>{{ REPORT_DESCRIPTION|raw }}
{% endif %}

