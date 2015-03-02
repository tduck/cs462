<html>
<head>
	
	<title>tduck - Lab 5</title>
	<link href="http://getbootstrap.com/2.3.2/assets/css/bootstrap.css" rel="stylesheet">
    <link href="http://getbootstrap.com/2.3.2/assets/css/bootstrap-responsive.css" rel="stylesheet">

</head>
<body>
	<div class="container">

	<h3>tduck - Lab 5</h3>

	<div>
		<h5>Send a Message</h5>

		<form action="<?php echo site_url('lab5/receive_message'); ?>" method="POST">
			<textarea style="width:300px; resize:none" name="message"></textarea><br>
			<input type="submit">
		</form>
	</div>


	<div id="messages">
		<h5>Messages Received</h5>
		<?php ?>
	</div>

</body>
</html>