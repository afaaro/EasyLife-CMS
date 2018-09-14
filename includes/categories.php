<?php

function load_categories() {
    global $db;

    $result = $db->query("SELECT * FROM #__media_categories ORDER BY name ASC");
    $categories = array();
    if($result->num_rows > 0){
	    foreach($result->rows as $row) {
            $categories[$row['id']] = $row;
        } 
    }
    return $categories;
}

function clean_link($item_id) {
    global $db;

    $full_path = "";
    while ($item_id > 0) {
        $result = dbquery("SELECT name,parent FROM ".DB_PREFIX."media_categories WHERE id='$item_id'");
        if (dbrows($result)) {
            $data = dbarray($result);
            if ($full_path) {
                $full_path = "/".$full_path;
            }
            $full_path = clean_url($data['name']).$full_path;
            $item_id = $data['parent'];
        }
    }
    return $full_path;
}

function dropdown_categories($id=0, $level=0) {
    /* Child patern */
    $indent = str_repeat("&#8212;", $level);

    $result = dbquery("SELECT * FROM ".DB_PREFIX."media_categories WHERE parent = '".intval($id)."' ORDER BY name ASC");
    $select["-ROOT-"] = 0;
    while($row = dbarray($result)) {
        $catid = Io::Output($row['id'], "int");
        $select[$indent." ".Io::Output($row['name'])] = Io::Output($row['id'], "int");
        $select = array_merge($select, dropdown_categories($catid, $level + 1));
    }
    return $select;
}

// Returns a String containing all the categories ids from the one that is given
// to tree down seperated by comma
function get_parents($id){
    global $db;

    $result = $db->query("SELECT * FROM #__media_categories");
    $catsArray = array(); $catsChildrensArray = array();
    if($result->num_rows) {
        foreach($result->rows as $row) {
            $catsArray[ $row["id"] ] = $row;
            $catsChildrensArray[ $row["parent"] ][] = $row["id"];
        }
        $html =  isset($catsArray[$id]) ? $id : "";
        if(isset($catsChildrensArray[$id])){
            foreach($catsChildrensArray[$id] as $catId){
                $html .= $html == "" ? "" : ",";
                $html .= get_parents($catId);
            }
        }
        return $html;
    }
}

function get_categories($level=0, $prefix='') {
    global $db;

    $categories = $db->query("SELECT id,parent,name,title FROM #__media_categories WHERE parent=".intval($level))->rows;
    $output = array(); $num_items = sizeof($output); $i = 0;
    if(sizeof($categories) > 0) {
        foreach($categories as $key => $element) {
            $children = get_categories($element['id'], $prefix);            

            $num_children = sizeof($children);
            $classes = array($element['id']);

            if ($element === reset($categories)) $classes[] = "first";
            elseif($element === end($categories)) $classes[] = "last";
            if ($children) $classes[] = "submenu";
            //$output[] = array('id'=>$element['id'],'parent'=>$element['parent'],'title'=>$element['title'],'name'=>$element['name']);
            
            $html = "\n<li class='%s'><a href='".$element['name']."'>".$element['title']."</a>";
            if($children) {
                $html .= "\n  <ul>".implode("", $children)."\n  </ul>";
            }
            $html .= "</li>";
            $output[$element['id']] = sprintf($html, implode(" ", $classes));
            unset($element);
            $i++;
        }
    }

    if (sizeof($output)) {
        return  $output;
    } else return "";
}
