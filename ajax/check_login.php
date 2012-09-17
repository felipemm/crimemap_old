<?php
    include('../include/config.php');

    $conexao = mysql_connect(MYSQL_HOSTNAME, MYSQL_USERNAME, MYSQL_PASSWORD) or die($msg[0]);
    mysql_select_db (MYSQL_DATABASE, $conexao);
    mysql_query("SET NAMES 'utf8'");
    mysql_query('SET character_set_connection=utf8');
    mysql_query('SET character_set_client=utf8');
    mysql_query('SET character_set_results=utf8');

    $username = !empty($_POST['username']) ? addslashes(trim($_POST['username'])) : '';
    $password = !empty($_POST['password']) ? addslashes($_POST['password']) : '';


    if($username != ''){
        $query = "SELECT * FROM " . USERS_TABLE_NAME . "
                   WHERE user_name ='$username'
                   AND user_password = '$password'";
        $result = mysql_query($query,$conexao);
        $row = mysql_fetch_array($result);
        if($row){
            if ($row['status_id'] == '1'){
                session_start();
                $_SESSION['is_successful_login'] = true;
                $_SESSION['user_name'] = $row['user_name'];
                $_SESSION['user_id'] = $row['user_id'];
                $_SESSION['admin_flag'] = $row['user_admin_flag'];
                echo json_encode(array('success'=>true,'usuario'=>$row['user_real_name'],"msg"=>"Seja bem vindo ao CrimeMap, ".$row['user_real_name']));
            } else {
                echo json_encode(array('success'=>false,'usuario'=>'',"msg"=>"Usuário ainda não foi validado por um administrador. Por favor aguarde."));
            }
        } else {
            //echo json_encode(array('success'=>false,'usuario'=>'',"msg"=>"Usuário inexistente"));
            echo json_encode(array('success'=>false,'usuario'=>'',"msg"=>$query));
        }
    }
?>