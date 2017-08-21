<!-- nav bar  drop down menu items and main menu options -->
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


<!-- right hand side option buttons in  nav bar -->
{if $SHOW_HIDE_NAVIGATION_MENU == "show" }
            <ul class= "nav navbar-nav pull-right navbar-right">
{else}
            <ul style="display:none" class= "nav navbar-nav pull-right navbar-right">
{/if}
{if $SHOW_TOPMENU}
{include file='bootstrap3mod/menu-bar-design.inc.tpl'}
{include file='bootstrap3mod/menu-bar-debug-level.inc.tpl'}
{include file='bootstrap3mod/menu-bar-project-password.inc.tpl'}
{/if}
{include file='bootstrap3mod/menu-bar-admin-menu.inc.tpl'}
{include file='bootstrap3mod/menu-bar-project-menu.inc.tpl'}
{include file='bootstrap3mod/menu-bar-logout.inc.tpl'}
</ul>
</div>
</ul>
<!-- close right hand side option buttons in  nav bar -->
        </div>
</div>
