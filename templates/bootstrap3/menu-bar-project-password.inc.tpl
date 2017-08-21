{if $SHOW_LOGIN}
{if strlen($PROJ_PASSWORD_ERROR) > 0}
                                <div style="color: #ff0000;">{$T_PASSWORD_ERROR}</div>
{/if}
            <li>
				<div style="inline-block; margin-top: 6px">{$T_ENTER_PROJECT_PASSWORD}<input type="password" name="project_password" value="">
				<input class="span2 swAdminButton" type="submit" name="login" value="{$T_LOGIN}">
                </div>
			</li>
{/if}
