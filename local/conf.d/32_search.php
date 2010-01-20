<?php if (!defined('PmWiki')) exit();
$SearchPatterns['default'][] = '!\.(?:All)?Recent(?:Changes|Uploads)$!';
$SearchPatterns['default'][] = '!\.Group(?:Print)?(?:Header|Footer|Attributes)$!';

$SearchPatterns['normal'][] = '!-(?:Preferences|EditForm|EditQuickReference)$!';
$SearchPatterns['normal'][] = '!(?:Scratchpad)$!';
