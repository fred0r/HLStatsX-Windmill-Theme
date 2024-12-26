<?php
/*
HLstatsX Community Edition - Real-time player and clan rankings and statistics
Copyleft (L) 2008-20XX Nicholas Hastings (nshastings@gmail.com)
http://www.hlxcommunity.com

HLstatsX Community Edition is a continuation of 
ELstatsNEO - Real-time player and clan rankings and statistics
Copyleft (L) 2008-20XX Malte Bayer (steam@neo-soft.org)
http://ovrsized.neo-soft.org/

ELstatsNEO is an very improved & enhanced - so called Ultra-Humongus Edition of HLstatsX
HLstatsX - Real-time player and clan rankings and statistics for Half-Life 2
http://www.hlstatsx.com/
Copyright (C) 2005-2007 Tobias Oetzel (Tobi@hlstatsx.com)

HLstatsX is an enhanced version of HLstats made by Simon Garner
HLstats - Real-time player and clan rankings and statistics for Half-Life
http://sourceforge.net/projects/hlstats/
Copyright (C) 2001  Simon Garner
            
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.

For support and installation notes visit http://www.hlxcommunity.com
*/

    if (!defined('IN_HLSTATS')) {
        die('Do not access this file directly.');
    }
	
global $game;
    // Get list of active games	
	$resultGames = $db->query("
		SELECT
			code,
			name
		FROM
			hlstats_Games
		WHERE
			hidden='0'
		ORDER BY
			realgame, name ASC
	");

	?>
<?php        

$linkFormat = valid_request(strval($linkFormat), false);

// Iterate over array of game names and codes
while ($gamedata = $db->fetch_row($resultGames))
{
	$image = getImage("/games/$gamedata[0]/game");
	if ($image) {
		if ($game == $gamedata[0]) {
			$img_id = 'id="gameslist-active-game"';
		} else {
			$img_id = '';
		}

		switch ($linkFormat) {
			case "dropdown":
				echo "				<li class=\"flex\">\n";
				echo "					<a\n";
				echo "						class=\"inline-flex items-center w-full px-2 py-1 text-sm font-semibold transition-colors duration-150 rounded-md hover:bg-gray-100 hover:text-gray-800 dark:hover:bg-gray-800 dark:hover:text-gray-200\"\n";
				echo "						href=\"" . $g_options['scripturl'] . "?game=$gamedata[0]\">\n";
				echo "							<img class=\"w-6 h-6 mr-3\" src=\"" .$image['url'] ."\" alt=\"" . strtoupper($gamedata[0]) ."\" title=\"" . $gamedata[1] ."\" width=\"24\" height=\"24\">\n";
				echo "							<span>" . $gamedata[1] . "</span>\n";
				echo "					</a>\n";
				echo "				</li>\n";
				break;

			case "sidemenu":
				// display_menu_item_games($gamedata[1], $g_options['scripturl'] . "?game=$gamedata[0]" , $image['url']);
				display_menu_item($gamedata[1], $g_options['scripturl'] . "?game=$gamedata[0]" , 'caret-right');
				break;

			default:
				break;
		}
	}
}
?>
