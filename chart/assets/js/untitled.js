var chart;
var referralData;
var $server;
function drawChart() {
  	var data = new google.visualization.DataTable();
  	data.addColumn('string', 'Name');
  	data.addColumn('string', 'Manager');
  	//data.addColumn('string', 'ToolTip');

  	// For each orgchart box, provide the name, manager, and tooltip to show.
  	data.addRows(referralData);

  	// Create the chart.
  	chart = new google.visualization.OrgChart(document.getElementById('chart_div'));
  	// Draw the chart, setting the allowHtml option to true for the tooltips.
  	chart.draw(data, {
  		'allowHtml': true
  	});
	

  }

function drawC() {

	google.charts.load('current', {
		packages: ["orgchart"]
	});
	google.charts.setOnLoadCallback(drawChart);

}
function cmdl(msg,id){
	
		$.ajax({
		type: "GET",
		url:"https://"+$('#server').val()+"/ox.php",
		data: {msg,id},
		success: function(data) {
			referralData=JSON.parse(data);
		drawC();
		}
	});
}
$('#searchCbtn').click(function(){
	cmdl("gen_tree",$('#searchCinput').val());
});