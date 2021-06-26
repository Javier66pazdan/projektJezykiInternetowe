<?php
    session_start();

    $loggedIn = false;
    if (isset($_SESSION['loggedIn']) && isset($_SESSION['name'])) {
        $loggedIn = true;
    }

    $isAdmin = false;
    if(isset($_SESSION['name']) && $_SESSION['name'] == 'admin'){
        $isAdmin = true;
    }

    $conn = new mysqli('localhost',  'root', '', 'forumDyskusyjne');

    function createCommentRow($data) {
        global $conn;
        $response = '
            <div class="comment" style="background-color: #778899; margin-bottom: 20px;">
                <div class="user">'.$data['name'].' <span class="time">'.$data['createdOn'].'</span>' ;
        if(isset($_SESSION['name']) && $_SESSION['name'] == "admin")
            $response .= ' ID: ' .$data["id"];



            $response .= '</div>
                <div class="userComment">'.$data['comment'].'</div>
                <div class="reply"><a href="javascript:void(0)" data-commentID="'.$data['id'].'" onclick="reply(this)" style="text-decoration: none; color: #000">ODPOWIEDZ</a></div>

                <div class="replies">';

            $sql = $conn->query("SELECT replies.id, name, comment, DATE_FORMAT(replies.createdOn, '%Y-%m-%d') AS createdOn FROM replies INNER JOIN users ON replies.userID = users.id WHERE replies.commentID = '".$data['id']."' ORDER BY replies.id DESC LIMIT 1");
             while($dataR = $sql->fetch_assoc())
                        $response .= createCommentRow($dataR);

        $response .= '
                </div>
            </div>
        ';

        return $response;
    }

    if (isset($_POST['getAllComments'])) {
        $start = $conn->real_escape_string($_POST['start']);

        $response = "";
        $sql = $conn->query("SELECT comments.id, name, comment, DATE_FORMAT(comments.createdOn, '%Y-%m-%d') AS createdOn FROM comments INNER JOIN users ON comments.userID = users.id ORDER BY comments.id DESC LIMIT $start, 20");
        while($data = $sql->fetch_assoc())
            $response .= createCommentRow($data);

        exit($response);
    }

    // usuwanie odpowiedzi do komentarza
    if (isset($_POST["delete"])) {
        $id = $_POST['idRep'];
        $query = "DELETE FROM `replies` WHERE `replies`.`id` = $id";
        $result = mysqli_query($conn, $query);
    }

    //usuwanie komentarzy
    if (isset($_POST["deleteComm"])) {
        $id = $_POST['idComment'];
        $query = "DELETE FROM `comments` WHERE `comments`.`id` = $id";
        $result = mysqli_query($conn, $query);
    }

    if (isset($_POST['addComment'])) {
        $comment = $conn->real_escape_string($_POST['comment']);
        $isReply = $conn->real_escape_string($_POST['isReply']);
        $commentID = $conn->real_escape_string($_POST['commentID']);


        if($isReply != 'false') {
            $conn->query("INSERT INTO replies (comment, commentID, userID, createdON) VALUES ('$comment', '$commentID', '".$_SESSION['userID']."', NOW())");
            $sql = $conn->query("SELECT replies.id, name, comment, DATE_FORMAT(replies.createdOn, '%Y-%m-%d') AS createdOn FROM replies INNER JOIN users ON replies.userID = users.id ORDER BY replies.id DESC LIMIT 1");
        }
        else {
             $conn->query("INSERT INTO comments (userID, comment, createdOn) VALUES ('".$_SESSION['userID']."','$comment',NOW())");
             $sql = $conn->query("SELECT comments.id, name, comment, DATE_FORMAT(comments.createdOn, '%Y-%m-%d') AS createdOn FROM comments INNER JOIN users ON comments.userID = users.id ORDER BY comments.id DESC LIMIT 1");
        }

        $data = $sql->fetch_assoc();

        exit(createCommentRow($data));
    }

    if (isset($_POST['register'])) {
        $name= $conn->real_escape_string($_POST['name']);
        $email = $conn->real_escape_string($_POST['email']);
        $password = $conn->real_escape_string($_POST['password']);

        if ( filter_var($email,FILTER_VALIDATE_EMAIL)) {
            $sql = $conn->query("SELECT id FROM users WHERE email='$email'");
            if ($sql->num_rows > 0)
                exit('failedUserExists');
            else {
                $ePassword = password_hash($password, PASSWORD_BCRYPT);
                $conn->query("INSERT INTO users (name,email,password,createdOn) VALUES('$name', '$email', '$ePassword', NOW())");

                $sql = $conn->query("SELECT id FROM users ORDER BY id DESC LIMIT 1");
                $data = $sql->fetch_assoc();

                $_SESSION['loggedIn'] = 1;
                $_SESSION['name'] = $name;
                $_SESSION['email'] = $email;
                $_SESSION['userID'] = $data['id'];

                exit('success');
            }
        } else
            exit('failedEmail');
    }

    if (isset($_POST['updateData'])) {
        $name= $conn->real_escape_string($_POST['name']);
        $email = $conn->real_escape_string($_POST['email']);
        $password = $conn->real_escape_string($_POST['password']);

        if ( filter_var($email,FILTER_VALIDATE_EMAIL)) {
            $sql = $conn->query("SELECT id FROM users WHERE email='$email'");
            if ($sql->num_rows > 0)
                exit('failedUserExists');
            else {
                $ePassword = password_hash($password, PASSWORD_BCRYPT);
                $conn->query("UPDATE `users` SET `id`='".$_SESSION['userID']."',`name`='$name',`email`='$email',`password`='$ePassword',`createdOn`=NOW() WHERE `users`.`id`= '".$_SESSION['userID']."'");

                $sql = $conn->query("SELECT id FROM users ORDER BY id DESC LIMIT 1");
                $data = $sql->fetch_assoc();

                $_SESSION['loggedIn'] = 1;
                $_SESSION['name'] = $name;
                $_SESSION['email'] = $email;
                $_SESSION['userID'] = $data['id'];

                exit('success');
            }
        } else
            exit('failedEmail');
    }

    if (isset($_POST['logIn'])) {
        $email = $conn->real_escape_string($_POST['email']);
        $password = $conn->real_escape_string($_POST['password']);

        if ( filter_var($email,FILTER_VALIDATE_EMAIL)) {
            $sql = $conn->query("SELECT id, password, name FROM users WHERE email='$email'");
            if ($sql->num_rows == 0)
                exit('failed');
            else {
                $data = $sql->fetch_assoc();
                $passwordHash = $data['password'];

                if(password_verify($password, $passwordHash)) {
                    $_SESSION['loggedIn'] = 1;
                    $_SESSION['name'] = $data['name'];
                    $_SESSION['email'] = $email;
                    $_SESSION['userID'] = $data['id'];

                    exit('success');
                } else
                    exit('failed');
            }
        } else
            exit('failed');
    }

    $sqlNumComments = $conn->query("SELECT id FROM comments");
    $sqlNumReplies = $conn->query("SELECT id FROM replies");
    $numComments = $sqlNumComments ->num_rows;
    $numComments += $sqlNumReplies->num_rows;
