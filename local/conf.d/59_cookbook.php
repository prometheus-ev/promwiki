<?php if (!defined('PmWiki')) exit();
foreach (glob("$FarmD/cookbook/*.php") as $filename) {
  include_once($filename);
}
