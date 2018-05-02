{% if STATUSMSG|length>0 %} 
			<TABLE class="reportico-status-block">
				<TR>
					<TD>{{ STATUSMSG|raw }}</TD>
				</TR>
			</TABLE>
{% endif %}
