<?php
header("Access-Control-Allow-Origin: *");
date_default_timezone_set("Asia/Manila");
require_once __DIR__ . "/config.php";
$db = new Connect;
function queryAct($a)
{
    $data = $GLOBALS["db"]->prepare($a);
    $data->execute();
    return $data;
}

if(isset($_GET["msg"])){

    $Gdat = $_GET['msg'];
    switch ($Gdat)
    {

        case "view":
            displayCart($_GET["orderStat"],$_GET["cartStat"],$_GET["opt"],$_GET["stat"],$_GET["store"],$_GET['pen']);
            break;

        case "done":
            done($_GET["orderid"],$_GET["client"]);
            break;

        case "categ":
            $invDir=$_GET['dirF'];
            $x1=queryAct("SELECT * FROM  `$invDir` WHERE `active`=0 GROUP BY categ ORDER BY `categ` ASC");
            if($x1->rowCount()>=1){
                while($x2=$x1->fetch()){
                    echo '<script>category("' .ucfirst($x2['categ']) . '")</script>';
                }
        
            }else{
                echo '<script>clearOptions()</script>';
            }
        break;
    }
}
if (isset($_POST["register"])){
   
    if($_POST['register']=='login'){go_login();}
    if($_POST['register']=='ShowItem'){go_ShowItem($_POST['category'],$_POST['dirF']);}
}

function displayCart($ax,$ay,$opt,$status2,$store,$pen){

    $x=queryAct("SELECT * FROM orders WHERE `status`=$ax AND `store`=$store AND pending=$pen ORDER BY id DESC");
   
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
            echo '<caption class="text-center "><button class="btn btn-success" type="button" style="width: 80%;" onClick="done('. $x1["id"].','. $x1["clientid"].')" '.$status2.'>Confirm</button></caption>';
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


function go_login()
{
    $first_name = $_POST["u_id"];
    $u_pass = $_POST["u_pass"];

    if ($first_name and $u_pass != null)
    {
        $data = queryAct("SELECT * FROM clients WHERE first_name='$first_name' AND pass='$u_pass' LIMIT 1");
        if ($data->rowCount()>0){
            $fetch = $data->fetch(PDO::FETCH_ASSOC);
            echo $fetch["id"];
        }

    }
}

function done($orderid,$client){
    if(queryAct("UPDATE `orders` SET `pending`=1  WHERE id=$orderid AND clientid=$client")){
            if(queryAct("UPDATE `cart` SET `status`=2,orderid=$orderid WHERE client=$client AND orderid=0")){

            }
    }
    }


    function go_ShowItem($a,$b){

        switch($a){
            case "All":
                disp('',$b);
                break;
            case "Category":
                disp('',$b);
                break;
            default:
                disp('AND categ="'.$a.'"',$b);
                break;
        }
    }

    function disp($a,$b){
        $invDir=$b;
        $x1=queryAct("SELECT * FROM `$invDir` WHERE active=0 AND view=1 $a ORDER BY categ ASC");
        $ie=0;
        while($x2=$x1->fetch()){
            $ie++;
        if($x2['view']==1){
                $vhide1 = "none";
                $vhide2 = "block";
        }else{
                $vhide1 = "block";
                $vhide2 = "none";
        }
        echo '<tr>';
/*         echo '<td><button id="viewOff" class="btn btn-dark btn-sm" type="button" style="margin-right: 5px;display:'.$vhide1.';" onClick="go_view('.$x2['id'].',1)"><svg class="bi bi-eye-slash-fill" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" viewBox="0 0 16 16">';
        echo '<path d="m10.79 12.912-1.614-1.615a3.5 3.5 0 0 1-4.474-4.474l-2.06-2.06C.938 6.278 0 8 0 8s3 5.5 8 5.5a7.029 7.029 0 0 0 2.79-.588zM5.21 3.088A7.028 7.028 0 0 1 8 2.5c5 0 8 5.5 8 5.5s-.939 1.721-2.641 3.238l-2.062-2.062a3.5 3.5 0 0 0-4.474-4.474L5.21 3.089z"></path>';
        echo '<path d="M5.525 7.646a2.5 2.5 0 0 0 2.829 2.829l-2.83-2.829zm4.95.708-2.829-2.83a2.5 2.5 0 0 1 2.829 2.829zm3.171 6-12-12 .708-.708 12 12-.708.708z"></path>';
        echo '</svg> Off</button><button id="viewOn" class="btn btn-success btn-sm align-items-center" type="button" style="margin-right: 5px;display:'.$vhide2.';" onClick="go_view('.$x2['id'].',0)"><svg class="bi bi-eye-fill" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" viewBox="0 0 16 16">';
        echo '<path d="M10.5 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0z"></path>';
        echo '<path d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8zm8 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7z"></path>';
        echo '</svg> On</button></td>'; */
        echo '<td>'.$ie.'</td>';
        echo '<td>'.strtolower($x2['item']).'</td>';
        echo '<td hidden>'.strtolower($x2['categ']).'</td>';
        echo '<td>'.$x2['price'].'</td>';
        echo '<td>'.$x2['qty'].'</td>';
        echo '<td>'.$x2['points'].'</td>';
/*         echo '<td class="text-end">';
        echo '<button class="btn btn-warning btn-sm" type="button" style="margin-right: 5px;" onClick="go_edit('.$x2['id'].')"><svg class="bi bi-tag" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" viewBox="0 0 16 16">';
        echo '<path d="M6 4.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0zm-1 0a.5.5 0 1 0-1 0 .5.5 0 0 0 1 0z"></path>';
        echo '<path d="M2 1h4.586a1 1 0 0 1 .707.293l7 7a1 1 0 0 1 0 1.414l-4.586 4.586a1 1 0 0 1-1.414 0l-7-7A1 1 0 0 1 1 6.586V2a1 1 0 0 1 1-1zm0 5.586 7 7L13.586 9l-7-7H2v4.586z"></path>';
        echo '</svg> Edit</button>';
        
        echo '<button class="btn btn-danger btn-sm" type="button" onClick="go_delete('.$x2['id'].')"><svg class="bi bi-eraser" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" viewBox="0 0 16 16">';
        echo '<path d="M8.086 2.207a2 2 0 0 1 2.828 0l3.879 3.879a2 2 0 0 1 0 2.828l-5.5 5.5A2 2 0 0 1 7.879 15H5.12a2 2 0 0 1-1.414-.586l-2.5-2.5a2 2 0 0 1 0-2.828l6.879-6.879zm2.121.707a1 1 0 0 0-1.414 0L4.16 7.547l5.293 5.293 4.633-4.633a1 1 0 0 0 0-1.414l-3.879-3.879zM8.746 13.547 3.453 8.254 1.914 9.793a1 1 0 0 0 0 1.414l2.5 2.5a1 1 0 0 0 .707.293H7.88a1 1 0 0 0 .707-.293l.16-.16z"></path>';
        echo '</svg> Delete</button></td>'; */
        echo '</tr>';
        
        }
    }

?>