{% if CRITERIA_ITEMS is defined %}
{% if CRITERIA_ITEMS|length > 1 %}
<div id="{{ BOOTSTRAP_STYLE_SMALL_BUTTON }}reportico-prepare-submitPane">
{% if not IS_ADMIN_SCREEN %}
	<input type="submit" class="{{ BOOTSTRAP_STYLE_GO_BUTTON }}prepareAjaxExecute reportico-html-go-box" id="prepareAjaxExecute" name="submitPrepare" value="{{ T_GO }}">
{% endif %}
    <!--input type="submit" class="reportico-submit" name="clearform" value="{{ T_RESET }}"-->
</div>
{% endif %}
{% endif %}
