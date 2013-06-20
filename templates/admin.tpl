{if !$REPORTICO_AJAX_CALLED}
{if !$EMBEDDED_REPORT} 
<!DOCTYPE html>
<HTML>
<HEAD>
<TITLE>{$T_ADMINTITLE}</TITLE>
<LINK id="reportico_css" REL="stylesheet" TYPE="text/css" HREF="{$STYLESHEET}">
{$OUTPUT_ENCODING}
</HEAD>
<BODY class="swMenuBody">
<p>
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
<script type="text/javascript" src="{/literal}{$JSPATH}{literal}/ui/i18n/jquery.ui.datepicker-{/literal}{$AJAX_DATEPICKER_LANGUAGE}{literal}.js"></script>
<LINK id="reportico_css" REL="stylesheet" TYPE="text/css" HREF="{/literal}{$JSPATH}{literal}/jquery.jdMenu.css">
<script type="text/javascript" src="{/literal}{$JSPATH}{literal}/jquery.jdMenu.js"></script>
<script type="text/javascript">var reportico_datepicker_language = "{/literal}{$AJAX_DATEPICKER_FORMAT}{literal}";</script>
<script type="text/javascript">var reportico_this_script = "{/literal}{$SCRIPT_SELF}{literal}";</script>
<script type="text/javascript">var reportico_ajax_script = "{/literal}{$REPORTICO_AJAX_RUNNER}{literal}";</script>
<script type="text/javascript">var reportico_ajax_mode = "{/literal}{$REPORTICO_AJAX_MODE}{literal}";</script>
{/literal}
<LINK id="reportico_css" REL="stylesheet" TYPE="text/css" HREF="{$JSPATH}/ui/themes/base/jquery.ui.all.css">
{/if}
{/if}
<div id="reportico_container">
<FORM class="swMenuForm" name="topmenu" method="POST" action="{$SCRIPT_SELF}">
<div style="height: 78px" class="swAdminBanner">
<div style="float: right;">
<img height="78px" src="{$REPORTICO_URL_DIR}/images/reportico100.png"/>

</div>
<div style="height: 78px">
<H1 class="swTitle" style="text-align: center; padding-top: 30px; padding-left: 200px;">{$T_ADMINTITLE}</H1>
</div>
</div>
<input type="hidden" name="session_name" value="{$SESSION_ID}" /> 
{if $SHOW_TOPMENU}
	<TABLE class="swPrpTopMenu">
		<TR>
{if ($DB_LOGGEDON)}
{if strlen($DBUSER)>0} 
			<TD class="swPrpTopMenuCell">{$T_LOGGED_ON_AS} {$DBUSER}</TD>
{/if}
{if strlen($DBUSER)==0} 
			<TD style="width: 15%" class="swPrpTopMenuCell">&nbsp;</TD>
{/if}
{/if}
{if strlen($MAIN_MENU_URL)>0} 
			<TD style="text-align:center">&nbsp;</TD>
{/if}
{if $SHOW_LOGOUT}
			<TD width="15%" align="right" class="swPrpTopMenuCell">
				<input class="swPrpSubmit reporticoSubmit" type="submit" name="adminlogout" value="{$T_LOGOFF}">
			</TD>
{/if}
{if $SHOW_LOGIN}
			<TD width="50%"></TD>
			<TD width="35%" align="right" class="swPrpTopMenuCell">
{$T_ADMIN_INSTRUCTIONS}
				<br><input type="password" name="admin_password" value="">
				<input class="swPrpSubmit reporticoSubmit" type="submit" name="login" value="{$T_LOGIN}">
{if strlen($ADMIN_PASSWORD_ERROR) > 0}
				<div style="color: #ff0000;">{$T_ADMIN_PASSWORD_ERROR}</div>
{/if}
			</TD>
			<TD width="15%" align="right" class="swPrpTopMenuCell">
			</TD>
{/if}
		</TR>
	</TABLE>
{/if}
{if $SHOW_SET_ADMIN_PASSWORD}
<div style="text-align:center;">
{if strlen($SET_ADMIN_PASSWORD_ERROR) > 0}
				<div style="color: #ff0000;">{$SET_ADMIN_PASSWORD_ERROR}</div>
{/if}
				<br>
				<br>
{$T_SET_ADMIN_PASSWORD_INFO}
				<br>
{$T_SET_ADMIN_PASSWORD_NOT_SET}
				<br>
{$T_SET_ADMIN_PASSWORD_PROMPT}
				<br>
				<input type="password" name="new_admin_password" value=""><br>
				<br>
{$T_SET_ADMIN_PASSWORD_REENTER} <br><input type="password" name="new_admin_password2" value=""><br>
<br>
<br>
{if count($LANGUAGES) > 0 }
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
{/if}
<br>
				<br>
				<input class="swPrpSubmit reporticoSubmit" type="submit" name="submit_admin_password" value="{$T_SET_ADMIN_PASSWORD}">
				<br>
				
