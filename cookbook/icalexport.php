<?php
if (!defined('PmWiki'))
	exit ();

/***************************************************************************
File: icalexport.php 
Version: 1.31

Purpose
=======
This script is a companion to the PmWiki Wiki Calender Cookbook to 
export events into the iCal format. A user can subscribe to the exported 
file generated by the script.

For the Admins
==============
Requirements
------------
PmWiki 2.32+

The script assume that you have the Wiki Calendar cookbook installed what gives wiki sides with group and 
names like "Groupname/yearmonthday" -> "Calendar/20050801" for example

Install
-------
# put this script into your cookbook folder
# include it into your config.php: "include_once("$FarmD/cookbook/icalexport.php");"
# set the following parameter in your config.php file:
## $ICalCalendarGroup - the name of your wikilog group - default:"Calendar"
## $ICalCalendarTitle - the title shown if the calendar is subscribed - default:"PmWiki Calendar"
## $ICalTimeZone - the time zone the events belog to - default:"Europe/Berlin" 
## $ICalFileName - the name of calendarfile - default: same as $ICalCalendarGroup

The link to the generated file is $UploadDir/$ICalCalendarGroup/$ICalFileName.ics 
so per default it is http://yourserver/index.php/uploads/Calendar/Calendar.ics

Language Adaption
-----------------
The keywords "Begin", "End", "Location", and "Description" can be localized.

Usage
=====
On a wiki side each calendar entry has do be seperated by a horizontal rule: 

	!!Title of first the Event
	Begin: hh:mm \\
	End: hh:mm \\
	Location: \\
	Description: 
	
	----
	!!Title of the next Event
	...
	

For Developers
==============
What's going on here?
---------------------
If a page in the calendar group is saved, we read all pages in the group, look for the
calendar markup descriped above, translate it into ical protocol and write it out to a file.

ToDo
----
* See TODO:1 in source
* provide an input form for new events -> split by token maybe instead of ----

Author/Contributors
------------------- 
* Sebastian Siedentopf (schlaefer@macnews.de)

Copyright Notice
================
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA


***************************************************************************/

SDV($ICalCalendarGroup, 'Calendar');
SDV($ICalCalendarTitle, 'PmWiki Calendar');
SDV($ICalTimeZone, 'Europe/Berlin');
SDV($ICalFileName, $ICalCalendarGroup);

//The script hooks into the $EditFunctions defined in index.php. 
//If you manualy changed the $EditFunctions elsewhere you have to take care of this.
//You can avoid this by generaly using the (:icalexport:) Markup
$EditFunctions[] = 'icalexportfct';

//This markup is for debugging purposes only. See outcommented return statement at the end of icalexportfct().
//Markup('icalexport', '<block', '/\(:icalexport:\)/e', "Keep(icalexportfct('$pagename'))");

