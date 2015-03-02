
	<div class="container">


	<div>
		<h3>tduck - Lab 5</h3>
	</div>

	<div>
		<h5>Send a Message</h5>

		<form action="<?php echo site_url('lab5/receive_message'); ?>" id="send_message_form" method="POST">
			<textarea style="width:300px; resize:none" id="message" name="message"></textarea><br>
			<input type="submit">
		</form>
	</div>

	<hr>

	<div>
		<h5>Add a Peer</h5>
		<form action="<?php ?>" method="POST">
			<table>
				<tr>
					<td>Name:</td>
					<td style="padding-left:20px"><input type="text" name="peer_name"></input></td>
				</tr>
				<tr>
					<td>URL:</td>
					<td style="padding-left:20px"><input type="text" name="url"></input></td>
				</tr>
			</table>
			<br>
			<input type="submit">
		</form>
	</div>

	<hr>

	<div id="messages" style="padding-bottom:20px">
		<h5>Messages Received</h5>
		<?php ?>
	</div>

	<script>

		$(function() {


			$('#send_message_form').submit(function(e) {

				e.preventDefault();

				var msgData = new Object();
				var rumorData = new Object();

				rumorData.Originator = "tduck";
				rumorData.Text = $('#message').val();
				rumorData.MessageID = "";

				msgData.Rumor = rumorData;
				msgData.EndPoint = "<?php echo site_url('lab5/receive_message'); ?>";

				var dataString = JSON.stringify(msgData);
				console.log(dataString);

				$.ajax({
					contentType: "application/json",
					type: "POST",
					url: "<?php echo site_url('lab5/receive_message'); ?>",
					data: msgData,
					success: function(result) {
						console.log(result);
					},
					error: function() {
						console.log("Error sending message");
					}
				});

			});

		});

	</script>

</body>
</html>