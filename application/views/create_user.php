<div class="container">

<h3>Register</h3>

<div style="background-color:yellow; margin-bottom:10px; width:200px">
<?php if (isset($message)) echo $message; ?>
</div>

<form action="<?php echo site_url('users/create'); ?>" method="POST">
	<table>
		<tr>
			<td><label>*Username: </label></td>
			<td><input type="text" name="username"></input></td>
		</tr>
		<tr>
			<td><label>*Password: </label></td>
			<td><input type="password" name="password"></input></td>
		</tr>
		<tr>
			<td><label>Foursquare Email Address: </label></td>
			<td><input type="text" name="email"></input></td>
		</tr>
		<tr>
			<td><label>Foursquare Phone Number: </label></td>
			<td><input type="text" name="phone"></input></td>
		</tr>
	</table>
	<br>
	<input type="submit">
</form>

</div>

</body>
</html>