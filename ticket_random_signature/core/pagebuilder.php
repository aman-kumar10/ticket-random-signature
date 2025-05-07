<?php
if(!defined("WHMCS")) {
    exit("This file cannot be accessed directly");
}
class NNM_Page_Builder
{
    public $modulename = "";
    public $modulelink = "";
    public $helplink = "";
    public $langtablename = "";
    public $havemultaddon_lang = false;
    public $menu = [];
    public function __construct()
    {
    }
    public function startlang()
    {
        if(isset($_REQUEST["getlang"]) && $_REQUEST["getlang"] != "") {
            $this->getlang();
        }
        if(isset($_REQUEST["savelang"]) && $_REQUEST["savelang"] != "") {
            $this->savelang();
        }
    }
    public function getlang()
    {
        global $aInt;
        $aInt->content = "";
        $langinputs = "";
        global $CONFIG;
        $name = $_REQUEST["getlang"];
        $existval = [];
        $existvals = Illuminate\Database\Capsule\Manager::table($this->langtablename)->where("setting", $name . "_lang")->value("values");
        if($existvals != "") {
            $existval = unserialize($existvals);
        }
        foreach (WHMCS\Language\ClientLanguage::getLanguages() as $lang) {
            if($lang == $CONFIG["Language"]) {
				continue;
            }
			$input = "<input type=\"text\" name=\"" . $lang . "\" class=\"form-control input-sm\" value=\"" . ($existval[$lang] != "" ? $existval[$lang] : "") . "\">";
			if(isset($_REQUEST["outtype"]) && $_REQUEST["outtype"] == "textarea") {
				$input = "<textarea name=\"" . $lang . "\" class=\"form-control input-sm\">" . ($existval[$lang] != "" ? $existval[$lang] : "") . "</textarea>";
			}
			$langinputs .= "<div class=\"col-md-4 col-sm-6 bottom-margin-5\">\r\n        " . ucfirst($lang) . "<br>\r\n            " . $input . "\r\n    </div>";
        }
        $orgval = $_REQUEST["origvalue"];
        if($orgval == "undefined") {
            $orgval = "";
        }
        $input = "<input type=\"text\" name=\"this_will_not_save\" disabled=\"disabled\" class=\"form-control input-sm\" value=\"" . $orgval . "\">";
        if(isset($_REQUEST["outtype"]) && $_REQUEST["outtype"] == "textarea") {
            $input = "<textarea disabled=\"disabled\" name=\"this_will_not_save\" class=\"form-control input-sm\">" . $orgval . "</textarea>";
        }
        $aInt->setBodyContent(["body" => "<form method=\"post\" action=\"?module=ageverification&savelang=" . $_REQUEST["getlang"] . "\" class=\"form\">\r\n    <p class=\"font-size-sm\">Localise the value of the selected field below. Leave a field empty to use the default value for that language.</p>\r\n    <div class=\"row\">\r\n        <div class=\"col-sm-10 col-sm-offset-1\">\r\n            <div class=\"panel panel-info font-size-sm translate-value\">\r\n                <div class=\"panel-heading\">Default Value</div>\r\n                <div class=\"panel-body\">\r\n                    " . $input . "\r\n                </div>\r\n            </div>\r\n        </div>\r\n    </div>\r\n    <div class=\"row font-size-sm\">\r\n            " . $langinputs . "\r\n    </div>\r\n</form>"]);
        $aInt->output();
        WHMCS\Terminus::getInstance()->doExit();
    }
    public function savelang()
    {
        global $aInt;
        global $CONFIG;
        $aInt->content = "";
        $savearray = [];
        foreach (WHMCS\Language\ClientLanguage::getLanguages() as $lang) {
            if($lang == $CONFIG["Language"]) {
				continue;
            } 
			if(isset($_REQUEST[$lang]) && $_REQUEST[$lang] != "") {
                $savearray[$lang] = $_REQUEST[$lang];
            }
        }
        $name = $_REQUEST["savelang"];
        Illuminate\Database\Capsule\Manager::table($this->langtablename)->updateOrInsert(["setting" => $name . "_lang"], ["setting" => $name . "_lang", "values" => serialize($savearray)]);
        $aInt->setBodyContent(["dismiss" => true, "successMsgTitle" => "Success!", "successMsg" => "Your changes have been saved."]);
        $aInt->output();
        WHMCS\Terminus::getInstance()->doExit();
    }
    public function menu()
    {
        return "<nav class=\"navbar navbar-default nnmnavbar\">\r\n      <div class=\"nnmcontainer\">\r\n        <!-- Brand and toggle get grouped for better mobile display -->\r\n        <div class=\"navbar-header\">\r\n          <button type=\"button\" class=\"navbar-toggle collapsed\" data-toggle=\"collapse\" data-target=\"#navbar-collapse-1\">\r\n            <span class=\"sr-only\">Toggle navigation</span>\r\n            <span class=\"icon-bar\"></span>\r\n            <span class=\"icon-bar\"></span>\r\n            <span class=\"icon-bar\"></span>\r\n          </button>\r\n          <a class=\"navbar-brand\" href=\"#\">" . $this->modulename . "</a>\r\n        </div>\r\n\r\n        <!-- Collect the nav links, forms, and other content for toggling -->\r\n        <div class=\"collapse navbar-collapse\" id=\"navbar-collapse-1\">\r\n          <ul class=\"nav navbar-nav navbar-left\">\r\n            " . $this->menulist() . "\r\n          </ul>\r\n          <ul class=\"nav navbar-nav navbar-right\">\r\n          <li><a target=\"_blank\" href=\"" . $this->helplink . "\"><i class=\"fa fa-question-circle\" aria-hidden=\"true\"></i> Help</a></li>\r\n          </ul>\r\n        </div><!-- /.navbar-collapse -->\r\n      </div><!-- /.container -->\r\n    </nav><!-- /.navbar -->";
    }
    public function menulist()
    {
        $menu = "";
        if(count($this->menu)) {
            $i = 1;
            foreach ($this->menu as $mkey => $mvalue) {
                $active = "";
                if(!$mvalue["target"]) {
                    if($mvalue["href"] != "") {
                        $mvalue["href"] = "addonmodules.php?module=" . $this->modulelink . "&" . $mvalue["href"];
                        if(isset($_REQUEST["c"]) && $_REQUEST["c"] == $mvalue["address"]) {
                            $active = "active";
                        }
                    } else {
                        $mvalue["href"] = "addonmodules.php?module=" . $this->modulelink;
                        if(!isset($_REQUEST["c"])) {
                            $active = "active";
                        }
                    }
                }
                $tab = "";
                if($mvalue["istab"]) {
                    $tab = "  role=\"tab\" data-toggle=\"tab\" id=\"tabLink" . $i . "\" aria-expanded=\"true\"";
                    if($i == 1) {
                        $active = "active";
                    }
                }
                $menu .= "<li class=\"" . $active . "\"><a href=\"" . $mvalue["href"] . "\" " . (isset($mvalue["class"]) ? "class=\"" . $mvalue["class"] . "\"" : "") . " " . ($mvalue["target"] ? "target=\"_blank\"" : "") . $tab . ">" . $mkey . "</a></li>";
                $i++;
            }
        }
        return $menu;
    }
    public function header()
    {
        echo "<div class=\"row\">\r\n        <div class=\"col-md-12\">\r\n            <div class=\"panel panel-default\">\r\n                <div class=\"panel-heading nnmheader\">\r\n                    " . $this->menu() . "\r\n                </div>\r\n                <div class=\"panel-body\">";
    }
    public function footer()
    {
        echo "</div>\r\n                <div class=\"panel-footer\">\r\n                    <div class=\"row\">\r\n                        <div class=\"col-md-12 text-center\">\r\n                            <p class=\"nnmcopyright\">Copyright <a target=\"_blank\" href=\"https://99modules.com\">99modules</a> - " . date("Y") . "</a></p>\r\n                        </div>\r\n                    </div>\r\n                </div>\r\n            </div>\r\n        </div>\r\n    </div>" . $js;
    }
    public function generateTranslateButton($name = "", $title = "")
    {
        return "<a id=\"translate" . $name . "\" href=\"addonmodules.php?module=" . $this->modulelink . "&getlang=" . $name . "\" class=\"btn btn-default btn-translate btn-nnmtranslate\" data-modal-title=\"Translate " . $title . "\"><i class=\"fas fa-edit\"></i> Translate</a>";
    }
}
?>