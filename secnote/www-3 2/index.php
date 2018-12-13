<?php

session_start();
error_reporting(0);
require_once('db.php');
function register($user, $pass) {
	global $conn;
	$user = '0x' . bin2hex($user);
	$pass = '0x' . bin2hex($pass);
	$result = $conn->query("select * from user where user=$user");
	$data = $result->fetch_assoc();
	if ($data) return false;
	return $conn->query("insert into user (user,pass) values ($user,$pass)");	
}
function login($user, $pass) {
	global $conn;
	$user = '0x' . bin2hex($user);
	$result = $conn->query("select * from user where user=$user");
	$data = $result->fetch_assoc();
	if (!$data) return false;
	if ($data['pass'] === $pass) return true;
	return false;
}

function listnote($user) {
	global $conn ; 
	$user = '0x'.bin2hex($user);
	$result = $conn->query("select * from note where user=$user");
	$array = array();
	while ($row = $result->fetch_assoc()) {
		array_push($array, $row);
	}
	return $array;
}

function getnote($id, $user) {
	global $conn;
	$user = '0x'.bin2hex($user);
	$result = $conn->query("select * from note where id=$id and user=$user");
	$data = $result ->fetch_assoc();
	return $data;
}

function savenote($id, $user, $title, $content) {
	global $conn;
	if ($title) $title = '0x' . bin2hex($title); else $title = "''";
	if ($content) $content = '0x' . bin2hex($content); else $content = "''";
	$user = '0x'.bin2hex($user);
	$result = $conn->query("update note set title=$title, content=$content where id=$id and user=$user");
	return $result;
}

function newnote($user, $title, $content) {
	global $conn;
	if ($title) $title = '0x' . bin2hex($title); else $title = "''";
	if ($content) $content = '0x' . bin2hex($content); else $content = "''";
	$user = '0x'.bin2hex($user);
	$result = $conn->query("insert into note (user, title, content) values ($user, $title, $content)");
	return $result;
}

function delnote($id, $user) {
	global $conn;
	$id = (int)$id;
	$result = $conn->query("delete from note where id=$id and user='$user'");
	return $result;
}

$user = $_SESSION['user'];
$action = $_GET['action'];
$admin = $user === 'admin';
$conn = mysqli_connect(DB_HOST,DB_USER,DB_PASS,DB_DATABASE) or die("connect to mysql error!");
$conn->query("set names 'utf8'");

?>
<!DOCTYPE html>
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>SECnote2</title>
<link rel="stylesheet" href="./css/bootstrap.min.css">
<script src="./js/jquery.min.js"></script>
<script src="./js/bootstrap.min.js"></script>
<script language="javascript">
function del(id) {
	if (confirm('Are you sure to delete this note?')) {
		window.location.href = '?action=delete&id=' + id;
	}
}
</script>
</head>
<body>
<nav class="navbar navbar-default">
  <div class="container-fluid"> 
    <!--  www.zip -->
    <div class="navbar-header">
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#defaultNavbar1"><span class="sr-only">Toggle navigation</span><span class="icon-bar"></span><span class="icon-bar"></span><span class="icon-bar"></span></button>
      <a class="navbar-brand" href="#">SECnote2</a></div>
    <div class="collapse navbar-collapse" id="defaultNavbar1">
      <ul class="nav navbar-nav">
        <li class="active"></li>
        <?php if ($user) { ?><li><a href="?action=home">Home</a></li><?php } ?>
      </ul>
      <ul class="nav navbar-nav navbar-right">
        <?php if (!$user) { ?><li><a href="?action=register">Register</a></li><?php } ?>
        <?php if (!$user) { ?><li><a href="?action=login">Login</a></li><?php } ?>
		<?php if ($user) { ?><li><a>User: <?php echo htmlspecialchars($user,ENT_QUOTES); ?></a></li><?php } ?>
		<?php if ($admin) { ?><li><a href="?action=backup">Backup</a></li><?php } ?>
        <?php if ($user) { ?><li><a href="?action=logout">Logout</a></li><?php } ?>
      </ul>
    </div>
    <!-- /.navbar-collapse --> 
  </div>
  <!-- /.container-fluid --> 
</nav>
<div class="container-fluid">
  <div class="row">
    <div class="col-md-6 col-md-offset-3">
      <h1 class="text-center">Write Your Notes Freely</h1>
    </div>
  </div>
  <hr>
</div>
<div class="container">
  <div class="row text-center">
    <div class="col-md-6 col-md-offset-3">
