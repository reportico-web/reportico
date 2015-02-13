reportico_jquery = jQuery.noConflict();

var reportico_ajax_script = "index.php";
/*
** Reportico Javascript functions
*/
function setupDynamicGrids()
{
    if (typeof reportico_dynamic_grids === 'undefined') {
        return;
    }
    if (  reportico_jquery.type(reportico_dynamic_grids) != 'undefined' )
    if ( reportico_dynamic_grids )
    {
        reportico_jquery(".swRepPage").each(function(){
            reportico_jquery(this).dataTable(
                {
                "retrieve" : true,
                "searching" : reportico_dynamic_grids_searchable,
                "ordering" : reportico_dynamic_grids_sortable,
                "paging" : reportico_dynamic_grids_paging,
                "iDisplayLength": reportico_dynamic_grids_page_size
                }
                );
        });
    }
}

function setupDatePickers()
{
    reportico_jquery(".swDateField").each(function(){
        reportico_jquery(this).datepicker({dateFormat: reportico_datepicker_language});
    });
}

function setupModals()
{
var options = {
}
    reportico_jquery('#reporticoModal').modal(options);
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

/*
* Where multiple data tables exist due to graphs
* resize the columns of all tables to match the first
*/
function resizeHeaders()
{
  // Size page header blocks to fit page headers
  reportico_jquery(".swPageHeaderBlock").each(function() {
    var maxheight = 0;
    reportico_jquery(this).find(".swPageHeader").each(function() {
        var headerheight  = reportico_jquery(this).height();
        if ( headerheight > maxheight )
            maxheight = headerheight;
   });
   reportico_jquery(this).css("height", maxheight + "px");
  });

  //reportico_jquery(".swRepForm").columnize();

}

/*
* Where multiple data tables exist due to graphs
* resize the columns of all tables to match the first
*/
function resizeTables()
{
  var tableArr = reportico_jquery('.swRepPage');
  var tableDataRow = reportico_jquery('.swRepResultLine:first');
  var cellWidths = new Array();
  reportico_jquery(tableDataRow).each(function() {
    for(j = 0; j < reportico_jquery(this)[0].cells.length; j++){
       var cell = reportico_jquery(this)[0].cells[j];
       if(!cellWidths[j] || cellWidths[j] < cell.clientWidth) cellWidths[j] = cell.clientWidth;
    }
  });

  var tablect = 0;
  reportico_jquery(tableArr).each(function() {
    tablect++;
    if ( tablect == 1 )
        return;

    reportico_jquery(this).find(".swRepResultLine:first").each(function() {
      for(j = 0; j < reportico_jquery(this)[0].cells.length; j++){
        reportico_jquery(this)[0].cells[j].style.width = cellWidths[j]+'px';
      }
   });
 });
}


//reportico_jquery(document).on('click', 'ul.dropdown-menu li a, ul.dropdown-menu li ul li a, ul.jd_menu li a, ul.jd_menu li ul li a', function(event) 
//{
    //event.preventDefault();
    //return false;
//});

reportico_jquery(document).on('click', 'a.reportico-dropdown-item, ul li.r1eportico-dropdown a, ul li ul.reportico-dropdown li a, ul.jd_menu li a, ul.jd_menu li ul li a', function(event) 
{
    if (  reportico_jquery.type(reportico_ajax_mode) === 'undefined' || !reportico_ajax_mode)
    {
        return true;
    }

    var url = reportico_jquery(this).prop('href');
    runreport(url, this);
    event.preventDefault();
    return false;
});

/* Load Date Pickers */
reportico_jquery(document).ready(function()
{
    setupDatePickers();
    setupDropMenu();
    resizeHeaders();
    resizeTables();
    setupDynamicGrids();
});

reportico_jquery(document).on('click', '.reportico-bootstrap-modal-close', function(event) 
{
    reportico_jquery("#swMiniMaintain").html("");
    reportico_jquery('#reporticoModal').modal('hide');
});

reportico_jquery(document).on('click', '.reportico-modal-close', function(event) 
{
    reportico_jquery("#swMiniMaintain").html("");
    reportico_jquery('#reporticoModal').hide();
});

reportico_jquery(document).on('click', '.swMiniMaintainSubmit', function(event) 
{

    if ( reportico_bootstrap_modal )
        var loadpanel = reportico_jquery("#reporticoModal .modal-dialog .modal-content .modal-header");
    else
        var loadpanel = reportico_jquery("#reporticoModal .reportico-modal-dialog .reportico-modal-content .reportico-modal-header");

	var expandpanel = reportico_jquery('#swPrpExpandCell');
    reportico_jquery(loadpanel).addClass("modal-loading");

    forms = reportico_jquery(this).closest('#reportico_container').find(".swPrpForm");
    if (    reportico_jquery.type(reportico_ajax_script) === 'undefined' )
    {
        var ajaxaction = reportico_jquery(forms).prop("action");
    }
    else
    {
        ajaxaction = reportico_ajax_script;
    }

	params = forms.serialize();
    params += "&" + reportico_jquery(this).prop("name") + "=1";
    params += "&reportico_ajax_called=1";
    params += "&execute_mode=PREPARE";

    var cont = this;
    reportico_jquery.ajax({
        type: 'POST',
        url: ajaxaction,
        data: params,
        dataType: 'html',
        success: function(data, status) 
        {
          reportico_jquery(loadpanel).removeClass("modal-loading");
          if ( reportico_bootstrap_modal )
          {
            reportico_jquery('#reporticoModal').modal('hide');
            reportico_jquery('.modal-backdrop').remove();
            reportico_jquery('#reportico_container').closest('body').removeClass('modal-open');
          }
          else
            reportico_jquery('#reporticoModal').hide();
          reportico_jquery("#swMiniMaintain").html("");

          //reportico_jquery(reportico_container).removeClass("loading");
          fillDialog(data, cont);
        },
        error: function(xhr, desc, err) {
          reportico_jquery("#swMiniMaintain").html("");
          reportico_jquery('#reporticoModal').modal('hide');
          reportico_jquery('.modal-backdrop').remove();
          reportico_jquery(loadpanel).removeClass("modal-loading");
          reportico_jquery(loadpanel).prop('innerHTML',"Ajax Error: " + xhr + "\nTextStatus: " + desc + "\nErrorThrown: " + err);
        }
      });
      return false;
});

/*
** Trigger AJAX request for reportico button/link press if running in AJAX mode
** AJAX mode is in place when reportico session ("reportico_ajax_script") is set
** will generate full reportico output to replace the reportico_container tag
*/

reportico_jquery(document).on('click', '.swMiniMaintain', function(event) 
{
	var expandpanel = reportico_jquery(this).closest('#criteriaform').find('#swPrpExpandCell');
    var reportico_container = reportico_jquery(this).closest("#reportico_container");

    reportico_jquery(expandpanel).addClass("loading");
    forms = reportico_jquery(this).closest('.swMntForm,.swPrpForm,form');
    if (    reportico_jquery.type(reportico_ajax_script) === 'undefined' )
    {
        var ajaxaction = reportico_jquery(forms).prop("action");
    }
    else
    {
        ajaxaction = reportico_ajax_script;
    }

    maintainButton = reportico_jquery(this).prop("name"); 
    reportico_jquery(".reportico-modal-title").html(reportico_jquery(this).prop("title")); 
    bits = maintainButton.split("_");
	params = forms.serialize();
    params += "&execute_mode=MAINTAIN&partialMaintain=" + maintainButton + "&partial_template=mini&submit_" + bits[0] + "_SHOW=1";
    params += "&reportico_ajax_called=1";

    reportico_jquery.ajax({
        type: 'POST',
        url: ajaxaction,
        data: params,
        dataType: 'html',
        success: function(data, status) 
        {
          reportico_jquery(expandpanel).removeClass("loading");
          reportico_jquery(reportico_container).removeClass("loading");
          if ( reportico_bootstrap_modal )
            setupModals();
          else
            reportico_jquery("#reporticoModal").show();
          reportico_jquery("#swMiniMaintain").html(data);
          x = reportico_jquery(".swMntButton").prop("name");
          reportico_jquery(".swMiniMaintainSubmit").prop("id", x);
        },
        error: function(xhr, desc, err) {
          reportico_jquery(expandpanel).removeClass("loading");
          reportico_jquery(reportico_container).removeClass("loading");
          reportico_jquery(expandpanel).prop('innerHTML',"Ajax Error: " + xhr + "\nTextStatus: " + desc + "\nErrorThrown: " + err);
        }
      });

    return false;

})

/*
** Trigger AJAX request for reportico button/link press if running in AJAX mode
** AJAX mode is in place when reportico session ("reportico_ajax_script") is set
** will generate full reportico output to replace the reportico_container tag
*/
reportico_jquery(document).on('click', '.swAdminButton, .swAdminButton2, .swMenuItemLink, .swPrpSubmit, .swLinkMenu, .swLinkMenu2, .reporticoSubmit', function(event) 
{
    if ( reportico_jquery(this).hasClass("swNoSubmit" )  )
    {
        return false;
    }

    if ( reportico_jquery(this).parents("#swMiniMaintain").length == 1 ) 
    {
	    var expandpanel = reportico_jquery(this).closest('#criteriaform').find('#swPrpExpandCell');
        if ( reportico_bootstrap_modal )
            var loadpanel = reportico_jquery("#reporticoModal .modal-dialog .modal-content .modal-header");
        else
            var loadpanel = reportico_jquery("#reporticoModal .reportico-modal-dialog .reportico-modal-content .reportico-modal-header");
        var reportico_container = reportico_jquery(this).closest("#reportico_container");

        reportico_jquery(loadpanel).addClass("modal-loading");
        forms = reportico_jquery(this).closest('.swMiniMntForm');
        if (    reportico_jquery.type(reportico_ajax_script) === 'undefined' )
        {
            var ajaxaction = reportico_jquery(forms).prop("action");
        }
        else
        {
            ajaxaction = reportico_ajax_script;
        }

        params = forms.serialize();
           
        maintainButton = reportico_jquery(this).prop("name"); 
        params += "&execute_mode=MAINTAIN&partial_template=mini";
        params += "&" + reportico_jquery(this).prop("name") + "=1";
        params += "&reportico_ajax_called=1";

        reportico_jquery.ajax({
            type: 'POST',
            url: ajaxaction,
            data: params,
            dataType: 'html',
            success: function(data, status) 
            {
              reportico_jquery(loadpanel).removeClass("modal-loading");
              if ( reportico_bootstrap_modal )
                setupModals();
              reportico_jquery("#swMiniMaintain").html(data);
              x = reportico_jquery(".swMntButton").prop("name");
              reportico_jquery(".swMiniMaintainSubmit").prop("id", x);
            },
            error: function(xhr, desc, err) {
              reportico_jquery(loadpanel).removeClass("modal-loading");
              reportico_jquery(expandpanel).prop('innerHTML',"Ajax Error: " + xhr + "\nTextStatus: " + desc + "\nErrorThrown: " + err);
            }
          });

        return false;
    }

    if ( reportico_jquery(this).parent().hasClass("swRepPrintBox" )  )
    {
        //var data = reportico_jquery(this).closest("#reportico_container").html();
        //html_print(data);
        window.print();
        return false;
    }

    if (  reportico_jquery.type(reportico_ajax_mode) === 'undefined' || !reportico_ajax_mode)
    {
        return true;
    }

	var expandpanel = reportico_jquery(this).closest('#criteriaform').find('#swPrpExpandCell');
    var reportico_container = reportico_jquery(this).closest("#reportico_container");

    if ( !reportico_jquery(this).prop("href") )
    {
            reportico_jquery(expandpanel).addClass("loading");
            reportico_jquery(reportico_container).addClass("loading");

            forms = reportico_jquery(this).closest('.swMntForm,.swPrpForm,form');
            if (    reportico_jquery.type(reportico_ajax_script) === 'undefined' )
            {
                var ajaxaction = reportico_jquery(forms).prop("action");
            }
            else
            {
			    ajaxaction = reportico_ajax_script;
            }


			params = forms.serialize();
            params += "&" + reportico_jquery(this).prop("name") + "=1";
            params += "&reportico_ajax_called=1";

            csvpdfoutput = false;

            if (  reportico_jquery(this).prop("name") != "submit_design_mode" )
            reportico_jquery(reportico_container).find("input:radio").each(function() { 
                d = 0;
                nm = reportico_jquery(this).prop("value");
                chk = reportico_jquery(this).prop("checked");
                if ( chk && ( nm == "PDF" || nm == "CSV"  ) )
                    csvpdfoutput = true;
            });

            if ( csvpdfoutput )
            {
                var windowSizeArray = [ "width=200,height=200",
                          "width=300,height=400,scrollbars=yes" ];

                var url = ajaxaction +"?" + params;
                var windowName = "popUp";//reportico_jquery(this).prop("name");
                var windowSize = windowSizeArray[reportico_jquery(this).prop("rel")];
                window.open(url, windowName, "width=200,height=200");
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
                  reportico_jquery(expandpanel).prop('innerHTML',"Ajax Error: " + xhr + "\nTextStatus: " + desc + "\nErrorThrown: " + err);
                }
              });
              return false;
    }
    else
    {
        url = reportico_jquery(this).prop("href");
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
reportico_jquery(document).on('click', '#returnFromExpand', function() {

	var critform = reportico_jquery(this).closest('#criteriaform');
	var expandpanel = reportico_jquery(this).closest('#criteriaform').find('#swPrpExpandCell');
    reportico_jquery(expandpanel).addClass("loading");

    var params = reportico_jquery(critform).serialize();
    params += "&execute_mode=PREPARE";
    params += "&partial_template=critbody";
    params += "&" + reportico_jquery(this).prop("name") + "=1";

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
        reportico_jquery(fillPoint).prop('innerHTML',"Ajax Error: " + xhr + "\nTextStatus: " + desc + "\nErrorThrown: " + err);
      }
    });
    return false;
	});

  reportico_jquery(document).on('click', '#reporticoPerformExpand', function() {

	forms = reportico_jquery(this).closest('.swMntForm,.swPrpForm,form');
	var ajaxaction = reportico_jquery(forms).prop("action");
	var critform = reportico_jquery(this).closest('#criteriaform');
    ajaxaction = reportico_ajax_script;

    var params = reportico_jquery(critform).serialize();
    params += "&execute_mode=PREPARE";
    params += "&partial_template=expand";
    params += "&" + reportico_jquery(this).prop("name") + "=1";

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
          reportico_jquery(fillPoint).prop('innerHTML',"Ajax Error: " + xhr + "\nTextStatus: " + desc + "\nErrorThrown: " + err);
        }
      });
      return false;
    });


