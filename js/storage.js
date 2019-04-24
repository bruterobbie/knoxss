function storeData(data) {

        l = parseInt(localStorage.length) + 1;
	localStorage.setItem("item"+l, data);
}

function showData() {

  var html = '';

  for (var i = 0; i < localStorage.length; i++) {

    items = JSON.parse(localStorage.getItem(localStorage.key(i)));

    html += '<tr>';
    html += '<td data-th="Target" id="table_target">' + items.target + '</td>';
    html += '<td data-th="Result"><span id="poc" class="extra" onclick="javascript:' + items.result + '">PoC</span></td>';
    html += '<td data-th="Target">' + items.date + '</td>';
    html += '</tr>';

  }

  document.getElementById("records").innerHTML = html;

}

function delAllData() {

  l = localStorage.length;

  for (var i = 0; i <= l; i++) {

//  localStorage.removeItem(localStorage.key(i));

    localStorage.removeItem("item"+i);
  }

  document.getElementById("records").innerHTML = '';

}


function showTable() {

  var myTable = '<table class="rwd-table">\
  		<tr>\
    		<th>Target</th>\
    		<th>Result</th>\
    		<th>Date</th>\
  		</tr>\
  		<tbody id="records">\
  		</tbody>\
		</table>';

     document.location.href = '#log';
     document.getElementById("data-table").innerHTML = myTable;

     showData();

}

