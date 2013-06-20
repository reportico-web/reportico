{if !$EMBEDDED_REPORT} 
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
<script type="text/javascript" src="{$JSPATH}/jquery.js"></script>
<script type="text/javascript" src="{$JSPATH}/ui/jquery.ui.core.js"></script>
<script type="text/javascript" src="{$JSPATH}/ui/jquery.ui.datepicker.js"></script>
<LINK id="reportico_css" REL="stylesheet" TYPE="text/css" HREF="{$JSPATH}/ui/themes/base/jquery.ui.all.css">
<LINK id="reportico_css" REL="stylesheet" TYPE="text/css" HREF="{$JSPATH}/jquery.jdMenu.css">
{/if}

<FORM class="swMenuForm" name="topmenu" method="POST" action="{$SCRIPT_SELF}">
<H1 class="swTitle">{$TITLE}</H1>

<input type="hidden" name="session_name" value="{$SESSION_ID}" /> 
{if $SHOW_TOPMENU}
	<TABLE class="swPrpTopMenu">
		<TR>
                        <TD class="swPrpTopMenuCell">
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
			<TD style="width: 15%" class="swPrpTopMenuCell">&nbsp;</TD>
{/if}
{if strlen($MAIN_MENU_URL)>0} 
			<TD style="text-align:center">&nbsp;</TD>
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
<input type="password" name="project_password" value=""></div>
				<input class="swLinkMenu" type="submit" name="login" value="{$T_LOGIN}"><br><br><br><br><br>
			</TD>
{/if}
			<TD style="text-align: right">
{if count($LANGUAGES) > 1 || ($SHOW_DESIGN_BUTTON)}
&nbsp; &nbsp; &nbsp; &nbsp;
                {$T_CHOOSE_LANGUAGE}
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
                <input class="swMntButton" type="submit" name="submit_language" value="{$T_GO}">
{/if}
			</TD>
{if $SHOW_LOGOUT}
			<TD width="15%" style="padding-left: 10px; text-align: right;" class="swPrpTopMenuCell">
				<input class="swLinkMenu" type="submit" name="logout" value="{$T_LOGOFF}">
			</TD>
{/if}
		</TR>
	</TABLE>
{/if}

{if $SHOW_REPORT_MENU}
{if $DROPDOWN_MENU_ITEMS} 

<ul id="dropmenu" class="jd_menu" style="clear: none;float: left;width: 100%; z-index: 400">
{section name=menu loop=$DROPDOWN_MENU_ITEMS}
<li>
<a href="{$MAIN_MENU_URL}&project={$DROPDOWN_MENU_ITEMS[menu].project}">{$DROPDOWN_MENU_ITEMS[menu].title}</a>
<ul>
{section name=menuitem loop=$DROPDOWN_MENU_ITEMS[menu].items}
<li><a href="{$RUN_REPORT_URL}&project={$DROPDOWN_MENU_ITEMS[menu].project}&xmlin={$DROPDOWN_MENU_ITEMS[menu].items[menuitem].reportfile}">{$DROPDOWN_MENU_ITEMS[menu].items[menuitem].reportname}</a></li>
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
<div class="smallbanner">Powered by <a href="http://www.reportico.org/" target="_blank">reportico {$REPORTICO_VERSION}</a></div>
{if !$EMBEDDED_REPORT} 
</BODY>
</HTML>
{/if}




{if $AJAX_ENABLED} 
{literal}
<script type="text/javascript" src="{/literal}{$JSPATH}{literal}/ui/i18n/jquery.ui.datepicker-{/literal}{$AJAX_DATEPICKER_LANGUAGE}{literal}.js"></script>
<script type="text/javascript" src="{/literal}{$JSPATH}{literal}/jquery.jdMenu.js"></script>
<script>
jQuery(function($) { 
            $('ul.jd_menu').jdMenu();
            $(document).bind('click', function() {
                $('ul.jd_menu ul:visible').jdMenuHide();
            });
</script>
{/literal}
{/if}


