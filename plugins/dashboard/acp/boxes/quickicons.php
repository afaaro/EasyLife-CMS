<?php


$result = $db->query("SELECT title,id FROM #__menu_acp WHERE parent=0 AND status='active' ORDER BY title")->rows;
foreach ($result as $row) {
	$parent = Io::Output($row['id']);
	opentable(Io::Output($row['title']));
		echo "<div class='row'>";
		$result = $db->query("SELECT title,id,url FROM #__menu_acp WHERE parent='{$parent}' AND status='active' ORDER BY title")->rows;
		foreach ($result as $row) {
			$id = Io::Output($row['id']);
			$url = ($row['url'] == '') ? Io::Output($row['title']) : "<a href='".Url::link(Io::Output($row['url']))."'>".Io::Output($row['title'])."</a>";
			echo "<div class='col-md-4'>";
				opentable($url);
					$result = $db->query("SELECT title,parent,url FROM #__menu_acp WHERE parent='{$id}' AND status='active' ORDER BY title")->rows;
					foreach($result as $row) {
						?>
						<div style="cursor:pointer;" class="col-md-12" onmouseover="javascript:$(this).addClass('highlight');" onmouseout="javascript:$(this).removeClass('highlight');" onclick="javascript:location='<?php echo Url::link(Io::Output($row['url'])); ?>'">
							<div><?php echo Io::Output($row['title']); ?></div>
						</div>
						<?php
					}
				closetable();
			echo "</div>";
		}
		echo "</div>";
	closetable();
}