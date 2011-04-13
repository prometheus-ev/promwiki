<?php

/***********************************************************
* pmcal.php, version 0.1 requires PmWiki 2.0.x             *
* This is a calendar display cookbook recipe for PmWiki.   *
*                                                          *
* Copyright (c) 2005, Chris Cox ccox@airmail.net           *
* All Rights, Reserved.                                    *
*                                                          *
* This program is free software; you can redistribute it   *
* and/or modify it under the terms of the GNU General      *
* Public License as published by the Free Software         *
* Foundation; either version 2 of the License, or (at your *
* option) any later version.                               * 
*                                                          *
* Installation:                                            *
*                                                          *
* Place pmcal.php (this file) into your cookbook           *
* directory (e.g. /srv/www/htdocs/pmwiki/cookbook)         *
*                                                          *
* Include the cookbook in your local/config.php            *
* include_once('cookbook/pmcal.php');                      *
*                                                          *
* Create a new Group called, for example, PmCal.           *
* You will need something on that page, let's say          *
* just ! This is My Calendar                               *
*                                                          *
* Create a GroupFooter page in that group with just        *
* (:pmcal:)                                                *
*                                                          *
* The entries on the individual days are show on the       *
* calendar but it defaults to show the first paragraph     *
* using (:include YYYYMMDD paras=1:)                       *
*                                                          *
* This will allow you to create a table of contents of     *
* sorts... not the (:toc:) recipe though.                  *
*                                                          *
* I freely admit I copied some ideas (but no code) from    *
* the WikiLog calendar recipe.                             *
*                                                          *
***********************************************************/

