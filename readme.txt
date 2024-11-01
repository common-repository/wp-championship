=== wp-championship ===
Contributors: tuxlog
Donate link: http://www.tuxlog.de/
Tags: championship, guessing, game, soccer, sport
Requires at least: 6.2
Tested up to: 6.6
Stable tag: 10.9
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

wp-championship is a plugin for wordpress letting you play a guessing game of a tournament e.g. soccer 

== Description ==
wp-championship is a plugin for wordpress letting you play a guessing game of a tournament e.g. soccer 

Features:

 + define number of groups, and points given to the winner, looser of each match
 + define teams and a team specific icons
 + define matches finalround and pre-final round
 + for each user you can set a substitute
 + sends mails about current game status (optional)
 + define game admins to edit match results   
 + shows various stats for admin and users
 + allows to arrange tippgroups as a kind of team guessing
 + access via XMLRPC is possible
 + various joker features (e.g. each player may select some matches and gets double points on it) BETA
 + a lot of data for various tournaments and leagues (German Bundesliga, WM 2018, EM 2021)
 + interface ti OpenLeagueDB to fetch team and match data automatically
 
Credits:
Thanks go to all who support this plugin, with  hints and suggestions for improvment and especially to Andy Chapman for doing a lot of tests



== Installation ==
	
	1.  Upload to your plugins folder, usually
	    `wp-content/plugins/`, keeping the directory structure intact
	    (i.e. `wp-championship.php` should end up in
	    `wp-content/plugins/wp-championship/`).

	1.  Activate the plugin on the plugin screen.

	1.  Visit the configuration page (wp-championship) to
            configure your guessing game

	1.  Optional: load teams and matches into your database using the import feature. 

	1.  Recommende for updates: Please remember to deactivate and activate the plugin once for database updates

== Frequently Asked Questions ==
= Where can I get further information about the plugin? =

There are several resources to visit:

* [A quick english reference for wp-championship][wpcsref] 
* [The german wp-championship page][wpcspost]
* [The german manual wp-championship][german] 

[german]:   http://www.tuxlog.de/wp-championship-handbuch/ "German manual for wp-championship"
[wpcsref]:  http://www.tuxlog.de/wordpress/2013/wp-championship-quickreference-english/ "quick reference for wp-championship"
[wpcspost]: http://www.tuxlog.de/wp-championship/


== Screenshots ==
1. wp-championship stats
2. wp-championship tipp dialog

== Changelog ==

= 2024-06-30 v10.9 =
* added number of tippgroup members to stats8
* added option to sort tippgroup stats (cs-stats8) by average
* added option to show fullnames in rnaking (cs-suerstats)
* fixed, show all entries for stats4 in tournament mode
* fxied a special case during final round update

= 2024-06-15 v10.8 =
* fixed frequently nonce problem on some of the statistic

= 2024-06-08 v10.7 =
* fixed some typo in german translations
* fixed label for matchid column for final round

= 2024-06-07 v10.6 =
* fixed new widget for not approved guessing game users

= 2024-06-07 v10.5 =
* added new widget to show infos about logged in user
* fixed typo in labels dialog
* fixed reset settings in xmlrpc labels
* fixed show/hide matchid column of tiptable
* fixed division by zero warning in log when tip is missing

= 2024-05-27 v10.4 =
* fixed add new user bug

= 2024-05-20 v10.3 =
* fixed missing tippspiel user bug

= 2024-04-04 v10.2 =
* fixed typo in XML RPC class
* added data for teams andmatches for the EM2024

= 2023-09-02 v10.1 =
* WP 6.2 ist now required 
* made source more compliant to the WP coding standards and using newer functions from WordPress
* fixed some typos in translation
* fixed some minor problems with tippgroup statistics
* changed default parametersfor easier configuration

= 2023-08-14 v10.0 =
* OpenLigaDB seems to have issues with there SOAP api, so I switched to json api.

= 2023-06-18 v9.9 =
* added data for women worldchampionship 2023
* fixed incompatibility with DB drop-in 
* added new option for cs_stats4 to show all tips and not only tips of finished matches
* added number of missed tips for each player in admin stats

= 2022-11-19 v9.8 =
* fixed column sorter on tippdialog for hidden columns
* extended statistics to show championtipp in ranking
* fixed statistic table heading

= 2022-10-21 v9.7 =
* fixed user auto create feature for new database structures
* fixed some santizing checks

