{% autoescape false %}
{% if not REPORTICO_AJAX_CALLED %}
{% if not EMBEDDED_REPORT %}
<!DOCTYPE html>
<HTML>
<HEAD>
<TITLE>{{ TITLE|raw }}</TITLE>
{{ OUTPUT_ENCODING|raw }}
</HEAD>
{% if REPORT_PAGE_STYLE %}
{% if REPORTICO_STANDALONE_WINDOW %}
<BODY class="reportico-body reportico-bodyStandalone" {{ REPORT_PAGE_STYLE }};">
{% else %}
<BODY class="reportico-body">
{% endif %}
{% else %}
{% if REPORTICO_STANDALONE_WINDOW %}
<BODY class="reportico-body reportico-bodyStandalone">
{% else %}
<BODY class="reportico-body">
{% endif %}
{% endif %}

    <LINK id="bootstrap_css" REL="stylesheet" TYPE="text/css" HREF="{{ ASSETS_PATH }}/js/bootstrap3/css/bootstrap.min.css">

<LINK id="PRP_StyleSheet" REL="stylesheet" TYPE="text/css" HREF="{{ THEME_DIR }}/css/reportico.css">
{% else %}
    {% if not REPORTICO_BOOTSTRAP_PRELOADED %}
        <LINK id="bootstrap_css" REL="stylesheet" TYPE="text/css" HREF="{{ ASSETS_PATH }}/js/bootstrap3/css/bootstrap.min.css">
    {% endif %}
    <LINK id="PRP_StyleSheet" REL="stylesheet" TYPE="text/css" HREF="{{ THEME_DIR }}/css/reportico.css">
{% endif %}
{% if AJAX_ENABLED %}
{% if not REPORTICO_AJAX_PRELOADED %}
{% if not REPORTICO_JQUERY_PRELOADED or REPORTICO_STANDALONE_WINDOW %}

<script type="text/javascript" src="{{ ASSETS_PATH }}/js/jquery.js"></script>

{% endif %}

<script type="text/javascript" src="{{ ASSETS_PATH }}/js/ui/jquery-ui.js"></script>


<script type="text/javascript" src="{{ ASSETS_PATH }}/js/download.js"></script>
<script type="text/javascript" src="{{ ASSETS_PATH }}/js/reportico.js"></script>

{% endif %}
{% if REPORTICO_CSRF_TOKEN %}
<script type="text/javascript">var reportico_csrf_token = "{{ REPORTICO_CSRF_TOKEN }}";</script>
<script type="text/javascript">var ajax_event_handler = "{{ REPORTICO_AJAX_HANDLER }}";</script>
{% endif %}
{% if BOOTSTRAP_STYLES %}
{% if not REPORTICO_BOOTSTRAP_PRELOADED %}
<script type="text/javascript" src="{{ ASSETS_PATH }}/js/bootstrap3/js/bootstrap.min.js"></script>
{% endif %}
{% endif %}
{% endif %}
{% if not REPORTICO_AJAX_PRELOADED %}

<script type="text/javascript" src="{{ ASSETS_PATH }}/js/ui/i18n/jquery.ui.datepicker-{{ AJAX_DATEPICKER_LANGUAGE }}.js"></script>

{% endif %}
{% if not BOOTSTRAP_STYLES %}

<script type="text/javascript" src="{{ ASSETS_PATH }}/js/jquery.jdMenu.js"></script>
<LINK id="reportico_css" REL="stylesheet" TYPE="text/css" HREF="{{ ASSETS_PATH }}/js/jquery.jdMenu.css">

{% endif %}

<LINK id="reportico_css" REL="stylesheet" TYPE="text/css" HREF="{{ ASSETS_PATH }}/js/ui/jquery-ui.css">
<script type="text/javascript">var reportico_datepicker_language = "{{ AJAX_DATEPICKER_FORMAT }}";</script>
<script type="text/javascript">var reportico_this_script = "{{ SCRIPT_SELF }}";</script>
<script type="text/javascript">var reportico_ajax_script = "{{ REPORTICO_AJAX_RUNNER }}";</script>
<script type="text/javascript">var pdf_delivery_mode = "{{ PDF_DELIVERY_MODE }}";</script>


