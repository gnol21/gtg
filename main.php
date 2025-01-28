<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: X-Requested-With, Content-Type, Accept, Origin");
date_default_timezone_set("Asia/Manila");
session_start();
require_once __DIR__ . "/config.php";

$referralData = array();
$down_info = array();
$intlvl = 0;
$intpos=array();
$db = new Connect;
$Dcash = 75;
$Dpoints = 1750;
$Dcash2 = 25;
$Dcash3 = 150;//pc
$Dcash31= 250;//sub ver3
$Dcash4 = 150;
$Dpoints2 = 100;
$Dpoints3 = 14000;
$Dpoints4 = 7000;
$totalPpA=0;
$totalPpB=0;
$limitCH=0;

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
        case "register":
            go_register();
        break;

        case "login":
            go_login();
        break;

        case "logout":
            session_start();
            session_unset();
            session_destroy();
            clearstatcache();
        break;

        case "set_dwn":
            go_setdwn();
        break;

        case "send_codes":
            go_send_codes();
        break;
        case "cash_o":
            if (isset($_POST['amount'])) {
                $a = $_POST['client'];
                $b = $_POST['amount'];
                $c = $_POST['coname'];
                $d = $_POST['comobile'];
                $e = $_POST['payopt'];
                if($b<=0){
                    echo "Invalid Amount";
                }else{
                    go_cash_o($a,$b,$c,$d,$e);
                }
               
            }
        break;

        case "products":
            go_products();
        break;
        case "addtocart":
            addtocart($_POST['clientId'],$_POST['item_tag'],$_POST['item_order'],$_POST['msg'],$_POST['dirF']);
        break;
        case "CartHistory":
            CartHistory(1,2,1,'Hidden',$_POST['clientId']);
        break;
        case "orderConfirm":
            orderConfirm(
                $_POST['orderCname'],
                $_POST['orderCaddrs'],
                $_POST['orderCmobile'],
                $_POST['orderCpayment'],
                $_POST['sumofprice'],
                $_POST['sumofpoints'],
                $_POST['clientId'],
                $_POST['dirF']
            );
        break;

        case "activate":
            activate($_POST['upline'],$_POST['clientId']);
        break;

    }
}
else
{

    if (isset($_GET['msg']) and $_GET['msg'] != null)
    {

        $Gdat = $_GET['msg'];

        switch ($Gdat)
        {

            case "dlines":
                if (isset($_GET['id']) and $_GET['id'] != null)
                {
                    dlines($_GET['id'],$_GET['status']);
                }
            break;

            case "gen_tree":
                if (isset($_GET['id']) and $_GET['id'] != null)
                {
                    $a = new disp_ref();
                    $a->l1($_GET['id']);
                    echo json_encode($referralData);
                }
            break;

            case "down_info":
                if (isset($_GET['id']) and $_GET['id'] != null)
                {
                    $a = new down_info();
                    $a->search($_GET['id']);
                    echo json_encode($down_info);
                }
            break;
            
            case "dispGroup":
                if (isset($_GET['id']) and $_GET['id'] != null)
                {
                    $a1 = new dispGroup();
                    $a1->a($_GET['id'],$_GET['pos']);
                }
            break;

            case "products_codes":
                products_codes($_GET['clientId']);
            break;

            case "trans_h":
                $a=$_GET['id'];
                go_trans_h($a);
            break;

            case "products_view":
                $clientx=$_GET['clientId'];
                    if($_GET['dirF']<>"undefined"){
                       
                            if(query("SELECT * FROM cart WHERE client=$clientx AND `status`=0")){
                                $invDir=$_GET['dirF'];
                            }else{$invDir=1;}
                          
                    }else{
                        $x=queryAct("SELECT * FROM orders WHERE clientid=$clientx AND `status`=0");
                        if($x->rowCount()>=1){
                            $x1=$x->fetch();
                            if($x1['store']==0){
                                $invDir='products';
                            }else{
                                $invDir=$x1['store'];
                            }
                        }else{$invDir=1;}
                       
                    }
                    products_view($clientx,$invDir);
            break;

            case "categ_items":
                categ_items($_GET['clientId'],$_GET['category'],$_GET['dirF']);
            break;

            case "openStoreItems":
                openStoreItems($_GET['dirF']);
            break;

            case "go_delete":
                go_delete($_GET['orderId']);
            break;
            case "cancelOrder":
                cancelOrder($_GET['client']);
            break;

            case "score":
                score($_GET['clientId']);
            break;
            case "purcHist":
               // purcHist($_GET['clientId'],$_GET['pos']);
            break;
            case "packPoints":
                packPoints($_GET['clientId'],$_GET['pos']);
            break;
            case "otHist":
                otHist($_GET['clientId'],$_GET['pos']);
            break;

        }

    }

}

function go_register()
{
    $password = rand(00000, 99999);
    $first_name = $_POST["first_name"];
    $last_name = $_POST["last_name"];
    $addrs = $_POST["addrs"];
    $sponsor = $_POST["sponsor"];
    $email = $_POST["email"];
    $mobile = $_POST["mobile"];

    if ($first_name and $last_name and $addrs and $sponsor and $email and $password and $mobile != null)
    {
        if (filter_reg('id', $sponsor, 1))
        {
            if (!filter_reg('email', $email, 0))
            {
                if (!filter_reg('mobile', $mobile, 1))
                {
                    if (query("SELECT * FROM `clients` WHERE `first_name`='$first_name' AND `last_name`= '$last_name'") == 0)
                    {

                        //
                
                        $data = queryAct("INSERT INTO clients ( first_name, last_name,addrs,sponsor, email, pass, mobile) VALUES ('$first_name', '$last_name','$addrs','$sponsor','$email','$password','$mobile')");
                        $row = $data->rowCount();
                        if ($row)
                        {
                            $data = queryAct("SELECT id FROM `clients` ORDER BY id DESC LIMIT 1;");
                            $data2 = $data->fetch(PDO::FETCH_ASSOC);
                            $arrayD = ["msg" => "success", "u_pass" => $password, "u_id" => $first_name, "u_code" => $data2['id'], ];

                            echo json_encode($arrayD);
                        }
                        //
                        
                    }
                    else
                    {
                        echo '{"msg":"Account already registered"}';
                    }
                }
                else
                {
                    echo '{"msg":"Mobile already in use"}';
                }
            }
            else
            {
                echo '{"msg":"Email already in use"}';
            }
        }
        else
        {
            echo '{"msg":"Invalid Sponsor"}';
        }

    }
    else
    {
        echo '{"msg":"NoData"}';
    }
}

function go_login()
{
    $first_name = $_POST["u_id"];
    $u_pass = $_POST["u_pass"];

    if ($first_name and $u_pass != null)
    {
        $data = queryAct("SELECT * FROM clients WHERE first_name='$first_name' AND pass='$u_pass' LIMIT 1");
        $row = $data->rowCount();
        $fetch = $data->fetch(PDO::FETCH_ASSOC);

        if ($row > 0)
        {
            $fetchId=$fetch['id'];
            if(query("SELECT * FROM earnings WHERE client=$fetchId LIMIT 1")<=0){
                query("INSERT INTO earnings (`client`,`direct`,`cash`) VALUES ($fetchId,0,0)");
            }
           

            $jd = explode(" ", $fetch["join_date"]);
            $arrayD = ["msg" => "accepted", "id" => $fetch["id"], "first_name" => $fetch["first_name"], "last_name" => $fetch["last_name"], "addrs" => $fetch["addrs"], "email" => $fetch["email"], "mobile" => $fetch["mobile"], "sponsor" => $fetch["sponsor"], "pass" => $fetch["pass"], "join_date" => $jd[0]." Package:".getpackage($fetch["id"]), "activated" => $fetch["activated"],"store"=>$fetch["outlet"]];

            echo json_encode($arrayD);

            $_SESSION["id"] = $fetch["id"];
      

        }
        else
        {
            echo '{"msg":"user_not_found"}';
        }
    }
    else
    {
        echo '{"msg":"blank_login"}';
    }
}