= 2022-10-11 v9.6 =
* changed game data for the WM 2022 due to changes of the FIFA

= 2022-07-11 v9.5 =
* added data for WM 2022 in Qatar
* moved old data to "old"- directory

= 2022-06-10 v9.4 =
* fixed typo in setup data

= 2022-06-06 v9.3 =
* enhanced URL length for OL DB import
* added some missing security checks

= 2022-06-02 v9.2 =
* fix invalid insert of users with some settings
* added some missing nonce checks

= 2021-10-17 v9.1 =
* added hint if preeliminaries of OpenLigaDB import are not met
* added joker feature, you can set joker matches which gives the double points, either per user or for global for all users

= 2021-07-03 v9.0 =
* fixed finals calculation for non unique target teams
* fixed typo in EM2021 match data
* added cs_stats9 champion-tip stats
* added data for the german Bundesliga 2021/22

= 2021-06-12 v8.9 =
* fixed typos in EM2921 data
* fixed german translation
* fixed table toggle in cs_userstats

= 2021-05-31 v8.8 =
* fixed problem with adding new users on some setups

= 2021-05-12 v8.7 =
* fixed user settings dialog 
* fixed translation

= 2021-05-08 v8.6 =
* added new feature to hide finished games in tip dialog

= 2021-05-06 v8.5 =
* fixed typo in EM2021 data

= 2021-05-05 v8.4 =
* added data for EM 2021

= 2020-09-09 v8.3 =
* fixed icon representation
* fixed german translations

= 2020-09-06 v8.2 =
* fixed deprecated create_function call
* added data for German bundesliga 2020/2021
* added support to import data from OpenligaDB

= 2020-05-20 v8.1 =
* fixed WPLANG warnings
* fixed deprecated calls

= 2019-08-03 v8.0 =
* added data for German Bundesliga 2019/2020

= 2018-08-27 v7.9 =
* replaced deprecated create_function calls
* changed translation to be compatible with WordPress standards

= 2018-07-18 v7.8 =
* reduced reminder mails to one mail per check not one mail per match
* fixed tippgroup parameter passing for stats in some special cases
* added option to send mails as text not HTML 
* fixed typo in user tipp
* fixed a matchtime typo in Matches 59 and 60
* added data for German bundesliag 2018/2019

= 2018-06-11 v7.7 =
* add submit buttons after eacht section to support quicker tipps
* fixed tablesorter for different month in one round

= 2018-06-11 v7.6 =
* fixed a typo in the match data, matcfh 40 is on 24th not on 23th
* fixed a typo in a select to substitute another player


= 2018-05-31 v7.5 =
* fixed some typos and changed times in match data for WM2018

= 2018-05-26 v7.4 =
- added new headline for cs-stats8 and show message if tippgroups are not activated
- switched from user_nicename to display_name to show the name the user selected in the profile dialog of WordPress
- surpress some warnings of undefined variables
- fixed incompatibility with Outlook mails
- english translation updated. Thanks to Kristin. 


= 2018-01-14 v7.3 =
- added new data for the WM2018 in russia

= 2017-08-03 v7.2 =
* added new data for Bundesliga 2017/2018 - Thanks to Max

= 2017-01-22 v7.1 =
* added tippgroup table to remove tables function 
* fixed auto add admin as player
* fixed show only a number of matches, failed if less then wanted number of matchdays left
* fixed import data without truncating db-tables

= 2016-08-05 v7.0 =
* added penalty/bounus points feature for users
* switched database ddl to dbDelta syntax
* replaced deprecated function get_currentuserinfo
* added feature to only display a given number of match days
* added tippgroup admin dialog to manage tippgroups better
* added switch to activate/deactivate tippgroups
* added cs-stats8 - stats for tippgroups
* added new widget to show stats for tippgroups
* added option to select tippgroup from tipp dialog
* extended cs-stats1 with column for number of tipps
* added data for 1. Bundesliga 2016/2017 Thanks to Mario
* added data for 2. Bundesliga 2016/2017 Thanks to Matthias

= 2016-05-18 v6.9 =
* fixed show tips was not saved in admin dialog
* adopted default css to twentysixteen Theme

= 2016-05-13 v6.8 =
* fixed team data and replaced wrong icon

