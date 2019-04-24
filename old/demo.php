<?php
error_reporting(0); // Turn off all error reporting
?>
<!DOCTYPE html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>KNOXSS Demo</title>
<link rel="stylesheet" href="styles.css">
</head>
<body id="body_demo">
<h1 id="knoxss_demo">KNOXSS</h1>
<div id="div_target">
<p id="info">KNOXSS 0.8.7 <b class="error">beta</b></p>
<form action="" method="POST">
<input type="url" id="target" name="target" placeholder="Target Page">
<input type="submit" name="submit" id="submit_demo" value="Test">
<textarea type="text" class="extra" id="post" name="post" rows="1" cols="80" placeholder="POST data (optional)"></textarea>
<input type="radio" class="mode" id="demo" name="mode" checked>
<label for="demo">demo</label>
</form>
<br>
<?php

/*error_reporting(E_ALL);
ini_set('display_errors',1);*/

// ===== VARS ===== //

$probe = "KNOX1S2S3";
$xss = 0;
$tester_url = "http://localhost:1337";
$demo = "'\"><svg/onload=alert(1)>";
$input = $_REQUEST["target"];
$target = $input;
$die = "\n</div>\n<div class=\"div_footer\" id=\"div_footer\">\n<p class=\"copyright\" id=\"copyright\">
        © 2016 Brute Logic - All rights reserved.</p>\n</div>\n</body>\n</html>\n<!--" . htmlentities($input) . "-->";
$preflight = 0;
$post_data = $_REQUEST["post"];
$http_method = 0;

if(strpos($target,"#")){
   $fragment = preg_replace("/.*#/","#",$target);
} else { $fragment = "#KNOXSS"; }


// ===== FUNCTIONS ===== //

function test($url, $http_method, $data){

   global $preflight, $die, $abort;

   $optArray = array(
      CURLOPT_URL => $url,
      CURLOPT_POST => $http_method,
      CURLOPT_USERAGENT => $_SERVER["HTTP_USER_AGENT"],
      CURLOPT_TIMEOUT => 10,
      CURLOPT_FOLLOWLOCATION => 1,
      CURLOPT_RETURNTRANSFER => 1
   );
   $ch = curl_init();
   curl_setopt_array($ch, $optArray);
   if ($data) { curl_setopt($ch, CURLOPT_POSTFIELDS, $data); }

   $response = curl_exec($ch) or die("\n<br>\n<p id=\"info\" class=\"error\">ERROR: Aborted, network issues!" .
                                        "\n<br>Reason: " . curl_error($ch) . ".</p>" . $die);

   curl_close($ch);
   return $response;
}

function pwn($pwn){

   $pwn = str_replace("\\", "\\\\", str_ireplace("</script", "%3C/script", $pwn));
   $pwn = str_replace("'", "%27", str_replace("%25%75", "%u", $pwn));
   echo "\n<script>window.open('".$pwn."', '', 'top=90, left=260, width=900, height=600');</script>\n";
}

function set($demo, $tester_url, $target){

   global $demo, $fragment, $xss, $probe, $post_data, $send_data;

   $p = $demo;

   $xss_data = str_replace($probe, $p, $send_data);

   $target_test = "{\"target\":" . json_encode(str_replace($probe, $p, $target) . $fragment) .
			", \"data\":" . json_encode($xss_data) . "}";

   if(!$post_data){
      $target_open = str_replace($probe, $p, $target) . $fragment;
   } else {
      $target_open = post_poc($target, $xss_data);
   }

   $response = test($tester_url, 1, $target_test);
   if ($response === "TRUE\n"){
      $xss = 2;
      pwn($target_open);
      return $target_open;
   }
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

   global $post_data, $tester_url, $target, $probe, $send_data, $poc, $xss;

   $http_method = 1;
   $a = explode("&", $post_data);

   for ($i=0;$i<count($a);$i++){
      $a[$i] = $a[$i] . $probe;

      $send_data = implode("&", $a);

      if (preg_match("/$probe/i", test($target, $http_method, $send_data))) {
            $poc = set($demo, $tester_url, $target);
            if($poc){ break; }
            $xss = 1;
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

if ($input){

   echo "<p id=\"info\">TARGET: " . htmlentities($target) . "</p>";

   if (!$post_data) {
      $r = test($target);
   } else {
      $r = test($target, 1, 0, $post_data);
   }

   preg_match("/<noscript>.*javascript.*<\/noscript>/i", $r, $noscript);

   $preflight = 1;

   if (!$post_data) {

      $parts = parse_url($target);

      if($parts["query"]){
         $a = explode("&", $parts["query"]);
      } else {
         $a = array("/");
      }

      for ($i=0;$i<count($a);$i++){
         $a[$i] = $a[$i] . $probe;
         $target = preg_replace("/\?.*/", "?", $target).implode("&",$a);

         if (preg_match("/$probe/i", test($target, $http_method))) {
            $poc = set($demo, $tester_url, $target);
            if($poc){ break; }
            $xss = 1;
         }
         $xss = 1;
         $a = explode("&", $parts["query"]);
      }
   } else { $xss = test_post(); }

   if ($xss != 0){
      if ($xss == 1) {
         echo "<p id=\"info\">No XSS found for <i>" . htmlentities($input) . "</i></p>\n";
         if ($noscript) {
            echo "<p id=\"info\">* Main content possibly generated by javascript (unable to handle).</i></p>\n";
         }
      } else {
         echo "<p id=\"info\">XSS found for <b>" . htmlentities($input) . "</b><p>\n";
         $poc = htmlentities($poc);
         echo "<p id=\"info\" class=\"found\"><a href=\"" . $poc . "\" target=\"_blank\">" . $poc  . "</a></p>\n";
      }
   }

} else {

   if (!$_REQUEST["submit"]){
      echo "<p id=\"info\">Enter a target to test (or hit the button).</p>\n";
   } else {
      echo "<p id=\"info\">Loaded default target for testing KNOXSS capabilities.</p>\n";
      echo "<p id=\"info\">ATTENTION: demo version doesn't work on all cases!</p>\n";
      $auto_complete = xss_page();
      echo "<script>\ndocument.getElementById('target').value='" . $auto_complete[0] .
           "';\ndocument.getElementById('post').value='" . $auto_complete[1] . "';\n</script>\n";
   }
}
?>
</div>
<div class="div_footer" id="div_footer_demo">
<p class="copyright" id="copyright_demo">© 2016 Brute Logic - All rights reserved.</p>
</div>
</body>
</html>
<!--<?=htmlentities($input);?>-->
