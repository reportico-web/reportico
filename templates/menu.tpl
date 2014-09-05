{if !$REPORTICO_AJAX_CALLED}
{if !$EMBEDDED_REPORT}
<!DOCTYPE html>
<HTML>
<HEAD>
<TITLE>{$TITLE}</TITLE>
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
<LINK id="reportico_css" REL="stylesheet" TYPE="text/css" HREF="{$STYLESHEET}">
{if $BOOTSTRAP_STYLES}
{if !$REPORTICO_BOOTSTRAP_PRELOADED}
{if $BOOTSTRAP_STYLES == "2"}
<LINK id="bootstrap_css" REL="stylesheet" TYPE="text/css" HREF="{$STYLESHEETDIR}/bootstrap2/bootstrap.min.css">
{else}
<LINK id="bootstrap_css" REL="stylesheet" TYPE="text/css" HREF="{$STYLESHEETDIR}/bootstrap3/bootstrap.min.css">
{/if}
{/if}
{/if}
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
<script type="text/javascript" src="{/literal}{$JSPATH}{literal}/jquery.dataTables.js"></script>
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
<input type="hidden" name="reportico_session_name" value="{$SESSION_ID}" /> 

{if true || $SHOW_REPORT_MENU}

{if $BOOTSTRAP_STYLES}
{if $BOOTSTRAP_STYLES == "2" || $BOOTSTRAP_STYLES == "3" }
<!-- BOOTSTRAP VERSION -->
{if $SHOW_HIDE_NAVIGATION_MENU == "show" || $SHOW_HIDE_DROPDOWN_MENU == "show"}
    <div class="navbar navbar-default" role="navigation">
{else}
    <div style="display:none" class="navbar navbar-default" role="navigation">
{/if}
    <!--div class="navbar navbar-default navbar-static-top" role="navigation"-->
{if $BOOTSTRAP_STYLES == "2" }
        <div class="navbar-inner">
{/if}
        <div class="container" style="width: 100%">
{if $BOOTSTRAP_STYLES == "2" }
            <button type="button" class="btn btn-navbar" data-toggle="collapse" data-target="#reportico-bootstrap-collapse"-->
{else}
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#reportico-bootstrap-collapse">
{/if}
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
{if $SHOW_HIDE_DROPDOWN_MENU == "show" && $DROPDOWN_MENU_ITEMS}
{if $BOOTSTRAP_STYLES == "2" }
            <a href="#" class="brand" style="float: left">{$MENU_TITLE} :</a>
{else}
            <a href="#" class="navbar-brand">{$MENU_TITLE} :</a>
{/if}
{/if}
{if $BOOTSTRAP_STYLES == "2" }
            <div class= "nav-collapse collapse" id="reportico-bootstrap-collapse">
{else}
            <div class= "nav-collapse collapse in" id="reportico-bootstrap-collapse">
{/if}
                <ul class="nav navbar-nav">
{if $SHOW_HIDE_DROPDOWN_MENU == "show" && $DROPDOWN_MENU_ITEMS}
{section name=menu loop=$DROPDOWN_MENU_ITEMS}
            <li class="dropdown"><a class="dropdown-toggle" data-toggle="dropdown" href="#">{$DROPDOWN_MENU_ITEMS[menu].title}<span class="caret"></span></a>
              <ul class="dropdown-menu reportico-dropdown">
{section name=menuitem loop=$DROPDOWN_MENU_ITEMS[menu].items}
{if isset($DROPDOWN_MENU_ITEMS[menu].items[menuitem].reportname)}
<li ><a class="reportico-dropdown-item" href="{$RUN_REPORT_URL}&project={$DROPDOWN_MENU_ITEMS[menu].project}&xmlin={$DROPDOWN_MENU_ITEMS[menu].items[menuitem].reportfile}">{$DROPDOWN_MENU_ITEMS[menu].items[menuitem].reportname}</a></li>
{/if}
{/section}

              </ul>
            </li>
{/section}
{/if}
            </ul>
{if $SHOW_HIDE_NAVIGATION_MENU == "show" }
            <ul class= "nav navbar-nav pull-right navbar-right">
{else}
            <ul style="display:none" class= "nav navbar-nav pull-right navbar-right">
{/if}
{if $SHOW_TOPMENU}
{if $SHOW_LOGOUT}
            <li> <input class="span {$BOOTSTRAP_STYLE_ADMIN_BUTTON}swAdminButton2" type="submit" name="logout" value="{$T_LOGOFF}"></li>
{/if}
{if $SHOW_LOGIN}
{if strlen($PROJ_PASSWORD_ERROR) > 0}
                                <div style="color: #ff0000;">{$T_PASSWORD_ERROR}</div>
{/if}
            <li>
				<div style="inline-block; margin-top: 6px">{$T_ENTER_PROJECT_PASSWORD}<input type="password" name="project_password" value="">
				<input class="span2 swAdminButton" type="submit" name="login" value="{$T_LOGIN}">
                </div>
			</li>
{/if}
{/if}
{if ($SHOW_ADMIN_BUTTON)}
{if strlen($ADMIN_MENU_URL)>0} 
              <li>
                    <a class="swAdminButton2" href="{$ADMIN_MENU_URL}">{$T_ADMIN_HOME}</a>
              </li>
{/if}
{/if}
{if ($SHOW_DESIGN_BUTTON)}
{if !$DEMO_MODE}
            <li style="float:right">
			    <a class="swLinkMenu2" href="{$CONFIGURE_PROJECT_URL}">{$T_CONFIG_PROJECT}</a>
            </li>
            <li style="float:right">
			    <a class="swLinkMenu2" href="{$CREATE_REPORT_URL}">{$T_CREATE_REPORT}</a>
            </li>
{/if}
{/if}
</div>
</ul>
        </div>
{if $BOOTSTRAP_STYLES == "2" }
        </div>
{/if}
</div>

