<div class="wrap">
	<h2>aitch ref!</h2>
	
	<?= $messages; ?>
	
	<p>possible urls seperated by space or new line</p>
	<form method="post">
		<textarea name="urls"><?= $urls; ?></textarea>
		
		<div>
			<input type="submit" value="Update"/>
		</div>
	</form>
</div>