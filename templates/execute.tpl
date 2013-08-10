{if !$EMBEDDED_REPORT}
<!DOCTYPE html>
<HTML>
<HEAD>
<TITLE>{$TITLE}</TITLE>
{$OUTPUT_ENCODING}
</HEAD>
{if $REPORT_PAGE_STYLE}
<BODY class="swRepBody" {$REPORT_PAGE_STYLE};">
<LINK id="PRP_StyleSheet" REL="stylesheet" TYPE="text/css" HREF="{$STYLESHEET}">
{else}
<BODY class="swRepBody">
<LINK id="PRP_StyleSheet" REL="stylesheet" TYPE="text/css" HREF="{$STYLESHEET}">
{/if}
{/if}
{if $AJAX_ENABLED}
{if !$REPORTICO_AJAX_PRELOADED}
{literal}
<script type="text/javascript" src="{/literal}{$JSPATH}{literal}/jquery.js"></script>
<script type="text/javascript" src="{/literal}{$JSPATH}{literal}/ui/jquery.ui.core.js"></script>
<script type="text/javascript" src="{/literal}{$JSPATH}{literal}/ui/jquery.ui.datepicker.js"></script>
<script type="text/javascript" src="{/literal}{$JSPATH}{literal}/reportico.js"></script>
<!--LINK id="reportico_css" REL="stylesheet" TYPE="text/css" HREF="{$JSPATH}/ui/themes/base/jquery.ui.all.css"-->
{/literal}
{/if}
{literal}
<script type="text/javascript" src="{/literal}{$JSPATH}{literal}/ui/i18n/jquery.ui.datepicker-{/literal}{$AJAX_DATEPICKER_LANGUAGE}{literal}.js"></script>
<script type="text/javascript" src="{/literal}{$JSPATH}{literal}/jquery.jdMenu.js"></script>
<script type="text/javascript">var reportico_datepicker_language = "{/literal}{$AJAX_DATEPICKER_FORMAT}{literal}";</script>
<script type="text/javascript">var reportico_this_script = "{/literal}{$SCRIPT_SELF}{literal}";</script>
<script type="text/javascript">var reportico_ajax_script = "{/literal}{$REPORTICO_AJAX_RUNNER}{literal}";</script>
<script type="text/javascript">var reportico_ajax_mode = "{/literal}{$REPORTICO_AJAX_MODE}{literal}";</script>
<script type="text/javascript">var reportico_report_title = "{/literal}{$TITLE}{literal}";</script>
<script type="text/javascript">var reportico_css_path = "{/literal}{$STYLESHEET}{literal}";</script>
{/literal}
{/else}
<LINK id="PRP_StyleSheet" REL="stylesheet" TYPE="text/css" HREF="{$STYLESHEET}">
{/if}
{if $PRINTABLE_HTML}
{literal}
<script type="text/javascript">
/*
* Where multiple data tables exist due to graphs
* resize the columns of all tables to match the first
*/
function resizeOutputTables(window)
{
  var tableArr = reportico_jquery(".swRepPage");
  var tableDataRow = reportico_jquery('.swRepResultLine:first');

  var cellWidths = new Array();
  reportico_jquery(tableDataRow).each(function() {
    for(j = 0; j < reportico_jquery(this)[0].cells.length; j++){
       var cell = reportico_jquery(this)[0].cells[j];
       if(!cellWidths[j] || cellWidths[j] < cell.clientWidth) cellWidths[j] = cell.clientWidth;
    }
  });

  var tablect = 0;
  reportico_jquery(tableArr).each(function() {
    tablect++;
    if ( tablect == 1 )
        return;

    reportico_jquery(this).find(".swRepResultLine:first").each(function() {
      for(j = 0; j < reportico_jquery(this)[0].cells.length; j++){
        reportico_jquery(this)[0].cells[j].style.width = cellWidths[j]+'px';
      }
   });
 });
}
</script>
{/literal}
{/if}
<div id="reportico_container">
<div class="swRepForm">
{if strlen($ERRORMSG)>0}
            <TABLE class="swError">
                <TR>
                    <TD>{$ERRORMSG}</TD>
                </TR>
            </TABLE>
{/if}
{if strlen($STATUSMSG)>0} 
			<TABLE class="swStatus">
				<TR>
					<TD>{$STATUSMSG}</TD>
				</TR>
			</TABLE>
{/if}
{if $SHOW_LOGIN}
			<TD width="10%"></TD>
			<TD width="55%" align="left" class="swPrpTopMenuCell">
{if strlen($PROJ_PASSWORD_ERROR) > 0}
                                <div style="color: #ff0000;">{$PASSWORD_ERROR}</div>
{/if}
				Enter the report project password. <br><input type="password" name="project_password" value=""></div>
				<input class="swLinkMenu" type="submit" name="login" value="Login">
			</TD>
{/if}
{$CONTENT}
</div>
</div>
{if !$EMBEDDED_REPORT}
{if !$REPORTICO_AJAX_CALLED}
{if !$EMBEDDED_REPORT}
</BODY>
</HTML>
{/if}
{/if}
{/if}
