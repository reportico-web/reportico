{include file='bootstrap3mod/header.inc.tpl'}
<div id="reportico_container">
<FORM class="swMenuForm" name="topmenu" method="POST" action="{$SCRIPT_SELF}">
<input type="hidden" name="reportico_session_name" value="{$SESSION_ID}" /> 

<!-- BOOTSTRAP VERSION -->
{if $SHOW_HIDE_NAVIGATION_MENU == "show" || $SHOW_HIDE_DROPDOWN_MENU == "show"}
    <div class="navbar navbar-default" role="navigation">
{else}
    <div style="display:none" class="navbar navbar-default" role="navigation">
{/if}
    <!--div class="navbar navbar-default navbar-static-top" role="navigation"-->
        <div class="container" style="width: 100%">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#reportico-bootstrap-collapse">
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
{include file='bootstrap3mod/dropdown-brand.inc.tpl'}
            <div class= "nav-collapse collapse in" id="reportico-bootstrap-collapse">
{include file='bootstrap3mod/dropdown-menu.inc.tpl'}
{if $SHOW_HIDE_NAVIGATION_MENU == "show" }
            <ul class= "nav navbar-nav pull-right navbar-right">
{else}
            <ul style="display:none" class= "nav navbar-nav pull-right navbar-right">
{/if}
{if $SHOW_TOPMENU}
{include file='bootstrap3mod/menu-bar-logout.inc.tpl'}
{include file='bootstrap3mod/menu-bar-project-password.inc.tpl'}
{/if}
{include file='bootstrap3mod/menu-bar-admin-menu.inc.tpl'}
{include file='bootstrap3mod/menu-bar-configure-create-buttons.inc.tpl'}
</div>
</ul>
        </div>
</div>
<!-- BOOTSTRAP VERSION -->
<H1 class="swTitle">{$TITLE}</H1>
	<TABLE class="swMenu">
		<TR> <TD>&nbsp;</TD> </TR>
{section name=menuitem loop=$MENU_ITEMS}
{strip}
		<TR> 
			<TD class="swMenuItem">
{if $MENU_ITEMS[menuitem].label == "TEXT"}
				{$MENU_ITEMS[menuitem].url}
{else}
{if $MENU_ITEMS[menuitem].label == "BLANKLINE"}
				&nbsp;
{else}
{if $MENU_ITEMS[menuitem].label == "LINE"}
				<hr>
{else}
				<a class="swMenuItemLink" href="{$MENU_ITEMS[menuitem].url}">{$MENU_ITEMS[menuitem].label}</a>
{/if}
{/if}
{/if}
			</TD>
		</TR>
{/strip}
{/section}
		
		<TR> <TD>&nbsp;</TD> </TR>
	</TABLE>

{if strlen($ERRORMSG)>0} 
			<TABLE class="swError">
				<TR>
					<TD>{$ERRORMSG}</TD>
				</TR>
			</TABLE>
{/if}
</FORM>
{include file='bootstrap3mod/reportico-banner.inc.tpl'}
</div>
{include file='bootstrap3mod/footer.inc.tpl'}
