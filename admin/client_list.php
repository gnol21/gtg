<?php
header("Access-Control-Allow-Origin: *");
date_default_timezone_set("Asia/Manila");
session_start();
require_once __DIR__ . "/config.php";

function queryAct($a)
{
    $db = new Connect;
    $data = $db->prepare($a);
    $data->execute();
    return $data;
}

if(isset($_GET['member'])){
    find_mem($_GET['member']);
}else{
    list_mem();
}
function list_mem(){
	$x=queryAct("SELECT * FROM clients WHERE id!=1000 ORDER BY id ASC");
    $i = 0;
while($x1=$x->fetch()){
        $i++;
	echo '<tr>';
	echo '<td>'.$i .". ".strtolower($x1['first_name']).' '.strtolower($x1['last_name']).'</td>';
    echo '<td>'.$x1['pass'].'</td>';
    echo '<td>'.$x1['id'].'</td>';
    echo '<td>'.idToname($x1['sponsor']).'</td>';
    echo '<td>'.explode(" ",$x1['join_date'])[0].'</td>';
	echo '</tr>';

}
	}

function find_mem($a){
        $x=queryAct("SELECT * FROM clients WHERE id!=1000 AND CONCAT(first_name,' ',last_name) LIKE '%$a%'");
        $i = 0;
    while($x1=$x->fetch()){
            $i++;
        echo '<tr>';
        echo '<td>'.$i .". ".strtolower($x1['first_name']).' '.strtolower($x1['last_name']).'</td>';
        echo '<td>'.$x1['pass'].'</td>';
        echo '<td>'.$x1['id'].'</td>';
        echo '<td>'.idToname($x1['sponsor']).'</td>';
        echo '<td>'.explode(" ",$x1['join_date'])[0].'</td>';
        echo '</tr>';
    
    }
        }

function idToname($a){
$x=queryAct("SELECT CONCAT(first_name,' ',last_name) AS fullname FROM clients WHERE id=$a");
if($x->rowCount()>=1){
    return strtolower($x->fetch()['fullname']);
}
}




?>