<?php
require 'header.php';
$tablist = '<ul>';
$today = new DateTime(date('Y-m').'-01');
$today->sub(new DateInterval('P5M'));
$tablist.='<li><a href="switch_admin.php?action=listeMesec&month='.$today->format('Y-m').'">'.$today->format('M').'</a></li>';
for ($i=1; $i<= 5; $i++) {
	$today->add(new DateInterval('P1M'));
	$tablist.='<li><a href="switch_admin.php?action=listeMesec&month='.$today->format('Y-m').'">'.$today->format('M').'</a></li>';
}
$tablist.= '</ul>';
?>
<script>
$(document).ready (
	function() {
		$('.sanklistaWrapper').tabs({active: -1,
									beforeActivate: 
										function(event, ui) {
											$('.checkObracun:checkbox:checked').each (
												function() {
													$(this).prop('checked', false);
												}
											);
										}});
	}
);
</script>
<div class="sanklistaWrapper">
<?php echo $tablist; ?>
</div>
<div class="dnevnaWrapper"></div>