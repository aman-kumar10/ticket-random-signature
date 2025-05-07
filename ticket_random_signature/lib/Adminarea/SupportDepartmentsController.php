<?php
namespace WHMCS\Module\Addon\Ticket_random_signature\Adminarea;

if(!defined("WHMCS")) {
    exit("This file cannot be accessed directly");
}
class SupportDepartmentsController
{
    public function index()
    {
        global $aInt;
        global $page;
        global $numrows;
        global $limit;
        $result = \WHMCS\Support\Department::get();
        $settings = ticket_random_signature_settings();
        foreach ($result as $data) {
            $tabledata[] = ["<center>" . $data->id . "</center>", "<center>" . $data->name . "</center>", "<center><span class=\"label label-" . (isset($settings["dept_" . $data->id]) && $settings["dept_" . $data->id] == "1" ? "success" : "warning") . "\">" . (isset($settings["dept_" . $data->id]) && $settings["dept_" . $data->id] == "1" ? "Enabled" : "Disabled") . "</span>" . "</center>", "<center><a href=\"?module=ticket_random_signature&a=show&did=" . $data->id . "\" class=\"btn btn-default btn-sm\"><i class=\"fas fa-edit\"></i>  Signatures List</a>" . "</center>"];
        }
        echo $aInt->sortableTable(["ID", "Support Department", "Status", " "], $tabledata);
    }
    public function show()
    {
        $department = \WHMCS\Support\Department::find($_REQUEST["did"]);
        if(!$department) {
            redir("module=ticket_random_signature");
        }
        if(isset($_REQUEST["delete_id"])) {
            \Illuminate\Database\Capsule\Manager::table("nnm_trs")->where("id", $_REQUEST["delete_id"])->delete();
            redir("module=ticket_random_signature&a=show&deleted=1&did=" . $_REQUEST["did"]);
        }
        $settings = ticket_random_signature_settings();
        $did = $_REQUEST["did"];
        echo "        <form action=\"?module=ticket_random_signature&a=saveDepartment&did=";
        echo $_REQUEST["did"];
        echo "\"\r\n              method=\"post\">\r\n            <input type=\"hidden\" name=\"save\" value=\"1\">\r\n            <table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">\r\n                <tbody>\r\n                <tr>\r\n                    <td class=\"fieldlabel\" width=\"300\">\r\n                        Support Department\r\n                    </td>\r\n                    <td class=\"fieldarea\">\r\n                        <strong>";
        echo $department->name;
        echo "</strong>\r\n                    </td>\r\n                <tr>\r\n                    <td class=\"fieldlabel\" width=\"300\">\r\n                        Random Signature Status\r\n                    </td>\r\n                    <td class=\"fieldarea\">\r\n                        <label class=\"radio-inline\">\r\n                            <input type=\"radio\" name=\"settings[dept_";
        echo $_REQUEST["did"];
        echo "]\"\r\n                                   value=\"1\" ";
        echo isset($settings["dept_" . $did]) && $settings["dept_" . $did] == "1" ? "checked=\"checked\"" : "";
        echo ">\r\n                            Enabled </label>\r\n                        <label class=\"radio-inline\">\r\n                            <input type=\"radio\" name=\"settings[dept_";
        echo $_REQUEST["did"];
        echo "]\"\r\n                                   value=\"0\" ";
        echo !isset($settings["dept_" . $did]) || $settings["dept_" . $did] == "0" ? "checked=\"checked\"" : "";
        echo ">\r\n                            Disabled </label>\r\n                    </td>\r\n                </tr>\r\n            </table>\r\n            <div class=\"btn-container\">\r\n                <input type=\"submit\" value=\"Save Changes\" class=\"btn btn-sm btn-primary\">\r\n                <a href=\"?module=ticket_random_signature\" class=\"btn btn-sm btn-default\">Go Back</a>\r\n            </div>\r\n            <hr>\r\n        </form>\r\n        <a href=\"#\" class=\"btn btn-default add-new-signature\"><i class=\"fas fa-plus\"></i> Add New Signature </a>\r\n        ";
        global $aInt;
        global $page;
        global $numrows;
        global $limit;
        global $CONFIG;
        $aInt->sortableTableInit("id");
        $numrows = \Illuminate\Database\Capsule\Manager::table("nnm_trs")->count();
        $limit = $aInt->rowLimit;
        $records = $page * $limit;
        $tabledata = [];
        $result = \Illuminate\Database\Capsule\Manager::table("nnm_trs")->where("rel_id", $_REQUEST["did"])->skip($records)->take($limit)->orderBy("id", "DESC");
        $result = $result->get();
        $aInt->deleteJSConfirm("doDelete", "global", "deleteconfirm", "?module=ticket_random_signature&a=show&did=" . $_REQUEST["did"] . "&delete_id=");
        foreach ($result as $data) {
            if(!$data->language) {
                $data->language = $CONFIG["Language"];
            }
            $tabledata[] = ["<center>" . $data->id . "</center>", "<center>" . $data->signature . "</center>", "<center><a href=\"#\"  data-resource='" . base64_encode(json_encode($data)) . "' class=\"btn btn-default btn-sm edit_signature_button\"><i class=\"fas fa-edit\"></i> Edit</a>" . " <a href=\"#\" onclick=\"doDelete(" . $data->id . ");\" class=\"btn btn-sm btn-default\" ><i class=\"fas fa-trash\"></i> Delete</a></center>"];
        }
        $locales = ["ee_TG" => "Ewe (Togo)", "kam_KE" => "Kamba (Kenya)", "es_HN" => "Spanish (Honduras)", "ml_IN" => "Malayalam (India)", "ro_MD" => "Romanian (Moldova)", "kab_DZ" => "Kabyle (Algeria)", "es_CO" => "Spanish (Colombia)", "es_PA" => "Spanish (Panama)", "az_Latn" => "Azerbaijani (Latin)", "en_NZ" => "English (New Zealand)", "xog_UG" => "Soga (Uganda)", "fr_GP" => "French (Guadeloupe)", "sr_Cyrl_BA" => "Serbian (Cyrillic, Bosnia and Herzegovina)", "fil_PH" => "Filipino (Philippines)", "lt_LT" => "Lithuanian (Lithuania)", "en_MT" => "English (Malta)", "si_LK" => "Sinhala (Sri Lanka)", "luo_KE" => "Luo (Kenya)", "it_CH" => "Italian (Switzerland)", "uz_Cyrl_UZ" => "Uzbek (Cyrillic, Uzbekistan)", "rm_CH" => "Romansh (Switzerland)", "az_Cyrl_AZ" => "Azerbaijani (Cyrillic, Azerbaijan)", "fr_GQ" => "French (Equatorial Guinea)", "cgg_UG" => "Chiga (Uganda)", "fr_RW" => "French (Rwanda)", "es_SV" => "Spanish (El Salvador)", "mas_TZ" => "Masai (Tanzania)", "en_MU" => "English (Mauritius)", "en_PH" => "English (Philippines)", "mk_MK" => "Macedonian (Macedonia)", "fr_TD" => "French (Chad)", "kln_KE" => "Kalenjin (Kenya)", "sr_Latn" => "Serbian (Latin)", "el_GR" => "Greek (Greece)", "el_CY" => "Greek (Cyprus)", "es_CR" => "Spanish (Costa Rica)", "fo_FO" => "Faroese (Faroe Islands)", "pa_Arab_PK" => "Punjabi (Arabic, Pakistan)", "ar_YE" => "Arabic (Yemen)", "ja_JP" => "Japanese (Japan)", "ur_PK" => "Urdu (Pakistan)", "pa_Guru" => "Punjabi (Gurmukhi)", "gl_ES" => "Galician (Spain)", "zh_Hant_HK" => "Chinese (Traditional Han, Hong Kong SAR China)", "ar_EG" => "Arabic (Egypt)", "th_TH" => "Thai (Thailand)", "es_PE" => "Spanish (Peru)", "fr_KM" => "French (Comoros)", "kk_Cyrl_KZ" => "Kazakh (Cyrillic, Kazakhstan)", "lv_LV" => "Latvian (Latvia)", "tzm_Latn" => "Central Morocco Tamazight (Latin)", "gsw_CH" => "Swiss German (Switzerland)", "ha_Latn_GH" => "Hausa (Latin, Ghana)", "is_IS" => "Icelandic (Iceland)", "pt_BR" => "Portuguese (Brazil)", "en_PK" => "English (Pakistan)", "fa_IR" => "Persian (Iran)", "zh_Hans_SG" => "Chinese (Simplified Han, Singapore)", "fr_TG" => "French (Togo)", "kde_TZ" => "Makonde (Tanzania)", "mr_IN" => "Marathi (India)", "ar_SA" => "Arabic (Saudi Arabia)", "ka_GE" => "Georgian (Georgia)", "mfe_MU" => "Morisyen (Mauritius)", "fr_LU" => "French (Luxembourg)", "de_LU" => "German (Luxembourg)", "ru_MD" => "Russian (Moldova)", "zh_Hans_HK" => "Chinese (Simplified Han, Hong Kong SAR China)", "bg_BG" => "Bulgarian (Bulgaria)", "shi_Latn" => "Tachelhit (Latin)", "es_BO" => "Spanish (Bolivia)", "ko_KR" => "Korean (South Korea)", "it_IT" => "Italian (Italy)", "shi_Latn_MA" => "Tachelhit (Latin, Morocco)", "pt_MZ" => "Portuguese (Mozambique)", "ff_SN" => "Fulah (Senegal)", "zh_Hans" => "Chinese (Simplified Han)", "so_KE" => "Somali (Kenya)", "bn_IN" => "Bengali (India)", "en_UM" => "English (U.S. Minor Outlying Islands)", "id_ID" => "Indonesian (Indonesia)", "uz_Cyrl" => "Uzbek (Cyrillic)", "en_GU" => "English (Guam)", "es_EC" => "Spanish (Ecuador)", "en_US_POSIX" => "English (United States, Computer)", "sr_Latn_BA" => "Serbian (Latin, Bosnia and Herzegovina)", "en_NA" => "English (Namibia)", "bo_IN" => "Tibetan (India)", "vun_TZ" => "Vunjo (Tanzania)", "ar_SD" => "Arabic (Sudan)", "uz_Latn_UZ" => "Uzbek (Latin, Uzbekistan)", "az_Latn_AZ" => "Azerbaijani (Latin, Azerbaijan)", "es_GQ" => "Spanish (Equatorial Guinea)", "ta_IN" => "Tamil (India)", "de_DE" => "German (Germany)", "fr_FR" => "French (France)", "rof_TZ" => "Rombo (Tanzania)", "ar_LY" => "Arabic (Libya)", "en_BW" => "English (Botswana)", "ha_Latn" => "Hausa (Latin)", "fr_NE" => "French (Niger)", "es_MX" => "Spanish (Mexico)", "bem_ZM" => "Bemba (Zambia)", "zh_Hans_CN" => "Chinese (Simplified Han, China)", "bn_BD" => "Bengali (Bangladesh)", "pt_GW" => "Portuguese (Guinea-Bissau)", "de_AT" => "German (Austria)", "kk_Cyrl" => "Kazakh (Cyrillic)", "sw_TZ" => "Swahili (Tanzania)", "ar_OM" => "Arabic (Oman)", "et_EE" => "Estonian (Estonia)", "da_DK" => "Danish (Denmark)", "ro_RO" => "Romanian (Romania)", "zh_Hant" => "Chinese (Traditional Han)", "bm_ML" => "Bambara (Mali)", "fr_CA" => "French (Canada)", "en_IE" => "English (Ireland)", "ar_MA" => "Arabic (Morocco)", "es_GT" => "Spanish (Guatemala)", "uz_Arab_AF" => "Uzbek (Arabic, Afghanistan)", "en_AS" => "English (American Samoa)", "bs_BA" => "Bosnian (Bosnia and Herzegovina)", "am_ET" => "Amharic (Ethiopia)", "ar_TN" => "Arabic (Tunisia)", "haw_US" => "Hawaiian (United States)", "ar_JO" => "Arabic (Jordan)", "fa_AF" => "Persian (Afghanistan)", "uz_Latn" => "Uzbek (Latin)", "en_BZ" => "English (Belize)", "nyn_UG" => "Nyankole (Uganda)", "ebu_KE" => "Embu (Kenya)", "te_IN" => "Telugu (India)", "cy_GB" => "Welsh (United Kingdom)", "en_JM" => "English (Jamaica)", "en_US" => "English (United States)", "ar_KW" => "Arabic (Kuwait)", "af_ZA" => "Afrikaans (South Africa)", "en_CA" => "English (Canada)", "fr_DJ" => "French (Djibouti)", "ti_ER" => "Tigrinya (Eritrea)", "ig_NG" => "Igbo (Nigeria)", "en_AU" => "English (Australia)", "fr_MC" => "French (Monaco)", "pt_PT" => "Portuguese (Portugal)", "es_419" => "Spanish (Latin America)", "fr_CD" => "French (Congo - Kinshasa)", "en_SG" => "English (Singapore)", "bo_CN" => "Tibetan (China)", "kn_IN" => "Kannada (India)", "sr_Cyrl_RS" => "Serbian (Cyrillic, Serbia)", "lg_UG" => "Ganda (Uganda)", "gu_IN" => "Gujarati (India)", "nd_ZW" => "North Ndebele (Zimbabwe)", "sw_KE" => "Swahili (Kenya)", "sq_AL" => "Albanian (Albania)", "hr_HR" => "Croatian (Croatia)", "mas_KE" => "Masai (Kenya)", "ti_ET" => "Tigrinya (Ethiopia)", "es_AR" => "Spanish (Argentina)", "fr_CF" => "French (Central African Republic)", "fr_RE" => "French (Réunion)", "ru_UA" => "Russian (Ukraine)", "yo_NG" => "Yoruba (Nigeria)", "dav_KE" => "Taita (Kenya)", "gv_GB" => "Manx (United Kingdom)", "pa_Arab" => "Punjabi (Arabic)", "teo_UG" => "Teso (Uganda)", "es_PR" => "Spanish (Puerto Rico)", "fr_MF" => "French (Saint Martin)", "rwk_TZ" => "Rwa (Tanzania)", "nb_NO" => "Norwegian Bokmål (Norway)", "fr_CG" => "French (Congo - Brazzaville)", "zh_Hant_TW" => "Chinese (Traditional Han, Taiwan)", "sr_Cyrl_ME" => "Serbian (Cyrillic, Montenegro)", "ses_ML" => "Koyraboro Senni (Mali)", "en_ZW" => "English (Zimbabwe)", "ak_GH" => "Akan (Ghana)", "vi_VN" => "Vietnamese (Vietnam)", "sv_FI" => "Swedish (Finland)", "to_TO" => "Tonga (Tonga)", "fr_MG" => "French (Madagascar)", "fr_GA" => "French (Gabon)", "fr_CH" => "French (Switzerland)", "de_CH" => "German (Switzerland)", "es_US" => "Spanish (United States)", "my_MM" => "Burmese (Myanmar [Burma])", "ar_QA" => "Arabic (Qatar)", "ga_IE" => "Irish (Ireland)", "ee_GH" => "Ewe (Ghana)", "as_IN" => "Assamese (India)", "ca_ES" => "Catalan (Spain)", "fr_SN" => "French (Senegal)", "ne_IN" => "Nepali (India)", "ms_BN" => "Malay (Brunei)", "ar_LB" => "Arabic (Lebanon)", "ta_LK" => "Tamil (Sri Lanka)", "ur_IN" => "Urdu (India)", "fr_CI" => "French (Côte d’Ivoire)", "ha_Latn_NG" => "Hausa (Latin, Nigeria)", "sg_CF" => "Sango (Central African Republic)", "om_ET" => "Oromo (Ethiopia)", "zh_Hant_MO" => "Chinese (Traditional Han, Macau SAR China)", "uk_UA" => "Ukrainian (Ukraine)", "mt_MT" => "Maltese (Malta)", "ki_KE" => "Kikuyu (Kenya)", "luy_KE" => "Luyia (Kenya)", "pa_Guru_IN" => "Punjabi (Gurmukhi, India)", "en_IN" => "English (India)", "ar_IQ" => "Arabic (Iraq)", "en_TT" => "English (Trinidad and Tobago)", "bez_TZ" => "Bena (Tanzania)", "es_NI" => "Spanish (Nicaragua)", "uz_Arab" => "Uzbek (Arabic)", "ne_NP" => "Nepali (Nepal)", "zh_Hans_MO" => "Chinese (Simplified Han, Macau SAR China)", "en_MH" => "English (Marshall Islands)", "hu_HU" => "Hungarian (Hungary)", "en_GB" => "English (United Kingdom)", "fr_BE" => "French (Belgium)", "de_BE" => "German (Belgium)", "be_BY" => "Belarusian (Belarus)", "sl_SI" => "Slovenian (Slovenia)", "sr_Latn_RS" => "Serbian (Latin, Serbia)", "fr_BF" => "French (Burkina Faso)", "sk_SK" => "Slovak (Slovakia)", "fr_ML" => "French (Mali)", "he_IL" => "Hebrew (Israel)", "ha_Latn_NE" => "Hausa (Latin, Niger)", "ru_RU" => "Russian (Russia)", "fr_CM" => "French (Cameroon)", "teo_KE" => "Teso (Kenya)", "seh_MZ" => "Sena (Mozambique)", "kl_GL" => "Kalaallisut (Greenland)", "fi_FI" => "Finnish (Finland)", "es_ES" => "Spanish (Spain)", "asa_TZ" => "Asu (Tanzania)", "cs_CZ" => "Czech (Czech Republic)", "tr_TR" => "Turkish (Turkey)", "es_PY" => "Spanish (Paraguay)", "tzm_Latn_MA" => "Central Morocco Tamazight (Latin, Morocco)", "en_HK" => "English (Hong Kong SAR China)", "nl_NL" => "Dutch (Netherlands)", "en_BE" => "English (Belgium)", "ms_MY" => "Malay (Malaysia)", "es_UY" => "Spanish (Uruguay)", "ar_BH" => "Arabic (Bahrain)", "kw_GB" => "Cornish (United Kingdom)", "lag_TZ" => "Langi (Tanzania)", "so_DJ" => "Somali (Djibouti)", "shi_Tfng_MA" => "Tachelhit (Tifinagh, Morocco)", "sr_Latn_ME" => "Serbian (Latin, Montenegro)", "sn_ZW" => "Shona (Zimbabwe)", "or_IN" => "Oriya (India)", "fr_BI" => "French (Burundi)", "jmc_TZ" => "Machame (Tanzania)", "chr_US" => "Cherokee (United States)", "eu_ES" => "Basque (Spain)", "saq_KE" => "Samburu (Kenya)", "naq_NA" => "Nama (Namibia)", "af_NA" => "Afrikaans (Namibia)", "kea_CV" => "Kabuverdianu (Cape Verde)", "es_DO" => "Spanish (Dominican Republic)", "kok_IN" => "Konkani (India)", "de_LI" => "German (Liechtenstein)", "fr_BJ" => "French (Benin)", "guz_KE" => "Gusii (Kenya)", "rw_RW" => "Kinyarwanda (Rwanda)", "mg_MG" => "Malagasy (Madagascar)", "km_KH" => "Khmer (Cambodia)", "shi_Tfng" => "Tachelhit (Tifinagh)", "ar_AE" => "Arabic (United Arab Emirates)", "fr_MQ" => "French (Martinique)", "sv_SE" => "Swedish (Sweden)", "az_Cyrl" => "Azerbaijani (Cyrillic)", "so_ET" => "Somali (Ethiopia)", "en_ZA" => "English (South Africa)", "ii_CN" => "Sichuan Yi (China)", "fr_BL" => "French (Saint Barthélemy)", "hi_IN" => "Hindi (India)", "mer_KE" => "Meru (Kenya)", "nn_NO" => "Norwegian Nynorsk (Norway)", "ar_DZ" => "Arabic (Algeria)", "ar_SY" => "Arabic (Syria)", "en_MP" => "English (Northern Mariana Islands)", "nl_BE" => "Dutch (Belgium)", "en_VI" => "English (U.S. Virgin Islands)", "es_CL" => "Spanish (Chile)", "hy_AM" => "Armenian (Armenia)", "zu_ZA" => "Zulu (South Africa)", "es_VE" => "Spanish (Venezuela)", "khq_ML" => "Koyra Chiini (Mali)", "ps_AF" => "Pashto (Afghanistan)", "so_SO" => "Somali (Somalia)", "sr_Cyrl" => "Serbian (Cyrillic)", "pl_PL" => "Polish (Poland)", "fr_GN" => "French (Guinea)", "om_KE" => "Oromo (Kenya)"];
        $locale_get_default = locale_get_default();
        $countries = "";
        foreach ($locales as $locale => $name) {
            $countries .= "<option value=\"" . $locale . "\" " . (strtolower($locale_get_default) == strtolower($locale) ? "selected=\"\"" : "") . ">" . $name . "</option>";
        }
        echo $aInt->sortableTable(["ID", "Signature", " "], $tabledata);
        $languages = "";
        foreach (\WHMCS\Language\ClientLanguage::getLanguages() as $lang) {
            $languages .= "<option " . ($lang == $CONFIG["Language"] ? "selected=\"\"" : "") . " value=\"" . $lang . "\">" . ucfirst($lang) . "</option>";
        }
        echo "<!-- Modal -->\r\n<style>\r\n.glyphicon-refresh-animate {\r\n    -animation: spin .7s infinite linear;\r\n    -webkit-animation: spin2 .7s infinite linear;\r\n}\r\n\r\n@-webkit-keyframes spin2 {\r\n    from { -webkit-transform: rotate(0deg);}\r\n    to { -webkit-transform: rotate(360deg);}\r\n}\r\n\r\n@keyframes spin {\r\n    from { transform: scale(1) rotate(0deg);}\r\n    to { transform: scale(1) rotate(360deg);}\r\n}\r\n</style>\r\n  <div class=\"modal fade\" id=\"create-new-edit-signature-modal\" role=\"dialog\">\r\n    <div class=\"modal-dialog modal-lg\">\r\n      <div class=\"modal-content\">\r\n      <form action=\"?module=ticket_random_signature&a=save&did=" . $_REQUEST["did"] . "\" method=\"post\" id=\"add_edit_form1\">\r\n        <input type=\"hidden\" value=\"1\" name=\"save\">\r\n        <input type=\"hidden\" value=\"\" id=\"table_id\" name=\"id\">\r\n        <div class=\"modal-header\">\r\n          <button type=\"button\" class=\"close\" data-dismiss=\"modal\">&times;</button>\r\n          <h4 class=\"modal-title\">Add/Edit Signature</h4>\r\n        </div>\r\n        <div class=\"modal-body\">\r\n          <table class=\"form\" width=\"100%\" cellspacing=\"2\" cellpadding=\"3\" border=\"0\">\r\n        <tbody> \r\n        <tr>\r\n            <td class=\"fieldlabel\">Random Name</td>\r\n            <td class=\"fieldarea\">\r\n            <select name=\"locale\" id=\"locale_select\" class=\"form-control select-inline\">" . $countries . "</select>\r\n            <button type=\"button\" style=\"margin-top: -4px\" id=\"generate_new_name\" class=\"btn btn-primary btn-sm\"><span style=\"display: none\" class=\"glyphicon glyphicon-refresh glyphicon-refresh-animate name_generator_loader\"></span>  Generate</button>\r\n            </td>\r\n        </tr>     \r\n        <tr>\r\n            <td class=\"fieldlabel\">Admin Language</td>\r\n            <td class=\"fieldarea\">\r\n                <select name=\"signature_language\" id=\"sign_language_v\" class=\"form-control\">" . $languages . "</select>\r\n            </td>\r\n        </tr>               \r\n        <tr>\r\n            <td class=\"fieldlabel\">Signature</td>\r\n            <td class=\"fieldarea\">\r\n            <textarea class=\"form-control\" rows=\"7\" id=\"signature_box\" name=\"signature\"></textarea>\r\n            </td>\r\n        </tr>                               \r\n    </tbody></table>\r\n    <div class=\"btn-container\">\r\n    <input type=\"submit\" value=\"Save\" class=\"btn btn-primary\">\r\n    <input type=\"button\" value=\"Cancel\"  data-dismiss=\"modal\" class=\"btn btn-default\">\r\n</div>\r\n</form>\r\n        </div>\r\n      </div>\r\n    </div>\r\n</div>";
        echo "        <script>\r\n            \$(document).ready(function () {\r\n                \$('.add-new-signature').click(function () {\r\n                    \$('#add_edit_form1').get(0).reset();\r\n                    \$('#create-new-edit-signature-modal').modal('show');\r\n                });\r\n\r\n                \$('.edit_signature_button').click(function () {\r\n                    var data = JSON.parse(atob(\$(this).data('resource')));\r\n                    \$('#signature_box').val(data.signature).change();\r\n                    \$('#table_id').val(data.id).change();\r\n                    \$(\"#sign_language_v\").val(data.language).change();\r\n                    \$('#create-new-edit-signature-modal').modal('show');\r\n                });\r\n                \$(\"#generate_new_name\").click(function () {\r\n                    \$('.name_generator_loader').show();\r\n                    \$.post(\"addonmodules.php?module=ticket_random_signature&a=generate\",\r\n                        {\r\n                            locale: \$('#locale_select').val()\r\n                        },\r\n                        function (data, status) {\r\n                            \$('#signature_box').val(data);\r\n                            \$('.name_generator_loader').hide();\r\n                        });\r\n                });\r\n\r\n            });\r\n        </script>\r\n        ";
    }
    public function generate()
    {
        ob_clean();
        ob_start();
        require_once ticket_random_signature_root . "/vendor/autoload.php";
        $locale = isset($_REQUEST["locale"]) && $_REQUEST["locale"] ? $_REQUEST["locale"] : locale_get_default();
        $faker = \Faker\Factory::create($locale);
        if(strpos($locale, "en_") !== false) {
            $thanks = ["Best regards", "Kind regards", "Sincerely", "Warm regards", "Regards", "Yours sincerely", "With gratitude", "Respectfully", "Many thanks", "Yours faithfully", "With appreciation", "All the best", "Thank you", "With warmest regards", "Take care", "Have a great day", "Best wishes", "With utmost respect", "Cordially", "With sincere appreciation", "Sending positive vibes"];
            echo $thanks[rand(0, count($thanks) - 1)] . "," . PHP_EOL;
        }
        echo $faker->firstName . " " . $faker->lastName . PHP_EOL;
        if(strpos($locale, "en_") !== false) {
            $titles = ["Support Specialist", "Technical Support Representative", "Customer Support Agent", "Help Desk Analyst", "Ticket Support Associate", "Support Team Member", "Client Assistance Specialist", "Customer Care Representative", "Ticket Resolution Expert", "Support Operations Coordinator", "Support Engineer", "Technical Assistance Representative", "Customer Service Advocate", "Troubleshooting Specialist", "Support Ticket Administrator", "Client Solutions Consultant", "Escalation Support Agent", "Product Support Analyst", "Technical Account Manager", "Support Representative", "Service Desk Technician", "Client Support Specialist", "Problem Resolution Specialist", "Ticketing System Administrator", "Support Coordinator", "Help Desk Supervisor", "Customer Service Representative", "IT Support Analyst", "Support Analyst", "Client Relations Manager", "Customer Success Specialist"];
            echo $titles[rand(0, count($titles) - 1)];
        }
        exit;
    }
    public function save()
    {
        if(isset($_POST["id"]) && isset($_REQUEST["did"])) {
            if(isset($_REQUEST["id"]) && $_REQUEST["id"]) {
                \Illuminate\Database\Capsule\Manager::table("nnm_trs")->where("id", $_REQUEST["id"])->update(["signature" => $_REQUEST["signature"], "language" => $_REQUEST["signature_language"]]);
                redir("module=ticket_random_signature&a=show&saved=1&did=" . $_REQUEST["did"]);
            } else {
                \Illuminate\Database\Capsule\Manager::table("nnm_trs")->insert(["rel_id" => $_REQUEST["did"], "language" => $_REQUEST["signature_language"], "signature" => $_REQUEST["signature"]]);
                redir("module=ticket_random_signature&a=show&added=1&did=" . $_REQUEST["did"]);
            }
        } else {
            redir("module=ticket_random_signature");
        }
    }
    public function saveDepartment()
    {
        if(isset($_REQUEST["did"])) {
            $settings = $_REQUEST["settings"];
            foreach ($settings as $setting => $value) {
                \Illuminate\Database\Capsule\Manager::table("nnm_trs_settings")->updateOrInsert(["setting" => $setting], ["setting" => $setting, "value" => $value]);
            }
            redir("module=ticket_random_signature&a=show&saved=1&did=" . $_REQUEST["did"]);
        } else {
            redir("module=ticket_random_signature");
        }
    }
}
?>