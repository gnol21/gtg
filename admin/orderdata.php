<?php
header("Access-Control-Allow-Origin: *");
date_default_timezone_set("Asia/Manila");
session_start();
require_once __DIR__ . "/config.php";
$db = new Connect;
function queryAct($a)
{
    $data = $GLOBALS["db"]->prepare($a);
    $data->execute();
    return $data;
}

if(isset($_GET["msg"])){
    if($_GET["msg"]=="view"){
        displayCart($_GET["orderStat"],$_GET["cartStat"],$_GET["opt"],$_GET["stat"]);
    }else if($_GET["msg"]=="done"){
        done($_GET["orderid"],$_GET["client"]);
    }
}


function displayCart($ax,$ay,$opt,$status2){

    $x=queryAct("SELECT * FROM orders WHERE `status`=$ax ORDER BY id DESC");
   
    if($x->rowCount()>=1){
        
        while($x1 = $x->fetch()){
            $a = $x1["clientid"];
            $invDir=$x1["store"];
            if($invDir==0){
                $invDirX="products";
            }else{
                $invDirX=$invDir;
            }
            if($opt==1){
                $orid = $x1['id'];
            }else{
                $orid = 0;
            }
            
            switch($x1["mop"]){
                case "COD":
                    if($x1['status']==1){
                        $status1 = "Paid";
                    }else{
                        $status1 = "To Pay";
                    }
                break;
                case "Gcash":
                    if($x1['status']==1){
                        $status1 = "Paid";
                    }else{
                        $status1 = "To Pay";
                    }
                   
                break;
                case "Wallet":
                    $status1 = "Paid";
                break;
            }
            echo '<div class="col" style="margin-bottom: 10px;">';
            echo '<div class="table-responsive border rounded " style="background: #ffffff;">';
            echo '<table class="table table-striped table-sm ">';
            echo '<thead>';
            echo '<tr>';
            echo '<th>Details</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';
            echo '<tr><td>Status:</td><td>'.$status1.'</td><td></td><td></td></tr>';
            echo '<tr><td>Store:</td><td>'.strtoupper(IdtoName($invDir)[0]).'</td><td></td><td></td></tr>';
            echo '<tr><td></td><td style="font-size:12px">'.strtoupper(IdtoName($invDir)[1]).'</td><td></td><td></td></tr>';
            echo '<tr><td>Date:</td><td>'.$x1["timestamp"].'</td><td></td><td></td></tr>';
            echo '<tr><td>Name:</td><td>'.ucwords($x1["fullname"]).'</td><td></td><td></td></tr>';
            echo '<tr><td>Addrs:</td><td>'.ucwords($x1["addrs"]).'</td><td></td><td></td></tr>';
            echo '<tr><td>Mobile:</td><td>'.$x1["mobile"].'</td><td></td><td></td></tr>';
            echo '<tr><td>Payment:</td><td>'.$x1["mop"].'</td><td></td><td></td></tr>';
            echo '</tbody>';
            echo '</table>';
            echo '<table class="table table-striped table-sm ">';
            echo '<thead>';
            echo '<tr>';
            echo '<th>Item</th>';
            echo '<th>UP</th>';
            echo '<th>Pts</th>';
            echo '<th>Qty</th>';
            echo '</tr>';
            echo '</thead>';
            if($invDir==0){
                echo '<caption class="text-center "><button class="btn btn-danger" type="button" style="width: 80%;" onClick="done('. $x1["id"].','. $x1["clientid"].')" '.$status2.'>Done</button></caption>';
            }else{
                $xpendx=queryAct("SELECT * FROM orders WHERE `status`=0 AND `pending`=1 AND id=".$x1['id']." LIMIT 1");
                    if($xpendx->rowCount()>=1){
                        $y1=queryAct("SELECT * FROM cart WHERE client=$a AND `status`=2 AND orderid=".$x1['id']);
                        if($y1->rowCount()>=1){
                            
                            while($y3 = $y1->fetch()){
                                $a3 = $y3['item'];
                                $z1=queryAct("SELECT * FROM `$invDirX` WHERE id=$a3 ");
                                if($z1->rowCount()>=1){
                                    while($z3=$z1->fetch()){
                                        echo '<tr>';
                                        echo '<td>' . ucwords($z3["item"]) . '</td>';
                                        echo '<td>' . $z3['price'] . '</td>';
                                        echo '<td>' . $z3['points'] . '</td>';
                                        echo '<td>*' . $y3['qty'] . '</td>';
                                        echo '<td></td>';
                                        echo '</tr>';
            
                                    }
            
                                }
            
                            }
                        }
                        echo '<caption class="text-center "><button class="btn btn-success" type="button" style="width: 80%;" onClick="done('. $x1["id"].','. $x1["clientid"].')" '.$status2.'>Done</button></caption>';
                    }else{
                        echo '<caption class="text-center "><button class="btn btn-dark disabled" type="button" style="width: 80%;" '.$status2.'>Waiting For Outlet</button></caption>';
                    }
            }
            echo '<tbody>';
            $y=queryAct("SELECT * FROM cart WHERE client=$a AND `status`=$ay AND orderid=$orid");
            if($y->rowCount()>=1){
                
                while($y2 = $y->fetch()){
                    $a2 = $y2['item'];
                    $z=queryAct("SELECT * FROM `$invDirX` WHERE id=$a2 ");
                    if($z->rowCount()>=1){
                        while($z2=$z->fetch()){
                            echo '<tr>';
                            echo '<td>' . ucwords($z2["item"]) . '</td>';
                            echo '<td>' . $z2['price'] . '</td>';
                            echo '<td>' . $z2['points'] . '</td>';
                            echo '<td>*' . $y2['qty'] . '</td>';
                            echo '<td></td>';
                            echo '</tr>';

                        }

                    }

                }
            }
            echo '<tr>';
            echo '<td>Total Points</td>';
            echo '<td></td>';
            echo '<td id="sumofpoints" data="' . $x1['tpoints'] . '">' . $x1['tpoints'] . '</td>';
            echo '<td></td>';
            echo '<td></td></tr>';
            echo '<tr>';
            echo '<td>Order Total</td>';
            echo '<td id="sumofprice" data="' . $x1['tprice'] . '">₱' . $x1['tprice'] . '</td>';
            echo '<td></td>';
            echo '<td></td>';
            echo '<td></td></tr>';
            echo '</tbody>';
            echo '</table>';
            echo '</div>';
            echo '</div>';

        }

        
    }


}

