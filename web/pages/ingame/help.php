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

    $server_id = 1;
	if (isset($_GET['server_id']) && is_numeric($_GET['server_id'])) {
		$server_id = valid_request($_GET['server_id'], true);
	}

	display_page_title('Help');
	display_ingame_menu();

?>
<div class="p-4 mb-8 text-sm px-4 py-3 bg-white rounded-lg shadow-md dark:bg-gray-800">

	<table style="width:100%;border:0;padding:1px;border-spacing:0;">
		<tr class="data-table-head">
			<td class="fSmall data-table" colspan="3"><?php display_page_subtitle('Commands display the results ingame'); ?></td>
		</tr>
		<tr class="bg1">
			<td class="fNormal">rank [skill, points, place (to all)]</td>
			<td class="fNormal">=</td>
			<td class="fNormal">Current position</td>
		</tr>
		<tr class="bg1">
			<td class="fNormal">kpd [kdratio, kdeath (to all)]</td>
			<td class="fNormal">=</td>
			<td class="fNormal">Total player statistics</td>
		</tr>
		<tr class="bg1">
			<td class="fNormal">session [session_data (to all)]</td>
			<td class="fNormal">=</td>
			<td class="fNormal">Current session statistics</td>
		</tr>
		<tr class="bg1">
			<td class="fNormal">next</td>
			<td class="fNormal">=</td>
			<td class="fNormal">Players ahead in the ranking.</td>
		</tr>
		<tr class="data-table-head">
			<td class="fSmall data-table" colspan="3"><?php display_page_subtitle('Commands display the results in window'); ?></td>
		</tr>
		<tr class="bg1">
			<td class="fNormal">load</td>
			<td class="fNormal">=</td>
			<td class="fNormal">Statistics from all servers</td>
		</tr>
		<tr class="bg1">
			<td class="fNormal">status</td>
			<td class="fNormal">=</td>
			<td class="fNormal">Current server status</td>
		</tr>
		<tr class="bg1">
			<td class="fNormal">servers</td>
			<td class="fNormal">=</td>
			<td class="fNormal">List of all participating servers</td>
		</tr>
		<tr class="bg1">
			<td class="fNormal">top20 [top5, top10]</td>
			<td class="fNormal">=</td>
			<td class="fNormal">Top-Players</td>
		</tr>
		<tr class="bg1">
			<td class="fNormal">clans</td>
			<td class="fNormal">=</td>
			<td class="fNormal">Clan ranking</td>
		</tr>
		<tr class="bg1">
			<td class="fNormal">cheaters</td>
			<td class="fNormal">=</td>
			<td class="fNormal">Banned players</td>
		</tr>
		<tr class="bg1">
			<td class="fNormal">statsme</td>
			<td class="fNormal">=</td>
			<td class="fNormal">Statistic summary</td>
		</tr>
		<tr class="bg1">
			<td class="fNormal">weapons [weapon]</td>
			<td class="fNormal">=</td>
			<td class="fNormal">Weapons usage</td>
		</tr>
		<tr class="bg1">
			<td class="fNormal">accuracy</td>
			<td class="fNormal">=</td>
			<td class="fNormal">Weapons accuracy</td>
		</tr>
		<tr class="bg1">
			<td class="fNormal">targets [target]</td>
			<td class="fNormal">=</td>
			<td class="fNormal">Targets hit positions</td>
		</tr>
		<tr class="bg1">
			<td class="fNormal">kills [kill, player_kills]</td>
			<td class="fNormal">=</td>
			<td class="fNormal">Kill statistics (5 or more kills)</td>
		</tr>
		<tr class="bg1">
			<td class="fNormal">actions [action]</td>
			<td class="fNormal">=</td>
			<td class="fNormal">Server actions summary</td>
		</tr>
		<tr class="bg1">
			<td class="fNormal">help [cmd, cmds, commands]</td>
			<td class="fNormal">=</td>
			<td class="fNormal">Help screen</td>
		</tr>
		<tr class="data-table-head">
			<td class="fSmall data-table" colspan="3"><?php display_page_subtitle('Commands to set your user options'); ?></td>
		</tr>
		<tr class="bg1">
			<td class="fNormal">hlx_auto clear|start|end|kill command</td>
			<td class="fNormal">=</td>
			<td class="fNormal">Auto-Command on specific event (on death, roundstart, roundend)</td>
		</tr>
		<tr class="bg1">
			<td class="fNormal">hlx_display 0|1</td>
			<td class="fNormal">=</td>
			<td class="fNormal">Enable or disable displaying console events.</td>
		</tr>
		<tr class="bg1">
			<td class="fNormal">hlx_chat 0|1</td>
			<td class="fNormal">=</td>
			<td class="fNormal">Enable or disable the displaying of global chat events(if enabled).</td>
		</tr>
		<tr class="bg1">
			<td class="fNormal">/hlx_set realname|email|homepage [value]</td>
			<td class="fNormal">=</td>
			<td class="fNormal">(Type in chat, not console) Sets your player info.</td>
		</tr>
		<tr class="bg1">
			<td class="fNormal">/hlx_hideranking</td>
			<td class="fNormal">=</td>
			<td class="fNormal">(Type in chat, not console) Makes you invisible on player rankings, unranked.</td>
		</tr>
    </table>
</div>
<br>