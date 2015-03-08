
	<div class="container">


	<div>
		<h3>tduck - Lab 5</h3>
		<form style="float:right" action="<?php echo site_url('lab5/reset'); ?>" method="POST">
			<input type="submit" value="Reset">
		</form>
	</div>

	<div>
		<h5>Send a Message</h5>

		<form action="<?php echo site_url('lab5/receive_message'); ?>" id="send_message_form" method="POST">
			<textarea style="width:300px; resize:none" id="message" name="message"></textarea><br>
			<input type="submit">
		</form>
	</div>

	<p>Propagation: <span id="propagateStatus">OFF</span>
	<input id="propagateToggle" type="submit" value="Toggle"></p>

	<hr>

	<div>
		<h5>Add a Peer</h5>
		<form action="<?php echo site_url('lab5/add_peer'); ?>" method="POST">
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


	<style type="text/css">

		#messages td
		{
			padding: 5px 20px;
		}

	</style>

	<div id="messages" style="padding-bottom:20px">
		<h5>Messages Received</h5>
		<table>

			<tr>
				<th style="width:200px">Time</th>
				<th style="width:100px">Originator</th>
				<th>Message ID</th>
				<th>Text</th>
			</tr>

			<?php if (isset($messages) && $messages !== NULL): ?>
				<?php foreach ($messages as $key => $msg): $msg = json_decode($msg); /*var_dump($msg);*/ ?>
					<tr>
						<td><?php echo date('Y-m-d H:i:s', floor($key)); ?></td>
						<td><?php echo $msg->Originator; ?></td>
						<td><?php echo $msg->MessageID; ?></td>
						<td><?php echo $msg->Text; ?></td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>

		</table>
	</div>

	<script>

		var propagateCheck = false;

		$(function() {

			// Number of seconds between propagate calls
			var n = 2;

			setInterval(function() {
			
				if (propagateCheck == true)
				{
					$.ajax('<?php echo site_url("lab5/propagate"); ?>',
						{
							type: 'POST',
							success: function(data)
							{
								console.log(data);
							},
							error: function(msg)
							{
								console.log(msg);
							}
						}
					);
				}

			}, n * 1000);


			$('#propagateToggle').click(function(e) {
				e.preventDefault();
				propagateCheck = !propagateCheck;

				if (propagateCheck == true)
				{
					$('#propagateStatus').html("ON");
				}

				else
				{
					$('#propagateStatus').html("OFF");
				}
			});


			$('#send_message_form').submit(function(e) {
				e.preventDefault();

				var msgData = new Object();
				var rumorData = new Object();

				rumorData.Originator = "tduck";
				rumorData.Text = $('#message').val();
				rumorData.MessageID = "<?php echo $uuid; ?>:<?php echo $next_msg; ?>";

				msgData.Rumor = rumorData;
				msgData.EndPoint = '<?php echo site_url("lab5/receive_message"); ?>';

				$.ajax('<?php echo site_url("lab5/receive_message"); ?>',
					{
				    type: 'POST',
				    data: JSON.stringify(msgData, null, 2),
				    contentType: 'application/json',
				    success: function(msg) {
				    	location.reload();
					},
					error: function(msg) {
						console.log(msg);
					}
				});
			});

		});

	</script>

</body>
</html>