function done($orderid,$client){
if(queryAct("UPDATE `orders` SET `status`=1  WHERE id=$orderid AND clientid=$client")){
        if(queryAct("UPDATE `cart` SET `status`=2,orderid=$orderid WHERE client=$client AND orderid=0")){
            $x=queryAct("SELECT * FROM orders WHERE clientid=$client AND id=$orderid");
            if ($x->rowCount()>=1) {
                $x1 = $x->fetch();
                $xpoint = $x1['tpoints'];
                if (queryAct("UPDATE `earnings` SET `points`=`points`+ $xpoint WHERE client=$client")) {
                    $y=queryAct("SELECT * FROM `earnings` WHERE client=$client LIMIT 1");
                    if ($y->rowCount()>=1) {
                        $y1 = $y->fetch();
                        $ypoint = $y1['points'];
                        $ypoint_o = $y1['points_o'];
                        $tocash = floor($ypoint / 20)- $ypoint_o;
                        if (queryAct("UPDATE `earnings` SET `cash`=`cash`+ $tocash,`points_o`=`points_o`+ $tocash WHERE client=$client")) {
                            displayCart(0,1,0,"");
                            distPoints($client,$xpoint);
                        }
                    }
                 
                }


            }
        }
}
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
                 queryAct("UPDATE `earnings` SET  $posX = $posX+$points WHERE `client`=$z2");

                        distPoints($z2, $points);
                    }

            }
        }

}

function IdtoName($a){
    if($a==0){
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