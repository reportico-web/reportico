{% if CRITERIA_ITEMS is defined %}
{% if CRITERIA_ITEMS|length > 1 %}
<div id="{{ BOOTSTRAP_STYLE_SMALL_BUTTON }}swPrpSubmitPane">
{% if not IS_ADMIN_SCREEN %}
	<input type="submit" class="{{ BOOTSTRAP_STYLE_GO_BUTTON }}prepareAjaxExecute swHTMLGoBox" id="prepareAjaxExecute" name="submitPrepare" value="{{ T_GO }}">
{% endif %}
    <!--input type="submit" class="reporticoSubmit" name="clearform" value="{{ T_RESET }}"-->
</div>
{% endif %}
{% endif %}