= 2016-05-10 v6.7 =
* fixed permissions for Switzerland icon
* fixed validation rule for separate threshold value
* made plugin independent from privileges to create temporary tables
* fixed typo in EM 2016 team name

= 2016-03-13 v6.6 =
* added Icons and SQL and CSV for EM 2016 in France -  Thanks to all who helped :-))

= 2016-03-01 v6.5 =
* fixed admin dialog XMLRPC options
* adopted mysql version check to mysqli api

= 2016-01-03 v6.4 =
* updated data for Bundesliga 2015/2016, dates and times were updated

= 2015-11-26 v6.3 =
* updated data for Bundesliga 2015/2016, dates and times were updated

= 2015-11-07 v6.2 =
* fixed php warning when file was called directly

= 2015-11-06 v6.1 =
* fixed ajax problem for stats4-stats7

= 2015-11-04 v6.0 =
* switched to ajax in backend
* removed core dependencies
* sanitize post calls

= 2015-10-29 v5.9 =
* fixed some potential sql injection leaks

= 2015-09-06 v5.8 =
* updated data for Bundesliga 2015/2016, dates and times were updated

= 2015-08-15 v5.7 =
* updated data for Bundesliga 2015/2016, dates and times were updated

= 2015-06-27 v5.6 =
* added data for Bundesliga 2015/2016
* fixed import (imported zero dates sometimes)


= 2015-04-06 v5.5 =
* updated data for Bundesliga 2014/2015, final update

= 2014-12-25 v5.4 =
* updated data for Bundesliga 2014/2015, dates and times were updated

= 2014-10-23 v5.3 =
* fixed data for Bundesliga 2014/2015 dates were not loaded correctly

= 2014-09-30 v5.2 =
* updated data for Bundesliga 2014/15

= 2014-09-12 v5.1 =
* updated data for Bundesliga 2014/15 

= 2014-09-01 v5.0 =
* fixed wrong warning message when saving tipps (for champion tip)

= 2014-08-21 v4.9 =
* fixed quotes in Bundesliga csv file

= 2014-08-15 v4.8 =
* added parameter for one side tipp, choice between with tendency only, withour tendency only and always
* added parameter to set final winner in case you are guessing on results after 90 Minutes which can be tied in the final
* added german Bundesliga 2014/2015 as SQL and CSV. Thanks to Manuel

= 2014-06-17 v4.7 =
* added responsive css for tipp table (uses CSS3)
* replaced use of gmt_offset for mailservice
* if tipp could not be saved because match has already started the wrong tip was displayed one time 
* fixed calc points for one side goal tip

= 2014-06-06 v4.6 =
* fixed typos in csv files

= 2014-05-24 v4.5 =
* added csv import and export for teams and matches
* updated jquery.tooltip.js to v1.1 and made it work with bootstrap themes
* fixed typo when deleting preround matches
* updated manual
* removed dead code
* fixed problem while editing final matches

= 2014-05-05 v4.4 =
* fixed some table names in sqls to vars from globals.php
* fixed bug for direct compare for german bundesliga
* fixed typo for WM2014 Match 59
* added support link
* added contextual help

= 2014-04-15 v4.3 =
* fixed SQL for WM2014
* added SQL for WM2014 with CEST timezones
* fixed delete team in team edit dialog

= 2014-02-15 v4.2 =
* added teams andmatches for the soccer-world-championship 2014 (Thanks to Dieter Pfenning for all your work)
* made many functions pluggable (you can overwrite them from within e.g. functions.php)
* you can now store icons in YourThemefolder/wp-championship/icons
* you can now store xwp-championship.css in YourThemefolder/wp-championship/ too
* you can now override cs-stats.php ,cs-groupstats.php and cs-matchstats.php if you place a file with the same name in YourThemefolder/wp-championship/
* removed jquery.dimensions enqueue since it is part of jquery now
* removed old icons

= 2013-10-23 v4.1 =
* fixed adding a match to the final round
* fixed cs-stats5 results were shifted when player did not enter a tip for every match
* removed timezone calc for client side due to geoip service was shut down
* added english quickreference guide

= 2013-08-04 v4.0 =
* fixed setup because default admin entry was not correct
* added icons and sql for Bundesliga 2013/2014

= 2012-11-24 v3.9 =
* add the possibility to give each team a penalty in points

= 2012-10-09 v3.8 =
* load javascript in backend only on wp-championship pages
* add cs-stats7 Guesser of the month stats
* changed enqueue script to proper use with plugins_url

