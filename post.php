<?php

session_start();

date_default_timezone_set('Asia/Manila');

$title = '';
$date = '';
$detail = '';
$errors = [];

if(isset($_GET['action']) && $_GET['action'] == 'rewrite'){
    $_POST['input_title'] = $_SESSION['title'];
    $_POST['input_date'] = $_SESSION['date'];
    $_POST['input_detail'] = $_SESSION['detail'];

    $errors['rewrite'] = true;
}

    if (!empty($_POST)){
    $title = htmlspecialchars($_POST['title']);
    $date = htmlspecialchars($_POST['date']);
    $detail = htmlspecialchars($_POST['detail']);

    $errors = [];
            //ユーザー名の空チェック
    $count = strlen($title);
    if ($title == '') {
        $errors['title'] = 'blank';
    } elseif ($count < 1 || 24 < $count) {
        $errors['title'] = 'length';
    }
    if ($date == '') {
        $errors['date'] = 'blank';
    }
    $count = strlen($detail);
    if ($detail == '') {
        $errors['detail'] = 'blank';
    } elseif ($count < 1 || 140 < $count) {
        $errors['detail'] = 'length';
    }

    //画像名を取得
    $file_name = '';
    if(!isset($_GET['action'])){
        $file_name = $_FILES['input_img_name']['name'];
    }
    if (!empty($file_name)){
    //拡張子チェックの処理
            $file_type = substr($file_name, -3);//画像名の後ろから3文字を取得
            $file_type = strtolower($file_type);//大文字が含まれていた場合すべて小文字化
            if($file_type != 'jpg' && 'file_type' != 'png' && $file_type != 'gif'){
                $errors['img_name'] = 'type';
            }
        }else{
            $errors['img_name'] = 'blank';
        }

        if (empty($errors)){
            //$errorsが空だった場合はバリデーション成功
            //成功時の処理を記述する
            $date_str = date('YmdHis');
            $submit_file_name = $date_str.$file_name;
            $img_name = 'post_img/'.$submit_file_name;
            move_uploaded_file($_FILES['input_img_name']['tmp_name'], 'post_img/'.$submit_file_name);

            $_SESSION['title'] = $_POST['title'];
            $_SESSION['date'] = $_POST['date'];
            $_SESSION['detail'] = $_POST['detail'];
            $_SESSION['img_name'] = $submit_file_name;

    $dsn = 'mysql:dbname=mymemories;host=localhost';
    $user = 'root';
    $password = '';
    $dbh = new PDO($dsn,$user,$password);
    $dbh->query('SET NAMES utf8');

    $sql = 'INSERT INTO `feeds`(`title`,`date`,`detail`,`img_name`)VALUES(?,?,?,?)';

    $data[] = $title;
    $data[] = $date;
    $data[] = $detail;
    $data[] = $img_name;
    $stmt = $dbh->prepare($sql);
    $stmt->execute($data);

    header('Location: index.php');
    exit();

    }
    }

    $dbh = null;

?>


<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>My Memories</title>

    <!-- Bootstrap core CSS -->
    <link href="assets/css/bootstrap.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="assets/css/main.css" rel="stylesheet">
    <link href="assets/css/font-awesome.min.css" rel="stylesheet">

    <script src="https://code.jquery.com/jquery-1.10.2.min.js"></script>
    <script src="assets/js/chart.js"></script>


    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->
  </head>

  <body>

    <!-- Fixed navbar -->
    <div class="navbar navbar-default navbar-fixed-top">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href=""><i class="fa fa-camera" style="color: #fff;"></i></a>
        </div>
        <div class="navbar-collapse collapse">
          <ul class="nav navbar-nav navbar-right">
            <li class="active"><a href="index.php">Main page</a></li>
          </ul>
        </div><!--/.nav-collapse -->
      </div>
    </div>


    <div class="container">
      <div class="col-xs-8 col-xs-offset-2 thumbnail">
        <h2 class="text-center content_header">写真投稿</h2>
        <form method="POST" action="" enctype="multipart/form-data">
          <div class="form-group"  id="title">
            <label for="task">タイトル</label>
            <input name="title" class="form-control" value="<?php echo $title; ?>">
            <?php if (isset($errors['title']) && $errors['title'] == 'blank'): ?>
                <p class="text-danger">タイトルを入力してください</p>
            <?php endif; ?>
            <?php if (isset($errors['title']) && $errors['title'] == 'length'): ?>
                <p class="text-danger">タイトルを24文字以内で入力してください</p>
            <?php endif; ?>
          </div>
          <div class="form-group">
            <label for="date">日程</label>
            <input type="date" name="date" class="form-control" value="<?php echo $date; ?>">
          </div>
          <div class="form-group">
            <label for="detail">詳細</label>
            <textarea name="detail" class="form-control" rows="3"><?php echo $detail; ?></textarea><?php if (isset($errors['detail']) && $errors['detail'] == 'blank'): ?>
                <p class="text-danger">詳細を入力してください</p>
            <?php endif; ?>
            <?php if (isset($errors['detail']) && $errors['detail'] == 'length'): ?>
                <p class="text-danger">詳細を140文字以内で入力してください</p>
            <?php endif; ?><br>
          </div>
          <div class="form-group">
            <label for="img_name">写真</label>
            <input type="file" name="input_img_name" id="img_name" accept="image/*">
            <?php if(isset($errors['img_name']) && $errors['img_name'] == 'blank'){ ?>
                <p class="text-danger">画像を選択してください</p>
            <?php } ?>
            <?php if(isset($errors['img_name']) && $errors['img_name'] == 'type') {?>
                <p class="text-danger">拡張子が「jpg」「png」「gif」の画像を選択してください。</p>
            <?php } ?>
          </div><br>
          <input type="submit" class="btn btn-primary" value="投稿">
        </form>
      </div>
    </div>

    <div id="f">
      <div class="container">
        <div class="row">
          <p>I <i class="fa fa-heart"></i> Cebu.</p>
        </div>
      </div>
    </div>

    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="assets/js/bootstrap.js"></script>
  </body>
</html>