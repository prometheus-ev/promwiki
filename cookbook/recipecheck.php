<?php if (!defined('PmWiki')) exit();
/*  Copyright 2007 Patrick R. Michaud (pmichaud@pobox.com)
    This file is recipecheck.php; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published
    by the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.  

    This recipe adds ?action=recipecheck to a site.  When activated,
    ?action=recipecheck fetches the current list of Cookbook recipes
    and their versions from pmwiki.org.  It then compares this list
    with the versions of any installed recipes on the local site
    and reports any differences.

    To use this recipe, simply place this file in the cookbook/
    directory and add the following to a local configuration:

        include_once('cookbook/recipecheck.php');

    Then use ?action=recipecheck on a page to see the results.
    By default the recipe restricts ?action=recipecheck to admins.

    Note that not all recipes currently follow PmWiki's
    recipecheck standard (feel free to report these to the pmwiki-users
    mailing list).

    * 2007-04-17:  Added suggestions by Feral
      - explicit black text
      - skip non-php files and directories
*/

SDV($RecipeInfo['RecipeCheck']['Version'], '2007-04-17');

SDV($HandleActions['recipecheck'], 'HandleRecipeCheck');
SDV($HandleAuth['recipecheck'], 'admin');
SDV($ActionTitleFmt['recipecheck'], '| $[Recipe Check]');

SDV($WikiStyleApply['tr'], 'tr');
SDV($HTMLStylesFmt['recipecheck'], '
  table.recipecheck tr.ok { color:black; background-color:#ccffcc; }
  table.recipecheck tr.check { color:black; background-color:#ffffcc; }
  table.recipecheck { border:1px solid #cccccc; padding:4px; }
');

SDV($RecipeListUrl, 'http://www.pmwiki.org/pmwiki/recipelist');

function HandleRecipeCheck($pagename, $auth = 'admin') {
  global $RecipeListUrl, $Version, $RecipeInfo, 
    $RecipeCheckFmt, $PageStartFmt, $PageEndFmt;
  $page = RetrieveAuthPage($pagename, $auth, true, READPAGE_CURRENT);
  if (!$page) Abort('?admin access required');
  $cvinfo = GetRecipeList($RecipeListUrl);
  if (!$cvinfo) {
    $msg = "Unable to retrieve cookbook data from $RecipeListUrl\n";
    $allow_url_fopen = ini_get('allow_url_fopen');
    if (!$allow_url_fopen) $msg .= "
      <br /><br />It appears that your PHP environment isn't allowing
      the recipelist to be downloaded from pmwiki.org  
      (allow_url_fopen&nbsp;=&nbsp;$allow_url_fopen).";
    Abort($msg);
  }
  $rinfo['PmWiki:Upgrades'] = $Version;
  ScanRecipeInfo('cookbook', $cvinfo);
  foreach((array)$RecipeInfo as $r => $v) {
    if (!@$v['Version']) continue;
    $r = preg_replace('/^(?!PmWiki:)(Cookbook[.:])?/', 'Cookbook:', $r);
    $rinfo[$r] = $v['Version'];
  }
  $markup = "!!Recipe status for {\$PageUrl}\n".RecipeTable($rinfo, $cvinfo);
  $html = MarkupToHTML($pagename, $markup);
  SDV($RecipeCheckFmt, array(&$PageStartFmt, $html, &$PageEndFmt));
  PrintFmt($pagename, $RecipeCheckFmt);
}


function GetRecipeList($list) {
  $cvinfo = array();
  $fp = fopen($list, 'r');
  while ($fp && !feof($fp)) {
    $line = fgets($fp, 1024);
    if ($line{0} == '#') continue;
    if (preg_match('/(\\S+) +(.*)/', $line, $match)) 
      $cvinfo[$match[1]] = trim($match[2]);
  }
  fclose($fp);
  return $cvinfo;
}


function ScanRecipeInfo($dlistfmt, $cvinfo = NULL) {
  global $RecipeInfo;
  foreach((array)$dlistfmt as $dir) {
    $dfp = @opendir($dir); if (!$dfp) continue;
    while ( ($name = readdir($dfp)) !== false) {
      if ($name{0} == '.') continue;
      if (!preg_match('/\\.php/i', $name)) continue;
      $text = implode('', @file("$dir/$name"));
      if (preg_match("/^\\s*\\\$RecipeInfo\\['(.*?)'\\]\\['Version'\\]\\s*=\\s*'(.*?)'\\s*;/m", $text, $match)) 
        SDV($RecipeInfo[$match[1]]['Version'], $match[2]);
      if (preg_match("/^\\s*SDV\\(\\s*\\\$RecipeInfo\\['(.*?)'\\]\\['Version'\\]\\s*,\\s*'(.*?)'\\s*\\)\\s*\\;/m", $text, $match)) 
        SDV($RecipeInfo[$match[1]]['Version'], $match[2]);
      if (@$cvinfo[$name]) {
        $r = preg_replace('/^.*:/', '', $cvinfo[$name]);
        SDV($RecipeInfo[$r]['Version'], "unknown ($name)");
      }
    }
    closedir($dfp);
  }
}


function RecipeTable($rinfo, $cvinfo) {
  $fmt = "||%-40s ||%-20s ||%-20s ||\n";
  $out = "||class=recipecheck cellpadding=0 cellspacing=0 width=600\n";
  $out .= sprintf($fmt, '!Recipe', '!local', '!pmwiki.org');
  foreach($rinfo as $r => $lv) {
    $cv = $cvinfo[$r];
    $style = ($lv == $cv) ? 'ok' : 'check';
    $out .= sprintf($fmt, "%apply=tr $style%[[$r]]", $lv, $cv);
  }
  return $out;
}
