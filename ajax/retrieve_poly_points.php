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

    include('../class/mysql2json.class.php');
    $mysql2json = new mysql2json();

    if ($_POST["polygonDef"] != "") {
//        $query = 'select event_id
//                  from crime_event
//                  where intersects(event_map_localization, GeomFromText(\''.$_POST["polygonDef"].'\'))';
//        $result = mysql_query($query,$conexao);
//        $num = mysql_affected_rows();
//        echo $mysql2json->getData($result);

//        $data = "<table>";
//        while ($row = mysql_fetch_array($result)) {
//            $data .= "<tr><td>".$row[0]."</td></tr>";
//        }
//        $data .= "</table>";
//        echo $data;

        $query = 'select date_format(cal.calendar_date, \'%b\') as month_name
                     , sum(case cc.crime_category_id when 6 then 1 else 0 end) as roubo_movel
                     , sum(case cc.crime_category_id when 7 then 1 else 0 end) as roubo_imovel
                     , sum(case cc.crime_category_id when 8 then 1 else 0 end) as roubo_obj_pessoal
                     , sum(case cc.crime_category_id when 9 then 1 else 0 end) as roubo_comercio
                     , sum(case cc.crime_category_id when 10 then 1 else 0 end) as roubo_automovel
                     , sum(case cc.crime_category_id when 11 then 1 else 0 end) as roubo_outros
                     , sum(case cc.crime_category_id when 12 then 1 else 0 end) as furto_movel
                     , sum(case cc.crime_category_id when 13 then 1 else 0 end) as furto_imovel
                     , sum(case cc.crime_category_id when 14 then 1 else 0 end) as furto_obj_pessoal
                     , sum(case cc.crime_category_id when 15 then 1 else 0 end) as furto_comercio
                     , sum(case cc.crime_category_id when 16 then 1 else 0 end) as furto_automovel
                     , sum(case cc.crime_category_id when 17 then 1 else 0 end) as furto_outros
                     , sum(case cc.crime_category_id when 18 then 1 else 0 end) as homicidio_briga
                     , sum(case cc.crime_category_id when 19 then 1 else 0 end) as homicidio_assassinato
                     , sum(case cc.crime_category_id when 20 then 1 else 0 end) as homicidio_latrocinio
                     , sum(case cc.crime_category_id when 21 then 1 else 0 end) as homicidio_outros
                     , sum(case cc.crime_category_id when 22 then 1 else 0 end) as suspeito_boca_fumo
                     , sum(case cc.crime_category_id when 23 then 1 else 0 end) as suspeito_armado
                     , sum(case cc.crime_category_id when 24 then 1 else 0 end) as suspeito_desmanche
                     , sum(case cc.crime_category_id when 25 then 1 else 0 end) as suspeito_domestica
                     , sum(case cc.crime_category_id when 26 then 1 else 0 end) as suspeito_outros
                     , count(ce.event_id) as total
                from calendar cal
                left join (select * from crime_event
                           where intersects(event_map_localization, 
                                            GeomFromText(\''.$_POST["polygonDef"].'\'))) ce
                                                 on cal.calendar_date = ce.event_date
                left join crime_category cc on cc.crime_category_id = ce.crime_category_id
                where year(cal.calendar_date) = year(current_date)
                group by 1
                order by cal.calendar_month';
        $result = mysql_query($query,$conexao);
        $num = mysql_affected_rows();
        echo $mysql2json->getJSON2($result, $num);
    } else {
        echo "ERRO!";
    }
?>
