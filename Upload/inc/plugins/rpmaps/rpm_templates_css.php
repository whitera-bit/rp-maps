<?php

// Disallow direct access to this file for security reasons
if (!defined("IN_MYBB")) die("Direct initialization of this file is not allowed.");

function rpm_templates_add() {
    global $db;

    $templates[] = array(
        'title' => 'maps_header_menu',
        'template' => $db->escape_string('
<li><a href="{$mybb->settings[\'bburl\']}/maps.php">{$lang->maps}</a></li>'),
        'sid' => '-2',
        'version' => '',
        'dateline' => time()
    );

    $templates[] = array(
        'title' => 'maps_modcp',
        'template' => $db->escape_string('
<html>
    <head>
        <title>{$mybb->settings[\'bbname\']} - {$lang->modcp_maps}</title>
        {$headerinclude}
    </head>
    <body>
        {$header}
        <table width="100%" border="0" align="center">
            <tr>
                {$modcp_nav}
                <td valign="top">
                    <table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
                        <tr>
                            <td class="thead" align="center" colspan="2"><strong>{$lang->modcp_maps}</strong></td>
                        </tr>
                        {$map_bit}
                    </table>
                </td>
            </tr>
        </table>
        {$footer}
    </body>
</html>'),
        'sid' => '-2',
        'version' => '',
        'dateline' => time()
    );

    $templates[] = array(
        'title' => 'maps_modcp_bit',
        'template' => $db->escape_string('
<tr>
    <td width="30%" class="trow1" valign="top">
        <strong>{$location[\'name\']}</strong> {$location[\'address\']}<br/>
        {$lang->modcp_createdby}: {$createdby}<br/>
        <form action="modcp.php" method="post" name="acceptmap">
            <input type="hidden" name="lid" id="lid" value="{$lid}" />
            <input type="hidden" name="action" value="maps" />
            <input type="submit" class="button" name="acceptmap" value="{$lang->modcp_accept}" />
        </form>
        <form action="modcp.php" method="post" name="deletemap">
            <input type="hidden" name="lid" id="lid" value="{$lid}" />
            <input type="hidden" name="action" value="maps" />
            <input type="submit" class="button" name="deletemap" value="{$lang->modcp_delete}" />
        </form>
    </td>
    <td width="70%" class="trow2">
        {$location[\'desc\']}
    </td>
</tr>'),
        'sid' => '-2',
        'version' => '',
        'dateline' => time()
    );

    $templates[] = array(
        'title' => 'maps_modcp_nav',
        'template' => $db->escape_string('
<tr>
    <td class="tcat tcat_menu">
        <div><span class="smalltext"><strong>{$lang->modcp_maps}</strong></span></div>
    </td>
</tr>
<tbody>
    <tr><td class="trow1 smalltext"><a href="{$mybb->settings[\'bburl\']}/modcp.php?action=maps" class="modcp_nav_item">{$lang->modcp_maps}</a></td></tr>
</tbody>'),
        'sid' => '-2',
        'version' => '',
        'dateline' => time()
    );

    $templates[] = array(
        'title' => 'maps',
        'template' => $db->escape_string('
<html>
    <head>
        <title>{$mybb->settings[\'bbname\']} - {$lang->maps}</title>
        {$headerinclude}
    </head>
    <body>
        {$header}
        <table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
            <tr>
                <td class="thead" colspan="4">
                    <strong>{$lang->maps}</strong>
                </td>
            </tr>
            <tr>
                {$map_bit}
            </tr>
        </table>
        {$footer}
    </body>
</html>'),
        'sid' => '-2',
        'version' => '',
        'dateline' => time()
    );

    $templates[] = array(
        'title' => 'maps_bit',
        'template' => $db->escape_string('
<td class="trow1 float_left" width="25%">
    <a href="?mid={$maps[\'slug\']}"><img src="{$maps[\'image\']}" title="{$maps[\'name\']}" style="width: 100%;" /></a>
</td>'),
        'sid' => '-2',
        'version' => '',
        'dateline' => time()
    );

    $templates[] = array(
        'title' => 'maps_map',
        'template' => $db->escape_string('
<html>
    <head>
        <title>{$mybb->settings[\'bbname\']} - {$name}</title>
        {$headerinclude}
    </head>
    <body>
        {$header}
        {$added}
        <table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
            <tr>
                <td class="thead">
                    <div class="float_right">{$newaddress}</div>
                    <div><strong>{$name}</strong><br /><small>{$lang->map_add}</small></div>
                </td>
            </tr>
            <tr>
                <td class="trow1">
                    <div id="map" class="map">
                        {$map_object}
                        {$locations_bit}
                    </div>
                </td>
            </tr>
            <tr>
                <td class="trow2">
                    {$map[\'desc\']}
                </td>
            </tr>
        </table>
        {$footer}
        {$address_script}
    </body>
</html>'),
        'sid' => '-2',
        'version' => '',
        'dateline' => time()
    );

    $templates[] = array(
        'title' => 'maps_map_bit_normal',
        'template' => $db->escape_string('
<div class="address {$locationclass}" style="top: {$location[\'ycoord\']}%; left: {$location[\'xcoord\']}%;">
    <a onclick="$(\'#location-{$location[\'lid\']}\').modal({ fadeDuration: 250, keepelement: true, zIndex: (typeof modal_zindex !== \'undefined\' ? modal_zindex : 9999) }); return false;" title="{$location[\'name\']}">
        <img src="{$theme[\'imgdir\']}/map/{$location[\'icon\']}" />
    </a>
</div>
<div class="modal" id="location-{$location[\'lid\']}" style="display: none;">
    <table width="100%" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" border="0" class="tborder">
        <tr>
            <td class="thead" colspan="2">
                <strong>{$location[\'name\']}</strong><br/>
                {$location[\'address\']} {$options}
            </td>
        </tr>
        <tr>
            <td class="trow1" colspan="2">{$location[\'desc\']}</td>
        </tr>
        <tr>{$bit}</tr>
    </table>
</div>'),
        'sid' => '-2',
        'version' => '',
        'dateline' => time()
    );

    $templates[] = array(
        'title' => 'maps_map_bit_normal_bit',
        'template' => $db->escape_string('<td class="trow2 float_left" width="50%">{$charactername}</td>'),
        'sid' => '-2',
        'version' => '',
        'dateline' => time()
    );

    $templates[] = array(
        'title' => 'maps_map_bit_short',
        'template' => $db->escape_string('<div class="address short {$locationclass}" style="top: {$location[\'ycoord\']}%; left: {$location[\'xcoord\']}%;"><img src="{$theme[\'imgdir\']}/map/{$location[\'icon\']}" /><div>{$location[\'name\']} {$options}</div></div>'),
        'sid' => '-2',
        'version' => '',
        'dateline' => time()
    );

    $templates[] = array(
        'title' => 'maps_new',
        'template' => $db->escape_string('
<html>
    <head>
        <title>{$mybb->settings[\'bbname\']} - {$lang->map_new}</title>
        {$headerinclude}
    </head>
    <body>
        {$header}
        <form action="maps.php" method="post" name="submitnewaddress">
            <table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
                <tr>
                    <td class="thead"><strong>{$lang->map_new}</strong></td>
                </tr>
                <tr>
                    <td width="100%" class="trow1" valign="top">
                        <input type="text" class="textbox" name="name" id="name" placeholder="{$lang->map_field_name}" required size="50" />
                        <input type="text" class="textbox" name="address" id="address" placeholder="{$lang->map_field_address}" required size="50" />
                        <br/><br/>
                        <input type="text" class="textbox" name="xcoord" id="xcoord" placeholder="{$lang->map_field_xcoord}" required size="10" />
                        <input type="text" class="textbox" name="ycoord" id="ycoord" placeholder="{$lang->map_field_ycoord}" required size="10" />
                        <br/><br/>
                        {$icon_options}
                        <br/><br/>
                        <input type="checkbox" name="details" id="details" value="1" /> {$lang->map_field_details}
                        <br/><br/>
                        <textarea name="desc" id="desc" placeholder="{$lang->map_field_desc}" rows="8" style="width: 100%;"></textarea>
                        <br/><br/>
                        <strong>{$lang->map_field_residents}</strong><br/>
                        <small>{$lang->map_field_residents_hint}</small><br/>
                        <input type="text" class="textbox" name="residents" id="residents" style="width: 100%;" />
                    </td>
                </tr>
            </table>
            <br/>
            <div align="center">
                <input type="hidden" name="mid" id="mid" value="{$current_mid}" />
                <input type="hidden" name="action" value="map_new" />
                <input type="submit" class="button" name="submitnewaddress" value="{$lang->map_send}" />
            </div>
        </form>
        {$footer}
        {$address_script}
    </body>
</html>'),
        'sid' => '-2',
        'version' => '',
        'dateline' => time()
    );

    $templates[] = array(
        'title' => 'maps_edit',
        'template' => $db->escape_string('
<html>
    <head>
        <title>{$mybb->settings[\'bbname\']} - {$lang->map_edit}</title>
        {$headerinclude}
    </head>
    <body>
        {$header}
        <form action="maps.php" method="post" name="submiteditaddress">
            <table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
                <tr>
                    <td class="thead"><strong>{$lang->map_edit}</strong></td>
                </tr>
                <tr>
                    <td width="100%" class="trow1" valign="top">
                        <input type="text" class="textbox" name="name" id="name" value="{$location[\'name\']}" required size="50" />
                        <input type="text" class="textbox" name="address" id="address" value="{$location[\'address\']}" required size="50" />
                        <br/><br/>
                        <input type="text" class="textbox" name="xcoord" id="xcoord" value="{$location[\'xcoord\']}" required size="10" />
                        <input type="text" class="textbox" name="ycoord" id="ycoord" value="{$location[\'ycoord\']}" required size="10" />
                        <br/><br/>
                        {$icon_options}
                        <br/><br/>
                        <input type="checkbox" name="details" id="details" value="1" {$detailscheck} /> {$lang->map_field_details}
                        <br/><br/>
                        <textarea name="desc" id="desc" rows="8" style="width: 100%;">{$location[\'desc\']}</textarea>
                        <br/><br/>
                        <strong>{$lang->map_field_residents}</strong><br/>
                        <small>{$lang->map_field_residents_hint}</small><br/>
                        <input type="text" class="textbox" name="residents" id="residents" value="{$location[\'residents\']}" style="width: 100%;" />
                    </td>
                </tr>
            </table>
            <br/>
            <div align="center">
                <input type="hidden" name="lid" id="lid" value="{$lid}" />
                <input type="hidden" name="action" value="map_edit" />
                <input type="submit" class="button" name="submiteditaddress" value="{$lang->map_change}" />
            </div>
        </form>
        {$footer}
    </body>
</html>'),
        'sid' => '-2',
        'version' => '',
        'dateline' => time()
    );

    $templates[] = array(
        'title' => 'maps_minimap',
        'template' => $db->escape_string('
<tr>
    <td class="trow1" align="center" colspan="5">
        <img src="{$forum[\'minimap\']}" class="minimap" />
        {$forum[\'maplink\']}
    </td>
</tr>'),
        'sid' => '-2',
        'version' => '',
        'dateline' => time()
    );

    $db->insert_query_multiple("templates", $templates);
}

function rpm_css_add() {
    global $db;

    $css = array(
		'name' => 'rpm.css',
		'tid' => 1,
		"stylesheet" =>	'
.map { position: relative; }
#mapevents {
    position: absolute;
    top: 0;
    right: 0;
    bottom: 0;
    left: 0;
    opacity: 0.5;
    background: #000;
    visibility: hidden;
}
.address {
    position: absolute;
    width: 20px;
    height: 20px;
    padding: 2px;
    background: #ddd;
    border-radius: 100%;
    transform: translate(-50%, -50%);
}
.address img { width: 100%; }
.address.draft img { opacity: 0.4; }
.address.short > div {
    background: #ddd;
    display: none;
}
.address.short:hover > div { display: block; }

.minimap {
    width: 100%;
    height: 100px;
    object-fit: cover;
}',
        'cachefile' => 'rpm.css',
        'lastmodified' => time()
    );

    require_once MYBB_ADMIN_DIR . "inc/functions_themes.php";

    $sid = $db->insert_query("themestylesheets", $css);
    $db->update_query("themestylesheets", array("cachefile" => "css.php?stylesheet=" . $sid), "sid = '" . $sid . "'", 1);
    $tids = $db->simple_select("themes", "tid");
    while ($theme = $db->fetch_array($tids)) {
        update_theme_stylesheet_list($theme['tid']);
    }
}
