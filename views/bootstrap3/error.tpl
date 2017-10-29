<div>
    {% if ERRORMSG|length>0 %}
        <div style="display:none" id="reporticoEmbeddedError">
            {{ ERRORMSG|raw }}
        </div>
        
            <script>
                if ( typeof(reportico_jquery) not = "undefined" )
                    reportico_jquery(document).ready(function()
                    {
                        showParentNoticeModal(reportico_jquery("#reporticoEmbeddedError").html());
                    });
                else
                    if ( typeof(parent.reportico_jquery) not = "undefined" ) 
                        parent.reportico_jquery(document).ready(function()
                        {   
                            parent.showNoticeModal(document.getElementById("reporticoEmbeddedError").innerHTML);
                        });
            </script>
        
        <div class="alert alert-danger" role="alert">
            {{ ERRORMSG|raw }}
        </div>
    {% endif %}
    {% if STATUSMSG|length>0 %} 
        <div class="alert alert-info" role="alert">
            {{ STATUSMSG|raw }}
        </div>
    {% endif %}
</div>
