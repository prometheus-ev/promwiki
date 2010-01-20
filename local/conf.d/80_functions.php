<?php if (!defined('PmWiki')) exit();
array_unshift($EditFunctions, 'ProvideDefaultSummary');

function ProvideDefaultSummary($pagename, &$page, &$new) {
  global $ChangeSummary, $DiffFunction;

  if ($ChangeSummary || !function_exists(@$DiffFunction)) return;

  $diff = $DiffFunction($new['text'], @$page['text']);
  $difflines = explode("\n", $diff."\n");

  $in = array(); $out = array();
  foreach ($difflines as $d) {
    if ($d == '' || $d[0] == '-' || $d[0] == '\\') continue;
    if ($d[0] == '<' && count($out) < 10) {
      $out[] = substr($d, 2);
      continue;
    }
    if ($d[0] == '>' && count($in) < 10) {
      $in[] = substr($d, 2);
      continue;
    }
  }

  $diff2 = '';
  if (count($out) == 0) {
    $out = $in; $diff2 = "[deleted] ";
  }
  foreach ($out as $s) {
    $diff2 .= $s." ";
  }

  $ChangeSummary = str_replace(array("<", ">", "&", "\n"), array("&lt;", "&gt;", "&amp;", " "), $diff2);
  $new['csum'] = $ChangeSummary;
}

function MakeTags($string) {
  $tags = array_map(
    create_function('$s', 'return "[[!" . trim($s) . "]]";'),
    explode(" ", $string)
  );

  return "<div class='tags'>Tags: " . join(" | ", $tags) . "</div>";
}
