<html>
    <body>
    <script>
    function download_csv(csv, filename) {
    var csvFile;
    var downloadLink;

    // CSV FILE
    csvFile = new Blob([csv], {type: "text/csv"});

    // Download link
    downloadLink = document.createElement("a");

    // File name
    downloadLink.download = filename;

    // We have to create a link to the file
    downloadLink.href = window.URL.createObjectURL(csvFile);

    // Make sure that the link is not displayed
    downloadLink.style.display = "none";

    // Add the link to your DOM
    document.body.appendChild(downloadLink);

    // Lanzamos
    downloadLink.click();
}

function export_table_to_csv(html, filename) {
	var csv = [];
	var rows = document.querySelectorAll("table tr");
	
    for (var i = 0; i < rows.length; i++) {
		var row = [], cols = rows[i].querySelectorAll("td, th");
		
        for (var j = 0; j < cols.length; j++) 
            row.push(cols[j].innerText);
        
		csv.push(row.join(","));		
	}

    // Download CSV
    download_csv(csv.join("\n"), filename);
}

</script>
    <?php
header("Access-Control-Allow-Origin: *");
date_default_timezone_set("Asia/Manila");
require_once __DIR__ . "/config.php";
function queryAct($a)
{
    $db = new Connect;
    $data = $db->prepare($a);
    $data->execute();
    return $data;
}

function IdtoName($a){
    $x = queryAct("SELECT addrs,CONCAT(first_name,' ',last_name) as fullname FROM clients WHERE id=$a");
    if($x->rowCount()>=1){
        $x1 = $x->fetch();
        $x2=$x1["fullname"];
         return $x2;
    }
}
function invCsv($a){
    if($a=="products"){
        $invDir='products';
        $name='GTadmin';
    }else{
        $invDir=$a;
        $name=strtolower(IdtoName($invDir));
    }
    
    echo '<script>var name="'.$name.'"</script>';
    $x1=@queryAct("SELECT * FROM `$invDir` WHERE active=0 ORDER BY categ ASC");
    if($x1->rowCount()>=1){
        echo '<table hidden>';
        echo '<thead class="text-start"><tr>
                <th>Item</th>
                <th>Category</th>
                <th>Unit Price</th>
                <th>Qty</th>
                <th>Points</th>
            </tr></thead>';
        echo '<tbody>';
        while($x2=$x1->fetch()){
        echo '<tr>';
        echo '<td>'.strtolower($x2['item']).'</td>';
        echo '<td>'.strtolower($x2['categ']).'</td>';
        echo '<td>'.$x2['price'].'</td>';
        echo '<td>'.$x2['qty'].'</td>';
        echo '<td>'.$x2['points'].'</td>';
        echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';
        //echo '<button> Download '.$name.' Store Csv</button>';
        echo '<script>
        var html = document.querySelector("table").outerHTML;
        export_table_to_csv(html, name+".csv");
        </script><h1>Download Completed !</h1>';


    }
  
}
if (isset( $_GET['id'])){
    invCsv($_GET['id']);
}

?>


    </body>
</html>

