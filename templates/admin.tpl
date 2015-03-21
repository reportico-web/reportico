{if !$REPORTICO_AJAX_CALLED}
{if !$EMBEDDED_REPORT}
<!DOCTYPE html>
<HTML>
<HEAD>
<TITLE>{$T_ADMINTITLE}</TITLE>
<LINK id="reportico_css" REL="stylesheet" TYPE="text/css" HREF="{$STYLESHEET}">
{if $BOOTSTRAP_STYLES}
{if $BOOTSTRAP_STYLES == "2"}
<LINK id="bootstrap_css" REL="stylesheet" TYPE="text/css" HREF="{$STYLESHEETDIR}/bootstrap2/bootstrap.min.css">
{else}
<LINK id="bootstrap_css" REL="stylesheet" TYPE="text/css" HREF="{$STYLESHEETDIR}/bootstrap3/bootstrap.min.css">
{/if}
{/if}
{$OUTPUT_ENCODING}
</HEAD>
<BODY class="swMenuBody">
{else}
{if $BOOTSTRAP_STYLES}
{if !$REPORTICO_BOOTSTRAP_PRELOADED}
{if $BOOTSTRAP_STYLES == "2"}
<LINK id="bootstrap_css" REL="stylesheet" TYPE="text/css" HREF="{$STYLESHEETDIR}/bootstrap2/bootstrap.min.css">
{else}
<LINK id="bootstrap_css" REL="stylesheet" TYPE="text/css" HREF="{$STYLESHEETDIR}/bootstrap3/bootstrap.min.css">
{/if}
{/if}
{/if}
<LINK id="reportico_css" REL="stylesheet" TYPE="text/css" HREF="{$STYLESHEET}">
{/if}
{if $AJAX_ENABLED}
{if !$REPORTICO_AJAX_PRELOADED}
{if !$REPORTICO_JQUERY_PRELOADED}
{literal}
<script type="text/javascript" src="{/literal}{$JSPATH}{literal}/jquery.js"></script>
{/literal}
{/if}
{literal}
<script type="text/javascript" src="{/literal}{$JSPATH}{literal}/ui/jquery-ui.js"></script>
{/literal}
{literal}
<script type="text/javascript" src="{/literal}{$JSPATH}{literal}/reportico.js"></script>
{/literal}
{/if}
{if $BOOTSTRAP_STYLES}
{if !$REPORTICO_BOOTSTRAP_PRELOADED}
{if $BOOTSTRAP_STYLES == "2"}
<script type="text/javascript" src="{$JSPATH}/bootstrap2/bootstrap.min.js"></script>
{else}
<script type="text/javascript" src="{$JSPATH}/bootstrap3/bootstrap.min.js"></script>
{/if}
{/if}
{/if}
{/if}
{if !$REPORTICO_AJAX_PRELOADED}
{literal}
<script type="text/javascript" src="{/literal}{$JSPATH}{literal}/ui/i18n/jquery.ui.datepicker-{/literal}{$AJAX_DATEPICKER_LANGUAGE}{literal}.js"></script>
{/literal}
{/if}
{if !$BOOTSTRAP_STYLES}
{literal}
<script type="text/javascript" src="{/literal}{$JSPATH}{literal}/jquery.jdMenu.js"></script>
<LINK id="reportico_css" REL="stylesheet" TYPE="text/css" HREF="{/literal}{$JSPATH}{literal}/jquery.jdMenu.css">
{/literal}
{/if}
{literal}
<LINK id="reportico_css" REL="stylesheet" TYPE="text/css" HREF="{/literal}{$JSPATH}{literal}/ui/jquery-ui.css">
<script type="text/javascript">var reportico_datepicker_language = "{/literal}{$AJAX_DATEPICKER_FORMAT}{literal}";</script>
<script type="text/javascript">var reportico_this_script = "{/literal}{$SCRIPT_SELF}{literal}";</script>
<script type="text/javascript">var reportico_ajax_script = "{/literal}{$REPORTICO_AJAX_RUNNER}{literal}";</script>
{/literal}
{if $REPORTICO_BOOTSTRAP_MODAL}
<script type="text/javascript">var reportico_bootstrap_modal = true;</script>
{else}
<script type="text/javascript">var reportico_bootstrap_modal = false;</script>
{/if}
{literal}
<script type="text/javascript">var reportico_ajax_mode = "{/literal}{$REPORTICO_AJAX_MODE}{literal}";</script>
{/literal}
{if $REPORTICO_DYNAMIC_GRIDS}
<script type="text/javascript">var reportico_dynamic_grids = true;</script>
{if $REPORTICO_DYNAMIC_GRIDS_SORTABLE}
<script type="text/javascript">var reportico_dynamic_grids_sortable = true;</script>
{else}
<script type="text/javascript">var reportico_dynamic_grids_sortable = false;</script>
{/if}
{if $REPORTICO_DYNAMIC_GRIDS_SEARCHABLE}
<script type="text/javascript">var reportico_dynamic_grids_searchable = true;</script>
{else}
<script type="text/javascript">var reportico_dynamic_grids_searchable = false;</script>
{/if}
{if $REPORTICO_DYNAMIC_GRIDS_PAGING}
<script type="text/javascript">var reportico_dynamic_grids_paging = true;</script>
{else}
<script type="text/javascript">var reportico_dynamic_grids_paging = false;</script>
{/if}
<script type="text/javascript">var reportico_dynamic_grids_page_size = {$REPORTICO_DYNAMIC_GRIDS_PAGE_SIZE};</script>
{else}
<script type="text/javascript">var reportico_dynamic_grids = false;</script>
{/if}
{/if}
{if !$REPORTICO_AJAX_PRELOADED}
{literal}
<script type="text/javascript" src="{/literal}{$JSPATH}{literal}/jquery.dataTables.min.js"></script>
{/literal}
<LINK id="PRP_StyleSheet" REL="stylesheet" TYPE="text/css" HREF="{$STYLESHEETDIR}/jquery.dataTables.css">
{/if}
{if $REPORTICO_CHARTING_ENGINE == "NVD3" }
{if !$REPORTICO_AJAX_PRELOADED}
{literal}
<script type="text/javascript" src="{/literal}{$JSPATH}{literal}/nvd3/d3.min.js"></script>
<script type="text/javascript" src="{/literal}{$JSPATH}{literal}/nvd3/nv.d3.js"></script>
<LINK id="bootstrap_css" REL="stylesheet" TYPE="text/css" HREF="{/literal}{$JSPATH}{literal}/nvd3/nv.d3.css">
{/literal}
{/if}
{/if}
<div id="reportico_container">

