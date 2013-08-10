{if !$REPORTICO_AJAX_CALLED} 
{if !$EMBEDDED_REPORT} 
<!DOCTYPE html>
<HTML>
<HEAD>
<TITLE>{$TITLE}</TITLE>
<LINK id="reportico_css" REL="stylesheet" TYPE="text/css" HREF="{$STYLESHEET}">
{$OUTPUT_ENCODING}
</HEAD>
<BODY class="swPrpBody">
{else}
<LINK id="reportico_css" REL="stylesheet" TYPE="text/css" HREF="{$STYLESHEET}">
{/if}

{literal}
<!--[if IE]>
<style type="text/css">
    .swPrpTextField
    {
        width: 350px;
    }
</style>
<![endif]-->
{/literal}

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
<LINK id="reportico_css" REL="stylesheet" TYPE="text/css" HREF="{/literal}{$JSPATH}{literal}/jquery.jdMenu.css">
<script type="text/javascript">var reportico_datepicker_language = "{/literal}{$AJAX_DATEPICKER_FORMAT}{literal}";</script>
<script type="text/javascript">var reportico_this_script = "{/literal}{$SCRIPT_SELF}{literal}";</script>
<script type="text/javascript">var reportico_ajax_script = "{/literal}{$REPORTICO_AJAX_RUNNER}{literal}";</script>
<script type="text/javascript">var reportico_ajax_mode = "{/literal}{$REPORTICO_AJAX_MODE}{literal}";</script>
{/literal}
<LINK id="reportico_css" REL="stylesheet" TYPE="text/css" HREF="{$JSPATH}/ui/themes/base/jquery.ui.all.css">
{/if}
{/if}
<div id="reportico_container">
{literal}<script type="text/javascript" src="{/literal}{$JSPATH}{literal}/ui/i18n/jquery.ui.datepicker-{/literal}{$AJAX_DATEPICKER_LANGUAGE}{literal}.js"></script>{/literal}
{literal}<script type="text/javascript" src="{/literal}{$JSPATH}{literal}/jquery.jdMenu.js"></script>{/literal}
<LINK id="reportico_css" REL="stylesheet" TYPE="text/css" HREF="{/literal}{$JSPATH}{literal}/jquery.jdMenu.css">
<FORM class="swPrpForm" id="criteriaform" name="topmenu" method="POST" action="{$SCRIPT_SELF}">
<h1 class="swTitle">{$TITLE}</h1>
<input type="hidden" name="session_name" value="{$SESSION_ID}" />
{if $DROPDOWN_MENU_ITEMS}
<ul id="dropmenu" class="jd_menu" style="clear: none;float: left;width: 100%; ">
{section name=menu loop=$DROPDOWN_MENU_ITEMS}
<li style="margin-left: 20px; margin-top: 0px">
<a href="{$MAIN_MENU_URL}&project={$DROPDOWN_MENU_ITEMS[menu].project}">{$DROPDOWN_MENU_ITEMS[menu].title}</a>
<ul style="padding: 0px; margin: 0px">
{section name=menuitem loop=$DROPDOWN_MENU_ITEMS[menu].items}
{if isset($DROPDOWN_MENU_ITEMS[menu].items[menuitem].reportname)}
<li ><a href="{$RUN_REPORT_URL}&project={$DROPDOWN_MENU_ITEMS[menu].project}&xmlin={$DROPDOWN_MENU_ITEMS[menu].items[menuitem].reportfile}">{$DROPDOWN_MENU_ITEMS[menu].items[menuitem].reportname}</a></li>
{/if}
{/section}
</ul>
</li>
{/section}
</ul>
{/if}

{if $SHOW_TOPMENU}
	<TABLE class="swPrpTopMenu">
		<TR>
			<TD style="width: 50%; text-align:left">
{if $SHOW_ADMIN_BUTTON}
{if strlen($ADMIN_MENU_URL)>0} 
                <a class="swLinkMenu" href="{$ADMIN_MENU_URL}">{$T_ADMIN_MENU}</a>
{/if}
{/if}
{if strlen($MAIN_MENU_URL)>0} 
{if $SHOW_PROJECT_MENU_BUTTON}
				<a class="swLinkMenu" href="{$MAIN_MENU_URL}">{$T_PROJECT_MENU}</a>
{/if}
{if $SHOW_DESIGN_BUTTON}
                                &nbsp;<input class="swLinkMenu" type="submit" name="submit_design_mode" value="{$T_DESIGN_REPORT}">
{/if}
{if $OUTPUT_SHOW_DEBUG}
{if $SHOW_DESIGN_BUTTON}
			<TD style="width:15%; text-align: right; padding-right: 10px;" class="swPrpTopMenuCell">
				{$T_DEBUG_LEVEL}
				<SELECT name="debug_mode">';
					<OPTION {$DEBUG_NONE} label="None" value="0">{$T_DEBUG_NONE}</OPTION>
					<OPTION {$DEBUG_LOW} label="Low" value="1">{$T_DEBUG_LOW}</OPTION>
					<OPTION {$DEBUG_MEDIUM} label="Medium" value="2">{$T_DEBUG_MEDIUM}</OPTION>
					<OPTION {$DEBUG_HIGH} label="High" value="3">{$T_DEBUG_HIGH}</OPTION>
				</SELECT>
			</TD>
{/if}
{/if}

