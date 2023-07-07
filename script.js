function contains2words(str) {
    return (/\s/.test(str) && !(/\s$/.test(str)));
}

function endswithspace(str) {
    return /\s$/.test(str);
}

$(document).ready(function(){
    $("#sugg-box li").click(function(){
        id = $(this).attr("id");
        console.log(id);
        var temp_text = $(this).text();
        console.log(temp_text);
    });
    /* with every character entered in the search text box show the 5 suggestions
    1. get the text entered into the text box
    2. make the call to the link: http://localhost:8983/solr/ny_times_core/suggest?q=califo ; with the query q from step 1
    3. loop over the results and show them in a clickable dropdown
    4. once clicked, the text should enter the text box and click on search
    */
    $("#q").on("input", function(){
        var q_words=[];
				var og_query = $("#q").val();
                console.log("query: ", og_query);
				q_words[0] = og_query;		
				if (contains2words(og_query)){
					var q_words = og_query.split(" ");
					console.log(q_words);
				}	
				query = q_words[q_words.length-1];
				pre_query = q_words.slice(0,q_words.length-1).join(" ");

				$("#q").autocomplete({
				    minLength: 0,
				    source: function (request, response) {

						$.ajax({
						    type: "POST",
						    dataType: 'jsonp',
						    jsonp: 'json.wrf',
						    url: "http://localhost:8983/solr/ny_times_core/suggest",
						    data:'q='+query,
						    success: function(result){
						    var sugg = result["suggest"]["suggest"][query]["suggestions"];
						    var suggestions = []
						    for (var i = 0; i < sugg.length; i++){
								if (contains2words(og_query)){
							 		suggestions[i] = pre_query + " " + sugg[i]["term"];
								}else{
									suggestions[i] = sugg[i]["term"];
								}
								if (endswithspace(og_query)){
							 		suggestions[i] = suggestions[i] + " ";
								}
						    }
						    response(suggestions)
						    },
						    close: function() {
								this.value = '';
							},
							dataType: 'jsonp',
							jsonp: 'json.wrf'
						  });
					}

			      });
    });
});