<!-- BOOTSTRAP VERSION -->
{/if} 
{else}
{if $SHOW_HIDE_DROPDOWN_MENU == "show" && $DROPDOWN_MENU_ITEMS}
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
{/if}
<H1 class="swTitle">{$TITLE}</H1>
{if !$BOOTSTRAP_STYLES}
{if $SHOW_TOPMENU}
{if $SHOW_HIDE_NAVIGATION_MENU == "show"}
	<TABLE class="swPrpTopMenu">
{else}
	<TABLE style="display:none" class="swPrpTopMenu">
{/if}
		<TR>
                        <TD class="swPrpTopMenuCell" style="width: 50%">
{if ($SHOW_ADMIN_BUTTON)}
			<a class="{$BOOTSTRAP_STYLE_ADMIN_BUTTON}swLinkMenu" href="{$ADMIN_MENU_URL}">{$T_ADMIN_HOME}</a>
{/if}
{if ($SHOW_DESIGN_BUTTON)}
{if !$DEMO_MODE}
			<a class="{$BOOTSTRAP_STYLE_ADMIN_BUTTON}swLinkMenu" href="{$CONFIGURE_PROJECT_URL}">{$T_CONFIG_PROJECT}</a>
			<a class="{$BOOTSTRAP_STYLE_ADMIN_BUTTON}swLinkMenu" href="{$CREATE_REPORT_URL}">{$T_CREATE_REPORT}</a>
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
				<input class="{$BOOTSTRAP_STYLE_ADMIN_BUTTON}swLinkMenu reporticoSubmit" type="submit" name="login" value="{$T_LOGIN}"><br><br><br><br><br>
			</TD>
{/if}
			<TD style="text-align: center">
{if count($LANGUAGES) > 1 || ($SHOW_DESIGN_BUTTON)}
&nbsp; &nbsp; &nbsp; &nbsp;
                {$T_CHOOSE_LANGUAGE}<BR>
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
                <input class="swMntButton reporticoSubmit" type="submit" name="submit_language" value="{$T_GO}">
{/if}
			</TD>
{if $SHOW_LOGOUT}
			<TD width="15%" style="padding-left: 10px; text-align: right;" class="swPrpTopMenuCell">
				<input class="{$BOOTSTRAP_STYLE_ADMIN_BUTTON}swLinkMenu reporticoSubmit" type="submit" name="logout" value="{$T_LOGOFF}">
			</TD>
{/if}
		</TR>
	</TABLE>
{/if}
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
</div>
{if !$REPORTICO_AJAX_CALLED}
{if !$EMBEDDED_REPORT} 
</BODY>
</HTML>
{/if}
{/if}
