reportico_jquery = jQuery.noConflict();

var reportico_ajax_script = "index.php";
/*
** Reportico Javascript functions
*/
function setupDatePickers()
{
    reportico_jquery(".swDateField").each(function(){
        reportico_jquery(this).datepicker({dateFormat: reportico_datepicker_language});
    });
}

function setupDropMenu()
{
    if ( reportico_jquery('ul.jd_menu').length != 0  )
    {
        reportico_jquery('ul.jd_menu').jdMenu();
        //reportico_jquery(document).bind('click', function() {
            //reportico_jquery('ul.jd_menu ul:visible').jdMenuHide();
        //});
    }
}

reportico_jquery('ul.jd_menu li a, ul.jd_menu li ul li a').live('click', function(event) 
{
    if (  reportico_jquery.type(reportico_ajax_mode) === 'undefined' || !reportico_ajax_mode)
    {
        return true;
    }

    var url = reportico_jquery(this).attr('href');
    runreport(url, this);
    return false;
});

/* Load Date Pickers */
reportico_jquery(document).ready(function()
{
    setupDatePickers();
    setupDropMenu();
});


/*
** Trigger AJAX request for reportico button/link press if running in AJAX mode
** AJAX mode is in place when reportico session ("reportico_ajax_script") is set
** will generate full reportico output to replace the reportico_container tag
*/
reportico_jquery('.swMenuItemLink, .swPrpSubmit, .swLinkMenu, .reporticoSubmit').live('click', function(event) 
{
    if (  reportico_jquery.type(reportico_ajax_mode) === 'undefined' || !reportico_ajax_mode)
    {
        return true;
    }

	var expandpanel = reportico_jquery(this).closest('#criteriaform').find('#swPrpExpandCell');
    var reportico_container = reportico_jquery(this).closest("#reportico_container");

    if ( !reportico_jquery(this).attr("href") )
    {
            reportico_jquery(expandpanel).addClass("loading");
            reportico_jquery(reportico_container).addClass("loading");

            forms = reportico_jquery(this).closest('.swMntForm,.swPrpForm,form');
            if (    reportico_jquery.type(reportico_ajax_script) === 'undefined' )
            {
                var ajaxaction = reportico_jquery(forms).attr("action");
            }
            else
            {
			    ajaxaction = reportico_ajax_script;
            }


			params = forms.serialize();
            params += "&option=com_reportico";
            params += "&format=raw";
            params += "&" + reportico_jquery(this).attr("name") + "=1";
            params += "&reportico_ajax_called=1";

            csvpdfoutput = false;
            reportico_jquery(reportico_container).find("input:radio").each(function() { 
                d = 0;
                nm = reportico_jquery(this).attr("value");
                chk = reportico_jquery(this).attr("checked");
                if ( chk && ( nm == "PDF" || nm == "CSV"  ) )
                    csvpdfoutput = true;
            });

            if ( csvpdfoutput )
            {
                var windowSizeArray = [ "width=200,height=200",
                          "width=300,height=400,scrollbars=yes" ];

                var url = ajaxaction +"?" + params;
                var windowName = "popUp";//reportico_jquery(this).attr("name");
                var windowSize = windowSizeArray[reportico_jquery(this).attr("rel")];
                window.open(url, windowName, windowSize);
                reportico_jquery(expandpanel).removeClass("loading");
                reportico_jquery(reportico_container).removeClass("loading");
                return false;
            }


            var cont = this;
            reportico_jquery.ajax({
                type: 'POST',
                url: ajaxaction,
                data: params,
                dataType: 'html',
                success: function(data, status) 
                {
                  reportico_jquery(expandpanel).removeClass("loading");
                  reportico_jquery(reportico_container).removeClass("loading");
                  fillDialog(data, cont);
                },
                error: function(xhr, desc, err) {
                  reportico_jquery(expandpanel).removeClass("loading");
                  reportico_jquery(reportico_container).removeClass("loading");
                  reportico_jquery(expandpanel).attr('innerHTML',"Ajax Error: " + xhr + "\nTextStatus: " + desc + "\nErrorThrown: " + err);
                }
              });
              return false;
    }
    else
    {
        url = reportico_jquery(this).attr("href");
        params = false;
        runreport(url, this);
    }
    return false;
})

