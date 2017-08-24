{include file='bootstrap3/header.inc.tpl'}

<div id="reportico_container">

<FORM class="swPrpForm" id="criteriaform" name="topmenu" method="POST" action="{$SCRIPT_SELF}">
<input type="hidden" name="reportico_session_name" value="{$SESSION_ID}" />

<!-- Menu Bar -->
{include file='bootstrap3/prepare-menu-bar.inc.tpl'}

<!-- Output options -->
{include file='bootstrap3/prepare-design-options.inc.tpl'}

<!-- Report Title -->
{include file='bootstrap3/prepare-title.inc.tpl'}

<!-- Report Output options -->
<div class="swPrpCritBox" style="background-color: #ffffff" id="critbody">
{if $SHOW_OUTPUT && !$IS_ADMIN_SCREEN}
{include file='bootstrap3/prepare-output-table-form.inc.tpl'}
{include file='bootstrap3/prepare-output-formats.inc.tpl'}
{include file='bootstrap3/prepare-output-show-hide-options.inc.tpl'}
{/if}
</div>

<!-- Criteria Items and Expand Box -->
{if $SHOW_CRITERIA}
<div id="criteriabody">
  <div class="swPrpCritBox" style="display: table">
    <div id="swPrpCriteriaBody" style="display: table-row">
      <div class="swPrpCritEntry" style="float:left;">
         {include file='bootstrap3/prepare-criteria-items-header.inc.tpl'}
         {include file='bootstrap3/prepare-criteria-items.inc.tpl'}
         {include file='bootstrap3/prepare-criteria-items-trailer.inc.tpl'}
      </div>
      <div class="swPrpExpand" style="float:left">
        <div class="swPrpExpandBox">
          <div class="swPrpExpandRow">
            <div id="swPrpExpandCell" valign="top">
               {include file='bootstrap3/prepare-expand-contents.inc.tpl'}
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
{/if}

</FORM>
{include file='bootstrap3/prepare-modals.inc.tpl'}
{include file='bootstrap3/reportico-banner.inc.tpl'}
</div>
{include file='bootstrap3/footer.inc.tpl'}

