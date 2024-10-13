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

// Clan Rankings
	$db->query
	("
		SELECT
			hlstats_Games.name
		FROM
			hlstats_Games
		WHERE
			hlstats_Games.code = '$game'
	");

	if ($db->num_rows() < 1) {
        error("No such game '$game'.");
	}

    list($gamename) = $db->fetch_row();
	$db->free_result();

	if (isset($_GET['minmembers'])) {
		$minmembers = valid_request(intval($_GET["minmembers"]),true);
	} else {
		$minmembers = 3;
	}

	pageHeader
	(
		array ($gamename, 'Clan Rankings'),
		array ($gamename=>"%s?game=$game", 'Clan Rankings' => '')
	);

	$table = new Table
	(
		array
		(
			new TableColumn
			(
				'name',
				'Clan',
				'width=25&icon=clan&link=' . urlencode('mode=claninfo&amp;clan=%k')
			),
			new TableColumn
			(
				'tag',
				'Tag',
				'width=15&align=center'
			),
			new TableColumn
			(
				'skill',
				'Avg. Points',
				'width=8&align=right&skill_change=1'
			),
			new TableColumn
			(
				'nummembers',
				'Members',
				'width=5&align=right'
			),
			new TableColumn
			(
				'activity',
				'Activity',
				'width=8&type=bargraph'
			),
			new TableColumn
			(
				'connection_time',
				'Connection Time',
				'width=13&align=right&type=timestamp'
			),
			new TableColumn
			(
				'kills',
				'Kills',
				'width=7&align=right'
			),
			new TableColumn
			(
				'deaths',
				'Deaths',
				'width=7&align=right'
			),
			new TableColumn
			(
				'kpd',
				'K:D',
				'width=7&align=right'
			)
		),
		'clanId',
		'skill',
		'kpd',
		true
	);
	$result = $db->query
	("
		SELECT
			hlstats_Clans.clanId,
			hlstats_Clans.name,
			hlstats_Clans.tag,
			COUNT(hlstats_Players.playerId) AS nummembers,
			SUM(hlstats_Players.kills) AS kills,
			SUM(hlstats_Players.deaths) AS deaths,
			SUM(hlstats_Players.connection_time) AS connection_time,
			ROUND(AVG(hlstats_Players.skill)) AS skill,
			ROUND(AVG(hlstats_Players.last_skill_change)) AS last_skill_change,
			ROUND(SUM(hlstats_Players.kills) / IF(SUM(hlstats_Players.deaths) = 0, 1, SUM(hlstats_Players.deaths)), 2) AS kpd,
			TRUNCATE(AVG(activity), 2) AS activity
		FROM
			hlstats_Clans,
			hlstats_Players
		WHERE
			hlstats_Clans.game = '$game'
			AND hlstats_Clans.hidden <> 1
			AND hlstats_Players.clan = hlstats_Clans.clanId
			AND hlstats_Players.hideranking = 0
		GROUP BY
			hlstats_Clans.clanId
		HAVING
			activity >= 0
			AND nummembers >= $minmembers
		ORDER BY
			$table->sort $table->sortorder,
			$table->sort2 $table->sortorder,
			hlstats_Clans.name ASC
		LIMIT
			$table->startitem,
			$table->numperpage
	");
	$resultCount = $db->query
	("
		SELECT
			hlstats_Clans.clanId,
			SUM(activity) AS activity
		FROM
			hlstats_Clans
		LEFT JOIN
			hlstats_Players
		ON
			hlstats_Players.clan = hlstats_Clans.clanId
		WHERE
			hlstats_Clans.game = '$game'
			AND hlstats_Clans.hidden <> 1
			AND hlstats_Players.hideranking = 0
		GROUP BY
			hlstats_Clans.clanId
		HAVING
			activity >= 0
			AND COUNT(hlstats_Players.playerId) >= $minmembers
	");
?>
<!-- start clans.php -->
<?php display_page_title('Clan Rankings'); ?>

				<div class="flex items-center justify-between p-4 mb-8 text-sm px-4 py-3 bg-white rounded-lg shadow-md dark:bg-gray-800">
					<div class="flex items-center">
						<form method="get" action="<?php echo $g_options['scripturl']; ?>">
							<input type="hidden" name="mode" value="search">
							<input type="hidden" name="game" value="<?php echo $game; ?>">
							<input type="hidden" name="st" value="clan">
							<span class="font-semibold text-center text-gray-700 dark:text-gray-400">Find a clan: </span>
							<input type="text" name="q" size="20" maxlength="64" class="mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input">
							<input type="submit" value="Search" class="windmill-button px-4 py-2 mb-2 text-sm font-medium leading-5 text-center border border-transparent rounded-lg btn">
						</form>
					</div>
				</div>

<?php $table->draw($result, $db->num_rows($resultCount), 95); ?>

<!-- end clans.php -->