{/if}
			</TD>
{if $SHOW_LOGOUT}
			<TD style="width:15%; text-align: right; padding-right: 10px;" class="swPrpTopMenuCell">
				<input class="swLinkMenu" type="submit" name="logout" value="{$T_LOGOFF}">
			</TD>
{/if}
{if $SHOW_LOGIN}
			<TD width="10%"></TD>
			<TD width="55%" align="left" class="swPrpTopMenuCell">
{if strlen($PROJ_PASSWORD_ERROR) > 0}
                                <div style="color: #ff0000;">{$T_PASSWORD_ERROR}</div>
{/if}
				{$T_ENTER_PROJECT_PASSWORD}<br><input type="password" name="project_password" value=""></div>
				<input class="swLinkMenu" type="submit" name="login" value="{$T_LOGIN}">
			</TD>
{/if}
		</TR>
	</TABLE>
{/if}
{if $SHOW_CRITERIA}
    <div style="display: none">
										&nbsp;
										{$T_OUTPUT}
											<INPUT type="radio" id="rpt_format_html" name="target_format" value="HTML" {$OUTPUT_TYPES[0]}>HTML
											<INPUT type="radio" id="rpt_format_pdf" name="target_format" value="PDF" {$OUTPUT_TYPES[1]}>PDF
											<INPUT type="radio" id="rpt_format_csv" name="target_format" value="CSV" {$OUTPUT_TYPES[2]}>CSV
{if $SHOW_DESIGN_BUTTON}
											<INPUT type="radio" id="rpt_format_xml" name="target_format" value="XML" {$OUTPUT_TYPES[3]}>XML
											<INPUT type="radio" id="rpt_format_json" name="target_format" value="JSON" {$OUTPUT_TYPES[4]}>JSON
{/if}
   
    </div>
	<TABLE class="swPrpCritBox" id="critbody">
{if $SHOW_OUTPUT}
        <TR>
            <td>
			<div style="width: 15%; padding-top: 15px;float: left;vertical-align: bottom;text-align: center">
                <b>{$T_REPORT_STYLE}</b>
                <INPUT type="radio" id="rpt_style_detail" name="target_style" value="TABLE" {$OUTPUT_STYLES[0]}>{$T_TABLE}
                <INPUT type="radio" id="rpt_style_form" name="target_style" value="FORM" {$OUTPUT_STYLES[1]}>{$T_FORM}
			</div>
			<div class="swPrpToolbarPane" style="width: 35%; float: left; vertical-align: bottom;text-align: right">
{if $SHOW_DESIGN_BUTTON}
    				<input type="submit" class="prepareAjaxExecute swJSONBox" title="{$T_PRINT_JSON}" id="prepareAjaxExecute" name="submitPrepare" value="">
    				<input type="submit" class="prepareAjaxExecute swXMLBox" style="margin-left: 20px" title="{$T_PRINT_XML}" id="prepareAjaxExecute" name="submitPrepare" value="">
{/if}
    				<input type="submit" class="prepareAjaxExecute swCSVBox" title="{$T_PRINT_CSV}" id="prepareAjaxExecute" name="submitPrepare" value="">
    				<input type="submit" class="prepareAjaxExecute swPDFBox" title="{$T_PRINT_PDF}" id="prepareAjaxExecute" name="submitPrepare" value="">
    				<input type="submit" class="prepareAjaxExecute swHTMLBox" title="{$T_PRINT_HTML}" id="prepareAjaxExecute" name="submitPrepare" value="">
    				<input type="submit" class="prepareAjaxExecute swPrintBox" style="margin-right: 30px" title="{$T_PRINTABLE}" id="prepareAjaxExecute" name="submitPrepare" value="">
			</div>
			<div style="width: 50%; padding-top: 15px;float: left;vertical-align: bottom;text-align: center">
                                  <b>{$T_SHOW}</b>
				<INPUT type="checkbox" style="display:none" name="user_criteria_entered" value="1" checked="1">
				<INPUT type="checkbox" name="target_show_criteria" value="1" {$OUTPUT_SHOWCRITERIA}>{$T_SHOW_CRITERIA}
				<INPUT type="checkbox" name="target_show_group_headers" value="1" {$OUTPUT_SHOWGROUPHEADERS}>{$T_SHOW_GRPHEADERS}
				<INPUT type="checkbox" name="target_show_detail" value="1" {$OUTPUT_SHOWDETAIL}>{$T_SHOW_DETAIL}
				<INPUT type="checkbox" name="target_show_group_trailers" value="1" {$OUTPUT_SHOWGROUPTRAILERS}>{$T_SHOW_GRPTRAILERS}
				<INPUT type="checkbox" name="target_show_column_headers" value="1" {$OUTPUT_SHOWCOLHEADERS}>{$T_SHOW_COLHEADERS}
{if $OUTPUT_SHOW_SHOWGRAPH}
				<INPUT type="checkbox" name="target_show_graph" value="1" {$OUTPUT_SHOWGRAPH}>{$T_SHOW_GRAPH}<BR>
{/if}
			</div>
            </td>
		</TR>
{else}
{/if}
	</TABLE>
