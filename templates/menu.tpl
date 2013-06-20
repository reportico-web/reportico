{if !$REPORTICO_AJAX_CALLED}
{if !$EMBEDDED_REPORT} 
<!DOCTYPE html>
<HTML>
<HEAD>
<TITLE>{$TITLE}</TITLE>
{$OUTPUT_ENCODING}
</HEAD>
<LINK id="reportico_css" REL="stylesheet" TYPE="text/css" HREF="{$STYLESHEET}">
<BODY class="swMenuBody">
{else}
<LINK id="reportico_css" REL="stylesheet" TYPE="text/css" HREF="{$STYLESHEET}">
{/if}
{if $AJAX_ENABLED} 
{if !$REPORTICO_AJAX_PRELOADED}
{literal}
<script type="text/javascript" src="{/literal}{$JSPATH}{literal}/jquery.js"></script>
<script type="text/javascript" src="{/literal}{$JSPATH}{literal}/ui/jquery.ui.core.js"></script>
<script type="text/javascript" src="{/literal}{$JSPATH}{literal}/ui/jquery.ui.datepicker.js"></script>
<script type="text/javascript" src="{/literal}{$JSPATH}{literal}/reportico.js"></script>
{/literal}
{/if}
{literal}
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
{literal}<LINK id="reportico_css" REL="stylesheet" TYPE="text/css" HREF="{/literal}{$JSPATH}{literal}/jquery.jdMenu.css">{/literal}
<FORM class="swMenuForm" name="topmenu" method="POST" action="{$SCRIPT_SELF}">
<H1 class="swTitle">{$TITLE}</H1>
<input type="hidden" name="session_name" value="{$SESSION_ID}" /> 
{if $SHOW_TOPMENU}
	<TABLE class="swPrpTopMenu">
		<TR>
                        <TD class="swPrpTopMenuCell" style="width: 40%">
{if ($SHOW_ADMIN_BUTTON)}
			<a class="swLinkMenu" href="{$ADMIN_MENU_URL}">{$T_ADMIN_HOME}</a>
{/if}
{if ($SHOW_DESIGN_BUTTON)}
{if !$DEMO_MODE}
			<a class="swLinkMenu" href="{$CONFIGURE_PROJECT_URL}">{$T_CONFIG_PROJECT}</a>
			<a class="swLinkMenu" href="{$CREATE_REPORT_URL}">{$T_CREATE_REPORT}</a>
{/if}
{/if}
			</TD>
{if strlen($DBUSER)>0} 
			<TD class="swPrpTopMenuCell">{$T_LOGGED_IN_AS} {$DBUSER}</TD>
{/if}
{if strlen($DBUSER)==0} 
{/if}
{if strlen($MAIN_MENU_URL)>0} 
{/if}
{if $SHOW_LOGIN}
			<TD align="left" class="swPrpTopMenuCell">
<br><br><br><br>
{if strlen($PROJ_PASSWORD_ERROR) > 0}
                                <div style="color: #ff0000;">{$T_PASSWORD_ERROR}</div>
{/if}
{if $DEMO_MODE}
{$T_ENTER_PROJECT_PASSWORD_DEMO}
{else}
{$T_ENTER_PROJECT_PASSWORD}
{/if}
<BR><input type="password" name="project_password" value=""></div>
				<input class="swLinkMenu reporticoSubmit" type="submit" name="login" value="{$T_LOGIN}"><br><br><br><br><br>
			</TD>
{/if}
			<TD style="text-align: center">
{if count($LANGUAGES) > 1 || ($SHOW_DESIGN_BUTTON)}
&nbsp; &nbsp; &nbsp; &nbsp;
                {$T_CHOOSE_LANGUAGE}<BR>
                <select class="swPrpDropSelectRegular" name="jump_to_language">
{section name=menuitem loop=$LANGUAGES}
{strip}
{if $LANGUAGES[menuitem].active }
                <OPTION label="{$LANGUAGES[menuitem].label}" selected value="{$LANGUAGES[menuitem].value}">{$LANGUAGES[menuitem].label}</OPTION>
{else}
                <OPTION label="{$LANGUAGES[menuitem].label}" value="{$LANGUAGES[menuitem].value}">{$LANGUAGES[menuitem].label}</OPTION>
{/if}
{/strip}
{/section}
                </select>
                <input class="swMntButton reporticoSubmit" type="submit" name="submit_language" value="{$T_GO}">
{/if}
			</TD>
{if $SHOW_LOGOUT}
			<TD width="15%" style="padding-left: 10px; text-align: right;" class="swPrpTopMenuCell">
				<input class="swLinkMenu reporticoSubmit" type="submit" name="logout" value="{$T_LOGOFF}">
			</TD>
{/if}
		</TR>
	</TABLE>
{/if}

{if $SHOW_REPORT_MENU}
{if $DROPDOWN_MENU_ITEMS}
<ul id="dropmenu" class="jd_menu" style="clear: none;float: left;width: 100%; ">
{section name=menu loop=$DROPDOWN_MENU_ITEMS}
<li style="margin-left: 20px; margin-top: 0px">
<a href="{$MAIN_MENU_URL}&project={$DROPDOWN_MENU_ITEMS[menu].project}">{$DROPDOWN_MENU_ITEMS[menu].title}</a>
<ul style="padding: 0px; margin: 0px">
{section name=menuitem loop=$DROPDOWN_MENU_ITEMS[menu].items}
<li ><a href="{$RUN_REPORT_URL}&project={$DROPDOWN_MENU_ITEMS[menu].project}&xmlin={$DROPDOWN_MENU_ITEMS[menu].items[menuitem].reportfile}">{$DROPDOWN_MENU_ITEMS[menu].items[menuitem].reportname}</a></li>
{/section}
</ul>
</li>
{/section}
</ul>
{/if}
	<TABLE class="swMenu">
		<TR> <TD>&nbsp;</TD> </TR>
{section name=menuitem loop=$MENU_ITEMS}
{strip}
		<TR> 
			<TD class="swMenuItem">
{if $MENU_ITEMS[menuitem].label == "BLANKLINE"}
				&nbsp;
{else}
{if $MENU_ITEMS[menuitem].label == "LINE"}
				<hr>
{else}
				<a class="swMenuItemLink" href="{$MENU_ITEMS[menuitem].url}">{$MENU_ITEMS[menuitem].label}</a>
{/if}
{/if}
			</TD>
		</TR>
{/strip}
{/section}
		
		<TR> <TD>&nbsp;</TD> </TR>
	</TABLE>
{/if}

{if strlen($ERRORMSG)>0} 
			<TABLE class="swError">
				<TR>
					<TD>{$ERRORMSG}</TD>
				</TR>
			</TABLE>
{/if}
</FORM>
<div class="smallbanner">Powered by <a href="http://www.reportico.org/" target="_blank">reportico {$REPORTICO_VERSION}</a></div>
</DIV>
{if !$REPORTICO_AJAX_CALLED}
{if !$EMBEDDED_REPORT} 
</BODY>
</HTML>
{/if}
{/if}
