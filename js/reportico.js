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
        reportico_jquery(this).datepicker(
            {
                dateFormat: reportico_datepicker_language,
                onSelect: function(dateText) {              // Automatically set a to date value from a from date
                    id = this.id;
                    if ( id.match(/_FROMDATE/) )
                    {
                        todate = id.replace(/_FROMDATE/, "_TODATE");
                        reportico_jquery("#" + todate).prop("value", this.value);
                    }
             }});
        });
}

function setupTooltips()
{
    reportico_jquery(".reportico_tooltip").each(function(){
        reportico_jquery(this).tooltip();
    });
}

// Sets jQuery attributes for dynamic criteria
function setupCriteriaItems()
{

    for ( i in reportico_criteria_items )
    {
        j = reportico_criteria_items[i];

        // Already checked values for prepopulation
        preselected =[];
        reportico_jquery("#select2_dropdown_" + j + ",#select2_dropdown_expanded_" + j).find("option").each(function() {
            lab = reportico_jquery(this).prop("label");
            value = reportico_jquery(this).prop("value");
            checked = reportico_jquery(this).attr("checked");
            if ( checked )
            {
                preselected.push(value);
            }
        });
        
        reportico_jquery("#select2_dropdown_" + j + ",#select2_dropdown_expanded_" + j).select2({
          ajax: {
            url: reportico_ajax_script + "?execute_mode=CRITERIA&reportico_criteria=" + j,
            type: 'POST',
            error: function(data, status) {
                return {
                    results: [{ id: 'error', text: 'Unable to autocomplete', disabled: true }]
                }
            },
            dataType: 'json',
            delay: 250,
            data: function (params) {
                forms = reportico_jquery('#reportico_container').find(".swPrpForm");
	            formparams = forms.serialize();
                formparams += "&reportico_ajax_called=1";
                formparams += "&execute_mode=CRITERIA";
                formparams += "&reportico_criteria_match=" + params.term;;
              return formparams;
              return {
                q: params.term, // search term
                formparams: formparams,
                page: params.page
              };
            },
            processResults: function (data, params) {
              // parse the results into the format expected by Select2
              // since we are using custom formatting functions we do not need to
              // alter the remote JSON data, except to indicate that infinite
              // scrolling can be used

              params.page = params.page || 1;

              return {
                results: data.items,
                pagination: {
                  more: (params.page * 30) < data.total_count
                }
              };
            },
            cache: false,
            placeholder: "hello",
            allowClear: true
          },
          escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
          minimumInputLength: 1
          //templateResult: select2FormatResult, // omitted for brevity, see the source of this page
          //templateSelection: select2FormatSelection // omitted for brevity, see the source of this page
        })
        reportico_jquery("#select2_dropdown_" + j).val(preselected).trigger("change");

        // If select2 exists in expand tab then hide the search box .. its not relevant
        reportico_jquery("#select2_dropdown_expanded_" + j).each(function() {
            reportico_jquery("#expandsearch").hide();
            reportico_jquery("#reporticoSearchExpand").hide();
        });
    };

}

function select2FormatResult(data)
{
    return data;
}

function select2FormatSelection(data)
{
    return data.name;
}

function formatState (state) {
  if (!state.id) { return state.text; }
  var $state = $(
    '<span><img src="vendor/images/flags/' + state.element.value.toLowerCase() + '.png" class="img-flag" /> ' + state.text + '</span>'
  );
  return $state;
};


function setupModals()
{
    var options = { } 
    reportico_jquery('#reporticoModal').modal(options);
}

function setupNoticeModals()
{
    var options = { } 
    reportico_jquery('#reporticoNoticeModal').modal(options);
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

function setupCheckboxes()
{
    reportico_jquery('.reportico_bootstrap2_checkbox').on('click', function(event){
        //The event won't be propagated to the document NODE and 
        // therefore events delegated to document won't be fired
        event.stopPropagation();
    });
}

/*
* Where multiple data tables exist due to graphs
* resize the columns of all tables to match the first
*/
function resizeHeaders()
{
  // Size page header blocks to fit page headers
  reportico_jquery(".swPageHeaderBlock").each(function() {
    var parenty = reportico_jquery(this).position().top;
    var maxheight = 0;
    reportico_jquery(this).find(".swPageHeader").each(function() {
        var headerheight  = reportico_jquery(this).outerHeight();
        reportico_jquery(this).find("img").each(function() {
            var imgheight = reportico_jquery(this).prop("height");
            if ( imgheight > headerheight )
                headerheight = imgheight;
        });
        var margintop  = parseInt(reportico_jquery(this).css("margin-top"));
        var marginbottom  = parseInt(reportico_jquery(this).css("margin-bottom"));
        headerheight += margintop + marginbottom;
        if ( headerheight > maxheight )
            maxheight = headerheight;
   });
   reportico_jquery(this).css("height", maxheight + "px");
  });
  //reportico_jquery(".swNewPageHeaderBlock").hide();
        //ct = 1;
        //hdrpos = 0;
        //while ( reportico_jquery(".swPageFooterBlock"+ct).length )
        //{
            //if ( reportico_jquery(".swPageHeaderBlock"+(ct+1)).length )
                //hdrpos = reportico_jquery(".swPageHeaderBlock"+(ct+1)).offset().top;
            //else
                //hdrpos = hdrpos + 1000;
            //reportico_jquery(".swPageFooterBlock"+ct).css("top", ( hdrpos ) + "px" );
            //ct++;
        //}
    

  //reportico_jquery(".swRepForm").columnize();

}

/*
* Where multiple data tables exist due to graphs
* resize the columns of all tables to match the first
*/
function resizeTables()
{

  var tableArr = reportico_jquery('.swRepPage');
  if ( tableArr.length == 0 )
    return;
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
    setupTooltips();
    setupDropMenu();
    resizeHeaders();
    resizeTables();
    setupDynamicGrids();
    setupCriteriaItems();
    setupCheckboxes();
    //reportico_jquery('#select2_dropdown_country').select2();

});

