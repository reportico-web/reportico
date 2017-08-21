<FORM class="swMiniMntForm" name="topmenu" method="POST" action="{$SCRIPT_SELF}">
	{if ($PARTIALMAINTAIN)} 
	    <input type="hidden" name="partialMaintain" value="{$PARTIALMAINTAIN}" />
	{/if}
	{if strlen($STATUSMSG)>0} 
		<div class="alert alert-info" role="alert">
            {$STATUSMSG}
        </div>
	{/if}
	{if strlen($ERRORMSG)>0} 
		<div class="alert alert-danger" role="alert">
            {$ERRORMSG}
        </div>
	{/if}
	<input type="hidden" name="reportico_session_name" value="{$SESSION_ID}" />
				{$CONTENT}
</FORM>
