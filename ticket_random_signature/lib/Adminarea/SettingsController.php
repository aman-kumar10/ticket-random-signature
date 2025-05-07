<?php
namespace WHMCS\Module\Addon\ticket_random_signature\Adminarea;

if(!defined("WHMCS")) {
    exit("This file cannot be accessed directly");
}
class SettingsController
{
    public function index()
    {
        $settings = ticket_random_signature_settings();
        echo "        <form action=\"?module=ticket_random_signature&c=settings&a=save\" method=\"post\">\r\n            <input type=\"hidden\" name=\"save\" value=\"1\">\r\n            <div class=\"tab-pane active\" id=\"tab11\">\r\n                <table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">\r\n                    <tbody>\r\n                    <tr>\r\n                        <td class=\"fieldlabel\" width=\"300\">\r\n                            Fixed Signature\r\n                        </td>\r\n                        <td class=\"fieldarea\">\r\n                            <label class=\"radio-inline\">\r\n                                <input type=\"radio\" name=\"settings[fixed]\"\r\n                                       value=\"1\" ";
        echo isset($settings["fixed"]) && $settings["fixed"] == "1" ? "checked=\"checked\"" : "";
        echo ">\r\n                                Enabled </label>\r\n                            <label class=\"radio-inline\">\r\n                                <input type=\"radio\" name=\"settings[fixed]\"\r\n                                       value=\"0\" ";
        echo !isset($settings["fixed"]) || $settings["fixed"] == "0" ? "checked=\"checked\"" : "";
        echo ">\r\n                                Disabled </label>\r\n                            <br>Choose fixed signature per ticket (Ticket assigned to staff)\r\n                        </td>\r\n                    </tr>\r\n                </table>\r\n            </div>\r\n            <div class=\"btn-container\">\r\n                <input id=\"save_change_text\" type=\"submit\" value=\"Save Changes\" class=\"btn btn-primary\">\r\n            </div>\r\n        </form>\r\n        ";
    }
    public function save()
    {
        if(isset($_POST["save"])) {
            if(!isset($_REQUEST["settings"]["fixed"])) {
                $_REQUEST["settings"]["fixed"] = "";
            }
            foreach ($_REQUEST["settings"] as $key => $setting) {
                if(is_array($setting)) {
                    $setting = serialize($setting);
                }
                \Illuminate\Database\Capsule\Manager::table("nnm_trs_settings")->updateOrInsert(["setting" => $key], ["setting" => $key, "value" => $setting]);
            }
        }
        redir("module=ticket_random_signature&c=settings&saved=1", "addonmodules.php");
    }
}
?>