reportico_jquery(document).on('click', '.reportico-notice-modal-close,.reportico-notice-modal-button', function(event) 
{
    reportico_jquery("#swMiniMaintain").html("");
    reportico_jquery('#reporticoNoticeModal').hide();
});

reportico_jquery(document).on('click', '.swMiniMaintainSubmit,.reportico-bootstrap-modal-close,.reportico-modal-close', function(event) 
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
    forms = reportico_jquery(this).closest('.swMntForm,.swPrpForm,.swPrpSaveForm,form');
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

reportico_jquery(document).on('click', '.swPrpSaveButton', function(event) 
{
	var expandpanel = reportico_jquery(this).closest('#criteriaform').find('#swPrpExpandCell');
    var reportico_container = reportico_jquery(this).closest("#reportico_container");

    reportico_jquery(expandpanel).addClass("loading");
    if (    reportico_jquery.type(reportico_ajax_script) === 'undefined' )
    {
        var ajaxaction = reportico_jquery(forms).prop("action");
    }
    else
    {
        ajaxaction = reportico_ajax_script;
    }

    filename = reportico_jquery("#swPrpSaveFile").prop("value");
	params = "";
    params += "&execute_mode=MAINTAIN&submit_xxx_PREPARESAVE=1&xmlout=" + filename;
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
          //alert(data);
        },
        error: function(xhr, desc, err) {
          reportico_jquery(expandpanel).removeClass("loading");
          reportico_jquery(reportico_container).removeClass("loading");
          showNoticeModal(xhr.responseText);
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
                if (typeof reportico_pdf_delivery_mode == 'undefined'
                    || !reportico_pdf_delivery_mode || reportico_pdf_delivery_mode != "DOWNLOAD_SAME_WINDOW" 
                    )
                {
                    reportico_jquery(expandpanel).removeClass("loading");
                    reportico_jquery(reportico_container).removeClass("loading");
                    var windowSizeArray = [ "width=200,height=200",
                          "width=300,height=400,scrollbars=yes" ];

                    var url = ajaxaction +"?" + params;

                    var windowName = "popUp";//reportico_jquery(this).prop("name");
                    var windowSize = windowSizeArray[reportico_jquery(this).prop("rel")];
                    window.open(url, windowName, "width=400,height=400").focus();
                    reportico_jquery(expandpanel).removeClass("loading");
                    window.focus();
                }
                else
                {

                    reportico_jquery(expandpanel).removeClass("loading");
                    var buttonName = reportico_jquery(this).prop("name");
                    var formparams = forms.serializeObject();
                    formparams['reportico_ajax_called'] = '1';
                    formparams[buttonName] = '1';

                    // Download pdf/csv from within current window
                    ajaxFileDownload(ajaxaction, formparams, expandpanel, reportico_container);
                }

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
 * Use ajax to return pdf or csv output and download to file.
 * For pdf, output is received in base64. 
 */
function ajaxFileDownload(url, data, expandpanel, reportico_container) {

    reportico_jquery.ajax({
      type: 'POST',
      url: url,
      data: data,
      dataType: 'html',
      success: function(data, status, request) {
        reportico_jquery(expandpanel).removeClass("loading");
        reportico_jquery(reportico_container).removeClass("loading");

        // PDF and CSV files are received in base64
        var contenttype = request.getResponseHeader('Content-Type');
        if ( contenttype == "application/pdf" )
        {
            var saveto = request.getResponseHeader('Content-Disposition');
            saveto = saveto.replace(/attachment;filename=/,"");
            objurl = "data:application/pdf;base64," + data;
            download(objurl, saveto, "application/pdf");
        }

        if ( contenttype == "application/octet-stream" )
        {
            var saveto = request.getResponseHeader('Content-Disposition');
            saveto = saveto.replace(/attachment;filename=/,"");
            objurl = "data:application/octet-stream;base64," + data;
            download(objurl, saveto, "application/pdf");
        }
      },
       error: function(xhr, desc, err) {
        reportico_jquery(expandpanel).removeClass("loading");
        reportico_jquery(reportico_container).removeClass("loading");
         try {
            // a try/catch is recommended as the error handler
            // could occur in many events and there might not be
            // a JSON response from the server
            var errstatus = reportico_jquery.parseJSON(xhr.responseText);
            var msg = errstatus.errmsg;
            //reportico_jquery(expandpanel).prop('innerHTML', msg);
            showNoticeModal(msg);

        } catch(e) { 
            showNoticeModal(xhr.responseText);
        }
       }
    
    });
}

//general serializeObject function - e.g. turn a form's fields into an object
reportico_jquery.fn.serializeObject = function() {
  var arrayData, objectData;
  arrayData = this.serializeArray();
  objectData = {};

  reportico_jquery.each(arrayData, function() {
    var value;

    if (this.value != null) {
      value = this.value;
    } else {
      value = '';
    }

    if (objectData[this.name] != null) {
      if (!objectData[this.name].push) {
        objectData[this.name] = [objectData[this.name]];
      }

      objectData[this.name].push(value);
    } else {
      objectData[this.name] = value;
    }
  });

  return objectData;
};
// ---------------------------------
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
        setupTooltips();
        setupDropMenu();
        setupCriteriaItems();
        setupCheckboxes();
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
          setupDatePickers();
          setupTooltips();
          setupDropMenu();
          setupCriteriaItems();
          setupCheckboxes();
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
        if (typeof reportico_pdf_delivery_mode == 'undefined'
            || !reportico_pdf_delivery_mode || reportico_pdf_delivery_mode != "DOWNLOAD_SAME_WINDOW" 
             )
        {
            var windowSizeArray = [ "width=200,height=200",
                  "width=300,height=400,scrollbars=yes" ];

            var url = ajaxaction +"?" + params;

            var windowName = "popUp";//reportico_jquery(this).prop("name");
            var windowSize = windowSizeArray[reportico_jquery(this).prop("rel")];
            window.open(url, windowName, "width=400,height=400").focus();
            reportico_jquery(expandpanel).removeClass("loading");
            reportico_jquery(reportico_container).removeClass("loading");
            window.focus();
        }
        else
        {
            // Download pdf/csv from within current window
            var buttonName = reportico_jquery(this).prop("name");
            var formparams = reportico_jquery(critform).serializeObject();
            formparams['execute_mode'] = 'EXECUTE';
            formparams[buttonName] = '1';
            formparams['reportico_ajax_called'] = '1';
            ajaxFileDownload(ajaxaction, formparams, expandpanel, reportico_container);
        }

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
            showNoticeModal(msg);

        } catch(e) { 
            showNoticeModal(xhr.responseText);
        }
       }
     });
     return false;
   });