</div>
{/if}
{if $SHOW_REPORT_MENU}
	<TABLE class="swMenu">
		<TR> <TD>&nbsp;</TD> </TR>
{if !$SHOW_SET_ADMIN_PASSWORD}
{if count($LANGUAGES) > 0 }
		<TR> 
			<TD class="swMenuItem" style="width: 30%">{$T_CHOOSE_LANGUAGE}
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
			</TD>
		</TR>
{/if}
{if count($PROJECT_ITEMS) > 0 }
		<TR> 
			<TD class="swMenuItem" style="width: 30%">{$T_RUN_SUITE}
				<select class="swPrpDropSelectRegular" name="jump_to_menu_project">
{section name=menuitem loop=$PROJECT_ITEMS}
{strip}
				<OPTION label="{$PROJECT_ITEMS[menuitem].label}" value="{$PROJECT_ITEMS[menuitem].label}">{$PROJECT_ITEMS[menuitem].label}</OPTION>
{/strip}
{/section}
				</select>
				<input class="swMntButton reporticoSubmit" type="submit" name="submit_menu_project" value="{$T_GO}">
			</TD>
		</TR>
{/if}
{/if}
{if count($PROJECT_ITEMS) > 0 }
		<TR> 
			<TD class="swMenuItem" style="width: 30%">{$T_CREATE_REPORT}
				<select class="swPrpDropSelectRegular" name="jump_to_design_project">
{section name=menuitem loop=$PROJECT_ITEMS}
{strip}
				<OPTION label="{$PROJECT_ITEMS[menuitem].label}" value="{$PROJECT_ITEMS[menuitem].label}">{$PROJECT_ITEMS[menuitem].label}</OPTION>
{/strip}
{/section}
				</select>
				<input class="swMntButton reporticoSubmit" type="submit" name="submit_design_project" value="{$T_GO}">
			</TD>
		</TR>
		<TR> 
			<TD class="swMenuItem" style="width: 30%">{$T_CONFIG_PARAM}
				<select class="swPrpDropSelectRegular" name="jump_to_configure_project">
{section name=menuitem loop=$PROJECT_ITEMS}
{strip}
				<OPTION label="{$PROJECT_ITEMS[menuitem].label}" value="{$PROJECT_ITEMS[menuitem].label}">{$PROJECT_ITEMS[menuitem].label}</OPTION>
{/strip}
{/section}
				</select>
				<input class="swMntButton reporticoSubmit" type="submit" name="submit_configure_project" value="{$T_GO}">
			</TD>
		</TR>
		<TR> 
			<TD class="swMenuItem" style="width: 30%">{$T_DELETE_PROJECT}
				<select class="swPrpDropSelectRegular" name="jump_to_delete_project">
{section name=menuitem loop=$PROJECT_ITEMS}
{strip}
				<OPTION label="{$PROJECT_ITEMS[menuitem].label}" value="{$PROJECT_ITEMS[menuitem].label}">{$PROJECT_ITEMS[menuitem].label}</OPTION>
{/strip}
{/section}
				</select>
				<input class="swMntButton reporticoSubmit" type="submit" name="submit_delete_project" value="{$T_GO}">
			</TD>
		</TR>
{/if}
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
		<TR> 
			<TD class="swMenuItem" style="width: 30%"><a href="{$REPORTICO_URL_DIR}/doc/li_reportico.html">{$T_DOCUMENTATION}</a>
			</TD>
		</TR>
	</TABLE>
{else}
	<TABLE class="swMenu">
		<TR> <TD>&nbsp;</TD> </TR>
{if !$SHOW_SET_ADMIN_PASSWORD}
{if count($LANGUAGES) > 1 }
		<TR> 
			<TD class="swMenuItem" style="width: 30%">{$T_CHOOSE_LANGUAGE}
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
			</TD>
		</TR>
{/if}
{if count($PROJECT_ITEMS) > 0 }
		<TR> 
			<TD class="swMenuItem" style="width: 30%">{$T_RUN_SUITE}
				<select class="swPrpDropSelectRegular" name="jump_to_menu_project">
{section name=menuitem loop=$PROJECT_ITEMS}
{strip}
				<OPTION label="{$PROJECT_ITEMS[menuitem].label}" value="{$PROJECT_ITEMS[menuitem].label}">{$PROJECT_ITEMS[menuitem].label}</OPTION>
{/strip}
{/section}
				</select>
				<input class="swMntButton reporticoSubmit" type="submit" name="submit_menu_project" value="{$T_GO}">
			</TD>
		</TR>
{/if}
{/if}
		<TR> <TD>&nbsp;</TD> </TR>
	</TABLE>
{/if}

	<!--TABLE class="swStatus"><TR><TD>Select a Report From the List Above</TD></TR></TABLE-->
{if strlen($ERRORMSG)>0} 
			<TABLE class="swError">
				<TR>
					<TD>{$ERRORMSG}</TD>
				</TR>
			</TABLE>
{/if}
</FORM>
<div class="smallbanner">Powered by <a href="http://www.reportico.org/" target="_blank">reportico {$REPORTICO_VERSION}</a></div>
</div>
{if !$REPORTICO_AJAX_CALLED}
{if !$EMBEDDED_REPORT} 
</BODY>
</HTML>
{/if}
{/if}
