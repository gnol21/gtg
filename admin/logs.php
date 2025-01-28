<?php
header("Access-Control-Allow-Origin: *");
date_default_timezone_set("Asia/Manila");
session_start();
require_once __DIR__ . "/config.php";
$db = new Connect;
function queryAct($a)
{
    $data =$GLOBALS['db']->prepare($a);
    $data->execute();
    return $data;  
}

if(isset($_GET['msg'])){

	switch($_GET['msg']){
		case "trans_h":
			go_trans_h();
			break;
	}
	
}
function go_trans_h(){
    $x = queryAct("SELECT * FROM cash_outs WHERE `status`=1 ORDER BY id DESC");
    $y = queryAct("SELECT SUM(cash_out) AS ctotal FROM cash_outs WHERE `status`=1");
	
    if($x->rowCount()>=1){
        $i = 0;
        while($x1=$x->fetch()){
            $i++;
            echo '<tr>';
			echo '<td>'.$i.". ".strtolower($x1['coname'])."/".IdtoName($x1['client'])[0].'</td>';
            echo '<td>₱'.number_format($x1['cash_out']).'</td>';
            echo '<td>'.$x1['date'].'</td>';
			echo '<td>'.$x1['payopt'].'</td>'; 
            echo '<td>'.$x1['comobile'].'</td>';          
            echo '</tr>';
        }
        echo '<tr>';
		echo '<td>Total Cash-Out</td>';
        echo '<td>₱'.number_format($y->fetch()['ctotal']).'</td>';
        echo '<td></td>';
		echo '<td></td>';
		echo '<td></td>';
        echo '</tr>';
    }
}

function IdtoName($a){
    if($a=='products'){
        $x2=["givethanksgrocers","admin"];
        return $x2;

    }else{
        $x = queryAct("SELECT addrs,CONCAT(first_name,' ',last_name) as fullname FROM clients WHERE id='$a' ");
        if($x->rowCount()>=1){
            $x1 = $x->fetch();
            $x2=[$x1["fullname"],$x1["addrs"]];
             return $x2;
        }
    }

}
?>