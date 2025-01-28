<?php
header("Access-Control-Allow-Origin: *");
date_default_timezone_set("Asia/Manila");
session_start();
require_once __DIR__ . "/config.php";
$db = new Connect;
$Dcash=1200;//500;
$Dpoints=70000;//18000;

function queryAct($a)
{
    $data =$GLOBALS['db']->prepare($a);
    $data->execute();
    return $data;  
}
if (isset($_POST['item'])) {go_products($_POST['dirF']);}
if (isset($_POST['outletPic'])) {go_changeOPic($_POST['dirF']);}
if(isset($_POST['register'])){
    if($_POST['register']=='delete'){go_delete($_POST['id'],$_POST['dirF']);}
    if($_POST['register']=='edit'){go_edit($_POST['id'],$_POST['dirF']);}
    if($_POST['register']=='view'){go_view($_POST['id'],$_POST['view'],$_POST['dirF']);}
    if($_POST['register']=='ShowItem'){go_ShowItem($_POST['category'],$_POST['dirF']);}
    if($_POST['register']=='showOutlets'){outlets();}
    if($_POST['register']=='actOutlet'){actOutlet($_POST['id']);}
}

if (isset($_GET['msg']) and $_GET['msg'] != null){
    $Gdat = $_GET['msg'];
    switch ($Gdat)
    {

        case "dlines":
            if (isset($_GET['id']) and $_GET['id'] != null)
            {
                dlines($_GET['id']);
            }
        break;
        
        case "categ":
            $invDir=$_GET['dirF'];
            categX($invDir);
        break;
    
    }
    }

function disp($a,$b){
    $invDir=$b;
    $x1=queryAct("SELECT * FROM `$invDir` WHERE active=0 $a ORDER BY categ ASC");
/*     if($x1->rowCount()==0){
        $x1=queryAct("SELECT * FROM products WHERE active=0 ORDER BY categ ASC");
    } */
    while($x2=$x1->fetch()){
    if($x2['view']==1){
            $vhide1 = "none";
            $vhide2 = "block";
    }else{
            $vhide1 = "block";
            $vhide2 = "none";
    }
    echo '<tr>';
    //echo '<td class="text-start" style="width: 10%;"><img src="'.$x2['image'].'" width="50px" /></td>';
    //echo '<td>'.$x2['id'].'</td>';
    echo '<td><button id="viewOff" class="btn btn-dark btn-sm" type="button" style="margin-right: 5px;display:'.$vhide1.';" onClick="go_view('.$x2['id'].',1)"><svg class="bi bi-eye-slash-fill" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" viewBox="0 0 16 16">';
    echo '<path d="m10.79 12.912-1.614-1.615a3.5 3.5 0 0 1-4.474-4.474l-2.06-2.06C.938 6.278 0 8 0 8s3 5.5 8 5.5a7.029 7.029 0 0 0 2.79-.588zM5.21 3.088A7.028 7.028 0 0 1 8 2.5c5 0 8 5.5 8 5.5s-.939 1.721-2.641 3.238l-2.062-2.062a3.5 3.5 0 0 0-4.474-4.474L5.21 3.089z"></path>';
    echo '<path d="M5.525 7.646a2.5 2.5 0 0 0 2.829 2.829l-2.83-2.829zm4.95.708-2.829-2.83a2.5 2.5 0 0 1 2.829 2.829zm3.171 6-12-12 .708-.708 12 12-.708.708z"></path>';
    echo '</svg> Off</button><button id="viewOn" class="btn btn-success btn-sm align-items-center" type="button" style="margin-right: 5px;display:'.$vhide2.';" onClick="go_view('.$x2['id'].',0)"><svg class="bi bi-eye-fill" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" viewBox="0 0 16 16">';
    echo '<path d="M10.5 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0z"></path>';
    echo '<path d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8zm8 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7z"></path>';
    echo '</svg> On</button></td>';
    echo '<td>'.strtolower($x2['item']).'</td>';
    echo '<td hidden>'.strtolower($x2['categ']).'</td>';
    echo '<td>'.$x2['price'].'</td>';
    echo '<td>'.$x2['qty'].'</td>';
    echo '<td>'.$x2['points'].'</td>';
    echo '<td class="text-end">';
    echo '<button class="btn btn-warning btn-sm" type="button" style="margin-right: 5px;" onClick="go_edit('.$x2['id'].')"><svg class="bi bi-tag" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" viewBox="0 0 16 16">';
    echo '<path d="M6 4.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0zm-1 0a.5.5 0 1 0-1 0 .5.5 0 0 0 1 0z"></path>';
    echo '<path d="M2 1h4.586a1 1 0 0 1 .707.293l7 7a1 1 0 0 1 0 1.414l-4.586 4.586a1 1 0 0 1-1.414 0l-7-7A1 1 0 0 1 1 6.586V2a1 1 0 0 1 1-1zm0 5.586 7 7L13.586 9l-7-7H2v4.586z"></path>';
    echo '</svg> Edit</button>';
    
    echo '<button class="btn btn-danger btn-sm" type="button" onClick="go_delete('.$x2['id'].')"><svg class="bi bi-eraser" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" viewBox="0 0 16 16">';
    echo '<path d="M8.086 2.207a2 2 0 0 1 2.828 0l3.879 3.879a2 2 0 0 1 0 2.828l-5.5 5.5A2 2 0 0 1 7.879 15H5.12a2 2 0 0 1-1.414-.586l-2.5-2.5a2 2 0 0 1 0-2.828l6.879-6.879zm2.121.707a1 1 0 0 0-1.414 0L4.16 7.547l5.293 5.293 4.633-4.633a1 1 0 0 0 0-1.414l-3.879-3.879zM8.746 13.547 3.453 8.254 1.914 9.793a1 1 0 0 0 0 1.414l2.5 2.5a1 1 0 0 0 .707.293H7.88a1 1 0 0 0 .707-.293l.16-.16z"></path>';
    echo '</svg> Delete</button></td>';
    echo '</tr>';
    
    }
}
function go_products($dirA)
{
    if($dirA=="products"){
        $dirB="uploads";
    }else{
        $dirB=$dirA;
    }

    $targetFile =$dirB."/".$_FILES["fileToUpload"]["name"];
    move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $targetFile);
    $a = $_POST['item'];
    $b = $_POST['categ'];
    $c = $_POST['qty'];
    $d = $_POST['price'];
    $e = $_POST['points'];
    $f =$targetFile;

    if($_POST['id']<>0){
        $id = $_POST['id'];
        if($_FILES["fileToUpload"]["name"]==""){
            queryAct("UPDATE `$dirA` SET `item`='$a',`categ`='$b',`qty`=$c,`price`=$d,`points`=$e WHERE id=$id");
            }else{
                queryAct("UPDATE `$dirA` SET `item`='$a',`categ`='$b',`qty`=$c,`price`=$d,`points`=$e,`imageDir`='$f' WHERE id=$id");
            }
    
       
    }else{
        queryAct("INSERT INTO `$dirA` (`item`,`categ`,`qty`,`price`,`points`,`imageDir`) VALUES ('$a','$b',$c,$d,$e,'$f')");
    }

  
}


