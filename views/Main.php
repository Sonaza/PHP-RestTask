<!DOCTYPE html>
<html>
<head>
	<title>Lamia Rest API Task</title>
	<meta charset="utf-8">
	<style>
		body
		{
			font-family: 'Roboto', sans-serif;
			font-size: 12pt;
			overflow-y: scroll;
			padding-bottom: 6rem;
		}
		
		pre
		{
			font-family: 'Consolas', monospace;
			font-size: 11pt;
			max-height: 30rem;
			overflow-y: scroll;
			padding: 0.7rem;
			border: 1px solid #ccc;
			white-space: pre-wrap;
			white-space: -moz-pre-wrap;
			white-space: -pre-wrap;
			white-space: -o-pre-wrap;
			word-wrap: break-word;
		}
		
		.api
		{
			border: 1px solid #A1E0F0;
			background: #D0F1FA;
			padding: 0.5rem;
		}
		
		code
		{
			background: #fff;
			border: 1px solid #A1E0F0;
		}
	</style>
</head>
<body>
	<h1>Lamia Rest API Task</h1>
	<hr>
	
	<?php if (!is_null($jwt_token)): ?>
		
		<form action="?" method="get">
	
			<h2>JWT Web Token</h2>
			<p>
				Requesting a JWT web token for API authorization.
			</p>
			<p class="api">
				POST request to <code><?= $base_endpoint_url; ?>/login</code> with <code>username</code> and <code>password</code> fields. Testing with values <code><?= $jwt_username; ?></code> and <code><?= $jwt_password; ?></code>.
			</p>
			<pre><?= $jwt_token; ?></pre>
			
			<hr>
			
			<h2>Books API</h2>
			<p>
				Requesting a book with ISBN <input type="text" name="isbn" value="<?= $books_isbn; ?>"> <input type="submit" value="Request">
			</p>
			<p class="api">
				GET request to <code><?= $base_endpoint_url; ?>/getBook?isbn=<?= $books_isbn; ?></code>. JWT required as bearer token in Authorization header.
			</p>
			<pre><?= $books_response; ?></pre>
			
			<hr>
			
			<h2>Movies API</h2>
			<p>
				Requesting a movie titled <input type="text" name="title" value="<?= $movies_title; ?>">
				released in <input type="text" name="year" value="<?= $movies_year; ?>">
				with <select name="plot">
					<option value="full" <?= $movies_plot == 'full' ? 'selected' : '' ?>>full</option>
					<option value="short" <?= $movies_plot == 'short' ? 'selected' : '' ?>>short</option>
				</select> plot synopsis. <input type="submit" value="Request">
			</p>
			<p class="api">
				GET request to <code><?= $base_endpoint_url; ?>/getMovie?title=<?= $movies_title; ?>&amp;year=<?= $movies_year; ?>&amp;plot=<?= $movies_plot; ?></code>. JWT required as bearer token in Authorization header.
			</p>
			<pre><?= $movies_response; ?></pre>
		
		</form>
		
	<?php else: ?>
		
		<h2>JWT Web Token Authorization failed</h2>
		<p>
			Cannot request books or movies without valid authorization token.
		</p>
		
	<?php endif; ?>
</body>
</html>
