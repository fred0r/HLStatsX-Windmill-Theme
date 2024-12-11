<?php
/** https://github.com/scheissegalo/PHP-TS3ServerViewer **/

/* Icon Packs
https://www.myteamspeak.com/addons/L2FkZG9ucy85MDdiMjY2Ny04YWRkLTRmMzEtODcxNi00NTY2NGM5MjI3ZTU%3D
https://www.myteamspeak.com/addons/L2FkZG9ucy8yNTg4NjUwOC1lN2Q4LTQzY2EtYmQ5Yy1hN2JhYjY4MTIxODI%3D
*/ 

class ts3ssv
{
	private $_host;
	private $_queryPort;
	private $_serverDatas;
	private $_channelDatas;
	private $_userDatas;
	private $_serverGroupFlags;
	private $_channelGroupFlags;
	private $_login;
	private $_password;
	private $_cacheFile;
	private $_cacheTime;
	private $_channelList;
	private $_useCommand;
	private $_javascriptName;
	private $_socket;

	public $imagePath;
	public $flagPath;
	public $showNicknameBox;
	public $timeout;
	public $hideEmptyChannels;
	public $hideParentChannels;

	public function __construct($host, $queryPort)
	{
		$this->_host = $host;
		$this->_queryPort = $queryPort;

		$this->_socket = null;
		$this->_serverDatas = array();
		$this->_channelDatas = array();
		$this->_userDatas = array();
		$this->_serverGroupFlags = array();
		$this->_channelGroupFlags = array();
		$this->_login = false;
		$this->_password = false;
		$this->_cacheTime = 0;
		$this->_cacheFile = __FILE__ . ".cache";
		$this->_channelList = array();
		$this->_useCommand = "use port=9987";

		$this->iconPack = "16x16";
		$this->imagePath = "img/default/";
		$this->flagPath = "img/flags/";
		$this->showNicknameBox = true;
		$this->showPasswordBox = false;
		$this->timeout = 2;
		$this->hideEmptyChannels = false;
		$this->hideParentChannels = false;
	}

	public function useServerId($serverId)
	{
		$this->_useCommand = "use sid=$serverId";
	}

	public function useServerPort($serverPort)
	{
		$this->_useCommand = "use port=$serverPort";
	}

	public function setLoginPassword($login, $password)
	{
		$this->_login = $login;
		$this->_password = $password;
	}

	public function setCache($time, $file = false)
	{
		$this->_cacheTime = $time;
		if($file !== false) $this->_cacheFile = $file;
	}

	public function clearServerGroupFlags()
	{
		$this->_serverGroupFlags = array();
	}

	public function setServerGroupFlag($serverGroupId, $image)
	{
		$this->_serverGroupFlags[$serverGroupId] = $image;
	}

	public function clearChannelGroupFlags()
	{
		$this->_channelGroupFlags = array();
	}

	public function setChannelGroupFlag($channelGroupId, $image)
	{
		$this->_channelGroupFlags[$channelGroupId] = $image;
	}

	public function limitToChannels()
	{
		$this->_channelList = func_get_args();
	}

	private function ts3decode($str, $reverse = false)
	{
		$find = array('\\\\', 	"\/", 		"\s", 		"\p", 		"\a", 	"\b", 	"\f", 		"\n", 		"\r", 	"\t", 	"\v");
		$rplc = array(chr(92),	chr(47),	chr(32),	chr(124),	chr(7),	chr(8),	chr(12),	chr(10),	chr(3),	chr(9),	chr(11));

		if(!$reverse) return str_replace($find, $rplc, $str);
		return str_replace($rplc, $find, $str);
	}

	private function toHTML($string)
	{
		return htmlentities($string, ENT_QUOTES, "UTF-8");
	}

	private function sortUsers($a, $b)
	{
		if($a["client_talk_power"] != $b["client_talk_power"]) return $a["client_talk_power"] > $b["client_talk_power"] ? -1 : 1;
		return strcasecmp($a["client_nickname"], $b["client_nickname"]);
	}

	private function parseLine($rawLine)
	{
		$datas = array();
		$rawItems = explode("|", $rawLine);
		foreach ($rawItems as $rawItem)
		{
			$rawDatas = explode(" ", $rawItem);
			$tempDatas = array();
			foreach($rawDatas as $rawData)
			{
				$ar = explode("=", $rawData, 2);
				$tempDatas[$ar[0]] = isset($ar[1]) ? $this->ts3decode($ar[1]) : "";
			}
			$datas[] = $tempDatas;
		}
		return $datas;
	}

