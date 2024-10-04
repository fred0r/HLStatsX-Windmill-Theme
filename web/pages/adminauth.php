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

	pageHeader(array('Admin'), array('Admin' => ''));
?>

<?php display_page_title('Admin Login'); ?>

<!-- Start log in Page -->
<div class="px-4 py-3 mb-8 bg-white rounded-lg shadow-md dark:bg-gray-800">
    <div class="flex items-center justify-center p-6 sm:p-12 md:w-1/2">
        <div class="w-full">
<?php
	if ($this->error)
	{
		echo "<span class=\"text-xs text-red-600 dark:text-red-400\">$this->error</span>";
	}
?>
 			<form method="post" name="auth">
                <label class="block text-sm">
                    <span class="text-gray-700 dark:text-gray-400">Username</span>
                    <input type="text" name="authusername" size="20" maxlength="16" value="<?php echo $this->username; ?>" class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input">
                </label>

                <label class="block mt-4 text-sm">
                    <span class="text-gray-700 dark:text-gray-400">Password</span>
						<input type="password" name="authpassword" size="20" maxlength="16" value="<?php echo $this->password; ?>" class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input">
                </label>

				<input type="submit" value=" Login " id="authsubmit" class="windmill-button block w-full px-4 py-2 mt-4 text-sm font-medium leading-5 text-center text-white transition-colors duration-150 border border-transparent rounded-lg focus:outline-none">
			</form>
        </div>
    </div>
</div>
<?php
/*
<div class="block">
	<?php printSectionTitle('Authorization Required'); ?>
	<div class="subblock">
	<?php
	if ($this->error)
	{
		echo "<span class=\"fTitle\" style=\"font-weight:bold;\">$this->error</span>";
	}
?>
		<div style="float:left;margin-left:40px;">
		<form method="post" name="auth">
	
			<table class="data-table">
				<tr style="vertical-align:middle;">
					<td class="bg1" style="width:45%;border:0;">Username:</td>
					<td class="bg1" style="width:55%;border:0;"><input type="text" name="authusername" size="20" maxlength="16" value="<?php echo $this->username; ?>" class="textbox"></td>
				</tr>
				<tr style="vertical-align:middle;">
					<td class="bg1" style="width:45%;border:0;">Password:</td>
					<td class="bg1" style="width:55%;border:0;"><input type="password" name="authpassword" size="20" maxlength="16" value="<?php echo $this->password; ?>" class="textbox"></td>
				</tr>
				<tr>
					<td class="bg1" style="border:0;">&nbsp;</td>
					<td class="bg1" style="border:0;"><input type="submit" value=" Login " id="authsubmit" class="submit"></td>
				</tr>
			
			</table><br />
				
			Please ensure cookies are enabled in your browser security options.<br />
			<!-- <strong>Note</strong> Do not select "Save my password" if other people will use this computer.</span>	<br /><br /> -->
		</form>
		</div>
	</div>
</div>
*/
?>
<!-- End log in Page -->