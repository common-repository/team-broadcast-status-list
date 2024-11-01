=== Team Broadcast Status List ===
Contributors: Grant S., Ben C.
Donate Link: http://gsmithweb.com/donate.html
Tags: twitch, online, offline, status, stream, list
Author URI: http://www.gsmithweb.com
Plugin URI: http://www.gsmithweb.com/twitch-status-list/
Requires at least: 4.3
Tested Up To: 4.4.1
Stable Tag: 4.4.1
License: GPLv2

== Description ==
Team Broadcast Status List for Twitch displays the current online/offline status of a group of twitch accounts.  It will display it in a nice list style similar to friend lists within gaming applications.  You can easily enter as many twitch channel names to follow as you would like.  When the user is online it will move them to the top of the status list showing them online along with the current game they are playing.  Each channel name is a hyperlink that leads to their channel at twitch.tv.

== Installation ==
1. Extract the contents of the twitch-status.zip file.  
2. Place the twitch folder and contents into wp-content/plugins. 
3. Activate the plugin via the plugin section when logged in as admin.  
4. Add the widget to you site through the widget menu.
5. Custimize the widget by going to Settings > TBSL Settings

== Frequently Asked Questions ==

= Can you add a feature to this? =

Sure, just fill out my contact form at http://www.gsmithweb.com.  I will let you know if it is something I can do.

= I cant add channel names on version 1.2.0. How do I fix this? =

Version 1.2.0 added a bit more database functionality and you will need to deactivate the plugin and activate it again to have the database components install properly.

== Changelog ==

= 1.0.0 =
* first public release

= 1.1.0 =
* Added setting page to allow for custimization of colors
* Custimize header background color, header font color, and channel font colors
* Upload your own filler image for accounts that dont have a twitch avatar
* Added a checkbox to allow for showing only the online portion of the list

= 1.1.1 =
* Fixed fatal error when sanitizing hex color code entries

= 1.2.0 =
* Added Channel Settings page to add and sort channels in list
* Channels can be added one at a time now and will be tested for connection to twitch api when added
* Added the ability to activate specific channels to show
* Added the ability to sort the channels manually
* Added field to designate how many characters to allow for the current game name.  This will keep the lines from word wrapping when set appropriately for you sites sizing.
* Fixes possible css confliction

= 1.2.1 =
* Fixes a couple css bugs

= 1.2.2 =
* Fixes calls to database to use proper prefix

= 1.2.3 =
* Fixed fatal error when sanitizing hex color code entries

= 1.2.4 =
* Removed function to sanitize hex color codes that was causing fatal errors.

== Upgrade Notice ==

= 1.2.4 =
* If you are receiving errors when trying to change the colors update now.

== Screenshots ==
1. Admin widget menu
2. Admin widget settings page to add Channel names
3. Admin widget settings page to customize the apperance
4. Front end view of twitch status list
5. Front end view of twitch status list

== Support == 
I am planning to support this plugin so please report any bugs or questions by submitting them through my contact form at http://www.gsmithweb.com. I will do my best to solve any problems with my work.

If you would like to create a side tab as shown in our example you will need to also download the plugin called TAB SLIDE.  Which can be found at https://wordpress.org/plugins/tab-slide/.  We have modifyed the css for it.  If you would like to know how contact me.