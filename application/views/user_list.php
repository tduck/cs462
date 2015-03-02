<div class="container">

	<h3>Welcome!</h3>

	<h5>Users</h5>

	<div style="background-color:yellow; margin-bottom:10px; width:200px">
	<?php if (isset($message)) echo $message; ?>
	</div>

	<table>
		<?php foreach ($json as $key => $user) : ?>

			<tr>
				<td><a href='<?php echo site_url("users/profile/" . $key); ?>'><u><?php echo $key; ?></u></a></td>
			</tr>

		<?php endforeach; ?>
	</table>
	
</div>

</body>
</html>