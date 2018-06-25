<?php include 'autoloader.php'; ?>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Gilda Rang Liste</title>
		<link rel="stylesheet" href="css/style.css">
		<link rel="stylesheet" href="css/rankings.css">
		<link id ="finalSkin" rel="stylesheet" href="css/finalSkins/default.css">
		<script src="javascript/jQuery.js"></script>
		<script src="javascript/jQueryUI.js"></script>
		<script src="javascript/jquery.cookie.js"></script>
		<script src="javascript/autocomplete.js"></script>
		<script>

		//modal loading screen while ajax is working
		$(document).ready (
			function () {
				$('.gamelist li').hover(
					function () {
						if (!$(this).hasClass('selected')){
							$(this).css('opacity', '0.6');
						}
					},
					function () {
						if (!$(this).hasClass('selected')){
							$(this).css('opacity', '0.3');
						}
					}
				);
			}
		);
		$(document).on ('click', '.gamelist li',
			function () {
				if ($('.selected')) {
					$('.selected').css('opacity', '0.3');
					$('.selected').removeClass('selected');
					$.ajax ({
						type: 'POST',
						url: 'rankAJAX.php',
						dataType: 'text',
						data: {
								gm_id: $(this).data('gm_id')
								},
						success: 
							function(response) {
								$('.ranklist').html(response);
							}
					});
				}
				$(this).addClass('selected');
			}
		);
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
			<div id="tabs">
				<?php
					echo Classes\Rankings::getGames();
				?>
			</div>
			<div class="ranklist">

			</div>
		</div>

	<?php include 'footer.php';?>
	<div class ="modal"></div>
	</body>
</html>