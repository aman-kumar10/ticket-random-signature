<?php
if(!defined("WHMCS")) {
    exit("This file cannot be accessed directly");
}
function ticket_random_signature_keyfunction()
{
    return "a0fa12972346612ef20cf31a6979696f1ef8e7e2";
}
// @ioncube.dynamickey encoding key: ticket_random_signature_keyfunction()
// Encryption type: 5
function ticket_random_signature_license($licensekey = "")
{
    $results = [];
    $results['status'] = "Active";

    return $results;
    $licensekey = Illuminate\Database\Capsule\Manager::table("tbladdonmodules")->where("module", "ticket_random_signature")->where("setting", "licensekey")->value("value");
    $whmcsurl = "https://99modules.com/clientarea/";
    $licensing_secret_key = "ea39b27df2a78661aab556b58e77c392";
    $localkeydays = 15;
    $allowcheckfaildays = 5;
    $check_token = time() . md5(mt_rand(1000000000, 0) . $licensekey);
    $checkdate = date("Ymd");
    $domain = $_SERVER["SERVER_NAME"];
    $usersip = isset($_SERVER["SERVER_ADDR"]) ? $_SERVER["SERVER_ADDR"] : $_SERVER["LOCAL_ADDR"];
    $dirpath = dirname(__FILE__);
    $verifyfilepath = "modules/servers/licenseserver/verification.php";
    $localkeyvalid = false;
    $localkey = Illuminate\Database\Capsule\Manager::table("tbladdonmodules")->where("module", "ticket_random_signature")->where("setting", "licensedata")->value("value");
    if($localkey) {
        $localkey = str_replace("\n", "", $localkey);
        $localdata = substr($localkey, 0, strlen($localkey) - 32);
        $md5hash = substr($localkey, strlen($localkey) - 32);
        if($md5hash == md5($localdata . $licensing_secret_key)) {
            $localdata = strrev($localdata);
            $md5hash = substr($localdata, 0, 32);
            $localdata = substr($localdata, 32);
            $localdata = base64_decode($localdata);
            $localkeyresults = unserialize($localdata);
            $originalcheckdate = $localkeyresults["checkdate"];
            if($md5hash == md5($originalcheckdate . $licensing_secret_key)) {
                $localexpiry = date("Ymd", mktime(0, 0, 0, date("m"), date("d") - $localkeydays, date("Y")));
                if($localexpiry < $originalcheckdate) {
                    $localkeyvalid = true;
                    $results = $localkeyresults;
                    $validdomains = explode(",", $results["validdomain"]);
                    if(!in_array($_SERVER["SERVER_NAME"], $validdomains)) {
                        $localkeyvalid = false;
                        $localkeyresults["status"] = "Invalid";
                        $results = [];
                    }
                    $validips = explode(",", $results["validip"]);
                    if(!in_array($usersip, $validips)) {
                        $localkeyvalid = false;
                        $localkeyresults["status"] = "Invalid";
                        $results = [];
                    }
                    $validdirs = explode(",", $results["validdirectory"]);
                    if(!in_array($dirpath, $validdirs)) {
                        $localkeyvalid = false;
                        $localkeyresults["status"] = "Invalid";
                        $results = [];
                    }
                }
            }
        }
    }
    if(!$localkeyvalid) {
        $responseCode = 0;
        $postfields = ["licensekey" => $licensekey, "domain" => $domain, "ip" => $usersip, "dir" => $dirpath];
        if($check_token) {
            $postfields["check_token"] = $check_token;
        }
        $query_string = "";
        foreach ($postfields as $k => $v) {
            $query_string .= $k . "=" . urlencode($v) . "&";
        }
        if(function_exists("curl_exec")) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $whmcsurl . $verifyfilepath);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $query_string);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $data = curl_exec($ch);
            $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
        } else {
            $responseCodePattern = "/^HTTP\\/\\d+\\.\\d+\\s+(\\d+)/";
            $fp = @fsockopen($whmcsurl, 80, $errno, $errstr, 5);
            if($fp) {
                $newlinefeed = "\r\n";
                $header = "POST " . $whmcsurl . $verifyfilepath . " HTTP/1.0" . $newlinefeed;
                $header .= "Host: " . $whmcsurl . $newlinefeed;
                $header .= "Content-type: application/x-www-form-urlencoded" . $newlinefeed;
                $header .= "Content-length: " . @strlen($query_string) . $newlinefeed;
                $header .= "Connection: close" . $newlinefeed . $newlinefeed;
                $header .= $query_string;
                $data = $line = "";
                @stream_set_timeout($fp, 20);
                @fputs($fp, $header);
                $status = @socket_get_status($fp);
                while (!@feof($fp) && $status) {
                    $line = @fgets($fp, 1024);
                    $patternMatches = [];
                    if(!$responseCode && preg_match($responseCodePattern, trim($line), $patternMatches)) {
                        $responseCode = empty($patternMatches[1]) ? 0 : $patternMatches[1];
                    }
                    $data .= $line;
                    $status = @socket_get_status($fp);
                }
                @fclose($fp);
            }
        }
        if($responseCode != 200) {
            $localexpiry = date("Ymd", mktime(0, 0, 0, date("m"), date("d") - ($localkeydays + $allowcheckfaildays), date("Y")));
            if($localexpiry < $originalcheckdate) {
                $results = $localkeyresults;
            } else {
                $results = [];
                $results["status"] = "Invalid";
                $results["description"] = "Remote Check Failed";
                return $results;
            }
        } else {
            preg_match_all("/<(.*?)>([^<]+)<\\/\\1>/i", $data, $matches);
            $results = [];
            foreach ($matches[1] as $k => $v) {
                $results[$v] = $matches[2][$k];
            }
        }
        if(!is_array($results)) {
            exit("Invalid License Server Response");
        }
        if($results["md5hash"] && $results["md5hash"] != md5($licensing_secret_key . $check_token)) {
            $results["status"] = "Invalid";
            $results["description"] = "MD5 Checksum Verification Failed";
            return $results;
        }
        if($results["status"] == "Active") {
            $results["checkdate"] = $checkdate;
            $data_encoded = serialize($results);
            $data_encoded = base64_encode($data_encoded);
            $data_encoded = md5($checkdate . $licensing_secret_key) . $data_encoded;
            $data_encoded = strrev($data_encoded);
            $data_encoded = $data_encoded . md5($data_encoded . $licensing_secret_key);
            $data_encoded = wordwrap($data_encoded, 80, "\n", true);
            $results["localkey"] = $data_encoded;
        }
        if($results["localkey"]) {
            Illuminate\Database\Capsule\Manager::table("tbladdonmodules")->updateOrInsert(["module" => "ticket_random_signature", "setting" => "licensedata"], ["module" => "ticket_random_signature", "setting" => "licensedata", "value" => $results["localkey"]]);
        }
        $results["remotecheck"] = true;
    }
    if(strtolower($results["status"]) == "active") {
        $results["licensestatus"] = "License is active";
        $results["labeltype"] = "success";
    } else {
        $results["labeltype"] = "danger";
    }
    if(trim($licensekey) == "") {
        $results["licensestatus"] = "Please enter your license key";
    } elseif(strtolower($results["status"]) != "active") {
        $results["licensestatus"] = "License information is wrong!";
    }
    unset($postfields);
    unset($data);
    unset($matches);
    unset($whmcsurl);
    unset($licensing_secret_key);
    unset($checkdate);
    unset($usersip);
    unset($localkeydays);
    unset($allowcheckfaildays);
    unset($md5hash);
    return $results;
}
// @ioncube.dynamickey encoding key: ticket_random_signature_keyfunction()
// Encryption type: 3
function ticket_random_signature_config()
{
    $licensestatus = ticket_random_signature_license();
    return ["name" => "Ticket Random Signature", "description" => "The ultimate WHMCS module that automates ticket responses with personalized, real names, enhancing customer engagement and streamlining support communication with a professional touch.", "version" => "1.1.1", "author" => "<a href='https://99modules.com/' target='_blank'>99modules</a>", "language" => "english", "fields" => ["nodeletedb" => ["FriendlyName" => "Database Table", "Type" => "yesno", "Size" => "25", "Description" => "Tick this box to delete the tables from the database when deactivating the module."], "licensekey" => ["FriendlyName" => "License key", "Type" => "text", "Size" => "35", "Description" => "<span style='padding:6px' class='label label-" . $licensestatus["labeltype"] . "'>&nbsp;" . $licensestatus["licensestatus"] . "&nbsp;</span>"]]];
}
// @ioncube.dynamickey encoding key: ticket_random_signature_keyfunction()
// Encryption type: 1
function ticket_random_signature_activate()
{
    if(!Illuminate\Database\Capsule\Manager::schema()->hasTable("nnm_trs")) {
        Illuminate\Database\Capsule\Manager::schema()->create("nnm_trs", function ($table) {
            $table->increments("id");
            $table->integer("rel_id");
            $table->string("language")->nullable();
            $table->text("signature")->nullable();
        });
    }
    if(!Illuminate\Database\Capsule\Manager::schema()->hasTable("nnm_trs_rl")) {
        Illuminate\Database\Capsule\Manager::schema()->create("nnm_trs_rl", function ($table) {
            $table->increments("id");
            $table->integer("rel_id");
            $table->integer("ticket_id");
        });
    }
    if(!Illuminate\Database\Capsule\Manager::schema()->hasTable("nnm_trs_ex")) {
        Illuminate\Database\Capsule\Manager::schema()->create("nnm_trs_ex", function ($table) {
            $table->increments("id");
            $table->integer("admin_id");
        });
    }
    if(!Illuminate\Database\Capsule\Manager::schema()->hasTable("nnm_trs_settings")) {
        Illuminate\Database\Capsule\Manager::schema()->create("nnm_trs_settings", function ($table) {
            $table->increments("id");
            $table->string("setting")->nullable();
            $table->text("value")->nullable();
        });
    }
    return ["status" => "success", "description" => "Ticket Signature Pro has been activated."];
}
// @ioncube.dynamickey encoding key: ticket_random_signature_keyfunction()
// Encryption type: 3
function ticket_random_signature_deactivate()
{
    $delete = Illuminate\Database\Capsule\Manager::table("tbladdonmodules")->where("module", "ticket_random_signature")->where("setting", "nodeletedb")->first();
    if($delete->value) {
        Illuminate\Database\Capsule\Manager::schema()->dropIfExists("nnm_trs");
        Illuminate\Database\Capsule\Manager::schema()->dropIfExists("nnm_trs_ex");
        Illuminate\Database\Capsule\Manager::schema()->dropIfExists("nnm_trs_rl");
        Illuminate\Database\Capsule\Manager::schema()->dropIfExists("nnm_trs_settings");
    }
    return ["status" => "success", "description" => "Ticket Signature Pro has been deactivated."];
}
// @ioncube.dynamickey encoding key: ticket_random_signature_keyfunction()
// Encryption type: 2
function ticket_random_signature_upgrade($vars)
{
    if(!Illuminate\Database\Capsule\Manager::schema()->hasColumn("nnm_trs", "language")) {
        Illuminate\Database\Capsule\Manager::schema()->table("nnm_trs", function ($table) {
            $table->string("language")->nullable();
        });
    }
}
// @ioncube.dynamickey encoding key: ticket_random_signature_keyfunction()
// Encryption type: 6
function ticket_random_signature_output($vars)
{
    $licensestatus = ticket_random_signature_license();
    switch ($licensestatus["status"]) {
        case "Active":
            break;
        case "Invalid":
            echo "License key is Invalid";
            return "";
        case "Expired":
            echo "License key is Expired";
            return "";
        case "Suspended":
            echo "License key is Suspended";
            return "";
        default:
            echo "Invalid Response";
            return "";
    }
    echo "<style>h1{display: none}</style>";
    if(!class_exists("NNM_Page_Builder")) {
        include __DIR__ . DIRECTORY_SEPARATOR . "core" . DIRECTORY_SEPARATOR . "pagebuilder.php";
    }
    $LANG = $vars["_lang"];
    $page_manager = new NNM_Page_Builder();
    $page_manager->modulename = "Ticket Random Signature";
    $page_manager->modulelink = "ticket_random_signature";
    $page_manager->helplink = "https://99modules.com/docs/ticket-random-signature/";
	$page_manager->menu = ["Support Departments" => ["href" => "", "istab" => false, "external" => false], "Exclude Admins" => ["href" => "c=exclude", "address" => "exclude", "istab" => false, "external" => false], "Settings" => ["href" => "c=settings", "address" => "settings", "istab" => false, "external" => false]];
    $page_manager->startlang();
    $page_manager->header();
    if(isset($_REQUEST["saved"])) {
        echo "<div class=\"alert alert-success\">Saved Successfully!</div>";
    }
    if(isset($_REQUEST["deleted"])) {
        echo "<div class=\"alert alert-success\">Deleted Successfully!</div>";
    }
    if(isset($_REQUEST["added"])) {
        echo "<div class=\"alert alert-success\">Added Successfully!</div>";
    }
    $controller = isset($_REQUEST["c"]) ? $_REQUEST["c"] : "SupportDepartments";
    $action = isset($_REQUEST["a"]) ? $_REQUEST["a"] : "index";
    $controller .= "Controller";
    $controller = ucfirst($controller);
    if(!class_exists("\\WHMCS\\Module\\Addon\\Ticket_random_signature\\Adminarea\\" . $controller)) {
        redir("module=ticket_random_signature", "addonmodules.php");
    }
    $controller = "\\WHMCS\\Module\\Addon\\Ticket_random_signature\\Adminarea\\" . $controller;
    $controller = new $controller();
    if(method_exists($controller, $action)) {
        $controller->{$action}($vars);
    } else {
        $controller->index($vars);
    }
    $page_manager->footer();
}

?>