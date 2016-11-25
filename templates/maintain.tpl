{if !$REPORTICO_AJAX_CALLED}
{if !$EMBEDDED_REPORT}
<!DOCTYPE html>
<HTML>
<HEAD>
<TITLE>{$TITLE}</TITLE>
<LINK id="reportico_css" REL="stylesheet" TYPE="text/css" HREF="{$STYLESHEET}">
{if $BOOTSTRAP_STYLES}
{if $BOOTSTRAP_STYLES == "2"}
<LINK id="bootstrap_css" REL="stylesheet" TYPE="text/css" HREF="{$JSPATH}/bootstrap2/css/bootstrap.min.css">
{else}
<LINK id="bootstrap_css" REL="stylesheet" TYPE="text/css" HREF="{$JSPATH}/bootstrap3/css/bootstrap.min.css">
{/if}
{/if}
{$OUTPUT_ENCODING}
</HEAD>
<BODY class="swMntBody">
{else}
<LINK id="reportico_css" REL="stylesheet" TYPE="text/css" HREF="{$STYLESHEET}">
{if $BOOTSTRAP_STYLES}
{if !$REPORTICO_BOOTSTRAP_PRELOADED}
{if $BOOTSTRAP_STYLES == "2"}
<LINK id="bootstrap_css" REL="stylesheet" TYPE="text/css" HREF="{$JSPATH}/bootstrap2/css/bootstrap.min.css">
{else}
<LINK id="bootstrap_css" REL="stylesheet" TYPE="text/css" HREF="{$JSPATH}/bootstrap3/css/bootstrap.min.css">
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
{if $REPORTICO_CSRF_TOKEN}
<script type="text/javascript">var reportico_csrf_token = "{$REPORTICO_CSRF_TOKEN}";</script>
{/if}
{/if}

{if $BOOTSTRAP_STYLES}
{if !$REPORTICO_BOOTSTRAP_PRELOADED}

{if $BOOTSTRAP_STYLES == "2"}
<script type="text/javascript" src="{$JSPATH}/bootstrap2/js/bootstrap.min.js"></script>
{else}
<script type="text/javascript" src="{$JSPATH}/bootstrap3/js/bootstrap.min.js"></script>
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
<script type="text/javascript">var reportico_bootstrap_styles = "{$BOOTSTRAP_STYLES}";</script>
<script type="text/javascript">var reportico_bootstrap_modal = true;</script>
{else}
<script type="text/javascript">var reportico_bootstrap_modal = false;</script>
<script type="text/javascript">var reportico_bootstrap_styles = false;</script>
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
<script type="text/javascript" src="{/literal}{$JSPATH}{literal}/select2/js/select2.min.js"></script>
<script type="text/javascript" src="{/literal}{$JSPATH}{literal}/jquery.dataTables.js"></script>
{/literal}
<LINK id="PRP_StyleSheet_s2" REL="stylesheet" TYPE="text/css" HREF="{$JSPATH}/select2/css/select2.min.css">
<LINK id="PRP_StyleSheet_dt" REL="stylesheet" TYPE="text/css" HREF="{$STYLESHEETDIR}/jquery.dataTables.css">
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
    <script>
        reportico_criteria_items = [];
{if isset($CRITERIA_ITEMS)}
{section name=critno loop=$CRITERIA_ITEMS}
        reportico_criteria_items.push("{$CRITERIA_ITEMS[critno].name}");
{/section}
{/if}
    </script>
<FORM class="swMntForm" name="topmenu" method="POST" action="{$SCRIPT_SELF}">
<H1 class="swTitle">{$TITLE}</H1>
{if strlen($STATUSMSG)>0} 
			<TABLE class="swStatus">
				<TR>
					<TD>{$STATUSMSG}</TD>
				</TR>
			</TABLE>
{/if}
{if strlen($ERRORMSG)>0} 
			<TABLE class="swError">
				<TR>
					<TD>{$ERRORMSG}</TD>
				</TR>
			</TABLE>
{/if}
<input type="hidden" name="reportico_session_name" value="{$SESSION_ID}" />
{if $SHOW_TOPMENU}
	<TABLE class="swMntTopMenu">
		<TR>
{if ($DB_LOGGEDON)} 
			<TD class="swPrpTopMenuCell">
{if ($DBUSER)}
Logged On As {$DBUSER}
{else}
&nbsp;
{/if}
			</TD>
{/if}
{if strlen($MAIN_MENU_URL)>0} 
			<TD style="text-align: left;">
				<a class="{$BOOTSTRAP_STYLE_ADMIN_BUTTON}swLinkMenu" href="{$MAIN_MENU_URL}">{$T_PROJECT_MENU}</a>
{if ($SHOW_ADMIN_BUTTON)}
				&nbsp;<a class="{$BOOTSTRAP_STYLE_ADMIN_BUTTON}swLinkMenu" href="{$ADMIN_MENU_URL}">{$T_ADMIN_MENU}</a>
{/if}
				&nbsp;<a class="{$BOOTSTRAP_STYLE_ADMIN_BUTTON}swLinkMenu" href="{$RUN_REPORT_URL}">{$T_RUN_REPORT}</a>
				&nbsp;<input class="{$BOOTSTRAP_STYLE_ADMIN_BUTTON}swLinkMenu" type="submit" name="submit_prepare_mode" style="display:none" onclick="return(false);" value="Do nothing on enter">
                <input class="swMntButton reporticoSubmit swNoSubmit" style="width: 0px; color: transparent; background-color: transparent; border-color: transparent; cursor: default;" type="submit" name="submit_dummy_SET" value="Ok">
			</TD>
{/if}
{if $SHOW_MODE_MAINTAIN_BOX && 0}
			<TD style="text-align: left;">
				<input class="swMntButton" type="submit" name="submit_genws_mode" value="{$T_GEN_WEB_SERVICE}">
			</TD>
			<TD style="text-align: right;">
			</TD>
{/if}
{if $SHOW_LOGOUT}
			<TD style="width:15%; text-align: right; padding-right: 10px;" align="right" class="swPrpTopMenuCell">
				<input class="{$BOOTSTRAP_STYLE_ADMIN_BUTTON}swLinkMenu" type="submit" name="logout" value="{$T_LOGOFF}">
			</TD>
{/if}
{if $SHOW_LOGIN}
			<TD style="width: 50%"></TD>
			<TD style="width: 35%" align="right" class="swPrpTopMenuCell">
{if strlen($PROJ_PASSWORD_ERROR) > 0}
                                <div style="color: #ff0000;">{$PASSWORD_ERROR}</div>
{/if}
				{$T_DESIGN_PASSWORD_PROMPT} <input type="password" name="project_password" value="">
			</TD>
			<TD style="width: 15%" align="right" class="swPrpTopMenuCell">
				<input class="btn btn-sm btn-default swPrpSubmit" type="submit" name="login" value="{$T_LOGIN}">
			</TD>
{/if}
		</TR>
	</TABLE>
{/if}
	<TABLE class="swMntMainBox" cellspacing="0" cellpadding="0">
		<TR>
			<TD>
{$CONTENT}
			</TD>
		</TR>
	</TABLE>
</FORM>
<div class="smallbanner">Powered by <a href="http://www.reportico.org/" target="_blank">reportico {$REPORTICO_VERSION}</a></div>
</div>
{if !$REPORTICO_AJAX_CALLED}
{if !$EMBEDDED_REPORT}
</BODY>
</HTML>
{/if}
{/if}
