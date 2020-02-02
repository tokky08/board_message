<?php 

define("DB_HOST","fbf818555993");
define("DB_USER","root");
define("DB_PASS","pass");
define("DB_NAME","board");

define("PASSWORD","password");

$error_message = array();
$clean = array();
$now_date = null;
$success_message = null;
$message_array = array();

$message_id = null;
$message_data = array();
$sql = null;
$res = null;
$mysqli = null;


session_start();//セッションスタート

date_default_timezone_set('Asia/Tokyo');//タイムゾーン設定

//管理者としてログインしているかの確認

if(empty($_SESSION["admin_login"]) || $_SESSION["admin_login"] !== true){
    header("Location:index.php");
}

//DBから情報を取得する処理

$mysqli = new mysqli(DB_HOST,DB_USER,DB_PASS,DB_NAME);

if($mysqli->connect_errno){
    $error_message[] = "データの読み込みに失敗しました。 エラー番号 ".$mysqli->connect_errno.':'.$mysqli->connect_error;
}else{
    $mysqli->set_charset('utf8');
    $sql = "SELECT id,view_name,message,post_date FROM message ORDER BY post_date DESC";
    $res = $mysqli->query($sql);
    if($res){
        $message_array = $res->fetch_all(MYSQLI_ASSOC);
    }
    $mysqli->close();
}

//編集をクリックしたデータをDBから取得

if(!empty($_GET["message_id"])){
    $message_id = (int)htmlspecialchars($_GET["message_id"],ENT_QUOTES);
    $mysqli = new mysqli(DB_HOST,DB_USER,DB_PASS,DB_NAME);
    if($mysqli->connect_errno){
        $error_message[] = "データベースの接続に失敗しました。 エラー番号 ".$mysqli->connect_errno." : ".$mysqli->connect_error; 
    }else{
        $sql = "SELECT * FROM message WHERE id = $message_id";
        $res = $mysqli->query($sql);
        if($res){
            $message_data = $res->fetch_assoc();
        }else{
            header("Location:admin.php");
        }
        $mysqli->close();
    }
}

//編集をクリックした時のDB処理

if(!empty($_POST["message_id"])){
    //更新をクリックした時のDB処理
    if(!empty($_POST["update"])){
        $message_id = (int)htmlspecialchars($_POST["message_id"],ENT_QUOTES);
        if(empty($_POST["view_name"])){
            $error_message[] = "表示名を入力してください。";
        }else{
            $message_data["view_name"] =htmlspecialchars($_POST["view_name"],ENT_QUOTES);
        }
        if(empty($_POST["message"])){
            $error_message[] = "メッセージを入力してください。";
        }else{
            $message_data["message"] = htmlspecialchars($_POST["message"],ENT_QUOTES);
        }
        if(empty($error_message)){
            $mysqli = new mysqli(DB_HOST,DB_USER,DB_PASS,DB_NAME);
            if($mysqli->connect_errno){
                $error_message[] = "データベースの接続に失敗しました。";
            }else{
                $sql = "UPDATE message set view_name = '$message_data[view_name]', message = '$message_data[message]' WHERE id = $message_id";
                $res = $mysqli->query($sql);
            }
            $mysqli->close();
            if($res){
                header("Location: ./admin.php");
            }
        }
    }
    //削除をクリックした時のDB処理
    if(!empty($_POST["delete"])){
        $message_id = (int)htmlspecialchars($_POST["message_id"],ENT_QUOTES);
        $mysqli = new mysqli(DB_HOST,DB_USER,DB_PASS,DB_NAME);
        if($mysqli->connect_errno){
            $error_message[] = "データベースの接続に失敗しました。 エラー番号 " . $mysqli->connect_errno . " : " .$mysqli->connect_error;
        }else{
            $sql = "DELETE FROM message WHERE id = $message_id";
            $res = $mysqli->query($sql);
        }
        $mysqli->close();
        if($res){
            header("Location:admin.php");
        }
    }
}


?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>掲示板を作ってみた</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/meyer-reset/2.0/reset.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
</head>
<body>
    <header id="header">
        <h1 class="title">掲示板を作ってみた</h1>
        <h2 class="sub_title">〜 管理者画面へようこそ 〜</h2>
    </header>

    <main>
        <div class="btn_board" id="logout">
            <a href="index.php"><div class="btn">ログアウト</div></a>
        </div>
        <!-- ここに投稿されたメッセージを表示 -->
        <section id="output_board">
            <?php if(!empty($message_array)): ?>
                <?php foreach($message_array as $value): ?>
                    <div class="article">
                        <div class="info">
                            <h2 class="info_view_name"><?php echo $value["view_name"]; ?></h2>
                            <time class="info_post_date"><?php echo date("Y年m月d日 H:i",strtotime($value["post_date"])); ?></time>
                            <p class="edit_btn">
                                <a href="admin.php?message_id=<?php echo $value["id"]; ?>">編集</a>
                            </p>
                        </div>
                        <p class="message_text"><?php echo nl2br($value["message"]); ?></p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>

        <!--  編集画面  -->
        <section class="modal" id="modal_show">
            <form id="login_modal" method="post">
                <div id="close_btn" class="close_btn_edit">
                    <img class="close_btn_img" src="img/close_btn.png" alt="バツボタン">
                </div>
                <h2 class="edit_title">編集画面</h2>
                <div class="name_board_edit">
                    <label class="name_label_edit"　for="view_name">表示名</label>
                    <input class="name_input_edit" type="text" name="view_name" value="<?php if(!empty($message_data["view_name"])){echo $message_data["view_name"];}?>">
                </div>
                <div class="message_board_edit">
                    <label class="message_label_edit"　for="message">メッセージ</label>
                    <textarea class="message_input_edit" name="message"><?php if(!empty($message_data["message"])){echo $message_data["message"];}?></textarea>
                </div>
                <div class="flex_btn">
                    <div class="btn_board">
                        <input class="btn" type="submit" name="update" value="更新">
                    </div>
                    <div class="btn_board">
                        <input class="btn" type="submit" name="delete" value="削除">
                    </div>
                </div>
                <input type="hidden" name="message_id" value="<?php echo $message_data["id"]; ?>">
            </form>
        </section>

    </main>

    <footer id="footer">
        
    </footer>

    <script>
        $(function() {
            var urlParam = location.search.substring(1);
            if(urlParam){
                $("#modal_show").fadeIn();
            }
        });

        $(function() {
            $("#close_btn").click(function(){
                $("#modal_show").fadeOut();
            })
        });
    </script>

</body>



</html>