{% if REPORTICO_BOOTSTRAP_MODAL %}
<script type="text/javascript">var reportico_bootstrap_styles = "{{ BOOTSTRAP_STYLES }}";</script>
<script type="text/javascript">var reportico_bootstrap_modal = true;</script>
{% else %}
<script type="text/javascript">var reportico_bootstrap_modal = false;</script>
<script type="text/javascript">var reportico_bootstrap_styles = false;</script>
{% endif %}

<script type="text/javascript">var reportico_ajax_mode = "{{ REPORTICO_AJAX_MODE }}";</script>
<script type="text/javascript">var reportico_report_title = "{{ TITLE }}";</script>
<script type="text/javascript">var reportico_css_path = "{{ THEME_DIR }}/css/reportico.css";</script>

{% endif %}
{% if REPORTICO_CHARTING_ENGINE == "FLOT"  %}

<script type="text/javascript" src="{{ ASSETS_PATH }}/js/flot/jquery.flot.js"></script>
<script type="text/javascript" src="{{ ASSETS_PATH }}/js/flot/jquery.flot.axislabels.js"></script>

{% endif %}
{% if REPORTICO_CHARTING_ENGINE == "NVD3"  %}
{% if not REPORTICO_AJAX_PRELOADED %}

<script type="text/javascript" src="{{ ASSETS_PATH }}/js/nvd3/d3.min.js"></script>
<script type="text/javascript" src="{{ ASSETS_PATH }}/js/nvd3/nv.d3.js"></script>
<LINK id="nvd3_css" REL="stylesheet" TYPE="text/css" HREF="{{ ASSETS_PATH }}/js/nvd3/nv.d3.css">

{% endif %}
{% endif %}
{% if not REPORTICO_AJAX_PRELOADED %}

<script type="text/javascript" src="{{ ASSETS_PATH }}/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="{{ ASSETS_PATH }}/js/select2/js/select2.min.js"></script>

<LINK id="PRP_StyleSheet_s2" REL="stylesheet" TYPE="text/css" HREF="{{ ASSETS_PATH }}/js/select2/css/select2.min.css">
<LINK id="datatable_css" REL="stylesheet" TYPE="text/css" HREF="{{ STYLESHEETDIR }}/jquery.dataTables.css">
{% endif %}
{% if PRINTABLE_HTML %}

<script type="text/javascript" src="{{ ASSETS_PATH }}/js/reportico.js"></script>


{% endif %}

{% if not REPORTICO_AJAX_CALLED %}
<div id="reportico-container">
{% endif %}

<div id="reportico-top-margin" style="z-index: 1; display: none;left: 80px;float: left; border: solid; height: {{ PAGE_TOP_MARGIN }}; width: {{ PAGE_LEFT_MARGIN }};">t</div>
<div id="reportico-bottom-margin" style="z-index: 1; display: none;left: 160;float: right; border: solid; height: {{ PAGE_BOTTOM_MARGIN }}; width: {{ PAGE_RIGHT_MARGIN }};">b</div>

<div style="{{ CONTENT.styles.body }}" class="reportico-output {{ PRINT_FORMAT }}">

    <script>
        reportico_criteria_items = [];
{% if CRITERIA_ITEMS is defined %}
{% for critno in CRITERIA_ITEMS %}
        reportico_criteria_items.push("{{CRITERIA_ITEMS[critno].name}}");
{% endfor %}
{% endif %}
    </script>