function filter_reg($a, $b, $c)
{

    if ($c)
    {
        $comn = "SELECT * FROM clients WHERE $a=$b";
    }
    else
    {
        $comn = "SELECT * FROM `clients` WHERE `$a`='$b'";
    }

    $data=queryAct($comn);
    $row = $data->rowCount();
    
    if ($row>=3)
    {
        return true;
    }
    if ($row == 1 && $a =="id") {
        return true;
    }
}

function dlines($a,$status)
{
    if ($status >= 1) {
        $data = queryAct("SELECT * FROM `clients` WHERE `sponsor`='$a' AND `status`=$status AND `deployed`=0"); // AND `status`=0 AND `deployed`=0"
        while ($outData = $data->fetch(PDO::FETCH_ASSOC)) {

            echo "<tr class='dltr' onClick='selRef(\"" . $outData['first_name'] . " " . $outData['last_name'] . "\"," . $outData['id'] . ")'><td>" . $outData['id'] . "</td><td>" . $outData['first_name'] . " " . $outData['last_name'] . "</td></tr>";
        }
    }else{
        $data = queryAct("SELECT * FROM clients WHERE CONCAT(first_name,' ',last_name) LIKE '%$a%'");
        while ($outData = $data->fetch(PDO::FETCH_ASSOC))
        {
            echo "<tr class='dltr' onClick='selRef(\"" . $outData['first_name'] . " " . $outData['last_name'] . "\"," . $outData['id'] . ")'><td>" . $outData['id'] . "</td><td>" . $outData['first_name'] . " " . $outData['last_name'] . "</td></tr>";
        }
      
    }

}

function dlinks($a4){
    

    $x = queryAct("SELECT * FROM referrals WHERE head='$a4'");
    if($x->rowCount()>=1){
        while($x2=$x->fetch(PDO::FETCH_ASSOC)){
            echo "<tr class='dltr' onClick='selRef(\"" . $x2['tail'] . "\"," . $x2['downline'] . ")'><td>" . $x2['downline'] . "</td><td>" .strtolower($x2['tail']). "</td></tr>";
            dlinks($x2['tail']);
        }
    }

}
class disp_ref
{
    private $i=0;
    public function l1($push)
    {
        $this->query1("SELECT * FROM `referrals` WHERE `head`='$push' ORDER BY `pos` ASC", $push);

        $x1 = queryAct("SELECT * FROM `referrals` WHERE `head`='$push' AND `pos`=0");
        if($x1->rowCount()>=1){
            $x2 = $x1->fetch()['tail'];
            $this->query1("SELECT * FROM `referrals` WHERE `head`='$x2' ORDER BY `pos` ASC", $x2);
        }
        $x12 = queryAct("SELECT * FROM `referrals` WHERE `head`='$push' AND `pos`=1");
        if($x12->rowCount()>=1){
            $x22 = $x12->fetch()['tail'];
            $this->query1("SELECT * FROM `referrals` WHERE `head`='$x22' ORDER BY `pos` ASC", $x22);
        }
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
                   
                
               
            }
        }
        else
        {
            array_push($GLOBALS["referralData"], ["<div hidden='hidden'>" . "" . strtolower($push) . "</div><div style='color:blue'>Add L ", "" . strtolower($push) . ""], ["<div hidden='hidden'>" . "" . strtolower($push) . "</div><div style='color:red'>Add R ", "" . strtolower($push) . ""]);
        }


    
    }
}
class dispGroup{

    public function a($a,$b){
        $x = queryAct("SELECT * FROM `referrals` WHERE `head`='$a' AND `pos`=$b");
        if($x->rowCount()>=1){
            $x1 = $x->fetch()['tail'];
            $this->b($x1,$b);
        }
    }

    private function b($a,$b){
        echo '<script>groupings("' .$a. '",'.$b.')</script>';
        $x = queryAct("SELECT * FROM `referrals` WHERE `head`='$a' ORDER BY pos DESC");
        if($x->rowCount()>=1){
            while($x1=$x->fetch()){
               $this->b($x1['tail'],$b);
            }
        }
        
    }
    

}

