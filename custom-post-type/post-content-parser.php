<?php
/**
 * Created by PhpStorm.
 * User: moridrin
 * Date: 2-7-17
 * Time: 9:48
 */

#region Post Content
/**
 * @param string $content
 *
 * @return string
 */
function mp_dd_filter_content($content)
{
    global $post;

    #region Map
    if ($post->post_type == 'map') {
        $content = mp_dd_get_map_content($post->ID);
    }
    if (preg_match_all("/\[map-([0-9]+)\]/", $content, $mapMatches)) {
        foreach ($mapMatches[1] as $mapID) {
            $mapHTML = mp_dd_get_map_content($mapID);
            $content = str_replace("[map-$mapID]", $mapHTML, $content);
        }
    }
    #endregion

    #region Building
    if ($post->post_type == 'building') {
        $content = mp_dd_get_building_content($post->ID);
    }
    if (preg_match_all("/\[building-url-([0-9]+)\]/", $content, $buildingURLMatches)) {
        foreach ($buildingURLMatches[1] as $buildingID) {
            $content = str_replace("[building-url-$buildingID]", "#modal_$buildingID", $content);
            if (strpos($content, 'id="modal_' . $buildingID . '"') === false) {
                $buildingHTML = mp_dd_get_building_content($buildingID, true);
                $content      .= $buildingHTML;
            }
        }
    }
    if (preg_match_all("/\[building-link-([0-9]+)\]/", $content, $buildingURLMatches)) {
        foreach ($buildingURLMatches[1] as $buildingID) {
            if (!get_post($buildingID)) {
                continue;
            }
            $buildingTitle = get_post($buildingID)->post_title;
            $buildingLink  = "<a href=\"#modal_$buildingID\">$buildingTitle</a>";
            $content       = str_replace("[building-link-$buildingID]", $buildingLink, $content);
            if (strpos($content, 'id="modal_' . $buildingID . '"') === false) {
                $buildingHTML = mp_dd_get_building_content($buildingID, true);
                $content      .= $buildingHTML;
            }
        }
    }
    if (preg_match_all("/\[building-link-with-type-([0-9]+)\]/", $content, $buildingURLMatches)) {
        foreach ($buildingURLMatches[1] as $buildingID) {
            if (!get_post($buildingID)) {
                continue;
            }
            $ownerID         = get_post_meta($buildingID, 'owner', true);
            $ownerProfession = get_post_meta($ownerID, 'profession', true);
            $buildingTitle   = get_post($buildingID)->post_title . ' - ' . $ownerProfession;
            $buildingLink    = "<a href=\"#modal_$buildingID\">$buildingTitle</a>";
            $content         = str_replace("[building-link-$buildingID]", $buildingLink, $content);
            if (strpos($content, 'id="modal_' . $buildingID . '"') === false) {
                $buildingHTML = mp_dd_get_building_content($buildingID, true);
                $content      .= $buildingHTML;
            }
        }
    }
    #endregion

    #region NPC
    if (preg_match_all("/(\[npc-li-([0-9]+)(-spouse)?(-child)?\]){1,}/", $content, $npcGroupMatches)) {
        foreach ($npcGroupMatches[0] as $npcGroupMatch) {
            $npcHTML = '<ul class="collapsible" data-collapsible="expandable">';
            preg_match_all("/\[npc-li?-([0-9]+)(-spouse)?(-child)?\]/", $npcGroupMatch, $npcMatches);
            for ($i = 0; $i < count($npcMatches[1]); $i++) {
                $npcID      = $npcMatches[1][$i];
                $npc        = get_post($npcID);
                $profession = get_post_meta($npcID, 'profession', true);
                $profession = $profession ? '<span style="margin-left: 5px;"> (' . $profession . ')</span>' : '';
                $profession = $npcMatches[2][$i] == '-spouse' ? '<span style="margin-left: 5px;"> (spouse)</span>' : $profession;
                $profession = $npcMatches[3][$i] == '-child' ? '<span style="margin-left: 5px;"> (child)</span>' : $profession;
                $npcHTML    .= '<li>';
                $npcHTML    .= '<div class="collapsible-header">';
                $npcHTML    .= '<h3 style="display: inline-block;">' . $npc->post_title . '</h3>' . $profession;
                $npcHTML    .= '</div>';
                $npcHTML    .= '<div class="collapsible-body">';
                $npcHTML    .= mp_dd_get_npc_content($npcID);
                $npcHTML    .= '</div>';
                $npcHTML    .= '</li>';
            }
            $npcHTML .= '</ul>';
            $content = str_replace($npcGroupMatch, $npcHTML, $content);
        }
    }
    if (preg_match_all("/\[npc-([0-9]+)(-spouse)?(-child)?\]/", $content, $npcMatches)) {
        for ($i = 0; $i < count($npcMatches[1]); $i++) {
            $npcID   = $npcMatches[1][$i];
            $npcHTML = mp_dd_get_npc_content($npcID, $npcMatches[2][$i] == '-spouse', $npcMatches[3][$i] == '-child');
            $search  = '[npc-' . $npcID . $npcMatches[2][$i] . $npcMatches[3][$i] . ']';
            $content = str_replace($search, $npcHTML, $content);
        }
    }
    #endregion

    return $content;
}

add_filter('the_content', 'mp_dd_filter_content');

