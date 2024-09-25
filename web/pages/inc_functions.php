<?php

function display_menu_item($name, $link) {

  echo "    <li class=\"relative px-6 py-3\">\r\n";
  echo "        <a\r\n";
  echo "        class=\"inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200\"\r\n";
  echo "        href=\"" . $g_options['scripturl'] . $link . "\">\r\n";
  Echo "            <span class=\"ml-4\">- " . $name . "</span>\r\n";
  echo "        </a>\r\n";
  echo "    </li>\r\n";

}

?>