/*
** AJAX call to run a report
** In pdf/csv mode this needs to trigger opening of a new browser window
** with output in rather that directing to screen
*/
reportico_jquery(document).on('click', '.swPrintBox,.prepareAjaxExecute,#prepareAjaxExecute', function() {

    var reportico_container = reportico_jquery(this).closest("#reportico_container");
    reportico_jquery(reportico_container).find("#rpt_format_pdf").prop("checked", false );
    reportico_jquery(reportico_container).find("#rpt_format_csv").prop("checked", false );
    reportico_jquery(reportico_container).find("#rpt_format_html").prop("checked", false );
    reportico_jquery(reportico_container).find("#rpt_format_json").prop("checked", false );
    reportico_jquery(reportico_container).find("#rpt_format_xml").prop("checked", false );
    if (  reportico_jquery(this).hasClass("swPDFBox") ) 
        reportico_jquery(reportico_container).find("#rpt_format_pdf").prop("checked", "checked");
    if (  reportico_jquery(this).hasClass("swCSVBox") ) 
        reportico_jquery(reportico_container).find("#rpt_format_csv").prop("checked", "checked");
    if (  reportico_jquery(this).hasClass("swHTMLBox") ) 
        reportico_jquery(reportico_container).find("#rpt_format_html").prop("checked", "checked");
    if (  reportico_jquery(this).hasClass("swHTMLGoBox") ) 
        reportico_jquery(reportico_container).find("#rpt_format_html").prop("checked", "checked");
    if (  reportico_jquery(this).hasClass("swXMLBox") ) 
        reportico_jquery(reportico_container).find("#rpt_format_xml").prop("checked", "checked");
    if (  reportico_jquery(this).hasClass("swJSONBox") ) 
        reportico_jquery(reportico_container).find("#rpt_format_json").prop("checked", "checked");
    if (  reportico_jquery(this).hasClass("swPrintBox") ) 
        reportico_jquery(reportico_container).find("#rpt_format_html").prop("checked", "checked");

    if (  !reportico_jquery(this).hasClass("swPrintBox") )
    if (  reportico_jquery.type(reportico_ajax_mode) === 'undefined' || !reportico_ajax_mode)
    {
        return true;
    }


	var expandpanel = reportico_jquery(this).closest('#criteriaform').find('#swPrpExpandCell');
	var critform = reportico_jquery(this).closest('#criteriaform');
    reportico_jquery(expandpanel).addClass("loading");

    params = reportico_jquery(critform).serialize();
    params += "&execute_mode=EXECUTE";
    params += "&" + reportico_jquery(this).prop("name") + "=1";

    forms = reportico_jquery(this).closest('.swMntForm,.swPrpForm,form');
    if ( jQuery.type(reportico_ajax_script) === 'undefined' || !reportico_ajax_script )
    {
        var ajaxaction = reportico_jquery(forms).prop("action");
    }
    else
    {
        ajaxaction = reportico_ajax_script;
    }

    var csvpdfoutput = false;
    var htmloutput = false;

    reportico_report_title = reportico_jquery(this).closest('#reportico_container').find('.swTitle').html();

    if (  !reportico_jquery(this).hasClass("swPrintBox") )
    {
        reportico_jquery(reportico_container).find("input:radio").each(function() { 
            d = 0;
            nm = reportico_jquery(this).prop("value");
            chk = reportico_jquery(this).prop("checked");
            if ( chk && ( nm == "PDF" || nm == "CSV"  ) )
                csvpdfoutput = true;
            //if ( chk && ( nm == "HTML" ) )
                //htmloutput = true;
        });
    }


    if ( csvpdfoutput )
    {
        var windowSizeArray = [ "width=200,height=200",
                  "width=300,height=400,scrollbars=yes" ];

        var url = ajaxaction +"?" + params;
        var windowName = "popUp";//reportico_jquery(this).prop("name");
        var windowSize = windowSizeArray[reportico_jquery(this).prop("rel")];
        window.open(url, windowName, "width=200,height=200");
        reportico_jquery(expandpanel).removeClass("loading");
        return false;
    }

    if (  reportico_jquery(this).hasClass("swPrintBox") )
    {
        htmloutput = true;
    }

    if ( !htmloutput )
        params += "&reportico_ajax_called=1";

    if (  reportico_jquery(this).hasClass("swPrintBox") )
        params += "&printable_html=1&new_reportico_window=1";

    var cont = this;
    reportico_jquery.ajax({
        type: 'POST',
        url: ajaxaction,
        data: params,
        dataType: 'html',
        success: function(data, status) {
        reportico_jquery(expandpanel).removeClass("loading");
        if ( htmloutput )
        {
            html_print(reportico_report_title, data);
        }
        else
            fillDialog(data, cont);
       },
       error: function(xhr, desc, err) {
         reportico_jquery(expandpanel).removeClass("loading");
         try {
            // a try/catch is recommended as the error handler
            // could occur in many events and there might not be
            // a JSON response from the server
            var errstatus = reportico_jquery.parseJSON(xhr.responseText);
            var msg = errstatus.errmsg;
            //reportico_jquery(expandpanel).prop('innerHTML', msg);
            alert(msg);
        } catch(e) { 
            reportico_jquery(expandpanel).prop('innerHTML',"Error occurred in data request. Error " + xhr.status + ": " + xhr.statusText);
        }
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
  setupDynamicGrids();
  resizeHeaders();
  resizeTables();
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

reporticohtmlwindow = null;
function html_div_print(data) 
{
    var reporticohtmlwindow = window.open('oooo', reportico_report_title, 'height=600,width=800');
    reporticohtmlwindow.document.write('<html><head><title>' + reportico_report_title + '</title>');
    reporticohtmlwindow.document.write('<link rel="stylesheet" href="' + reportico_css_path + '" type="text/css" />');
    reporticohtmlwindow.document.write('</head><body >');
    reporticohtmlwindow.document.write(data);
    reporticohtmlwindow.document.write('</body></html>');
    
    reporticohtmlwindow.print();
    reporticohtmlwindow.close();

    return true;
}

function html_print(title, data) 
{
    if (navigator.userAgent.indexOf('Chrome/') > 0) {
        if (reporticohtmlwindow) {
            reporticohtmlwindow.close();
            reporticohtmlwindow = null;
        }
    }

    reporticohtmlwindow = window.open('', "reportico_print", 'location=no,scrollbars=yes,status=no,height=600,width=800');
    d = reporticohtmlwindow.document.open("text/html","replace");
    reporticohtmlwindow.document.write(data);
    reporticohtmlwindow.document.close();

    setTimeout(html_print_fix,200);

    reporticohtmlwindow.focus();
    return true;
}

function html_print_fix() 
{
    if(!reporticohtmlwindow.resizeOutputTables) 
    {
        setTimeout(html_print_fix,1000);
    } 
    else
    { 
        reporticohtmlwindow.resizeOutputTables(reporticohtmlwindow); 
    }
}
