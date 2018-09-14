<?php
require_once '../../../maincore.php';

if(isset($_POST['page'])) {
    $start = isset($_POST['page']) ? $_POST['page'] : 0;
    $limit = 10;

    //set conditions for search
    //$whereSQL = $orderSQL = '';

    if (!empty($_POST['keywords'])) {
        $whereSQL = "WHERE title LIKE '%".trim($_POST['keywords'])."%'";
    } else {
        $whereSQL = "";
    }

    if (!empty($_POST['sortBy'])) {
        $orderSQL = "ORDER BY created ".trim($_POST['sortBy']);
    } else {
        $orderSQL = "ORDER BY created DESC";
    }

    //get number of rows
    $rowCount = $db->query("SELECT COUNT(*) as total FROM #__post $whereSQL $orderSQL")->row['total'];

    //initialize pagination class
    $pagConfig = array(
        'currentPage' => $start,
        'totalRows' => $rowCount,
        'perPage' => $limit,
        'link_func' => 'searchFilter'
    );

    $pagination = new Pagination($pagConfig);

    //get rows
    $query = $db->query("SELECT * FROM #__post $whereSQL $orderSQL LIMIT $start,$limit");
    if($query->num_rows > 0) {
    	echo "<table class='table table-striped table-bordered table-rounded table-hover'>";
		foreach($query->rows as $row) {
			$id = Io::Output($row['id'], 'int');
			echo "<tr onmouseover='javascript:showmenu($id);' onmouseout='javascript:showmenu($id);'>\n";
				echo "<td>".Io::Output($row['title']);
					echo "<div id='menu_$id' style='display:none; margin-top:2px;' class='pull-right'>\n";
					if($User->hasPermission('modify', 'post/news')) {
						echo "<a href='".Url::link("post/news&amp;ref=form&amp;id=$id")."' title='Edit' class='btn btn-info btn-xs'>Edit</a>\n";
					}
					if($User->hasPermission('delete', 'post/news')) {
						echo "<a href='".Url::link("post/news&amp;ref=delete&amp;id=$id")."' title='Delete' class='btn btn-danger btn-xs'>Delete</a>\n";
					}
					echo "</div>";
				echo "</td>";
				//echo "<td>&nbsp;</td>";
			echo "</tr>";
		}
    	echo "</table>";
    	echo $pagination->createLinks();
    }
}