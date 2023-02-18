<?php
// セッションの有効期限を30分に設定
ini_set('session.gc_maxlifetime', 1800);
session_start();
// 最後のアクセス時間が設定されていない場合、現在の時間をセット
if (!isset($_SESSION['last_access_time'])) {
    $_SESSION['last_access_time'] = time();
}
// 最後のアクセス時間から30分以上経過している場合、セッションを破棄
if (time() - $_SESSION['last_access_time'] > 1800) {
    session_unset();
    session_destroy();
}

require('library.php');

if(isset($_SESSION['id']) && isset($_SESSION['name'])){
    $id = $_SESSION['id'];
    $name = $_SESSION['name'];
}else{
    header('Location:login.php');
    exit();
}

$db = dbconnect();

// メッセージの投稿
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING);
	if(!$db){
		die($db->error);
	}
	$stmt = $db->prepare('insert into posts (message, member_id) VALUES(?,?)');
    if(!$stmt){
		die($db->error);
	}
    $stmt->bind_param('si', $message, $id);
    $success = $stmt->execute();
	if(!$success){
		die($db->error);
	}

    header('Location:index.php');
    exit();
}

$name = $_SESSION['name'];
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>掲示板</title>

    <link rel="stylesheet" href="style.css"/>
</head>

<body>
<div id="wrap">
    <div id="head">
        <h1>掲示板</h1>
    </div>
    <div id="content">
        <div style="text-align: right"><a href="logout.php">ログアウト</a></div>
        <form action="" method="post">
            <dl>
                <dt><?php echo h($name); ?>さん、メッセージをどうぞ</dt>
                <dd>
                    <textarea name="message" cols="50" rows="5"></textarea>
                </dd>
            </dl>
            <div>
                <p>
                    <input type="submit" value="投稿する"/>
                </p>
            </div>
        </form>

        <?php 
        $stmt = $db->prepare('select p.id, p.member_id, p.message, p.created, m.name, m.picture from posts p, members m where m.id=p.member_id order by id desc');
        if(!$stmt){
            die($db->error);
        }
        $success = $stmt->execute();
        if(!$success){
            die($db->error);
        }

        $stmt->bind_result($id, $member_id, $message, $created, $name, $picture);
        while($stmt->fetch()):
        ?>
        <div class="msg">
            <?php if($picture) :?>
                <img src="member_picture/<?php echo h($picture); ?>" width="48" height="48" alt=""/>
            <?php endif; ?>
            <p><?php echo h($message); ?><span class="name">（<?php echo h($name); ?>）</span></p>
            <p class="day"><a href="view.php?id=<?php echo h($id); ?>"><?php echo h($created); ?></a>
            <?php if($_SESSION['id'] === $member_id) :?>
                [<a href="delete.php?id=<?php echo h($id); ?>" style="color: #F33;">削除</a>]
            <?php endif; ?>
            </p>
        </div>
        <?php endwhile; ?>
    </div>
</div>
</body>

</html>