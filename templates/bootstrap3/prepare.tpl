{include file='bootstrap3mod/header.inc.tpl'}

<div id="reportico_container">

<FORM class="swPrpForm" id="criteriaform" name="topmenu" method="POST" action="{$SCRIPT_SELF}">
<input type="hidden" name="reportico_session_name" value="{$SESSION_ID}" />

<!-- Menu Bar -->
{include file='bootstrap3mod/prepare-menu-bar.inc.tpl'}

<!-- Output options -->
{include file='bootstrap3mod/prepare-design-options.inc.tpl'}

<!-- Report Title -->
{include file='bootstrap3mod/prepare-title.inc.tpl'}

<!-- Report Output options -->
<div class="swPrpCritBox" style="background-color: #ffffff" id="critbody">
{if $SHOW_OUTPUT && !$IS_ADMIN_SCREEN}
{include file='bootstrap3mod/prepare-output-table-form.inc.tpl'}
{include file='bootstrap3mod/prepare-output-formats.inc.tpl'}
{include file='bootstrap3mod/prepare-output-show-hide-options.inc.tpl'}
{/if}
</div>

<!-- Criteria Items and Expand Box -->
{if $SHOW_CRITERIA}
<div id="criteriabody">
  <div class="swPrpCritBox" style="display: table">
    <div id="swPrpCriteriaBody" style="display: table-row">
      <div class="swPrpCritEntry" style="float:left;">
         {include file='bootstrap3mod/prepare-criteria-items-header.inc.tpl'}
         {include file='bootstrap3mod/prepare-criteria-items.inc.tpl'}
         {include file='bootstrap3mod/prepare-criteria-items-trailer.inc.tpl'}
      </div>
      <div class="swPrpExpand" style="float:left">
        <div class="swPrpExpandBox">
          <div class="swPrpExpandRow">
            <div id="swPrpExpandCell" valign="top">
               {include file='bootstrap3mod/prepare-expand-contents.inc.tpl'}
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
{/if}

</FORM>
{include file='bootstrap3mod/prepare-modals.inc.tpl'}
{include file='bootstrap3mod/reportico-banner.inc.tpl'}
</div>
{include file='bootstrap3mod/footer.inc.tpl'}

