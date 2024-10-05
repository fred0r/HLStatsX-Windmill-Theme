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
	
	// Contents
	
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
	
	$num_games = $db->num_rows($resultGames);
	$redirect_to_game = 0;  
	$game = (!empty($_GET['game'])) ? valid_request($_GET['game'], false) : null;

	if ($num_games == 1 || !empty($game)) {
		$redirect_to_game++;
		if ($num_games == 1) {
			list($game) = $db->fetch_row($resultGames);
		}
		
		include(PAGE_PATH . '/game.php');
	} else {
		unset($_SESSION['game']);
		
		pageHeader(array('Contents'), array('Contents' => ''));
		include(PAGE_PATH . '/voicecomm_serverlist.php');
		printSectionTitle('Games');
	?>

		<div class="subblock">
		
			<table class="data-table">
			
				<tr class="data-table-head">
					<td class="fSmall" width="60%" align="left">&nbsp;Game</td>
					<td class="fSmall" width="10%" align="center">&nbsp;Players</td>
					<td class="fSmall" width="20%" align="center">&nbsp;Top Player</td>
					<td class="fSmall" width="10%" align="center">&nbsp;Top Clan</td>
				</tr>
				
<?php
        $nonhiddengamestring = "(";
		while ($gamedata = $db->fetch_row($resultGames))
		{
			$nonhiddengamestring .= "'$gamedata[0]',";
			$result = $db->query("
				SELECT
					playerId,
					lastName,
					activity
				FROM
					hlstats_Players
				WHERE
					game='$gamedata[0]'
					AND hideranking=0
				ORDER BY
					".$g_options['rankingtype']." DESC,
					(kills/IF(deaths=0,1,deaths)) DESC
				LIMIT 1
			");
		
			if ($db->num_rows($result) == 1)
			{
				$topplayer = $db->fetch_row($result);
			}
			else
			{
				$topplayer = false;
			}
					
			$result = $db->query("
				SELECT
					hlstats_Clans.clanId,
					hlstats_Clans.name,
					AVG(hlstats_Players.skill) AS skill,
					AVG(hlstats_Players.kills) AS kills,
					COUNT(hlstats_Players.playerId) AS numplayers
				FROM
					hlstats_Clans
				LEFT JOIN
					hlstats_Players
				ON
					hlstats_Players.clan = hlstats_Clans.clanId
				WHERE
					hlstats_Clans.game='$gamedata[0]'
					AND hlstats_Clans.hidden = 0
					AND hlstats_Players.hideranking=0
				GROUP BY
					hlstats_Clans.clanId
				HAVING
					".$g_options['rankingtype']." IS NOT NULL
					AND numplayers >= 3
				ORDER BY
					".$g_options['rankingtype']." DESC
				LIMIT 1
			");

			if ($db->num_rows($result) == 1)
			{
				$topclan = $db->fetch_row($result);
			}
			else
			{
				$topclan = false;
			}

			$result= $db->query("
				SELECT
					SUM(act_players) AS `act_players`,                                
					SUM(max_players) AS `max_players`
				FROM
					hlstats_Servers
				WHERE
					hlstats_Servers.game='$gamedata[0]'
			");
							
			$numplayers = $db->fetch_array($result);
			if ($numplayers['act_players'] == 0 and $numplayers['max_players'] == 0)
				$numplayers = false;
			else
				$player_string = $numplayers['act_players'].'/'.$numplayers['max_players'];
?>				
				<tr class="game-table-row">
					<td class="game-table-cell" style="height:30px">
						<div style="float:left;line-height:30px;" class="fHeading">&nbsp;<a href="<?php echo $g_options['scripturl'] . "?game=$gamedata[0]"; ?>"><img src="<?php
			$image = getImage("/games/$gamedata[0]/game");
			if ($image)
				echo $image['url'];
			else
				echo IMAGE_PATH . '/game.gif';
               ?>"  style="margin-left: 3px; margin-right: 4px;" alt="Game" /></a><a href="<?php echo $g_options['scripturl'] . "?game=$gamedata[0]"; ?>"><?php echo $gamedata[1]; ?></a>
						</div>
						<div style="float:right;">
							<div style="margin-left: 3px; margin-right: 4px; vertical-align:top; text-align:center;"><a href="<?php echo $g_options['scripturl'] . "?mode=clans&amp;game=$gamedata[0]"; ?>"><img src="<?php echo IMAGE_PATH; ?>/clan.gif" alt="Clan Rankings" /></a></div>
							<div style="vertical-align:bottom; text-align:left;">&nbsp;<a href="<?php echo $g_options['scripturl'] . "?mode=clans&amp;game=$gamedata[0]"; ?>" class="fSmall">Clans</a>&nbsp;&nbsp;</div>
						</div>
							
						<div style="float:right;">
							<div style="margin-left: 3px; margin-right: 4px; vertical-align:top; text-align:center;"><a href="<?php echo $g_options['scripturl'] . "?mode=players&amp;game=$gamedata[0]"; ?>"><img src="<?php echo IMAGE_PATH; ?>/player.gif" alt="Player Rankings" /></a></div>
							<div style="vertical-align:bottom; text-align:left;">&nbsp;<a href="<?php echo $g_options['scripturl'] . "?mode=players&amp;game=$gamedata[0]"; ?>" class="fSmall">Players</a>&nbsp;&nbsp;</div>
						</div>
					</td>
					<td class="game-table-cell" style="text-align:center;"><?php 
			if ($numplayers)
			{
				echo $player_string;
			}
			else
			{
				echo '-';
			}
					?>
					</td>
					<td class="game-table-cell" style="text-align:center;"><?php
			if ($topplayer)
			{
				echo '<a href="' . $g_options['scripturl'] . '?mode=playerinfo&amp;player='
					. $topplayer[0] . '">'.htmlspecialchars($topplayer[1], ENT_COMPAT).'</a>';
			}
			else
			{
				echo '-';
			}
					?></td>
					<td class="game-table-cell" style="text-align:center;"><?php
			if ($topclan)
			{
				echo '<a href="' . $g_options['scripturl'] . '?mode=claninfo&amp;clan='
					. $topclan[0] . '">'.htmlspecialchars($topclan[1], ENT_COMPAT).'</a>';
			}
			else
			{
				echo '-';
			}
					?></td>
				</tr>
<?php
		}
?>	
				</table>
		
		</div><br /><br />
		<br />
		
<?php
		
		$nonhiddengamestring = preg_replace('/,$/', ')', $nonhiddengamestring);
		
		$result = $db->query("SELECT COUNT(playerId) FROM hlstats_Players WHERE game IN $nonhiddengamestring");
		list($num_players) = $db->fetch_row($result);
		$num_players = number_format($num_players);

		$result = $db->query("SELECT COUNT(clanId) FROM hlstats_Clans WHERE game IN $nonhiddengamestring");
		list($num_clans) = $db->fetch_row($result);
		$num_clans = number_format($num_clans);

		$result = $db->query("SELECT COUNT(serverId) FROM hlstats_Servers WHERE game IN $nonhiddengamestring");
		list($num_servers) = $db->fetch_row($result);
		$num_servers = number_format($num_servers);
		
		$result = $db->query("SELECT SUM(kills) FROM hlstats_Servers WHERE game IN $nonhiddengamestring");
		list($num_kills) = $db->fetch_row($result);
		$num_kills = number_format($num_kills);

		$result = $db->query("
			SELECT 
				eventTime
			FROM
				hlstats_Events_Frags
			ORDER BY
				id DESC
			LIMIT 1
		");
		list($lastevent) = $db->fetch_row($result);
/*
?>

		<div class="subblock">
		
			<ul>
				<li><?php
					echo "<strong>$num_players</strong> players and <strong>$num_clans</strong> clans "
						. "ranked in <strong>$num_games</strong> games on <strong>$num_servers</strong>"
						. " servers with <strong>$num_kills</strong> kills."; ?></li>
<?php
		if ($lastevent)
		{
			echo "\t\t\t\t<li>Last Kill <strong> " . date('g:i:s A, D. M. d, Y', strtotime($lastevent)) . "</strong></li>";
		}
?>
				<li>All statistics are generated in real-time. Event history data expires after <strong><?php echo $g_options['DeleteDays']; ?></strong> days.</li>
			</ul>
		</div>
<?php
	}
*/
?>

<!--
<div class="grid gap-6 mb-8 md:grid-cols-2">
	<div
	class="min-w-0 p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800"
	>
		<h4 class="mb-4 font-semibold text-gray-600 dark:text-gray-300">
			Revenue
		</h4>
		<p class="text-gray-600 dark:text-gray-400">
			Lorem ipsum dolor sit, amet consectetur adipisicing elit.
			Fuga, cum commodi a omnis numquam quod? Totam exercitationem
			quos hic ipsam at qui cum numquam, sed amet ratione! Ratione,
			nihil dolorum.
		</p>
	</div>
	<div
	class="min-w-0 p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800"
	>
		<h4 class="mb-4 font-semibold text-gray-600 dark:text-gray-300">
			Colored card
		</h4>
		<p class="text-gray-600 dark:text-gray-400">
			Lorem ipsum dolor sit, amet consectetur adipisicing elit.
			Fuga, cum commodi a omnis numquam quod? Totam exercitationem
			quos hic ipsam at qui cum numquam, sed amet ratione! Ratione,
			nihil dolorum.
		</p>
	</div>
</div>

	-->

            <!-- Responsive cards -->
            <h4
              class="mb-4 text-lg font-semibold text-gray-600 dark:text-gray-300"
            >
				General Statistics
            </h4>
            <div class="grid gap-6 mb-8 md:grid-cols-2 xl:grid-cols-4">
              <!-- Card -->
              <div
                class="flex items-center p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800"
              >
                <div
                  class="p-3 mr-4 text-orange-500 bg-orange-100 rounded-full dark:text-orange-100 dark:bg-orange-500"
                >
                  <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path
                      d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z"
                    ></path>
                  </svg>
                </div>
                <div>
                  <p
                    class="mb-2 text-sm font-medium text-gray-600 dark:text-gray-400"
                  >
                    Total Players
                  </p>
                  <p
                    class="text-lg font-semibold text-gray-700 dark:text-gray-200"
                  >
				  <?php echo $num_players ?>
                  </p>
                </div>
              </div>
              <!-- Card -->
              <div
                class="flex items-center p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800"
              >
                <div
                  class="p-3 mr-4 text-green-500 bg-green-100 rounded-full dark:text-green-100 dark:bg-green-500"
                >
                  <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path
                      fill-rule="evenodd"
                      d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z"
                      clip-rule="evenodd"
                    ></path>
                  </svg>
                </div>
                <div>
                  <p
                    class="mb-2 text-sm font-medium text-gray-600 dark:text-gray-400"
                  >
                    Total Clans
                  </p>
                  <p
                    class="text-lg font-semibold text-gray-700 dark:text-gray-200"
                  >
				  	<?php echo $num_clans ?>
                  </p>
                </div>
              </div>
              <!-- Card -->
              <div
                class="flex items-center p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800"
              >
                <div
                  class="p-3 mr-4 text-blue-500 bg-blue-100 rounded-full dark:text-blue-100 dark:bg-blue-500"
                >
                  <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path
                      d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3zM16 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM6.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3z"
                    ></path>
                  </svg>
                </div>
                <div>
                  <p
                    class="mb-2 text-sm font-medium text-gray-600 dark:text-gray-400"
                  >
                    Total Games
                  </p>
                  <p
                    class="text-lg font-semibold text-gray-700 dark:text-gray-200"
                  >
                    <?php echo $num_games ?>
                  </p>
                </div>
              </div>
              <!-- Card -->
              <div
                class="flex items-center p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800"
              >
                <div
                  class="p-3 mr-4 text-teal-500 bg-teal-100 rounded-full dark:text-teal-100 dark:bg-teal-500"
                >
                  <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path
                      fill-rule="evenodd"
                      d="M18 5v8a2 2 0 01-2 2h-5l-5 4v-4H4a2 2 0 01-2-2V5a2 2 0 012-2h12a2 2 0 012 2zM7 8H5v2h2V8zm2 0h2v2H9V8zm6 0h-2v2h2V8z"
                      clip-rule="evenodd"
                    ></path>
                  </svg>
                </div>
                <div>
                  <p
                    class="mb-2 text-sm font-medium text-gray-600 dark:text-gray-400"
                  >
                    Total Servers
                  </p>
                  <p
                    class="text-lg font-semibold text-gray-700 dark:text-gray-200"
                  >
                    <?php echo $num_servers ?>
                  </p>
                </div>
              </div>

              <!-- Card -->
              <div
                class="flex items-center p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800"
              >
                <div
                  class="p-3 mr-4 text-orange-500 bg-orange-100 rounded-full dark:text-orange-100 dark:bg-orange-500"
                >
                  <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path
                      d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z"
                    ></path>
                  </svg>
                </div>
                <div>
                  <p
                    class="mb-2 text-sm font-medium text-gray-600 dark:text-gray-400"
                  >
                    Total Kills
                  </p>
                  <p
                    class="text-lg font-semibold text-gray-700 dark:text-gray-200"
                  >
				  <?php echo $num_kills ?>
                  </p>
                </div>
              </div>

              <!-- Card -->
              <div
                class="flex items-center p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800"
              >
                <div
                  class="p-3 mr-4 text-green-500 bg-green-100 rounded-full dark:text-green-100 dark:bg-green-500"
                >
                  <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path
                      fill-rule="evenodd"
                      d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z"
                      clip-rule="evenodd"
                    ></path>
                  </svg>
                </div>
                <div>
                  <p
                    class="mb-2 text-sm font-medium text-gray-600 dark:text-gray-400"
                  >
                    Player Data Expires
                  </p>
                  <p
                    class="text-lg font-semibold text-gray-700 dark:text-gray-200"
                  >
				  <?php echo $g_options['DeleteDays']; ?> Days
                  </p>
                </div>
              </div>

<?php
		if ($lastevent)
		{
?>
              <!-- Card -->
              <div
                class="flex items-center p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800"
              >
                <div
                  class="p-3 mr-4 text-blue-500 bg-blue-100 rounded-full dark:text-blue-100 dark:bg-blue-500"
                >
                  <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path
                      d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3zM16 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM6.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3z"
                    ></path>
                  </svg>
                </div>
                <div>
                  <p
                    class="mb-2 text-sm font-medium text-gray-600 dark:text-gray-400"
                  >
                    Last Kill
                  </p>
                  <p
                    class="text-lg font-semibold text-gray-700 dark:text-gray-200"
                  >
                    <?php echo date('g:i:s A, D. M. d', strtotime($lastevent)) ?>
                  </p>
                </div>
              </div>
<?php
			}
?>

             <!-- Card -->
			 <div
                class="flex items-center p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800"
              >
                <div
                  class="p-3 mr-4 text-teal-500 bg-teal-100 rounded-full dark:text-teal-100 dark:bg-teal-500"
                >
                  <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path
                      fill-rule="evenodd"
                      d="M18 5v8a2 2 0 01-2 2h-5l-5 4v-4H4a2 2 0 01-2-2V5a2 2 0 012-2h12a2 2 0 012 2zM7 8H5v2h2V8zm2 0h2v2H9V8zm6 0h-2v2h2V8z"
                      clip-rule="evenodd"
                    ></path>
                  </svg>
                </div>
                <div>
                  <p
                    class="mb-2 text-sm font-medium text-gray-600 dark:text-gray-400"
                  >
                    Real Time Stats
                  </p>
                  <p
                    class="text-lg font-semibold text-gray-700 dark:text-gray-200"
                  >
                    Enabled
                  </p>
                </div>
              </div>

			</div>

<?php
	}

?>