<FORM class="swMenuForm" name="topmenu" method="POST" action="{$SCRIPT_SELF}">
<div style="height: 78px" class="swAdminBanner">
<div style="float: right;">
<img height="78px" src="{$REPORTICO_URL_DIR}/images/reportico100.png"/>
<div class="smallbanner">Version <a href="http://www.reportico.org/" target="_blank">{$REPORTICO_VERSION}</a></div>
</div>
<div style="height: 78px">
<H1 class="swTitle" style="text-align: center; padding-top: 30px; padding-left: 200px;">{$T_ADMINTITLE}</H1>
</div>
</div>
<input type="hidden" name="reportico_session_name" value="{$SESSION_ID}" /> 
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
				<input class="{$BOOTSTRAP_STYLE_ADMIN_BUTTON}swPrpSubmit reporticoSubmit" type="submit" name="adminlogout" value="{$T_LOGOFF}">
			</TD>
{/if}
{if $SHOW_LOGIN}
			<TD width="50%"></TD>
			<TD width="35%" align="right" class="swPrpTopMenuCell">
{$T_ADMIN_INSTRUCTIONS}
				<br><input class="{$BOOTSTRAP_STYLE_TEXTFIELD}" type="password" name="admin_password" value="">
				<input class="{$BOOTSTRAP_STYLE_ADMIN_BUTTON}swPrpSubmit reporticoSubmit" type="submit" name="login" value="{$T_LOGIN}">
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
				<select class="{$BOOTSTRAP_STYLE_DROPDOWN}swPrpDropSelectRegular" name="jump_to_language">
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
				<input class="{$BOOTSTRAP_STYLE_ADMIN_BUTTON}swPrpSubmit reporticoSubmit" type="submit" name="submit_admin_password" value="{$T_SET_ADMIN_PASSWORD}">
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
				<select class="{$BOOTSTRAP_STYLE_DROPDOWN}swPrpDropSelectRegular" name="jump_to_language">
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
				<input class="{$BOOTSTRAP_STYLE_ADMIN_BUTTON}swMntButton reporticoSubmit" type="submit" name="submit_language" value="{$T_GO}">
			</TD>
		</TR>
{/if}
{if count($PROJECT_ITEMS) > 0 }
		<TR> 
			<TD class="swMenuItem" style="width: 30%">{$T_RUN_SUITE}
				<select class="{$BOOTSTRAP_STYLE_DROPDOWN}swPrpDropSelectRegular" name="jump_to_menu_project">
{section name=menuitem loop=$PROJECT_ITEMS}
{strip}
				<OPTION label="{$PROJECT_ITEMS[menuitem].label}" value="{$PROJECT_ITEMS[menuitem].label}">{$PROJECT_ITEMS[menuitem].label}</OPTION>
{/strip}
{/section}
				</select>
				<input class="{$BOOTSTRAP_STYLE_ADMIN_BUTTON}swMntButton reporticoSubmit" type="submit" name="submit_menu_project" value="{$T_GO}">
			</TD>
		</TR>
{/if}
{/if}
{if count($PROJECT_ITEMS) > 0 }
		<TR> 
			<TD class="swMenuItem" style="width: 30%">{$T_CREATE_REPORT}
				<select class="{$BOOTSTRAP_STYLE_DROPDOWN}swPrpDropSelectRegular" name="jump_to_design_project">
{section name=menuitem loop=$PROJECT_ITEMS}
{strip}
				<OPTION label="{$PROJECT_ITEMS[menuitem].label}" value="{$PROJECT_ITEMS[menuitem].label}">{$PROJECT_ITEMS[menuitem].label}</OPTION>
{/strip}
{/section}
				</select>
				<input class="{$BOOTSTRAP_STYLE_ADMIN_BUTTON}swMntButton reporticoSubmit" type="submit" name="submit_design_project" value="{$T_GO}">
			</TD>
		</TR>
		<TR> 
			<TD class="swMenuItem" style="width: 30%">{$T_CONFIG_PARAM}
				<select class="{$BOOTSTRAP_STYLE_DROPDOWN}swPrpDropSelectRegular" name="jump_to_configure_project">
{section name=menuitem loop=$PROJECT_ITEMS}
{strip}
				<OPTION label="{$PROJECT_ITEMS[menuitem].label}" value="{$PROJECT_ITEMS[menuitem].label}">{$PROJECT_ITEMS[menuitem].label}</OPTION>
{/strip}
{/section}
				</select>
				<input class="{$BOOTSTRAP_STYLE_ADMIN_BUTTON}swMntButton reporticoSubmit" type="submit" name="submit_configure_project" value="{$T_GO}">
			</TD>
		</TR>
		<TR> 
			<TD class="swMenuItem" style="width: 30%">{$T_DELETE_PROJECT}
				<select class="{$BOOTSTRAP_STYLE_DROPDOWN}swPrpDropSelectRegular" name="jump_to_delete_project">
{section name=menuitem loop=$PROJECT_ITEMS}
{strip}
				<OPTION label="{$PROJECT_ITEMS[menuitem].label}" value="{$PROJECT_ITEMS[menuitem].label}">{$PROJECT_ITEMS[menuitem].label}</OPTION>
{/strip}
{/section}
				</select>
				<input class="{$BOOTSTRAP_STYLE_ADMIN_BUTTON}swMntButton reporticoSubmit" type="submit" name="submit_delete_project" value="{$T_GO}">
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
			<TD class="swMenuItem" style="width: 30%"><a target="_blank" href="{$REPORTICO_SITE}documentation/{$REPORTICO_VERSION}">{$T_DOCUMENTATION}</a>
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
				<select class="{$BOOTSTRAP_STYLE_DROPDOWN}swPrpDropSelectRegular" name="jump_to_language">
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
				<input class="{$BOOTSTRAP_STYLE_ADMIN_BUTTON}swMntButton reporticoSubmit" type="submit" name="submit_language" value="{$T_GO}">
			</TD>
		</TR>
{/if}
{if count($PROJECT_ITEMS) > 0 }
		<TR> 
			<TD class="swMenuItem" style="width: 30%">{$T_RUN_SUITE}
				<select class="{$BOOTSTRAP_STYLE_DROPDOWN}swPrpDropSelectRegular" name="jump_to_menu_project">
{section name=menuitem loop=$PROJECT_ITEMS}
{strip}
				<OPTION label="{$PROJECT_ITEMS[menuitem].label}" value="{$PROJECT_ITEMS[menuitem].label}">{$PROJECT_ITEMS[menuitem].label}</OPTION>
{/strip}
{/section}
				</select>
				<input class="{$BOOTSTRAP_STYLE_ADMIN_BUTTON}swMntButton reporticoSubmit" type="submit" name="submit_menu_project" value="{$T_GO}">
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



</div>
{if !$REPORTICO_AJAX_CALLED}
{if !$EMBEDDED_REPORT} 
</BODY>
</HTML>
{/if}
{/if}
