<?php
namespace Reportico\Engine;

/*


 * File:        swpanel.php
 *
 * This module provides functionality for reading and writing
 * xml reporting.
 * It also controls browser output through Twig templating class
 * for the different report modes MENU, PREPARE, DESIGN and
 * EXECUTE
 *
 * @link http://www.reportico.org/
 * @copyright 2010-2014 Peter Deed
 * @author Peter Deed <info@reportico.org>
 * @package Reportico
 * @version $Id: swpanel.php,v 1.40 2014/05/17 15:12:32 peter Exp $
 */

/* $Id $ */

/**
 * Class DesignPanel
 *
 * Class for storing the hierarchy of content that will be
 * displayed through the browser when running Reportico
 *
 */
class DesignPanel
{
    public $panel_type;
    public $query = null;
    public $visible = false;
    public $pre_text = "";
    public $body_text = "";
    public $post_text = "";
    public $full_text = "";
    public $program = "";
    public $panels = array();
    public $template = false;
    public $reportlink_report = false;
    public $reportlink_report_item = false;

    public function __construct(&$in_query, $in_type)
    {
        $this->query = &$in_query;
        $this->panel_type = $in_type;
    }

    public function setTemplate(&$in_template)
    {
        $this->template = &$in_template;
    }

    public function setMenuItem($in_program, $in_text)
    {
        // Dont specify xml extensions in menu options
        $in_program = preg_replace("/\.xml\$/", "", $in_program);

        $this->program = $in_program;
        $this->text = $in_text;

        $cp = new DesignPanel($this->query, "MENUITEM");
        $cp->visible = true;
        $this->panels[] = &$cp;
        $cp->program = $in_program;
        $cp->text = $in_text;

    }

    public function setProjectItem($in_program, $in_text)
    {
        $this->program = $in_program;
        $this->text = $in_text;

        $cp = new DesignPanel($this->query, "PROJECTITEM");
        $cp->visible = true;
        $this->panels[] = &$cp;
        $cp->program = $in_program;
        $cp->text = $in_text;

    }

    public function setVisibility($in_visibility)
    {
        $this->visible = $in_visibility;
    }

    public function addPanel(&$in_panel)
    {
        $in_panel->setTemplate($this->template);
        $this->panels[] = &$in_panel;
    }

    public function drawTemplate($send_to_browser = false)
    {
        $text = "";
        if (!$this->visible) {
            return;
        }

        $this->pre_text = $this->preDrawTemplate();

        // Now draw any panels owned by this panel
        foreach ($this->panels as $k => $panel) {
            $panelref = &$this->panels[$k];
            $this->body_text .= $panelref->drawTemplate();
        }

        $this->post_text = $this->postDrawTemplate();
        $this->full_text = $this->pre_text . $this->body_text . $this->post_text;
        return $this->full_text;
    }

