<?php
//require 'header.php';
?>
<html>
	<head>
		 <meta charset="UTF-8"> 
		<title>Gilda Turnir Menadžer</title>
		<link rel="stylesheet" href="css/style.css">
		<link rel="stylesheet" href="css/jQueryUI.css">
		<link rel="stylesheet" href="css/tm.css">
		<link id ="finalSkin" rel="stylesheet" href="css/finalSkins/default.css">
		<script src="javascript/jQuery.js"></script>
		<script src="javascript/jQueryUI.js"></script>
		<script src="javascript/jquery.cookie.js"></script>
		<script src="javascript/autocomplete.js"></script>
		<script>
		function getRound() {
			data = {to_id: $.cookie('to_id')};
			$.ajax({
								url: 'switch_tm.php',
								type: 'POST',
								dataType: 'text',
								data: {
										action: 'getRound',
										data: data
										},
								success:
									function (response) {
										$('.tmMain').html(response);
									}
							});
		}
		var scoringColumns;
		$(document).ready (
			function () {
				//check to se if there is a tournement in progress/ an existing to_id cookie
				if ($.cookie('to_id') == undefined) {
					
					$.ajax ({
						url: 'switch_tm.php',
						type: 'POST',
						dataType: 'JSON',
						data: {action: 'getGamesAndSystems'},
						success:
							function (data) {
								scoringColumns = data.columns;
								$('#game').html(data.games);
								$('#game').change();
								$('#pairingSystem').html(data.systems);
							}
					});
				} else {
					getRound();
				}
			
			}
		);
		//change Bye value fileds basedon selected game system
		$(document).on('change', '#game',
			function () {
				$('#byeValues').html(scoringColumns[$(this).val()]);
			}
		);
		//add row of player input fields
		$(document).on('click', '#addPlayer',
			function() {
				var id = $('#players li').length + 1;
				var html = '<li id="' + id + '">' + id + '. Ime: <input class="pl_name autocomplete" type="text"> Frakcija: <input class="pl_faction" type="text"></li>';
				$('#players').append(html);
			}
		);
		//submit pairs from dialog
		$(document).on('click', '#savePairs',
			function(event) {
				event.preventDefault();
				var pairs = [];
				for (i = 0; i < $('#pl1 li').length; i++) {
					var pl1_id = $('#pl1 li:eq('+i+')').attr('id');
					var pl2_id = $('#pl2 li:eq('+i+')').attr('id');
					pairs.push({
						to_id: $.cookie('to_id'),
						rnd: $.cookie('currentRound'),
						pl1_id: pl1_id,
						pl2_id: pl2_id
					});
				}
				var data = {
					action: 'savePairs',
					pairs: pairs
				};
				$.ajax({
					url: 'switch_tm.php',
					type: 'POST',
					dataType: 'text',
					data: data,
					success:
						function (){
							$('#sortDialog').dialog('close');
							getRound();
						}
				});

			}
		);
		//submiting new torunament data via AJAX
		$(document).on('click', '#createTournament',
			function (event) {
				event.preventDefault();
				var byeValues = {};
				$('#byeValues :input').each( 
					function() {
						var id = $(this).attr('id');
						var value = parseInt($(this).val());
						byeValues[id]= value;
					}
				);
				var to_options = {
					optMaxRounds: parseInt($('#optMaxRounds').val()),
					optCurrentRound: 1,
					optPairingSystem: $('#pairingSystem').val(),
					optByeValues: byeValues
				};
				var players = [];
				$('#players li').each(
					function() {
						var inputVal = $(this).find('.pl_name').val();
						var player = [inputVal.slice(0, inputVal.lastIndexOf('-')), inputVal.slice(inputVal.lastIndexOf('-') + 1)];
						var cl_broj = player[1];
						var pl_name = player[0];
						var pl_faction = $(this).find('.pl_faction').val();
						players.push({
							cl_broj: cl_broj,
							pl_name: pl_name,
							pl_faction: pl_faction
						});
					}
				);
				//check for an odd number of players and ad Bye player if necessary
				if ((players.length / 2) != Math.floor(players.length / 2)) {
					players.push({
						cl_broj: 999,
						pl_name: 'BYE',
						pl_faction: ''
					});
				}
				var data = {
					to_name: $('#to_name').val(),
					to_game: $('#game').val(),
					to_options: to_options,
					players: players
				};
				$.ajax ({
					url: 'switch_tm.php',
					type: 'POST',
					dataType: 'JSON',
					data : {action: 'createTournament',
							data: data},
					success:
						function (response) {
							$('#sortDialog').html(response.randomPairs).dialog({
													width: '550px',
													modal: true
													});
							//$.cookie('to_id', response.to_id);
							//$.cookie('currentRound', 1);
							$('#pl1, #pl2').sortable({
								connectWith: '.playerSort'
							}).disableSelection();
						}
				});
				
			}
		);
		//save current round and generate pairs for the next one
		$(document).on('click', '#saveRound',
			function (event) {
				event.preventDefault();
				var rows = {};
				var i = 0;
				$('.playerResult').each (
					function(){
					rows[i] = {cl_broj: $(this).data('plid')};
					$(this).find('.columnField').each(
						function() {
							var colName = $(this).data('column');
							rows[i][colName] = $(this).val();
					});
					i++;
				});
				$.ajax({
					url: 'switch_tm.php',
					type: 'POST',
					dataType: 'text',
					data: {action: 'saveRound',
							to_id: $.cookie('to_id'),
							rows: rows},
					success: 
						function (response) {
							if ($('#saveRound').val() == 'SAČUVAJ RUNDU') {
								$('#sortDialog').html(response).dialog({
														width: '550px',
														modal: true
														});
								$('#pl1, #pl2').sortable({
									connectWith: '.playerSort'
								}).disableSelection();
							}
							
						},
					complete: 
						function () {
							if ($('#saveRound').val() == 'ZAVRŠI TURNIR') {
								$.ajax({
									url: 'switch_tm.php',
									type: 'POST',
									dataType: 'text',
									data: {action: 'finishTournament',
											to_id: $.cookie('to_id')},
									success: 
										function (response) {
											$('#sortDialog').html(response).dialog ({
												width: '700px',
												modal: true
												})
										}
								})
							}
						}
				})
			}
		);
		$(document).on('change', '#skinSelector',
			function (event) {
				var sheet = 'css/finalSkins/' + $(this).val();
				$('#finalSkin').remove();
				console.dir('<link id="finalSkin" rel="stylesheet" href="'+sheet+'">');
				$('head').append ('<link id="finalSkin" rel="stylesheet" href="'+sheet+'">');
			}
		);
		//modal loading screen while ajax is working
		$(document).on ({
			ajaxStart: 
				function () {
					$('body').addClass('loading');
				},
			ajaxStop:
				function() {
					$('body').removeClass('loading');
				}
		});
		</script>
	</head>
	<body>
		<div class="sankWrapper">
			<div class="tmMain sankBorders">
				<form id="newTournament" name="newTournament" action="" method="POST">
					<table class="formtable">
						<tr>
							<td>Naslov turnira: </td>
							<td><input type="text" id="to_name"></td>
						</tr>
						<tr>
							<td>Igra: </td>
							<td><select id="game" name="game"></select></td>
						</tr>
						<tr>
							<td>Sistem uparivanja: </td>
							<td><select id="pairingSystem" name="pairingSystem"></select></td>
						</tr>
						<tr>
							<td>Broj rundi: </td>
							<td><input type="text" class="to_options" id="optMaxRounds" size="2"></td>
						</tr>
						<tr>
							<td colspan="2" style="text-align: center;">Bodovanje za Bye:</td>
						</tr>
						<tr>
							<td colspan="2" id="byeValues">
							</td>
						</tr>
						<tr>
							<td colspan="2" style="text-align: center;">
							<div class="formButton" id="addPlayer">DODAJ IGRAČA</div>
							<ul id="players" style="list-style-type:none;">
							
							</ul>
							</td>
						</tr>
						<tr>
							<td colspan="2" style="text-align: center;"><input type="submit" id="createTournament" value="KREIRAJ TURNIR" class="formButton"></td>
						</tr>
					</table>
				</form>
				
			</div>
		</div>
		<div id="sortDialog"></div>
	<?php include 'footer.php';?>
	<div class ="modal"></div>
	</body>
</html>