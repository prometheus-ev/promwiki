<?php if (!defined('PmWiki')) exit();
$BaseUrl = 'http://prometheus-bildarchiv.de/wiki';
$FmtPV['$BaseUrl'] = '$GLOBALS["BaseUrl"]';

$ScriptUrl = "$BaseUrl";
$PubDirUrl = "$BaseUrl/pub";

$EnablePathInfo = 1;

include_once('scripts/xlpage-utf-8.php');

XLPage('de', 'PmWikiDe.XLPage');
XLPage('de', 'PmWikiDe.XLPageCookbook');

$ConfDir = dirname(__FILE__) . "/conf.d";

include_once("$ConfDir/passwords.php");

foreach (glob("$ConfDir/[0-9][0-9]_*.php") as $filename) {
  include_once($filename);
}
