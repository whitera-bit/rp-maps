<?php
define('IN_MYBB', 1);
define('THIS_SCRIPT', 'maps.php');

require_once "global.php";
require_once "inc/class_parser.php";

$lang->load('rpmaps');

if (!$db->table_exists('maps') && !$db->table_exists('locations')) redirect('index.php');

$maps_query = $db->query("
    SELECT *
    FROM " . TABLE_PREFIX . "maps
");

if ($db->num_rows($maps_query) < 1) redirect('index.php');

$parser = new postParser();
$parser_options = array(
    "allow_html" => 0, // instructions: to allow html change to 1
    "allow_mycode" => 1,
    "allow_smilies" => 0,
    "allow_imgcode" => 0,
    "filter_badwords" => 0,
    "nl2br" => 1,
    "allow_videocode" => 0,
);

$uid = $mybb->user['uid'];
$mid = isset($_REQUEST['mid']) ? $_REQUEST['mid'] : false;

if (!$mybb->get_input('action') && !$mid) {
    $visibility_value = $uid ? "" : "AND visibility != '0'";

    $maps_query_teamonly = $db->query("
        SELECT *
        FROM " . TABLE_PREFIX . "maps
        WHERE visibility != '2'
        " . $visibility_value . "
    ");

    $map_query = $mybb->usergroup['canmodcp'] ? $maps_query : $maps_query_teamonly;
    $map_bit = "";

    while ($maps = $db->fetch_array($map_query)) {
        eval("\$map_bit .= \"".$templates->get("maps_bit")."\";");
    }

    eval("\$page = \"".$templates->get("maps")."\";");
    output_page($page);
}

while ($map = $db->fetch_array($maps_query)) {
    $slug = $map['slug'];
    $name = $map['name'];

    if (!$mybb->get_input('action') && $mid == $slug) {
        add_breadcrumb($lang->maps, "maps.php");
        add_breadcrumb($name);

        if (($map['visibility'] == 0 && $uid == 0) || ($map['visibility'] == 2 && !$mybb->usergroup['canmodcp'])) {
            redirect("maps.php");
        }
        else {
            $all_locations_query = $db->query("
                SELECT *
                FROM " . TABLE_PREFIX . "locations
                WHERE mid = '" . $map['mid'] . "'
            ");
            $notall_locations_query = $db->query("
                SELECT *
                FROM " . TABLE_PREFIX . "locations
                WHERE accepted != '0'
                AND mid = '" . $map['mid'] . "'
            ");

            $locations_query = $mybb->usergroup['canmodcp'] ? $all_locations_query : $notall_locations_query;

            $newaddress = ($map['suggestions'] && $mybb->user['uid'] != 0) ? "<a href='maps.php?action=map_new&mapid=" . $map['mid'] . "' id='newaddress'>{$lang->map_button_new}</a>" : "";

            $map_type = strpos($map['image'], '.svg') !== false ? 'image/svg+xml' : (strpos($map['image'], '.png') !== false ? 'image/png' : 'image/jpg');
            $map_object = '<object data="' . $map['image'] . '" type="' . $map_type . '" style="opacity: 1; width: 100%;" id="mapimage"><img src="' . $map['image'] . '" /></object><div id="mapevents"></div>';
            $map['desc'] = $parser->parse_message($map['desc'], $parser_options);

            $added_query = $db->query("
                SELECT *
                FROM " . TABLE_PREFIX . "locations
                WHERE accepted = '0'
                AND uid = '" . $uid . "'
            ");
            $added_count = $db->num_rows($added_query);

            $lang->map_added = $lang->sprintf($lang->map_added, $added_count);

            $added = $added_count > 0 ? "<div class='success' id='message'>{$lang->map_added}</div>" : "";

            $address_script = "<script type='text/javascript' src='{$mybb->asset_url}/jscripts/rpmaplocation.min.js'></script>";

            $locations_bit = "";

            while ($location = $db->fetch_array($locations_query)) {
                $lid = $location['lid'];

                $locationclass = $location['accepted'] ? '' : 'draft';

                $options = $mybb->usergroup['canmodcp'] ? "<a href='maps.php?action=map_edit&lid=$lid'>{$lang->map_button_edit}</a> <a href='maps.php?action=map_delete&lid=$lid'>{$lang->map_button_delete}</a>" : "";

                $location['desc'] = $parser->parse_message($location['desc'], $parser_options);

                $location['xcoord'] = $location['xcoord'] / 100;
                $location['ycoord'] = $location['ycoord'] / 100;

                $type = $location['details'] ? 'normal' : 'short';

                $residents_query = explode(',', $location['residents']);

                $bit = "";

                foreach ($residents_query as $resident) {
                    if ($resident !== '') {
                        $resident_character = $db->fetch_array($db->simple_select("users", "*", "username = '$resident'"));

                        $charactername = $resident_character ? '<a href="member.php?action=profile&uid=' . $resident_character['uid'] . '">' . $resident_character['username'] . '</a>' : $resident;

                        eval("\$bit .= \"".$templates->get("maps_map_bit_normal_bit")."\";");
                    }
                }

                eval("\$locations_bit .= \"".$templates->get("maps_map_bit_" . $type . "")."\";");
            }

            eval("\$page = \"".$templates->get("maps_map")."\";");
            output_page($page);
        }
    }
}

if ($mybb->get_input('action') == "map_new") {
    $current_mid = isset($_REQUEST['mapid']) ? $_REQUEST['mapid'] : 1;
    $map = $db->fetch_array($db->simple_select("maps", "*", "mid = '" . $current_mid . "'"));

    add_breadcrumb($lang->maps, "maps.php");
    add_breadcrumb($map['name'], "maps.php?mid=" . $map['slug']);
    add_breadcrumb($lang->map_new);

    $icon_options = '';
    $dir = "./images/map";
    $files = array_values(array_diff(scandir($dir), array('..', '.')));
    for ($file = 0; $file < count($files); $file++) {
        $icon_options .= "<input type='radio' name='icon' id='{$file}' value='{$files[$file]}' /><label for='{$file}'><img src=\"{$theme['imgdir']}/map/{$files[$file]}\"/></label>";
    }

    $address_script = "<script type='text/javascript' src='{$mybb->asset_url}/jscripts/rpmaplocationinput.min.js'></script>";

    if ($map['suggestions'] && $mybb->user['uid'] != 0) {
        if ($mybb->usergroup['canmodcp']) {
            $new_record = array(
                "uid" => (int) $mybb->user['uid'],
                "mid" => (int) $mybb->get_input('mid'),
                "name" => $db->escape_string($mybb->get_input('name')),
                "address" => $db->escape_string($mybb->get_input('address')),
                "details" => (int) $mybb->get_input('details'),
                "residents" => $db->escape_string($mybb->get_input('residents')),
                "icon" => $db->escape_string($mybb->get_input('icon')),
                "xcoord" => $db->escape_string($mybb->get_input('xcoord')),
                "ycoord" => $db->escape_string($mybb->get_input('ycoord')),
                "desc" => $db->escape_string($mybb->get_input('desc')),
                "accepted" => 1
            );
        }
        else {
            $new_record = array(
                "uid" => (int) $mybb->user['uid'],
                "mid" => (int) $mybb->get_input('mid'),
                "name" => $db->escape_string($mybb->get_input('name')),
                "address" => $db->escape_string($mybb->get_input('address')),
                "details" => (int) $mybb->get_input('details'),
                "residents" => $db->escape_string($mybb->get_input('residents')),
                "icon" => $db->escape_string($mybb->get_input('icon')),
                "xcoord" => $db->escape_string($mybb->get_input('xcoord')),
                "ycoord" => $db->escape_string($mybb->get_input('ycoord')),
                "desc" => $db->escape_string($mybb->get_input('desc')),
                "accepted" => 0
            );
        }

        if (isset($mybb->input['submitnewaddress'])) {
            $themid = (int) $mybb->get_input('mid');
            $mapslug = $db->fetch_field($db->simple_select("maps", "*", "mid = '" . $themid . "'"), "slug");

            $db->insert_query("locations", $new_record);
            redirect("maps.php?mid=" . $mapslug);
        }

        eval("\$page = \"".$templates->get("maps_new")."\";");
        output_page($page);
    }
    else {
        redirect("maps.php?mid=" . $map['slug']);
    }
}

if ($mybb->get_input('action') == "map_edit") {
    $lid = $_REQUEST['lid'];
    $location = $db->fetch_array($db->simple_select("locations", "*", "lid = '$lid'"));
    $map = $db->fetch_array($db->simple_select("maps", "*", "mid = '" . $location['mid'] . "'"));

    add_breadcrumb($lang->maps, "maps.php");
    add_breadcrumb($map['name'], "maps.php?mid=" . $map['slug']);
    add_breadcrumb($lang->map_edit);

    $detailscheck = $location['details'] == '1' ? "checked" : "";

    $icon_options = '';
    $dir = "./images/map";
    $files = array_values(array_diff(scandir($dir), array('..', '.')));
    for ($file = 0; $file < count($files); $file++) {
        $checked = ($location['icon'] == $files[$file]) ? "checked" : "";
        $icon_options .= "<input type='radio' name='icon' id='{$file}' value='{$files[$file]}' $checked /><label for='{$file}'><img src=\"{$theme['imgdir']}/map/{$files[$file]}\"/></label>";
    }

    if ($mybb->usergroup['canmodcp']) {
        $lid = $location['lid'];

        if (isset($mybb->input['submiteditaddress'])) {
            $lid = $mybb->get_input('lid');

            $new_record = array(
                "name" => $db->escape_string($mybb->get_input('name')),
                "address" => $db->escape_string($mybb->get_input('address')),
                "details" => (int) $mybb->get_input('details'),
                "residents" => $db->escape_string($mybb->get_input('residents')),
                "icon" => $db->escape_string($mybb->get_input('icon')),
                "xcoord" => $db->escape_string($mybb->get_input('xcoord')),
                "ycoord" => $db->escape_string($mybb->get_input('ycoord')),
                "desc" => $db->escape_string($mybb->get_input('desc'))
            );

            $db->update_query("locations", $new_record, "lid = '$lid'");
            redirect("maps.php?mid=" . $map['slug']);
        }

        eval("\$page = \"".$templates->get("maps_edit")."\";");
        output_page($page);
    }
    else {
        redirect("maps.php?mid=" . $map['slug']);
    }
}

if ($mybb->get_input('action') == "map_delete") {
    $lid = $_REQUEST['lid'];
    $location = $db->fetch_array($db->simple_select("locations", "*", "lid = '$lid'"));
    $mapslug = $db->fetch_field($db->simple_select("maps", "*", "mid = '" . $location['mid'] . "'"), "slug");

    if ($mybb->usergroup['canmodcp']) {
        $db->delete_query("locations", "lid = '$lid'");
        redirect("maps.php?mid=" . $mapslug);
    }
    else {
        redirect("maps.php?mid=" . $mapslug);
    }
}