= 2012-07-06 v3.7 =
* added new option for goalsum tip you only hit if your tipp is euqal goalsum not equal or greater
* added sql and icons for Bundesliga 2012/2013
* added columnd points to stats4
* added stats6 to show all matches from one team
* extended tipp-aids in tippdialog with stats6 when hovering over the teamname

= 2012-06-25 v3.6 =
* fixed the widget display name 
* extended goal sum tipp to accept a sum of 0
* fixed goalsum tipp could only be entered for all games at once
* points for goalsum tipp where assigned when tipp was >= result, changed this to =
* tipps from other players are now shown directly after the match has started in stats4
* extended selection filter to select from match and player 
* fixed a bug in team stats, count of wins and loss

= 2012-06-10 v3.5 =
* fixed substitue list contained all usernames not only the players in tip dialog
* fixed wrong trend was shown at first result
* fixed a numbering issue with the widget
* fixed display average in the widget
* trend was lost when admin entered tips but no results
* mailreminder was not send due to a typo in date variable

= 2012-06-03 v3.4 =
* fixed problem with table sorting option
* fixed some typos in readme.txt

= 2012-06-01 v3.3 =
* fixed xmlrpc statistic 7 (all tables were displayed independent of the parameter)
* fixed first tipp when user was not in cs_users


= 2012-05-20 v3.2 =
* added hover table feature to tipp dialog (shows an ajax like group table when hovering over the group id)
* fixed uninitialized value fpr cs_sort_tipp

= 2012-05-18 v3.1 =
* fixed deprecated use of user_level (leads to many many php notices)
* fixed problem with entering a wrong tipp as first tipp (all tips were set to -1)
* fixed html error in confirmation mail
* removed deprecated parameter from add_option in setup.php (leads to many php notices during activation)
* clean up some php notices on the admin dialogs

= 2012-05-12 v3.0 =
* fixed button style in admin dialog
* added XMLRPC interface to wp-championship for use with smartphone apps
* fixed width of menu entry in wp admin menu
* fixed widget layout on twentyeleven
* adopt default css to twentyeleven
* added demo mode (activate in wp-championship.php)
* fixed an incompatibility with wordpress MU and register_activation/deactivation
* adopted to HTML5 for 3.3 compatibility
* added SQL for EM2012 (folder sql)
* fixed heading for location in tipp table
* fixed confirmation mail (teams and time were not displayed, for finals set team to n/a)
* fixed wrong message (Mail could not be sent...) when deactivating the mailreceipt
* added auto add feature to automatically add new users to guessing game (see admin dialog to activate)
* fixed mailservice receipt was only send for admins
* cleanup php warning (a bit)
* cleanup html validity
* fixed stats4 when all players were selected only part of the tipps was shown


= 2011-08-26 v2.9=
* added the possibility to select all users in cs-stats4
* fixed problem adding final match with bundesliga mode
* fixed problem with jquery 1.4.4 and trigger in wordpress 3.1
* added cs-stats5 an compact overview over one day
* fixed that field spieltag was deleted when editing match
* added shortname for teams for use in reports and stats
* added widget for display the current ranking
* fixed error in winner calculation in Bundesliga-mode
* added confirmation mail feature
* fix ranking when matches were deleted and old tipps were still present
* added group feature for players (every user can be member of a tippgroup, stats can be calculated only for one group or for all)
* fixed an incompatibility with FF6 and Wordpress adminbar

= 2010-11-08 v2.8 =
* fixed debug info dump
* fixed stats to work with non standard wp installations
* fixed pie chart calculating correct percentage
* added charset /collation to setup
* fixed wrong collation in ajax data transfer

= 2010-10-31 v2.7 =
* added label configuration admin dialog 
* conserve wp-championship.css during autoupdate
* added basic statistics (insert [cs-stats1], [cs-stats2], [cs-stats3] or [cs-stats4] into a page or post) see documentation for further information

= 2010-08-20 v2.6 =
* fixed collision with older wp-championship version and buddypress

= 2010-08-06 v2.5 =
* removed invalid table class from default css file
* added collapse/expand per day for Bundesligamodus
* added admin switch to lock round1-tipps generally
* added spieltag attribute to matches for mapping in liga-mode
* added trend barometer for ranking table and mailservice
* only output finalround on stats page when final matches exists

