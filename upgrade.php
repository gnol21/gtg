<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: X-Requested-With, Content-Type, Accept, Origin");
date_default_timezone_set("Asia/Manila");

require_once __DIR__ . "/config.php";

$db = new Connect;
//package c
$Dcash3 = 250;
$Dpoints3 = 14000;
function queryAct($a)
{
    $data =$GLOBALS['db']->prepare($a);
    $data->execute();
    return $data;  
}
function query($a)
{
    $data =$GLOBALS['db']->prepare($a);
    $data->execute();
    $row = $data->rowCount();
    return $row;
}


if (isset($_POST["register"]))
{
    switch ($_POST["register"])
    {
        case "activate":
            activate($_POST['upline'],$_POST['clientId']);
        break;

    }
}

function activate($a ,$a1 )
{
    if(query("SELECT code3 FROM earnings WHERE client=$a1 AND code3>0")){
        $x = queryAct("SELECT * , CONCAT(first_name, ' ', last_name) AS full_name FROM clients WHERE `id`=$a1 AND `status`=1 AND `deployed`=1 AND `activated`=1");

        if ($x->rowCount())
        {
            $x2 = $x->fetch(PDO::FETCH_ASSOC);
            $a2 = $x2['full_name']; //client

           if($x2['entry']==31){
                //distPoints($a1, 14000);
                send_ref($a, $a2,$a1);
            }else{
                if (query("UPDATE `clients` SET `entry` = 31 WHERE `id` = $a1") >= 1)
                {
                    send_ref($a, $a2,$a1);
                }
            } 

            query("UPDATE `earnings` SET `code3`=`code3`-1 WHERE `client`=$a1");
            echo "success";
        }
    }else{
        echo 'success';
    }
    

}
///direct referral
function send_ref($a, $a2,$a1)
{
    $enT=queryAct("SELECT * FROM clients WHERE id=$a1 LIMIT 1");
    if($enT->rowCount()>=1){

        switch($enT->fetch()['entry']){
            case 31:
                $cashIn=$GLOBALS["Dcash3"];
                $directV="directV41";
            break;
        }
    }
    $b = queryAct("SELECT * FROM `earnings` WHERE `client`=$a LIMIT 1"); //direct
    $row = $b->rowCount();
    if ($row)
    {
        query("UPDATE `earnings` SET `cash`=`cash`+$cashIn,`$directV`=`$directV`+1 WHERE `client`=$a");
    }
    else
    {
        query("INSERT INTO earnings (`client`,`$directV`,`cash`) VALUES ($a,1,$cashIn)");
    }
    distPoints($a1, 14000);
   
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

                    $z1=queryAct("SELECT * FROM clients WHERE CONCAT(first_name, ' ', last_name)='$head' LIMIT 1");
                    if($z1->rowCount()>=1){
                        $z2 = $z1->fetch()['id'];
                            
                        if ($pos == 0) {
                                queryAct("UPDATE `earnings` SET  pointsA = pointsA+$points WHERE `client`=$z2");
                            }else{
                                queryAct("UPDATE `earnings` SET  pointsB = pointsB+$points WHERE `client`=$z2");
                            }

                        distPoints($z2, $points);
                    }

            }
        }

  

}
//300623
?>