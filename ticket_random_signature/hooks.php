<?php
if(!defined("WHMCS")) {
    exit("This file cannot be accessed directly");
}
include __DIR__ . "/config.php";
define("ticket_random_signature_root", __DIR__);
if(!defined("ticket_random_signature_name")) {
    define("ticket_random_signature_name", 2);
}
add_hook("AdminAreaPage", 1, function ($vars) {
    if(App::getCurrentFilename() == "supporttickets" && isset($vars["ticketid"]) && $vars["ticketid"] && isset($vars["signature"])) {
        $is_exclude = Illuminate\Database\Capsule\Manager::table("nnm_trs_ex")->where("admin_id", $_SESSION["adminid"])->count();
        if($is_exclude) {
            return [];
        }
        if(isset($vars["deptid"]) && $vars["deptid"]) {
            $did = $vars["deptid"];
        } else {
            $did = Illuminate\Database\Capsule\Manager::table("tbltickets")->where("id", $vars["ticketid"])->value("did");
        }
        $settings = ticket_random_signature_settings();
        if(isset($settings["dept_" . $did]) && $settings["dept_" . $did] == "1") {
            global $CONFIG;
            $signature_to_updates = Illuminate\Database\Capsule\Manager::table("nnm_trs")->whereNull("language")->select("id")->get();
            if($signature_to_updates) {
                foreach ($signature_to_updates as $signature_to_update) {
                    Illuminate\Database\Capsule\Manager::table("nnm_trs")->where("id", $signature_to_update->id)->update(["language" => $CONFIG["Language"]]);
                }
            }
            $current_language = AdminLang::getName();
            $signature_item = Illuminate\Database\Capsule\Manager::table("nnm_trs");
            if($current_language != $CONFIG["Language"]) {
                $signature_item = $signature_item->where("language", $current_language);
            } else {
                $signature_item = $signature_item->where("language", $CONFIG["Language"]);
            }
            $signature_item = $signature_item->where("rel_id", $did)->inRandomOrder()->first();
            if(isset($settings["fixed"]) && $settings["fixed"] == "1") {
                $exist_signature = Illuminate\Database\Capsule\Manager::table("nnm_trs_rl")->where("nnm_trs_rl.ticket_id", $vars["ticketid"])->leftJoin("nnm_trs", "nnm_trs.id", "=", "nnm_trs_rl.rel_id")->value("nnm_trs.signature");
                if(!$exist_signature) {
                    Illuminate\Database\Capsule\Manager::table("nnm_trs_rl")->updateOrInsert(["ticket_id" => $vars["ticketid"]], ["ticket_id" => $vars["ticketid"], "rel_id" => $signature_item->id]);
                } else {
                    $signature_item->signature = $exist_signature;
                }
            }
            return ["signature" => $signature_item->signature];
        }
    }
});
add_hook("TicketDelete", 1, function ($vars) {
    Illuminate\Database\Capsule\Manager::table("nnm_trs_rl")->where("ticket_id", $vars["ticketId"])->delete();
});
add_hook("ClientAreaPageViewTicket", 60000, function ($vars) {
    if($vars["replies"] || $vars["descreplies"]) {
        if(isset($vars["departmentid"]) && $vars["departmentid"]) {
            $did = $vars["departmentid"];
        } else {
            $did = Illuminate\Database\Capsule\Manager::table("tbltickets")->where("id", $vars["id"])->value("did");
        }
        $settings = ticket_random_signature_settings();
        if(isset($settings["dept_" . $did]) && $settings["dept_" . $did] == "1") {
            $all = Illuminate\Database\Capsule\Manager::table("nnm_trs")->get();
            foreach ($vars["replies"] as $k => $reply) {
                if($reply["admin"]) {
                    $admin_name = find_related_reply_admin_name($reply["message"], $all);
                    if($admin_name) {
                        $vars["replies"][$k]["requestor"]["name"] = $admin_name;
                    }
                }
            }
            foreach ($vars["descreplies"] as $k => $reply) {
                if($reply["admin"]) {
                    $admin_name = find_related_reply_admin_name($reply["message"], $all);
                    if($admin_name) {
                        $vars["descreplies"][$k]["requestor"]["name"] = $admin_name;
                    }
                }
            }
            return ["replies" => $vars["replies"], "descreplies" => $vars["descreplies"]];
        }
    }
});
function ticket_random_signature_settings()
{
    $settings = Illuminate\Database\Capsule\Manager::table("nnm_trs_settings")->pluck("value", "setting");
    if(!is_array($settings)) {
        $settings = $settings->toArray();
    }
    return $settings;
}
function find_related_reply_admin_name($message = "", $all = [])
{
    $name = "";
    $message = str_replace(["<br />", "<br>", "</p>", "<p>"], "", $message);
    $lines = explode("\n", $message);
    foreach ($lines as $k => $line) {
        if(trim($line) == "") {
            unset($lines[$k]);
        }
    }
    $name = $lines[count((array)$lines) - ticket_random_signature_name];
    $find = false;
    foreach ($all as $item) {
        if($item->signature && mb_strpos($item->signature, $name) !== false) {
            $find = true;
        }
    }
    if($find) {
        return $name;
    }
    return "";
}

?>