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


    $arr_treeview = array();

    $query = 'select * from crime_category where crime_category_parent_id is null';
    $result = @mysql_query($query) or die(mysql_error());

    while ($row = mysql_fetch_array($result)) {
        $arr_child = array();
        $node['text'] = $row['crime_category_name'];
        $node['cls'] = 'folder';

        $query = 'select * from crime_category where crime_category_parent_id = '.$row['crime_category_id'];
        $result2 = mysql_query($query);
        if($result2){
            while ($row2 = mysql_fetch_array($result2)) {
                $children['text'] = $row2['crime_category_name'];
                $children['id'] = $row2['crime_category_id'].'_'.$row2['crime_category_name'];
                $children['leaf'] = true;
                $children['checked'] = true;
                array_push($arr_child, $children);
            }
            $node['children'] = $arr_child;
        }
        array_push($arr_treeview, $node);
    }
//    $data['data'] = $arr_treeview;
//    echo json_encode($data);
    echo json_encode($arr_treeview);
?>