<div class="reportico-report-form">
{% if ERRORMSG|length>0 %}
            <div id="reporticoEmbeddedError">
                {{ ERRORMSG|raw }}
            </div>

            <script>
                reportico_jquery(document).ready(function()
                {
                    showParentNoticeModal(reportico_jquery("#reporticoEmbeddedError").html());
                });
            </script>

            <TABLE class="reportico-error-box">
                <TR>
                    <TD>{{ ERRORMSG|raw }}</TD>
                </TR>
            </TABLE>
{% endif %}
{% if STATUSMSG|length>0 %} 
			<TABLE class="reportico-status-block">
				<TR>
					<TD>{{ STATUSMSG|raw }}</TD>
				</TR>
			</TABLE>
{% endif %}
{% if SHOW_LOGIN %}
			<TD width="10%"></TD>
			<TD width="55%" align="left" class="reportico-prepare-top-menuCell">
{% if PROJ_PASSWORD_ERROR|length > 0 %}
                                <div style="color: #ff0000;">{{ PASSWORD_ERROR }}</div>
{% endif %}
				Enter the report project password. <br><input type="password" name="project_password" value=""></div>
				<input class="reportico-ajax-link" type="submit" name="login" value="Login">
			</TD>
{% endif %}
{% if REPORTICO_DYNAMIC_GRIDS %}
<script type="text/javascript">var reportico_dynamic_grids = true;</script>
{% if REPORTICO_DYNAMIC_GRIDS_SORTABLE %}
<script type="text/javascript">var reportico_dynamic_grids_sortable = true;</script>
{% else %}
<script type="text/javascript">var reportico_dynamic_grids_sortable = false;</script>
{% endif %}
{% if REPORTICO_DYNAMIC_GRIDS_SEARCHABLE %}
<script type="text/javascript">var reportico_dynamic_grids_searchable = true;</script>
{% else %}
<script type="text/javascript">var reportico_dynamic_grids_searchable = false;</script>
{% endif %}
{% if REPORTICO_DYNAMIC_GRIDS_PAGING %}
<script type="text/javascript">var reportico_dynamic_grids_paging = true;</script>
{% else %}
<script type="text/javascript">var reportico_dynamic_grids_paging = false;</script>
{% endif %}
<script type="text/javascript">var reportico_dynamic_grids_page_size = {{ REPORTICO_DYNAMIC_GRIDS_PAGE_SIZE }};</script>
{% else %}
<script type="text/javascript">var reportico_dynamic_grids = false;</script>
{% endif %}

<div class="reportico-output-button-block">
{# Navigation Buttons #}
{% for button in CONTENT.buttons %}
{% if button.linkClass is defined %}
    <div class="{{ button.class }}"><a class="{{ button.linkClass }}" href="{{ button.href }}" title="{{ button.title }}">&nbsp;</a></div>
{% else %}
    <div class="{{ button.class }}"><a class="reportico-ajax-link" href="{{ button.href }}" title="{{ button.title }}">&nbsp;</a></div>
{% endif %}

{% endfor %}
</div>

{% if PAGE_LAYOUT == "FORM" %}
    {% include 'execute-content-form.inc.tpl' %}
{% else %}
    {% include 'execute-content.inc.tpl' %}
{% endif %}

{% if not REPORTICO_AJAX_CALLED %}
</div>
{% endif %}
</div>
{% if not REPORTICO_AJAX_CALLED %}
{% if not EMBEDDED_REPORT %}
</BODY>
</HTML>
{% endif %}
{% endif %}

{% if REPORTICO_BOOTSTRAP_MODAL %}
{% if BOOTSTRAP_STYLES == "3"  %}
<div class="modal fade" id="reporticoNoticeModal" tabindex="-1" role="dialog" aria-labelledby="reporticoNoticeModal" aria-hidden="true">
    <div class="modal-dialog modal-lg">
{% else %}
<div class="modal fade" style="width: 500px; margin-left: -450px" id="reporticoNoticeModal" tabindex="-1" role="dialog" aria-labelledby="reporticoModal" aria-hidden="true">
    <div class="modal-dialog">
{% endif %}
        <div class="modal-content">
            <div class="modal-header">
            <button type="button" data-dismiss="modal" class="close" aria-hidden="true">&times;</button>
            <h4 class="modal-title reportico-modal-title" id="reporticoNoticeModalLabel">{{ T_NOTICE }}</h4>
            </div>
            <div class="modal-body" style="padding: 0px" id="reporticoNoticeModalBody">
                <h3>Modal Body</h3>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
                <!--button type="button" class="btn btn-primary" >Close</button-->
        </div>
    </div>
  </div>
</div>
{% else %}
<div id="reporticoModal" tabindex="-1" class="reportico-modal">
    <div class="reportico-modal-dialog">
        <div class="reportico-modal-content">
            <div class="reportico-modal-header">
            <button type="button" class="reportico-modal-close">&times;</button>
            <h4 class="reportico-modal-title" id="reporticoModalLabel">Set Parameter</h4>
            </div>
            <div class="reportico-modal-body" style="padding: 0px" id="reportico-edit-link">
                <h3>Modal Body</h3>
            </div>
            <div class="reportico-modal-footer">
                <!--button type="button" class="btn btn-default" data-dismiss="modal">Close</button-->
                <button type="button" class="reportico-edit-linkSubmit" >Close</button>
        </div>
    </div>
  </div>
</div>
{% endif %}
{% endautoescape %}