function  NametoId($a){
    $x = queryAct("SELECT * FROM clients WHERE CONCAT(first_name,' ',last_name)='$a' ");
    if($x->rowCount()>=1){
        $x1 = $x->fetch();
        return $x1["id"];
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

class down_info
{

    public function search($push)
    {
        $this->query1("SELECT * FROM `referrals` INNER JOIN `clients` WHERE `referrals`.downline=$push AND `clients`.id=$push LIMIT 1");
    }

    private function query1($a)
    {
        
        $data = queryAct($a);
        $row = $data->rowCount();

        if ($row)
        {
            $b = $data->fetch(PDO::FETCH_ASSOC);
            if ($b["pos"])
            {
                $pos = "Right";
            }
            else
            {
                $pos = "left";
            };
            $GLOBALS["down_info"] = ["id" => $b["id"], "name" => $b["first_name"] . " " . $b["last_name"], "pos" => $pos, ];
        }

    }

}

function go_setdwn()
{

    if (isset($_POST['downline']))
    {
        $t1 = $_POST['downline'];
        $gV=queryAct("SELECT `entry` FROM clients WHERE id=$t1");
        if($gV->rowCount()>=1){
            $getVer=$gV->fetch()['entry'];
            switch($getVer){
                case 1:
                    $ver=2;//1750 plan a
                break;
                case 2:
                    $ver=35;//100 plan b
                break;
                case 3:
                    $ver=4;//7000 plan c
                break;
                case 31:
                    $ver=31;//14000 plan c
                break;
                case 4:
                    $ver=4;//7000 plan d
                break;
                case 5:
                    $ver=5;//14000 plan p2
                break;
                    
            }

            if (query("SELECT * FROM `referrals` WHERE `downline`=$t1 LIMIT 1") == 0)
            {
                query("INSERT INTO referrals (`maxps`,`tail`,`head`,`pos`,`downline`,`ver`) VALUES (1,\"" . $_POST['tail'] . "\",\"" . $_POST['head'] . "\"," . $_POST['pos'] . "," . $_POST['downline'] . ",$ver)");
                query("UPDATE `clients` SET `deployed` = 1 WHERE `clients`.`id` = " . $_POST['downline']);
                Subquery($_POST['head']);
            }
        }


    }
}

function  go_send_codes()
{
    if(isset($_POST['client'])){
        $cc = $_POST['client'];
        $cc1 = $_POST['user'];
        switch($_POST['ct']){
            case 1:
                $vcType="code";
                $entry=1;
            break;
            case 2:
                $vcType="code2";
                $entry=2;
            break;
            case 3:
                $vcType="code3";
                $entry=31;//original=3
            break;
            case 4:
                $vcType="code4";
                $entry=4;
            break;
            case 5:
                $vcType="code5";
                $entry=5;
            break;

        }
        $cc2=queryAct("SELECT * FROM clients WHERE id=$cc");
        $cc3=$cc2->fetch(PDO::FETCH_ASSOC);

        if($cc3['status']==0){
            if (query("SELECT * FROM `earnings` WHERE `client` =$cc1 AND  `$vcType`!=0 LIMIT 1")) {
                if (query("UPDATE `clients` SET `status` = 1,`entry`=$entry WHERE `clients`.`id` = $cc")) {
                    query("UPDATE `earnings` SET `$vcType` = `$vcType`-1 WHERE `client` =$cc1 ");
                }
            }
        }else{
            if(query("SELECT * FROM `earnings` WHERE `client` =$cc LIMIT 1")>=1){
                query("UPDATE `earnings` SET `$vcType` = `$vcType`+1 WHERE `client` =$cc ");
                query("UPDATE `earnings` SET `$vcType` = `$vcType`-1 WHERE `client` =$cc1 ");
            }else{
                query("INSERT INTO `earnings` (client,`$vcType`) VALUES ($cc,1)");
                query("UPDATE `earnings` SET `$vcType` = `$vcType`-1 WHERE `client` =$cc1 ");
            }
            

        }

    }
    
}

function  go_cash_o($a,$b,$c,$d,$e){
    $x=queryAct("SELECT * FROM earnings WHERE client=$a LIMIT 1");
    if($x->rowCount()>=1){
        $x1 = $x->fetch(PDO::FETCH_ASSOC);
        $x2 = $x1['cash']-$x1['cash_o'];
        if($x2>=$b){
           if(query("INSERT INTO cash_outs(client,cash_out,coname,comobile,payopt) VALUES($a,$b,'$c','$d','$e')")){
               if(query("UPDATE earnings SET cash_o=cash_o+$b WHERE client=$a ")){
                    echo "Pending";
                }
             
           }
        
        }else{
            echo "Insufficient Balance";
       }
    }


}

function go_trans_h($a){
    $x = queryAct("SELECT * FROM cash_outs WHERE client=$a ORDER BY id DESC");
    if($x->rowCount()>=1){
        while($x1=$x->fetch(PDO::FETCH_ASSOC)){
            echo '<tr>';
            echo '<td>Cash Out</td>';
            echo '<td>₱'.$x1['cash_out'].'</td>';
            echo '<td>'.$x1['date'].'</td>';
            echo '</tr>';
        }
    }
}
function Subquery($a)
{
    $data = queryAct("SELECT SUM(`maxps`) AS `num_ps` FROM `referrals` WHERE `head`=\"" . $a . "\"");
    $data2 = $data->fetch(PDO::FETCH_ASSOC);
    $row = $data->rowCount();

    if ($row)
    {
        if ($data2['num_ps'] > 1)
        {
            query("UPDATE `referrals` SET `maxps` = 2 WHERE `head` =\"" . $a . "\" ");

        }

    }

}

function activate($a ,$a1 )
{
    $x = queryAct("SELECT CONCAT(first_name, ' ', last_name) AS full_name FROM clients WHERE `id`=$a1 AND `status`=1 AND `deployed`=1 AND `activated`=0");

    if ($x->rowCount())
    {
        $x2 = $x->fetch(PDO::FETCH_ASSOC);
        $a2 = $x2['full_name']; //client
        if (query("UPDATE `clients` SET `activated` = 1 WHERE `id` = $a1") >= 1)
        {
            send_ref($a, $a2,$a1);
        }
    }

    echo "activated";
}
///direct referral
function send_ref($a, $a2,$a1)
{
    $enT=queryAct("SELECT * FROM clients WHERE id=$a1 LIMIT 1");
    if($enT->rowCount()>=1){

        switch($enT->fetch()['entry']){
            case 1:
                $cashIn=$GLOBALS["Dcash"];
                $directV="directV2";
            break;
            case 2:
                $cashIn=$GLOBALS["Dcash2"];
                $directV="directV3";
            break;
            case 3:
                $cashIn=$GLOBALS["Dcash3"];
                $directV="directV4";
            break;
            case 31:
                $cashIn=$GLOBALS["Dcash31"];
                $directV="directV41";
            break;
            case 4:
                $cashIn=$GLOBALS["Dcash4"];
                $directV="directV5";//directv =Dcash4+1;
            break;
            case 5:
                $cashIn=0;
                $directV="directVP2";//p2;
            break;
        }
    }
    $b = queryAct("SELECT * FROM `earnings` WHERE `client`=$a LIMIT 1"); //direct
    $row = $b->rowCount();
    if ($row)
    {
        if (query("UPDATE `earnings` SET `cash`=`cash`+$cashIn,`$directV`=`$directV`+1 WHERE `client`=$a") >= 1)
        {
            //send_points($a2, 0); //client
            
        }
    }
    else
    {
        if (query("INSERT INTO earnings (`client`,`$directV`,`cash`) VALUES ($a,1,$cashIn)") >= 1)
        {
            //send_points($a2, 0); //client
            
        }
    }
}
///check all uplines
function send_points($a2, $points)
{
    
    $x1 = queryAct("SELECT * FROM referrals WHERE tail='$a2'"); //client
    if ($x1->rowCount() >= 1) {
        $x2 = $x1->fetch(PDO::FETCH_ASSOC);
        $x3 = $x2['points'] + $points;
        $x4 = $x2['head']; //upline
        if (queryAct("UPDATE referrals SET points=$x3 WHERE tail='$a2'"))
        {
            array_push($GLOBALS['intpos'],[$GLOBALS['intlvl']++,$x2['pos']]);
            $lvl=filter_lvl();
            
            if ($lvl[0] == "a") {
                $cpos = "b".ltrim($lvl,"a");
            } else {
                $cpos = "a".ltrim($lvl,"b");
            }
          
            if(!query("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = 'levels'AND column_name = '$lvl'")){
                query('ALTER TABLE levels ADD COLUMN IF NOT EXISTS ' . $lvl . ' INT NOT NULL');
                    if(query("SELECT * FROM levels WHERE client='$x4'")>=1){
                        query("UPDATE levels SET $lvl=$lvl+1 WHERE client='$x4'");
                    }else{
                        query("INSERT INTO levels (`client`,$lvl) VALUES ('$x4',1)");
                    }

             
            }else{
                if(query("SELECT * FROM levels WHERE client='$x4'")>=1){
                    query("UPDATE levels SET $lvl=$lvl+1 WHERE client='$x4'");
                }else{
                    query("INSERT INTO levels (`client`,$lvl) VALUES ('$x4',1)");
                }
            }
            //
                $n = queryAct("SELECT * FROM levels WHERE client='$x4'");
                if($n->rowCount()>=1){
                $n1= $n->fetch(PDO::FETCH_ASSOC);
                    if (@$n1[$lvl] == @$n1[$cpos]) {
                       // query("UPDATE levels SET paired=paired+1 WHERE client='$x4'");
                       send_50cash($x4);
                } elseif(@$n1[$lvl]<>0 AND @$n1[$cpos]<>0 AND @$n1[$cpos]>@$n1[$lvl]){
                    //query("UPDATE levels SET paired=paired+1 WHERE client='$x4'");
                    send_50cash($x4);

                }
                }

            send_points($x4, $points); //check all uplines pairing
        }
       
    }
   
}

function send_50cash($x4){
    if($a1=queryAct("SELECT * FROM clients WHERE CONCAT(first_name,' ',last_name)='$x4'")){
        $a2 = $a1->fetch(PDO::FETCH_ASSOC);
        $a3 = $a2['id'];
        $b = queryAct("SELECT * FROM `earnings` WHERE `client`=$a3"); 
        $row = $b->rowCount();
        if ($row>=1)
        {
            query("UPDATE `earnings` SET `cash`=`cash`+50,`pair`=`pair`+1 WHERE `client`=$a3") ;

        }
        else
        {
            query("INSERT INTO earnings (`client`,`pair`,`cash`) VALUES ($a3,1,50)");

        }
    }
}
function filter_lvl()
{
    if(json_encode($GLOBALS['intpos'][count($GLOBALS['intpos']) - 1][1])==1){
        $a = "b";
    } else {
        $a = "a";
    }
    return $a.count($GLOBALS['intpos']);
}


function score($ax)
{

    $a = queryAct("SELECT * FROM `earnings` WHERE `client`=$ax");
    $a2 = $a->fetch(PDO::FETCH_ASSOC);
    $a3 = $a->rowCount();
    if ($a3)
    {

            $pa = new pointsAB();
            $pa1 = $pa->getFullname($ax, 0);
            $pb = new pointsAB();
            $pb1 = $pb->getFullname($ax, 1);
            $groupA = ($pa1) + floor($a2['pointsA']);
            $groupB = ($pb1) + floor($a2['pointsB']);

            if($groupA-$groupB>=0){
                $groupM = floor($groupB/70);
            }else{
                $groupM = floor($groupA/70);
            }
            if($groupM<>$a2['pairV2']){
                $opair = $a2['pair'];
                $xpair =$opair-$a2['pair_o'];
                $newPair =  $groupM-($xpair*50);
                $newPair1 =   $newPair-$a2['pairV2'];
                query("UPDATE `earnings` SET pairV2=$groupM,cash=cash+$newPair1,pair_o=$opair WHERE `client`=$ax");
            }
            
        $arrayD = [
            "cash_bal" => number_format($a2["cash"]-$a2["cash_o"]) ,
            "cash" => number_format($a2["cash"]) ,
            "dr" => number_format($a2["direct"]+$a2["directV2"]+$a2["directV3"]+$a2["directV4"]+$a2["directV41"]+$a2["directV5"]) , 
            "dr2" => "₱" . number_format(($a2["direct"] * 150)+($a2["directV2"]*$GLOBALS["Dcash"])+($a2["directV3"]*$GLOBALS["Dcash2"])+($a2["directV4"]*$GLOBALS["Dcash3"])+($a2["directV41"]*$GLOBALS["Dcash31"])+($a2["directV5"]*$GLOBALS["Dcash4"])) , 
            "ownpoints" => number_format($a2["points"]),
            "ownpcash" => number_format($a2["points_o"]),
            "points" => number_format($groupM*70) , //gmatch
            "points2" => "₱" . number_format($groupM),
            "pointsA" =>number_format($groupA) , 
            "pointsB" =>number_format($groupB),
            "oref" => number_format($a2["oref"]+$a2["oref1200"]),
            "orefC" => number_format($a2["oref"]*500+$a2["oref1200"]*1200),
            "orefP" => number_format($a2["oref"]*18000+$a2["oref1200"]*70000),

        ];

        echo json_encode($arrayD);
    }
    else
    {
        $arrayD = [
            "cash_bal" =>0,
            "cash" => 0, 
            "dr" => 0, 
            "dr2" => "₱" . 0, 
            "points" => 0, 
            "points2" => "₱" . 0,
            "pointsA" => 0 , 
            "pointsB" =>0,
            "pointsAm" => 0 , 
            "pointsBm" =>0
        ];

        echo json_encode($arrayD);

    }
   
}

class pointsAB{
    public $xcount = 0;
    public function getFullname($a,$b){
        $x = queryAct("SELECT CONCAT(first_name,' ',last_name) AS full_name FROM clients WHERE id=$a LIMIT 1");
        $x1 = $x->fetch(PDO::FETCH_ASSOC);
        $x2 = $x1['full_name'];
        $y = queryAct("SELECT * FROM referrals WHERE head='$x2' AND pos=$b ");
        if ($y->rowCount() >= 1) {
            $y1 = $y->fetch(PDO::FETCH_ASSOC);
            $this->firstStage($y1['tail'],$y1['ver'],$a);
        }
       return $this->xcount;
    }

    private function firstStage($a,$b,$cid){

        switch($b){
            case 2:
                $b2=3500/$b;//1750
            break;
            case 35:
                $b2=3500/$b;//100
            break;
            case 4:
                $b2=7000;//7000
            break;
            case 31:
                $b2=14000;//14000
            break;
            case 5:
                if(p2($cid)){
                    $b2=14000;//14000
                }else{
                    $b2=0;
                }
                
            break;
            default:
                $b2=3500/$b;
            break;
        }

        $this->xcount= $this->xcount+($b2);
        $y = queryAct("SELECT * FROM referrals WHERE head='$a'");
        if($y->rowCount()>=1){
           while ($y1 = $y->fetch(PDO::FETCH_ASSOC)){
          $this->firstStage($y1['tail'],$y1['ver'],$cid);
           }


        }
       
    }
   

}

define("DOC_ROOT", $_SERVER['DOCUMENT_ROOT'] . "/");
define("PDF_UPLOADS", DOC_ROOT . "products/");

if (isset($_FILES['file']['name']))
{

    $name = $_FILES['file']['name'];
    $size = $_FILES['file']['size'];
    $type = $_FILES['file']['type'];
    $tmp_name = $_FILES['file']['tmp_name'];

    if (!empty($name))
    {
        move_uploaded_file($tmp_name, PDF_UPLOADS . $name);
    }

}

function getpackage($a){
    $getpackage = queryAct("SELECT * FROM clients WHERE id=$a ");       
    switch($getpackage->fetch(PDO::FETCH_ASSOC)["entry"]){
        case 0:
            $package="0";
        break;
        case 1:
            $package="A";
        break;
        case 2:
            $package="B";
        break;
        case 3:
            $package="C";
        break;
        case 31:
            $package="C";
        break;
        case 4:
            $package="D";
        break;
        case 5:
            $package="P2";
        break;

    }
    return $package;
}
function go_products()
{
    $a = $_POST['item'];
    $b = $_POST['qty'];
    $c = $_POST['price'];
    $d = $_POST['image'];
    query("INSERT INTO products (`item`,`qty`,`price`,`image`) VALUES ('$a',$b,$c,'$d')");
}

function products_view($a,$invDir)
{
    $chk = query("SELECT `deployed` FROM `clients` WHERE `id`=$a AND `status`=1 AND `deployed`=1 AND `activated`=0");

    if ($chk)
    {


        $data = queryAct("SELECT * FROM `products` WHERE `active`=1 ORDER BY `id` ASC");

        if ($outData = $data->fetch(PDO::FETCH_ASSOC))
        {

            echo '<div class="col shop_item">';
            echo '<div class="card">';
            echo '<img class="card-img w-100 d-block view_item_img" src="admin/180.jpg" height="128" />';
            echo '<div class="card-body">';
            echo '<p class="text-truncate  text-capitalize card-text" style="font-size: 10px;">Package Type = '. getpackage($a).'</p>';
            echo '<hr />';
            echo '<button class="btn btn-danger float-start" onClick="client_active();" type="button" style="width: 100%;">Activate</button>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
            echo '<script type="text/javascript">$(".loader1").hide();$("#products_view").show();</script>';


        }
    }
    else
    {
        if($invDir==1){
            $chk2 = query("SELECT `deployed` FROM `clients` WHERE `id`=$a AND `status`=1 AND `deployed`=1");

            if ($chk2)
            {
                $opic=showOutPic(1); 
                    //echo '<div class="col-12"><input id="find_outlet" type="search" style="width: 100%;" placeholder="Search Outlet" /></div>';         
                    echo '<div class="col"  onClick="openStoreItems(1,\'Admin\')">';
                    echo '<div class="card border-0 rounded-0" style="background: rgb(255,255,255);"><img class="card-img-top w-100 border rounded-0  d-block" src="admin/'.$opic.'" />';
                    echo '<div class="card-body">';
                    echo '<h6 class="text-truncate text-capitalize card-text" style="font-size: 10px;">GTgrocers</h6>';
                    echo '<h6 class="text-capitalize card-text" style="font-size: 10px;">09*********</h6>';
                    echo '<p class="text-truncate text-capitalize card-text" style="font-size: 10px;">Admin</p>';
                    echo '</div>';
                    echo '</div>';
                    echo '</div>';
                    echo '<script type="text/javascript">$(".loader1").hide();$("#products_view").show();</script>';
    
                    $data =queryAct("SELECT * FROM `outlet` WHERE `admin`=0");
                    if($data->rowCount()>=1){
                        while ($outData = $data->fetch(PDO::FETCH_ASSOC))
                        {
                            $d1=queryAct("SELECT id,addrs,mobile,CONCAT(first_name,' ',last_name) as fname FROM `clients` WHERE id=".$outData['client']);
                            if($d1->rowCount()>=1){
                                $d2=$d1->fetch();
                                $opic=showOutPic($d2['id']);
                                echo '<div class="col" onClick="openStoreItems('.$d2['id'].',\'Admin\')">';
                                echo '<div class="card border-0 rounded-0" style="background: rgb(255,255,255);"><img class="card-img-top w-100 border rounded-0 d-block" src="admin/'.$opic.'" />';
                                echo '<div class="card-body">';
                                echo '<h6 class="text-capitalize card-text" style="font-size: 10px;">'.strtolower($d2['fname']).'</h6>';
                                echo '<h6 class="text-capitalize card-text" style="font-size: 10px;">'.strtolower($d2['mobile']).'</h6>';
                                echo '<p class="text-capitalize card-text" style="font-size: 10px;">'.strtolower($d2['addrs']).'</p>';
                                echo '</div>';
                                echo '</div>';
                                echo '</div>';
                                //echo '<script type="text/javascript">$(".loader1").hide();$("#products_view").show();</script>';
                            }
    
                        }
                    }
            }

        }else{
            $x=queryAct("SELECT * FROM orders WHERE clientid=$a AND `status`=0");
            if($x->rowCount()==0){
            openStoreItems($invDir);}else{
                echo '<script>$(".cart_click")[0].click();</script>';
            }
        }

    }

}

function openStoreItems($invDir){
    if($invDir==0){
        $invDirX="products";
    }else{
        $invDirX=$invDir;
    }
           $data =queryAct("SELECT * FROM `$invDirX` WHERE `active`=0 AND view=1 GROUP BY categ ORDER BY `categ` ASC");
                if($data->rowCount()>=1){
                    echo '<script type="text/javascript">open_item();$(".loader1").hide();$("#products_view").show();</script>';
                    while ($outData = $data->fetch(PDO::FETCH_ASSOC))
                    {
                        $categ = ucwords($outData['categ']);
        
                        
                        echo '<div class="col shop_category" >';
                        echo '<div class="card border rounded-0 shadow">';
                        echo '<img class="card-img-top w-100 d-block border rounded-0 view_item_img" src="admin/'. $outData['imageDir'] .'" style="width: 500px;padding:0px;" height="120px" loading="eager" />';
                        echo '<div class="card-body">';
                        echo '<p class="text-uppercase fw-bold text-center text-success card-text item_category text-truncate" style="color: var(--bs-dark);font-size: 12px;" data="'. $categ.'">'. $categ.'</p>';
                        echo '</div>';
                        echo '</div>';
                        echo '</div>';
                        
            
            
            
                    }

                }


}
function categ_items($a,$b,$invDir){
    {
        $chk2 = query("SELECT `deployed` FROM `clients` WHERE `id`=$a AND `status`=1 AND `deployed`=1");

        if ($chk2)
        {
          
            $data =queryAct("SELECT * FROM `$invDir` WHERE `active`=0 AND `categ`='$b' AND view=1 ORDER BY `id` ASC");
            echo '<script type="text/javascript">shop_active();$(".loader1").hide();$("#products_view").show();</script>';
            while ($outData = $data->fetch(PDO::FETCH_ASSOC))
            {
                $itemtag = $outData['id'];
                $item = ucwords($outData['item']);
                $categ = ucwords($outData['categ']);
                $qty = $outData['qty'];
                $price = $outData['price'];
                $ppoints = $outData['points'];
                
                echo '<div class="col shop_item" >';
                echo '<div class="card rounded-0"><img class="card-img-top rounded-0 w-100 d-block view_item_img" src="admin/' . $outData['imageDir'] . '" style="width: 500px;" height="120px" loading="eager" />';
                echo '<div class="card-body">';
                echo '<h6 class="text-truncate card-title  text-primary item_tag" style="color: #0e0e0c;font-size: 12px;" data="'.$itemtag.'">' . $item . '</h6>';
                echo '<h6 class="text-secondary card-subtitle item_stock" style="color: #0e0e0c;font-size: 10px;margin-bottom:10px;" data="' . $qty . '" >Stock :' . $qty . '</h6>';
                echo '<h6 class="text-secondary card-subtitle text-truncate" style="color: #0e0e0c;font-size: 10px;">Category :' . $categ . '</h6>';
                echo '<hr />';
                echo '<p class="text-end float-start card-text item_price text-danger" style="color: var(--bs-dark);font-size: 16px;font-style: italic;" data="' . $outData['price'] . '">₱' . $price . '</p>';
                echo '<p class="text-end float-end card-text item_points text-success" style="color: var(--bs-dark);font-size: 12px;font-style: italic;" data="' . $ppoints . '">+' . $ppoints . '</p>';
                echo '</div>';
                echo '</div>';
                echo '</div>';
                
    
    
    
            }
        }
    }
}

function products_codes($a)
{
    //a=c b=c2 c=c3  d=c4 e=c5
    $forAct = query("SELECT * FROM `clients` WHERE `id`=$a AND `status`=1 AND `deployed`=1 AND activated=1");
    if($forAct>=1){
        $getcode = queryAct("SELECT * FROM earnings WHERE client=$a ");
        $getcode1 = $getcode->fetch(PDO::FETCH_ASSOC);
        if (@$getcode1['code'] > 0) {
            echo '<div class="col shop_item" style="margin-bottom: 10px;">';
            echo '<div class="card"><span class="badge bg-dark text-light item_stock" style="width: 75px;margin: 5px;" data="30">' . $getcode1['code'] . ' AC</span><img class="card-img w-100 d-block view_item_img" height="128" src="/admin/180.jpg" />';
            echo '<div class="card-body">';
            echo '<h6 class="card-title" style="color: #0e0e0c;">Package A</h6>';
            echo '<p class="text-truncate  text-capitalize card-text" style="font-size: 10px;">Ref= 75php 1750pts</p>';
            echo '<p class="text-truncate text-capitalize card-text" style="font-size: 10px;">Activation</p>';
            echo '<hr /><button class="btn btn-sm btn-danger sactcode" type="button" style="width: 100%;" onclick="send_vcode(1)">Send</button>';
            echo '</div>';
            echo '</div>';
            echo '</div>';

            
        }
        if (@$getcode1['code2'] > 0) {
            echo '<div class="col shop_item" style="margin-bottom: 10px;">';
            echo '<div class="card"><span class="badge bg-dark text-light item_stock" style="width: 75px;margin: 5px;" data="30">' . $getcode1['code2'] . ' AC</span><img class="card-img w-100 d-block view_item_img" height="128" src="/admin/180.jpg" />';
            echo '<div class="card-body">';
            echo '<h6 class="card-title" style="color: #0e0e0c;">Package B</h6>';
            echo '<p class="text-truncate  text-capitalize card-text" style="font-size: 10px;">Ref= 25php 100pts</p>';
            echo '<p class="text-truncate  text-capitalize card-text" style="font-size: 10px;">Activation</p>';
            echo '<hr /><button class="btn btn-sm btn-success sactcode" type="button" style="width: 100%;" onclick="send_vcode(2)">Send</button>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
        }
        if (@$getcode1['code3'] > 0) {
            echo '<div class="col shop_item" style="margin-bottom: 10px;">';
            echo '<div class="card"><span class="badge bg-dark text-light item_stock" style="width: 75px;margin: 5px;" data="30">' . $getcode1['code3'] . ' AC</span><img class="card-img w-100 d-block view_item_img" height="128" src="/admin/180.jpg" />';
            echo '<div class="card-body">';
            echo '<h6 class="card-title" style="color: #0e0e0c;">Package C</h6>';
            echo '<p class="text-truncate  text-capitalize card-text" style="font-size: 10px;">Ref= 150php 7000pts</p>';
            echo '<p class="text-truncate  text-capitalize card-text" style="font-size: 10px;">Activation</p>';
            $getEntry = queryAct("SELECT * FROM clients WHERE id=$a ");
            $getEntry1 = $getEntry->fetch(PDO::FETCH_ASSOC);
            if($a>1000){
                if($getEntry1['entry']==3){
                    echo '<hr /><button class="btn btn-sm btn-success sactcode" type="button" style="width: 100%;" onclick="upg();">Update</button>';
                    echo '<script>
                        function upg(){$.ajax({
                        type: "POST",
                        url: "/upgrade.php/",
                        data: {register: "activate",upline:upline,clientId:clientId},
                        success: function(data) {
                            if(data=="success"){location.href = "/";}
                        }});}</script>';
                }else{
                    echo '<hr /><button class="btn btn-sm btn-success sactcode" type="button" style="width: 100%;" onclick="upg();">Upgrade</button>';
                    echo '<script>
                        function upg(){$.ajax({
                        type: "POST",
                        url: "/upgrade.php/",
                        data: {register: "activate",upline:upline,clientId:clientId},
                        success: function(data) {
                            if(data=="success"){location.href = "/";}
                        }});}</script>';
                }
            }

            
            echo '<hr /><button class="btn btn-sm btn-warning sactcode" type="button" style="width: 100%;" onclick="send_vcode(3)">Send</button>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
        }
            if (@$getcode1['code4'] > 0) {
            echo '<div class="col shop_item" style="margin-bottom: 10px;">';
            echo '<div class="card"><span class="badge bg-dark text-light item_stock" style="width: 75px;margin: 5px;" data="30">' . $getcode1['code4'] . ' AC</span><img class="card-img w-100 d-block view_item_img" height="128" src="/admin/180.jpg" />';
            echo '<div class="card-body">';
            echo '<h6 class="card-title" style="color: #0e0e0c;">Package D</h6>';
            echo '<p class="text-truncate  text-capitalize card-text" style="font-size: 10px;">Ref= 150php 7000pts</p>';
            echo '<p class="text-truncate  text-capitalize card-text" style="font-size: 10px;">Activation</p>';
            echo '<hr /><button class="btn btn-sm btn-success sactcode" type="button" style="width: 100%;" onclick="send_vcode(4)">Send</button>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
        }

        if (@$getcode1['code5'] > 0) {
            echo '<div class="col shop_item" style="margin-bottom: 10px;">';
            echo '<div class="card"><span class="badge bg-dark text-light item_stock" style="width: 75px;margin: 5px;" data="30">' . $getcode1['code5'] . ' AC</span><img class="card-img w-100 d-block view_item_img" height="128" src="/admin/180.jpg" />';
            echo '<div class="card-body">';
            echo '<h6 class="card-title" style="color: #0e0e0c;">Package P2</h6>';
            echo '<p class="text-truncate  text-capitalize card-text" style="font-size: 10px;">Ref= 0php 14000pts</p>';
            echo '<p class="text-truncate  text-capitalize card-text" style="font-size: 10px;">Activation</p>';
            $getEntry = queryAct("SELECT * FROM clients WHERE id=$a ");
            $getEntry1 = $getEntry->fetch(PDO::FETCH_ASSOC);
            
            if($a>1000){
                if($getEntry1['entry']<>5){
                    echo '<hr /><button class="btn btn-sm btn-success sactcode" type="button" style="width: 100%;" onclick="upg();">Upgrade</button>';
                    echo '<script>
                        function upg(){$.ajax({
                        type: "POST",
                        url: "/upgradep2.php/",
                        data: {register: "activate",upline:upline,clientId:clientId},
                        success: function(data) {
                            if(data=="success"){location.href = "/";}
                        }});}</script>'; 
                }
            }
            
            echo '<hr /><button class="btn btn-sm btn-warning sactcode" type="button" style="width: 100%;" onclick="send_vcode(5)">Send</button>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
        }
        
    }

}
function addtocart($a,$b,$c,$d,$invDir){
    if ($d) {
        if($invDir=="products"){
            $invDirX=0;
        }else{
            $invDirX=$invDir;
        }

        if(stockChk($invDir,$b,$c)){

            $x1=queryAct("SELECT * FROM cart WHERE client=$a AND item=$b AND status=0 LIMIT 1");
            if($x1->rowCount()>=1){
                $x2 = $x1->fetch()['id'];
                query("UPDATE `cart` SET `qty`=`qty`+$c,`store`=$invDirX WHERE id=$x2");
                displayCart($a);
            }else{
                if (query("INSERT INTO cart (`client`,`item`,`qty`,`store`) VALUES ($a,$b,$c,$invDirX)")) {
                    displayCart($a);
                }
            }
        }else{
            echo "<script>alert('Out of Stock !')</script>";
        }

       
    }else{
        displayCart($a);
    }
    
}

function displayCart($a){
    $sumofprice = 0;
    $sumofpoints = 0;
    $i = 0;
    $xs = queryAct("SELECT * FROM cart WHERE client=$a AND status=0");
    if ($xs->rowCount() >= 1) {
        $s1=$xs->fetch();
        if( $s1['store']==0){
            $store='products';
        }else{
            $store=$s1['store'];
        }
        echo '<div class="col" style="margin-top: 20px;">';
        echo '<h6 class="text-center" ><i class="far fa-edit text-danger"></i> Your Order From</h6>';
        echo '<hr>';
        echo '<h6 class="text-start text-truncate" >&nbsp<i class="fas fa-store-alt text-success"></i> STORE : '.strtoupper(IdtoName($store)[0]).'</h6>';
        echo '<p class="text-start  text-truncate">&nbsp<i class="fas fa-map-marked-alt text-success"></i><span style="font-size: 12px;"> '.strtoupper(IdtoName($store)[1]).'</span></p>';
        echo '<table class="table table-striped table-sm ">';
        echo '<caption class="text-center"><button class="btn btn-danger" type="button" style="width: 80%;" onClick="chkout()"><i class="far fa-check-square"></i> Check Out</button></caption>';
        echo '<thead>';
        echo '<tr>';
        echo '<th>Item</th>';
        echo '<th>UP</th>';
        echo '<th>Pts</th>';
        echo '<th>Qty</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        $x1 = queryAct("SELECT * FROM cart WHERE client=$a AND status=0");
        while ($x2 = $x1->fetch()) {
            $i++;
            $itemid = $x2['item'];

            $y1 = queryAct("SELECT * FROM `$store` WHERE id=$itemid");
            $y2 = $y1->fetch();
            $sumofprice =  $sumofprice+($y2['price'] * $x2['qty']);
            $sumofpoints = $sumofpoints+($y2['points'] * $x2['qty']);

            echo '<tr>';
            echo '<td>' . ucwords($y2['item']). '</td>';
            echo '<td>' . $y2['price']. '</td>';
            echo '<td>' . $y2['points']. '</td>';
            echo '<td>*' . $x2['qty']. '</td>';
            echo '<td class="text-center"><button class="btn btn-dark btn-sm" type="button" onClick="go_delete('.$x2['id'].')"><i class="fas fa-trash-alt"></i></button></td>';
            echo '</tr>';

        }
        echo '<tr>';
        echo '<td>Total Points</td>';
        echo '<td></td>';
        echo '<td id="sumofpoints" data="'.$sumofpoints.'">'.$sumofpoints.'</td>';
        echo '<td></td>';
        echo '<td></td></tr>';
        echo '<tr>';
        echo '<td>Order Total</td>';
        echo '<td id="sumofprice" data="'.$sumofprice.'">₱'.$sumofprice.'</td>';
        echo '<td></td>';
        echo '<td></td>';
        echo '<td></td></tr>';
        echo '</tbody>';
        echo '</table>';
        echo '</div>';
        echo '</div>';
        echo '<script type="text/javascript">cartinfo('.$i.',\''.$store.'\');</script>';
    } else {
       if($x11 = queryAct("SELECT * FROM cart WHERE client=$a AND `status`=1")){
        $x12 = queryAct("SELECT * FROM orders WHERE clientid=$a AND `status`=0");
       }
        
        if ($x11->rowCount() >= 1) {
            $x22 = $x12->fetch();
            switch($x22["mop"]){
                case "COD":
                    $status1 = "To Pay";
                    $status2 = "";
                break;
                case "Gcash":
                    $status1 = "To Pay";
                    $status2 = "";
                break;
                case "Wallet":
                    $status1 = "Paid";
                    $status2 = "hidden";
                break;
            }
            echo '<div class="col">';
            echo '<div class="table-responsive border rounded " style="background: #ffffff;">';
            echo '<table class="table table-striped table-sm ">';
            echo '<thead>';
            echo '<tr>';
            echo '<th>Order Details</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';
            echo '<tr><td>Status:</td><td>'.$status1.'</td><td></td><td></td></tr>';
            echo '<tr><td>Store:</td><td>'.strtoupper(IdtoName($x22['store'])[0]).'</td><td></td><td></td></tr>';
            echo '<tr><td></td><td style="font-size:12px">'.strtoupper(IdtoName($x22['store'])[1]).'</td><td></td><td></td></tr>';
            echo '<tr><td>Name:</td><td>'.ucwords($x22["fullname"]).'</td><td></td><td></td></tr>';
            echo '<tr><td>Addrs:</td><td>'.ucwords($x22["addrs"]).'</td><td></td><td></td></tr>';
            echo '<tr><td>Mobile:</td><td>'.$x22["mobile"].'</td><td></td><td></td></tr>';
            echo '<tr><td>Payment:</td><td>'.$x22["mop"].'</td><td></td><td></td></tr>';
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
            echo '<caption class="text-center "><button class="btn btn-danger" type="button" style="width: 80%;" onClick="cancelOrder()" '.$status2.'>Cancel Order</button></caption>';
            echo '<tbody>';
            while ($x21 = $x11->fetch()) {
                $i++;
                $itemid = $x21['item'];
                if($x21['store']==0){
                    $store='products';
                    $storeNum=0;
                }else{
                    $store=$x21['store'];
                }
                $y11 = queryAct("SELECT * FROM `$store` WHERE id=$itemid");
                $y21 = $y11->fetch();
                $sumofprice = $sumofprice + ($y21['price'] * $x21['qty']);
                $sumofpoints = $sumofpoints + ($y21['points'] * $x21['qty']);


                echo '<tr>';
                echo '<td>' . ucwords($y21['item']) . '</td>';
                echo '<td>' . $y21['price'] . '</td>';
                echo '<td>' . $y21['points'] . '</td>';
                echo '<td>*' . $x21['qty'] . '</td>';
                echo '<td></td>';
                echo '</tr>';


            }
            echo '<tr>';
            echo '<td>Total Points</td>';
            echo '<td></td>';
            echo '<td id="sumofpoints" data="' . $sumofpoints . '">' . $sumofpoints . '</td>';
            echo '<td></td>';
            echo '<td></td></tr>';
            echo '<tr>';
            echo '<td>Order Total</td>';
            echo '<td id="sumofprice" data="' . $sumofprice . '">₱' . $sumofprice . '</td>';
            echo '<td></td>';
            echo '<td></td>';
            echo '<td></td></tr>';
            echo '</tbody>';
            echo '</table>';
            echo '</div>';
            echo '</div>';
            echo '<script type="text/javascript">
            cartinfo(' . $i . ');
            </script>';
        }
        echo '<script type="text/javascript">cartinfo("Cart");</script>';
        //echo '<script type="text/javascript">cartPending('.$x22['store'].');</script>';
    }
   
}

function CartHistory($ax,$ay,$opt,$status2,$cc){

    $x=queryAct("SELECT * FROM orders WHERE `status`=$ax AND clientid=$cc ORDER BY id DESC");
   
    if($x->rowCount()>=1){
        echo '<div class="col text-center"style="margin-top: 20px;">';
        echo '<h6 >&nbsp<i class="fa fa-history text-success" ></i> Order History</h6>';
        echo '<hr>';
        while($x1 = $x->fetch()){
            $a = $x1["clientid"];
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

            echo '<h6 class="text-start text-truncate" >&nbsp<i class="fas fa-store-alt "></i> STORE : '.strtoupper(IdtoName($x1['store'])[0]).'</h6>';
            echo '<p class="text-start  text-truncate">&nbsp<i class="fas fa-map-marked-alt "></i><span style="font-size: 12px;"> '.strtoupper(IdtoName($x1['store'])[1]).'</span></p>';
            echo '<table class="table table-striped table-sm ">';
            echo '<thead>';
            echo '<tr>';
            echo '<th>Order Details</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';
            echo '<tr><td>Date:</td><td>'.$x1["timestamp"].'</td><td></td><td></td></tr>';
            echo '<tr><td>Status:</td><td>'.$status1.'</td><td></td><td></td></tr>';
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
            echo '<caption class="text-center "><button class="btn btn-danger" type="button" style="width: 80%;" onClick="done('. $x1["id"].','. $x1["clientid"].')" '.$status2.'>Done</button></caption>';
            echo '<tbody>';
            $y=queryAct("SELECT * FROM cart WHERE client=$a AND `status`=$ay AND orderid=$orid");
            if($y->rowCount()>=1){
                
                while($y2 = $y->fetch()){
                    $a2 = $y2['item'];
                    if($x1['store']==0){
                        $store='products';
                    }else{
                        $store=$x1['store'];
                    }
                    $z=queryAct("SELECT * FROM `$store` WHERE id=$a2 ");
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
function go_delete($x){
    query("DELETE FROM `cart` WHERE `cart`.`id` =$x");
}
function cancelOrder($x){
    itmAM($x,1);
}
function orderConfirm($a,$b,$c,$d,$e,$f,$g,$invDir){
    $totalHits=0;
    if($invDir=="products"){
        $invDirX=0;
    }else{
        $invDirX=$invDir;
    }


    $ccc=queryAct("SELECT * FROM cart WHERE client=$g AND `status`=0 AND store=$invDirX");
    if($ccc->rowCount()>=1){
        
        while($ccc1=$ccc->fetch()){
           
           if(!stockChk($invDir,$ccc1['item'],$ccc1['qty'])){
            $totalHits++;
           }
        }
        
    }

if($totalHits==0){

    if($d=="Wallet"){
        $x1 = queryAct("SELECT * FROM earnings WHERE client=$g");
        if($x1->rowCount()>=1){
            $x2 = $x1->fetch();
            $wallet = $x2["cash"] - $x2["cash_o"];
            if($e<=$wallet){

                $search = queryAct("SELECT * FROM orders WHERE clientid=$g AND `status`=0");
                if($search->rowCount()>=1){
                $s0 = $search->fetch();
                $s1 = $s0['id'];
                $s2 = $s0['tprice']+$e;
                $s3 = $s0['tpoints']+$f;

                    if (query("UPDATE orders SET fullname='$a',addrs='$b',mobile='$c',mop='$d',tprice=$s2,tpoints=$s3,clientid=$g WHERE id=$s1")) {
                        if (query("UPDATE `cart` SET `status`=1 WHERE client=$g AND `status`=0")){
                            if (query("UPDATE `earnings` SET `cash_o`=`cash_o`+$e WHERE client=$g")){
                                itmAM($g,0);
                                echo "Success!";
                               }
                        }
                         
                     }

                }else{

                    if (query("INSERT INTO orders(fullname,addrs,mobile,mop,tprice,tpoints,clientid,store) VALUES('$a','$b','$c','$d',$e,$f,$g,$invDirX)")) {
                        if (query("UPDATE `cart` SET `status`=1 WHERE client=$g AND `status`=0")){
                            if (query("UPDATE `earnings` SET `cash_o`=`cash_o`+$e WHERE client=$g")){
                                itmAM($g,0);
                                echo "Success!";
                               }
                           }
                    }

                }

            }else{
                echo "Insufficient Balance";
            }
        }
    }else{
        $z1 = queryAct("SELECT * FROM orders WHERE clientid=$g AND mop='Wallet' AND `status`=0");
            if($z1->rowCount()>=1){
                echo "Pending Order Using Wallet";
            }else{
            $search = queryAct("SELECT * FROM orders WHERE clientid=$g AND `status`=0");
                if($search->rowCount()>=1){
                $s0 = $search->fetch();
                $s1 = $s0['id'];
                $s2 = $s0['tprice']+$e;
                $s3 = $s0['tpoints']+$f;

                    if (query("UPDATE orders SET fullname='$a',addrs='$b',mobile='$c',mop='$d',tprice=$s2,tpoints=$s3,clientid=$g WHERE id=$s1")) {
                        if (query("UPDATE `cart` SET `status`=1 WHERE client=$g AND `status`=0")){
                            itmAM($g,0);
                         echo "Success!";
                        }
                         
                     }

                }else{

                    if (query("INSERT INTO orders(fullname,addrs,mobile,mop,tprice,tpoints,clientid,store) VALUES('$a','$b','$c','$d',$e,$f,$g,$invDirX)")) {
                        if (query("UPDATE `cart` SET `status`=1 WHERE client=$g AND `status`=0")){
                            itmAM($g,0);
                         echo "Success!";
                        }
                         
                     }


                }


            }

    }
}else{
    echo "Item Out of Stock !";
}

 
}
function showOutPic($a){
    $x=queryAct("SELECT pic FROM outlet WHERE client=$a");
    if($x->rowCount()>=1){
        $x2=$x->fetch();
        return $x2['pic'];
    }
}

function itmAM($client,$m){

    $x=queryAct("SELECT * FROM cart WHERE client=$client AND `status`=1");

    if($x->rowCount()>=1){
        while($x2=$x->fetch()){
            $itemId=$x2['item'];
            $store=$x2['store'];
            if($store==0){
                $storex="products";
            }else{
                $storex=$store;
            }
            $qty=$x2['qty'];
            if($m==0){
            queryAct("UPDATE `$storex` SET `qty`=`qty`-$qty WHERE id=$itemId");
            }else{
            if(query("UPDATE `$storex` SET `qty`=`qty`+$qty WHERE id=$itemId")){
                if(query("DELETE FROM `cart` WHERE client=$client AND `status`=1")){
                    query("DELETE FROM `orders` WHERE clientid=$client AND `status`=0");
                }
            }

            }
        }
    }

}

function stockChk($invDir,$itmID,$itmQty){
    $chkS=queryAct("SELECT qty FROM `$invDir` WHERE id=$itmID ");
    if($chkS->rowCount()>=1){
        $chkqty=$chkS->fetch()['qty'];
        if($chkqty>=$itmQty){
            return 1;
        }
    }
}

function purcHist($a,$pos){
    $namex=IdtoName($a);
$x=queryAct("SELECT * FROM orders WHERE `status`=1 ORDER BY id DESC");
if($x->rowCount()>=1){
    while($x1=$x->fetch()){
        phFil(strtolower($x1['fullname']),strtolower($x1['fullname']),$x1['tpoints'],strtolower($namex[0]),$x1['timestamp'],$pos);
    }
}

}

function phFil($orderer,$buyer,$points,$check,$ts,$pos){
  
    $y=queryAct("SELECT * FROM referrals WHERE tail='$orderer'");
    if($y->rowCount()>=1){
        $z=$y->fetch();
        $sample=strtolower($z['head']);
        if($sample==$check){
            $posx=$z['pos'];
            if($posx==$pos){
                if($GLOBALS['limitCH']!=5){
                    $GLOBALS['limitCH']++;
                    if($pos==0){$g="A";}else{$g="B";}
                    echo '<tr class="text-truncate text-capitalize"><td>'.$buyer.'</td><td>+'.$points.'</td><td>'.$g."-".$ts.'</td></tr></br>';
    
                }
                

      }
        }else{

            phFil($sample,$buyer,$points,$check,$ts,$pos);

        }
    }
    
}

function  packPoints($a,$b){
    $x=queryAct("SELECT *,CONCAT(first_name,' ',last_name) AS fullname FROM clients WHERE activated=1 ORDER BY id DESC LIMIT 50");
    if($x->rowCount()>=1){
       while($x1=$x->fetch()){
            packPoints1($x1['fullname'],$b,IdtoName($a)[0],$x1['join_date'],$x1['entry'],$x1['fullname'],$a);
        }
    }
}
function  packPoints1($name,$Cid,$a,$jd,$entry,$memFname,$idc){
    $aa=strtoupper($a);
  
    $y=queryAct("SELECT * FROM referrals WHERE tail='$name'");
    if($y->rowCount()>=1){
        if($y1=$y->fetch()){
            $head=strtoupper($y1['head']);
            $posx=$y1['pos'];
            if($head==$aa && $posx==$Cid){
                  if($GLOBALS['limitCH']!=5){
                    $GLOBALS['limitCH']++;
                $ts=explode(" ",$jd)[0];
                switch($entry){
                    case 1:
                        $points=1750;$pkg="A";
                    break;
                    case 2:
                        $points=100;$pkg="B";
                    break;
                    case 3:
                        $points=7000;$pkg="C";
                    break;
                    case 31:
                        $points=14000;$pkg="C";
                    break;
                    case 4:
                        $points=7000;$pkg="D";
                    break;
                    case 5:
                        if(p2($idc)){
                            $points=14000;$pkg="P2";
                        }else{
                            $points=0;$pkg="P2";
                        }
                       
                    break;
                }
                if($Cid==0){$cId="A";}else{$cId="B";}
                echo '<tr class="text-truncate text-capitalize"><td>Pkg '.$pkg.': '.$memFname.'</td><td>+'.$points.'</td><td>'.$cId.'-'.$ts.'</td></tr></br>';
                  }
            }else{
                if($GLOBALS['limitCH']!=5){
                packPoints1($y1['head'],$Cid,$a,$jd,$entry,$memFname,$idc);
                }
            }
        }
    }

 }
 function otHist($a,$pos){
$namex=IdtoName($a)[0];
$x=queryAct("SELECT * FROM outlet WHERE `admin`=0 ORDER BY id DESC LIMIT 10");
if($x->rowCount()>=1){
    while($x1=$x->fetch()){
        $name=IdtoName($x1['client'])[0];
        
       otHist1($name,$pos,$namex,$x1['actdate'], $name);

    }
}

}

function otHist1($name,$Cid,$a,$jd,$memFname){
    $aa=strtoupper($a);
  
    $y=queryAct("SELECT * FROM referrals WHERE tail='$name'");
    if($y->rowCount()>=1){
        if($y1=$y->fetch()){
            $head=strtoupper($y1['head']);
            $posx=$y1['pos'];
            if($head==$aa && $posx==$Cid){
                  if($GLOBALS['limitCH']!=5){
                    $GLOBALS['limitCH']++;
                $ts=explode(" ",$jd)[0];
                if($Cid==0){$cId="A";}else{$cId="B";}
                echo '<tr class="text-truncate text-capitalize"><td>'.strtolower($memFname).'</td><td>+18,000</td><td>'.$cId.'-'.$ts.'</td></tr></br>';
                  }
            }else{
                if($GLOBALS['limitCH']!=5){
                    otHist1($y1['head'],$Cid,$a,$jd,$memFname);
                }
            }
        }
    }

 }

 function p2($a){
    $cc2=queryAct("SELECT * FROM clients WHERE id=$a");
    if($cc2->fetch(PDO::FETCH_ASSOC)["entry"]==5){
        return 1;
    }else{
        return 0;
    }
 }
 //201123
?>