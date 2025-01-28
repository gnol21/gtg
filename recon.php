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

if(isset($_GET['id'])){
    $idx=$_GET['id'];
          $a = queryAct("SELECT * FROM `cash_outs` WHERE client=$idx AND `status`=0");
          
          if($a->rowCount()>=1){
              $a1 = $a->fetch();
              $cash_o=$a1['cash_out'];
              
                $b = queryAct("UPDATE `earnings` SET `cash_o` =cash_o-$cash_o WHERE `earnings`.`client` =$idx");
                
                    if($b){
                           $c = queryAct("DELETE FROM `cash_outs` WHERE `client`=$idx AND `status`=0");
                        
                        echo  $cash_o." returned to : " . $a1['coname']." Wallet";
                    }
                
              
          }else{
              echo "Not Found!";
          }
          


}