/*
** Called when used presses ok in expand mode to 
** refresh middle prepare mode section with non expand mode 
** text
*/
reportico_jquery('#returnFromExpand').live('click', function() {

	var critform = reportico_jquery(this).closest('#criteriaform');
	var expandpanel = reportico_jquery(this).closest('#criteriaform').find('#swPrpExpandCell');
    reportico_jquery(expandpanel).addClass("loading");

    var params = reportico_jquery(critform).serialize();
    params += "&execute_mode=PREPARE";
    params += "&option=com_reportico";
    params += "&format=raw";
    params += "&partial_template=critbody";
    params += "&" + reportico_jquery(this).attr("name") + "=1";

	forms = reportico_jquery(this).closest('.swMntForm,.swPrpForm,form');
    ajaxaction = reportico_ajax_script;

	fillPoint = reportico_jquery(this).closest('#criteriaform').find('#criteriabody');
		
    reportico_jquery.ajax({
      type: 'POST',
      url: ajaxaction,
      data: params,
      dataType: 'html',
      success: function(data, status) {
        reportico_jquery(expandpanel).removeClass("loading");
        reportico_jquery(fillPoint).html(data);
        setupDatePickers();
        setupDropMenu();
        },
        error: function(xhr, desc, err) {
        reportico_jquery(expandpanel).removeClass("loading");
        reportico_jquery(fillPoint).attr('innerHTML',"Ajax Error: " + xhr + "\nTextStatus: " + desc + "\nErrorThrown: " + err);
      }
    });
    return false;
	});

  reportico_jquery('#reporticoPerformExpand').live('click', function() {

	forms = reportico_jquery(this).closest('.swMntForm,.swPrpForm,form');
	var ajaxaction = reportico_jquery(forms).attr("action");
	var critform = reportico_jquery(this).closest('#criteriaform');

    var params = reportico_jquery(critform).serialize();
    params += "&execute_mode=PREPARE";
    params += "&option=com_reportico";
    params += "&format=raw";
    params += "&partial_template=expand";
    params += "&" + reportico_jquery(this).attr("name") + "=1";

	var fillPoint = reportico_jquery(this).closest('#criteriaform').find('#swPrpExpandCell');
    reportico_jquery(fillPoint).addClass("loading");

    reportico_jquery.ajax({
        type: 'POST',
        url: ajaxaction,
        data: params,
        dataType: 'html',
        success: function(data, status) {
          reportico_jquery(fillPoint).removeClass("loading");
          reportico_jquery(fillPoint).html(data);
        },
        error: function(xhr, desc, err) {
          reportico_jquery(fillPoint).removeClass("loading");
          reportico_jquery(fillPoint).attr('innerHTML',"Ajax Error: " + xhr + "\nTextStatus: " + desc + "\nErrorThrown: " + err);
        }
      });
      return false;
    });


