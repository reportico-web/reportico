{% if not OUTPUT_SHOW_SHOWGRAPH %}
                                        <input style="display:none" type="checkbox" name="target_show_graph" value="1" {{ OUTPUT_SHOWGRAPH }}>
{% endif %}
<INPUT type="checkbox" style="display:none" name="user_criteria_entered" value="1" checked="1">
<div class="container" style="width: 100%; display: block">



<div class="nav-collapse collapse in" id="reportico-bootstrap-collapse-show-hide">
   <ul class="nav navbar-nav pull-right navbar-right">
           <li class="dropdown"><a class="dropdown-toggle" data-toggle="dropdown" href="#">{{ T_SHOW }}<span class="caret"></span></a>
               <ul class="dropdown-menu reportico-dropdown" style="padding-top:0px; padding-bottom:0px">
                  <li>
                       <div class="input-group" style="margin-bottom: 0px; ; float: right">
                           <label style="width:200px" class="form-control" aria-label="Text input with checkbox">{{ T_SHOW_CRITERIA }}</label>
                           <span class="input-group-addon">
                               <input type="checkbox" name="target_show_criteria" value="1" {{ OUTPUT_SHOWCRITERIA }}>
                           </span>
                       </div>
                   </li>
                   <li>
                       <div class="input-group" style="margin-bottom: 0px; ; float: right">
                           <label class="form-control" aria-label="Text input with checkbox">{{ T_SHOW_DETAIL }}</label>
                           <span class="input-group-addon">
                               <input type="checkbox" name="target_show_detail" value="1" {{ OUTPUT_SHOWDETAIL }}>
                           </span>
                       </div>
                   </li>
{% if OUTPUT_SHOW_SHOWGRAPH %}
       <li>
                       <div class="input-group" style="margin-bottom: 0px; ; float: right">
                           <label class="form-control" aria-label="Text input with checkbox">{{ T_SHOW_GRAPH }}</label>
                           <span class="input-group-addon">
                               <input type="checkbox" name="target_show_graph" value="1" {{ OUTPUT_SHOWGRAPH }}>
                           </span>
                       </div>
  </li>
{% endif %}
       <li>
                       <div class="input-group" style="margin-bottom: 0px; ; float: right">
                           <label class="form-control" aria-label="Text input with checkbox">{{ T_SHOW_GRPHEADERS }}</label>
                           <span class="input-group-addon">
                               <input type="checkbox" name="target_show_group_headers" value="1" {{ OUTPUT_SHOWGROUPHEADERS }}>
                           </span>
                       </div>
                   </li>
       <li>
                       <div class="input-group" style="margin-bottom: 0px; ; float: right">
                           <label class="form-control" aria-label="Text input with checkbox">{{ T_SHOW_GRPTRAILERS }}</label>
                           <span class="input-group-addon">
                               <input type="checkbox" name="target_show_group_trailers" value="1" {{ OUTPUT_SHOWGROUPTRAILERS }}>
                           </span>
                       </div>
                   </li>
               </ul>
           </li>
   </ul>
                </div>
            </div>
