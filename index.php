<?php
require 'header.php';
include 'classes/artikli.php';
$kategorije = Kategorija::getKategorije($db);
$tabs = '<div id="tabs"><ul>';
foreach($kategorije as $kat) {
	$tabs.='<li><a href="includes/sank_artikli.php?kat_id='.$kat['kat_id'].'">'.$kat['kat_naziv'].'</a></li>';
}


$tabs .= '</ul></div>';

?>
<html>
	<head>
		<title>Gilda Šank Lista</title>
		<link rel="stylesheet" href="css/style.css">
		<link rel="stylesheet" href="css/jQueryUI.css">
		<script src="javascript/jQuery.js"></script>
		<script src="javascript/jQueryUI.js"></script>
		<script src="javascript/jquery.cookie.js"></script>
		<script>
		var dragItem = {};
		//funkcija za drop event na accordion
		function dropFn (event, ui) {
						var accordion = $(this);
						$(accordion).orderArtikal().done(
							function() {
								var accPanel = $(accordion).next();
								getAccordionClan($(accordion).attr('id')).done(
									function (html) {
										accPanel.html('');
										accPanel.append(html);
									}
								);
						});
			var index = $(this).index('div > h3');
			$('#accordion').accordion('option', 'active', index);
			};
		//azuriranje accordion polja za dati clanski id
		function getAccordionClan(cl_broj) {
			var data = {action: 'getaccordionclan',
						cl_broj: parseInt(cl_broj),
						rd_id: parseInt($.cookie('rd_id'))};
			return $.ajax({
				url: 'switch_sank.php',
				type: 'POST',
				dataType: 'html',
				data: data
			});

		}
		$.fn.extend({
			orderArtikal: 
				function () {
					var dropTarget = $(this);
					var cl_broj = (isNaN(dropTarget.attr('id'))) ? '' : dropTarget.attr('id');
					var data = {action: 'orderartikal',
								art_id: dragItem.art_id,
								rd_id: parseInt($.cookie('rd_id')),
								cl_broj: cl_broj
								};
					return $.ajax({
						url: 'switch_sank.php',
						type: 'POST',
						dataType: 'text',
						data: data
					});
				}
		});
		$(document).ready(
			function() {
				//proveravamo da li se nastavlja prethodni radni dan po rd_id cookie-ju
				if ($.cookie('rd_id') == undefined) {
					$('.sankWrapper').css('visibility', 'hidden');
				}
				else {
					$('.sankWrapper').css('visibility', 'visible');
					$('.radniDugme').removeClass('radniPocni');
					$('.radniDugme').addClass('radniZavrsi');
					$('.radniDugme').html('ZAVRŠI RADNI DAN');
					//populisemo sankliste
					//populacija accordiona
					var data = {action: 'rebuildaccordion',
								rd_id: parseInt($.cookie('rd_id'))};
					$.ajax ({
						url: 'switch_sank.php',
						type: 'POST',
						dataType: 'json',
						data: data
					})
					.done (
								function(response) {
								$.each (response, 
									function (key, item) {
										
										var accordionElement = '<h3 id="'+key+'">'+item+'</h3><div></div>';
										var id = key;
										$('#accordion').append(accordionElement);
										$('#accordion').accordion('refresh');
										$('h3#'+key).droppable ({
											drop: dropFn
										});
									}
								);
							}
					);
				//populacija blank sankilste
				getAccordionClan('').done(
										function (html) {
											$('#sankBlank').html('');
											$('#sankBlank').append(html);
										}
									);
				
				}
				//droppable za blanko sanklistu
				$('#sankBlank').droppable({
					over:
						function (event, ui) {
							$('#sankBlank').addClass('blankOver');
						},
					out:
						function (event, ui) {
							$('#sankBlank').removeClass('blankOver');
						},
					drop: 
						function (event, ui) {
							$('#sankBlank').removeClass('blankOver');
							$('#sankBlank').orderArtikal().done(
								function() {
									
									getAccordionClan('').done(
										function (html) {
											$('#sankBlank').html('');
											$('#sankBlank').append(html);
										}
									);
							});
						}
						
				});
				//ucitavanje tablova sa artiklima
				$('#tabs').tabs({
					load: 
						function (event, ui) {
							
							$('.artikalTile').draggable ({
								helper: 'clone',
								opacity: 0.7,
								start:
									function (event, ui) {
										dragItem = {art_id: $(this).data('art_id'),
													art_naziv: $(this).children(':first').html()};
									}
							});
						}
				});
				$('#accordion').accordion({
					heightStyle: 'content',
					beforeActivate:
						function (event, ui) {
							getAccordionClan(ui.newHeader.attr('id')).done(
								function (html) {
									ui.newPanel.html('');
									ui.newPanel.append(html);
								}
							);	
						}
				});
				$('#clanAutocomplete').autocomplete({
					minLength: 2,
					source:
						function (request, response) {
							var data = [];
							$.ajax ({
								url: 'switch_sank.php',
								type: 'POST',
								dataType: 'json',
								data: {action: 'autocomplete', autocomplete: request.term},
								success: 
									function (data) {	
										
										response($.map(data,
											function (n, i) {
												
												return{label: n.cl_imeprezime, value: n.cl_imeprezime + '-' + n.cl_broj};
											}
										));
									}

							});
							response(data);
						}
					
				});
			
			
			}
		);
		$(document).on('click', '#clanDodaj',
			function (event) {
				event.preventDefault();
				var clan = $('#clanAutocomplete').val().split('-');
				if ($('#'+clan[1]).length) {
					alert ('Taj clan je vec na listi');
				}
				else if (clan.length != 2){
					alert ('Clan mora biti pravilno unet (Ime-cl. broj)');
				}
				else {
					var accordionElement = '<h3 id="'+clan[1]+'">'+clan[0]+'</h3><div></div>';
					var id = clan[1];
					$('#accordion').append(accordionElement);
					$('#accordion').accordion('refresh');
					$('h3#'+clan[1]).droppable ({
						drop: dropFn
					});
				}
				
			}
		);
		//event listener za klik na dugme za radni dan
		$(document).on('click', '.radniDugme',
			function (event) {
				if ($.cookie('rd_id') == undefined){
					$.ajax ({
						url: 'switch_sank.php',
						type: 'POST',
						dataType: 'text',
						data: {action: 'newradnidan'},
						success:
							function (response) {
								$.cookie('rd_id', parseInt(response));
								$('.sankWrapper').css('visibility', 'visible');
								$('.radniDugme').removeClass('radniPocni');
								$('.radniDugme').addClass('radniZavrsi');
								$('.radniDugme').html('ZAVRŠI RADNI DAN');
							}
					});
				}
				else {
					$('#sankBlank').html('');
					$('#accordion').html('');
					$.ajax ({
						url: 'switch_sank.php',
						type: 'POST',
						dataType: 'text',
						data: {action: 'endradnidan',
								rd_id: $.cookie('rd_id')},
						success:
							function(response) {
								$('.sankWrapper').css('visibility', 'hidden');
								$('.radniDugme').addClass('radniPocni');
								$('.radniDugme').removeClass('radniZavrsi');
								$('.radniDugme').html('ZAPOČNI RADNI DAN');
								$.removeCookie('rd_id');
							}
					});
				}
			}
		);
		//event listener za klik na '-' dugme 
		$(document).on('click', '.minus',
			function (event) {
				var accPanel = $(this).parent().parent();
				var data = {action: 'payartikal',
				sl_id: $(this).attr('id'),
				cl_broj: $(this).parent().parent().prev().attr('id'),
				rd_id: $.cookie('rd_id')};
				var minus = $(this);
				$.ajax ({
					url: 'switch_sank.php',
					type: 'POST',
					data: data,
					success:
						function (response) {
							accPanel.html(response);
						}
				});
			}
		);
		</script>
	</head>
	<body>
	<div id="radniDanBtn" class="radniPocni radniDugme">ZAPOČNI RADNI DAN</div>
		<div class="sankWrapper">
			<div class="sankArtikli sankBorders">
			<?=$tabs;?>
			</div>
			<div id ="sankBlank" class="sankLista sankBorders"></div>
			<div id="sankClanovi" class="sankLista sankBorders">
				<input type="text" id="clanAutocomplete">
				<input class="formButton" type="submit" id="clanDodaj" value="DODAJ">
				<div id="accordion">
				
				</div>
			</div>
		</div>
	<?=include 'footer.php';?>
	</body>
</html>