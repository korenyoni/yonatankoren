<?php
require_once __DIR__ . '/parsedown/Parsedown.php';
$post_id = $_GET['id'];

$file_text = file_get_contents(__DIR__ . '/posts/' . $post_id);
$title_regex = "/Title:.*\n/";
preg_match($title_regex,$file_text,$title_line_matches);
$title = preg_replace("/Title:/", '', $title_line_matches[0]);
$title = trim($title);
$file_text = preg_replace("/Date:.*\nTitle:.*\ncat:.*\n/",'',$file_text);
$Parsedown = new Parsedown();
$html = $Parsedown->text($file_text);

echo <<<HTML
     <head>
      <!-- Mobile Specific Metas
      –––––––––––––––––––––––––––––––––––––––––––––––––– -->
      <meta name="viewport" content="width=device-width, initial-scale=1">

      <!-- FONT
      –––––––––––––––––––––––––––––––––––––––––––––––––– -->
      <link href="//fonts.googleapis.com/css?family=Raleway:400,300,600" rel="stylesheet" type="text/css">

      <!-- CSS
      –––––––––––––––––––––––––––––––––––––––––––––––––– -->
      <link rel="stylesheet" href="/css/normalize.css">
      <link rel="stylesheet" href="/css/skeleton.css">
      <link rel="stylesheet" href="/css/blog.css">

      <!-- Favicon
      –––––––––––––––––––––––––––––––––––––––––––––––––– -->
      <link rel="icon" type="image/png" href="/images/favicon.png">
      <script src="/js/jquery-3.3.1.js"></script>
      <script>
      $(document).ready(function(){
        $( "#header" ).load( "/header.html" );
        $( "#footer" ).load( "/footer.php" );
        $( "#homeSubmit" ).on('click', function() {
          window.location = "/index.html";
        });
      });
      </script>
    </head>
    <div id="header"></div>
    <div class="container">
      <div class="twelve columns" style="text-align: center">
        <button id="homeSubmit">Home</button>
      </div>
      <div class="twelve columns" style="text-align: center; margin-top: 5%">
        <h1>{$title}</h1>
      </div>
      <div class="twelve columns" style="margin-top: 5%;text-align: left;">
        {$html}
      </div>
      <div id="footer"></div>
    </div>

    <!-- End Document
      –––––––––––––––––––––––––––––––––––––––––––––––––– -->
    </body>
    </html>
HTML;
?>
