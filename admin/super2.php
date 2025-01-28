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




if(isset($_GET['client'])){
	
	$x=queryAct("SELECT * FROM clients WHERE id=".$_GET['client']);
	$y=$x->fetch();
	if($y['sponsor']==1000){
		
		$a=queryAct("UPDATE `clients` SET `status` = 1, `deployed`=1 WHERE `clients`.`id` = " . $_GET['client']);

	}else{
	
		$a=queryAct("UPDATE `clients` SET `status` = 1 WHERE `clients`.`id` = " . $_GET['client']);

	}
}

if(isset($_GET['member'])){
    find_mem($_GET['member']);
}else if(isset($_POST['code'])){
    $code = $_POST['code'];
    $code2 = $_POST['code2'];
    $code3 = $_POST['code3'];
    $name = $_POST['name'];
    if( queryAct("SELECT * FROM earnings WHERE client=$name LIMIT 1")->rowCount()<=0){
        queryAct("INSERT INTO earnings (client,code,code2,code3) VALUES($name,$code,$code2,$code3)");
    }else{
        queryAct("UPDATE earnings SET code=$code,code2=$code2,code3=$code3 WHERE client=$name");
    }
    

}

function find_mem($a){
        $x=queryAct("SELECT clients.first_name,clients.last_name,clients.id,clients.pass,earnings.code,earnings.code2,earnings.code3 FROM `clients` INNER JOIN earnings ON clients.id=earnings.client WHERE CONCAT(first_name,' ',last_name) LIKE '%$a%'");
        $i = 0;
    while($x1=$x->fetch()){
            $i++;
        echo '<tr onClick="sendC($(this))">';
        echo '<td>'.$i .". ".strtolower($x1['first_name']).' '.strtolower($x1['last_name']).'</td>';
        echo '<td>'.$x1['code'].'</td>';
        echo '<td>'.$x1['code2'].'</td>';
        echo '<td>'.$x1['code3'].'</td>';
        echo '<td hidden="hidden">'.$x1['pass'].'</td>';
        echo '<td hidden="hidden">'.$x1['id'].'</td>';
        echo '</tr>';
    
    }
        }


?>