<?php
    include('../include/config.php');
    // Conexao ao DB. Não mexer.
    $conexao = mysql_connect(MYSQL_HOSTNAME, MYSQL_USERNAME, MYSQL_PASSWORD) or die($msg[0]);
    mysql_select_db (MYSQL_DATABASE, $conexao) or die($msg[1]);
    mysql_query("SET NAMES 'utf8'");
    mysql_query('SET character_set_connection=utf8');
    mysql_query('SET character_set_client=utf8');
    mysql_query('SET character_set_results=utf8');

    //----------------------------------------------------------//

    $username = addslashes(trim($_POST['cad_username']));
    $password = addslashes(trim($_POST['cad_password']));
    $user_real_name = addslashes(trim($_POST['cad_real_name']));
    $email = addslashes(trim($_POST['cad_email']));

    //checa se já existe um usário com este nome
    $query = "select * from crimemap_user where user_name = '".$username."'";
    $result = @mysql_query($query);
    $num = mysql_affected_rows();
    if($num>0){
        echo json_encode(array("success"=>false, "msg"=>"ID de usuário já existe! Por favor escolha outro."));
    } else {
        $query = NULL;
        $result = NULL;
        $num = NULL;

        $query = "insert into crimemap_user (user_name
                                           , user_password
                                           , user_admin_flag
                                           , status_id
                                           , user_real_name
                                           , user_email)
                                     VALUES ('".$username."'
                                           , '".$password."'
                                           , 0
                                           , 2
                                           , '".$user_real_name."'
                                           , '".$email."')";

        $result = @mysql_query($query); // or die(mysql_error());
        $num = mysql_affected_rows();
        if($result){
            echo json_encode(array("success"=>true, "msg"=>"Usuário cadastrado com sucesso! Aguarde aprovação dos administradores para logar."));
        } else {
            echo json_encode(array("success"=>false, "msg"=>mysql_error()));
        }
    }
?>
