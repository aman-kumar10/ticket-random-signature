<?php
namespace WHMCS\Module\Addon\Ticket_random_signature\Adminarea;

if(!defined("WHMCS")) {
    exit("This file cannot be accessed directly");
}
class ExcludeController
{
    public function index()
    {
        if(isset($_REQUEST["delete_id"])) {
            \Illuminate\Database\Capsule\Manager::table("nnm_trs_ex")->where("id", $_REQUEST["delete_id"])->delete();
            redir("module=ticket_random_signature&c=exclude");
        }
        global $aInt;
        global $page;
        global $numrows;
        global $limit;
        $aInt->sortableTableInit("id");
        $numrows = \Illuminate\Database\Capsule\Manager::table("nnm_trs_ex")->count();
        $limit = $aInt->rowLimit;
        $records = $page * $limit;
        $tabledata = [];
        $result = \Illuminate\Database\Capsule\Manager::table("nnm_trs_ex")->leftJoin("tbladmins", "tbladmins.id", "=", "nnm_trs_ex.admin_id")->skip($records)->take($limit)->select("nnm_trs_ex.id", "tbladmins.firstname", "tbladmins.lastname")->orderBy("nnm_trs_ex.id", "DESC");
        $result = $result->get();
        $aInt->deleteJSConfirm("doDelete", "global", "deleteconfirm", "?module=ticket_random_signature&c=exclude&delete_id=");
        echo "        <a href=\"#\" class=\"btn btn-default add-new-admin\"><i class=\"fas fa-plus\"></i> Add New</a>\r\n        ";
        $admin_select = "";
        foreach (\Illuminate\Database\Capsule\Manager::table("tbladmins")->select("firstname", "lastname", "id")->get() as $admin) {
            $admin_select .= "<option value=\"" . $admin->id . "\">" . $admin->firstname . " " . $admin->lastname . "</option>";
        }
        echo "<!-- Modal -->\r\n        <div class=\"modal fade\" id=\"create-new-edit-admin-modal\" role=\"dialog\">\r\n            <div class=\"modal-dialog modal-lg\">\r\n                <div class=\"modal-content\">\r\n                    <form action=\"?module=ticket_random_signature&c=exclude&a=save\" method=\"post\" id=\"add_edit_form1\">\r\n                        <input type=\"hidden\" value=\"1\" name=\"save\">\r\n                        <input type=\"hidden\" value=\"\" id=\"table_id\" name=\"id\">\r\n                        <div class=\"modal-header\">\r\n                            <button type=\"button\" class=\"close\" data-dismiss=\"modal\">&times;</button>\r\n                            <h4 class=\"modal-title\">Add/Edit Exclude Admin</h4>\r\n                        </div>\r\n                        <div class=\"modal-body\">\r\n                            <table class=\"form\" width=\"100%\" cellspacing=\"2\" cellpadding=\"3\" border=\"0\">\r\n                                <tbody>\r\n                                <tr>\r\n                                    <td class=\"fieldlabel\">Admin</td>\r\n                                    <td class=\"fieldarea\">\r\n                                        <select name=\"admin_id\" class=\"form-control\">" . $admin_select . "</select>\r\n                                    </td>\r\n                                </tr>\r\n                                </tbody></table>\r\n                            <div class=\"btn-container\">\r\n                                <input type=\"submit\" value=\"Save\" class=\"btn btn-primary\">\r\n                                <input type=\"button\" value=\"Cancel\"  data-dismiss=\"modal\" class=\"btn btn-default\">\r\n                            </div>\r\n                    </form>\r\n                </div>\r\n            </div>\r\n        </div>\r\n        </div>";
        echo "        <script>\r\n            \$(document).ready(function () {\r\n                \$('.add-new-admin').click(function () {\r\n                    \$('#add_edit_form1').get(0).reset();\r\n                    \$('#create-new-edit-admin-modal').modal('show');\r\n                });\r\n\r\n            });\r\n        </script>\r\n        ";
        foreach ($result as $data) {
            $tabledata[] = ["<center>" . $data->id . "</center>", "<center>" . $data->firstname . " " . $data->lastname . "</center>", "<center><a href=\"#\" onclick=\"doDelete(" . $data->id . ");\" class=\"btn btn-sm btn-default\" ><i class=\"fas fa-trash\"></i> Delete</a></center>"];
        }
        echo $aInt->sortableTable(["ID", "Admin", " "], $tabledata);
    }
    public function save()
    {
        if(!isset($_REQUEST["save"])) {
            redir("module=ticket_random_signature&c=exclude");
        }
        \Illuminate\Database\Capsule\Manager::table("nnm_trs_ex")->updateOrInsert(["admin_id" => $_REQUEST["admin_id"]], ["admin_id" => $_REQUEST["admin_id"]]);
        redir("module=ticket_random_signature&c=exclude&saved=1");
    }
}
?>