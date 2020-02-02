<?php 

/* DockerでのDB情報
define("DB_HOST","fbf818555993");
define("DB_USER","root");
define("DB_PASS","pass");
define("DB_NAME","board");
*/

define("DB_HOST","us-cdbr-iron-east-04.cleardb.net");
define("DB_USER","bbbd77314bda7d");
define("DB_PASS","e1a24f0f");
define("DB_NAME","heroku_19e0e083d404b8b");

define("PASSWORD","password");

$error_message = array();
$clean = array();
$now_date = null;
$success_message = null;
$message_array = array();
$mysqli = null;
$res = null;
$sql = null;

session_start();//セッションスタート

date_default_timezone_set('Asia/Tokyo');//タイムゾーン設定


//送信ボタンが押されてから情報がDBへ保存されるまでの処理
if(!empty($_POST["btn_submit"])){

    //表示名の入力
    if(empty($_POST["view_name"])){
        $error_message[] = "表示名を入力してください";
    }else{
        $clean["view_name"] = htmlspecialchars($_POST["view_name"],ENT_QUOTES);
        $clean["viea_name"] = preg_replace('/\\r\\n|\\n|\\r/', '', $clean['view_name']);
        $_SESSION["view_name"] = $clean["view_name"];
    }

    //メッセージの入力
    if(empty($_POST["message"])){
        $error_message[] = "メッセージを入力してください";
    }else{
        $clean["message"] = htmlspecialchars($_POST["message"],ENT_QUOTES);
        $clean['message'] = preg_replace( '/\\r\\n|\\n|\\r/', '<br>', $clean['message']);
    }

    //DBへ保存
    if(empty($error_message)){
        //DBへ接続
        $mysqli = new mysqli(DB_HOST,DB_USER,DB_PASS,DB_NAME);

        if($mysqli->connect_errno){
            $error_message[] = "書き込みに失敗しました。 エラー番号 ".$mysqli->connect_errno.':'.$mysqli->connect_error;
        }else{
            $mysqli->set_charset("utf8");//文字化け防止
            $now_date = date("Y-m-d H:i:s");//現時刻取得
            $sql = "INSERT INTO message (view_name,message,post_date) VALUES ('$clean[view_name]','$clean[message]','$now_date')";
            $res = $mysqli->query($sql);

            if($res){
                $success_message = "メッセージを書き込みました";
            }else{
                $error_message[] = "書き込みに失敗しました";
            }

            $mysqli->close();
        }
    }
}


//DBから情報を取得する処理

$mysqli = new mysqli(DB_HOST,DB_USER,DB_PASS,DB_NAME);

if($mysqli->connect_errno){
    $error_message[] = "書き込みに失敗しました。 エラー番号 ".$mysqli->connect_errno.':'.$mysqli->connect_error;
}else{
    $mysqli->set_charset('utf8');
    $sql = "SELECT view_name,message,post_date FROM message ORDER BY post_date DESC";
    $res = $mysqli->query($sql);
    if($res){
        $message_array = $res->fetch_all(MYSQLI_ASSOC);
    }
    $mysqli->close();
}

//ログインパスワードの確認処理

if(!empty($_POST["btn_submit_pass"])){
    if(!empty($_POST["admin_password"]) && $_POST["admin_password"] === PASSWORD){
        $_SESSION["admin_login"] = true;
        header("Location:admin.php");
    }else{
        $error_message[] = "ログインに失敗しました";
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
        <?php if(!empty($success_message)): ?>
            <p class="success_message"><?php echo "*".$success_message; ?></p>
        <?php endif; ?>
        <?php if(!empty($error_message)): ?>
            <ul class="error_message">
                <?php foreach($error_message as $value): ?>
                    <li><?php echo "*".$value; ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </header>

    <main>
        <!-- ここにメッセージの入力フォームを設置 -->
        <form method="post" id="input_board">

            <div class="name_board">
                <label class="name_label"　for="view_name">表示名</label>
                <input class="name_input" type="text" name="view_name" value="<?php if(!empty($_SESSION["view_name"])){echo $_SESSION["view_name"];}?>">
            </div>
            <div class="message_board">
                <label class="message_label"　for="message">メッセージ</label>
                <textarea class="message_input" name="message"></textarea>
            </div>
            <div class="btn_flex">
                <div class="btn_board" id="login_show">
                    <p class="btn">管理者画面へ</p>
                </div>
                <div class="btn_board">
                    <input class="btn input_btn" type="submit" name="btn_submit" value="書き込む">
                </div>
            </div>
            
        </form>


        <!-- ここに投稿されたメッセージを表示 -->
        <section id="output_board">
            <?php if(!empty($message_array)): ?>
                <?php foreach($message_array as $value): ?>
                    <div class="article">
                        <div class="info">
                            <h2 class="info_view_name"><?php echo $value["view_name"]; ?></h2>
                            <time class="info_post_date"><?php echo date("Y年m月d日 H:i",strtotime($value["post_date"])); ?></time>
                        </div>
                        <p class="message_text"><?php echo nl2br($value["message"]); ?></p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>

        <!-- 管理者画面へログイン -->
        <section class="modal" id="modal_show">
            <form id="login_modal" method="post">
                <div id="close_btn">
                    <img class="close_btn_img" src="img/close_btn.png" alt="バツボタン">
                </div>
                <h2 class="admin_login_title">管理者画面ログイン</h2>
                <div class="login_board">
                    <label class="pass_label" for="admin_password">パスワード</label>
                    <input class="pass_input" type="password" name="admin_password" placeholder="パスワードを入力してください">
                </div>
                <div class="btn_board">
                    <input class="btn" type="submit" name="btn_submit_pass" value="ログイン">
                </div>
            </form>
        </section>
    </main>

    <footer id="footer">
        
    </footer>

    <script>
        $(function() {
            $("#login_show").click(function(){
                $("#modal_show").fadeIn();
            })
        });

        $(function() {
            $("#close_btn").click(function(){
                $("#modal_show").fadeOut();
            })
        });
    </script>

</body>



</html>