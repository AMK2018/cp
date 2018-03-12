
var report = null;

function init(){
	report = localStorage.getItem("ID-Rep");

	if(report != null){
		var id = report.split('-')[1];

		getLocation(id);

		var user = localStorage.getItem(report).split(',');

		$(".username").text(user[1]);

		getPresentations(id);

		getPictures(id);

		$(window).on("beforeunload", function() { 
			//localStorage.clear();    
		});


		$(".dwdPDF a").on("click", function(){
			var path = "assets/fpdf/pdf.php";

			var result = path + "?repid=" + id;

			$(".dwdPDF a").attr("href", result);
		});
	}
}