function icalexportfct($pagename) {
	global $ICalCalendarTitle, $ICalCalendarGroup, $ICalTimeZone, $ICalFileName;
	global $UploadDir, $UploadPrefixFmt, $UploadUrlFmt, $GroupPattern, $NamePattern, $FarmD, $IsPagePosted;
	global $ScriptUrl;

	// Script only executes if the wiki page is written
	if (!$IsPagePosted)
		return;

	// Script only executes if the currently saved page is in the $ICalCalendarGroup
	if (!preg_match("/".$ICalCalendarGroup."[\\/.]".$NamePattern."/i", $pagename))
		return;

	$pagelist = ListPages();
	$attachlist = array ();

	$locationi18n = FmtPageName("$[Location]", $pagename);
	$begini18n = FmtPageName("$[Begin]", $pagename);
	$endi18n = FmtPageName("$[End]", $pagename);
	$descriptioni18n = FmtPageName("$[Description]", $pagename);

	$out[] = "BEGIN:VCALENDAR\n";
	$out[] = "VERSION:2.0\n";
	$out[] = "X-WR-CALNAME:".$ICalCalendarTitle."\n";

	foreach ($pagelist as $pagename) {
		// we need only search events on wiki sides belonging to group $ICalCalendarGroup
		if (!preg_match("/".$ICalCalendarGroup.".".$NamePattern."/", $pagename))
			continue;

		// we read the page and ...
		$rcpage = ReadPage($pagename);
		// ... split the events apart on horizontal ruler
		$calenderEvents = explode("----", utf8_encode($rcpage['text']));

    $acalYear = date("Y", $rcpage['ctime']);

		foreach ($calenderEvents as $eventNumber => $event) {
			if (preg_match("/^(?:!!)(?!!)\s?(.*)/m", $event, $eventTitle)) {
				preg_match("/^(?:".$endi18n.":)\s*([0-9]{2}:[0-9]{2}).*$/m", $event, $eventEnd);
				preg_match("/^(?:".$begini18n.":)\s*([0-9]{2}:[0-9]{2}).*$/m", $event, $eventBegin);
				preg_match("/^(?:".$locationi18n.":)\s*(.*)$/m", $event, $eventLocation);
				preg_match("/^(?:".$descriptioni18n.":)\s*(.*)$/ms", $event, $eventDescription);

				$eventTitle = $eventTitle[1];
				$eventBegin = $eventBegin[1];
				$eventEnd = $eventEnd[1];
				// uh, why I did the str_replace here?
				$eventLocation = str_replace("\\", "", $eventLocation[1]);
				$eventDescription = str_replace("\\", "", $eventDescription[1]);

				$temp = explode(".", $pagename);
        $out[] = "BEGIN:VEVENT\n";

        if (preg_match("/^ACAL/", $temp[1])) {
          $temp[1] = str_replace("ACAL", date("Y", $acalYear), $temp[1]);
          $out[] = "RRULE:FREQ=YEARLY;INTERVAL=1\n";
        }

				if ($eventBegin && $eventEnd) {
					$beginn = str_replace(":", "", $eventBegin);
					$ende = str_replace(":", "", $eventEnd);
					$out[] = "DTSTART;TZID=".$ICalTimeZone.":".$temp[1]."T".$beginn."00\n";
					$out[] = "DTEND;TZID=".$ICalTimeZone.":".$temp[1]."T".$ende."00\n";
				} else {
					//we say it is a full day event and set the end to the next day
					// TODO:1 check if date exists and wrap to next month if not 
					$nextDay = $temp[1] + 1;
					$out[] = "DTSTART;VALUE=DATE:".$temp[1]."\n";
					$out[] = "DTEND;VALUE=DATE:".$nextDay."\n";
				}

				if ($eventLocation)
					$out[] = "LOCATION:".$eventLocation."\n";

				$title = MarkupToHTML($pagename, $eventTitle);
				$title = chop(preg_replace("/<.*?>/s", "", $title));
				$out[] = "SUMMARY:".$title."\n";

				//Every event needs a clear ID in the iCal protokoll
				$out[] = "UID:".$pagename."-".$eventNumber."-@".$_SERVER['HTTP_HOST']."\n";

				if ($eventDescription) {
					$infos = MarkupToHTML($pagename, $eventDescription);
					$infos = preg_replace("/<.*?>/s", "", $infos);
					$infos = chop($infos);
					$infos = preg_replace("/\n/s", "\\n", $infos);
					//Seperates the following URL by two lines
					$infos = $infos."\\n\\n";
				} else
					$infos = "";
				$out[] = "DESCRIPTION:".$infos.$ScriptUrl."/".$pagename."\n";

				$out[] = "END:VEVENT\n";

			}
		}
	}

	$out[] = "END:VCALENDAR\n";
	$pagetext = implode("", $out);

	//debugging help: prints out the written iCal file on the wikipage
	#return implode("<br>", $out);

	// writes out the ics file
	$filename = $UploadDir."/".$ICalCalendarGroup."/".$ICalFileName.".ics";
	$handle = fopen($filename, "w");
	fwrite($handle, $pagetext);
	fclose($handle);
	chmod($filename, 0777);

	return;
}
?>