<?php
    session_start();
    include('../include/config.php');
    // Conexao ao DB. Não mexer.
    $conexao = mysql_connect(MYSQL_HOSTNAME, MYSQL_USERNAME, MYSQL_PASSWORD) or die($msg[0]);
    mysql_select_db (MYSQL_DATABASE, $conexao) or die($msg[1]);
    mysql_query("SET NAMES 'utf8'");
    mysql_query('SET character_set_connection=utf8');
    mysql_query('SET character_set_client=utf8');
    mysql_query('SET character_set_results=utf8');

    //variáveis de sessão
    $user_name = $_SESSION['user_name'];
    $user_id = $_SESSION['user_id'];
    $admin_flag = $_SESSION['admin_flag'];

    //----------------------------------------------------------//
    $query = "insert into crime_event (crime_category_id
                                     , event_map_localization
                                     , event_date
                                     , event_time
                                     , district_id
                                     , event_txt
                                     , status_id
                                     , user_id)
                               VALUES (".$_POST['formSubCategoriaId']."
                                     , geomfromtext('POINT(" . $_POST['pointLat'] . " " . $_POST['pointLng'] . ")')
                                     , str_to_date('" . $_POST['formDataEvento'] . "','%m/%d/%Y')
                                     , '".$_POST['formHoraEvento'].":".$_POST['formMinutoEvento']."'
                                     , ".$_POST['formBairroId']."
                                     , '".$_POST['formDescricao']."'
                                     , 2
                                     , ".$user_id.")";

    $result = @mysql_query($query); // or die(mysql_error());
    $num = mysql_affected_rows();
    if($result){
        echo json_encode(array("success"=>true, "msg"=>"Incidente cadastrado com sucesso!"));
    } else {
        echo json_encode(array("success"=>false, "msg"=>mysql_error()));
    }
?>
