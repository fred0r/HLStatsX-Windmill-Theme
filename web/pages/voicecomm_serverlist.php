<?php
	// VOICECOMM MODULE
	global $db;
	
	define('TS', 0);
	define('VENT', 1);
	define('TS3', 2);
	
	$result = $db->query("
		SELECT
			serverId,
			name,
			addr,
			password,
			descr,
			queryPort,
			UDPPort,
			serverType
		FROM
			hlstats_Servers_VoiceComm
        ");
  
	if ($db->num_rows($result) >= 1) {
?>

<!-- start voicecomm_serverlist.php -->
<div class="w-full mt-8 overflow-hidden rounded-lg shadow-xs">
	<div class="w-full overflow-x-auto">
		<table class="w-full whitespace-no-wrap">
			<tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
				<td>Voice Server Name</td>
				<td>Status</td>
				<td>Server Address</td>
				<td>Password</td>
				<td>Slots&nbsp;used</td>
				<td>Server Uptime</td>
				<td>Notes</td>
			</tr> 
			<tbody class="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800">
<?php
		$i = 0;
		$j = 0;
		$t = 0;
		while ($row = $db->fetch_array()) {
			if ($row['serverType'] == TS) {
				$ts_servers[$i]['serverId'] = $row['serverId'];
				$ts_servers[$i]['name'] = $row['name'];
				$ts_servers[$i]['addr'] = $row['addr'];
				$ts_servers[$i]['password'] = $row['password'];
				$ts_servers[$i]['descr'] = $row['descr'];
				$ts_servers[$i]['queryPort'] = $row['queryPort'];
				$ts_servers[$i]['UDPPort'] = $row['UDPPort'];
				$i++;
			} else if ($row['serverType'] == VENT) {
				$vent_servers[$j]['serverId'] = $row['serverId'];
				$vent_servers[$j]['name'] = $row['name'];
				$vent_servers[$j]['addr'] = $row['addr'];
				$vent_servers[$j]['password'] = $row['password'];
				$vent_servers[$j]['descr'] = $row['descr'];
				$vent_servers[$j]['queryPort'] = $row['queryPort'];
				$j++;
			} else if ($row['serverType'] == TS3) {
				$ts3_servers[$t]['serverId'] = $row['serverId'];
				$ts3_servers[$t]['name'] = $row['name'];
				$ts3_servers[$t]['addr'] = $row['addr'];
				$ts3_servers[$t]['password'] = $row['password'];
				$ts3_servers[$t]['descr'] = $row['descr'];
				$ts3_servers[$t]['queryPort'] = $row['queryPort'];
				$ts3_servers[$t]['UDPPort'] = $row['UDPPort'];
				$t++;
			}
		}
		if (isset($ts_servers))
		{
			require_once(PAGE_PATH . '/teamspeak_class.php');
			foreach($ts_servers as $ts_server)
			{
				$settings = $teamspeakDisplay->getDefaultSettings();
				$settings['serveraddress'] = $ts_server['addr'];
				$settings['serverqueryport'] = $ts_server['queryPort'];
				$settings['serverudpport'] = $ts_server['UDPPort'];
				$ts_info = $teamspeakDisplay->queryTeamspeakServerEx($settings);
				if ($ts_info['queryerror'] != 0) {
					$ts_channels = 'err';
					$ts_slots = $ts_info['queryerror'];
				} else {
					$ts_channels = count($ts_info['channellist']);
					$ts_slots = count($ts_info['playerlist']).'/'.$ts_info['serverinfo']['server_maxusers'];
				}
?>
        <tr class="bg1">
			<td class="fHeading">
				<img src="<?php echo IMAGE_PATH; ?>/teamspeak/teamspeak.gif" alt="tsicon" />
				&nbsp;<a href="<?php echo $g_options['scripturl'] . "?mode=teamspeak&amp;game=$game&amp;tsId=".$ts_server['serverId']; ?>"><?php echo trim($ts_server['name']); ?></a>
			</td>
			<td>
				<a href="teamspeak://<?php echo $ts_server['addr'].':'.$ts_server['UDPPort'] ?>/?channel=?password=<?php echo $ts_server['password']; ?>"><?php echo $ts_server['addr'].':'.$ts_server['UDPPort']; ?></a>
			</td>
			<td>
				<?php echo $ts_server['password']; ?>
			</td>
			<td style="text-align:right;">
				<?php echo $ts_channels; ?>
			</td>
			<td style="text-align:right;">
				<?php echo $ts_slots; ?>
			</td>
			<td>
				<?php echo $ts_server['descr']; ?>
			</td>
		</tr>
<?php
			}
		}
		if (isset($vent_servers))
		{
			require_once(PAGE_PATH . '/ventrilostatus.php');
			foreach($vent_servers as $vent_server)
			{
				$ve_info = new CVentriloStatus;
				$ve_info->m_cmdcode	= 2;					// Detail mode.
				$ve_info->m_cmdhost = $vent_server['addr'];
				$ve_info->m_cmdport = $vent_server['queryPort'];
				/////////
				$rc = $ve_info->Request();
			//	if ($rc) {
			//		echo "CVentriloStatus->Request() failed. <strong>$ve_info->m_error</strong><br /><br />\n";
			//	} else {
					$ve_channels = $ve_info->m_channelcount;
					$ve_slots = $ve_info->m_clientcount.'/'.$ve_info->m_maxclients;
			//	}
		?>  
			<tr class="text-sm font-semibold text-gray-700 dark:text-gray-400">
				<td class="flex items-center">
					<img src="<?php echo IMAGE_PATH; ?>/ventrilo/ventrilo.png" alt="venticon">
					&nbsp;<a href="<?php echo $g_options['scripturl'] . "?mode=ventrilo&amp;game=$game&amp;veId=".$vent_server['serverId']; ?>"><?php echo $vent_server['name']; ?></a>
				</td>
				<td>
					<a href="ventrilo://<?php echo $vent_server['addr'].':'.$vent_server['queryPort'] ?>/servername=<?php echo $ve_info->m_name; ?>">
					<?php echo $vent_server['addr'].':'.$vent_server['queryPort']; ?>
					</a>
				</td>
				<td>
					<?php echo $vent_server['password']; ?>				
				</td>
				<td style="text-align:right;">
					<?php echo $ve_channels; ?>
				</td>
				<td style="text-align:right;">
					<?php echo $ve_slots; ?>
				</td>
				<td>
					<?php echo $vent_server['descr']; ?>
				</td>
			</tr>
		<?php
			}
		}
		
				if (isset($ts3_servers))
		{
			require_once(PAGE_PATH . '/ts3admin_class.php');
			require_once('includes/inc_ts3admin_query_password.php');

		/**
			*
			* Is a small script to demonstrate how to get a serverlist via ts3admin.class
			*
			* by par0noid solutions - ts3admin.info https://github.com/Speckmops/ts3admin.class
			*
		*/

		foreach($ts3_servers as $ts3_server)
		{
					
					$ts3_ip = $ts3_server['addr'];
					$ts3_queryport = $ts3_server['queryPort'];
					$ts3_user = 'serveradmin';
					$ts3_pass = $ts3q_server_query_password;
					
					#build a new ts3admin object
					$tsAdmin = new ts3admin($ts3_ip, $ts3_queryport);
					
					if($tsAdmin->getElement('success', $tsAdmin->connect())) {
						#login as serveradmin
						$tsAdmin->login($ts3_user, $ts3_pass);
						
						#get serverlist
						$servers = $tsAdmin->serverList();
						
						#generate table codes for all servers
						foreach($servers['data'] as $server) {

							$ts3q_server_id = $server['virtualserver_id'];
							$ts3q_server_name = htmlspecialchars($server['virtualserver_name']);
							$ts3q_server_port = $server['virtualserver_port'];
							if(isset($server['virtualserver_clientsonline'])) {
								$ts3q_server_clients = $server['virtualserver_clientsonline'] . '/' . $server['virtualserver_maxclients'];
							}else{
								$ts3q_server_clients = '-';
							}
							$ts3q_server_status = $server['virtualserver_status'];
							if(isset($server['virtualserver_uptime'])) {
								$ts3q_server_uptime = $tsAdmin->convertSecondsToStrTime(($server['virtualserver_uptime']));
							}else{
								$ts3q_server_uptime = '-';
							}
						}
					}else{
						$ts3q_server_id = '0';
						$ts3q_server_name = '-';
						$ts3q_server_port = '-';
						$ts3q_server_clients = '-';
						$ts3q_server_status = 'offline';
						$ts3q_server_uptime = '-';
					}

		?>
			<tr class="text-sm font-semibold text-gray-700 dark:text-gray-400">
				<td class="flex items-center">
					<img src="<?php echo IMAGE_PATH; ?>/teamspeak3/ts3.png" alt="tsicon">
						&nbsp;<a href="<?php echo $g_options['scripturl'] . "?mode=teamspeak&amp;game=$game&amp;tsId=" . $ts3q_server_id; ?>">
						<?php echo $ts3q_server_name ."\n"; ?>
					</a>
				</td>
				<td><?php echo $ts3q_server_status ?></td>
				<td>
					<a href="ts3server://<?php echo $ts3_server['addr'].'?port='.$ts3q_server_port ?>&nickname=WebGuest">
						<?php echo $ts3_server['addr'].':'.$ts3q_server_port; ?> (Join)
					</a>
				</td>
				<td>-</td>
				<td><?php echo $ts3q_server_clients ?></td>
				<td><?php echo $ts3q_server_uptime ?></td>
				<td><?php echo $ts3_server['descr']; ?></td>
			</tr>
<?php			
		}
}
?>
			</tbody>
		</table>
	</div>
<?php	echo "	<div class=\"rounded-b-lg border-t dark:border-gray-700 bg-gray-50 sm:grid-cols-9 dark:text-gray-400 dark:bg-gray-800\">&nbsp;</div>\n"; ?>
</div>
<?php
	}
?>
<!-- end voicecomm_serverlist.php -->