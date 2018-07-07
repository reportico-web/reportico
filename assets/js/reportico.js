var reportico_jquery = jQuery.noConflict();


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
        reportico_jquery(".reportico-page").each(function(){
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
    reportico_jquery(".reportico-date-field").each(function(){
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
            },
            beforeShow: function()
            {
                setTimeout(function()
                {
                    reportico_jquery(".ui-datepicker").css("z-index", 999999);
                }, 10); 
            }
            });
        });
}

function setupTooltips()
{
    reportico_jquery(".reportico_tooltip").each(function(){
        reportico_jquery(this).tooltip();
    });
}

function getFilterGroupState()
{
    var openfilters = "";
    var closedfilters = "";
    
    var arr = [];
    reportico_jquery(".reportico-toggleCriteriaDiv").each(function(){
        filterid = reportico_jquery(this).prop("id");
        filterid = filterid.replace("reportico-toggleCriteriaDiv","");
        filterno = filterid;
        filterid = ".displayGroup" + filterid;
        
            
        if ( reportico_jquery(filterid).first().is(":visible") )
        {
            if ( !openfilters )
                openfilters = "&openfilters[]="+filterno;
            else
                openfilters += "&openfilters[]="+filterno;
            
        }
        else
        {
            
            if ( !closedfilters )
                closedfilters = "&closedfilters[]="+filterno;
            else
                closedfilters += "&closedfilters[]="+filterno;
            
        }
    });
    openfilters = closedfilters  + openfilters;
    return openfilters;

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
        
        headers =  getCSRFHeaders();
        
        if ( jQuery.type(reportico_ajax_script) === 'undefined' || !reportico_ajax_script )
        {
            var ajaxaction = reportico_jquery(forms).prop("action");
        }
        else
        {
            ajaxaction = reportico_ajax_script;
        }

        ajaxextra = getYiiAjaxURL();
        if ( ajaxextra != "" ) {
            ajaxaction += ajaxextra
            ajaxaction += "&" + "reportico_criteria=" + j;
        }
        else
            ajaxaction += "?" + "reportico_criteria=" + j;

        ajaxaction +=  getCSRFURLParams();
        headers =  getCSRFHeaders();
        
        reportico_jquery("#select2_dropdown_" + j + ",#select2_dropdown_expanded_" + j).select2({
          ajax: {
            url: ajaxaction,
            headers: headers,
            type: 'POST',
            error: function(data, status) {
                return {
                    results: [{ id: 'error', text: 'Unable to autocomplete', disabled: true }]
                }
            },
            dataType: 'json',
            delay: 250,
            data: function (params) {
                forms = reportico_jquery('#reportico-container').find(".reportico-prepare-form");
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
  var $state = reportico_jquery(
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
** Pagination in HTML
*/
var max_pages = 100;
var page_count = 0;

function paginate() {

    // Set pageno so we can do things just on first page
    pagination_page_no = 1;
    page_count = 0;

    var paged = false;
    reportico_jquery('.autopaginate.original-page').each(function() {
        if ( !reportico_jquery(this).hasClass("already-paginated") )  {
            splitPage.call(this);
            paged = true;
        }
    });
    if ( paged )
        postPagination();

}

/*
** Pass through each page setting page numbers and page counts
** Also flag first page so we can ensure for example
** browser printouts dont throw page before first page
*/
function postPagination() {

      var page_count = 0;
      var page_total = reportico_jquery('.autopaginate.original-page').length;
      reportico_jquery('.reportico-paginated').each(function() {

        page_count++;

        // Dont allow repagination
        reportico_jquery(this).addClass("already-paginated");

        if ( page_count == 1 ) {
            reportico_jquery(this).addClass("first-page");
        } else {
            reportico_jquery(this).addClass("mid-page");
        }

      });

      var page_count = 0;
      reportico_jquery('.reportico-page-number').each(function() {

        page_count++;
        reportico_jquery(this).html(page_count);

      });

      reportico_jquery('.reportico-page-count').each(function() {
        reportico_jquery(this).html(page_total);
      });
}

function splitPage() {

      page_count++;
      if (page_count > max_pages) {
        return;
      }

      var topMargin = reportico_jquery("#reportico-top-margin").outerHeight();
      var bottomMargin = reportico_jquery("#reportico-bottom-margin").outerHeight();


      //alert("margin " + topMargin +" " + bottomMargin );
      var long = reportico_jquery(this)[0].scrollHeight - Math.ceil(reportico_jquery(this).innerHeight());

      // Start pag size off as total of margin we dont want to exceed this
      long = topMargin + bottomMargin;

      var pageheight = Math.ceil(reportico_jquery(this).innerHeight());
      var pageclasses = reportico_jquery(this).attr("class");
      var pagestyles = reportico_jquery(this).attr("style");
      var children = reportico_jquery(this).children().toArray(); // Save elements in this page to children[] array
      var removed = [];
      var thispage = reportico_jquery(this);
      // Loop while this page is longer than an A4 page
      lastlong = long;
      //console.log("============== " + children.length + " =====================");
      ct = 0;

      var pageheaders = false;
      var pagefooters = false;
      var putbackheaders = false;
      var putbackfooters = false;

      while (long > 0 && children.length > 0) {

        var child = children.shift();
        var childheight = reportico_jquery(child).outerHeight();
        var childclass = reportico_jquery(child).prop("className");

        // Clone page title
        if ( reportico_jquery(child).hasClass("reportico-title") ) {
            putbacktitle = reportico_jquery(child).clone();
        }

        // Clone page headers
        if ( reportico_jquery(child).hasClass("reportico-page-header-block") ) {
            putbackheaders = reportico_jquery(child).clone();
        }

        // Extract first footer and remove all others. Then apply this one to all pages
        firstfooter = reportico_jquery(thispage).find(".reportico-page-footer-block:first");
        if ( firstfooter.length > 0 ) {
            putbackfooters = firstfooter.clone();
        }

        // Remove this pages footers as they have been stored in previous statement
        if ( reportico_jquery(child).hasClass("reportico-page-footer-block") ) {
            reportico_jquery(child).remove();
        }


        var nextlong = long +  childheight;

        var newpage = false;
        if ( nextlong > pageheight ) {
            //console.log("ALERT " + childclass + " " + nextlong + " > " + pageheight + "!" );

            var newtable = false;
            //if ( reportico_jquery(child).hasClass("reportico-page-header-block") ) {
                //pageheaders = reportico_jquery(child).clone();
            //}
            if ( reportico_jquery(child).hasClass("reportico-page") ) {

                var th = reportico_jquery(child).find("thead");
                var hf = reportico_jquery(child).find("thead").first();

                //var headers = reportico_jquery(child).find("thead").first().outerHtml();
                var headers = reportico_jquery(child).find("thead").clone();

                var hheight = reportico_jquery(hf).outerHeight();

                var hf = reportico_jquery(child).find("tfoot").first();
                var fheight = reportico_jquery(hf).outerHeight();
                
                var body = reportico_jquery(child).find("tbody");
                var rows = reportico_jquery(child).find("tr");

                var rheight = 0;

                var rlong = long;
                var sliceAt = 0;

                var looping = true;
                reportico_jquery(thispage).find("tr").each(function() {
                    if ( !looping ) 
                        return;
                    rheight = reportico_jquery(this).outerHeight();
                    rlong += rheight;
                    if ( rlong + rheight > pageheight ) {

                        newtable = reportico_jquery('<TABLE class="table table-striped table-condensed  reportico-page"><TBODY></TBODY></TABLE>');
                        reportico_jquery(newtable).find("tbody").append(reportico_jquery(child).find("tbody tr,tfoot tr").slice(0,sliceAt));
                        reportico_jquery(newtable).prepend(headers);
                        /*reportico_jquery(thispage).after(newpage); */
                        //console.log(" ALERT IN " + rlong + " > " + pageheight );
                        looping = false;
                        long = topMargin + bottomMargin;
                    }

                    sliceAt++;

                    /*reportico_jquery(newpage).append(headers);
                    reportico_jquery(thispage).before(newpage);*/
                });

            }

            //newpage = reportico_jquery('<div class="reportico-paginated autopaginate' + ' page-size-' + reportico_page_size + ' page-orientation-' + reportico_page_orientation + ' newPage">' /*+ reportico_page_size + "," + reportico_page_orientation*/ + ' </div>');
            newpage = reportico_jquery('<div class="' + pageclasses + '" style="' + pagestyles + '">' + ' </div>');

            // Copy back in the current page title moved to the new page
            if ( putbacktitle && reportico_page_title_display == "topofallpages" ) {
                reportico_jquery(thispage).prepend(putbacktitle);
            }

            // Copy back in the current page headers moved to the new page
            if ( putbackheaders ) 
                reportico_jquery(thispage).prepend(putbackheaders);

            newpage.append(reportico_jquery(removed));

            // Copy back in the current page footers moved to the new page
            if ( putbackfooters ) {
                var footer = putbackfooters.clone();
                newpage.append(footer);
            }


            if ( newtable ) {
                if ( reportico_jquery(newtable).find("tbody tr").length > 0 ) {
                    reportico_jquery(newpage).append(newtable);
                }
            }

            // Prepend the snipped page before the current but only if there are chidren that were snipped
            // else weve snipped everything form the main page so delete current, this wil be then the
            // last iteration through
            if ( children.length > 0 )
                reportico_jquery(thispage).before(newpage);
            else
                reportico_jquery(thispage).remove();
            children.unshift(child); 

            break;
        }
        else
        {
            reportico_jquery(child).detach();  // JQuery Method detach() removes the "child" element from DOM for the current page
            long = nextlong;
        }
        removed.push(child); 
      }

      if (newpage) {
        pagination_page_no++;
        splitPage.call(thispage); // Recursively call myself to adjust the remainder of the pages
      } else {

        // We are down to the last page. If anything that was not a header or footer was detached from the page we have been snipping
        // then it justifies being put back
        nonbody=0;
        for(j = 0; j < removed.length; j++){
            x = removed[j];
            if ( reportico_jquery(x).hasClass("reportico-page-header-block") || reportico_jquery(x).hasClass("reportico-page-footer-block") ) 
                nonbody++;
        }

        if ( removed.length > nonbody ) {
            // There are report body items
            reportico_jquery(thispage).append(removed);
        } else {
            // No report body items remove the last blank page
            reportico_jquery(thispage).remove();
        }
      }
    }

/*
* Where multiple data tables exist due to graphs
* resize the columns of all tables to match the first
*/
function resizeHeaders()
{
  // Size page header blocks to fit page headers
  reportico_jquery(".reportico-page-header-block").each(function() {
    var parenty = reportico_jquery(this).position().top;
    var maxheight = 0;
    reportico_jquery(this).find(".reportico-page-header").each(function() {
        var headerheight  = reportico_jquery(this).outerHeight();
        reportico_jquery(this).find("img").each(function() {
            var imgheight = reportico_jquery(this).prop("height");
            //if ( imgheight > headerheight )
                //headerheight = imgheight;
        });
        var margintop  = parseInt(reportico_jquery(this).css("margin-top"));
        var marginbottom  = parseInt(reportico_jquery(this).css("margin-bottom"));
        headerheight += margintop + marginbottom;
        if ( headerheight > maxheight )
            maxheight = headerheight;
   });
   reportico_jquery(this).css("height", maxheight + "px");
  });

  // Resize Custom Headers
  reportico_jquery(".reportico-custom-header-block,.reportico-custom-trailer-block").each(function() {
    var parenty = reportico_jquery(this).position().top;
    var maxheight = 0;
    reportico_jquery(this).find("div").each(function() {
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

}

/*
* Where multiple data tables exist due to graphs
* resize the columns of all tables to match the first
*/
function resizeTables()
{

  var tableArr = reportico_jquery('.reportico-page');
  if ( tableArr.length == 0 )
    return;
  var tableDataRow = reportico_jquery('.reportico-row:first');
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

    reportico_jquery(this).find(".reportico-row:first").each(function() {
      for(j = 0; j < reportico_jquery(this)[0].cells.length; j++){
        reportico_jquery(this)[0].cells[j].style.width = cellWidths[j]+'px';
      }
   });
 });
}


reportico_jquery(document).on('click', '.reportico-show-criteria', function(event) 
{
    // On manual paginate add class to trigger pagination
    reportico_jquery(".reportico-show-criteria").hide();
    reportico_jquery(".reportico-hide-criteria").show();
    reportico_jquery("#criteriabody").show();
    return false;
});

reportico_jquery(document).on('click', '.reportico-hide-criteria', function(event) 
{
    // On manual paginate add class to trigger pagination
    reportico_jquery(".reportico-hide-criteria").hide();
    reportico_jquery(".reportico-show-criteria").show();
    reportico_jquery("#criteriabody").hide();
    return false;
});

reportico_jquery(document).on('click', '.reportico-paginate-button-link', function(event) 
{
    // On manual paginate add class to trigger pagination
    reportico_jquery(".reportico-paginated").addClass("autopaginate");
    paginate();
    return false;
});

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
    paginate();
});

function reportico_initialise_page()
{
    setupDatePickers();
    setupTooltips();
    setupDropMenu();
    resizeHeaders();
    resizeTables();
    setupDynamicGrids();
    setupCriteriaItems();
};

reportico_jquery(document).on('click', '.reportico-notice-modal-close,.reportico-notice-modal-button', function(event) 
{
    reportico_jquery("#reportico-edit-link").html("");
    reportico_jquery('#reporticoNoticeModal').hide();
});

reportico_jquery(document).on('click', '.reportico-edit-linkSubmit,.reportico-bootstrap-modal-close,.reportico-modal-close', function(event) 
{
    if ( reportico_bootstrap_modal )
        var loadpanel = reportico_jquery("#reporticoModal .modal-dialog .modal-content .modal-header");
    else
        var loadpanel = reportico_jquery("#reporticoModal .reportico-modal-dialog .reportico-modal-content .reportico-modal-header");

	var expandpanel = reportico_jquery('#reportico-prepare-expandCell');
	var expandpanel = reportico_jquery('.reportico-prepare-crit-output-options');
    reportico_jquery(loadpanel).addClass("modal-loading");

    forms = reportico_jquery(this).closest('#reportico-container').find(".reportico-prepare-form");
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

    ajaxaction += getYiiAjaxURL();
    params +=  getCSRFURLParams();
    headers =  getCSRFHeaders();

    var cont = this;
    reportico_jquery.ajax({
        type: 'POST',
        headers: headers,
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
            reportico_jquery('#reportico-container').closest('body').removeClass('modal-open');

            // Weird behaviour after moda open causes right padding to be added to body - remove it
            reportico_jquery('#reportico-container').closest('body').css('padding-right', "0px");
          }
          else
            reportico_jquery('#reporticoModal').hide();

          reportico_jquery("#reportico-edit-link").html("");

          criteriabodyshowing = reportico_jquery("#criteriabody").is(":visible");

          //reportico_jquery(reportico_container).removeClass("loading");
          fillDialog(data, cont);

          if ( !criteriabodyshowing ) {
            reportico_jquery("#criteriabody").hide();
            reportico_jquery(".reportico-show-criteria").show();
            reportico_jquery(".reportico-hide-criteria").hide();
          }
        },
        error: function(xhr, desc, err) {
          reportico_jquery("#reportico-edit-link").html("");
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
** will generate full reportico output to replace the reportico-container tag
*/

reportico_jquery(document).on('click', '.reportico-edit-link', function(event) 
{
	var expandpanel = reportico_jquery(this).closest('#criteriaform').find('#reportico-prepare-expandCell');
	var expandpanel = reportico_jquery(this).closest('#criteriaform').find('.reportico-prepare-crit-output-options');
    var reportico_container = reportico_jquery(this).closest("#reportico-container");

    reportico_jquery(expandpanel).addClass("loading");
    forms = reportico_jquery(this).closest('.reportico-maintain-form,.reportico-prepare-form,.reportico-prepare-save-form,form');
    if (    reportico_jquery.type(reportico_ajax_script) === 'undefined' )
    {
        var ajaxaction = reportico_jquery(forms).prop("action");
    }
    else
    {
        ajaxaction = reportico_ajax_script;
    }
    
    reportico_jquery(".reportico-modal-title").html(reportico_jquery(this).prop("title")); 
    
    var maintainButton = reportico_jquery(this).prop("name"); 
	var bits = maintainButton.split("_");
	var params = forms.serialize();
    params += "&execute_mode=MAINTAIN&partialMaintain=" + maintainButton + "&partial_template=mini&submit_" + bits[0] + "_SHOW=1";
    params += "&reportico_ajax_called=1";
    

    ajaxaction += getYiiAjaxURL();
    params +=  getCSRFURLParams();
    headers =  getCSRFHeaders();

    reportico_jquery.ajax({
        type: 'POST',
        headers: headers,
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
          reportico_jquery("#reportico-edit-link").html(data);
          x = reportico_jquery(".reportico-maintain-button").prop("name");
          reportico_jquery(".reportico-edit-linkSubmit").prop("id", x);
        },
        error: function(xhr, desc, err) {
          reportico_jquery(expandpanel).removeClass("loading");
          reportico_jquery(reportico_container).removeClass("loading");
          reportico_jquery(expandpanel).prop('innerHTML',"Ajax Error: " + xhr + "\nTextStatus: " + desc + "\nErrorThrown: " + err);
        }
      });

    return false;

})

reportico_jquery(document).on('click', '.reportico-prepare-save-button', function(event) 
{
	var expandpanel = reportico_jquery(this).closest('#criteriaform').find('#reportico-prepare-expandCell');
    var reportico_container = reportico_jquery(this).closest("#reportico-container");

    reportico_jquery(expandpanel).addClass("loading");
    if (    reportico_jquery.type(reportico_ajax_script) === 'undefined' )
    {
        var ajaxaction = reportico_jquery(forms).prop("action");
    }
    else
    {
        ajaxaction = reportico_ajax_script;
    }

    filename = reportico_jquery("#reportico-prepare-save-file").prop("value");
    var forms = reportico_jquery(this).closest('.reportico-maintain-form,.reportico-prepare-form,.reportico-prepare-save-form,form');
    params = forms.serialize();
    params += "&execute_mode=MAINTAIN&submit_xxx_PREPARESAVE=1&errorsInModal=1&xmlout=" + filename;
    params += "&reportico_ajax_called=1";

    ajaxaction += getYiiAjaxURL();
    params +=  getCSRFURLParams();
    headers =  getCSRFHeaders();

    reportico_jquery.ajax({
        type: 'POST',
        headers: headers,
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
** will generate full reportico output to replace the reportico-container tag
*/
reportico_jquery(document).on('click', '.reportico-admin-button, .reportico-admin-button2, .reportico-menu-item-link, .reportico-prepare-submit, .reportico-ajax-link, .reportico-link-menu2, .reportico-submit', function(event) 
{
    if ( reportico_jquery(this).hasClass("reportico-no-submit" )  )
    {
        return false;
    }

    if ( reportico_jquery(this).parents("#reportico-edit-link").length == 1 ) 
    {
	    var expandpanel = reportico_jquery(this).closest('#criteriaform').find('#reportico-prepare-expandCell');
	    var expandpanel = reportico_jquery(this).closest('#criteriaform').find('.reportico-prepare-crit-output-options');
        if ( reportico_bootstrap_modal )
            var loadpanel = reportico_jquery("#reporticoModal .modal-dialog .modal-content .modal-header");
        else
            var loadpanel = reportico_jquery("#reporticoModal .reportico-modal-dialog .reportico-modal-content .reportico-modal-header");
        var reportico_container = reportico_jquery(this).closest("#reportico-container");

        reportico_jquery(loadpanel).addClass("modal-loading");
        forms = reportico_jquery(this).closest('.reportico-edit-link-form');
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

        ajaxaction += getYiiAjaxURL();
        params +=  getCSRFURLParams();
        headers =  getCSRFHeaders();

        reportico_jquery.ajax({
            type: 'POST',
            headers: headers,
            url: ajaxaction,
            data: params,
            dataType: 'html',
            success: function(data, status) 
            {
              reportico_jquery(loadpanel).removeClass("modal-loading");
              if ( reportico_bootstrap_modal )
                setupModals();
              reportico_jquery("#reportico-edit-link").html(data);
              x = reportico_jquery(".reportico-maintain-button").prop("name");
              reportico_jquery(".reportico-edit-linkSubmit").prop("id", x);
            },
            error: function(xhr, desc, err) {
              reportico_jquery(loadpanel).removeClass("modal-loading");
              reportico_jquery(expandpanel).prop('innerHTML',"Ajax Error: " + xhr + "\nTextStatus: " + desc + "\nErrorThrown: " + err);
            }
          });

        return false;
    }

    if ( reportico_jquery(this).parent().hasClass("reportico-print-button" )  )
    {
        //var data = reportico_jquery(this).closest("#reportico-container").html();
        //html_print(data);
        window.print();
        return false;
    }

    if (  reportico_jquery.type(reportico_ajax_mode) === 'undefined' || !reportico_ajax_mode)
    {
        return true;
    }

	var expandpanel = reportico_jquery(this).closest('#criteriaform').find('#reportico-prepare-expandCell');
	var expandpanel = reportico_jquery(this).closest('#criteriaform').find('.reportico-prepare-crit-output-options');
    var reportico_container = reportico_jquery(this).closest("#reportico-container");

    if ( !reportico_jquery(this).prop("href") )
    {
            reportico_jquery(expandpanel).addClass("loading");
            reportico_jquery(reportico_container).addClass("loading");

            forms = reportico_jquery(this).closest('.reportico-maintain-form,.reportico-prepare-form,form');
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

            ajaxaction += getYiiAjaxURL();
            params +=  getCSRFURLParams();
            headers =  getCSRFHeaders();

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
                headers: headers,
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
        runreport(url, this);
    }
    return false;
})

/*
 * Use ajax to return pdf or csv output and download to file.
 * For pdf, output is received in base64. 
 */
function ajaxFileDownload(url, data, expandpanel, reportico_container) {

    url += getYiiAjaxURL();
    url +=  getCSRFURLParams();
    headers =  getCSRFHeaders();

    reportico_jquery.ajax({
      type: 'POST',
      headers: headers,
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
	var expandpanel = reportico_jquery(this).closest('#criteriaform').find('#reportico-prepare-expandCell');
	var expandpanel = reportico_jquery(this).closest('#criteriaform').find('.reportico-prepare-crit-output-options');
    reportico_jquery(expandpanel).addClass("loading");

    var params = reportico_jquery(critform).serialize();
    params += "&execute_mode=PREPARE";
    params += "&partial_template=critbody";
    params += "&" + reportico_jquery(this).prop("name") + "=1";
    params += getFilterGroupState();

    ajaxaction += getYiiAjaxURL();
    params +=  getCSRFURLParams();
    headers =  getCSRFHeaders();

	forms = reportico_jquery(this).closest('.reportico-maintain-form,.reportico-prepare-form,form');
    ajaxaction = reportico_ajax_script;

	fillPoint = reportico_jquery(this).closest('#criteriaform').find('#criteriabody');
		
    reportico_jquery.ajax({
      type: 'POST',
      headers: headers,
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

	forms = reportico_jquery(this).closest('.reportico-maintain-form,.reportico-prepare-form,form');
	var ajaxaction = reportico_jquery(forms).prop("action");
	var critform = reportico_jquery(this).closest('#criteriaform');
    ajaxaction = reportico_ajax_script;

    var params = reportico_jquery(critform).serialize();
    params += "&execute_mode=PREPARE";
    params += "&partial_template=expand";
    params += "&" + reportico_jquery(this).prop("name") + "=1";
    params += getFilterGroupState();

	var fillPoint = reportico_jquery(this).closest('#criteriaform').find('#reportico-prepare-expandCell');
    reportico_jquery(fillPoint).addClass("loading");

    ajaxaction += getYiiAjaxURL();
    params +=  getCSRFURLParams();
    headers =  getCSRFHeaders();


    reportico_jquery.ajax({
        type: 'POST',
        headers: headers,
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
reportico_jquery(document).on('click', '.reportico-print-box,.prepareAjaxExecute,#prepareAjaxExecute', function() {


    var reportico_container = reportico_jquery(this).closest("#reportico-container");
    reportico_jquery(reportico_container).find("#rpt_format_pdf").prop("checked", false );
    reportico_jquery(reportico_container).find("#rpt_format_csv").prop("checked", false );
    reportico_jquery(reportico_container).find("#rpt_format_html").prop("checked", false );
    reportico_jquery(reportico_container).find("#rpt_format_json").prop("checked", false );
    reportico_jquery(reportico_container).find("#rpt_format_xml").prop("checked", false );
    if (  reportico_jquery(this).hasClass("reportico-pdf-box") ) 
        reportico_jquery(reportico_container).find("#rpt_format_pdf").prop("checked", "checked");
    if (  reportico_jquery(this).hasClass("reportico-csv-box") ) 
        reportico_jquery(reportico_container).find("#rpt_format_csv").prop("checked", "checked");
    if (  reportico_jquery(this).hasClass("reportico-html-box") ) 
        reportico_jquery(reportico_container).find("#rpt_format_html").prop("checked", "checked");
    if (  reportico_jquery(this).hasClass("reportico-html-go-box") ) 
        reportico_jquery(reportico_container).find("#rpt_format_html").prop("checked", "checked");
    if (  reportico_jquery(this).hasClass("reportico-xml-box") ) 
        reportico_jquery(reportico_container).find("#rpt_format_xml").prop("checked", "checked");
    if (  reportico_jquery(this).hasClass("reportico-json-box") ) 
        reportico_jquery(reportico_container).find("#rpt_format_json").prop("checked", "checked");
    if (  reportico_jquery(this).hasClass("reportico-print-box") ) 
        reportico_jquery(reportico_container).find("#rpt_format_html").prop("checked", "checked");

    if (  !reportico_jquery(this).hasClass("reportico-print-box") )
    if (  reportico_jquery.type(reportico_ajax_mode) === 'undefined' || !reportico_ajax_mode)
    {
        return true;
    }


	var expandpanel = reportico_jquery(this).closest('#criteriaform').find('#reportico-prepare-expandCell');
	var expandpanel = reportico_jquery(this).closest('#criteriaform').find('.reportico-prepare-crit-output-options');
	var critform = reportico_jquery(this).closest('#criteriaform');
    reportico_jquery(expandpanel).addClass("loading");

    params = reportico_jquery(critform).serialize();
    params += "&execute_mode=EXECUTE";
    params += "&" + reportico_jquery(this).prop("name") + "=1";

    forms = reportico_jquery(this).closest('.reportico-maintain-form,.reportico-prepare-form,form');
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

    reportico_report_title = reportico_jquery(this).closest('#reportico-container').find('.reportico-title-bar').html();

    if (  !reportico_jquery(this).hasClass("reportico-print-box") )
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

    if (  reportico_jquery(this).hasClass("reportico-print-box") )
    {
        htmloutput = true;
    }

    if ( !htmloutput )
        params += "&reportico_ajax_called=1";

    if (  reportico_jquery(this).hasClass("reportico-print-box") )
        params += "&printable_html=1&new_reportico_window=1";

    ajaxaction += getYiiAjaxURL();
    params +=  getCSRFURLParams();
    headers =  getCSRFHeaders();


    var cont = this;
    reportico_jquery.ajax({
        type: 'POST',
        headers: headers,
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
            fillReportOutputArea(data);
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
    params +=  getCSRFURLParams();
    headers =  getCSRFHeaders();

    url += "&reportico_template=";
    url += "&reportico_ajax_called=1";
    reportico_jquery(container).closest("#reportico-container").addClass("loading");
    reportico_jquery.ajax({
        type: "POST",
        contentType: "application/json; charset=utf-8",
        headers: headers,
        data: params,
        url: url,
        dataType: "html",
        error: function(XMLHttpRequest, textStatus, errorThrown) {
            alert ("Ajax Error: " + XMLHttpRequest.responseText + "\nTextStatus: " + textStatus + "\nErrorThrown: " + errorThrown);
        },
        success: function(data, status) {
            reportico_jquery(container).closest("#reportico-container").removeClass("loading");
            fillDialog(data,container);
        }
    });
}

function fillReportOutputArea(results) {

  reportico_jquery(".reportico-show-criteria").show();
  reportico_jquery(".reportico-hide-criteria").hide();
  reportico_jquery("#criteriabody").hide();
  reportico_jquery("#reportico-report-output").html(results);
  reportico_jquery(".reportico-back-button").hide();
  resizeTables();
  resizeHeaders();
  paginate();
}

function fillDialog(results, cont) {
  x = reportico_jquery(cont).closest("#reportico-container");
  reportico_jquery(cont).closest("#reportico-container").replaceWith(results);
  setupDatePickers();
  setupTooltips();
  setupDropMenu();
  setupDynamicGrids();
  resizeHeaders();
  setupCriteriaItems();
  setupCheckboxes();
  resizeTables();

  paginate();
}

var ie7 = (document.all && !window.opera && window.XMLHttpRequest) ? true : false;

/*
** Shows and hides a block of design items fields
*/
function toggleCriteria(id) {
    if ( reportico_jquery(".displayGroup" + id ).css("display") == "none" )
    {
        reportico_jquery(".displayGroup" + id ).show();
        reportico_jquery("#reportico-toggleCriteria" + id ).html("-");
    }
    else
    {
        reportico_jquery("#reportico-toggleCriteria" + id ).html("+");
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


// For laravel HTTP headers need to include a X-CSRF-TOKEN field to place the CSRF-TOKEN
function getCSRFHeaders() {

    headers = false;
    if (typeof reportico_csrf_token != 'undefined' && reportico_ajax_mode == "laravel" ) {
        headers =  { 'X-CSRF-TOKEN': reportico_csrf_token };
    }

    return headers;
}

// For Yii with nonclean URLs add the reportico ajax call as a separate URP Param
// For Yii with clean URLS the ajax call is implied in the main url
function getCSRFURLParams() {

    params = "";
    if (typeof reportico_csrf_token != 'undefined' 
        && ( reportico_ajax_mode == "yii-ugly-url" || reportico_ajax_mode == "yii-pretty-url" ) ) {
        params = "&YII_CSRF_TOKEN=" + reportico_csrf_token;
    }

    return params;
}

// For Yii with nonclean URLs add the reportico ajax call as a separate URP Param
// For Yii with clean URLS the ajax call is implied in the main url
function getYiiAjaxURL() {

    ajaxaction = "";
    if ( reportico_ajax_mode == "yii-ugly-url" )
        ajaxaction = "?r=reportico/reportico/ajax";

    return ajaxaction;
}
