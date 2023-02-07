<?php
session_start();
require('library.php');

$error = [];
$email = '';
$password = '';
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $email  = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_STRING);
    $password  = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);
    if($email === '' || $password === ''){
        $error['login'] = 'blank';
    }else{
        // ログインチェック
        $db = dbconnect();
        $stmt = $db->prepare('select id, name, password from members where email=? limit 1');
        if(!$stmt){
            die($db->error);
        }
        $stmt->bind_param('s', $email);
        $success = $stmt->execute();
        if(!$success){
            die($db->error);
        }

        $stmt->bind_result($id, $name, $hash);
        $stmt->fetch();

        if(password_verify($password, $hash)){
            session_regenerate_id();
            $_SESSION['id'] = $id;
            $_SESSION['name'] = $name;
            header('Location:index.php');
            exit();
        }else{
            $error['login'] = 'failed';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>ログインする</title>
    <link rel="stylesheet" type="text/css" href="style.css"/>
</head>

<body>
<div id="wrap">
    <div id="head">
        <h1>ログインする</h1>
    </div>
    <div id="content">
        <div id="lead">
            <p>メールアドレスとパスワードを記入してログインしてください。</p>
            <p>入会手続きがまだの方はこちらからどうぞ。</p>
            <p>&raquo;<a href="join/">入会手続きをする</a></p>
        </div>
        <form action="" method="post">
            <dl>
                <dt>メールアドレス</dt>
                <dd>
                    <input type="text" name="email" size="35" maxlength="255" value="<?php echo h($email); ?>"/>
                    <?php if(isset($error['login']) && $error['login'] === 'blank'): ?>
                        <p class="error">* メールアドレスとパスワードをご記入ください</p>
                    <?php endif; ?>
                    <?php if(isset($error['login']) && $error['login'] === 'failed'): ?>
                        <p class="error">* ログインに失敗しました。正しくご記入ください。</p>
                    <?php endif; ?>
                </dd>
                <dt>パスワード</dt>
                <dd>
                    <input type="password" name="password" size="35" maxlength="255" value="<?php echo h($password); ?>"/>
                </dd>
            </dl>
            <div>
                <input type="submit" value="ログインする"/>
            </div>
        </form>
    </div>
</div>
</body>
</html>
