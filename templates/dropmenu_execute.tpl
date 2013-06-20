{if !$EMBEDDED_REPORT}
<HTML>
<HEAD>
<TITLE>{$TITLE}</TITLE>
{$OUTPUT_ENCODING}
</HEAD>
<LINK id="PRP_StyleSheet" REL="stylesheet" TYPE="text/css" HREF="{$STYLESHEET}">
{if $REPORT_PAGE_STYLE}
<BODY class="swRepBody" {$REPORT_PAGE_STYLE};">
{else}
<BODY class="swRepBody">
{/if}
<div class="swRepForm">
{if strlen($ERRORMSG)>0}
            <TABLE class="swError">
                <TR>
                    <TD>{$ERRORMSG}</TD>
                </TR>
            </TABLE>
{/if}
{if strlen($STATUSMSG)>0} 
			<TABLE class="swStatus">
				<TR>
					<TD>{$STATUSMSG}</TD>
				</TR>
			</TABLE>
{/if}
{if $SHOW_LOGIN}
			<TD width="10%"></TD>
			<TD width="55%" align="left" class="swPrpTopMenuCell">
{if strlen($PROJ_PASSWORD_ERROR) > 0}
                                <div style="color: #ff0000;">{$PASSWORD_ERROR}</div>
{/if}
				Enter the report project password. <br><input type="password" name="project_password" value=""></div>
				<input class="swLinkMenu" type="submit" name="login" value="Login">
			</TD>
{/if}
{/if}
{if $EMBEDDED_REPORT}
<LINK id="PRP_StyleSheet" REL="stylesheet" TYPE="text/css" HREF="{$STYLESHEET}">
{/if}
{$CONTENT}
{if !$EMBEDDED_REPORT}
</div>
</BODY>
</HTML>
{/if}
