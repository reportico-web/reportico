{% if SHOW_MINIMAINTAIN %} 
{% if not REPORTICO_BOOTSTRAP_MODAL %}
    <button type="submit" class="btn btn-default" title="{{ T_EDIT }} {{ T_EDITSQL }}" id="submit_mainquerqury_SHOW" name="mainquerqurysqlt_QuerySql">
        <span class="glyphicon glyphicon-pencil"></span>{{ T_EDITSQL }}
    </button>
    <button type="submit" class="btn btn-default" title="{{ T_EDIT }} {{ T_EDITCOLUMNS }}" id="submit_mainquerquryqcol_SHOW" name="mainquerquryqcol_ANY">
        <span class="glyphicon glyphicon-pencil"></span>{{ T_EDITCOLUMNS }}
    </button>
    <button type="submit" class="btn btn-default" title="{{ T_EDIT }} {{ T_EDITASSIGNMENT }}" id="submit_mainquerassg" name="mainquerassg_ANY">
        <span class="glyphicon glyphicon-pencil"></span>{{ T_EDITASSIGNMENT }}
    </button>
    <button type="submit" class="btn btn-default" title="{{ T_EDIT }} {{ T_EDITGROUPS }}" id="submit_mainqueroutpgrps" name="mainqueroutpgrps_ANY">
        <span class="glyphicon glyphicon-pencil"></span>{{ T_EDITGROUPS }}
    </button>
    <button type="submit" class="btn btn-default" title="{{ T_EDIT }} {{ T_EDITGRAPHS }}" id="submit_mainqueroutpgrph" name="mainqueroutpgrph_ANY">
        <span class="glyphicon glyphicon-pencil"></span>{{ T_EDITGRAPHS }}
    </button>
{% else %}


<div class="navbar navbar-default" role="navigation">
    <!--div class="navbar navbar-default navbar-static-top" role="navigation"-->
        <div class="container" style="width: 100%">
            <div class="nav-collapse collapse in" id="reportico-bootstrap-collapse">
                <ul class="nav navbar-nav pull-right navbar-right">
    				    <li style="margin-right: 40px">
{{ T_REPORT_FILE }} <input type="text" name="xmlout" id="swPrpSaveFile" value="{{ XMLFILE }}"> <input type="submit" class="{{ BOOTSTRAP_STYLE_PRIMARY_BUTTON }} swPrpSaveButton" type="submit" name="submit_xxx_SAVE" value="{{ T_SAVE }}">
                        </li>
    				   <div class="btn-group" role="group">
    				       <button type="submit" class="btn btn-default prepareMiniMaintain swMiniMaintain" title="{{ T_EDIT }} {{ T_EDITSQL }}" id="submit_mainquerqury_SHOW" name="mainquerqurysqlt_QuerySql">
                                <span class="glyphicon glyphicon-pencil"></span>{{ T_EDITSQL }}
                            </button>
                            <button type="submit" class="btn btn-default prepareMiniMaintain swMiniMaintain" title="{{ T_EDIT }} {{ T_EDITCOLUMNS }}" id="submit_mainquerquryqcol_SHOW" name="mainquerquryqcol_ANY">
                                <span class="glyphicon glyphicon-pencil"></span>{{ T_EDITCOLUMNS }}
                            </button>
                            <button type="submit" class="btn btn-default prepareMiniMaintain swMiniMaintain" title="{{ T_EDIT }} {{ T_EDITASSIGNMENT }}" id="submit_mainquerassg" name="mainquerassg_ANY">
                                <span class="glyphicon glyphicon-pencil"></span>{{ T_EDITASSIGNMENT }}
                            </button>
                            <button type="submit" class="btn btn-default prepareMiniMaintain swMiniMaintain" title="{{ T_EDIT }} {{ T_EDITGROUPS }}" id="submit_mainqueroutpgrps" name="mainqueroutpgrps_ANY">
                                <span class="glyphicon glyphicon-pencil"></span>{{ T_EDITGROUPS }}
                            </button>
                            <button type="submit" class="btn btn-default prepareMiniMaintain swMiniMaintain" title="{{ T_EDIT }} {{ T_EDITGRAPHS }}" id="submit_mainqueroutpgrph" name="mainqueroutpgrph_ANY">
                                <span class="glyphicon glyphicon-pencil"></span>{{ T_EDITGRAPHS }}
                            </button>
                            <div class="btn-group" role="group" style="margin-top: 5px">
                        <li class="dropdown"><a class="dropdown-toggle" data-toggle="dropdown" href="#">More<span class="caret"></span></a>
                            <ul class="dropdown-menu reportico-dropdown">
                                <li><input type="submit" class="{{ BOOTSTRAP_STYLE_TOOLBAR_BUTTON }}prepareMiniMaintain swMiniMaintain" style="margin-right: 30px" title="{{ T_EDIT }} {{ T_EDITPAGEHEADERS }}" id="submit_mainqueroutppghd0000form" value="{{ T_EDITPAGEHEADERS }}" name="mainqueroutppghd0000form_ANY"></li>
                                <li><input type="submit" class="{{ BOOTSTRAP_STYLE_TOOLBAR_BUTTON }}prepareMiniMaintain swMiniMaintain" style="margin-right: 30px" title="{{ T_EDIT }} {{ T_EDITPAGEFOOTERS }}" id="submit_mainqueroutppgft0000form" value="{{ T_EDITPAGEFOOTERS }}" name="mainqueroutppgft0000form_ANY"></li>
                                <li><input type="submit" class="{{ BOOTSTRAP_STYLE_TOOLBAR_BUTTON }}prepareMiniMaintain swMiniMaintain" style="margin-top: 10px; margin-right: 30px" title="{{ T_EDIT }} {{ T_EDITPRESQLS }}" id="submit_mainquerqurypsql_SHOW" value="{{ T_EDITPRESQLS }}" name="mainquerqurypsql_ANY"></li>
                            </ul>
                        </li>
                            </div>
                        </div>
                </ul>   
            </div>
        </div>
</div>

{% endif %}
{% endif %}
