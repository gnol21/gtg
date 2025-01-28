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


function verify(){
	$a=queryAct("SELECT * FROM clients WHERE status=0 ORDER BY id ASC");
	$b=$a->rowCount();
	$i=0;
	while($c=$a->fetch()){
		$i++;
		echo '<tr>';
		echo '<td>'.$i.'. '.strtolower($c['first_name']).' '.strtolower($c['last_name']).'</td>';
		echo '<td>'.strtolower(IdtoName($c['sponsor'])).'</td>';
		
		//echo '<td style="width: 20%;"><button class="btn btn-primary" type="button" onClick="verify('.$c['id'].')" style="width: 100%;">Verify</button></td>';
		echo '</tr>';

	}
	
	
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

if(isset($_GET['msg'])){

	switch($_GET['msg']){
		case "trans_h":
			go_trans_h();
			break;
		case "paid":
			go_paid($_GET['client'],$_GET['id']);
			break;

	}
	
}else{	
verify();
}

function go_trans_h(){
    $x = queryAct("SELECT * FROM cash_outs WHERE `status`=0 ORDER BY id DESC");
	
    if($x->rowCount()>=1){
		
        while($x1=$x->fetch()){
			$m = $x1['client'];
			$z = queryAct("SELECT CONCAT(first_name, ' ', last_name) AS full_name FROM  clients WHERE id=$m LIMIT 1");
            echo '<tr>';
			echo '<td>'.strtolower($z->fetch()['full_name']).'</td>';
			echo '<td>'.strtolower($x1['coname']).'</td>';
			echo '<td>'.$x1['comobile'].'</td>';
			echo '<td>'.$x1['payopt'].'</td>';
            echo '<td>₱'.$x1['cash_out'].'</td>';
            echo '<td>'.$x1['date'].'</td>';
			echo '<td><button type="button" onClick="paid('.$m.','.$x1['id'].')" style="width: 100%;">Paid</button></td>';
            echo '</tr>';
        }
    }
}

function go_paid($a,$b){
	queryAct("UPDATE `cash_outs` set `status`=1 WHERE client=$a AND id=$b");
}
function IdtoName($a){
        $x = queryAct("SELECT addrs,CONCAT(first_name,' ',last_name) as fullname FROM clients WHERE id='$a' ");
        if($x->rowCount()>=1){
            $x1 = $x->fetch();
            $x2=$x1["fullname"];
             return $x2;
        }
    
}

?>