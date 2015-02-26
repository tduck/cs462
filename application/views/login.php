<div class="container">

<h3>Login</h3>

<div style="background-color:yellow; margin-bottom:10px; width:200px">
<?php if (isset($message)) echo $message; ?>
</div>

<form action='../users/login' method="POST">

	<table>
		<tr>
			<td>Username:</td>
			<td><input type="text" name="username"></input></td>
		</tr>
		<tr>
			<td>Password:</td>
			<td><input type="password" name="password"></input></td>
		</tr>
	</table>
	<input type="submit">
</form>


</div>

</body>
</html>