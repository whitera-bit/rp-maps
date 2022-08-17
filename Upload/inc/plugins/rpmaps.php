<?php

if (!defined("IN_MYBB")) die("Direct initialization of this file is not allowed.");
if (!defined("PLUGINLIBRARY")) define("PLUGINLIBRARY", MYBB_ROOT . "inc/plugins/pluginlibrary.php");

function rpmaps_info() {
    global $lang;
    $lang->load('rpmaps');

    return array(
        "name"          => "Roleplay Maps",
        "description"   => $lang->plugin_desc,
        "website"       => "https://github.com/venomnomous/rp-maps",
        "author"        => "Venomous",
        "authorsite"    => "https://github.com/venomnomous",
        "version"       => "1.0",
        "codename"      => "rpmaps",
        "compatibility" => "18*"
    );
}

function rpmaps_install() {
    global $db, $lang;
    $lang->load('rpmaps');

    $db->query("CREATE TABLE " . TABLE_PREFIX . "maps (
        `mid` int(10) NOT NULL AUTO_INCREMENT,
        `fid` int(10) NOT NULL,
        `name` varchar(255) NOT NULL,
        `slug` varchar(255) NOT NULL,
        `desc` text NOT NULL DEFAULT '',
        `image` varchar(200) NOT NULL,
        `visibility` int(10) NOT NULL DEFAULT 1,
        `suggestions` int(10) NOT NULL DEFAULT 1,
        PRIMARY KEY (`mid`),
        KEY `mid` (`mid`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=1");

    $db->query("CREATE TABLE " . TABLE_PREFIX . "locations (
        `lid` int(10) NOT NULL AUTO_INCREMENT,
        `uid` int(10) NOT NULL,
        `mid` int(10) NOT NULL,
        `name` varchar(255) NOT NULL,
        `address` varchar(255) NOT NULL,
        `details` int(10) NOT NULL DEFAULT 1,
        `residents` varchar(255) NOT NULL DEFAULT '',
        `icon` varchar(255) NOT NULL,
        `xcoord` int(10) NOT NULL,
        `ycoord` int(10) NOT NULL,
        `desc` text NOT NULL DEFAULT '',
        `accepted` int(10) NOT NULL DEFAULT 0,
        PRIMARY KEY (`lid`),
        KEY `lid` (`lid`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=1");

    $template_group = array(
        'prefix' => $db->escape_string("maps"),
        'title' => $db->escape_string($lang->template_title),
        'isdefault' => 0
    );

    $db->insert_query('templategroups', $template_group);

    require_once MYBB_ROOT . "inc/plugins/rpmaps/rpm_templates_css.php";

    rpm_templates_add();
    rpm_css_add();
}

function rpmaps_is_installed() {
    global $db;

    if ($db->table_exists("maps") && $db->table_exists("locations")) return true;
    return false;
}

function rpmaps_uninstall() {
    global $db;

    if ($db->table_exists("maps")) $db->drop_table("maps");
    if ($db->table_exists("locations")) $db->drop_table("locations");

    $db->delete_query("templategroups", "prefix = 'maps'");
    $db->delete_query("templates", "title LIKE 'maps%'");

    require_once MYBB_ADMIN_DIR . "inc/functions_themes.php";

    $db->delete_query("themestylesheets", "name = 'rpm.css'");
    $query = $db->simple_select("themes", "tid");

    while($theme = $db->fetch_array($query)) {
        update_theme_stylesheet_list($theme['tid']);
    }
}

function rpmaps_activate() {
    global $lang, $PL;
    $lang->load('rpmaps');

    if (!file_exists(PLUGINLIBRARY)) {
        flash_message($lang->error_pluginlibrary, "error");
        admin_redirect("index.php?module=config-plugins");
    }

    $PL or require_once PLUGINLIBRARY;

    $PL->edit_core('rpm', 'inc/functions_forumlist.php', array(
        array(
            'search' => 'eval("\$forum_list .= \"".$templates->get("forumbit_depth$depth$forumcat")."\";");',
            'replace' => '$mapfid = $db->fetch_field($db->simple_select("maps", "*", "fid = \'" . $forum[\'fid\'] . "\'"), "fid");

            if ($mapfid == $forum[\'fid\']) {
                eval("\$forum_list .= \"".$templates->get("maps_minimap")."\";");
            }
            else {
                eval("\$forum_list .= \"".$templates->get("forumbit_depth$depth$forumcat")."\";");
            }'
        )
    ), true);

    require_once MYBB_ROOT . "inc/adminfunctions_templates.php";

    find_replace_templatesets('header', '#'.preg_quote('{$menu_memberlist}').'#i', '{$menu_memberlist}{$menu_maps}');
    find_replace_templatesets('header', '#'.preg_quote('{$modnotice}').'#i', '{$modnotice}{$maps_modcp}');
    find_replace_templatesets("modcp_nav", "#".preg_quote('{$modcp_nav_users}').'#i', '{$modcp_nav_users}{$modcp_nav_maps}');
}

function rpmaps_deactivate() {
    global $lang, $PL;
    $lang->load('rpmaps');

    if (!file_exists(PLUGINLIBRARY)) {
        flash_message($lang->error_pluginlibrary, "error");
        admin_redirect("index.php?module=config-plugins");
    }

    $PL or require_once PLUGINLIBRARY;

    $PL->edit_core('rpm', 'inc/functions_forumlist.php', array(), true);

    require_once MYBB_ROOT . "inc/adminfunctions_templates.php";

    find_replace_templatesets("header", "#".preg_quote('{$menu_maps}')."#i", '', 0);
    find_replace_templatesets("header", "#".preg_quote('{$maps_modcp}')."#i", '', 0);
    find_replace_templatesets("modcp_nav", "#".preg_quote('{$modcp_nav_maps}')."#i", '', 0);
}

$plugins->add_hook("global_intermediate", "add_header_menu");
function add_header_menu() {
    global $db, $mybb, $lang, $templates, $menu_maps;
    $lang->load('rpmaps');

    eval("\$menu_maps = \"".$templates->get("maps_header_menu")."\";");
}

$plugins->add_hook("global_intermediate", "add_modcp_notice");
function add_modcp_notice() {
    global $mybb, $db, $lang, $templates, $theme, $maps_modcp;
    $lang->load('rpmaps');

    $mapquery = $db->query("
        SELECT *
        FROM " . TABLE_PREFIX . "locations
        WHERE accepted = '0'
    ");
    $mapcount = $db->num_rows($mapquery);

    $lang->notifications_modcp_locations = $lang->sprintf($lang->notifications_modcp_locations, $mapcount);

    $maps_modcp .= ($mapcount > 0 && $mybb->usergroup['canmodcp']) ? "<div class='red_alert'><a href='modcp.php?action=maps'>{$lang->notifications_modcp_locations}</a></div>" : "";
}

$plugins->add_hook("build_forumbits_forum", "forumbit_map");
function forumbit_map(&$forum) {
    global $db, $mybb, $lang, $templates, $plugins, $theme;
    $lang->load('rpmaps');

    $minimap = $db->fetch_array($db->query("
        SELECT *
        FROM " . TABLE_PREFIX . "maps
        WHERE fid = '" . $forum['fid'] . "'
    "));

    $forum['minimap'] = $minimap['image'];
    $forum['maplink'] = "<a href='maps.php?mid=" . $minimap['slug'] . "'>{$lang->map_maplink}</a>";
}

$plugins->add_hook("admin_config_action_handler", "custommaps_admin_config_action_handler");
function custommaps_admin_config_action_handler(&$actions) {
    $actions['maps'] = array('active' => 'maps', 'file' => 'maps');
}

$plugins->add_hook("admin_config_permissions", "custommaps_admin_config_permissions");
function custommaps_admin_config_permissions(&$admin_permissions) {
    global $lang;
    $lang->load('rpmaps');

    $admin_permissions['maps'] = $lang->acp_maps_settings;
}

$plugins->add_hook("admin_config_menu", "custommaps_admin_config_menu");
function custommaps_admin_config_menu(&$sub_menu) {
    global $lang;
    $lang->load('rpmaps');

    $sub_menu[] = [
        "id" => "maps",
        "title" => $lang->acp_maps,
        "link" => "index.php?module=config-maps"
    ];
}

$plugins->add_hook("admin_load", "custommaps_load");
function custommaps_load() {
    global $mybb, $db, $lang, $page, $run_module, $action_file, $errors;
    $lang->load('rpmaps');

    if ($page->active_action != 'maps')  return false;

    if ($run_module == 'config' && $action_file == 'maps') {
        if ($mybb->input['action'] == "" || !isset($mybb->input['action'])) {
            $page->add_breadcrumb_item($lang->acp_maps);
            $page->output_header($lang->acp_maps);

            $sub_tabs['maps'] = [
                "title" => $lang->acp_maps,
                "link" => "index.php?module=config-maps",
                "description" => $lang->acp_maps_desc
            ];
            $sub_tabs['maps_add'] = [
                "title" => $lang->acp_maps_new,
                "link" => "index.php?module=config-maps&amp;action=add_map",
                "description" => $lang->acp_maps_new_desc
            ];
            $page->output_nav_tabs($sub_tabs, 'maps');

            if (isset($errors))  $page->output_inline_error($errors);

            $form = new Form("index.php?module=config-maps", "post");
            $form_container = new FormContainer($lang->acp_maps);
            $form_container->output_row_header($lang->acp_maps_fields_name);
            $form_container->output_row_header($lang->acp_maps_fields_slug);
            $form_container->output_row_header($lang->acp_maps_fields_fid);
            $form_container->output_row_header($lang->acp_maps_fields_desc);
            $form_container->output_row_header($lang->acp_maps_fields_image);
            $form_container->output_row_header($lang->acp_maps_fields_visibility);
            $form_container->output_row_header($lang->acp_maps_fields_suggestions);
            $form_container->output_row_header($lang->acp_maps_fields_options);

            $mapquery = $db->simple_select("maps", "*", "", ["order_by" => 'mid', 'order_dir' => 'ASC']);
            while ($map = $db->fetch_array($mapquery)) {
                $form_container->output_cell('<strong>'.htmlspecialchars_uni($map['name']).'</strong>');
                $form_container->output_cell('<em>'.htmlspecialchars_uni($map['slug']).'</em>');
                $form_container->output_cell(htmlspecialchars_uni($map['fid']));
                $form_container->output_cell(htmlspecialchars_uni($map['desc']));
                $form_container->output_cell(htmlspecialchars_uni($map['image']));
                $form_container->output_cell(htmlspecialchars_uni($map['visibility']));
                $form_container->output_cell(htmlspecialchars_uni($map['suggestions']));
                $popup = new PopupMenu("maps_{$map['mid']}", $lang->acp_maps_edit);
                $popup->add_item(
                    $lang->acp_maps_edit,
                    "index.php?module=config-maps&amp;action=edit_map&amp;mid={$map['mid']}"
                );
                $popup->add_item(
                    $lang->acp_maps_delete,
                    "index.php?module=config-maps&amp;action=delete_map&amp;mid={$map['mid']}"
                    ."&amp;my_post_key={$mybb->post_code}"
                );
                $form_container->output_cell($popup->fetch(), array("class" => "align_center"));
                $form_container->construct_row();
            }

            $form_container->end();
            $form->end();
            $page->output_footer();
            exit;
        }

        if ($mybb->input['action'] == "add_map") {
            if ($mybb->request_method == "post") {
                if (empty($mybb->input['name'])) $errors[] = $lang->acp_maps_error1;
                if (empty($mybb->input['image'])) $errors[] = $lang->acp_maps_error2;

                if (empty($errors)) {
                    $name = $db->escape_string($mybb->input['name']);

                    $searchArr = array('/ß/','/Ä/','/Ö/','/Ü/','/ä/','/ö/','/ü/', '/\s+/');
                    $replaceArr = array('sz','Ae','Oe','Ue','ae','oe','ue', '');
                    $slugValue = strtolower(preg_replace($searchArr, $replaceArr, $mybb->input['name']));
                    $slug = $db->escape_string(preg_replace("/[^0-9a-zA-Z-]/", "", $slugValue));

                    $fid = $db->escape_string($mybb->input['fid']);
                    $desc = $db->escape_string($mybb->input['desc']);
                    $image = $db->escape_string($mybb->input['image']);
                    $visibility = $db->escape_string($mybb->input['visibility']);
                    $suggestions = $db->escape_string($mybb->input['suggestions']);

                    add_map($name, $slug, $fid, $desc, $image, $visibility, $suggestions);

                    $mybb->input['module'] = "maps";
                    $mybb->input['action'] = $lang->acp_maps_success;
                    log_admin_action(htmlspecialchars_uni($mybb->input['name']));

                    flash_message($lang->acp_maps_success, 'success');
                    admin_redirect("index.php?module=config-maps");
                }
            }

            $page->add_breadcrumb_item($lang->acp_maps_add);
            $page->output_header($lang->acp_maps);

            $sub_tabs['maps'] = [
                "title" => $lang->acp_maps,
                "link" => "index.php?module=config-maps",
                "description" => $lang->acp_maps_desc
            ];
            $sub_tabs['maps_add'] = [
                "title" => $lang->acp_maps_new,
                "link" => "index.php?module=config-maps&amp;action=add_map",
                "description" => $lang->acp_maps_new_desc
            ];
            $page->output_nav_tabs($sub_tabs, 'maps_add');

            if(isset($errors)) $page->output_inline_error($errors);

            $form = new Form("index.php?module=config-maps&amp;action=add_map", "post", "", 1);
            $form_container = new FormContainer($lang->acp_maps_add);
            $form_container->output_row($lang->acp_maps_fields_name . "<em>*</em>", $lang->acp_maps_fields_name_desc, $form->generate_text_box('name', $mybb->input['name']));

            $searchArr = array('/ß/','/Ä/','/Ö/','/Ü/','/ä/','/ö/','/ü/', '/\s+/');
            $replaceArr = array('sz','Ae','Oe','Ue','ae','oe','ue', '');
            $slugValue = strtolower(preg_replace($searchArr, $replaceArr, $mybb->input['name']));

            $form_container->output_row("<em>" . $lang->acp_maps_fields_slug . "</em>", $lang->acp_maps_fields_slug_desc, $form->generate_hidden_field('slug', preg_replace("/[^0-9a-zA-Z-]/", "", $slugValue)));
            $form_container->output_row($lang->acp_maps_fields_fid, $lang->acp_maps_fields_fid_desc, $form->generate_text_box('fid', $mybb->input['fid']));
            $form_container->output_row($lang->acp_maps_fields_desc, $lang->acp_maps_fields_desc_desc, $form->generate_text_area('desc', $mybb->input['desc']));
            $form_container->output_row($lang->acp_maps_fields_image . "<em>*</em>", $lang->acp_maps_fields_image_desc, $form->generate_text_box('image', $mybb->input['image']));

            $form_container->output_row($lang->acp_maps_fields_visibility, $lang->acp_maps_fields_visibility_desc, $form->generate_select_box('visibility', array(
                "0" => $lang->acp_maps_fields_visibility1,
                "1" => $lang->acp_maps_fields_visibility2,
                "2" => $lang->acp_maps_fields_visibility3
            ), $mybb->input['visibility'], array('id' => 'visibility')), 'visibility');
            $form_container->output_row($lang->acp_maps_fields_suggestions, $lang->acp_maps_fields_suggestions_desc, $form->generate_yes_no_radio('suggestions', $mybb->get_input('suggestions')));
            $form_container->end();
            $buttons[] = $form->generate_submit_button($lang->acp_maps_add);
            $form->output_submit_wrapper($buttons);
            $form->end();
            $page->output_footer();
            exit;
        }

        if ($mybb->input['action'] == "edit_map") {
            if ($mybb->request_method == "post") {
                if (empty($mybb->input['name'])) $errors[] = $lang->acp_maps_error1;
                if (empty($mybb->input['image'])) $errors[] = $lang->acp_maps_error2;

                if (empty($errors)) {
                    $mid = $mybb->get_input('mid', MyBB::INPUT_INT);

                    $name = $db->escape_string($mybb->input['name']);

                    $searchArr = array('/ß/','/Ä/','/Ö/','/Ü/','/ä/','/ö/','/ü/', '/\s+/');
                    $replaceArr = array('sz','Ae','Oe','Ue','ae','oe','ue', '');
                    $slugValue = strtolower(preg_replace($searchArr, $replaceArr, $mybb->input['name']));
                    $slug = $db->escape_string(preg_replace("/[^0-9a-zA-Z-]/", "", $slugValue));

                    $fid = $db->escape_string($mybb->input['fid']);
                    $desc = $db->escape_string($mybb->input['desc']);
                    $image = $db->escape_string($mybb->input['image']);
                    $visibility = $db->escape_string($mybb->input['visibility']);
                    $suggestions = $db->escape_string($mybb->input['suggestions']);

                    edit_map($mid, $name, $slug, $fid, $desc, $image, $visibility, $suggestions);

                    $mybb->input['module'] = "maps";
                    $mybb->input['action'] = $lang->acp_maps_success2;
                    log_admin_action(htmlspecialchars_uni($mybb->input['name']));

                    flash_message($lang->acp_maps_success2, 'success');
                    admin_redirect("index.php?module=config-maps");
                }
            }
            
            $page->add_breadcrumb_item($lang->acp_maps_edit);
            $page->output_header($lang->acp_maps);

            $sub_tabs['maps'] = [
                "title" => $lang->acp_maps,
                "link" => "index.php?module=config-maps",
                "description" => $lang->acp_maps_desc
            ];
            $page->output_nav_tabs($sub_tabs, 'maps'); 

            if(isset($errors))  $page->output_inline_error($errors);

            $mid = $mybb->get_input('mid', MyBB::INPUT_INT);
            $mapsquery = $db->simple_select("maps", "*", "mid={$mid}");
            $map = $db->fetch_array($mapsquery);

            $form = new Form("index.php?module=config-maps&amp;action=edit_map", "post", "", 1);
            echo $form->generate_hidden_field('mid', $mid);
            $form_container = new FormContainer($lang->acp_maps_edit);
            $form_container->output_row($lang->acp_maps_fields_name . "<em>*</em>", $lang->acp_maps_fields_name_desc, $form->generate_text_box('name', htmlspecialchars_uni($map['name'])));
            $form_container->output_row("<em>" . $lang->acp_maps_fields_slug . "</em>", $lang->acp_maps_fields_slug_desc, $form->generate_hidden_field('slug', htmlspecialchars_uni($map['slug'])));
            $form_container->output_row($lang->acp_maps_fields_fid, $lang->acp_maps_fields_fid_desc, $form->generate_text_box('fid', htmlspecialchars_uni($map['fid'])));
            $form_container->output_row($lang->acp_maps_fields_desc, $lang->acp_maps_fields_desc_desc, $form->generate_text_area('desc', htmlspecialchars_uni($map['desc'])));
            $form_container->output_row($lang->acp_maps_fields_image . "<em>*</em>", $lang->acp_maps_fields_image_desc, $form->generate_text_box('image', htmlspecialchars_uni($map['image'])));
            $form_container->output_row($lang->acp_maps_fields_visibility, $lang->acp_maps_fields_visibility_desc, $form->generate_select_box('visibility', array(
                "0" => $lang->acp_maps_fields_visibility1,
                "1" => $lang->acp_maps_fields_visibility2,
                "2" => $lang->acp_maps_fields_visibility3
            ), htmlspecialchars_uni($map['visibility']), array('id' => 'visibility')), 'visibility');
            $form_container->output_row($lang->acp_maps_fields_suggestions,$lang->acp_maps_fields_suggestions_desc, $form->generate_yes_no_radio('suggestions', htmlspecialchars_uni($map['suggestions'])));
            $form_container->end();
            $buttons[] = $form->generate_submit_button($lang->acp_maps_edit);
            $form->output_submit_wrapper($buttons);
            $form->end();
            $page->output_footer();
            exit;
        }

        if ($mybb->input['action'] == "delete_map") {
            $mid = $mybb->get_input('mid', MyBB::INPUT_INT);
            $mapquery = $db->simple_select("maps", "*", "mid={$mid}");
            $map = $db->fetch_array($mapquery);

            if (empty($mid)) {
                flash_message($lang->acp_maps_error3, 'error');
                admin_redirect("index.php?module=config-maps");
            }
            if (isset($mybb->input['no']) && $mybb->input['no']) admin_redirect("index.php?module=config-maps");
            if (!verify_post_check($mybb->input['my_post_key'])) {
                flash_message($lang->invalid_post_verify_key2, 'error');
                admin_redirect("index.php?module=config-maps");
            }
            else {
                if ($mybb->request_method == "post") {
                    delete_map($mid);

                    $mybb->input['module'] = "maps";
                    $mybb->input['action'] = $lang->acp_maps_success3;
                    log_admin_action(htmlspecialchars_uni($map['name']));

                    flash_message($lang->acp_maps_success3, 'success');
                    admin_redirect("index.php?module=config-maps");
                }
                else {
                    $page->output_confirm_action(
                        "index.php?module=config-maps&amp;action=delete_map&amp;mid={$mid}",
                        $lang->acp_maps_delete
                    );
                }
            }
            exit;
        }
    }
}

function add_map($name, $slug, $fid, $desc, $image, $visibility, $suggestions) {
    global $db;
    require_once MYBB_ROOT . '/inc/adminfunctions_templates.php';

    $new_record = [
        "name" => $name,
        "fid" => $fid,
        "slug" => $slug,
        "desc" => $desc,
        "image" => $image,
        "visibility" => $visibility,
        "suggestions" => $suggestions
    ];

    $db->insert_query("maps", $new_record);
}

function edit_map($mid, $name, $slug, $fid, $desc, $image, $visibility, $suggestions) {
    global $db;
    require_once MYBB_ROOT . '/inc/adminfunctions_templates.php';

    $new_record = [
        "name" => $name,
        "fid" => $fid,
        "slug" => $slug,
        "desc" => $desc,
        "image" => $image,
        "visibility" => $visibility,
        "suggestions" => $suggestions
    ];

    $db->update_query("maps", $new_record, "mid = '$mid'");
}

function delete_map($mid) {
    global $db;
    require_once MYBB_ROOT . '/inc/adminfunctions_templates.php';

    $db->delete_query("maps", "mid = '$mid'");
    $db->delete_query("locations", "mid = '$mid'");
}

$plugins->add_hook("fetch_wol_activity_end", "online_activity_maps");
function online_activity_maps($user_activity) {
    global $user;

    if (my_strpos($user['location'], "maps.php") !== false) $user_activity['activity'] = "maps";

    return $user_activity;
}

$plugins->add_hook("build_friendly_wol_location_end", "online_location_maps");
function online_location_maps($plugin_array) {
    global $mybb, $theme, $lang;
    $lang->load("rpmaps");

    if ($plugin_array['user_activity']['activity'] == "maps") $plugin_array['location_name'] = $lang->viewing_maps;

    return $plugin_array;
}

$plugins->add_hook("modcp_nav", "add_modcp_nav_maps");
function add_modcp_nav_maps() {
    global $db, $mybb, $lang, $templates, $modcp_nav_maps;
    $lang->load('rpmaps');

    eval("\$modcp_nav_maps = \"".$templates->get("maps_modcp_nav")."\";");
}

$plugins->add_hook("modcp_start", "modcp_manage_maps");
function modcp_manage_maps() {
    global $mybb, $db, $lang, $templates, $headerinclude, $header, $footer, $modcp_nav, $theme;
    $lang->load('rpmaps');

    $mybb->input['action'] = $mybb->get_input('action');

    if ($mybb->input['action'] == "maps") {
        add_breadcrumb($lang->modcp_maps, "modcp.php?action=maps");

        $query = $db->query("
            SELECT *
            FROM " . TABLE_PREFIX . "locations
            WHERE accepted = '0'
            ORDER by name ASC
        ");

        $map_bit = "";

        while ($location = $db->fetch_array($query)) {
            $lid = $location['lid'];
            $cb = $location['uid'];
            $user = $db->fetch_array($db->simple_select("users", "*", "uid = '$cb'"));
            $createdby = $user['username'];

            eval("\$map_bit .= \"".$templates->get("maps_modcp_bit")."\";");
        }

        if (isset($mybb->input['acceptmap'])) {
            $lid = $mybb->get_input('lid');

            $new_record = array( "accepted" => (int) 1 );

            $db->update_query("locations", $new_record, "lid = '$lid'");
            redirect("modcp.php?action=maps");
        }

        if (isset($mybb->input['deletemap'])) {
            $lid = $mybb->get_input('lid');

            $db->delete_query("locations", "lid = '$lid'");
            redirect("modcp.php?action=maps");
        }

        eval("\$page = \"".$templates->get("maps_modcp")."\";");
        output_page($page);
    }
}

$plugins->add_hook("member_profile_start", "add_location_profile");
function add_location_profile() {
    global $db, $map_location;

    $locations_query = $db->query("
        SELECT *
        FROM " . TABLE_PREFIX . "locations l
        JOIN " . TABLE_PREFIX . "users u
        ON FIND_IN_SET(u.username, l.residents) != 0
        WHERE u.uid = '" . $_REQUEST['uid'] . "'
    ");

    $map_location = "";

    while ($locations = $db->fetch_array($locations_query)) {
        if ($map_location != "") {
            $map_location .= '<br />';
        }
        $map_location .= $locations['address'] . ', ' . $locations['name'];
    }
}
