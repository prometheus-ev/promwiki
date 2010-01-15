<?php
  header("Content-type: image/jpg");
  $html = file_get_contents('http://prometheus-bildarchiv.de/?skin=1');
  ereg('<div id="startbild"><img[^>]* src="([^"]*)"', $html, $capture);
  echo file_get_contents('http://prometheus-bildarchiv.de/'.$capture[1])
?>
