var server = require('webserver').create();
var agent = 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:50.0) Gecko/20100101 Firefox/48.0';
console.log('Server running at http://0.0.0.0:1337');

server.listen('1337', function(req, res) {

   var page = require('webpage').create();
   var xss = false;
   var myJSON = JSON.parse(req.postRaw);
   var target = myJSON.target.replace("%25%75", "%u");
   var myHeaders = JSON.stringify(myJSON.headers);
   var myData = myJSON.data;
   var myMethod = myData ? "POST" : "GET";

   if (target) {

       page.resourceTimeout = 10000;
       page.settings.loadImages = 0;
       page.settings.userAgent = agent;

       page.onAlert = function (msg) {
           if (msg == 1) {
              xss = true;
           }
       };

       page.onConfirm = function (msg) {
           if (msg == 1) {
              xss = true;
           }
       };

       page.customHeaders = myJSON.headers;

       page.onLoadStarted = function() {
           page.customHeaders = {};
       };

       page.open(target, myMethod, myData, function() {
           page.sendEvent('mousemove');
	   res.statusCode = 200;
           if (xss) {
              res.write('TRUE\n');
           } else {
              res.write('FALSE\n');
           }
           page.close();
           res.close();
       });

       page.onLongRunningScript = function() {
           page.stopJavaScript();
       };

       page.onResourceTimeout = function() {
           res.write('TIMEOUT\n');
           page.close();
           res.close();;
       };

//       page.onError = function() {}

   } else {
       res.statusCode = 200;
       res.write('No data.\n');
       res.close();
   }

});
