<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: X-Requested-With, Content-Type, Accept, Origin");
date_default_timezone_set("Asia/Manila");
session_start();
require_once __DIR__ . "/config.php";

$referralData = array();
$db = new Connect;
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

if (isset($_POST["register"])){}
else
{

    if (isset($_GET['msg']) and $_GET['msg'] != null)
    {

        $Gdat = $_GET['msg'];

        switch ($Gdat)
        {           
        case "gen_tree":
            if (isset($_GET['id']) and $_GET['id'] != null)
            {
                $a = new disp_ref();
                $a->l1($_GET['id']);
                echo json_encode($referralData);
            }
        break;
        case "listG":
            echo '<html>
            <head>
            <style>
            table, th, td {
              border: 1px solid black;
            }
            </style>
            </head>
            <body><table style="width:100%">
            <tr>
    <th>Client Name: '.$_GET['id'].'</th>
 
  </tr>
  <tr>
    <th align="left">Genealogy Group: A Position: Left</th>
 
  </tr>';
            if (isset($_GET['id']) and $_GET['id'] != null)
            {
                $a1 = new dispGroup();
                $a1->a($_GET['id'],0);
                echo ' <tr>
    <th align="left">Genealogy Group: B Position: Right</th>

  </tr>';
                $a2 = new dispGroup();
                $a2->a($_GET['id'],1);
            }
            
         echo '</table></body></html>';
        break;
        }
    }

}
class disp_ref
{
    public function l1($push)
    { 
        $this->query1("SELECT * FROM `referrals` WHERE `head`='$push' ORDER BY `pos` ASC", $push);
   
    }
    private function query1($a, $push)
    {

        $data = queryAct($a);
        $row = $data->rowCount();
        if ($row)
        {
            while ($yy = $data->fetch(PDO::FETCH_ASSOC))
            {

                switch ($yy["maxps"])
                {
                    case 0:
                        array_push($GLOBALS["referralData"], ["<div hidden='hidden'>" . strtolower($yy["tail"]) . "</div><div style='color:blue'>Add L ", "" . strtolower($yy["tail"]) ], ["<div hidden='hidden'>" . strtolower($yy["tail"]) . "</div><div style='color:red'>Add R ", "" . strtolower($yy["tail"]) ]);
                    break;

                    case 1:
                        if ($yy["pos"])
                        {
                            array_push($GLOBALS["referralData"], ["<div hidden='hidden'>" . strtolower($yy["head"]) . "</div><div style='color:blue'>Add L ", "" . strtolower($yy["head"]) ], ["" . strtolower($yy["tail"]) , "" . strtolower($yy["head"]) ]);
                        }
                        else
                        {
                            array_push($GLOBALS["referralData"], ["" . strtolower($yy["tail"]) , "" . strtolower($yy["head"]) ], ["<div hidden='hidden'>" . strtolower($yy["head"]) . "</div><div style='color:red'>Add R ", "" . strtolower($yy["head"]) ]);
                        }
                    break;
                    default:

                    break;
                }
                array_push($GLOBALS["referralData"], ["" . strtolower($yy["tail"]) , "" . strtolower($yy["head"]) ]);
                $this->l1($yy["tail"]);          
            }
        }
        else
        {
            array_push($GLOBALS["referralData"], ["<div hidden='hidden'>" . "" . strtolower($push) . "</div><div style='color:blue'>Add L ", "" . strtolower($push) . ""], ["<div hidden='hidden'>" . "" . strtolower($push) . "</div><div style='color:red'>Add R ", "" . strtolower($push) . ""]);
        }
       
    }
}
class dispGroup{
public $i=1;
    public function a($a,$b){
        
        $x = queryAct("SELECT * FROM `referrals` WHERE `head`='$a' AND `pos`=$b");
        if($x->rowCount()>=1){
            $x1 = $x->fetch()['tail'];
            $this->b($x1,$b);
        }
       
    }

    private function b($a,$b){

        echo '<tr><td>'.$this->i++.'. '.$a.'</td></tr>';
        
        $x = queryAct("SELECT * FROM `referrals` WHERE `head`='$a' ORDER BY pos DESC");
        if($x->rowCount()>=1){
            while($x1=$x->fetch()){
               $this->b($x1['tail'],$b);
            }
        }
        
    }
    

}
?>