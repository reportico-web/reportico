{if !$REPORTICO_AJAX_CALLED}
{if !$EMBEDDED_REPORT}
<!DOCTYPE html>
<HTML>
<HEAD>
<TITLE>{$TITLE}</TITLE>
<LINK id="reportico_css" REL="stylesheet" TYPE="text/css" HREF="{$THEME_DIR}/css/reportico_bootstrap.css">
<LINK id="bootstrap_css" REL="stylesheet" TYPE="text/css" HREF="{$ASSETS_PATH}/js/bootstrap3/css/bootstrap.min.css">
{$OUTPUT_ENCODING}
</HEAD>
<BODY class="swMenuBody">
{else}
<LINK id="reportico_css" REL="stylesheet" TYPE="text/css" HREF="{$THEME_DIR}/css/reportico_bootstrap.css">
{if !$REPORTICO_BOOTSTRAP_PRELOADED}
<LINK id="bootstrap_css" REL="stylesheet" TYPE="text/css" HREF="{$ASSETS_PATH}/js/bootstrap3/css/bootstrap.min.css">
{/if}
{/if}
{if $AJAX_ENABLED}
{if !$REPORTICO_AJAX_PRELOADED}
{if !$REPORTICO_JQUERY_PRELOADED}
{literal}
<script type="text/javascript" src="{/literal}{$ASSETS_PATH}/js{literal}/jquery.js"></script>
{/literal}
{/if}
{literal}
<script type="text/javascript" src="{/literal}{$ASSETS_PATH}/js{literal}/ui/jquery-ui.js"></script>
<script type="text/javascript" src="{/literal}{$ASSETS_PATH}/js{literal}/download.js"></script>
<script type="text/javascript" src="{/literal}{$ASSETS_PATH}/js{literal}/reportico.js"></script>
{/literal}
{/if}
{if $REPORTICO_CSRF_TOKEN}
<script type="text/javascript">var reportico_csrf_token = "{$REPORTICO_CSRF_TOKEN}";</script>
{/if}
{if !$REPORTICO_BOOTSTRAP_PRELOADED}
<script type="text/javascript" src="{$ASSETS_PATH}/js/bootstrap3/js/bootstrap.min.js"></script>
{/if}
{/if}
{if !$REPORTICO_AJAX_PRELOADED}
{literal}
<script type="text/javascript" src="{/literal}{$ASSETS_PATH}/js{literal}/ui/i18n/jquery.ui.datepicker-{/literal}{$AJAX_DATEPICKER_LANGUAGE}{literal}.js"></script>
{/literal}
{/if}
{literal}
<LINK id="reportico_css" REL="stylesheet" TYPE="text/css" HREF="{/literal}{$ASSETS_PATH}/js{literal}/ui/jquery-ui.css">
<script type="text/javascript">var reportico_datepicker_language = "{/literal}{$AJAX_DATEPICKER_FORMAT}{literal}";</script>
<script type="text/javascript">var reportico_this_script = "{/literal}{$SCRIPT_SELF}{literal}";</script>
<script type="text/javascript">var reportico_ajax_script = "{/literal}{$REPORTICO_AJAX_RUNNER}{literal}";</script>
{/literal}
{if $REPORTICO_BOOTSTRAP_MODAL}
<script type="text/javascript">var reportico_bootstrap_styles = "{$BOOTSTRAP_STYLES}";</script>
<script type="text/javascript">var reportico_bootstrap_modal = true;</script>
{else}
<script type="text/javascript">var reportico_bootstrap_modal = false;</script>
<script type="text/javascript">var reportico_bootstrap_styles = false;</script>
{/if}
{literal}
<script type="text/javascript">var reportico_ajax_mode = "{$REPORTICO_AJAX_MODE}";</script>
{/literal}
{if $REPORTICO_DYNAMIC_GRIDS}
<script type="text/javascript">var reportico_dynamic_grids = true;</script>
{if $REPORTICO_DYNAMIC_GRIDS_SORTABLE}
<script type="text/javascript">var reportico_dynamic_grids_sortable = true;</script>
{else}
<script type="text/javascript">var reportico_dynamic_grids_sortable = false;</script>
{/if}
{if $REPORTICO_DYNAMIC_GRIDS_SEARCHABLE}
<script type="text/javascript">var reportico_dynamic_grids_searchable = true;</script>
{else}
<script type="text/javascript">var reportico_dynamic_grids_searchable = false;</script>
{/if}
{if $REPORTICO_DYNAMIC_GRIDS_PAGING}
<script type="text/javascript">var reportico_dynamic_grids_paging = true;</script>
{else}
<script type="text/javascript">var reportico_dynamic_grids_paging = false;</script>
{/if}
<script type="text/javascript">var reportico_dynamic_grids_page_size = {$REPORTICO_DYNAMIC_GRIDS_PAGE_SIZE};</script>
{else}
<script type="text/javascript">var reportico_dynamic_grids = false;</script>
{/if}
{/if}
{if !$REPORTICO_AJAX_PRELOADED}
{literal}
<script type="text/javascript" src="{/literal}{$ASSETS_PATH}/js{literal}/select2/js/select2.min.js"></script>
<script type="text/javascript" src="{/literal}{$ASSETS_PATH}/js{literal}/jquery.dataTables.js"></script>
{/literal}
<LINK id="StyleSheet_s2" REL="stylesheet" TYPE="text/css" HREF="{$ASSETS_PATH}/js/select2/css/select2.min.css">
<LINK id="StyleSheet_dt" REL="stylesheet" TYPE="text/css" HREF="{$STYLESHEETDIR}/jquery.dataTables.css">
{/if}
{if $REPORTICO_CHARTING_ENGINE == "NVD3" }
{if !$REPORTICO_AJAX_PRELOADED}
{literal}
<script type="text/javascript" src="{/literal}{$ASSETS_PATH}/js{literal}/nvd3/d3.min.js"></script>
<script type="text/javascript" src="{/literal}{$ASSETS_PATH}/js{literal}/nvd3/nv.d3.js"></script>
<LINK id="bootstrap_css" REL="stylesheet" TYPE="text/css" HREF="{/literal}{$ASSETS_PATH}/js{literal}/nvd3/nv.d3.css">
{/literal}
{/if}
{/if}
    <script>
        reportico_criteria_items = [];
{if isset($CRITERIA_ITEMS)}
{section name=critno loop=$CRITERIA_ITEMS}
        reportico_criteria_items.push("{$CRITERIA_ITEMS[critno].name}");
{/section}
{/if}
    </script>
{if isset($PDF_DELIVERY_MODE)}
<script type="text/javascript">var reportico_pdf_delivery_mode = "{$PDF_DELIVERY_MODE}";</script>
{/if}
