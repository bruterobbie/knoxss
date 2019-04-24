function loading(){

        document.getElementById('x').outerHTML='<div class="shot"></div>';
	document.getElementById('loading').innerHTML = '\
	<div class="sk-circle1 sk-circle"></div>\
  	<div class="sk-circle2 sk-circle"></div>\
  	<div class="sk-circle3 sk-circle"></div>\
  	<div class="sk-circle4 sk-circle"></div>\
  	<div class="sk-circle5 sk-circle"></div>\
  	<div class="sk-circle6 sk-circle"></div>\
  	<div class="sk-circle7 sk-circle"></div>\
  	<div class="sk-circle8 sk-circle"></div>\
  	<div class="sk-circle9 sk-circle"></div>\
  	<div class="sk-circle10 sk-circle"></div>\
  	<div class="sk-circle11 sk-circle"></div>\
  	<div class="sk-circle12 sk-circle"></div>';

}


function moveResults(info1, info2, info3){

        document.getElementById('title').outerHTML='<div class="move_title">Results</div>';
	document.getElementById('line').outerHTML='<div class="move_line"></div>';

        document.getElementById('results').outerHTML='\
	<div class="fade_info">\
	<p id="fade_info_1">' + info1 + '</p>\
	<p id="fade_info_2">' + info2 + '</p>\
	<p id="fade_info_3">' + info3 + '</p>\
	</div>';

}

function moveBack(){

	document.getElementById('back').outerHTML='<div id="back" class="move_back"></div>';
	document.location.href = '#back';

}