<?php
switch ($action) {
	case 'login':
		if ($user) {
			header("HTTP/1.1 302 Found");
			header("Location: ?action=home");
		}
		elseif (isset($_POST['user']) && isset($_POST['pass']) && isset($_POST['code'])) {
			if ($_POST['code'] != $_SESSION['answer']) echo '<div class="alert alert-danger">Math Test Failed</div>';
			elseif ($_POST['user'] == '') echo '<div class="alert alert-danger">Username Required</div>';
			elseif ($_POST['pass'] == '') echo '<div class="alert alert-danger">Password Required</div>';
			elseif (!login((string)$_POST['user'], (string)$_POST['pass'])) echo '<div class="alert alert-danger">Incorrect</div>';
			else {
				$_SESSION['user'] = $_POST['user'];
				header("HTTP/1.1 302 Found");
				header("Location: ?action=home");
			}
			$_SESSION['answer'] = rand();
		}
		?>
    <h2>Login</h2>
    <form method="post">
      <div class="input-group"><span class="input-group-addon">User</span>
        <input type="text" name="user" class="form-control" placeholder="Input your username">
      </div>
	  <br />
      <div class="input-group"><span class="input-group-addon">Pass</span>
        <input type="password" name="pass" class="form-control" placeholder="Input your password">
      </div>
	  <br />
      <div class="input-group"><span class="input-group-addon">Captcha</span>
        <input type="text" name="code" class="form-control" placeholder="Do some math">
		<span class="input-group-addon"><img src="valicode.php" onclick="this.src='./valicode.php?'+Math.random();"></span>
      </div>
      <br /><br />
      <input type="submit" class="btn btn-default" value="Submit" /><br />
    </form>
		<?php
		break;
	case 'register':
		if ($user) {
			header("HTTP/1.1 302 Found");
			header("Location: ?action=home");
		}
		elseif (isset($_POST['user']) && isset($_POST['pass']) && isset($_POST['code'])) {
			if ($_POST['code'] != $_SESSION['answer']) echo '<div class="alert alert-danger">Math Test Failed</div>';
			elseif ($_POST['user'] == '') echo '<div class="alert alert-danger">Username Required</div>';
			elseif ($_POST['pass'] == '') echo '<div class="alert alert-danger">Password Required</div>';
			elseif (!register((string)$_POST['user'], (string)$_POST['pass'])) echo '<div class="alert alert-danger">User Already Exists</div>';
			else echo '<div class="alert alert-success">OK</div>';
			$_SESSION['answer'] = rand();
		}
		?>
    <h2>Register</h2>
    <form method="post">
      <div class="input-group"><span class="input-group-addon">User</span>
        <input type="text" name="user" class="form-control" placeholder="Input your username">
      </div>
	  <br />
      <div class="input-group"><span class="input-group-addon">Pass</span>
        <input type="password" name="pass" class="form-control" placeholder="Input your password">
      </div>
	  <br />
      <div class="input-group"><span class="input-group-addon">Captcha</span>
        <input type="text" name="code" class="form-control" placeholder="Do some math">
		<span class="input-group-addon"><img src="valicode.php" onclick="this.src='./valicode.php?'+Math.random();"></span>
      </div>
      <br /><br />
      <input type="submit" class="btn btn-default" value="Submit" /><br />
    </form>
		<?php
		break;
	case 'home':
		if (!$user) {
			header("HTTP/1.1 302 Found");
			header("Location: ?action=login");
		}
		?>
    <h2>Your Notes:</h2>
    <table class="table table-striped">
    <tr><th>Title</th></tr>
		<?php
		$array = listnote($user);
		foreach ($array as $row) {
			echo '<tr><td><a href="?action=edit&id=' . $row['id'] . '">' . $row['title'] . '</a></td></tr>';
		}
		?>
    </table>
    <button type="button" class="btn btn-success" onclick="window.location.href='?action=new'">New Note</button>
		<?php
		break;
	case 'new':
		if (!$user) {
			header("HTTP/1.1 302 Found");
			header("Location: ?action=login");
		}
		if (isset($_POST['title']) && isset($_POST['content'])) {
			$title = htmlspecialchars($_POST['title'],ENT_QUOTES);
			$content = htmlspecialchars($_POST['content'],ENT_QUOTES);
			if (!$title) $title = 'Untitled';
			if (strlen($content) > 1024) echo '<div class="alert alert-warning">Content Too Long</div>';
			else {
				if (newnote($user, $title, $content)) {
					header("HTTP/1.1 302 Found");
					header("Location: ?action=home");
				}
				else echo '<div class="alert alert-danger">Failed to save note</div>';
			}
		}
		?>
    <h2>Create Your Note</h2>
    <form method="post">
      <input type="text" name="title" class="form-control" placeholder="Enter your title here">
      <textarea class="form-control" name="content" rows="3" placeholder="Enter your note here"></textarea>
	  <button type="button" class="btn btn-default" onclick="window.location.href = '?action=home';">Back</button>
      <input type="submit" class="btn btn-success" value="Save" />
    </form>
		<?php
		break;
	case 'edit':
		if (!$user) {
			header("HTTP/1.1 302 Found");
			header("Location: ?action=login");
		}
		$id = (int)$_GET['id'];
		if (isset($_POST['title']) && isset($_POST['content'])) {
			$title = htmlspecialchars($_POST['title'],ENT_QUOTES);
			$content = htmlspecialchars($_POST['content'],ENT_QUOTES);
			if (!$title) $title = 'Untitled';
			if (strlen($content) > 1024) echo '<div class="alert alert-warning">Content Too Long</div>';
			else {
				if (savenote($id, $user, $title, $content)) echo '<div class="alert alert-success">OK</div>';
				else echo '<div class="alert alert-danger">Failed to save note</div>';
			}
		}
		if ($data = getnote($id, $user)) {
			?>
    <h2>Edit Your Note</h2>
    <form method="post">
      <input type="text" name="title" class="form-control" value="<?php echo $data['title']; ?>" placeholder="Enter your title here">
      <textarea class="form-control" name="content" rows="3" placeholder="Enter your note here"><?php echo $data['content']; ?></textarea>
      <button type="button" class="btn btn-default" onclick="window.location.href = '?action=home';">Back</button>
	  <input type="submit" class="btn btn-success" value="Save" />
      <button type="button" class="btn btn-danger" onclick="del(<?php echo $id; ?>);">Delete</button>
    </form>
			<?php
		}
		else {
			?>
			<h2>Invalid ID</h2>
			<?php
		}
		break;
	case 'delete':
		if (!$user) {
			header("HTTP/1.1 302 Found");
			header("Location: ?action=login");
		}
		$id = (int)$_GET['id'];
		delnote($id, $user);
		header("HTTP/1.1 302 Found");
		header("Location: ?action=home");
		break;
	case 'logout':
		session_destroy();
		header("HTTP/1.1 302 Found");
		header("Location: ?action=login");
		break;
	case 'backup':
		if (!$admin) {
			header("HTTP/1.1 302 Found");
			header("Location: ?action=home");
		}
		if (!empty($_POST['id']) && !empty($_POST['file'])) {

			$id = (int)$_POST['id'];
			chdir("./backupnotes/");
			$file = str_replace("..","",$_POST['file']);
			if (preg_match('/.+\.ph(p[3457]?|t|tml)$/', $file)) echo '<div class="alert alert-danger">Bad file extension</div>';
			else {
				$result = $conn->query("select * from note where id=$id");
				if (!$result->num_rows)  echo '<div class="alert alert-danger">Failed to backup</div>';
				else {
					$data = $result->fetch_assoc();
					$f = fopen($file, 'w');
					if($f){
							fwrite($f, $data['content']);
							fclose($f);
							echo '<div class="alert alert-success">Backup saved at ./backupnotes/' . $file . '</div>';
						}else{
							echo '<div class="alert alert-danger">Failed to backup</div>';
						}
				}
			}
	 }
		?>
    <h2>Backup</h2>
    <form method="post">
      <div class="input-group"><span class="input-group-addon">ID</span>
        <input type="text" name="id" class="form-control" placeholder="Enter the note id to backup">
      </div>
      <div class="input-group"><span class="input-group-addon">File</span>
        <input type="text" name="file" class="form-control" placeholder="Enter the backup file name">
      </div>
      <input type="submit" class="btn btn-success" value="Backup" />
    </form>
		<?php
		break;
	default:
		header("HTTP/1.1 302 Found");
		header("Location: ?action=home");
		break;
}
?>
    </div>
  </div>
  <div class="row">
    <div class="text-center col-md-6 col-md-offset-3">
      <h4>SECnote2</h4>
      <p>Copyright &copy; 2017</p>
    </div>
  </div>
  <hr>
</div>
</body>
</html>