/*
 * Shows modal window containing the passed text
 */
function showNoticeModal(content)
{
    reportico_jquery("#reporticoNoticeModalBody").html("");
    if ( reportico_bootstrap_modal )
    {
        reportico_jquery('#reporticoNoticeModal').modal({});
    }
    else
        reportico_jquery("#reporticoNoticeModal").show();
    reportico_jquery("#reporticoNoticeModalBody").html(content);
}

/*
 * Shows modal window containing the passed text from within a child iframe
 */
function showParentNoticeModal(content)
{
    reportico_jquery("#reporticoNoticeModalBody",window.parent.document).html("");
    if ( reportico_bootstrap_modal )
    {
        reportico_jquery('#reporticoNoticeModal',window.parent.document).modal({});
    }
    else
        reportico_jquery("#reporticoNoticeModal",window.parent.document).show();
    reportico_jquery("#reporticoNoticeModalBody",window.parent.document).html(content);
}


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
  setupTooltips();
  setupDropMenu();
  setupDynamicGrids();
  resizeHeaders();
  setupCriteriaItems();
  setupCheckboxes();
  resizeTables();
}

var ie7 = (document.all && !window.opera && window.XMLHttpRequest) ? true : false;

/*
** Shows and hides a block of design items fields
*/
function toggleCriteria(id) {
    if ( reportico_jquery(".displayGroup" + id ).css("display") == "none" )
    {
        reportico_jquery(".displayGroup" + id ).show();
        reportico_jquery("#swToggleCriteria" + id ).html("-");
    }
    else
    {
        reportico_jquery("#swToggleCriteria" + id ).html("+");
        reportico_jquery(".displayGroup" + id ).hide();
    }
} 

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
