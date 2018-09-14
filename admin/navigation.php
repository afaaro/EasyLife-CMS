<?php

$result = $db->query("SELECT id, parent, title, url, icon FROM ".PREFIX."menu_acp ORDER BY menu ASC, parent ASC, title ASC");
$refs = array(); $list = array();
foreach($result->rows as $data) {
    $thisref = &$refs[ $data['id'] ];
    $thisref['id'] = $data['id'];
    $thisref['parent'] = $data['parent'];
    $thisref['name'] = $data['title'];
    $thisref['href'] = $data['url'];
    $thisref['icon'] = $data['icon'];
    if ($data['parent'] == 0) {
        $list[ $data['id'] ] = &$thisref;
    } else {
        $refs[ $data['parent'] ]['children'][] = &$thisref;
    }
}

echo "<nav id='column-left'>";
	echo "<div id='navigation'><span class='fa fa-bars'></span> Navigation</div>";
		echo "<ul id='menu'>";
		echo buildMenu($list);
		echo "</ul>";
echo "</nav>";

function buildMenu($tree, $level = 0){
    global $User;

	$output = "";
    foreach ($tree as $cat) {
        if ($cat['parent'] == 0) {
            if (isset($cat['children'])) {                
                $output .= "<li>";
                $output .= "<a href='#collapse".$cat['id']."' data-toggle='collapse' class='parent collapsed'><i class='fa ".$cat['icon']." fw'></i> ".$cat['name']."</a>";
                if($User->hasPermission('access', $cat['href'])) {
                    $output .= "<ul class='collapse' id='collapse".$cat['id']."'>";
                    $output .= buildMenu($cat['children'], $level + 1);
                    $output .= "</ul>";
                }
                $output .= "</li>";
                
            } else {
                if($User->hasPermission('access', $cat['href'])) {
                	$output .= "<li>";
                    $output .= "<a href='".Url::link($cat['href'])."'><i class='fa ".$cat['icon']." fw'></i> ".$cat['name']."</a>";
                    $output .= "</li>";
                }
            }
        } else {
            if (isset($cat['children'])) {                
            	$output .= "<li>";
                $output .= "<a href='#collapse".$cat['id']."' data-toggle='collapse' class='parent collapsed'>".$cat['name']."</a>";
                if($User->hasPermission('access', $cat['href'])) {
                    $output .= "<ul class='collapse' id='collapse" . $cat['id'] . "'>";
                    $output .= buildMenu($cat['children'], $level + 1);
                    $output .= "</ul>";
                }
                $output .= "</li>";                
            } else {
                if($User->hasPermission('access', $cat['href'])) {
                	$output .= "<li>";
                    $output .= "<a href='".Url::link($cat['href'])."'>".$cat['name']."</a>";
                    $output .= "</li>";
                }
            }
        }
    }
    return $output;
}
