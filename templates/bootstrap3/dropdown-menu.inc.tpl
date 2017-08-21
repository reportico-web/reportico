                <ul class="nav navbar-nav">
{if $SHOW_HIDE_DROPDOWN_MENU == "show" && $DROPDOWN_MENU_ITEMS}
{section name=menu loop=$DROPDOWN_MENU_ITEMS}
            <li class="dropdown"><a class="dropdown-toggle" data-toggle="dropdown" href="#">{$DROPDOWN_MENU_ITEMS[menu].title}<span class="caret"></span></a>
              <ul class="dropdown-menu reportico-dropdown">
{section name=menuitem loop=$DROPDOWN_MENU_ITEMS[menu].items}
{if isset($DROPDOWN_MENU_ITEMS[menu].items[menuitem].reportname)}
<li ><a class="reportico-dropdown-item" href="{$RUN_REPORT_URL}&project={$DROPDOWN_MENU_ITEMS[menu].items[menuitem].project}&xmlin={$DROPDOWN_MENU_ITEMS[menu].items[menuitem].reportfile}">{$DROPDOWN_MENU_ITEMS[menu].items[menuitem].reportname}</a></li>
{/if}
{/section}

              </ul>
            </li>
{/section}
{/if}
            </ul>
