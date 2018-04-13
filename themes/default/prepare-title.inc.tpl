<h1 class="swTitle" >{{ TITLE }}
{% if SHOW_MINIMAINTAIN %} 
{% if not REPORTICO_BOOTSTRAP_MODAL %}
    				<button type="submit" class="prepareMiniMaintain swMiniMaintain" style="margin-right: 30px" title="{{ T_EDIT }} {{ T_EDITTITLE }}" id="submit_mainquerform_SHOW" value="" name="mainquerform_ReportTitle">
                                <span class="glyphicon glyphicon-pencil"></span>
                            </button>
{% else %}
    				<button type="submit" class="btn btn-default prepareMiniMaintain swMiniMaintain" style="margin-right: 30px" title="{{ T_EDIT }} {{ T_EDITTITLE }}" id="submit_mainquerform_SHOW" value="" name="mainquerform_ReportTitle">
                            <span class="glyphicon glyphicon-pencil"></span>
                     </button>
{% endif %}
{% endif %}
</h1>
