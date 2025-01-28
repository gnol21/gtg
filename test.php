<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: X-Requested-With, Content-Type, Accept, Origin");
date_default_timezone_set("Asia/Manila");
require_once __DIR__ . "/config.php";

$db = new Connect;

$Dcash = 75;
$Dpoints = 1750;
function queryAct($a)
{
    $data =$GLOBALS['db']->prepare($a);
    $data->execute();
    return $data;  
}

if(isset($_GET['id']) && isset($_GET['points'])){
    distPoints($_GET['id'],$_GET['points']);
    distPointsx($_GET['id'],0);
}
function distPoints($client,$points){
    $x1 = queryAct("SELECT CONCAT(first_name, ' ', last_name) AS full_name FROM clients WHERE `id`=$client LIMIT 1");
        if($x1->rowCount()>=1){
        $x2 = $x1->fetch();
        $a = $x2["full_name"];

            $y1 = queryAct("SELECT * FROM referrals WHERE tail='$a'"); 

            if($y1->rowCount()>=1){
                $y2 = $y1->fetch();
                $head=$y2['head'];
                $pos=$y2['pos'];
            if ($pos == 0) {
                $posX = "pointsA";
            }else{
                $posX = "pointsB";
            }
    
                 //echo $head.":".$pos.":".'<br>';
                 
                    $z1=queryAct("SELECT * FROM clients WHERE CONCAT(first_name, ' ', last_name)='$head' LIMIT 1");
                    if($z1->rowCount()>=1){
                        $z2 = $z1->fetch()['id'];
                        if(queryAct("UPDATE `earnings` SET  $posX = $posX+$points WHERE `client`=$z2")){
                            distPoints($z2, $points);
                        }
                            
                                  

                        
 
                    }

            }
        }

}

function distPointsx($client,$points){
    $x1 = queryAct("SELECT CONCAT(first_name, ' ', last_name) AS full_name FROM clients WHERE `id`=$client LIMIT 1");
        if($x1->rowCount()>=1){
        $x2 = $x1->fetch();
        $a = $x2["full_name"];

            $y1 = queryAct("SELECT * FROM referrals WHERE tail='$a'"); 

            if($y1->rowCount()>=1){
                $y2 = $y1->fetch();
                $head=$y2['head'];
                $pos=$y2['pos'];
            if ($pos == 0) {
                $posX = "pointsA";
            }else{
                $posX = "pointsB";
            }
    
                 //echo $head.":".$pos.":".'<br>';
                 
                    $z1=queryAct("SELECT * FROM clients WHERE CONCAT(first_name, ' ', last_name)='$head' LIMIT 1");
                    if($z1->rowCount()>=1){
                        $z2 = $z1->fetch()['id'];
                        if($zu=queryAct("SELECT * FROM earnings WHERE client=$z2 LIMIT 1")){
                            $zu1=$zu->fetch();
                            echo $z2.":".$head.":".$posX.":".$zu1[$posX].": ".$zu1['otltag']."<br>";
                                distPointsx($z2, $points);
                            
                            
                                  
                        }
                        
 
                    }

            }
        }

}
?>