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

    if ($_GET["type"] != ''){
        $type=$_GET["type"];

        if($type == 'last_3_months'){
            $query = 'select time_hour          as id
                             , count(event_id)  as crime_cnt
                        from hours h
                        left join crime_event c on h.time_hour = hour(c.event_time)
                        and event_date >= cast(DATE_FORMAT(date_add(current_date,interval -3 month), \'%Y-%m-01\') as date)
                        group by 1';
        } else {
            if($type == 'last_6_months'){
                $query = 'select time_hour          as id
                                 , count(event_id)  as crime_cnt
                            from hours h
                            left join crime_event c on h.time_hour = hour(c.event_time)
                            and event_date >= cast(DATE_FORMAT(date_add(current_date,interval -6 month), \'%Y-%m-01\') as date)
                            group by 1';
            } else {
                $query = 'select time_hour          as id
                                 , count(event_id)  as crime_cnt
                            from hours h
                            left join crime_event c on h.time_hour = hour(c.event_time)
                            and event_date >= cast(DATE_FORMAT(date_add(current_date,interval -12 month), \'%Y-%m-01\') as date)
                            group by 1';
            }
        }

        include('../class/mysql2json.class.php');
        $mysql2json = new mysql2json();

        $result = @mysql_query($query) or die(mysql_error());
        $num = mysql_affected_rows();
        echo $mysql2json->getJSON2($result, $num);
    }
?>
