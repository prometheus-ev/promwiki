<?php if (!defined('PmWiki')) exit();
/*
+----------------------------------------------------------------------+
|
| This script inserts links to RSS and Atom feeds in your HTML headers.
| The links are used for "autodiscovery" of your feeds.
|
| Place this script in your cookbook/ dirctory, then use the following
| in your config.php file:
|
|   ## Enable the feedlinks recipe.
|   $EnableSitewideFeed = 1;  # Offer feeds by group if this is disabled.
|   $EnableRssLink = 1;
|   $EnableAtomLink = 1;
|   @include_once("$FarmD/cookbook/feedlinks.php");
|
| Make sure your feeds are enabled.  :-)  To enable RSS and Atom feeds,
| use the following in your local/config.php file:
|
|   if ($action == 'rss' || $action == 'atom') {
|     include_once("scripts/feeds.php"); }
|
| This recipe's page:  http://www.pmwiki.org/wiki/Cookbook/FeedLinks
+----------------------------------------------------------------------+
| Copyright 2006 Hagan Fox - http://pmwiki.org/wiki/Profiles/HaganFox
| This program is free software; you can redistribute it and/or modify
| it under the terms of the GNU General Public License, Version 2, as
| published by the Free Software Foundation.
| http://www.gnu.org/copyleft/gpl.html
| This program is distributed in the hope that it will be useful,
| but WITHOUT ANY WARRANTY; without even the implied warranty of
| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
| GNU General Public License for more details.
+----------------------------------------------------------------------+
*/
$RecipeInfo['FeedLinks']['Version'] = '0.03 (2006-03-19)';

SDV($EnableSitewideFeed, TRUE);   # Link to Site/AllRecentChanges,
                                  # otherwise feed is group-specific.
SDV($EnableRssLink, TRUE);
SDV($EnableAtomLink, TRUE);
if (@$EnableSitewideFeed == TRUE) {
  SDV($FeedLinkSourcePath , '$[$SiteGroup/AllRecentChanges]');
  SDV($FeedLinkTitleGroup , '');
  $FeedLinkType = 'sitewide';
} else {
  $FeedLinkSourcePath = '$[$Group/RecentChanges]';
  $FeedLinkTitleGroup = ' : $[$Group] -';
  $FeedLinkType = 'bygroup';
}
SDV($FeedLinkNum, 0);
$FeedLinkNum++;
if ($EnableRssLink) {
  $HTMLHeaderFmt['rsslink'.$FeedLinkNum] =
    "<link rel='alternate' title='\$WikiTitle$FeedLinkTitleGroup $[RSS Feed]'
      href='\$ScriptUrl/$FeedLinkSourcePath?action=rss'
      type='application/rss+xml' />\n  ";
}
if ($EnableAtomLink) {
  $HTMLHeaderFmt['atomlink'.$FeedLinkNum] =
    "<link rel='alternate' title='\$WikiTitle$FeedLinkTitleGroup $[Atom Feed]'
      href='\$ScriptUrl/$FeedLinkSourcePath?action=atom'
      type='application/atom+xml' />\n  ";
}