<div id="criteriabody">
	<TABLE class="swPrpCritBox" cellpadding="0">
<!---->
		<TR id="swPrpCriteriaBody">
			<TD class="swPrpCritEntry">
			<div id="swPrpSubmitPane">
    				<input type="submit" class="prepareAjaxExecute swHTMLGoBox" id="prepareAjaxExecute" name="submitPrepare" value="{$T_GO}">
    				<input type="submit" class="reporticoSubmit" name="clearform" value="{$T_RESET}">
                    &nbsp;
			</div>

                <TABLE class="swPrpCritEntryBox">
{if isset($CRITERIA_ITEMS)}
{section name=critno loop=$CRITERIA_ITEMS}
                    <tr class="swPrpCritLine" id="criteria_{$CRITERIA_ITEMS[critno].name}">
                        <td class='swPrpCritTitle'>
                            {$CRITERIA_ITEMS[critno].title}
                        </td>
                        <td class="swPrpCritSel">
                            {$CRITERIA_ITEMS[critno].entry}
                        </td>
                        <td class="swPrpCritExpandSel">
{if $CRITERIA_ITEMS[critno].expand}
{if $AJAX_ENABLED} 
                            <input class="swPrpCritExpandButton" id="reporticoPerformExpand" type="button" name="EXPAND_{$CRITERIA_ITEMS[critno].name}" value="{$T_EXPAND}">
{else}
                            <input class="swPrpCritExpandButton" type="submit" name="EXPAND_{$CRITERIA_ITEMS[critno].name}" value="{$T_EXPAND}">
{/if}
{/if}
                        </td>
                    </TR>
{/section}
{/if}
                </TABLE>
{if isset($CRITERIA_ITEMS)}
{if count($CRITERIA_ITEMS) > 1}
<div id="swPrpSubmitPane">
	<input type="submit" class="prepareAjaxExecute swHTMLGoBox" id="prepareAjaxExecute" name="submitPrepare" value="{$T_GO}">
    <!--input type="submit" class="reporticoSubmit" name="clearform" value="{$T_RESET}"-->
</div>
{/if}
{/if}
			</td>
			<TD class="swPrpExpand">
				<TABLE class="swPrpExpandBox">
					<TR class="swPrpExpandRow">
						<TD id="swPrpExpandCell" rowspan="0" valign="top">
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
{if strlen($STATUSMSG)==0 && strlen($ERRORMSG)==0}
<div style="float:right; ">
{if strlen($MAIN_MENU_URL)>0}
<!--a class="swLinkMenu" style="float:left;" href="{$MAIN_MENU_URL}">&lt;&lt; Menu</a-->
{/if}
</div>
<p>
{if $SHOW_EXPANDED}
							{$T_SEARCH} {$EXPANDED_TITLE} :<br><input  type="text" name="expand_value" style="width: 50%" size="30" value="{$EXPANDED_SEARCH_VALUE}"</input>
									<input id="reporticoPerformExpand" class="swPrpSubmit" type="submit" name="EXPANDSEARCH_{$EXPANDED_ITEM}" value="Search"><br>

{$CONTENT}
							<br>
							<input class="swPrpSubmit" type="submit" name="EXPANDCLEAR_{$EXPANDED_ITEM}" value="Clear">
							<input class="swPrpSubmit" type="submit" name="EXPANDSELECTALL_{$EXPANDED_ITEM}" value="Select All">
							<input class="swPrpSubmit" type="submit" name="EXPANDOK_{$EXPANDED_ITEM}" value="OK">
{/if}
{if !$SHOW_EXPANDED}
{if !$REPORT_DESCRIPTION}
{$T_DEFAULT_REPORT_DESCRIPTION}
{else}
						&nbsp;<br>
						{$REPORT_DESCRIPTION}
{/if}
{/if}
{/if}
						</TD>
					</TR>
				</TABLE>
			</TD>
		</TR>
			</TABLE>

{/if}
</div>
			<!---->

</FORM>
<div class="smallbanner">Powered by <a href="http://www.reportico.org/" target="_blank">reportico {$REPORTICO_VERSION}</a></div>
</div>
{if !$REPORTICO_AJAX_CALLED} 
{if !$EMBEDDED_REPORT} 
</BODY>
</HTML>
{/if}
{/if}
