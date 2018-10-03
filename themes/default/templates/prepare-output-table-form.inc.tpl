{% if SHOW_HIDE_PREPARE_PAGE_STYLE == "show" %}
			<!--div style="padding: 10px 15px; float: left;vertical-align: bottom;text-align: center; border-right: solid 1px #bbb">
{% else %}
			<!--div style="display:none; width: 20%; padding-top: 15px;float: left;vertical-align: bottom;text-align: center">
{% endif %}
                <b>{{ T_REPORT_STYLE }}</b>

<div class="btn-group" data-toggle="buttons">
  <label class="btn btn-primary active" style="padding: 2px 4px">
    <input type="radio" name="target_style" id="rpt_style_detail" autocomplete="off" value="TABLE" {OUTPUT_STYLES[0]}>{{ T_TABLE }}
  </label>
  <label class="btn btn-primary" style="padding: 2px 4px">
    <input type="radio" name="target_style" id="rpt_style_form" autocomplete="off" value="FORM" {OUTPUT_STYLES[1]}>{{ T_FORM }}
  </label>
</div>

			</div-->
