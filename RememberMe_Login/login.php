<?php
session_start();
include "cfg/dbconnect.php";
$email = $password = $err_msg = "";
$remember = "";

if (isset($_POST['submit'])) {
	$email = trim((string) ($_POST['email'] ?? ''));
	$password = trim((string) ($_POST['password'] ?? ''));
	$remember = isset($_POST['remember']) ? '1' : '';

	if ($email === '' || $password === '') {
		$err_msg = "Email and password are required.";
	} else {
		$sql = "SELECT id, email, name, password FROM users WHERE email = ? LIMIT 1";
		$stmt = mysqli_prepare($conn, $sql);

		if (!$stmt) {
			$err_msg = "Database error. Please try again.";
		} else {
			mysqli_stmt_bind_param($stmt, "s", $email);
			mysqli_stmt_execute($stmt);
			$result = mysqli_stmt_get_result($stmt);
			$row = mysqli_fetch_assoc($result);
			mysqli_stmt_close($stmt);

			if ($row) {
				$storedPassword = (string) $row['password'];
				$isValid = password_verify($password, $storedPassword)
					|| hash_equals($storedPassword, md5($password))
					|| hash_equals($storedPassword, $password);

				if ($isValid) {
					if (!password_get_info($storedPassword)['algo']) {
						$newHash = password_hash($password, PASSWORD_DEFAULT);
						$updateSql = "UPDATE users SET password = ? WHERE id = ?";
						$updateStmt = mysqli_prepare($conn, $updateSql);
						if ($updateStmt) {
							$userId = (int) $row['id'];
							mysqli_stmt_bind_param($updateStmt, "si", $newHash, $userId);
							mysqli_stmt_execute($updateStmt);
							mysqli_stmt_close($updateStmt);
						}
					}

					$_SESSION['name'] = $row['name'];
					$_SESSION['email'] = $row['email'];

					if ($remember !== '') {
						setcookie("remember_email", $row['email'], time() + 3600 * 24 * 365);
						setcookie("remember", "1", time() + 3600 * 24 * 365);
					} else {
						setcookie("remember_email", "", time() - 3600);
						setcookie("remember", "", time() - 3600);
					}

					header("Location: index.php");
					exit;
				}
			}

			$err_msg = "Incorrect Email Id/Password";
		}
	}
}
include 'header.php';

$rememberEmail = '';
if ($email !== '') {
	$rememberEmail = $email;
} elseif (isset($_COOKIE['remember_email'])) {
	$rememberEmail = (string) $_COOKIE['remember_email'];
}
?>

<form class="form-1" action="login.php" method="post">
	<h2>Login Form</h2>
	<?php if ($err_msg !== ''): ?>
		<p class="err-msg"><?php echo htmlspecialchars($err_msg, ENT_QUOTES, 'UTF-8'); ?></p>
	<?php endif; ?>

	<div class="col-md-12 form-group">
		<label for="email">Email Id</label>
		<input
			type="text"
			class="form-control"
			name="email"
			id="email"
			value="<?php echo htmlspecialchars($rememberEmail, ENT_QUOTES, 'UTF-8'); ?>"
			placeholder="Enter your Email Id"
			required
		>
	</div>

	<div class="col-md-12 form-group">
		<label for="password">Password</label>
		<input type="password" class="form-control" name="password" id="password" placeholder="Enter Password" required>
	</div>

	<div class="col-md-12 form-group">
		<input
			type="checkbox"
			name="remember"
			class="check"
			<?php echo ($remember !== '' || isset($_COOKIE['remember'])) ? 'checked' : ''; ?>
		>
		Remember Me
	</div>

	<div class="col-md-12 form-group text-right">
		<button type="submit" class="btn btn-primary" name="submit">Login</button>
		<a href="index.php" class="btn btn-danger" name="cancel">Cancel</a>
	</div>
</form>
</body>
</html>