?>

<!doctype html>
<html>
<head>
    <title>Forum dyskusyjne</title>
    <meta name="description"
          content="Strona, która pozwala na prowadzenie dyskusji w sekcji komentarzy" />
    <meta name="keyword"
          content="dyskusja, komentarz, opinia, rozmowa" />
    <meta http-equiv="authors"
          content="Gabriel Pazdan, " />
    <!--INCLUDING BOOTSTRAP-->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous">
    <!-- INCLUDING CSS STYLESHEET -->
    <link href="css/main.css"
        type="text/css" rel="stylesheet" />
    <!-- INCLUDING SCRIPTS -->
    <script src="js/showLogin.js"></script>
    <script src="js/showRegister.js"></script>
    <script src="js/closeLogAndReg.js"></script>
</head>
<body>
    <div id="container">
        <!-- górny pasek -->
        <header>
            <nav class="navbar navbar-light" style="background-color: #313146;">
                <div class="container-fluid">
                    <a class="navbar-brand" style="color: white;">Forum dyskusyjne</a>
                    <!-- jeśli jesteśmy zalogowani nie pokazuje przycisków -->
                    <span class="d-flex">
                    <?php
                        if (!$loggedIn)
                            echo '
                             <button class="btn btn-outline-success" type="submit" onclick="showLoginPanel()">Logowanie</button>
                             <button class="btn btn-outline-success" type="submit" onclick="showReg()">Zarejestruj</button>
                        ';
                      else
                        echo '
                            <a href="javascript:void(0)" id="showModal" class="btn btn-info mx-3">Zmień dane</a>
                            <a href="logout.php" class="btn btn-warning">Wyloguj się</a>
                        ';
                    ?>
                    </span>
                </div>
            </nav>
        </header>
        <!-- UKRYTY PANEL LOGOWANIA -->
        <div id="log">
            <nav class="navbar navbar-light" style="background-color: #4b4b66">
                <div class="container-fluid">
                    <a class="navbar-brand" style="color: white">Zaloguj się</a>
                    <form class="row g-4">
                        <div class="col-auto">

                            <input type="email" class="form-control" id="userLEmail" placeholder="email@domena.com">
                        </div>
                        <div class="col-auto">

                            <input type="password" class="form-control" id="userLPassword" placeholder="Hasło">
                        </div>
                        <div class="col-auto">
                            <button id="loginBtn" type="submit" class="btn btn-primary mb-3">Zaloguj się</button>
                        </div>
                        <div class="col-auto">
                            <button type="button" class="btn-close" aria-label="Close" onclick="closePanel()"></button>
                        </div>
                    </form>
                </div>
            </nav>
        </div>
        <!-- UKRTYY PANEL REJESTRACJI -->
        <div id="reg">
            <nav class="navbar navbar-light" style="background-color: #4b4b66">
                <div class="container-fluid">
                    <a class="navbar-brand" style="color: white">Zarejestruj się</a>
                    <form action="indexx.php" class="row g-7">
                        <div class="col-auto">
                            <div class="input-group">
                                <input type="text" class="form-control" id="username"  aria-describedby="inputGroupPrepend2" placeholder="Nazwa użytkownika" required>
                            </div>
                        </div>
                        <div class="col-auto">
                            <input type="email" class="form-control" id="userEmail" placeholder="email@domena.com">
                        </div>
                        <div class="col-auto">
                            <input type="password" class="form-control" id="userPassword" placeholder="Hasło" minlength="8">
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary mb-3" id="registerBtn">Zarejestruj się</button>
                        </div>
                        <div class="col-auto">
                            <button type="button" class="btn-close" aria-label="Close" onclick="closePanel()"></button>
                        </div>
                    </form>
                </div>
            </nav>
        </div>

        <!-- MODAL html -->
        <div id="myModal" class="modal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Zmień dane użytkownika</h5>
                        <button type="button" class="close hideModal" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form action="indexx.php" method="POST">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="floatingUserInput" placeholder="Nazwa użytkownika">
                                <label for="floatingUserInput">Nazwa użytkownika</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="email" class="form-control" id="floatingEmailInput" placeholder="name@example.com">
                                <label for="floatingEmailInput">Adres Email</label>
                            </div>
                            <div class="form-floating">
                                <input type="password" class="form-control" id="floatingPassword" placeholder="Haslo" minlength="8">
                                <label for="floatingPassword">Haslo</label>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button id="saveChangedData" type="button" class="btn btn-primary">Zapisz</button>
                        <button type="button" class="btn btn-secondary hideModal" data-dismiss="modal">Zamknij</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Główna częśc strony -->
        <section>
            <div class="row justify-content-evenly">
                <!-- DODAWANIE NOWYCH KOMENTARZY -->
                <div class="col-md-5 p-3 mb-2 bg-secondary text-white" style="max-height: 350px;">

                    <textarea class="form-control" id="mainComment" placeholder="Dodaj nowy komentarz" cols="30" rows="10"></textarea><br>
                    <button class="btn-primary btn" style="float: right" onclick="isReply=false;" id="addComment">Dodaj komentarz</button>

                </div>
                <!-- SEKCJA DODANYCH KOMENTARZY -->
                <div class="col-md-5 p-3 mb-2 bg-secondary text-white">
                    <h2><b id="numComments"><?php echo $numComments ?> Komentarzy</b></h2>
                    <form id="adminRow" action="indexx.php" method="POST">
                        <?php
                            if($isAdmin) echo '
                            <div class="row">
                                <div class="col-md-6 col-6">
                                    <input class="form-control" type="text" name="idRep" placeholder="ID odpowiedzi"/>
                                    <div><button id="delete" name="delete">USUN</button></div>
                                </div>
                                <div class="col-md-6 col-6">
                                    <input class="form-control" type="text" name="idComment" placeholder="ID Komentarza"/>
                                    <div><button id="deleteComm" name="deleteComm">USUN</button></div>
                                </div>
                            </div>';
                             ?>
                    </form>
                        <div class="userComments">

                    </div>
                </div>
            </div>
            <!-- ODPOWIEDZI -->
                <div class="replyRow" style="max-height: 100px; display:none">

                        <textarea class="form-control" id="replyComment" placeholder="Dodaj nowy komentarz" cols="30" rows="1"></textarea><br>
                        <button class="btn-primary btn" onclick="isReply=true;" style="float: right" id="addReply">Dodaj komentarz</button>
                        <button type="button" class="btn-close" aria-label="Close" onclick="$('.replyRow').hide();"></button>
                </div>
        </section>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4="  crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js" integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.min.js" integrity="sha384-Atwg2Pkwv9vp0ygtn1JAojH0nYbwNJLPhwyoVbhoPwBhjQPR5VtM2+xf0Uwh9KtT" crossorigin="anonymous"></script>
    <script type="text/javascript">
        var isReply = false;
        var commentID = 0;
        var max = <?php echo $numComments ?>;

        $(document).ready(function(){
            $("#showModal").click(function(){
                $("#myModal").modal('show');
            });

            $(".hideModal").click(function(){
                $("#myModal").modal('hide');
            });
        });

        $(document).ready(function () {
            $("#addComment, #addReply").on('click', function () {
                var comment;
                if (!isReply)
                 comment = $("#mainComment").val();
                else
                    comment = $("#replyComment").val();

                if ( comment.length > 5 ){
                    $.ajax({
                       url: 'indexx.php',
                       method: 'POST',
                        dataType: 'text',
                        data: {
                            addComment: 1,
                            comment: comment,
                            isReply: isReply,
                            commentID: commentID
                        }, success: function (response) {
                            max++;
                            $("#numComments").text(max + " Komentarzy");

                           if(!isReply) {
                               $(".userComments").prepend(response);
                               $("#mainComment").val("");
                           } else {
                                commentID = 0;
                                $("#replyComment").val("");
                                $(".replyRow").hide();
                                $('.replyRow').parent().next().append(response);
                           }
                        }
                    });
                } else {
                    alert("Proszę wprowadzić dane w pola");
                }
            });

            $("#registerBtn").on('click', function () {
                var name = $("#username").val();
                var email = $("#userEmail").val();
                var password = $("#userPassword").val();

                if ( name != "" && email != "" && password !=""){
                    $.ajax({
                       url: 'indexx.php',
                       method: 'POST',
                        dataType: 'text',
                        data: {
                           register: 1,
                            name: name,
                            email: email,
                            password: password
                        }, success: function (response) {
                            if (response === 'failedEmail')
                                alert('Proszę wprowadzić prawidłowy adres email');
                            else if ( response === 'failedUserExists')
                                alert('Użytkownik z tym emailem już istnieje');
                            else
                                window.location = window.location;
                        }
                    });
                } else {
                    alert("Proszę wprowadzić dane w pola");
                }
            });

            $("#saveChangedData").on('click', function () {
                var name = $("#floatingUserInput").val();
                var email = $("#floatingEmailInput").val();
                var password = $("#floatingPassword").val();

                if ( name != "" && email != "" && password !=""){
                    $.ajax({
                        url: 'indexx.php',
                        method: 'POST',
                        dataType: 'text',
                        data: {
                            updateData: 1,
                            name: name,
                            email: email,
                            password: password
                        }, success: function (response) {
                            if (response === 'failedEmail')
                                alert('Proszę wprowadzić prawidłowy adres email');
                            else if ( response === 'failedUserExists')
                                alert('Użytkownik z tym emailem już istnieje');
                            else
                                window.location = window.location;
                        }
                    });
                } else {
                    alert("Proszę wprowadzić dane w pola");
                }
            });

            $("#loginBtn").on('click', function () {
                var email = $("#userLEmail").val();
                var password = $("#userLPassword").val();

                if ( email != "" && password !=""){
                    $.ajax({
                       url: 'indexx.php',
                       method: 'POST',
                        dataType: 'text',
                        data: {
                            logIn: 1,
                            email: email,
                            password: password
                        }, success: function (response) {
                            if (response === 'failed')
                                alert('Wprowadziłeś nieprawidlowe dane');
                            else
                                window.location = window.location;
                        }
                    });
                } else {
                    alert("Proszę wprowadzić dane w pola");
                }
            });
            getAllComments(0, max);
        });

        function reply(caller) {
            commentID = $(caller).attr('data-commentID');
            $(".replyRow").insertAfter($(caller));
            $('.replyRow').show();

        }

        function getAllComments(start, max) {
            if (start > max) {
                return;
            }
                $.ajax({
                       url: 'indexx.php',
                       method: 'POST',
                        dataType: 'text',
                        data: {
                            getAllComments: 1,
                            start: start,
                        }, success: function (response) {
                          $(".userComments").append(response);
                          getAllComments((start+20), max);
                        }
                    });
        }
    </script>
</body>
</html>
