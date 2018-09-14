<?php
//Deny direct access
defined("_LOAD") or die("Access denied");

function build_Horizontal_Menu($tree, $level = 0){
    global $User;

    $output = "";
    foreach ($tree as $cat) {
        if ($cat['parent'] == 0) {
            if (isset($cat['children'])) {                
                $output .= "<li class='submenu'>";
                $output .= "<a href='#'><i class='fa ".$cat['icon']." fw'></i> ".$cat['name']."</a>";
                if($User->hasPermission('access', $cat['href'])) {
                    $output .= "<ul>";
                    $output .= build_Horizontal_Menu($cat['children'], $level + 1);
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
                $output .= "<li class='submenu'>";
                $output .= "<a href='#'>".$cat['name']."</a>";
                if($User->hasPermission('access', $cat['href'])) {
                    $output .= "<ul>";
                    $output .= build_Horizontal_Menu($cat['children'], $level + 1);
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

function build_Vertical_Menu($tree, $level = 1){
    global $User;

    $output = "";
    //$output .= "<li><a href='index.html'><i class='fa fa-dashboard fa-fw'></i>&nbsp;Dashboard</a></li>";
    foreach ($tree as $cat) {
        $indent = str_repeat("&nbsp;&nbsp;", $level);
        if ($cat['parent'] == 0) {
            if (isset($cat['children'])) {     
                $output .= "<li>";
                $output .= "<a href='#'><i class='fa ".$cat['icon']." fw'></i>&nbsp;".$cat['name']."<span class='fa arrow'></span></a>";
                if (checkrights($cat['href'])) {
                    $output .= "<ul class='nav nav-".convertNumberToWord($level)."-level'>";
                    $output .= build_Vertical_Menu($cat['children'], $level + 1);
                    $output .= "</ul>";
                }
                $output .= "</li>";
                
            } else {
                $output .= "<li>";
                $output .= "<a href='".Url::link($cat['href'])."'><i class='fa ".$cat['icon']." fw'></i>$indent".$cat['name']."</a>";
                $output .= "</li>";
            }
        } else {
            if (isset($cat['children'])) {                
                $output .= "<li>";
                $output .= "<a href='#'>$indent".$cat['name']."<span class='fa arrow'></span></a>";
                if (checkrights($cat['href'])) {
                    $output .= "<ul class='nav nav-".convertNumberToWord($level)."-level'>";
                    $output .= build_Vertical_Menu($cat['children'], $level + 1);
                    $output .= "</ul>";
                }
                $output .= "</li>";                
            } else {
                $output .= "<li>";
                $output .= "<a href='".Url::link($cat['href'])."'><i class='fa fa-angle-right'></i>$indent".$cat['name']."</a>";
                $output .= "</li>";
            }
        }
    }
    return $output;
}

function checkrights($right) {
    global $User;
    if (in_array($right, $User->GetInfo('user_rights')['access']) || $User->GetInfo('user_level') == '1') {
        return TRUE;
    } else {
        return FALSE;
    }
}