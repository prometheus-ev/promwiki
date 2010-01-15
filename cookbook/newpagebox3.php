<?php if (!defined('PmWiki')) exit();
/*  Copyright 2005 Hans Bracker, modified from newpagebox.php
    Copyright 2005 Patrick R. Michaud (pmichaud@pobox.com)
    This file is newpagebox2.php; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published
    by the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    To use this script, simply place it into the cookbook/ folder
    and add the line

        include_once('cookbook/newpagebox2.php');

    to a local customization file.

    Version: 3.0 -- DaveG: Combined functionality of newpagebox 1 and 2
    Version: 3.1 -- DaveG: Renamed parameters: "button" becomes "label"; "field" becomes "value"; "position" becomes "button".

	PARAMETERS
    * base: same as original, create page in the same group as group.pagename
    * template: same as original, template for new page
    * button: button should go left or right of the text field (default as original left)
    * value: label or value for the inside of the field, which disappears when clicking the box. Default as original "".
    * label: label for the button, default as original Create a new page called:
*/

Markup('newpagebox', '>links',
  '/\\(:newpagebox\\s*(.*?):\\)/ei',
  "NewPageBox(\$pagename, PSS('$1'))");
$HandleActions['new'] = 'HandleNew';

function NewPageBox($pagename, $opt) {
  global $ScriptUrl;

  $defaults = array('base' => $pagename, 'template' => '', 'button' => 'left',
    'value' => '', 'label' => FmtPageName(' $[Create a new page called:] ', $pagename)
  );
  $opt = array_merge($defaults, ParseArgs($opt));
  $buttonHTML = "<input class='inputbutton newpagebutton' type='submit' value='{$opt['label']}' />";
  return "<form class='newpage' action='$ScriptUrl' method='post'>
     <input type='hidden' name='n' value='$pagename' />
     <input type='hidden' name='action' value='new' />
     <input type='hidden' name='base' value='{$opt['base']}' />
     <input type='hidden' name='template' value='{$opt['template']}' />" .
     ($opt['button']=="left" ? $buttonHTML : "") .
     "<input class='inputbox newpagetext' type='text' name='name' size='16' value=' {$opt['value']} '
     onfocus=\"if(this.value='{$opt['value']}') {this.value=''}\"
     onblur=\"if(this.value=='') {this.value='{$opt['value']}'}\" />" .
     ($opt['button']=="right" ? $buttonHTML : "") .
     "</form>";
 }

function HandleNew($pagename) {
  $name = @$_REQUEST['name'];
  if (!$name) Redirect($pagename);
  $base = MakePageName($pagename, $_REQUEST['base']);
  $newpage = MakePageName($base, $name);
  $urlfmt = '$PageUrl?action=edit';
  if (@$_REQUEST['template'])
    $urlfmt .= '&template=' . MakePageName($base, $_REQUEST['template']);
  Redirect($newpage, $urlfmt);
}