function go_delete($x,$y){
    queryAct("DELETE FROM `$y` WHERE `$y`.`id` =$x");
}

function go_edit($x,$y){
    $x1=queryAct("SELECT * FROM `$y` WHERE `$y`.`id` =$x LIMIT 1");
    $x2 = $x1->fetch();
    $arrayD = [
"item"=>$x2['item'],
"categ"=>$x2['categ'],
"qty"=>$x2['qty'],
"price"=>$x2['price'],
"points"=>$x2['points'],
"image"=>$x2['imageDir']
    ];

    echo json_encode($arrayD);
}

function go_view($a,$b,$c){
    queryAct("UPDATE `$c` SET `view`=$b WHERE id=$a");
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

    categX($b);
}

function outlets(){
    $opic=showOutPic(1);
    echo '<div class="col" style="margin-bottom: 5px;" onClick="openStoreItems(1,\'Admin\',\''.$opic.'\')">';
    echo '<div class="card border rounded-0" style="background: rgb(255,255,255);"><img class="card-img-top w-100 d-block" src="/'.$opic.'" />';
    echo '<div class="card-body">';
    echo '<h6 class="text-truncate text-capitalize card-title">GTgrocers</h6>';
    echo '<span class="text-truncate text-capitalize card-text" style="font-size: 10px;">09*********</span>';
    echo '<p class="text-truncate text-capitalize card-text" style="font-size: 10px;">Admin</p>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
    $data =queryAct("SELECT * FROM `outlet` WHERE `admin`=0");
    if($data->rowCount()>=1){
        while ($outData = $data->fetch(PDO::FETCH_ASSOC))
        {
            $d1=queryAct("SELECT id,addrs,mobile,CONCAT(first_name,' ',last_name) as fname FROM `clients` WHERE id=".$outData['client']);
            if($d1->rowCount()>=1){
                $d2=$d1->fetch();
                $opic=showOutPic($d2['id']);
                echo '<div class="col" style="margin-bottom: 5px;" onClick="openStoreItems('.$d2['id'].',\''.strtolower($d2['fname']).'\',\''.$opic.'\')">';
                echo '<div class="card border rounded-0" style="background: rgb(255,255,255);"><img class="card-img-top w-100 d-block" src="/'.$opic.'" />';
                echo '<div class="card-body">';
                echo '<h6 class="text-truncate text-capitalize card-title">'.strtolower($d2['fname']).'</h6>';
                echo '<span class="text-truncate text-capitalize card-text" style="font-size: 10px;">'.strtolower($d2['mobile']).'</span>';
                echo '<p class="text-truncate text-capitalize card-text" style="font-size: 10px;">'.strtolower($d2['addrs']).'</p>';
                echo '</div>';
                echo '</div>';
                echo '</div>';
                
            }

        }
    }
}
function dlines($a)
{

        $data = queryAct("SELECT * FROM clients WHERE CONCAT(first_name,' ',last_name) LIKE '%$a%' AND outlet=0");
        while ($outData = $data->fetch(PDO::FETCH_ASSOC))
        {
            echo "<tr class='dltr' onClick='selRef(\"" . $outData['first_name'] . " " . $outData['last_name'] . "\"," . $outData['id'] . ")'><td>" . $outData['id'] . "</td><td>" . $outData['first_name'] . " " . $outData['last_name'] . "</td></tr>";
        }
      
    

}
function actOutlet($a)
{
    $pic='assets/img/s3.jpg';
    if(queryAct("INSERT INTO outlet (`client`,`pic`) VALUES ($a,'$pic')")){
        if(queryAct("UPDATE clients SET `outlet`=1 WHERE id=$a")){
           if(queryAct("CREATE TABLE `$a` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `item` char(50) NOT NULL,
            `qty` int(11) NOT NULL,
            `points` decimal(11,2) NOT NULL,
            `categ` varchar(100) NOT NULL,
            `price` decimal(11,2) NOT NULL,
            `imageDir` varchar(100) NOT NULL,
            `active` double NOT NULL,
            `timestamp` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
            `view` int(11) NOT NULL,
            PRIMARY KEY (`id`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
          ")){
                    ///
                    $dirName = $a;
                    if (!is_dir($dirName)) {
                        if (mkdir($dirName)) {
                           ///
                           $x=queryAct("SELECT * FROM clients WHERE id=$dirName LIMIT 1");
                    
                           if($x->rowCount()>=1){
                               $x1=$x->fetch();
                               $a=$x1['sponsor'];
                               queryAct("UPDATE `earnings` SET `cash`=`cash`+".$GLOBALS["Dcash"].",`oref1200`=`oref1200`+1 WHERE `client`=$a");
                               distPoints($dirName,$GLOBALS["Dpoints"],$dirName);
                                   
                           }
                           ///
                            echo "Done";
                        } else {
                            echo "Error creating directory.";
                        }
                    } else {
                        echo "Directory already exists.";
                    }
                    ///
              }

        }
     


    }
    

}

function distPoints($client,$points,$otltag){
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
                            if(queryAct("UPDATE `earnings` SET  $posX = $posX+$points,otltag=CONCAT(otltag,':',$otltag) WHERE `client`=$z2")){
                                distPoints($z2, $points,$otltag);
                            }
                            
                                  

                        
 
                    }

            }
        }

}

function go_changeOPic($a){
    if($a=="products"){
        $b=1;
        $c="uploads";
    }else{
        $b=$a;
        $c=$a;
    }

    $targetFile = $c."/".$_FILES["fileToUpload"]["name"];
    move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $targetFile);
    queryAct("UPDATE outlet SET `pic`='$targetFile' WHERE client=$b");

}
function showOutPic($a){
    $x=queryAct("SELECT pic FROM outlet WHERE client=$a");
    if($x->rowCount()>=1){
        $x2=$x->fetch();
        return $x2['pic'];
    }
}

function categX($invDir){
    $x1=queryAct("SELECT * FROM  `$invDir` WHERE `active`=0 GROUP BY categ ORDER BY `categ` ASC");
    if($x1->rowCount()>=1){
        while($x2=$x1->fetch()){
            echo '<script>category("' .ucfirst($x2['categ']) . '")</script>';
        }

    }else{
        echo '<script>clearOptions()</script>';
    }
}
///010723
?>