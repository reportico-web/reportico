<div>
    {if strlen($ERRORMSG)>0}
        <div style="display:none" id="reporticoEmbeddedError">
            {$ERRORMSG}
        </div>
        {literal}
            <script>
                if ( typeof(reportico_jquery) != "undefined" )
                    reportico_jquery(document).ready(function()
                    {
                        showParentNoticeModal(reportico_jquery("#reporticoEmbeddedError").html());
                    });
                else
                    if ( typeof(parent.reportico_jquery) != "undefined" ) 
                        parent.reportico_jquery(document).ready(function()
                        {   
                            parent.showNoticeModal(document.getElementById("reporticoEmbeddedError").innerHTML);
                        });
            </script>
        {/literal}
        <div class="alert alert-danger" role="alert">
            {$ERRORMSG}
        </div>
    {/if}
    {if strlen($STATUSMSG)>0} 
        <div class="alert alert-info" role="alert">
            {$STATUSMSG}
        </div>
    {/if}
</div>
