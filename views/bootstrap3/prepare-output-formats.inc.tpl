<div class="swPrpToolbarPane" style="padding: 0px 5px; float: left;vertical-align: bottom;text-align: center; border-right: solid 1px #bbb">
<div style="display: none">
    <INPUT type="radio" id="rpt_format_html" name="target_format" value="HTML" {OUTPUT_TYPES[0]}>HTML
    <INPUT type="radio" id="rpt_format_pdf" name="target_format" value="PDF" {OUTPUT_TYPES[1]}>PDF
    <INPUT type="radio" id="rpt_format_csv" name="target_format" value="CSV" {OUTPUT_TYPES[2]}>CSV
</div>
{% if SHOW_HIDE_PREPARE_CSV_BUTTON == "show" %}
    <input type="submit" class="{{ BOOTSTRAP_STYLE_TOOLBAR_BUTTON }}prepareAjaxExecute swCSVBox" title="{{ T_PRINT_CSV }}" id="prepareAjaxExecute" name="submitPrepare" value="">
{% else %}
    <input style="display:none" type="submit" class="{{ BOOTSTRAP_STYLE_TOOLBAR_BUTTON }}prepareAjaxExecute swCSVBox" title="{{ T_PRINT_CSV }}" id="prepareAjaxExecute" name="submitPrepare" value="">
{% endif %}
{% if SHOW_HIDE_PREPARE_PDF_BUTTON == "show" %}
    <input type="submit" class="{{ BOOTSTRAP_STYLE_TOOLBAR_BUTTON }}prepareAjaxExecute swPDFBox" title="{{ T_PRINT_PDF }}" id="prepareAjaxExecute" name="submitPrepare" value="">
{% else %}
    <input style="display:none" type="submit" class="{{ BOOTSTRAP_STYLE_TOOLBAR_BUTTON }}prepareAjaxExecute swPDFBox" title="{{ T_PRINT_PDF }}" id="prepareAjaxExecute" name="submitPrepare" value="">
{% endif %}
{% if SHOW_HIDE_PREPARE_HTML_BUTTON == "show" %}
    <input type="submit" class="{{ BOOTSTRAP_STYLE_TOOLBAR_BUTTON }}prepareAjaxExecute swHTMLBox" title="{{ T_PRINT_HTML }}" id="prepareAjaxExecute" name="submitPrepare" value="">
{% else %}
    <input style="display:none" type="submit" class="{{ BOOTSTRAP_STYLE_TOOLBAR_BUTTON }}prepareAjaxExecute swHTMLBox" title="{{ T_PRINT_HTML }}" id="prepareAjaxExecute" name="submitPrepare" value="">
{% endif %}
{% if SHOW_HIDE_PREPARE_PRINT_HTML_BUTTON == "show" %}
    <input type="submit" class="{{ BOOTSTRAP_STYLE_TOOLBAR_BUTTON }}prepareAjaxExecute swPrintBox" style="margin-right: 30px" title="{{ T_PRINTABLE }}" id="prepareAjaxExecute" name="submitPrepare" value="">
{% else %}
    <input style="display:none" type="submit" class="{{ BOOTSTRAP_STYLE_TOOLBAR_BUTTON }}prepareAjaxExecute swPrintBox" style="margin-right: 30px" title="{{ T_PRINTABLE }}" id="prepareAjaxExecute" name="submitPrepare" value="">
{% endif %}
</div>
