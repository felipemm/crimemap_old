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

//    $query_list[1] = 'select DATE_FORMAT(a.calendar_date, \'%d/%b\') as calendar_date
//                             , count(b.event_id) as crime_cnt
//                        from calendar a
//                        left join crime_event b on a.calendar_date = b.event_date
//                        where a.calendar_month = month(current_date)
//                          and a.calendar_year = year(current_date)
//                        group by 1
//                        order by 1';
    $query_list[1] = 'select a.calendar_month
                             , a.month_name
                        from calendar a
                        where a.calendar_year = year(current_date)
                        group by 1,2
                        order by 1,2';
    $query_list[2] = 'select DATE_FORMAT(a.calendar_date, \'%d/%b\') as calendar_date
                             , count(b.event_id) as crime_cnt
                        from calendar a
                        left join crime_event b on a.calendar_date = b.event_date
                        where a.calendar_month = month(current_date)-1
                          and a.calendar_year = year(current_date)
                        group by 1
                        order by 1';
    $query_list[3] = 'select concat(a.month_name, \'/\', cast(a.calendar_year as char(4))) as calendar_date
                             , count(b.event_id) as crime_cnt
                        from calendar a
                        left join crime_event b on a.calendar_date = b.event_date
                        where a.calendar_year = year(current_date)
                        group by 1
                        order by a.calendaR_month';
    $query_list[4] = 'select district_name
                             , count(event_id) as crime_cnt
                        from district d
                        left join crime_event c on c.district_id = d.district_id
                        group by 1
                        order by 1';
    $query_list[5] = 'select crime_category_name as category_name
                             , sum(case when month(event_date) = 1 then 1 else 0 end) as count_jan
                             , sum(case when month(event_date) = 2 then 1 else 0 end) as count_fev
                             , sum(case when month(event_date) = 3 then 1 else 0 end) as count_mar
                             , sum(case when month(event_date) = 4 then 1 else 0 end) as count_abr
                             , sum(case when month(event_date) = 5 then 1 else 0 end) as count_mai
                             , sum(case when month(event_date) = 6 then 1 else 0 end) as count_jun
                             , sum(case when month(event_date) = 7 then 1 else 0 end) as count_jul
                             , sum(case when month(event_date) = 8 then 1 else 0 end) as count_aug
                             , sum(case when month(event_date) = 9 then 1 else 0 end) as count_set
                             , sum(case when month(event_date) = 10 then 1 else 0 end) as count_out
                             , sum(case when month(event_date) = 11 then 1 else 0 end) as count_nov
                             , sum(case when month(event_date) = 12 then 1 else 0 end) as count_dez
                        from crime_category c
                        left join crime_event e on c.crime_category_id = e.crime_category_id
                        group by 1';
    $query_list[6] = 'select a.calendar_month
                             , a.month_name
                        from calendar a
                        where a.calendar_year = year(current_date)
                        group by 1,2
                        order by 1,2';

    if ($_GET["id"] > 0){
        include('../class/mysql2json.class.php');
        $mysql2json = new mysql2json();

        $query = $query_list[$_GET['id']];
        $result = @mysql_query($query) or die(mysql_error());
        $num = mysql_affected_rows();
        echo $mysql2json->getJSON2($result, $num);
    }
?>
