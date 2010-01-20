<?php if (!defined('PmWiki')) exit();
if ($action == 'rss' || $action == 'rdf' || $action == 'atom' || $action == 'dc') {
  include_once('scripts/feeds.php');
}

## Add author to title string
$FeedFmt['rss']['item']['title'] = '{$Group} / {$Title} ({$LastModifiedBy})';

## Add change summary as description
$FeedFmt['rss']['item']['description'] = '$LastModifiedSummary';