	private function sendCommand($cmd)
	{
		fputs($this->_socket, "$cmd\n");
		$response = "";
		do
		{
			$response .= fread($this->_socket, 8096);
		}while(strpos($response, 'error id=') === false);
		if(strpos($response, "error id=0") === false)
		{
			throw new Exception("TS3 Server returned the following error: " . $this->ts3decode(trim($response)));
		}
		return $response;
	}

	private function queryServer()
	{
		$this->_socket = @fsockopen($this->_host, $this->_queryPort, $errno, $errstr, $this->timeout);
		if($this->_socket)
		{
			@socket_set_timeout($this->_socket, $this->timeout);
			$isTs3 = trim(fgets($this->_socket)) == "TS3";
			if(!$isTs3) throw new Exception("Not a Teamspeak 3 server/bad query port");

			if($this->_login !== false)
			{
				$this->sendCommand("login client_login_name=" . $this->_login . " client_login_password=" . $this->_password);
			}

			$response = "";
			$response .= $this->sendCommand($this->_useCommand);
			$response .= $this->sendCommand("serverinfo");
			$response .= $this->sendCommand("channellist -topic -flags -voice -limits");
			$response .= $this->sendCommand("clientlist -uid -away -voice -groups -country");
			$response .= $this->sendCommand("servergrouplist");
			$response .= $this->sendCommand("channelgrouplist");

			$this->disconnect();
			return $response;
		}
		else throw new Exception("Socket error: $errstr [$errno]");
	}

	private function disconnect()
	{
		@fputs($this->_socket, "quit\n");
		@fclose($this->_socket);
	}

	private function update()
	{
		$response = $this->queryServer();
		$lines = explode("error id=0 msg=ok\n\r", $response);
		if(count($lines) == 7)
		{
			$this->_serverDatas = $this->parseLine($lines[1]);
			$this->_serverDatas = $this->_serverDatas[0];

			$tmpChannels = $this->parseLine($lines[2]);
			$hide = count($this->_channelList) > 0 || $this->hideEmptyChannels;
			foreach ($tmpChannels as $channel)
			{
				$channel["show"] = !$hide;
				$this->_channelDatas[$channel["cid"]] = $channel;
			}

			$tmpUsers = $this->parseLine($lines[3]);
			usort($tmpUsers, array($this, "sortUsers"));
			foreach ($tmpUsers as $user)
			{
				if($user["client_type"] == 0)
				{
					if(!isset($this->_userDatas[$user["cid"]])) $this->_userDatas[$user["cid"]] = array();
					$this->_userDatas[$user["cid"]][] = $user;
				}
			}

			$serverGroups = $this->parseLine($lines[4]);
			foreach ($serverGroups as $sg) {
					if($sg["iconid"] < 0) $sg["iconid"] += 1<<32;
					if($sg["iconid"] > 0) $this->setServerGroupFlag($sg["sgid"], $this->iconPack . '/group_' . $sg["iconid"] . '.png');
			}

			$channelGroups = $this->parseLine($lines[5]);
			foreach ($channelGroups as $cg) {
					if($cg["iconid"] < 0) $cg["iconid"] += 1<<32;
					if($cg["iconid"] > 0) $this->setChannelGroupFlag($cg["cgid"], $this->iconPack . '/group_' . $cg["iconid"] . '.png');
			}
		}
		else throw new Exception("Invalid server response");
	}

	private function setShowFlag($channelIds)
	{
		if(!is_array($channelIds)) $channelIds = array($channelIds);
		foreach ($channelIds as $cid)
		{
			if(isset($this->_channelDatas[$cid]))
			{
				$this->_channelDatas[$cid]["show"] = true;
				if(!$this->hideParentChannels && $this->_channelDatas[$cid]["pid"] != 0)
				{
					$this->setShowFlag($this->_channelDatas[$cid]["pid"]);
				}
			}
		}
	}

	private function getCache()
	{
		if($this->_cacheTime > 0 && file_exists($this->_cacheFile) && (filemtime($this->_cacheFile) + $this->_cacheTime >= time()) )
		{
			return file_get_contents($this->_cacheFile);
		}
		return false;
	}

