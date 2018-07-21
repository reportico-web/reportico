			<div id="reportico-prepare-submitPane">
{% if not IS_ADMIN_SCREEN %}
{% if SHOW_HIDE_PREPARE_GO_BUTTONS == "show" %}
    				<input type="submit" class="{{ BOOTSTRAP_STYLE_GO_BUTTON }}prepareAjaxExecute reportico-html-go-box" id="prepareAjaxExecute" name="submitPrepare" value="{{ T_GO }}">
{% endif %}
{% if SHOW_HIDE_PREPARE_RESET_BUTTONS == "show" %}
    				<input type="submit" class="{{ BOOTSTRAP_STYLE_RESET_BUTTON }}reportico-submit" name="clearform" value="{{ T_RESET }}">
{% endif %}
{% else %}
    				<input type="submit" class="{{ BOOTSTRAP_STYLE_GO_BUTTON }}prepareAjaxExecute reportico-html-go-box" id="prepareAjaxExecute" name="submitPrepare" value="{{ T_GO }}">
{% endif %}
{% if SHOW_MINIMAINTAIN %} 
<div style="float: left">
{% if not REPORTICO_BOOTSTRAP_MODAL %}
                    <button type="submit" class="btn btn-default prepareMiniMaintain reportico-edit-link" style="margin-right: 30px" title="{{ T_EDIT }} {{ T_EDITCRITERIA }}" id="submit_mainquercrit" value="{{ T_EDITCRITERIA }}" name="mainquercrit_ANY">
                        <span class="glyphicon glyphicon-pencil icon-pencil"></span>{{ T_EDITCRITERIA }}
                    </button>
{% else %}
                    <button type="submit" class="btn btn-default prepareMiniMaintain reportico-edit-link" style="margin-right: 30px" title="{{ T_EDIT }} {{ T_EDITCRITERIA }}" id="submit_mainquercrit" value="{{ T_EDITCRITERIA }}" name="mainquercrit_ANY">
                        <span class="glyphicon glyphicon-pencil icon-pencil"></span>{{ T_EDITCRITERIA }}
                    </button>
{% endif %}
</div>
{% endif %}
                    &nbsp;
			</div>
