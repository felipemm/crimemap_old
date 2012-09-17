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

    $query = 'select district_id    as id
                   , district_name  as displayText
              from district
              where city_id = 1';

    include('../class/mysql2json.class.php');
    $mysql2json = new mysql2json();

    $result = @mysql_query($query) or die(mysql_error());
    $num = mysql_affected_rows();
    echo $mysql2json->getJSON2($result, $num);
?>
