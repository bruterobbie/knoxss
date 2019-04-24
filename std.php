<!DOCTYPE html>
  <head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KNOXSS Standard</title>
    <link rel="stylesheet" href="styles/index.css">
    <link rel="stylesheet" href="styles/spinner.css">
    <link rel="stylesheet" href="styles/background.css"> 
    <link rel="stylesheet" href="styles/move.css">
    <link rel="stylesheet" href="styles/modal.css">
    <link rel="stylesheet" href="styles/lato.css">
    <link rel="stylesheet" href="styles/menu.css">
    <link rel="stylesheet" href="styles/table.css">
    <link rel="stylesheet" href="styles/modal2.css">
  </head>
  <body id="std">
  <ul id="menu">
    <li>
      <a href="#">Menu</a>
      <ul>
        <li><a href="/?page_id=252">My Profile</a></li>
        <li><a href="/?s2member_file_download=knoxss_std_extension-0.5.3-an%2Bfx.xpi">Browser Addon</a></li>
        <li><a href="#">Results Log</a></li>
        <li><a href="#">Plan Upgrade</a></li>
        <li><a href="/old/std">Old Interface</a></li>
      </ul>
    </li>
  </ul>
  <a href="#" class="tmodal" id="log" aria-hidden="true">
  </a>
  <div class="tmodal-dialog">
    <div class="tmodal-header">
      <b class="header">KNOXSS Log</b>
      <a href="#" class="btn-close" aria-hidden="true">×</a>
    </div>
    <div class="tmodal-body">
      <div id="data-table"></div>
    </div>
    <div class="tmodal-footer">
      <a href="#" class="btn" onclick="delAllData()">Clear</a>
    </div>
  </div>
<?php
  if (!$_REQUEST["target"]) {
?>
  <div id="x">
  </div>
  <div id="loading" class="sk-fading-circle">
  </div>
  <script>
  function showModal(){
        var modal = document.getElementById('myModal');
        var mspan = document.getElementsByClassName("close")[0];

        modal.style.display = "block";

        mspan.onclick = function() {
                modal.style.display = "none";
        }

        window.onclick = function(event) {
                if (event.target == modal) {
                        modal.style.display = "none";
                }
        }
  };
  </script>
  <form action="" method="post" onsubmit="loading()">
    <div id="myModal" class="modal">
      <div class="modal-content">
        <div class="modal-header">
          <span class="close">&times;</span>
          <p class="header">Extra</p>
        </div>
        <div class="modal-body">
          <textarea id="extra_data" name="post" autocomplete="off" placeholder="POST Data"></textarea>
          <textarea id="extra_data" name="auth" autocomplete="off" placeholder="Auth Data"></textarea>
          <br>
        </div>
        <div class="modal-footer">
          <br>
          <p class="description">Provide POST data and/or HTTP authentication headers.</p>
        </div>
      </div>
    </div>
    <div class="header">
      <p>KNOXSS</p>
    </div>
    <div class="description">
      <p>Standard Edition - beta v1.0.0</p>
    </div>
    <div class="input">
      <input type="url" class="button" id="target" name="target" autocomplete="off" placeholder="TARGET PAGE">
      <input type="submit" class="button" id="submit" name="submit" value="TEST">
    </div>
    <br>
    <span class="extra" onclick="showModal()">Extra Data</span>
  </form>
  <div class="description">
    <p id="copyright">© 2017 Brute Logic - All rights reserved.</p>
  </div>
  </body>
  <script src="js/functions.js"></script>
  <script src="js/storage.js"></script>
</html>
<?php
  } else {
?>
  <script src="js/storage.js"></script>
  <script src="js/functions.js"></script>
  <div id="title"></div>
  <div id="line"></div>
  <div id="results"></div>
  <div id="back"></div>
  <div class="description">
    <button id="return" class="button" onclick="location.href='';">BACK</button>
    <p id="copyright">© <?=date("Y");?> Brute Logic - All rights reserved.</p>
  </div>
  </body>
</html>
<?php
/*error_reporting(E_ALL);
ini_set('display_errors',1);*/
error_reporting(0); // Turn off all error reporting
// ===== VARS ===== //

$probe = "KNOX1S2S3";
$xss = 0;
$tester_url = "http://localhost:1337";
$demo = "<!'/*!\"/*!\'/*\\\"/*--!></Title/</Script/><Input/Autofocus/%0D*/Onfocus=confirm`1`//><svg>";
$input = $_REQUEST["target"];
$etc = (strlen($input) >= 89) ? " ..." : "";
$target = $input;
$die = "\n<!--" . htmlentities($input) . "-->";
$auth = $_REQUEST["auth"];
$httpHeaders = explode("\r\n", $auth);
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
      CURLOPT_TIMEOUT => 20,
      CURLOPT_FOLLOWLOCATION => 0,
      CURLOPT_RETURNTRANSFER => 1,
	  CURLOPT_SSL_VERIFYPEER => 0,
	  CURLOPT_SSL_VERIFYHOST => 0
   );
   $ch = curl_init();
   curl_setopt_array($ch, $optArray);
   if ($data) { curl_setopt($ch, CURLOPT_POSTFIELDS, $data); }

   $response = curl_exec($ch) or die("\n<br>\n<p id=\"fade_info_2\" class=\"error\">ERROR: Aborted, network issues!" . 
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
	 return 1;
   } else {

        $r = test(str_replace($probe, "'\"".$probe, $url), 0, 1);
        preg_match_all("/$probe/i", $r, $matches);

        if ($matches[0]) {
           return 1;
        }

   }

}

function pwn($pwn) {

   global $open_poc;

   $pwn = str_replace("\\", "\\\\", str_ireplace("</script", "%3C/script", $pwn));
   $pwn = str_replace("'", "%27", str_replace("%25%75", "%u", $pwn));
   $open_poc = "window.open('". htmlentities($pwn, ENT_QUOTES) ."');";
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

   $localhost = ["localhost", "127.0", "192.168", "10.0", "0", "[", "knoxss.me", "104.236.100.235", "1760322795"];
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

fsockopen("localhost", 1337) or die("<p id=\"fade_info_2\" class=\"error\">ERROR: Service DOWN (unable to connect to tester module)!</p>" . $die);

sanitize_local();

if ($input) {

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
	       echo "<script>moveResults('" . substr(htmlentities($input), 0, 89) . $etc . "','No XSS found.', '')</script>";
            }
         } else {
            echo "<script>moveResults('" . substr(htmlentities($input), 0, 89) . $etc . "','XSS found!', '')</script>";
            echo "<p id=\"fade_info_3\" class=\"description\"><span id=\"poc\" class=\"extra\" onclick=\"" . $open_poc . 
		     "\" target=\"_blank\">Proof-of-Concept</span></p>\n";
         }

      }

      $target = preg_replace("/\?.*/", "", $target);

   } while ( $query_less != 1 );

} else {

   if ($_REQUEST["submit"]){

      $auto_complete = xss_page();
      echo "<script>\ndocument.getElementById('target').value='" . $auto_complete[0] .
	   "';\ndocument.getElementById('post').value='" . $auto_complete[1] . "';\n</script>\n";
   }
}
?>
<script>
   var data = {"target":"<?=$target;?>", "result":"<?=$open_poc;?>", "date":"<?=date('d-m-Y');?>"}
   storeData(JSON.stringify(data));
</script>
</body>
</html>
<?php
}
?>
<!--<?=htmlentities($input);?>-->