/*
** AJAX call to run a report
** In pdf/csv mode this needs to trigger opening of a new browser window
** with output in rather that directing to screen
*/
reportico_jquery('#prepareAjaxExecute').live('click', function() {

    if (  reportico_jquery.type(reportico_ajax_mode) === 'undefined' || !reportico_ajax_mode)
    {
        return true;
    }

    var reportico_container = reportico_jquery(this).closest("#reportico_container");
	var expandpanel = reportico_jquery(this).closest('#criteriaform').find('#swPrpExpandCell');
	var critform = reportico_jquery(this).closest('#criteriaform');
    reportico_jquery(expandpanel).addClass("loading");

    params = reportico_jquery(critform).serialize();
    params += "&execute_mode=EXECUTE";
    params += "&" + reportico_jquery(this).attr("name") + "=1";
    params += "&reportico_ajax_called=1";
    params += "&option=com_reportico";
    params += "&format=raw";

    forms = reportico_jquery(this).closest('.swMntForm,.swPrpForm,form');
    if ( jQuery.type(reportico_ajax_script) === 'undefined' || !reportico_ajax_script )
    {
        var ajaxaction = reportico_jquery(forms).attr("action");
    }
    else
    {
        ajaxaction = reportico_ajax_script;
    }

    csvpdfoutput = false;
    reportico_jquery(reportico_container).find("input:radio").each(function() { 
        d = 0;
        nm = reportico_jquery(this).attr("value");
        chk = reportico_jquery(this).attr("checked");
        if ( chk && ( nm == "PDF" || nm == "CSV"  ) )
            csvpdfoutput = true;
    });

    if ( csvpdfoutput )
    {
        var windowSizeArray = [ "width=200,height=200",
                  "width=300,height=400,scrollbars=yes" ];

        var url = ajaxaction +"?" + params;
        var windowName = "popUp";//reportico_jquery(this).attr("name");
        var windowSize = windowSizeArray[reportico_jquery(this).attr("rel")];
        window.open(url, windowName, windowSize);
        reportico_jquery(expandpanel).removeClass("loading");
        return false;
    }

    var cont = this;
    reportico_jquery.ajax({
        type: 'POST',
        url: ajaxaction,
        data: params,
        dataType: 'html',
        success: function(data, status) {
        reportico_jquery(expandpanel).removeClass("loading");
        fillDialog(data, cont);
       },
       error: function(xhr, desc, err) {
         reportico_jquery(expandpanel).removeClass("loading");
         reportico_jquery(expandpanel).attr('innerHTML',"Ajax Error: " + xhr + "\nTextStatus: " + desc + "\nErrorThrown: " + err);
       }
     });
     return false;
   });

/*
** Runs an AJAX reportico request from a link
*/
function runreport(url, container) 
{
    url += "&reportico_template=";
    url += "&reportico_ajax_called=1";
    url += "&option=com_reportico";
    url += "&format=raw";
    reportico_jquery(container).closest("#reportico_container").addClass("loading");
    reportico_jquery.ajax({
        type: "POST",
        contentType: "application/json; charset=utf-8",
        url: url,
        dataType: "html",
        error: function(XMLHttpRequest, textStatus, errorThrown) {
            alert ("Ajax Error: " + XMLHttpRequest.responseText + "\nTextStatus: " + textStatus + "\nErrorThrown: " + errorThrown);
        },
        success: function(data, status) {
            reportico_jquery(container).closest("#reportico_container").removeClass("loading");
            fillDialog(data,container);
        }
    });
}

function fillDialog(results, cont) {
  x = reportico_jquery(cont).closest("#reportico_container");
  reportico_jquery(cont).closest("#reportico_container").replaceWith(results);
  setupDatePickers();
  setupDropMenu();
}

var ie7 = (document.all && !window.opera && window.XMLHttpRequest) ? true : false;

/*
** Shows and hides a block of design items fields
*/
function toggleLine(id) {

    var a = this;
    var nm = a.id;
    var togbut = document.getElementById(id);
    var ele = document.getElementById("toggleText");
    var elems = document.getElementsByTagName('*'),i;
    for (i in elems)
    {
		if ( ie7 )
		{
        	if((" "+elems[i].className+" ").indexOf(" "+id+" ") > -1)
			{
            	if(elems[i].style.display == "inline") {
                	elems[i].style.display = "none";
                	togbut.innerHTML = "+";
            	}
            	else {
                	togbut.innerHTML = "-";
                	elems[i].style.display = "";
                	elems[i].style.display = "inline";
            	}
			}
		}
		else
		{
        	if((" "+elems[i].className+" ").indexOf(" "+id+" ") > -1)
			{
            	if(elems[i].style.display == "table-row") {
                	elems[i].style.display = "none";
                	togbut.innerHTML = "+";
            	}
            	else {
                	togbut.innerHTML = "-";
                	elems[i].style.display = "";
                	elems[i].style.display = "table-row";
            	}
			}
		}
    }
} 