#region Get Map
/**
 * @param int $mapID
 *
 * @return string
 */
function mp_dd_get_map_content($mapID)
{
    $map     = get_post($mapID);
    $mapHTML = $map->post_content;
    if (strpos($mapHTML, '[building-labels]') !== false) {
        $buildingLabels = get_post_meta($mapID, 'building_labels', true);
        ob_start();
        foreach ($buildingLabels as $buildingLabel) {
            ?>
            <div style="position:absolute; top:<?= $buildingLabel['top'] ?>px; left:<?= $buildingLabel['left'] ?>px; z-index: 100">
                <?php if ($buildingLabel['showing']): ?>
                    <?php $url = isset($buildingLabel['id']) ? '[building-url-' . $buildingLabel['id'] . ']' : '#modal' . $buildingLabel['id']; ?>
                    <a href="<?= $url ?>"
                       style="color: #FFFFFF; background: rgba(0,0,0,0.6); height: 30px; width: 30px; text-align: center; display: block; border: 3px solid <?= $buildingLabel['color'] ?>; border-radius: 20%;font-size: 9px;line-height: 25px;">
                        <?= $buildingLabel['label'] ?>
                    </a>
                <?php endif; ?>
            </div>
            <?php
        }
        $mapHTML = str_replace('[building-labels]', ob_get_clean(), $mapHTML);
    }
    return $mapHTML;
}

#endregion

#region Get Building
/**
 * @param int  $buildingID
 * @param bool $inModal
 *
 * @return string
 */
function mp_dd_get_building_content($buildingID, $inModal = false)
{
    $building = get_post($buildingID);
    if (!$building) {
        return '';
    }

    $buildingHTML = "<h2 style=\"display: inline-block;\">$building->post_title</h2> ($buildingID)<br/>";
    $buildingHTML .= $building->post_content;
    if ($inModal) {
        $buildingHTML = "<div id=\"modal_$buildingID\" class=\"modal modal-fixed-footer\"><div class=\"modal-content\">$buildingHTML</div></div>";
    }

    if (preg_match('/\[npc(-li)?-owner\]/', $buildingHTML)) {
        $ownerID      = get_post_meta($building->ID, 'owner', true);
        $buildingHTML = preg_replace('/\[npc(-li)?-owner\]/', "[npc$1-$ownerID]", $buildingHTML);
    }
    if (preg_match('/\[npc(-li)?-owner-with-family\]/', $buildingHTML)) {
        $ownerID         = get_post_meta($building->ID, 'owner', true);
        $ownerWithFamily = "[npc$1-$ownerID]";
        $spouse          = get_post_meta($ownerID, 'spouse', true);
        if ($spouse) {
            $ownerWithFamily .= "[npc$1-$spouse-spouse]";
        }
        $children = get_post_meta($ownerID, 'children', true);
        foreach ($children as $child) {
            $ownerWithFamily .= "[npc$1-$child-child]";
        }
        $buildingHTML = preg_replace('/\[npc(-li)?-owner-with-family\]/', $ownerWithFamily, $buildingHTML);
    }
    return $buildingHTML;
}

#endregion

#region Get NPC
/**
 * @param int  $npcID
 * @param bool $isSpouse
 * @param bool $isChild
 *
 * @return string
 */
function mp_dd_get_npc_content($npcID, $isSpouse = false, $isChild = false)
{
    $npc        = get_post($npcID);
    $profession = get_post_meta($npcID, 'profession', true);
    $profession = $profession ? '<span style="margin-left: 5px;"> (' . $profession . ')</span>' : '';
    $profession = $isSpouse ? '<span style="margin-left: 5px;"> (spouse)</span>' : $profession;
    $profession = $isChild ? '<span style="margin-left: 5px;"> (child)</span>' : $profession;
    $style      = !empty($profession) ? 'style="display: inline-block;"' : '';
    $npcHTML    = "<h2 $style>$npc->post_title</h2> $profession";
    if (has_post_thumbnail($npcID)) {
        $npcHTML .= '<div class="row">';
        $npcHTML .= '<div class="col s10" style="padding-left: 0;">';
    }
    $npcHTML .= '<p>';
    $class   = get_post_meta($npcID, 'class', true);
    $level   = get_post_meta($npcID, 'level', true);
    if ($class && $level) {
        $npcHTML .= '<b>Class:</b> ' . $class . ' <b>Level:</b> ' . $level . '<br/>';
    }
    $npcHTML .= '<b>Height:</b> ' . get_post_meta($npcID, 'height', true) . ' <b>Weight:</b> ' . get_post_meta($npcID, 'weight', true) . '<br/>';
    $npcHTML .= $npc->post_content . '<br/>';
    $npcHTML .= '<b>Wearing:</b> ' . get_post_meta($npcID, 'clothing', true) . '<br/>';
    $npcHTML .= '<b>Possessions:</b> ' . get_post_meta($npcID, 'possessions', true) . '<br/>';
    $npcHTML .= '</p>';
    if (has_post_thumbnail($npcID)) {
        $npcHTML .= '</div>';
        $npcHTML .= '<div class="col s2 valign-wrapper center-align">';
        $npcHTML .= get_the_post_thumbnail($npcID, 'thumbnail');
        $npcHTML .= '</div></div>';
    }

    return $npcHTML;
}
#endregion
#endregion
