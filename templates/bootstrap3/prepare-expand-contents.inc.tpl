
<!-- Error and status message -->
{include file='bootstrap3mod/message-error.inc.tpl'}
{include file='bootstrap3mod/message-status.inc.tpl'}

{if strlen($STATUSMSG)==0 && strlen($ERRORMSG)==0}
<p>
{if $SHOW_EXPANDED}
{include file='bootstrap3mod/prepare-criteria-expand-lookup.inc.tpl'}
{else}
{include file='bootstrap3mod/prepare-report-description.inc.tpl'}
{/if}
{/if}
