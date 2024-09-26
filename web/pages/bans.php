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

// Player Rankings
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

	if (isset($_GET['minkills'])) {
		$minkills = valid_request(intval($_GET['minkills']),true);
	} else {
		$minkills = 0;
	}

	pageHeader
	(
		array ($gamename, 'Cheaters &amp; Banned Players'),
		array ($gamename=>"%s?game=$game", 'Cheaters &amp; Banned Players'=>'')
	);

	$table = new Table
	(
		array(
			new TableColumn
			(
				'lastName',
				'Player',
				'width=26&flag=1&link=' . urlencode('mode=playerinfo&amp;player=%k')
			),
			new TableColumn
			(
				'ban_date',
				'Ban Date',
				'width=15&align=right'
			),
			new TableColumn
			(
				'skill',
				'Points',
				'width=6&align=right'
			),
			new TableColumn
			(
				'activity',
				'Activity',
				'width=10&sort=no&type=bargraph'
			),
			new TableColumn
			(
				'kills',
				'Kills',
				'width=5&align=right'
			),
			new TableColumn
			(
				'deaths',
				'Deaths',
				'width=5&align=right'
			),
			new TableColumn
			(
				'headshots',
				'Headshots',
				'width=7&align=right'
			),
			new TableColumn
			(
				'kpd',
				'K:D',
				'width=10&align=right'
			),
			new TableColumn
			(
				'hpk',
				'HS:K',
				'width=5&align=right'
			),
			new TableColumn
			(
				'acc',
				'Accuracy',
				'width=6&align=right&append=' . urlencode('%')
			)
		),
		'playerId',
		'last_event',
		'skill',
		true
	);

	$day_interval = 28;

	$resultCount = $db->query
	("
		SELECT
			COUNT(*)
		FROM
			hlstats_Players
		WHERE
			hlstats_Players.game = '$game'
			AND hlstats_Players.hideranking = 2
			AND hlstats_Players.kills >= $minkills
	");

	list($numitems) = $db->fetch_row($resultCount);

	$result = $db->query
	("
		SELECT
			hlstats_Players.playerId,
			FROM_UNIXTIME(last_event,'%Y.%m.%d %T') as ban_date,
			hlstats_Players.flag,
                        unhex(replace(hex(hlstats_Players.lastName), 'E280AE', '')) as lastName,
			hlstats_Players.skill,
			hlstats_Players.kills,
			hlstats_Players.deaths,
			IFNULL(ROUND(hlstats_Players.kills / IF(hlstats_Players.deaths = 0, 1, hlstats_Players.deaths), 2), '-') AS kpd,
			hlstats_Players.headshots,
			IFNULL(ROUND(hlstats_Players.headshots / hlstats_Players.kills, 2), '-') AS hpk,
			IFNULL(ROUND((hlstats_Players.hits / hlstats_Players.shots * 100), 0), 0) AS acc,
			activity
		FROM
			hlstats_Players
		WHERE
			hlstats_Players.game = '$game'
			AND hlstats_Players.hideranking = 2
			AND hlstats_Players.kills >= $minkills
		ORDER BY
			$table->sort $table->sortorder,
			$table->sort2 $table->sortorder,
			hlstats_Players.lastName ASC
		LIMIT
			$table->startitem,
			$table->numperpage
	");
?>
<!-- start bans.php -->

<!--

<div class="block">
	<?php printSectionTitle('Cheaters &amp; Banned Players'); ?>
		<div class="subblock">
			<div style="float:left;">
				<form method="get" action="<?php echo $g_options['scripturl']; ?>">
					<input type="hidden" name="mode" value="search" />
					<input type="hidden" name="game" value="<?php echo $game; ?>" />
					<input type="hidden" name="st" value="player" />
					<strong>&#8226;</strong> Find a player:
					<input type="text" name="q" size="20" maxlength="64" class="textbox" />
					<input type="submit" value="Search" class="smallsubmit" />
				</form>
			</div>
		</div><br /><br />
		<div style="clear:both;padding-top:4px;"></div>
		<?php $table->draw($result, $numitems, 95); ?><br /><br />
		<div class="subblock">
			<div style="float:left;">
				<form method="get" action="<?php echo $g_options['scripturl']; ?>">
					<?php
						foreach ($_GET as $k=>$v)
						{
							$v = valid_request($v, false);
							if ($k != "minkills")
							{
								echo "<input type=\"hidden\" name=\"" . htmlspecialchars($k) . "\" value=\"" . htmlspecialchars($v) . "\" />\n";
							}
						}
					?>
					<strong>&#8226;</strong> Show only players with
					<input type="text" name="minkills" size="4" maxlength="2" value="<?php echo $minkills; ?>" class="textbox" /> or more kills from a total <strong><?php echo number_format($numitems); ?></strong> banned players
					<input type="submit" value="Apply" class="smallsubmit" />
				</form>
			</div>
			<div style="float:right;">
				Go to: <a href="<?php echo $g_options["scripturl"] . "?game=$game"; ?>"><?php echo $gamename; ?></a>
			</div>
	</div>
</div>

-->

<!-- Start demo table -->


<h2
              class="my-6 text-2xl font-semibold text-gray-700 dark:text-gray-200"
            >
               
            </h2>
            <!-- CTA -->
            <a
              class="flex items-center justify-between p-4 mb-8 text-sm font-semibold text-purple-100 bg-purple-600 rounded-lg shadow-md focus:outline-none focus:shadow-outline-purple"
              href="https://github.com/estevanmaito/windmill-dashboard"
            >
              <div class="flex items-center">
                <svg
                  class="w-5 h-5 mr-2"
                  fill="currentColor"
                  viewBox="0 0 20 20"
                >
                  <path
                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"
                  ></path>
                </svg>
                <span>Star this project on GitHub</span>
              </div>
              <span>View more &RightArrow;</span>
            </a>

            <!-- With avatar -->
            <h4
              class="mb-4 text-lg font-semibold text-gray-600 dark:text-gray-300"
            >
              Table with avatars
            </h4>
            <div class="w-full mb-8 overflow-hidden rounded-lg shadow-xs">
              <div class="w-full overflow-x-auto">
                <table class="w-full whitespace-no-wrap">
                  <thead>
                    <tr
                      class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-800"
                    >
                      <th class="px-4 py-3">Client</th>
                      <th class="px-4 py-3">Amount</th>
                      <th class="px-4 py-3">Status</th>
                      <th class="px-4 py-3">Date</th>
                    </tr>
                  </thead>
                  <tbody
                    class="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800"
                  >
                    <tr class="text-gray-700 dark:text-gray-400">
                      <td class="px-4 py-3">
                        <div class="flex items-center text-sm">
                          <!-- Avatar with inset shadow -->
                          <div
                            class="relative hidden w-8 h-8 mr-3 rounded-full md:block"
                          >
                            <img
                              class="object-cover w-full h-full rounded-full"
                              src="https://images.unsplash.com/flagged/photo-1570612861542-284f4c12e75f?ixlib=rb-1.2.1&q=80&fm=jpg&crop=entropy&cs=tinysrgb&w=200&fit=max&ixid=eyJhcHBfaWQiOjE3Nzg0fQ"
                              alt=""
                              loading="lazy"
                            />
                            <div
                              class="absolute inset-0 rounded-full shadow-inner"
                              aria-hidden="true"
                            ></div>
                          </div>
                          <div>
                            <p class="font-semibold">Hans Burger</p>
                            <p class="text-xs text-gray-600 dark:text-gray-400">
                              10x Developer
                            </p>
                          </div>
                        </div>
                      </td>
                      <td class="px-4 py-3 text-sm">
                        $ 863.45
                      </td>
                      <td class="px-4 py-3 text-xs">
                        <span
                          class="px-2 py-1 font-semibold leading-tight text-green-700 bg-green-100 rounded-full dark:bg-green-700 dark:text-green-100"
                        >
                          Approved
                        </span>
                      </td>
                      <td class="px-4 py-3 text-sm">
                        6/10/2020
                      </td>
                    </tr>


                    <tr class="text-gray-700 dark:text-gray-400">
                      <td class="px-4 py-3">
                        <div class="flex items-center text-sm">
                          <!-- Avatar with inset shadow -->
                          <div
                            class="relative hidden w-8 h-8 mr-3 rounded-full md:block"
                          >
                            <img
                              class="object-cover w-full h-full rounded-full"
                              src="https://images.unsplash.com/flagged/photo-1570612861542-284f4c12e75f?ixlib=rb-1.2.1&q=80&fm=jpg&crop=entropy&cs=tinysrgb&w=200&fit=max&ixid=eyJhcHBfaWQiOjE3Nzg0fQ"
                              alt=""
                              loading="lazy"
                            />
                            <div
                              class="absolute inset-0 rounded-full shadow-inner"
                              aria-hidden="true"
                            ></div>
                          </div>
                          <div>
                            <p class="font-semibold">Hans Burger</p>
                            <p class="text-xs text-gray-600 dark:text-gray-400">
                              10x Developer
                            </p>
                          </div>
                        </div>
                      </td>
                      <td class="px-4 py-3 text-sm">
                        $ 863.45
                      </td>
                      <td class="px-4 py-3 text-xs">
                        <span
                          class="px-2 py-1 font-semibold leading-tight text-green-700 bg-green-100 rounded-full dark:bg-green-700 dark:text-green-100"
                        >
                          Approved
                        </span>
                      </td>
                      <td class="px-4 py-3 text-sm">
                        6/10/2020
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <div
                class="grid px-4 py-3 text-xs font-semibold tracking-wide text-gray-500 uppercase border-t dark:border-gray-700 bg-gray-50 sm:grid-cols-9 dark:text-gray-400 dark:bg-gray-800"
              >
                <span class="flex items-center col-span-3">
                  Showing 21-30 of 100
                </span>
                <span class="col-span-2"></span>
                <!-- Pagination -->
                <span class="flex col-span-4 mt-2 sm:mt-auto sm:justify-end">
                  <nav aria-label="Table navigation">
                    <ul class="inline-flex items-center">
						<li>
							.
						</li>
                    </ul>
                  </nav>
                </span>
              </div>
            </div>

<!-- end demo table -->

<!-- end bans.php -->