/* Changelog

-<0.2: Went ahead and renamed Day pages to just YYYYMMDD to \
be compatible with Wikilog in case you just want to \
use PmCalendar instead.

-<0.3: Pass month/day/year get vars to day pages.

-<0.4: Fix bad date logic when figuring up day of week names.

-<0.5: Commented out style attributes on cells to allow better \
CSS customization... likewise you can uncomment them \
if you like it the way it was. Added table border=1 \
so it doesn't look horrible without a css file.

->Added a Today link in case you get lost.

-<0.6: Mostly CSS related.  Changed cjccaldaytitle to \
cjccaldayofweektitle and change added alternate prefix \
pmcal (I didn't intend to release this with cjcal intact). \
PmCal.css makes a bit more sense... I'm not a CSS expert. \
Defaults are now smaller and PmCal.css is also reduced \
to look better on smaller screen sizes.

-<0.7: Added the ability to include other calendars into a calendar.

->Added the ability parse arguments to (:pmcal var=...:):

     year=YYYY
     month=1..12
     day=1..number of days in month
     cals=Calendar1,Calendar2,....
          Where Calendar1.Calendar1 is a page with (:pmcal:) on it somehow
          (e.g. in the Calendar1.GroupFooter) 

->Made better comments in PmCal.css.

->Could have lots of includes if you use cals= Not sure how to handle this...

     $MaxIncludes = 500;

->Set this in your local config.php for now.

->Better comments in code.

-<0.8: Lots of code changes.  Added caltype, monthsahead, monthsback.

    caltype=[normal|text] (defaults to normal)
    monthsahead=0..60 (can show as many as 5 years more months ahead)
    monthsback=0..60 (can show as many as 5 years more months back)

->The GroupFooter for your PmCalendar Group page can now contain:

     [=
     [[PmCal?monthsahead=1&monthsback=1&caltype=text|Event Diary]]
     (:pmcal cals=Holidays,Cjc:)
     =]

-<0.9: Code cleanup.  Some minor formatting changes.  Stylized inclusions \
Just not possible without nested divs.  PmCal.css is now more MSIE friendly.

-<0.10: Hah! ...I know, it looks like 0.1.  You'd think I'd learn how to \
do versioning.  Force a new block for day text.  This means that the space \
beside the day number in the default skin won't have anything.  But also \
may make blocking the daynumber easier.  Big features is locales, use \
locale= and optionally set isodate='true' if you want to force ISO date \
format.  If test='css', then you can set styles=PmCal,Other where,... where \
PmCal and Other are examples of CSS file names.  Firefox users can use \
View->Page Style to switch.  There is now a pmcaltodaynumber.

->The page name of the form YYYYMMDD is now used as the default date for any \
(:pmwiki:) markup on the page, otherwise it still defaults to "today".

-<0.11: Won't set locale by default.. my mistake (duh).  Fixed a syntax \
problem preventing uploadable themes from working (I think).  Most everything \
on the url line remembered and used for other links (illusion of state?).

-<0.12: Added a week start offset.  Normally a week starts with day zero, which \
is Sunday.  A weekstart=1 implies your week starts with Monday.  Fixed a major \
problem with caltype=text where the days were hard coded to 1.  Fixed references \
to cals= calendars to not specify a page name (let pmwiki find it).

-<0.13: Added pmcalzebra div to text display.

-<0.14: 2 new features (sorry).  The one that was truly necessary is the ability \
to reverse the calendars.  Making it display the most recent day/month first. \
Set that with reverse=true.  For example, you might want to use the calendar \
to create a diary (or perhaps blog) in which you want the most recent items \
first.  The second feature is the ability to disable variable settings made on \
the url line.  Set overrides=false in the :pmcal: markup itself. One major bug \
fix with regards to how monthtitles were processed, the array of names is filled \
in... but since it's now populated with strftime values, the monthtitles are \
supposed to be more dynamic, always needs to be recalculated.  Had a type where \
monthsahead (missing the s) was not being passed on the url line.  A lot of code changes \
were needed to support the reverse feature.  I hope this is the last major delta \
before 1.0.

-<1.0rc1: Removed all cjccal ids for css use, use pmcal ones.  Fixed ampersands to \
use correct &amp; inside of URL links (should pass w3c check).  Added a textlinks \
boolean to control the textlinks when caltype=text is used.  Even though you could \
control their visibility with css, recipes like pagetoc could still see them.

-<1.0rc2: Changed misspelled directive type to <include instead. Added support for \
anniversary recurring data via year=ACAL.  Also included acals= parameter to specify \
anniversary data to include from a PmCalendar with anniversary data.  Also added \
callinks= to be able to turn off the links at the top of the calendar for Today and \
any included calendars via cals=.

-<1.0rc3: Added calfmt and textcalfmt to control the text displayed as a heading for 
and included calendar entry.  Added expire flag.  Set this and entries before today's
date are not shown in caltype=text display.

-<1.0rc4: Fixed bug in included calendar when it's an anniversary calendar.
Added option for expire to be a delta of days, positive or negative, to expire
from today's date.

-<1.0rc5: Overloaded textlinks= to allow the value nolinks.  This includes the text
for the dates listed without making them links to the page data when caltype=text.

-<1.0rc6: Added lines= and paras=.  All includes default to paras=1.  Remember
if you use lines= that lines have to have a carriage return.  Added my own
pmcalcreatetextlink class.  Current version of PmWiki 2.1.beta26, changes
behavior of links to where links with parameters are always "true" links.

-<1.0rc7: Added stopafter=. Similar to expire=, except this controls display
of caltype=text entries after delta days offset from today.

-<1.0rc8: Added alwaystoday=.  Ensures that it is always today.  Useful for
having a This Week's Events text diary on the same page with a normal
calendar.  Also added onedate=.  This will prevent using multiple date
textlinks when several caltype=text calendar entries are being displayed for
a given day.  May want to set textcalfmt=''.  In addition the variables
[=$PmCalTextLinkMark, $PmCalIncludeTextLinkMark, $PmCalACALTextLinkMark=] now
exist that can be set by the local admin to control the PmWiki markup used
for textlinks (they all default to [=!!=].

-<1.0rc9: Added onedate=showcals and added $PmCalSubIncludeTextLinkMark and
$PmCalSubACALTextLinkMark for the markups associated with entries on the same
day using onedate=showcals that are not the primary (first one) entry.  Also
added acalfmt and textacalfmt for consistency.  Hopefully fixed the
mktime bug for php 5.1 (not tested).

-<1.0rc10: Fixed a bug in the computing monthtitle.  If a month only has
30 days in it and you say it has 31 days, printing a formatted month will
show the next month.  Changed the code to use '1' instead of the current day.

-<1.0rc11: Added extra today classes for included calendars.

-<1.0rc12: Fixed issue where you could not set a constant year=ACAL.  Now
you can set year=ACAL in the markup or on the URL line and it will stay
in the ACAL context when you navigate to next/previous months.


->Variables:
     [=
     year=              defaults to today's year (year=ACAL to add dates that can be used with acals=).
     month=             defaults to today's month.
     day=               defaults to today's day.
     acalfmt=           Formatted display of included ACAL calendar name.  Defaults to calfmt (below).
                          Substitutes the calendar name for %s (see also textacalfmt).
     acals=             anniversary date calendars to include (normal calendars with year=ACAL entries).
     alwaystoday=       defaults to false.  If true, they date is always today.  Useful for viewing
                          upcoming events when on a page containing a calendar viewing a different date.
     calfmt=            Formatted display of included calendar name.  Defaults to %s.  Substitutes
                          the calendar name for %s (see also textcalfmt).
     callinks=          defaults to true, includes links to Today and included calendars via cals=
     cals=              calendars to include: Holidays,Cjc,...
     caltype=           normal (default) or text.
     dayofweektitlefmt= default to %a (processed with strftime).
     dayofweektitle=    hardcode your own days of the week array (processed with strftime):
                          Sun,Mon,Tue,Hump,Thu,TGIF,Sat
     expire=            defaults to false, when set, do not display entries before today. Can also
                          be an integer delta number of days from today.
     includes=          Do includes, default is true.
     isodate=           YYYY-MM-DD, defaults is false (obsolete, textdatefmt=%Y-%m-%d).
     lines=             Set to an integer value for number of lines to include from a given
                        day in a display.  This overrides paras below.
     locale=            A system valid LC_TIME locale.
     monthsback=        Can display as many as 5 years of months back (60 months)
                          (might kill your browser though)
     monthsahead=       Can display as many as 5 years of months ahead (60 months)
                          (might kill your browser though)
     monthtitlefmt=     defaults to %B %Y (processed with strftime).
     monthtitle=        hardcode your own month names (processed with strftime):
                           New Year,Lovers Month,Lions Month,Showers Month,
     navnext=           defaults to &raquo; (processed with strftime)
     navprev=           defaults to &laquo; (processed with strftime)
     onedate=           defaults to false.  On caltype=text, if true, don't repeated date entry text links
                           more than once if same date.  If set to showcals, show the calendar of origin
                           for included calendar entries after the first one.
     overrides=         (in markup only) defaults to true.  If false, disallow URL variables.
     paras=             Set to an integer value for number of paragraphs to include from a given
                        day in a display.
     cssprefix=         Create css style/divs with this prefix.  Defaults to pmcal.
     reverse=           months start with the end and go backwards.
     stopafter=         days (positive or negative) from today for which no more entries will
			be displayed in caltype=text.  If stopafter=5, show entries for the month
                        stopping with entries beyond 5 days from today (see also expire).
     styles=            (experimental) css style selections, first is preferred.
                           e.g. PmCal,PmCal-Red
     textacalfmt=        Analagous to acalfmt, except for caltype=text display.  Defaults to
                           textcalfmt.
     textcalfmt=        Analagous to calfmt, except for caltype=text display.  Defaults to
                           %s.
     textdatefmt=       for caltype=text, defaults to %x, used for dates with caltype=text
                           (processed with strftime)
     textlinks=         defaults to true, include a dated link in caltype=text before
                           included entry.  If set to nolinks, same as true, but text
                           displayed will not be links.
     weekstart=         defaults to 0.  With 0=Sunday.  Set to the day of the week in which
                           your week starts.
     =]

*/

//Some globals
$PmCaltyear = date("Y");
$PmCaltmonth = date("m");
$PmCaltday = date("d");


// Based on an idea by Pm and fixes by Dominique Faure
//
Markup('{$PmCalDName}', '{$var}',
	'/{\\$PmCalDName(:(.+?))?}/e',
	"pmcaldname(\$pagename, '$2')");

