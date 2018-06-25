<?php
require 'header.php';
$kategorije = Classes\Kategorija::getKategorije($db);
$tabs = '<div id="tabs"><ul>';
foreach($kategorije as $kat) {
	$tabs.='<li><a href="switch_admin.php?action=art_list&kat_id='.$kat['kat_id'].'">'.$kat['kat_naziv'].'</a></li>';
}
$tabs .= '<li id="artNew"><a href= "switch_admin.php?action=art_new">Dodaj artikal</a></li>';
$tabs .= '<li id="clNew"><a href= "switch_admin.php?action=cl_new">Dodaj člana</a></li>';
$tabs .= '<li id="clUpdate"><a href= "switch_admin.php?action=cl_list">Spisak članova</a></li>';
$tabs .= '<li id="liste"><a href= "switch_admin.php?action=liste">Šank liste</a></li>';
$tabs .= '<li id="duznici"><a href = "switch_admin.php?action=duznici">Dužnici</a></li>';
$tabs .= '</ul></div>';

?>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Gilda Admin Panel</title>
		<link rel="stylesheet" href="css/style.css">
		<link rel="stylesheet" href="css/jQueryUI.css">
		<script src="javascript/jQuery.js"></script>
		<script src="javascript/jQueryUI.js"></script>
		<script src="javascript/jquery.cookie.js"></script>
		<script>
			$(document).ready (
				function() {
					var lastActive = $.cookie('lastActive');
					$.removeCookie('lastActive');
					var activeIndex = $('li#'+lastActive).index();
					var activeTab = 0;
					if (activeIndex > -1 ) {
						activeTab = activeIndex;
					}
					$('div#tabs').tabs({ 
						activate: function (event, ui) {
							$('div#errWrapper').empty();
							
						},
						load: function (event, ui) {
							if (lastActive == 'artUpdate') {
								var artId = $.cookie('artUpdateId');
								$.removeCookie('artUpdateId');
								$('#artDialog').load('switch_admin.php?action=art_update&art_id='+artId)
												.dialog({
													width: '550px',
													modal: true,
													close: function (event, ui) {
															lastActive = '';
														}
													});
							}
							if (lastActive == 'clUpdate') {
								var clBroj = $.cookie('clUpdateBroj');
								$.removeCookie('clUpdateBroj');
								$('#clDialog').load('switch_admin.php?action=cl_update&cl_broj='+clBroj)
												.dialog({
													width: '550px',
													modal: true,
													close: function (event, ui) {
															lastActive = '';
														}
													});
							}
						
						},
						active: activeTab
					});
					
					
				}
			);
			$(document).on('click', '.artikalLink',
				function (event) {
					event.preventDefault();
					var artId = $(this).data('art_id');
					$('#artDialog').load('switch_admin.php?action=art_update&art_id='+artId)
									.dialog({ width: '550px', modal: true,});
				}
			);
			$(document).on('click', '.pagelink', 
				function (event) {
					event.preventDefault();
					var tab =$('.pagewrapper').parent();
					tab.empty();
					tab.load($(this).attr('href'));
				}
			);
			$(document).on('click', '.clanLink',
				function (event) {
					event.preventDefault();
					var clBroj = $(this).data('cl_broj');
					$('#artDialog').load('switch_admin.php?action=cl_update&cl_broj='+clBroj)
									.dialog({ width: '550px', modal: true,});
				}
			);
			$(document).on('click', '#azuriraj',
				function (event) {
					event.preventDefault();
					var error = false;
					var fields = [];
					$('.artStanje').each(
						function() {
							var value = $(this).val();
							if (value.length > 0 && $.isNumeric(value)) {
								fields.push ({art_id: $(this).data('art_id'),
												art_stanje: value
												});
							}
							else {
								error = true;
							}
						}
					);
					if (error == false) {
						var postData = {fields: fields, submit: $('#azuriraj').val()};
						$.ajax({
							type: 'POST',
							url: 'transact_admin.php',
							data: postData,
							dataType: 'text',
							success: function (response) {
								alert(response);
							}
						});
					}
					else {
						alert('Svako polje mora da sadrži broj!');
					}
				}
			);
			$(document).on('click', '.delButton',
				function (event) {
					event.preventDefault();
					var postData = {art_id: $(this).data('art_id'),
									submit: 'deleteArtikal'
									};
					$.ajax ({
						type: 'POST',
						url: 'transact_admin.php',
						data: postData,
						dataType: 'text',
						success: function(response) {
							var index = $('.ui-tabs-active').index();
							$('#tabs').tabs('load', index);
						}
						
					});
					
				}
			);
			$(document).on('click', '.checkObracun',
				function (event) {
					var total = 0;
					$('.checkObracun:checkbox:checked').each (
						function () {
							total += parseInt($(this).parent().parent().children('.ukupno').html().replace('.',''));
						}
					);
					$('.obracunCalc').empty();
					$('.obracunCalc').text(total.toLocaleString('sr-SR'));
					console.log(total);
					/*if ($(this).is(':checked')) {
						var obracun = parseInt($('.obracunCalc').html().replace('.','')) + parseInt($(this).parent().parent().children('.ukupno').html().replace('.',''));
					}
					else {
						var obracun = parseInt($('.obracunCalc').html().replace('.','')) - parseInt($(this).parent().parent().children('.ukupno').html().replace('.',''));
					}
				$('.obracunCalc').html(parseInt(obracun).toLocaleString('sr-SR'));*/
				}
			);
			$(document).on('click', '.listRow',
				function (event) {
					var data = {action: 'dnevnalista',
								rd_id: $(this).attr('id')};
					$.ajax({
						url: 'switch_admin.php',
						type: 'POST',
						data: data,
						DataType: 'text',
						success:
							function (response) {
								$('.dnevnaWrapper').html(response);
							}
					});
				}
			);
			$(document).on('click', '#addNapomena',
				function (event) {
					rd_id = $(this).data('rd_id');
					$('<div id="napomenaDialog"><textarea id="np_sadrzaj" cols="40" rows="10"></textarea></div>').dialog({
						width: 380,
						close: 
								function(event, ui) {
									$('#napomenaDialog').remove();
								},
						buttons : [{
							text: 'UNESI',
							click: 
								function () {
									var data = {action: 'newnapomena',
												rd_id: rd_id,
												np_sadrzaj: $('#np_sadrzaj').val()};
									$.ajax({
										url: 'switch_admin.php',
										type: 'POST',
										data: data
									})
									.done (
										function(){
										$('tr#'+rd_id).click();
										}
									);
									$('#'+rd_id).trigger('click');
									$(this).dialog('close');

								}
						}]
					});
				}
			);
			$(document).on('click', '.stavkaButton',
				function(event) {
					
					var data = {action: 'payartikal',
								sl_id: $(this).data('sl_id')};
					$.ajax({
						url: 'switch_sank.php',
						type: 'POST',
						data: data
					})
					.done(
						function() {
							rd_id = $('#addNapomena').data('rd_id');
							$('#'+rd_id).trigger('click');
						}
					);
				}
			);
			$(document).on('click', '.duznik', 
				function (event) {
					$('#duznikDialog').empty();
					var id = $(this).attr('id');
					$('#duznikDialog').load('switch_admin.php?action=getDuznikArtikli&cl_broj='+id)
									.dialog({ width: '550px', modal: true,});
				}
			);
		</script>
	</head>
	<body>
		<div class="adminWrapper">
			<?=$tabs;?>
		</div>
	<?=include 'footer.php';?>
	</body>
</html>