= 2010-07-09 v2.4 =
* fixed missing calculation of next match in finalround (match for third place) again :-(

= 2010-07-07 v2.3 =
* fixed invalid xhtml in tipp-dialog
* fixed missing calculation of next match in finalround (match for third place)

= 2010-06-20 v2.2 =
* fixed invalid xhtml tableheader in ranking mail
* fixed sql for result calculation in group tables

= 2010-06-11 v2.1 =
* fixed some translation tags
* fixed warning message for browser timezone when ip is localhost
* fixed WM2010 location of match #56 from Johannisburg to Kapstadt
* fix saving other tipps during a game was not possible

= 2010-06-01 v2.0 =
* fixed output of nonce field, was probably a collision with other plugins 
* fixed sort order on tipp dialog for finalround
* fixed points calculation for oneside tipp in conjunction with tendency tipp(> instead of >=)
* fixed goalsum tipp for tipps with no points yet
* fixed link to substitue was not correct with all permalink settings in wordpress

= 2010-05-19 v1.9 =
* fixed missing cr in mail header (which causes some mailservers to deny mailtransfer)
* fixed check for championtipp against current time, used server time instead of blog-time. this lead to problems if server and blog time are in different timezones

= 2010-05-09 v1.8 =
* fixed warning message when allow_url_fopen was Off

= 2010-05-08 v1.7 =
* added option for oneside tipp only hits if tendency is correct
* added auto goalsum tipp (tipp will be calcualted from result tipp (sum of goals)

= 2010-04-27 v1.6 =
* fixed layout in readme.txt
* added screenshots
* fixed, championtipp was not updated when no other field was changed (tippdialog)
* added error message when championtipp should be changed after first match start
* added feature to gain points for a only on one side correct tip
* added feature to gain points for a sum of goals/points during for each match
* fixed save user data when using substitutes
* added feature to gain points if one side of the tipp is exactly correct
* fixed button class on tipp page
* added switch to enable/disable floating link

= 2010-04-15 v1.5 =
* adopt to wordpress 2.9.2
* added sql for WM2010 (teams, matches)
* added mail reminder for upcoming matches
* added italian translation (thanks to Davide :-) )
* added sortable tables to tipp page
* added foldable tables to results page
* added tooltip to matchtime column showing the starttime based on browsers timezone
* added animated flags (thanks to Andy)
* extended input checking on tipp page
* mark not accepted values in red (tipp page)
* added auto-floating "Top of page" link
* make sure only one admin entry is added by default
* added menu icon - the worldcup :-)

= 2009-03-29 v1.4 =
* adding a first draft of english translation

= 2008-08-02 v1.3 =
* corrected a bit of incorrect xhtml
* fixed wrong timestamp for championtime
* check tipptime for championtime in case of injection 
* mark admin as tippspiel admin during install
* add switch to disable substitute feature
* added nonce check
* added championship modus for the german bundesliga
* extended classification boards with some stats

= 2008-06-18 v1.2 =
* added the possibility to define mixed finalround matches (from groups and match)
* fixed a problem to store user settings when no champion tipp was given
* fixed an error when using a substitute
* added separate trigger for recalculating points and finals in admin dialog

= 2008-06-16 v1.1 =
* fixed some spelling mistakes
* fixed xhtml for tipp page
* fixed problem with saving user options
* fixed html in admin dialog
* fixed sql error when updating finals
* corrected type error in team dialog

= 2008-06-11 v1.0 =
* send mails only when admin is entering results (not when admin entered tipps)
* added mailservice trigger in admin dialog
* corrected order in group classification
* consider wordpress timezone for time checking
* store only new or changed tipps
* corrected pulldown menu for champion tipp in user dialog
* fixed points calculation for tendency and tied games (when no tipp was entered points for tied games were added)

= 2008-06-01 v0.9 =
* fixed mistake in em2008.sql
* fixed problem creating matches

= 2008-05-31 v0.8=
* read correct wordpress table prefix
* added possibility to remove wp-championship db tables
* the finals will now be calculated each time a results is changed
* you can overrule pre-elimination classification manual by setting the standing in the match dialog
* when creating a new user in user dialog check if user allready exists

= 2008-05-27 v0.7=
* extended data validation for input fields (tipps and results)
* prepare for translation, added .pot file
* corrected spelling errors

= 2008-05-22 v0.6=
* initial alpha release
