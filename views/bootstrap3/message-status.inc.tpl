{% if STATUSMSG|length>0 %} 
			<TABLE class="swStatus">
				<TR>
					<TD>{{ STATUSMSG|raw }}</TD>
				</TR>
			</TABLE>
{% endif %}