function pmcaldname($pagename, $fmt) {
	if (!preg_match('/\\.(\d\d\d\d)(\d\d)(\d\d)$/', $pagename, $match)) {
		return $pagename;
	}
	if (!$fmt) {
		$fmt = '%b %e %Y';
	}
	$gmt = mktime(0, 0, 0, $match[2], $match[3], $match[1]);
	if(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
		$fmt = preg_replace(array('/%e/', '/%D/'), array(date('n', $gmt), '%m/%d/%y'), $fmt);
	}
	return strftime($fmt, $gmt);
} 

// Function will output pmwiki markup.
//
Markup('pmcal','directives',"/^\(:pmcal[ 	]*(.*?):\)\s*$/e",
	"pmcal('$1')");

function pmcal_year($y) {
	global $PmCaltyear;
/*
php stinks
	if (!is_int("$y"))
		return $PmCaltyear;
*/
	if(preg_match("/^[0-9]+$/", "$y")) $y = (int)$y;
	if (!is_int($y))
		return $PmCaltyear;
	return $y;
}

function pmcal($opts) {
	global $pagename, $MaxIncludes, $HTMLHeaderFmt,
		$PmCalTextLinkMark, $PmCalIncludeTextLinkMark,
		$PmCalACALTextLinkMark, $PmCalSubIncludeTextLinkMark,
		$PmCalSubACALTextLinkMark,
		$PmCaltyear, $PmCaltmonth,
		$PmCaltday;

	//Initial PmWiki markup for textlinks
        SDV($PmCalTextLinkMark, '!!');
        SDV($PmCalIncludeTextLinkMark, '!!');
        SDV($PmCalACALTextLinkMark, '!!');
	SDV($PmCalSubIncludeTextLinkMark, $PmCalIncludeTextLinkMark);
	SDV($PmCalSubACALTextLinkMark, $PmCalACALTextLinkMark);

	// Determine this Group
	//
	$group = FmtPageName('$Group',$pagename);
	$name = FmtPageName('$Name',$pagename);

	// What is today?
	//
	// $tmonth = date("m");
	// $tyear = date("Y");
	// $tday = date("d");

	// Process markup arguments first
	//
	$defaults = array(
		'year'=>$PmCaltyear,
		'month'=>$PmCaltmonth,
		'day'=>$PmCaltday,
		'alwaystoday'=>'false',
		'acalfmt'=>NULL,
		'calfmt'=>'%s',
		'callinks'=>'true',
		'caltype'=>'normal',
		'cssprefix'=>'pmcal',
		'dayofweektitlefmt'=>'%a',
		'expire'=>'false',
		'overrides'=>'true',
		'includes'=>'true',
		'isodate'=>'false',
		'lines'=>NULL,
		'locale'=>NULL,
		'monthsahead'=>0,
		'monthsback'=>0,
		'monthtitlefmt'=>'%B %Y',
		'navnext'=>'&raquo;',
		'navprev'=>'&laquo;',
		'onedate'=>'false',
		'paras'=>NULL,
		'reverse'=>'false',
		'stopafter'=>'false',
		'textacalfmt'=>NULL,
		'textcalfmt'=>'(%s)',
		'textdatefmt'=>'%x',
		'textlinks'=>'true',
		'weekstart'=>0,
		'zebra'=>'true',
		'acals'=>NULL,
		'cals'=>NULL,
		'dayofweektitle'=>NULL,
		'monthtitle'=>NULL,
		'styles'=>NULL
	);

	// If name is of the format YYYYMMDD, then we'll
	// override the year, month, day defaults
	//
	// Hack for ACAL isn't perfect here... but ok.
	//
	if (preg_match("/([A0-9][C0-9][A0-9][L0-9])([01][0-9])([0-3][0-9])/",
		$name, $matches)) {
		$defaults['year']=$matches[1];
		$defaults['month']=$matches[2];
		$defaults['day']=$matches[3];
	}

	$args = array_merge($defaults, ParseArgs($opts));
	$urladd='';

	// Default to today if nothing supplied on the URL
	// line... ?month=12&day=25&year=2005 would be
	// Christmas 2005
	// URL GET vars override markup arguments
	//
	// With that said, might be useful to have a command line
	// augment the markup arguments in the case of foreign
	// calendar overlays... hmmm...  possible new feature.
	// calsplus=Calendar maybe...
	//
	// Build a url tack on of get vars when get vars are used.
	//

	
	$year = isset($_GET['year']) ? $_GET['year'] : $args['year'];
	$month = isset($_GET['month']) ? $_GET['month'] : $args['month'];
	$day = isset($_GET['day']) ? $_GET['day'] : $args['day'];

	// Sneaky fix for default monthtitlefmt for ACAL
	if ($year == 'ACAL') {
		$ayear='ACAL';
		$args['monthtitlefmt']='%B ACAL';
	}

	// Allows overrides=false in the :pmcal: markup to disallow
	// settings on the URL line.
	//
	$overrides = $args['overrides'];
	if ($overrides == 'false') {
		$_GET = NULL;
	}

	$acals = isset($_GET['acals']) ? explode(',',$_GET['acals']) : explode(',',$args['acals']);
	if (isset($_GET['acals']))
		$urladd.="&amp;acals=".urlencode($_GET['acals']);
	$alwaystoday = isset($_GET['alwaystoday']) ? $_GET['alwaystoday'] : $args['alwaystoday'];
	if (isset($_GET['alwaystoday']))
		$urladd.="&amp;alwaystoday=".urlencode($_GET['alwaystoday']);
	$acalfmt= isset($_GET['acalfmt']) ? $_GET['acalfmt'] : $args['acalfmt'];
	if (isset($_GET['acalfmt']))
		$urladd.="&amp;acalfmt=".urlencode($_GET['acalfmt']);
	$calfmt= isset($_GET['calfmt']) ? $_GET['calfmt'] : $args['calfmt'];
	if (isset($_GET['calfmt']))
		$urladd.="&amp;calfmt=".urlencode($_GET['calfmt']);
	$callinks = isset($_GET['callinks']) ? $_GET['callinks'] : $args['callinks'];
	if (isset($_GET['callinks']))
		$urladd.="&amp;callinks=".urlencode($_GET['callinks']);
	$cals = isset($_GET['cals']) ? explode(',',$_GET['cals']) : explode(',',$args['cals']);
	if (isset($_GET['cals']))
		$urladd.="&amp;cals=".urlencode($_GET['cals']);
	$caltype = isset($_GET['caltype']) ? $_GET['caltype'] : $args['caltype'];
	if (isset($_GET['caltype']))
		$urladd.="&amp;caltype=".urlencode($_GET['caltype']);
	$cssprefix = isset($_GET['cssprefix']) ? $_GET['cssprefix'] : $args['cssprefix'];
	if (isset($_GET['cssprefix']))
		$urladd.="&amp;cssprefix=".urlencode($_GET['cssprefix']);
	$dayofweektitle = isset($_GET['dayofweektitle']) ? explode(',',$_GET['dayofweektitle']) :
		explode(',',$args['dayofweektitle']);
	if (isset($_GET['dayofweektitle']))
		$urladd.="&amp;dayofweektitle=".urlencode($_GET['dayofweektitle']);
	$dayofweektitlefmt = isset($_GET['dayofweektitlefmt']) ? $_GET['dayofweektitlefmt'] : $args['dayofweektitlefmt'];
	if (isset($_GET['dayofweektitlefmt']))
		$urladd.="&amp;dayofweektitlefmt=".urlencode($_GET['dayofweektitlefmt']);
	$expire = isset($_GET['expire']) ? $_GET['expire'] : $args['expire'];
	if (isset($_GET['expire']))
		$urladd.="&amp;expire=".urlencode($_GET['expire']);
	$includes = isset($_GET['includes']) ? $_GET['includes'] : $args['includes'];
	if (isset($_GET['includes']))
		$urladd.="&amp;includes=".urlencode($_GET['includes']);
	$isodate = isset($_GET['isodate']) ? $_GET['isodate'] : $args['isodate'];
	if (isset($_GET['isodate']))
		$urladd.="&amp;isodate=".urlencode($_GET['isodate']);
	$lines = isset($_GET['lines']) ? $_GET['lines'] : $args['lines'];
	if (isset($_GET['lines']))
		$urladd.="&amp;lines=".urlencode($_GET['lines']);
	$locale = isset($_GET['locale']) ? $_GET['locale'] : $args['locale'];
	if (isset($_GET['locale']))
		$urladd.="&amp;locale=".urlencode($_GET['locale']);
	$monthsahead = isset($_GET['monthsahead']) ? $_GET['monthsahead'] : $args['monthsahead'];
	if (isset($_GET['monthsahead']))
		$urladd.="&amp;monthsahead=".urlencode($_GET['monthsahead']);
	$monthsback = isset($_GET['monthsback']) ? $_GET['monthsback'] : $args['monthsback'];
	if (isset($_GET['monthsback']))
		$urladd.="&amp;monthsback=".urlencode($_GET['monthsback']);
	$monthtitle= isset($_GET['monthtitle']) ? explode(',',$_GET['monthtitle']) : explode(',',$args['monthtitle']);
	if (isset($_GET['monthtitle']))
		$urladd.="&amp;monthtitle=".urlencode($_GET['monthtitle']);
	$monthtitlefmt = isset($_GET['monthtitlefmt']) ? $_GET['monthtitlefmt'] : $args['monthtitlefmt'];
	if (isset($_GET['monthtitlefmt']))
		$urladd.="&amp;monthtitlefmt=".urlencode($_GET['monthtitlefmt']);
	$navnext = isset($_GET['navnext']) ? $_GET['navnext'] : $args['navnext'];
	if (isset($_GET['navnext']))
		$urladd.="&amp;navnext=".urlencode($_GET['navnext']);
	$navprev = isset($_GET['navprev']) ? $_GET['navprev'] : $args['navprev'];
	if (isset($_GET['navprev']))
		$urladd.="&amp;navprev=".urlencode($_GET['navprev']);
	$onedate = isset($_GET['onedate']) ? $_GET['onedate'] : $args['onedate'];
	if (isset($_GET['onedate']))
		$urladd.="&amp;paras=".urlencode($_GET['onedate']);
	$paras = isset($_GET['paras']) ? $_GET['paras'] : $args['paras'];
	if (isset($_GET['paras']))
		$urladd.="&amp;paras=".urlencode($_GET['paras']);
	$reverse = isset($_GET['reverse']) ? $_GET['reverse'] : $args['reverse'];
	if (isset($_GET['reverse']))
		$urladd.="&amp;reverse=".urlencode($_GET['reverse']);
	$stopafter = isset($_GET['stopafter']) ? $_GET['stopafter'] : $args['stopafter'];
	if (isset($_GET['stopafter']))
		$urladd.="&amp;stopafter=".urlencode($_GET['stopafter']);
	$styles = isset($_GET['styles']) ? explode(',',$_GET['styles']) : explode(',',$args['styles']);
	if (isset($_GET['styles']))
		$urladd.="&amp;styles=".urlencode($_GET['styles']);
	$textacalfmt= isset($_GET['textacalfmt']) ? $_GET['textacalfmt'] : $args['textacalfmt'];
	if (isset($_GET['textacalfmt']))
		$urladd.="&amp;textacalfmt=".urlencode($_GET['textacalfmt']);
	$textcalfmt= isset($_GET['textcalfmt']) ? $_GET['textcalfmt'] : $args['textcalfmt'];
	if (isset($_GET['textcalfmt']))
		$urladd.="&amp;textcalfmt=".urlencode($_GET['textcalfmt']);
	$textdatefmt = isset($_GET['textdatefmt']) ? $_GET['textdatefmt'] : $args['textdatefmt'];
	if (isset($_GET['textdatefmt']))
		$urladd.="&amp;textdatefmt=".urlencode($_GET['textdatefmt']);
	$textlinks = isset($_GET['textlinks']) ? $_GET['textlinks'] : $args['textlinks'];
	if (isset($_GET['textlinks']))
		$urladd.="&amp;textlinks=".urlencode($_GET['textlinks']);
	$weekstart = isset($_GET['weekstart']) ? $_GET['weekstart'] : $args['weekstart'];
	if (isset($_GET['weekstart']))
		$urladd.="&amp;weekstart=".urlencode($_GET['weekstart']);
	if (isset($_GET['zebra']))
		$urladd.="&amp;zebra=".urlencode($_GET['zebra']);
	$zebra = isset($_GET['zebra']) ? $_GET['zebra'] : $args['zebra'];


	// Experimenting with CSS
	// Styles can come out of the upload area for the group!
	//
	$first=1; //Set first style to the preferred.
	if ($styles[0] != NULL) {
		foreach ((array)$styles as $stylename) {
  			$filepath = FmtPageName("pub/css/$stylename.css", $pagename);
			if ($first) {
				$rel="rel='stylesheet'";
				$first=0;
			} else {
				$rel="rel='alternate stylesheet'";
			}
			if (file_exists($filepath)) {
				$HTMLHeaderFmt[] = "<link $rel type='text/css' href='\$PubDirUrl/css/$stylename.css' title='$stylename'/>\n";
			} else {
  				$filepath = FmtPageName("\$UploadFileFmt/$stylename.css", "$pagename");
				if (file_exists($filepath)) {
					$HTMLHeaderFmt[] = "<link $rel type='text/css' href='\$UploadUrlFmt/$group/$stylename.css' title='$stylename'/>\n";
				} 
			}
		}
	}

	// Set the locale
	//
	if ($isodate == 'true') {
		$textdatefmt='%Y-%m-%d';
	} 
	if ($locale != NULL) {
		setlocale(LC_TIME, $locale);
	}

	// Number of paragraphs to include
	$parasorlines='paras';
	if ($lines != NULL) {
		$parasorlines='lines';
		$normallinesparas=$lines;
		$otherlinesparas=$lines;
	} else {
		if ($paras == NULL) {
			$normallinesparas='1';
			$otherlinesparas='-1';
		} else {
			$normallinesparas=$paras;
			$otherlinesparas=$paras;
		}
	}


	// Fallback to today/default information if something doesn't look right.
	//
	if ($month < 1 || $month > 12) {
		$month=$PmCaltmonth;
	}
	if ($year < 1 || $year > 2038) {
		if ($year != 'ACAL')
			$year=$PmCaltyear;
	}
	if ($alwaystoday == 'true') {
		$year=$PmCaltyear;
		$month=$PmCaltmonth;
		$day=$PmCaltday;
	}

	switch ($caltype) {
	case 'text':
		break;
	case 'normal':
		break;
	default:
		$caltype='normal';
	}

	// Set acalfmt to calfmt if not set.
	if ($acalfmt == NULL)
		$acalfmt=$calfmt;

	// Set textacalfmt to textcalfmt if not set.
	if ($textacalfmt == NULL)
		$textacalfmt=$textcalfmt;

	// Set PmCalPrefix to cssprefix.
	$PmCalPrefix=$cssprefix;

	// Try to limit look backs and lookaheads (5 years)
	//
	if ($monthsback < 0 || $monthsback > 60) {
		$monthsback=0;
	}
	if ($monthsahead < 0 || $monthsahead > 60) {
		$monthsahead=0;
	}

	// Handle expiration.
	//
	$eyear=$PmCaltyear;
	$emonth=$PmCaltmonth;
	$eday=$PmCaltday;
	if ($expire != 'true' && $expire != 'false') {
		// expire could be a day delta off from today
		//
		if ($expire <= 1780 && $expire >= -1780) {
			$expire_time=time() + ($expire * 86400);
			$emonth = date("m",$expire_time);
			$eyear = date("Y",$expire_time);
			$eday = date("d",$expire_time);
		}
	}
	// Handle stop after days.
	//
	$syear=0;
	$smonth=0;
	$sday=0;
	if ($stopafter != 'false') {
		// expire could be a day delta off from today
		//
		if ($stopafter <= 1780 && $stopafter >= -1780) {
			$stopafter_time=time() + ($stopafter * 86400);
			$smonth = date("m",$stopafter_time);
			$syear = date("Y",$stopafter_time);
			$sday = date("d",$stopafter_time);
		}
	}
	// weekstart should be from 0 to 6.
	// weekstart should be from 0 to 6.
	$weekstart=abs($weekstart) % 7;
	if ($reverse == 'true')
		$weekstart=(abs($weekstart - 6)+1) % 7;


	// Begin a new month.
	// If monthsahead is zero, then monthsahead is not used.  Don't go beyond this month.
	// If monthsback is zero, then monthsback is not used, starts with this month (or specified month).
	// If not zero, it is relative to whatever month is set to.

	// We're reindexing to consider months going from 0..11
	// Thus we subtract 1 from month.
	//
	$yearadjust=0;
	if ($reverse == 'true') {
		$startmonth=$month - 1 + $monthsahead;
		if ($startmonth > 11) {
			$yearadjust=floor($startmonth/12);
			$year=$year+$yearadjust;
		}
	} else {
		$startmonth=$month - 1 - $monthsback;
		if ($startmonth <= 0) {
			$yearadjust=floor($startmonth/12);
			$year=$year+$yearadjust;
		}
	}
	
	$out="";
	$zebraflag=0;

	// Adjust month from 0..11 back to 1..12
	//
	$month=((abs($yearadjust*12)+$startmonth) % 12)+1;

	// Computer total number of months to display
	//
	$totalmonths = $monthsback + $monthsahead;

	for ($mcount=0;$mcount <= $totalmonths; $mcount++) {

		// Calculate next month and prev month
		// Used for navigation forward and backward
		//
		if ($month == 12) {
			$nextmonth=1;
			if ($ayear == "ACAL")
				$nextyear="ACAL";
			else
				$nextyear=$year + 1;
				
		} else {
			$nextmonth=$month + 1;
			if ($ayear == "ACAL")
				$nextyear="ACAL";
			else
				$nextyear=$year;
		}
		if ($month == 1) {
			$prevmonth=12;
			if ($ayear == "ACAL")
				$prevyear="ACAL";
			else
				$prevyear=$year - 1;
		} else {
			$prevmonth=$month - 1;
			if ($ayear == "ACAL")
				$prevyear="ACAL";
			else
				$prevyear=$year;
		}

		// Get number of days in the month, day of week the first day starts on
		// and get month name
		//
		$totaldays = date("t",mktime(0,0,0,$month,1,pmcal_year($year)));
		if ($day > $totaldays) {
			$day=$PmCaltday;
		}
		if ($reverse == 'true') {
			$startdayofweek = date('w',mktime(0,0,0,$nextmonth,1,pmcal_year($nextyear))) - 1;
			if ($startdayofweek < 0) {
				$startdayofweek = 6;
			}
			$startdayofweek = abs($startdayofweek - 6);
		} else {
			$startdayofweek = date('w',mktime(0,0,0,$month,1,pmcal_year($year)));
		}

		// Format monthtitle.
		//
		$m=$month-1;
		if ($monthtitle[$m] == NULL) {
			// Do not use $day... use 1
			$mt = strftime($monthtitlefmt,mktime(0,0,0,$month,1,pmcal_year($year)));
		} else {
			// This isn't perfect.  Difficulties with apostrophes.
			$mt = strftime(urldecode($monthtitle[$m]),mktime(0,0,0,$month,1,pmcal_year($year)));
		}

		// Fill in array of textual day titles
		// I did this like this so the locale could be changed someday.
		//
		$d = $PmCaltday;
		for ($i=0; $i<7; $i++) {
			$titleindex = date("w",mktime(0,0,0,$month,$d,pmcal_year($year)));
			if ($dayofweektitle[$titleindex] != NULL)
				$dayofweektitlefmt=urldecode($dayofweektitle[$titleindex]);
			$title = strftime($dayofweektitlefmt,mktime(0,0,0,$month,$d,pmcal_year($year)));
			$dayofweektitle[$titleindex] = $title;
			$d++;
		}

		// It's necessary to force a line break before the (:pmcal:) output.
		//
		if ($callinks != 'false' && $caltype == 'normal') {
			$out.="\\\\\n";
			// Output Today link
			//
			$cl="${PmCalPrefix}todaylink";
			$out.="%class='$cl'%";
	 		$out.="[[$group?year=$PmCaltyear&amp;month=$PmCaltmonth&amp;day=$PmCaltday$urladd|";
	 		$out.="Today]]%%\n";

			// Output Extra Included Calendars links
			//
			$cl=sprintf("${PmCalPrefix}include%slink pmcalincludelink",$cal);
			foreach ($cals as $cal) {
				if ($cal != '') {
					$out.="%class='$cl'%";
					 $out.=sprintf("[[%s|%s]]\n",$cal,$cal);
				}
			}
		}

		if ($caltype == 'normal') {
			// Note: had to insert some forced returns... still some problems
			// with PmWiki and table begins I guess.
			//
			$out.="\\\\\n\\\\\n\n";
	
			// Output monthtitle, the banner with prev and next month links
			//
			$navprevout=strftime($navprev,mktime(0,0,0,$prevmonth,$day,pmcal_year($prevyear)));
			$navnextout=strftime($navnext,mktime(0,0,0,$nextmonth,$day,pmcal_year($nextyear)));

			$cl='pmcal';
			$out.="(:table ";
			$out.="class='$cl' ";
			$out.="border=1 cellspacing=0 cellpadding=3 width=100%:)\n";
			$out.="(:cellnr class='${PmCalPrefix}monthtitle' colspan=7:)\n";
			// Large one liner broken up into multiple appends
			$out.="%class='${PmCalPrefix}navlinks ${PmCalPrefix}navlinksprev'%";
			 $out.="[[$group?month=$prevmonth&amp;day=1&amp;year=$prevyear$urladd|$navprevout]] %%";
			 $out.="[[$group?month=$month&amp;day=$day&amp;year=$year$urladd|$mt]]";
			 $out.="%class='${PmCalPrefix}navlinks ${PmCalPrefix}navlinksnext'%";
			 $out.=" [[$group?month=$nextmonth&amp;day=1&amp;year=$nextyear$urladd|$navnextout]]%%\n";
		
			// Output days of week headings
			//
			$ctype="cellnr";
			for ($i=0; $i < 7; $i++) {
				$out.="(:$ctype ";
			 	 $out.="class='${PmCalPrefix}dayofweektitle' ";
			 	 $out.="width=10% ";
			 	 $out.=":)\n";
				$d=($i+$weekstart) % 7;
				if ($reverse == 'true') {
					$d=abs($d - 6);
				}
				$out.="$dayofweektitle[$d]\n";
				$ctype="cell";
			}

			// Output null cells, the empty cells before the first day
			// of the month.
			$ctype="cellnr";
			for ($i=0; $i < 7; $i++) {
				$d=($i+$weekstart) % 7;
				if ($d == $startdayofweek)
					break;
				$out.="(:$ctype class='${PmCalPrefix}null':)\n";
				$ctype="cell";
			}
		}
	
		// Output the calendar cells
		// Use a special class for today
		//
		$d=$startdayofweek;
		if ($reverse == 'true') {
			$iday=$totaldays;
		} else {
			$iday=1;
		}

		// Default flag for monitoring repeated days (included text calendar entries)
		$onedatedone=0;
		$lyear=0;
		$lmonth=0;
		$lday=0;

		// $i isn't the day now... it's just a counter.
		// $iday is the day now.
		//
		for ($i=1; $i<=$totaldays; $i++) {
			$dayindex = $d % 7;
			if ($ayear == 'ACAL') 
				$year='ACAL';
			else
				$year=sprintf("%04d",$year);
			$pmcalpagename=sprintf("%s.%s%02d%02d",$group,$year,$month,$iday);
			// Keep skip here in case we want to use it in caltype=normal??
			//
			$skip=0;
			if ($expire != 'false') {
				if ($year < $eyear) {
					$skip=1;
				} else if ($year == $eyear && $month < $emonth) {
					$skip=1;
				} else if ($year == $eyear && $month == $emonth && $iday < $eday) {
					$skip=1;
				}
			}
			if ($stopafter != 'false') {
				if ($year > $syear) {
					$skip=1;
				} else if ($year == $syear && $month > $smonth) {
					$skip=1;
				} else if ($year == $syear && $month == $smonth && $iday > $sday) {
					$skip=1;
				}
			}
			if ($zebra == "resetdaily")
				$zebraflag=0;

			$istoday=0;
			if ($year == $PmCaltyear && $month == $PmCaltmonth && $iday == $PmCaltday) {
				$istoday=1;
			}

			if ($caltype == 'normal') {
				if ($dayindex == $weekstart) {
					$ctype="cellnr";
				} else {
					$ctype="cell";
				}
				$cl="${PmCalPrefix}day";
				$dn="${PmCalPrefix}daynumber";
				if ($istoday) {
					$cl="${PmCalPrefix}today";
					$dn="$dn ${PmCalPrefix}todaynumber";
				}
				if (!PageExists($pmcalpagename)) {
					// Bizarre hack added due to PmWiki change.
					$dn=$dn . " ${PmCalPrefix}createtextlink";
				}
				$out.=sprintf("(:$ctype class='%s' height=80px :)\n",$cl);
				 $out.="%class='$dn'%";
				 $out.=sprintf("[[$group.%s%02d%02d?year=%s&amp;month=%s&amp;day=%s%s|%s]]\n",
				  $year,$month,$iday,$year,$month,$iday,$urladd,$iday);
				if ($includes != 'false' && PageExists($pmcalpagename)) {
					$MaxIncludes++;
					$out.=sprintf("\\\\\n\n(:include %s %s=%s:)\n",$pmcalpagename,$parasorlines,$normallinesparas);
				}
			} else if ($caltype == "text") {
				if (! $skip && PageExists($pmcalpagename)) {
					if ($zebra != 'false' && $zebraflag) 
						$out.="(:div id=${PmCalPrefix}zebra:)\n";
					if ($textlinks != 'false') {
						$cl="${PmCalPrefix}daytextlink";
						if ($istoday) {
							$cl="${PmCalPrefix}todaytextlink";
						}

						if ($onedate != 'false' && ($lyear != $year || $lmonth != $month || $lday != $iday)) {
							$onedatedone=0;
							$lyear=$year;
							$lmonth=$month;
							$lday=$iday;
						}

						if (! $onedatedone) {
							$formatteddate=strftime($textdatefmt,mktime(0,0,0,$month,$iday,pmcal_year($year)));
							if ($textlinks == 'nolinks') {
								$out.=sprintf("$PmCalTextLinkMark%%class='%s'%%[=%s=]\n",$cl,$formatteddate);
							} else {
								$out.=sprintf("$PmCalTextLinkMark%%class='%s'%%[[%s|[=%s=]]]\n",$cl,$pmcalpagename,$formatteddate);
							}
							if ($onedate != 'false') {
								$onedatedone=1;
							}
						}
					}
					if ($includes != 'false') {
						$MaxIncludes++;
						$out.=sprintf("(:include %s %s=%s:)\n",$pmcalpagename,$parasorlines,$otherlinesparas);
					}
					if ($zebra != 'false' && $zebraflag) 
						$out.="(:divend:)\n";
					if ($zebraflag) {
						$zebraflag=0;
					} else {
						$zebraflag=1;
					}
				}
	
			}

			// Include day pages from other calendars if present.
			// NOTE: The pmcalinclude class will only hold true if no new block construct is begun as a part of
			// the included page.
			//
			foreach ($cals as $cal) {
				if ($cal != '') {
					$pmcalincpagename=sprintf("%s.%s%02d%02d",$cal,$year,$month,$iday);
					if (! $skip && PageExists($pmcalincpagename)) {
						if ($caltype == "text") {
							if ($zebra != 'false' && $zebraflag) 
								$out.="(:div id=${PmCalPrefix}zebra:)\n";
							if ($onedate != 'false' && ($lyear != $year || $lmonth != $month || $lday != $iday)) {
								$onedatedone=0;
								$lyear=$year;
								$lmonth=$month;
								$lday=$iday;
							}
							if (! $onedatedone) {
								$formatteddate=strftime($textdatefmt,mktime(0,0,0,$month,$iday,pmcal_year($year)));
								$formattedtextcal=sprintf($textcalfmt,$cal);
								if ($textlinks != 'false') {
									$todaycl="";
									if ($istoday) {
										$todaycl=sprintf(" ${PmCalPrefix}include%stodaytextlink ${PmCalPrefix}includetodaytextlink",$cal);
									}
									if ($textlinks == 'nolinks') {
										$out.=sprintf("$PmCalIncludeTextLinkMark%%class='${PmCalPrefix}include%stextlink ${PmCalPrefix}includetextlink${todaycl}'%%[=%s %s=]\n",
											$cal,$formatteddate,$formattedtextcal);
									} else {
										$out.=sprintf("$PmCalIncludeTextLinkMark%%class='${PmCalPrefix}include%stextlink ${PmCalPrefix}includetextlink${todaycl}'%%[[%s|[=%s %s=]]]\n",
											$cal,$pmcalincpagename,$formatteddate,$formattedtextcal);
									}
								}
								if ($onedate != 'false') {
									$onedatedone=1;
								}
							} elseif ($onedate == "showcals") {
								$formattedtextcal=sprintf($textcalfmt,$cal);
								if ($textlinks != 'false') {
									$todaycl="";
									if ($istoday) {
										$todaycl=sprintf(" ${PmCalPrefix}subinclude%stodaytextlink ${PmCalPrefix}includetodaytextlink",$cal);
									}
									if ($textlinks == 'nolinks') {
										$out.=sprintf("$PmCalSubIncludeTextLinkMark%%class='${PmCalPrefix}subinclude%stextlink ${PmCalPrefix}subincludetextlink${todaycl}'%%[=%s=]\n",
											$cal,$formattedtextcal);
									} else {
										$out.=sprintf("$PmCalSubIncludeTextLinkMark%%class='${PmCalPrefix}subinclude%stextlink ${PmCalPrefix}subincludetextlink${todaycl}'%%[[%s|[=%s=]]]\n",
											$cal,$pmcalincpagename,$formattedtextcal);
									}
								}
							}
							if ($includes != 'false') {
								$MaxIncludes++;
								$out.=sprintf("(:include %s %s=%s:)\n",$pmcalincpagename,$parasorlines,$otherlinesparas);
							}
							if ($zebra != 'false' && $zebraflag) 
								$out.="(:divend:)\n";
							if ($zebraflag) {
								$zebraflag=0;
							} else {
								$zebraflag=1;
							}
						} else if ($caltype == "normal") {
							if ($includes != 'false') {
								$formattedcal=sprintf($calfmt,$cal);
								$todaycl="";
								if ($istoday) {
									$todaycl=sprintf(" ${PmCalPrefix}include%stoday ${PmCalPrefix}includetoday",$cal);
								}
								$out.=sprintf("%%class='${PmCalPrefix}include%s ${PmCalPrefix}include${todaycl}'%%[[%s|%s]]\n",
								 $cal,$pmcalincpagename,$formattedcal);
								$MaxIncludes++;
								$out.=sprintf("(:include %s %s=%s:)\n",$pmcalincpagename,$parasorlines,$normallinesparas);
							}
						}
					}
				}
			}
			foreach ($acals as $acal) {
				if ($acal != '') {
					$pmcalincpagename=sprintf("%s.ACAL%02d%02d",$acal,$month,$iday);
					if (! $skip && PageExists($pmcalincpagename)) {
						if ($caltype == "text") {
							if ($zebra != 'false' && $zebraflag) 
								$out.="(:div id=${PmCalPrefix}zebra:)\n";
							if ($onedate != 'false' && ($lyear != $year || $lmonth != $month || $lday != $iday)) {
								$onedatedone=0;
								$lyear=$year;
								$lmonth=$month;
								$lday=$iday;
							}
							if (! $onedatedone) {
								$formatteddate=strftime($textdatefmt,mktime(0,0,0,$month,$iday,pmcal_year($year)));
								$formattedtextcal=sprintf($textacalfmt,$acal);
								if ($textlinks != 'false') { 
									$todaycl="";
									if ($istoday) {
										$todaycl=sprintf(" ${PmCalPrefix}include%stodaytextlink ${PmCalPrefix}includetodaytextlink",$cal);
									}
									if ($textlinks == 'nolinks') {
										$out.=sprintf("$PmCalACALTextLinkMark%%class='${PmCalPrefix}include%stextlink ${PmCalPrefix}includetextlink${todaycl}'%%[=%s %s=]\n",
											$acal,$formatteddate,$formattedtextcal);
									} else {
										$out.=sprintf("$PmCalACALTextLinkMark%%class='${PmCalPrefix}include%stextlink ${PmCalPrefix}includetextlink${todaycl}'%%[[%s|[=%s %s=]]]\n",
											$acal,$pmcalincpagename,$formatteddate,$formattedtextcal);
									}
								}
								if ($onedate != 'false') {
									$onedatedone=1;
								}
							} elseif ($onedate == "showcals") {
								$formattedtextcal=sprintf($textacalfmt,$acal);
								if ($textlinks != 'false') {
									$todaycl="";
									if ($istoday) {
										$todaycl=sprintf(" ${PmCalPrefix}sbuinclude%stodaytextlink ${PmCalPrefix}subincludetodaytextlink",$cal);
									}
									if ($textlinks == 'nolinks') {
										$out.=sprintf("$PmCalSubACALTextLinkMark%%class='${PmCalPrefix}subinclude%stextlink ${PmCalPrefix}subincludetextlink${todaycl}'%%[=%s=]\n",
											$cal,$formattedtextcal);
									} else {
										$out.=sprintf("$PmCalSubACALTextLinkMark%%class='${PmCalPrefix}subinclude%stextlink ${PmCalPrefix}subincludetextlink${todaycl}'%%[[%s|[=%s=]]]\n",
											$cal,$pmcalincpagename,$formattedtextcal);
									}
								}
							}
							if ($includes != 'false') {
								$MaxIncludes++;
								$out.=sprintf("(:include %s %s=%s:)\n",$pmcalincpagename,$parasorlines,$otherlinesparas);
							}
							if ($zebra != 'false' && $zebraflag) 
								$out.="(:divend:)\n";
							if ($zebraflag) {
								$zebraflag=0;
							} else {
								$zebraflag=1;
							}
						} else if ($caltype == "normal") {
							if ($includes != 'false') {
								$formattedcal=sprintf($acalfmt,$acal);
								$todaycl="";
								if ($istoday) {
									$todaycl=sprintf(" ${PmCalPrefix}include%stoday ${PmCalPrefix}includetoday",$cal);
								}
								$out.=sprintf("%%class='${PmCalPrefix}include%s ${PmCalPrefix}include${todaycl}'%%[[%s|%s]]\n",
								 $acal,$pmcalincpagename,$formattedcal);
								$MaxIncludes++;
								$out.=sprintf("(:include %s %s=%s:)\n",$pmcalincpagename,$parasorlines,$normallinesparas);
							}
						}
					}
				}
			}
			if ($reverse == 'true') {
				$iday--;
			} else {
				$iday++;
			}
			$d++;
		}
	
		// Output null cells, the empty cells after the last day
		// of the month.
		//
		if ($caltype == 'normal') {
			$dayindex = ($d + 7 - $weekstart) % 7;
			if ($dayindex != 0) {
				for ($i=$dayindex; $i<7; $i++) {
					$ctype="cell";
					$out.="(:$ctype class='${PmCalPrefix}null':)\n";
				}
			}
	
			// End the calendar table
			//
			$out.="(:tableend:)\n";
		}
		if ($reverse == 'true') {
			$year=$prevyear;
			$month=$prevmonth;
		} else {
			$year=$nextyear;
			$month=$nextmonth;
		}
	} //end of  month loop
	PRR(); return $out;
}
?>
