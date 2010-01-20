<?php if (!defined('PmWiki')) exit();
Markup('tags', 'directives', '/\\(:tags (.*?):\\)/e', "MakeTags('$1')");

Markup('includeupload', 'directives',
  '/\(:includeupload\s+(.+)\s*:\)/e',
  "is_readable(\"$FarmD/uploads/$1\")
    ? '<pre>  ' . Keep(str_replace(array('<', '>'), array('&lt;', '&gt;'), implode('  ', file('$BaseUrl/uploads/$1')))) . '</pre><div style=\"text-align: right; margin-top: 0.4em;\">[Source: $BaseUrl/uploads/$1]</div>'
    : '<p class=\"wikimessage\">[File not found: $1]</p>'"
);
