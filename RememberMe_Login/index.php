<?php
include 'header.php';
?>
<h1>My Home Page</h1>

<?php if (isset($_SESSION['email'])): ?>
	<h2>
		Welcome <?php echo htmlspecialchars((string) $_SESSION['name'], ENT_QUOTES, 'UTF-8'); ?>,
		Your email id is <?php echo htmlspecialchars((string) $_SESSION['email'], ENT_QUOTES, 'UTF-8'); ?>
	</h2>
	<h3><a href="logout.php">Logout</a></h3>
<?php else: ?>
	<h3>Click <a href="login.php">here</a> to login</h3>
<?php endif; ?>

</body>
</html>