	private function saveCache($content)
	{
		if($this->_cacheTime > 0)
		{
			if(!@file_put_contents($this->_cacheFile, $content))
			{
				throw new Exception("Unable to write to file: " . $this->_cacheFile);
			}
		}
	}

	private function renderFlags($flags)
	{
		$content = "";
		foreach ($flags as $flag) $content .= '<img src="' . $this->imagePath . $flag . '">';
		return $content;
	}

	private function renderOptionBox($name, $label)
	{
		$key = "ts3ssv-" . $this->_javascriptName . "-$name";
		$value = isset($_COOKIE[$key]) ? htmlspecialchars($_COOKIE[$key]) : "";
		return '<label>' . $label . ': <input type="text" id="' . $key . '" value="' . $value . '" /></label>';
	}

	private function renderUsers($channelId)
	{
		$content = "";
		if(isset($this->_userDatas[$channelId]))
		{
			$imagePath = $this->imagePath;
			foreach ($this->_userDatas[$channelId] as $user)
			{
				if($user["client_type"] == 0)
				{
					$name = $this->toHTML($user["client_nickname"]);

					$icon = $this->iconPack . "/player_off.png";
					if($user["client_away"] == 1) $icon = $this->iconPack . "/away.png";
					else if($user["client_flag_talking"] == 1) $icon = $this->iconPack . "/player_on.png";
					else if($user["client_output_hardware"] == 0) $icon = $this->iconPack . "/hardware_output_muted.png";
					else if($user["client_output_muted"] == 1) $icon = $this->iconPack . "/output_muted.png";
					else if($user["client_input_hardware"] == 0) $icon = $this->iconPack . "/hardware_input_muted.png";
					else if($user["client_input_muted"] == 1) $icon = $this->iconPack . "/input_muted.png";

					$flags = array();

					if(isset($this->_channelGroupFlags[$user["client_channel_group_id"]]))
					{
						$flags[] = $this->_channelGroupFlags[$user["client_channel_group_id"]];
					}

					$serverGroups = explode(",", $user["client_servergroups"]);
					foreach ($serverGroups as $serverGroup)
					{
						if(isset($this->_serverGroupFlags[$serverGroup]))
						{
							$flags[] = $this->_serverGroupFlags[$serverGroup];
						}
					}

					if ($user["client_country"]){
						$flags = $this->renderFlags($flags).'&nbsp;<img src="' . $this->flagPath .''.substr(strtolower($user["client_country"]), 0, 2).'.gif">';
					}else{
						$flags = $this->renderFlags($flags);
					}

					$content .= <<<HTML
				<sl-tree-item><img src="$imagePath$icon">&nbsp;<span class="text-gray-600 dark:text-gray-400">$name</span>&nbsp;$flags</sl-tree-item>
HTML;
				}
			}
		}
		return $content;
	}

