<html>
<head>
<meta charset="UTF-8">

	<script src="js/jquery.min.js"></script>
<script>
function getParsedData(){
	$('#pointsTable').html('<h2>Crunching the Data. Please Wait..!</h2>');
	    $.ajax({
	url: "crawl_data.php",
	data:$( "#crawlForm" ).serialize(),
	method: 'post',
	type: 'json',
	success: function (data) {
		$("#pointsTable").html('<h2>' + data + '</h2>');
	        }
	    });
}
</script>
</head>
<body >
<form action='crawl_data.php' method=post id='crawlForm'>
<input type="text" name="q">
<br>
<input type=button value=search onclick='getParsedData();' />
<br>
</form>
<div id='pointsTable' style='width:70%; margin:auto;'></div>
</body>
</html>
