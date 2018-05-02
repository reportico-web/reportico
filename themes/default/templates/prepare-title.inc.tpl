<h1 class="reportico-title-bar" >{{ TITLE }}
{% if SHOW_MINIMAINTAIN %} 
{% if not REPORTICO_BOOTSTRAP_MODAL %}
    				<button type="submit" class="prepareMiniMaintain reportico-edit-link" style="margin-right: 30px" title="{{ T_EDIT }} {{ T_EDITTITLE }}" id="submit_mainquerform_SHOW" value="" name="mainquerform_ReportTitle">
                                <span class="glyphicon glyphicon-pencil"></span>
                            </button>
{% else %}
    				<button type="submit" class="btn btn-default prepareMiniMaintain reportico-edit-link" style="margin-right: 30px" title="{{ T_EDIT }} {{ T_EDITTITLE }}" id="submit_mainquerform_SHOW" value="" name="mainquerform_ReportTitle">
                            <span class="glyphicon glyphicon-pencil"></span>
                     </button>
{% endif %}
{% endif %}
</h1>
