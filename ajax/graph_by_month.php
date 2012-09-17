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


    if ($_GET["month_id"] > 0){
        include('../class/mysql2json.class.php');
        $mysql2json = new mysql2json();

        $query = 'select DATE_FORMAT(a.calendar_date, \'%d/%b\') as calendar_date
                         , count(b.event_id) as crime_cnt
                    from calendar a
                    left join crime_event b on a.calendar_date = b.event_date
                    where a.calendar_year = year(current_date)
                      and a.calendar_month = '.$_GET["month_id"].'
                    group by 1
                    order by 1';

        $result = @mysql_query($query) or die(mysql_error());
        $num = mysql_affected_rows();
        echo $mysql2json->getJSON2($result, $num);
    }
?>
