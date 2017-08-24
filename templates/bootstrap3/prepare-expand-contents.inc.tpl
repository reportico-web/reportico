
<!-- Error and status message -->
{include file='bootstrap3/message-error.inc.tpl'}
{include file='bootstrap3/message-status.inc.tpl'}

{if strlen($STATUSMSG)==0 && strlen($ERRORMSG)==0}
<p>
{if $SHOW_EXPANDED}
{include file='bootstrap3/prepare-criteria-expand-lookup.inc.tpl'}
{else}
{include file='bootstrap3/prepare-report-description.inc.tpl'}
{/if}
{/if}
