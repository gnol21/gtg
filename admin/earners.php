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

function list_mem(){
	$x=queryAct("SELECT clients.first_name,clients.last_name,earnings.pairV2,earnings.cash, earnings.direct,directV2,earnings.cash_o
    FROM earnings
    INNER JOIN clients ON earnings.client=clients.id WHERE earnings.cash<>0 ORDER BY earnings.cash DESC;");
    $i = 0;
while($x1=$x->fetch()){
        $i++;
	echo '<tr>';
	echo '<td>'.$i .". ".strtolower($x1['first_name']).' '.strtolower($x1['last_name']).'</td>';
    echo '<td>'.number_format($x1['pairV2']).'</td>';
    echo '<td>'.number_format($x1['direct']+$x1['directV2']).'</td>';
    echo '<td>'.number_format($x1['cash']-$x1['cash_o']).'</td>';
    echo '<td>'.number_format($x1['cash']).'</td>';
	echo '</tr>';

}
	}

list_mem();
?>