    public function preDrawTemplate()
    {
	$sessionClass = ReporticoSession();

        $text = "";
        switch ($this->panel_type) {
            case "LOGIN":
                if (ReporticoApp::getConfig('admin_password') == "__OPENACCESS__") {
                    $this->template->assign('SHOW_OPEN_LOGIN', true);
                } else {
                    $this->template->assign('SHOW_LOGIN', true);
                    $this->template->assign('SHOW_OPEN_LOGIN', false);
                }
                break;

            case "LOGOUT":
                if (!ReporticoApp::getConfig("db_connect_from_config")) {
                    $this->template->assign('SHOW_LOGOUT', true);
                }
                break;

            case "MAINTAIN":
                $text .= $this->query->xmlin->xml2html($this->query->xmlin->data);
                break;

            case "BODY":
                $this->template->assign('EMBEDDED_REPORT', $this->query->embedded_report);
                break;

            case "MAIN":
                break;

            case "TITLE":

                // For Admin options title should be translatable
                // Also for configureproject.xml
                if ($this->query->xmlinput == "configureproject.xml" || ReporticoApp::getConfig("project") == "admin") {
                    $reporttitle = $this->query->deriveAttribute("ReportTitle", "Set Report Title");
                    $this->template->assign('TITLE', ReporticoLang::translate($reporttitle));
                } else {
                    $reporttitle = ReporticoLang::translate($this->query->deriveAttribute("ReportTitle", "Set Report Title"));
                    $this->template->assign('TITLE', $reporttitle);
                }

                $submit_self = $this->query->getActionUrl();
                $forward = $sessionClass::sessionRequestItem('forward_url_get_parameters', '');
                if ($forward) {
                    $submit_self .= "?" . $forward;
                }

                $this->template->assign('SCRIPT_SELF', $submit_self);
                break;

            case "CRITERIA":
                $this->template->assign('SHOW_CRITERIA', true);
                break;

            case "CRITERIA_FORM":
                $dispcrit = array();
                $ct = 0;
                // Build Select Column List
                $this->query->expand_col = false;
                $lastdisplaygroup = "";
                foreach ($this->query->lookup_queries as $k => $col) {
                    if ($col->criteria_type) {
                        if (array_key_exists("EXPAND_" . $col->query_name, $_REQUEST)) {
                            $this->query->expand_col = &$this->query->lookup_queries[$col->query_name];
                        }

                        if (array_key_exists("EXPANDCLEAR_" . $col->query_name, $_REQUEST)) {
                            $this->query->expand_col = &$this->query->lookup_queries[$col->query_name];
                        }

                        if (array_key_exists("EXPANDSELECTALL_" . $col->query_name, $_REQUEST)) {
                            $this->query->expand_col = &$this->query->lookup_queries[$col->query_name];
                        }

                        if (array_key_exists("EXPANDSEARCH_" . $col->query_name, $_REQUEST)) {
                            $this->query->expand_col = &$this->query->lookup_queries[$col->query_name];
                        }

                        $crititle = "";
                        if ($tooltip = $col->deriveAttribute("tooltip", false)) {
                            $title = $col->deriveAttribute("column_title", $col->query_name);
                            $crittitle = '<a HREF="" onMouseOver="return overlib(\'' . $tooltip .
                            '\',STICKY,CAPTION,\'' . $title .
                            '\',DELAY,400);" onMouseOut="nd();" onclick="return false;">' .
                            $title . '</A>';
                        } else {
                            $crittitle = $col->deriveAttribute("column_title", $col->query_name);
                        }

                        $critsel = $col->formatFormColumn();
                        if ($col->hidden == "yes") {
                            $crithidden = true;
                        } else {
                            $crithidden = false;
                        }

                        $critdisplaygroup = $col->display_group;
                        if ($col->required == "yes") {
                            $critrequired = true;
                        } else {
                            $critrequired = false;
                        }

                        $critexp = false;

                        if ($col->expand_display && $col->expand_display != "NOINPUT") {
                            $critexp = true;
                        }

                        $openfilters = preg_replace("/ /", "_", $sessionClass::getReporticoSessionParam("openfilters"));
                        $closedfilters = $sessionClass::getReporticoSessionParam("closedfilters");
                        //echo "Look for $critdisplaygroup!!!!!!!!!!!!!!!!!! in $openfilters<BR>";
                        //var_dump($openfilters);
                        //var_dump($closedfilters);
                        //echo "!!!!!!!!!!!!!!!!!!1Filters<BR>";
                        $visible = false;
                        if ($openfilters) {
                            if (in_array(preg_replace("/ /", "_", $critdisplaygroup), $openfilters)) {
                                $visible = true;
                            }
                        }

                        $dispcrit[] = array(
                            "name" => $col->query_name,
                            "title" => ReporticoLang::translate($crittitle),
                            "entry" => $critsel,
                            "entry" => $critsel,
                            "hidden" => $crithidden,
                            "last_display_group" => $lastdisplaygroup,
                            "display_group" => $critdisplaygroup,
                            "display_group_class" => preg_replace("/ /", "_", $critdisplaygroup),
                            "required" => $critrequired,
                            "visible" => $visible,
                            "expand" => $critexp,
                            "tooltip" => ReporticoLang::translate($col->criteria_help),
                        );
                        $lastdisplaygroup = $critdisplaygroup;
                    }
                    $this->template->assign("CRITERIA_ITEMS", $dispcrit);
                }
                break;

            case "CRITERIA_EXPAND":
                // Expand Cell Table
                $this->template->assign("SHOW_EXPANDED", false);

                if ($this->query->expand_col) {
                    $this->template->assign("SHOW_EXPANDED", true);
                    $this->template->assign("EXPANDED_ITEM", $this->query->expand_col->query_name);
                    $this->template->assign("EXPANDED_SEARCH_VALUE", false);
                    $title = $this->query->expand_col->deriveAttribute("column_title", $this->query->expand_col->query_name);
                    $this->template->assign("EXPANDED_TITLE", ReporticoLang::translate($title));

                    // Only use then expand value if Search was press
                    $expval = "";
                    if ($this->query->expand_col->submitted('MANUAL_' . $this->query->expand_col->query_name)) {
                        $tmpval = $_REQUEST['MANUAL_' . $this->query->expand_col->query_name];
                        if (strlen($tmpval) > 1 && substr($tmpval, 0, 1) == "?") {
                            $expval = substr($tmpval, 1);
                        }

                    }
                    if ($this->query->expand_col->submitted('EXPANDSEARCH_' . $this->query->expand_col->query_name)) {
                        if (array_key_exists("expand_value", $_REQUEST)) {
                            $expval = $_REQUEST["expand_value"];
                        }
                    }

                    $this->template->assign("EXPANDED_SEARCH_VALUE", $expval);

                    $text .= $this->query->expand_col->expand_template();
                } else {
                    if (!($desc = ReporticoLang::translateReportDesc($this->query->xmloutfile))) {
                        $desc = $this->query->deriveAttribute("ReportDescription", false);
                    }

                    $this->template->assign("REPORT_DESCRIPTION", $desc);
                }
                break;

            case "USERINFO":
                $this->template->assign('DB_LOGGEDON', true);
                if (!ReporticoApp::getConfig("db_connect_from_config")) {
                    $this->template->assign('DBUSER', $this->query->datasource->user_name);
                } else {
                    $this->template->assign('DBUSER', false);
                }

                break;

            case "RUNMODE":
                if ($this->query->execute_mode == "MAINTAIN") {
                    $this->template->assign('SHOW_MODE_MAINTAIN_BOX', true);
                } else {
                    // In demo mode for reporitco web site allow design
                    if ($this->query->allow_maintain == "DEMO") {
                        $this->template->assign('SHOW_DESIGN_BUTTON', true);
                    }

                    // Dont allow design option when configuring project
                    if ($this->query->xmlinput != "configureproject.xml" && $this->query->xmlinput != "deleteproject.xml") {
                        $this->template->assign('SHOW_DESIGN_BUTTON', true);
                    }

                    if ($this->query->xmlinput == "deleteproject.xml") {
                        $this->template->assign('SHOW_ADMIN_BUTTON', true);
                        $this->template->assign('SHOW_PROJECT_MENU_BUTTON', false);
                    } else if ($this->query->xmlinput == "configureproject.xml") {
                        $this->template->assign('SHOW_ADMIN_BUTTON', true);
                    }
                }

                $create_report_url = $this->query->create_report_url;
                $configure_project_url = $this->query->configure_project_url;
                $forward = $sessionClass::sessionRequestItem('forward_url_get_parameters', '');
                if ($forward) {
                    $configure_project_url .= "&" . $forward;
                    $create_report_url .= "&" . $forward;
                }
                $this->template->assign('CONFIGURE_PROJECT_URL', $configure_project_url);
                $this->template->assign('CREATE_REPORT_URL', $create_report_url);

                break;

            case "MENUBUTTON":
                $prepare_url = $this->query->prepare_url;
                $menu_url = $this->query->menu_url;
                $forward = $sessionClass::sessionRequestItem('forward_url_get_parameters', '');
                if ($forward) {
                    $menu_url .= "&" . $forward;
                    $prepare_url .= "&" . $forward;
                }
                $this->template->assign('MAIN_MENU_URL', $menu_url);
                $this->template->assign('RUN_REPORT_URL', $prepare_url);

                $admin_menu_url = $this->query->admin_menu_url;
                $forward = $sessionClass::sessionRequestItem('forward_url_get_parameters', '');
                if ($forward) {
                    $admin_menu_url .= "&" . $forward;
                }

                $this->template->assign('ADMIN_MENU_URL', $admin_menu_url);
                break;

            case "MENU":
                break;

            case "PROJECTITEM":
                if ($this->text != ".." && $this->text != "admin") {
                    $forward = $sessionClass::sessionRequestItem('forward_url_get_parameters', '');
                    if ($forward) {
                        $forward .= "&";
                    }

                    if (preg_match("/\?/", $this->query->getActionUrl())) {
                        $url_join_char = "&";
                    } else {
                        $url_join_char = "?";
                    }

                    $this->query->projectitems[] = array(
                        "label" => $this->text,
                        "url" => $this->query->getActionUrl() . $url_join_char . $forward . "execute_mode=MENU&project=" . $this->program . "&reportico_session_name=" . $sessionClass::reporticoSessionName(),
                    );
                }
                break;

            case "MENUITEM":
                $forward = $sessionClass::sessionRequestItem('forward_url_get_parameters', '');
                if ($forward) {
                    $forward .= "&";
                }

                if (preg_match("/\?/", $this->query->getActionUrl())) {
                    $url_join_char = "&";
                } else {
                    $url_join_char = "?";
                }

                if ($this->text == "TEXT") {
                    $this->query->menuitems[] = array(
                        "label" => $this->text,
                        "url" => $this->program,
                    );
                } else {
                    $this->query->menuitems[] = array(
                        "label" => $this->text,
                        "url" => $this->query->getActionUrl() . $url_join_char . $forward . "execute_mode=PREPARE&xmlin=" . $this->program . "&reportico_session_name=" . $sessionClass::reporticoSessionName(),
                    );
                }

                break;

            case "TOPMENU":
                $this->template->assign('SHOW_TOPMENU', true);
                break;

            case "DESTINATION":

                $this->template->assign('SHOW_OUTPUT', true);

                if (!ReporticoApp::getConfig("allow_output", true)) {
                    $this->template->assign('SHOW_OUTPUT', false);
                }

                $op = $sessionClass::sessionRequestItem("target_format", "HTML");
                $output_types = array(
                    "HTML" => "",
                    "PDF" => "",
                    "CSV" => "",
                    "XML" => "",
                    "JSON" => "",
                    "GRID" => "",
                );
                $output_types[$op] = "checked";
                $noutput_types = array();
                foreach ($output_types as $val) {
                    $noutput_types[] = $val;
                }

                $this->template->assign('OUTPUT_TYPES', $noutput_types);

                $op = $sessionClass::sessionRequestItem("target_style", "TABLE");
                $output_styles = array(
                    "TABLE" => "",
                    "FORM" => "",
                );
                $output_styles[$op] = "checked";
                $noutput_styles = array();
                foreach ($output_styles as $val) {
                    $noutput_styles[] = $val;
                }

                $this->template->assign('OUTPUT_STYLES', $noutput_styles);

                $attach = ReporticoUtility::getRequestItem("target_attachment", "1", $this->query->first_criteria_selection);
                if ($attach) {
                    $attach = "checked";
                }

                $this->template->assign("OUTPUT_ATTACH", $attach);

                $this->template->assign("OUTPUT_SHOWGRAPH", $sessionClass::getReporticoSessionParam("target_show_graph") ? "checked" : "");
                $this->template->assign("OUTPUT_SHOWCRITERIA", $sessionClass::getReporticoSessionParam("target_show_criteria") ? "checked" : "");
                $this->template->assign("OUTPUT_SHOWDETAIL", $sessionClass::getReporticoSessionParam("target_show_detail") ? "checked" : "");
                $this->template->assign("OUTPUT_SHOWGROUPHEADERS", $sessionClass::getReporticoSessionParam("target_show_group_headers") ? "checked" : "");
                $this->template->assign("OUTPUT_SHOWGROUPTRAILERS", $sessionClass::getReporticoSessionParam("target_show_group_trailers") ? "checked" : "");
                $this->template->assign("OUTPUT_SHOWCOLHEADERS", $sessionClass::getReporticoSessionParam("target_showColumnHeaders") ? "checked" : "");

                if ($this->query->allow_debug && ReporticoApp::getConfig("allow_debug", true)) {
                    $this->template->assign("OUTPUT_SHOW_DEBUG", true);
                    $debug_mode = ReporticoUtility::getRequestItem("debug_mode", "0", $this->query->first_criteria_selection);
                    $this->template->assign("DEBUG_NONE", "");
                    $this->template->assign("DEBUG_LOW", "");
                    $this->template->assign("DEBUG_MEDIUM", "");
                    $this->template->assign("DEBUG_HIGH", "");
                    switch ($debug_mode) {
                    case 1:
                            $this->template->assign("DEBUG_LOW", "selected");
                            break;
                    case 2:
                            $this->template->assign("DEBUG_MEDIUM", "selected");
                            break;
                    case 3:
                            $this->template->assign("DEBUG_HIGH", "selected");
                            break;
                    default:
                            $this->template->assign("DEBUG_NONE", "selected");
                    }

                    if ($debug_mode) {
                        $debug_mode = "checked";
                    }

                    $this->template->assign("OUTPUT_DEBUG", $debug_mode);
                }

                $checked = "";

                $this->template->assign("OUTPUT_SHOW_SHOWGRAPH", false);
                if (count($this->query->graphs) > 0) {
                    $checked = "";
                    if ($this->query->getAttribute("graphDisplay")) {
                        $checked = "checked";
                    }

                    if (!ReporticoUtility::getRequestItem("target_show_graph") && !$this->query->first_criteria_selection) {
                        $checked = "";
                    }

                    $this->template->assign("OUTPUT_SHOW_SHOWGRAPH", true);
                    $this->template->assign("OUTPUT_SHOWDET", $checked);
                }
                break;

            case "STATUS":

                $msg = "";
                if ($this->query->status_message) {
                    $this->template->assign('STATUSMSG', $this->query->status_message);
                }

                $debug = ReporticoApp::getSystemDebug();
                foreach ($debug as $val) {

                    $msg .= "<hr>" . $val["dbgarea"] . " - " . $val["dbgstr"] . "\n";
                }

                if ($msg) {
                    $msg = "<BR><B>" . ReporticoLang::templateXlate("INFORMATION") . "</B>" . $msg;
                }

                $this->template->assign('STATUSMSG', $msg);
                break;

            case "ERROR":
                $msg = "";

                $lastval = false;
                $duptypect = 0;

                $ct = 0;
                $errors = &ReporticoApp::getSystemErrors();
                foreach ($errors as $val) {

                    if ($val["errno"] == E_USER_ERROR || $val["errno"] == E_USER_WARNING || $val["errno"] == E_USER_NOTICE) {
                        if ($ct++ > 0) {
                            $msg .= "<HR>";
                        }

                        if ($val["errarea"]) {
                            $msg .= $val["errarea"] . " - ";
                        }

                        if ($val["errtype"]) {
                            $msg .= $val["errtype"] . ": ";
                        }

                        $msg .= $val["errstr"];

                        $msg .= $val["errsource"];
                        $msg .= "\n";
                    } else {
                        // Dont keep repeating Assignment errors
                        if ($ct++ > 0) {
                            $msg .= "<HR>";
                        }

                        if ($val["errarea"]) {
                            $msg .= $val["errarea"] . " - ";
                        }

                        if ($val["errtype"]) {
                            $msg .= $val["errtype"] . ": ";
                        }

                        if (isset($val["errstr"]) && $val["errstr"]) {
                            $msg .= "{$val["errfile"]} Line {$val["errline"]} - ";
                        }

                        $msg .= $val["errstr"];
                        $duptypect = 0;
                    }
                    $lastval = $val;
                }
                if ($duptypect > 0) {
                    $msg .= "<BR>$duptypect more errors like this<BR>";
                }

                $debugmsg = "";
                if ($this->query->status_message) {
                    $this->template->assign('STATUSMSG', $this->query->status_message);
                }

                $debug = ReporticoApp::getSystemDebug();
                foreach ($debug as $val) {

                    $debugmsg .= "<hr>" . $val["dbgarea"] . " - " . $val["dbgstr"] . "\n";
                }

                if ($debugmsg) {
                    $debugmsg = "<BR><B>" . ReporticoLang::templateXlate("INFORMATION") . "</B>" . $debugmsg;
                }

                if (false && $msg && $this->query->reportico_ajax_called) {
                    header("HTTP/1.0 500 Not Found", true);
                    $response_array = array();
                    $response_array["errno"] = 100;
                    $response_array["errmsg"] = "<div class=\"reportico-error-box\">$msg</div>$debugmsg";
                    echo json_encode($response_array);
                    die;
                } else {
                    if ($msg) {
                        $msg = "</B><div class=\"reportico-error-box\">$msg</div>$debugmsg";
                    }

                }

                $this->template->assign('ERRORMSG', $msg);
                $sessionClass::setReporticoSessionParam('latestRequest', "");
                break;
        }
        return $text;
    }

    public function postDrawTemplate()
    {
        $text = "";
        switch ($this->panel_type) {
            case "LOGIN":
            case "LOGOUT":
            case "USERINFO":
            case "DESTINATION":
                break;

            case "BODY":
                break;

            case "CRITERIA":
                break;

            case "CRITERIA_FORM":
                break;

            case "CRITERIA_EXPAND":
                break;

            case "MENU":
                $this->template->assign('MENU_ITEMS', $this->query->menuitems);
                break;

            case "ADMIN":
                $this->template->assign('DOCDIR', ReporticoUtility::findBestLocationInIncludePath("doc"));
                $this->template->assign('PROJECT_ITEMS', $this->query->projectitems);
                break;

            case "MENUBUTTON":
                break;

            case "MENUITEM":
                break;

            case "PROJECTITEM":
                break;

            case "TOPMENU":
                break;

            case "MAIN":
                break;
        }
        return $text;
    }

}