	private function renderChannels($channelId)
	{
		$content = "";
		$imagePath = $this->imagePath;
		foreach ($this->_channelDatas as $channel)
		{
			if($channel["pid"] == $channelId)
			{
				if($channel["show"])
				{
					$name = $this->toHTML($channel["channel_name"]);
					$title = $name  . " [" . $channel["cid"] . "]";
					$link = "javascript:ts3ssvconnect('" . $this->_javascriptName . "'," . $channel["cid"] . ")";

					$icon = $this->iconPack . "/channel_green.png";
					if( $channel["channel_maxclients"] > -1 && ($channel["total_clients"] >= $channel["channel_maxclients"])) $icon = $this->iconPack . "/channel_red.png";
					else if( $channel["channel_maxfamilyclients"] > -1 && ($channel["total_clients_family"] >= $channel["channel_maxfamilyclients"])) $icon = $this->iconPack . "/channel_red.png";
					else if($channel["channel_flag_password"] == 1) $icon = $this->iconPack . "/channel_yellow.png";

					$flags = array();
					if($channel["channel_flag_default"] == 1) $flags[] = $this->iconPack . '/default.png';
					if($channel["channel_needed_talk_power"] > 0) $flags[] = $this->iconPack . '/moderated.png';
					if($channel["channel_flag_password"] == 1) $flags[] = $this->iconPack . '/register.png';
					$flags = $this->renderFlags($flags);

					$users = $this->renderUsers($channel["cid"]);
					$childs = $this->renderChannels($channel["cid"]);

					$cid = $channel["cid"];
					$image = "<img src=\"{$imagePath}{$icon}\">";

					if(preg_match( '/\[(.*)spacer([\d\p{L}\w]+)?\]/', $channel["channel_name"], $matches) && $channel["channel_flag_permanent"] && !$channel["pid"])
					{
						$flags = '';
						$image = '';
						$spacer = explode( $matches[0], $channel["channel_name"] );
						$checkSpacer = isset( $spacer[1][0] ) ? $spacer[1][0] . $spacer[1][0] . $spacer[1][0] : '';

						if($matches[1] == 'c')
						{
							/* Channel name should be centered */
							$name = $this->toHTML($spacer[1]);
						}
						elseif($matches[1] == '*' || (strlen($spacer[1]) == 3 && $checkSpacer == $spacer[1]))
						{
							/* Repeat given character (in most use-cases this draws a line) */
							$addSpacer = '';

							for ($i = 0; $i <= 40; $i++)
							{
								if(strlen($addSpacer) >= 40) break;

								$addSpacer .= $spacer[1];
							}

							$name = $this->toHTML($addSpacer);
						}
						else
						{
							$name = $spacer[1];
						}
					}

					$content .= <<<HTML
		<sl-tree-item>$image&nbsp;<span class="text-gray-600 dark:text-gray-400">$name</span>&nbsp;$flags
		$users
		$childs
		</sl-tree-item>
HTML;
				}
				else $content .= $this->renderChannels($channel["cid"]);
			}
		}
		return $content;
	}

	public function render()
	{
		try
		{
			$cache = $this->getCache();
			if($cache != false) return $cache;

			$this->update();

			if($this->hideEmptyChannels && count($this->_channelList) > 0) $this->setShowFlag(array_intersect($this->_channelList, array_keys($this->_userDatas)));
			else if($this->hideEmptyChannels) $this->setShowFlag(array_keys($this->_userDatas));
			else if(count($this->_channelList) > 0) $this->setShowFlag($this->_channelList);

			$host = $this->_host;
			$port = $this->_serverDatas["virtualserver_port"];
			$name = $this->toHTML($this->_serverDatas["virtualserver_name"]);
			$icon = $this->imagePath . $this->iconPack . "/server_green.png";
			$this->_javascriptName = $javascriptName = preg_replace("#[^a-z-A-Z0-9]#", "-", $host . "-" . $port);

			$options = "";
			if ($this->showNicknameBox) $options .= $this->renderOptionBox("nickname", "Nickname");
			if($this->showPasswordBox && isset($this->_serverDatas["virtualserver_flag_password"]) && $this->_serverDatas["virtualserver_flag_password"] == 1) $options .= $this->renderOptionBox("password", "Password");

			$channels = $this->renderChannels(0);

			$hostbanner_gfx_url = $this->toHTML($this->_serverDatas["virtualserver_hostbanner_gfx_url"]);
			$welcome_message = $this->toHTML($this->_serverDatas["virtualserver_welcomemessage"]);
			$clients_online = ($this->_serverDatas["virtualserver_clientsonline"] - $this->_serverDatas["virtualserver_queryclientsonline"]);
			$max_clients = $this->toHTML($this->_serverDatas["virtualserver_maxclients"]);
			$platform = $this->toHTML($this->_serverDatas["virtualserver_platform"]);
			$version = $this->toHTML($this->_serverDatas["virtualserver_version"]);

			$content = <<<HTML
			<img src="$hostbanner_gfx_url"><br>
			Server Name: $name&nbsp;&nbsp;Slots Used: $clients_online/$max_clients<br><br>
			"$welcome_message"<br><br>
			Platform: $platform (v$version)<br>*-1@9-*
HTML;

			$content = $content . <<<HTML
	$options
	<sl-tree selection="leaf">
	<sl-tree-item expanded><img src="$icon">&nbsp;<span class="text-gray-600 dark:text-gray-400">$name</span>
	$channels
	</sl-tree-item>
	</sl-tree>
HTML;
			
			$this->saveCache($content);
		}
		catch (Exception $ex)
		{
			$this->disconnect();
			$content = '<div class="ts3ssvError">' . $ex->getMessage() . '</div>';
		}

		return $content;
	}

}
?>
