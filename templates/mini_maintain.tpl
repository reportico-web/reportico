<FORM class="swMiniMntForm" name="topmenu" method="POST" action="{$SCRIPT_SELF}">
{if ($PARTIALMAINTAIN)} 
    <input type="hidden" name="partialMaintain" value="{$PARTIALMAINTAIN}" />
{/if}
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
	<TABLE class="swMntMainBox" cellspacing="0" cellpadding="0">
		<TR>
			<TD>
{$CONTENT}
			</TD>
		</TR>
	</TABLE>
</FORM>
