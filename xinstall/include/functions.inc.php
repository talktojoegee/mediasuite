<?php


/**
 * 	Prepare reading of SQL dump file and executing SQL statements
 * 		@param $sql_dump_file
 */
function sap_db_install($sql_dump_file) {

    global $error_mg;
    global $admin_username;
    global $admin_first_name;
    global $admin_last_name;
    global $admin_password;
    global $admin_email;
    global $database_prefix;
    global $db;

    $sql_array = array();
    $query = '';

    // get  sql dump content
    $sql_dump = file($sql_dump_file);

    // replace database prefix if exists
    $sql_dump = str_ireplace('<DB_PREFIX>', $database_prefix, $sql_dump);

    if (version_compare(phpversion(), '8.0.0') < 0 ) {

        // disabling magic quotes at runtime
        if (get_magic_quotes_runtime()) { 

            function stripslashes_runtime(&$value) {
                $value = stripslashes($value);
            }

            array_walk_recursive($sql_dump, 'stripslashes_runtime');
        }
    }

    // add ';' at the end of file to catch last sql query
    if (substr($sql_dump[count($sql_dump) - 1], -1) != ';')
        $sql_dump[count($sql_dump) - 1] .= ';';

    // replace username,email and password
    $sql_dump = str_ireplace('<USER_NAME>', $admin_username, $sql_dump);

    $sql_dump = str_ireplace('<USER_FIRST_NAME>', $admin_first_name, $sql_dump);

    $sql_dump = str_ireplace('<USER_LAST_NAME>', $admin_last_name, $sql_dump);

    $sql_dump = str_ireplace('<USER_EMAIL>', $admin_email, $sql_dump);


    $sql_dump = str_ireplace('<USER_EMAIL>', $admin_email, $sql_dump);

    // Apply MD5 encryption to password
    $admin_password = md5($admin_password);
    $sql_dump = str_ireplace('<PASSWORD>', $admin_password, $sql_dump);

    $sql_dump = str_ireplace('<CREATED>', date('Y-m-d H:i:s'), $sql_dump);
    $sql_dump = str_ireplace('<MODIFIED>', date('Y-m-d H:i:s'), $sql_dump);

    // encode connection, server, client etc.	
    if (SAP_USE_ENCODING) {
        $db->SetEncoding(SAP_DUMP_FILE_ENCODING, SAP_DUMP_FILE_COLLATION);
    }

    foreach ($sql_dump as $sql_line) {
        $tsl = trim(utf8_decode($sql_line));
        if (($sql_line != '') && (substr($tsl, 0, 2) != '--') && (substr($tsl, 0, 1) != '?') && (substr($tsl, 0, 1) != '#')) {
            $query .= $sql_line;
            if (preg_match("/;\s*$/", $sql_line)) {
                if (strlen(trim($query)) > 5) {

                    if (!$db->Query($query)) {
                        $error_mg[] = '<b>SQL</b>:' . $query . '<br /><br /><b>Error</b>:<br />' . $db->Error();
                        return false;
                    }
                }
                $query = '';
            }
        }
    }
    return true;
}

/**
 * 	Remove bad chars from input
 * 	  	@param $str_words - input
 * */
function prepare_input($str_words, $escape = false, $level = 'high') {
    $found = false;
    $str_words = htmlentities(strip_tags($str_words));
    if ($level == 'low') {
        $bad_string = array('drop', '--', 'insert', 'xp_', '%20union%20', '/*', '*/union/*', '+union+', 'load_file', 'outfile', 'document.cookie', 'onmouse', '<script', '<iframe', '<applet', '<meta', '<style', '<form', '<body', '<link', '_GLOBALS', '_REQUEST', '_GET', '_POST', 'include_path', 'prefix', 'ftp://', 'smb://', 'onmouseover=', 'onmouseout=');
    } else if ($level == 'medium') {
        $bad_string = array('select', 'drop', '--', 'insert', 'xp_', '%20union%20', '/*', '*/union/*', '+union+', 'load_file', 'outfile', 'document.cookie', 'onmouse', '<script', '<iframe', '<applet', '<meta', '<style', '<form', '<body', '<link', '_GLOBALS', '_REQUEST', '_GET', '_POST', 'include_path', 'prefix', 'ftp://', 'smb://', 'onmouseover=', 'onmouseout=');
    } else {
        $bad_string = array('select', 'drop', '--', 'insert', 'xp_', '%20union%20', '/*', '*/union/*', '+union+', 'load_file', 'outfile', 'document.cookie', 'onmouse', '<script', '<iframe', '<applet', '<meta', '<style', '<form', '<img', '<body', '<link', '_GLOBALS', '_REQUEST', '_GET', '_POST', 'include_path', 'prefix', 'http://', 'https://', 'ftp://', 'smb://', 'onmouseover=', 'onmouseout=');
    }
    for ($i = 0; $i < count($bad_string); $i++) {
        $str_words = str_replace($bad_string[$i], '', $str_words);
    }

    if ($escape) {
        $str_words = encode_text($str_words);
    }

    return $str_words;
}

function is_html($string)
{
  return preg_match("/<[^<]+>/",$string,$m) != 0;
}

/**
 * 	Get encoded text
 * 		@param $string
 */
function encode_text($string = '') {
    $search = array("\\", "\0", "\n", "\r", "\x1a", "'", '"', "\'", '\"');
    $replace = array("\\\\", "\\0", "\\n", "\\r", "\Z", "\'", '\"', "\\'", '\\"');
    return str_replace($search, $replace, $string);
}

function is_email($value) {
    return preg_match('/^[\w-]+(?:\.[\w-]+)*@(?:[\w-]+\.)+[a-zA-Z]{2,7}$/', $value);
}

function draw_side_navigation($step = 1, $draw = true) {
    global $sap_common;

    $steps = array(
        '1' => array('url' => 'index.php', 'text' => $sap_common->lang('install_menu_start')),
        '2' => array('url' => 'server_requirements.php', 'text' => $sap_common->lang('install_menu_server_requirements')),
        '3' => array('url' => 'database_settings.php', 'text' => $sap_common->lang('install_menu_db_settings')),
        '4' => array('url' => 'administrator_account.php', 'text' => $sap_common->lang('install_menu_admin_account')),
        '5' => array('url' => 'ready_to_install.php', 'text' => $sap_common->lang('install_menu_ready_to_install')),
        '6' => array('url' => 'complete_installation.php', 'text' => $sap_common->lang('install_menu_completed'))
    );

    $output = '<div class="left-part">';
    $output .= '<ul class="left-menu">';
    foreach ($steps as $key => $val) {
        if ($step > $key) {
            $css_class = ' class="passed"';
            $output .= '<li' . $css_class . '><a href="' . $val['url'] . '">' . $val['text'] . '</a></li>';
        } else if ($step == $key) {
            $css_class = ' class="current"';
            $output .= '<li' . $css_class . '><label>' . $val['text'] . '</label></li>';
        } else {
            $output .= '<li><label>' . $val['text'] . '</label></li>';
        }
    }
    $output .= '</ul>';
    $output .= '</div>';

    if ($draw)
        echo $output;
    else
        return $output;
}

function is_ssl() {
    if ( isset( $_SERVER['HTTPS'] ) ) {
        if ( 'on' === strtolower( $_SERVER['HTTPS'] ) ) {
            return true;
        }
 
        if ( '1' == $_SERVER['HTTPS'] ) {
            return true;
        }
    } elseif ( isset( $_SERVER['SERVER_PORT'] ) && ( '443' == $_SERVER['SERVER_PORT'] ) ) {
        return true;
    }
    return false;
}