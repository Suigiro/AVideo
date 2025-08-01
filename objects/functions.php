<?php
$AVideoMobileAPP_UA = "AVideoMobileApp";
$AVideoEncoder_UA = "AVideoEncoder";
$AVideoEncoderNetwork_UA = "AVideoEncoderNetwork";
$AVideoStreamer_UA = "AVideoStreamer";
$AVideoStorage_UA = "AVideoStorage";
$mysql_connect_was_closed = 1;

function forbiddenWords($text) {
    global $global;
    if (empty($global['forbiddenWords'])) {
        return false;
    }
    foreach ($global['forbiddenWords'] as $value) {
        if (preg_match("/{$value}/i", $text)) {
            return true;
        }
    }
    return false;
}

if (!function_exists('xss_esc')) {

    function xss_esc($text) {
        if (empty($text)) {
            return "";
        }
        if (!is_string($text)) {
            if (is_array($text)) {
                foreach ($text as $key => $value) {
                    $text[$key] = xss_esc($value);
                }
            }
            return $text;
        }
        $result = @htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
        if (empty($result)) {
            $result = str_replace(['"', "'", "\\"], ["", "", ""], strip_tags($text));
        }
        $result = str_replace(['&amp;amp;'], ['&amp;'], $result);
        return $result;
    }

}

function xss_esc_back($text) {
    $text = htmlspecialchars_decode($text, ENT_QUOTES);
    $text = str_replace(['&amp;', '&#039;', "#039;"], [" ", "`", "`"], $text);
    return $text;
}

// Make sure SecureVideosDirectory will be the first
function cmpPlugin($a, $b) {
    if (
            $a['name'] === 'SecureVideosDirectory' ||
            $a['name'] === 'GoogleAds_IMA' ||
            $a['name'] === 'Subscription' ||
            $a['name'] === 'PayPerView' ||
            $a['name'] === 'FansSubscriptions'
    ) {
        return -1;
    } elseif ($a['name'] === 'PlayerSkins') {
        return 1;
    } elseif (
            $b['name'] === 'SecureVideosDirectory' ||
            $b['name'] === 'GoogleAds_IMA' ||
            $b['name'] === 'Subscription' ||
            $b['name'] === 'PayPerView' ||
            $b['name'] === 'FansSubscriptions'
    ) {
        return 1;
    } elseif ($b['name'] === 'PlayerSkins') {
        return -1;
    }
    return 0;
}

// Returns a file size limit in bytes based on the PHP upload_max_filesize
// and post_max_size
function file_upload_max_size() {
    static $max_size = -1;

    if ($max_size < 0) {
        // Start with post_max_size.
        $max_size = parse_size(ini_get('post_max_size'));

        // If upload_max_size is less, then reduce. Except if upload_max_size is
        // zero, which indicates no limit.
        $upload_max = parse_size(ini_get('upload_max_filesize'));
        if ($upload_max > 0 && $upload_max < $max_size) {
            $max_size = $upload_max;
        }
    }
    return $max_size;
}

function parse_size($size) {
    $unit = preg_replace('/[^bkmgtpezy]/i', '', $size); // Remove the non-unit characters from the size.
    $size = preg_replace('/[^0-9\.]/', '', $size); // Remove the non-numeric characters from the size.
    if ($unit) {
        // Find the position of the unit in the ordered string which is the power of magnitude to multiply a kilobyte by.
        return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
    } else {
        return round($size);
    }
}

function humanFileSize($size, $unit = "") {
    if ((!$unit && $size >= 1 << 30) || $unit == "GB") {
        return number_format($size / (1 << 30), 2) . "GB";
    }

    if ((!$unit && $size >= 1 << 20) || $unit == "MB") {
        return number_format($size / (1 << 20), 2) . "MB";
    }

    if ((!$unit && $size >= 1 << 10) || $unit == "KB") {
        return number_format($size / (1 << 10), 2) . "KB";
    }

    return number_format($size) . " bytes";
}

function get_max_file_size() {
    return humanFileSize(file_upload_max_size());
}

function humanTiming($time, $precision = 0, $useDatabaseTime = true) {
    $time = secondsIntervalFromNow($time, $useDatabaseTime);
    return secondsToHumanTiming($time, $precision);
}

/**
 *
 * @param type $time
 * @param type $precision
 * @param type $useDatabaseTime good if you are checking the created time
 * @return type
 */
function humanTimingAgo($time, $precision = 0, $useDatabaseTime = true) {
    $time = secondsIntervalFromNow($time, $useDatabaseTime);
    if (empty($time)) {
        return __("Now");
    }
    return secondsToHumanTiming($time, $precision) . " " . __("ago");
}

function humanTimingAfterwards($time, $precision = 0, $useDatabaseTime = true) {
    if (!is_numeric($time)) {
        $time = strtotime($time);
    }
    $time = secondsIntervalFromNow($time, $useDatabaseTime);
    if (empty($time)) {
        return __("Now");
    } elseif ($time > 0) {
        return secondsToHumanTiming($time, $precision) . ' ' . __('Ago');
    }
    return __('Coming in') . ' ' . secondsToHumanTiming($time, $precision);
}

function secondsToHumanTiming($time, $precision = 0) {
    if (empty($time)) {
        return __("Now");
    }
    $time = ($time < 0) ? $time * -1 : $time;
    $time = ($time < 1) ? 1 : $time;
    $tokens = [
        31536000 => 'year',
        2592000 => 'month',
        604800 => 'week',
        86400 => 'day',
        3600 => 'hour',
        60 => 'minute',
        1 => 'second',
    ];

    /**
     * For detection purposes only
     */
    __('year');
    __('month');
    __('week');
    __('day');
    __('hour');
    __('minute');
    __('second');
    __('years');
    __('months');
    __('weeks');
    __('days');
    __('hours');
    __('minutes');
    __('seconds');

    foreach ($tokens as $unit => $text) {
        if ($time < $unit) {
            continue;
        }

        $numberOfUnits = floor($time / $unit);
        if ($numberOfUnits > 1) {
            $text = __($text . "s");
        } else {
            $text = __($text);
        }

        if ($precision) {
            $rest = $time % $unit;
            if ($rest) {
                $text .= ' ' . secondsToHumanTiming($rest, $precision - 1);
            }
        }

        return $numberOfUnits . ' ' . $text;
    }
}

function checkVideosDir() {
    $dir = "../videos";
    if (file_exists($dir)) {
        return is_writable($dir);
    }
    return mkdir($dir);
}

function isApache() {
    return (strpos($_SERVER['SERVER_SOFTWARE'], 'Apache') !== false);
}

function isPHP($version = "'7.3.0'") {
    return (version_compare(PHP_VERSION, $version) >= 0);
}

function modEnabled($mod_name) {
    if (!function_exists('apache_get_modules')) {
        _ob_start();
        phpinfo(INFO_MODULES);
        $contents = ob_get_contents();
        _ob_end_clean();
        return (strpos($contents, 'mod_' . $mod_name) !== false);
    }
    return in_array('mod_' . $mod_name, apache_get_modules());
}

function modRewriteEnabled() {
    return modEnabled("rewrite");
}

function modAliasEnabled() {
    return modEnabled("alias");
}

function isFFMPEG() {
    return trim(shell_exec('which ffmpeg'));
}

function isUnzip() {
    return trim(shell_exec('which unzip'));
}

function isExifToo() {
    return trim(shell_exec('which exiftool'));
}

function isAPPInstalled($appName) {
    $appName = preg_replace('/[^a-z0-9_-]/i', '', $appName);
    return trim(shell_exec("which {$appName}"));
}

function getPathToApplication() {
    return str_replace("install/index.php", "", $_SERVER["SCRIPT_FILENAME"]);
}

function getURLToApplication() {
    $url = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    $url = explode("install/index.php", $url);
    return $url[0];
}

//max_execution_time = 7200
function check_max_execution_time() {
    $max_size = ini_get('max_execution_time');
    $recomended_size = 7200;
    return ($recomended_size <= $max_size);
}

//post_max_size = 100M
function check_post_max_size() {
    $max_size = parse_size(ini_get('post_max_size'));
    $recomended_size = parse_size('100M');
    return ($recomended_size <= $max_size);
}

//upload_max_filesize = 100M
function check_upload_max_filesize() {
    $max_size = parse_size(ini_get('upload_max_filesize'));
    $recomended_size = parse_size('100M');
    return ($recomended_size <= $max_size);
}

//memory_limit = 100M
function check_memory_limit() {
    $max_size = parse_size(ini_get('memory_limit'));
    $recomended_size = parse_size('512M');
    return ($recomended_size <= $max_size);
}

function check_mysqlnd() {
    return function_exists('mysqli_fetch_all');
}

function base64DataToImage($imgBase64) {
    $img = $imgBase64;
    $img = str_replace('data:image/png;base64,', '', $img);
    $img = str_replace(' ', '+', $img);
    return base64_decode($img);
}

function getRealIpAddr() {
    if (isCommandLineInterface()) {
        $ip = "127.0.0.1";
    } else if (!empty($_SERVER['HTTP_CLIENT_IP'])) { //check ip from share internet
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) { //to check ip is pass from proxy
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
        $ip = $_SERVER['REMOTE_ADDR'];
    } else {
        $ip = "127.0.0.1";
    }
    return $ip;
}

function cleanString($text) {
    $utf8 = [
        '/[áaâaaäą]/u' => 'a',
        '/[ÁAÂAÄĄ]/u' => 'A',
        '/[ÍIÎI]/u' => 'I',
        '/[íiîi]/u' => 'i',
        '/[éeeëę]/u' => 'e',
        '/[ÉEEËĘ]/u' => 'E',
        '/[óoôooö]/u' => 'o',
        '/[ÓOÔOÖ]/u' => 'O',
        '/[úuuü]/u' => 'u',
        '/[ÚUUÜ]/u' => 'U',
        '/[çć]/u' => 'c',
        '/[ÇĆ]/u' => 'C',
        '/[nń]/u' => 'n',
        '/[NŃ]/u' => 'N',
        '/[żź]/u' => 'z',
        '/[ŻŹ]/u' => 'Z',
        '/ł/' => 'l',
        '/Ł/' => 'L',
        '/ś/' => 's',
        '/Ś/' => 'S',
        '/–/' => '-', // UTF-8 hyphen to 'normal' hyphen
        '/[’‘‹›‚]/u' => ' ', // Literally a single quote
        '/[“”«»„]/u' => ' ', // Double quote
        '/ /' => ' ', // nonbreaking space (equiv. to 0x160)
        '/Є/' => 'YE', '/І/' => 'I', '/Ѓ/' => 'G', '/і/' => 'i', '/№/' => '#', '/є/' => 'ye', '/ѓ/' => 'g',
        '/А/' => 'A', '/Б/' => 'B', '/В/' => 'V', '/Г/' => 'G', '/Д/' => 'D',
        '/Е/' => 'E', '/Ё/' => 'YO', '/Ж/' => 'ZH',
        '/З/' => 'Z', '/И/' => 'I', '/Й/' => 'J', '/К/' => 'K', '/Л/' => 'L',
        '/М/' => 'M', '/Н/' => 'N', '/О/' => 'O', '/П/' => 'P', '/Р/' => 'R',
        '/С/' => 'S', '/Т/' => 'T', '/У/' => 'U', '/Ф/' => 'F', '/Х/' => 'H',
        '/Ц/' => 'C', '/Ч/' => 'CH', '/Ш/' => 'SH', '/Щ/' => 'SHH', '/Ъ/' => '',
        '/Ы/' => 'Y', '/Ь/' => '', '/Э/' => 'E', '/Ю/' => 'YU', '/Я/' => 'YA',
        '/а/' => 'a', '/б/' => 'b', '/в/' => 'v', '/г/' => 'g', '/д/' => 'd',
        '/е/' => 'e', '/ё/' => 'yo', '/ж/' => 'zh',
        '/з/' => 'z', '/и/' => 'i', '/й/' => 'j', '/к/' => 'k', '/л/' => 'l',
        '/м/' => 'm', '/н/' => 'n', '/о/' => 'o', '/п/' => 'p', '/р/' => 'r',
        '/с/' => 's', '/т/' => 't', '/у/' => 'u', '/ф/' => 'f', '/х/' => 'h',
        '/ц/' => 'c', '/ч/' => 'ch', '/ш/' => 'sh', '/щ/' => 'shh', '/ъ/' => '',
        '/ы/' => 'y', '/ь/' => '', '/э/' => 'e', '/ю/' => 'yu', '/я/' => 'ya',
        '/—/' => '-', '/«/' => '', '/»/' => '', '/…/' => '',
    ];
    return preg_replace(array_keys($utf8), array_values($utf8), $text);
}

function safeString($text, $strict = false) {
    $text = strip_tags($text);
    $text = str_replace(array('&amp;', '&lt;', '&gt;'), array('', '', ''), $text);
    $text = preg_replace('/(&#*\w+)[\x00-\x20]+;/u', '', $text);
    $text = preg_replace('/(&#x*[0-9A-F]+);*/iu', '', $text);
    $text = html_entity_decode($text, ENT_COMPAT, 'UTF-8');
    if ($strict) {
        $text = filter_var($text, FILTER_SANITIZE_STRING);
        //$text = cleanURLName($text);
    }
    $text = trim($text);
    return $text;
}

function cleanURLName($name, $replaceChar = '-') {
    $name = preg_replace('/[!#$&\'()*+,\\/:;=?@[\\]%"\/\\\\ ]+/', $replaceChar, trim(strtolower(cleanString($name))));
    return trim(preg_replace('/[\x00-\x1F\x7F\xD7\xE0]/u', $replaceChar, $name), $replaceChar);
}

/**
 * @brief return true if running in CLI, false otherwise
 * if is set $_GET['ignoreCommandLineInterface'] will return false
 * @return boolean
 */
function isCommandLineInterface() {
    return (empty($_GET['ignoreCommandLineInterface']) && php_sapi_name() === 'cli');
}

/**
 * @brief show status message as text (CLI) or JSON-encoded array (web)
 *
 * @param array $statusarray associative array with type/message pairs
 * @return string
 */
function status($statusarray) {
    if (isCommandLineInterface()) {
        foreach ($statusarray as $status => $message) {
            echo $status . ":" . $message . "\n";
        }
    } else {
        echo json_encode(array_map(function ($text) {
                    return nl2br($text);
                }, $statusarray));
    }
}

/**
 * @brief show status message and die
 *
 * @param array $statusarray associative array with type/message pairs
 */
function croak($statusarray) {
    status($statusarray);
    die;
}

function getSecondsTotalVideosLength() {
    $configFile = dirname(__FILE__) . '/../videos/configuration.php';
    require_once $configFile;
    global $global;

    if (!User::isLogged()) {
        return 0;
    }
    $sql = "SELECT * FROM videos v ";
    $formats = '';
    $values = [];
    if (!User::isAdmin()) {
        $id = User::getId();
        $sql .= " WHERE users_id = ? ";
        $formats = "i";
        $values = [$id];
    }

    $res = sqlDAL::readSql($sql, $formats, $values);
    $fullData = sqlDAL::fetchAllAssoc($res);
    sqlDAL::close($res);
    $seconds = 0;
    foreach ($fullData as $row) {
        $seconds += parseDurationToSeconds($row['duration']);
    }
    return $seconds;
}

function getMinutesTotalVideosLength() {
    $seconds = getSecondsTotalVideosLength();
    return floor($seconds / 60);
}

function secondsToVideoTime($seconds) {
    if (!is_numeric($seconds)) {
        return $seconds;
    }
    $seconds = round($seconds);
    $hours = floor($seconds / 3600);
    $mins = floor(intval($seconds / 60) % 60);
    $secs = floor($seconds % 60);
    return sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
}

function parseSecondsToDuration($seconds) {
    return secondsToVideoTime($seconds);
}

function parseDurationToSeconds($str) {
    if (is_numeric($str)) {
        return intval($str);
    }
    $durationParts = explode(":", $str);
    if (empty($durationParts[1]) || $durationParts[0] == "EE") {
        return 0;
    }
    if (empty($durationParts[2])) {
        $durationParts[2] = 0;
    }
    $minutes = intval(($durationParts[0]) * 60) + intval($durationParts[1]);
    return intval($durationParts[2]) + ($minutes * 60);
}

function durationToSeconds($str) {
    return parseDurationToSeconds($str);
}

function secondsToDuration($seconds) {
    return parseSecondsToDuration($seconds);
}

/**
 *
 * @global type $global
 * @param type $mail
 * call it before send mail to let AVideo decide the method
 */
function setSiteSendMessage(&$mail) {
    global $global;
    if (empty($_POST["comment"])) {
        $_POST["comment"] = '';
    }
    require_once $global['systemRootPath'] . 'objects/configuration.php';
    $config = new Configuration();
    $mail->CharSet = 'UTF-8';
    if ($config->getSmtp()) {
        _error_log("Sending SMTP Email");
        $mail->CharSet = 'UTF-8';
        $mail->IsSMTP(); // enable SMTP
        if (!empty($_POST) && $_POST["comment"] == "Test of comment" && User::isAdmin()) {
            $mail->SMTPDebug = 3;
            $mail->Debugoutput = function ($str, $level) {
                _error_log("SMTP ERROR $level; message: $str", AVideoLog::$ERROR);
            };

            _error_log("Debug enable on the SMTP Email");
        }
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true,
            ],
        ];
        $mail->SMTPAuth = $config->getSmtpAuth(); // authentication enabled
        $mail->SMTPSecure = $config->getSmtpSecure(); // secure transfer enabled REQUIRED for Gmail
        $mail->Host = $config->getSmtpHost();
        $mail->Port = $config->getSmtpPort();
        $mail->Username = $config->getSmtpUsername();
        $mail->Password = $config->getSmtpPassword();
        //_error_log(print_r($config, true));
    } else {
        _error_log("Sending SendMail Email");
        $mail->isSendmail();
    }
    // do not let the system hang on email send
    session_write_close();
}

function array_iunique($array) {
    return array_intersect_key($array, array_unique(array_map("strtolower", $array)));
}

function partition(array $list, $totalItens) {
    $listlen = count($list);
    _error_log("partition: listlen={$listlen} totalItens={$totalItens}");
    $p = ceil($listlen / $totalItens);
    $partlen = floor($listlen / $p);

    $partition = [];
    $mark = 0;
    for ($index = 0; $index < $p; $index++) {
        $partition[$index] = array_slice($list, $mark, $totalItens);
        $mark += $totalItens;
    }

    return $partition;
}

function sendSiteEmail($to, $subject, $message, $fromEmail = '', $fromName = '') {
    global $advancedCustom;
    $resp = false;
    if (empty($to)) {
        return false;
    }
    if (is_object($to)) {
        $to = object_to_array($to);
    }
    if (!is_array($to)) {
        $to = [$to];
    }

    if (empty($advancedCustom)) {
        $advancedCustom = AVideoPlugin::loadPlugin("CustomizeAdvanced");
    }

    $subject = UTF8encode($subject);
    $message = UTF8encode($message);
    $message = createEmailMessageFromTemplate($message);
    
    $total = count($to);
    if($total == 1){
        $debug = $to[0];
    }else{
        $debug = "count={$total}";
    }
    
    _error_log("sendSiteEmail [{$debug}] {$subject}");
    global $config, $global;
    //require_once $global['systemRootPath'] . 'objects/include_phpmailer.php';
    if (empty($fromEmail)) {
        $fromEmail = $config->getContactEmail();
    }
    if (empty($fromName)) {
        $fromName = $config->getWebSiteTitle();
    }
    $webSiteTitle = $config->getWebSiteTitle();
    try {
        if (!is_array($to)) {
            $mail = new \PHPMailer\PHPMailer\PHPMailer();
            setSiteSendMessage($mail);
            $mail->setFrom($fromEmail, $fromName);
            $mail->Subject = $subject . " - " . $webSiteTitle;
            $mail->msgHTML($message);

            $mail->addAddress($to);

            $resp = $mail->send();
            if (!$resp) {
                _error_log("sendSiteEmail Error Info: {$mail->ErrorInfo}");
            } else {
                _error_log("sendSiteEmail Success Info: $subject " . json_encode($to));
            }
        } else {
            $size = intval(@$advancedCustom->splitBulkEmailSend);
            if (empty($size)) {
                $size = 90;
            }

            $to = array_iunique($to);
            $pieces = partition($to, $size);
            $totalEmails = count($to);
            $totalCount = 0;
            _error_log("sendSiteEmail::sending totalEmails=[{$totalEmails}]");
            foreach ($pieces as $piece) {
                $mail = new \PHPMailer\PHPMailer\PHPMailer();
                setSiteSendMessage($mail);
                $mail->setFrom($fromEmail, $fromName);
                $mail->Subject = $subject . " - " . $webSiteTitle;
                $mail->msgHTML($message);
                $count = 0;
                foreach ($piece as $value) {
                    $totalCount++;
                    $count++;
                    //_error_log("sendSiteEmail::addBCC [{$count}] {$value}");
                    $mail->addBCC($value);
                }
                //_error_log("sendSiteEmail::sending now count=[{$count}] [{$totalCount}/{$totalEmails}]");

                $resp = $mail->send();
                if (!$resp) {
                    _error_log("sendSiteEmail Error Info: {$mail->ErrorInfo} count=[{$count}] [{$totalCount}/{$totalEmails}]");
                } else {
                    _error_log("sendSiteEmail Success Info: count=[{$count}] [{$totalCount}/{$totalEmails}]");
                }
            }
            
        }
        //Set the subject line
        return $resp;
    } catch (phpmailerException $e) {
        _error_log($e->errorMessage()); //Pretty error messages from PHPMailer
    } catch (Exception $e) {
        _error_log($e->getMessage()); //Boring error messages from anything else!
    }
    return $resp;
}

function sendSiteEmailAsync($to, $subject, $message) {
    global $global;
    $content = ['to' => $to, 'subject' => $subject, 'message' => $message];
    $tmpFile = getTmpFile();
    file_put_contents($tmpFile, _json_encode($content));
    //outputAndContinueInBackground();
    $command = "php {$global['systemRootPath']}objects/sendSiteEmailAsync.php '$tmpFile'";
    $totalEmails = count($to);
    _error_log("sendSiteEmailAsync start [totalEmails={$totalEmails}] ($command)");
    $pid = execAsync($command);
    _error_log("sendSiteEmailAsync end {$pid}");
    return $pid;
}

function createEmailMessageFromTemplate($message) {

    //check if the message already have a HTML body
    if (preg_match("/html>/i", $message)) {
        return $message;
    }

    global $global, $config;
    $text = file_get_contents("{$global['systemRootPath']}view/include/emailTemplate.html");
    $siteTitle = $config->getWebSiteTitle();
    $logo = "<img src=\"" . getURL($config->getLogo()) . "\" alt=\"{$siteTitle}\"/>";

    $words = [$logo, $message, $siteTitle];
    $replace = ['{logo}', '{message}', '{siteTitle}'];

    return str_replace($replace, $words, $text);
}

function sendEmailToSiteOwner($subject, $message) {
    global $advancedCustom, $global;
    $subject = UTF8encode($subject);
    $message = UTF8encode($message);
    _error_log("sendEmailToSiteOwner {$subject}");
    global $config, $global;
    //require_once $global['systemRootPath'] . 'objects/include_phpmailer.php';
    $contactEmail = $config->getContactEmail();
    $webSiteTitle = $config->getWebSiteTitle();
    try {
        $mail = new \PHPMailer\PHPMailer\PHPMailer();
        setSiteSendMessage($mail);
        $mail->setFrom($contactEmail, $webSiteTitle);
        $mail->Subject = $subject . " - " . $webSiteTitle;
        $mail->msgHTML($message);
        $mail->addAddress($contactEmail);
        $resp = $mail->send();
        if (!$resp) {
            _error_log("sendEmailToSiteOwner Error Info: {$mail->ErrorInfo}");
        } else {
            _error_log("sendEmailToSiteOwner Success Info: $subject " . json_encode($to));
        }
        return $resp;
    } catch (phpmailerException $e) {
        _error_log($e->errorMessage()); //Pretty error messages from PHPMailer
    } catch (Exception $e) {
        _error_log($e->getMessage()); //Boring error messages from anything else!
    }
}

function parseVideos($videoString = null, $autoplay = 0, $loop = 0, $mute = 0, $showinfo = 0, $controls = 1, $time = 0, $objectFit = "") {
    global $global;
    //_error_log("parseVideos: $videoString");
    if (strpos($videoString, 'youtube.com/embed') !== false) {
        return $videoString . (parse_url($videoString, PHP_URL_QUERY) ? '&' : '?') . 'modestbranding=1&showinfo='
                . $showinfo . "&autoplay={$autoplay}&controls=$controls&loop=$loop&mute=$mute&t=$time&objectFit=$objectFit";
    }
    if (strpos($videoString, 'iframe') !== false) {
        // retrieve the video url
        $anchorRegex = '/src="(.*)?"/isU';
        $results = [];
        if (preg_match($anchorRegex, $video, $results)) {
            $link = trim($results[1]);
        }
    } else {
        // we already have a url
        $link = $videoString;
    }

    if (stripos($link, 'embed') !== false) {
        return $link . (parse_url($link, PHP_URL_QUERY) ? '&' : '?') . 'modestbranding=1&showinfo='
                . $showinfo . "&autoplay={$autoplay}&controls=$controls&loop=$loop&mute=$mute&t=$time&objectFit=$objectFit";
    } elseif (strpos($link, 'youtube.com') !== false) {
        preg_match(
                '/[\\?\\&]v=([^\\?\\&]+)/',
                $link,
                $matches
        );
        //the ID of the YouTube URL: x6qe_kVaBpg
        if (empty($matches[1])) {
            return $link;
        }
        $id = $matches[1];
        return '//www.youtube.com/embed/' . $id . '?modestbranding=1&showinfo='
                . $showinfo . "&autoplay={$autoplay}&controls=$controls&loop=$loop&mute=$mute&te=$time&objectFit=$objectFit";
    } elseif (strpos($link, 'youtu.be') !== false) {
        //https://youtu.be/9XXOBSsPoMU
        preg_match(
                '/youtu.be\/([a-zA-Z0-9_]+)($|\/)/',
                $link,
                $matches
        );
        //the ID of the YouTube URL: x6qe_kVaBpg
        $id = $matches[1];
        return '//www.youtube.com/embed/' . $id . '?modestbranding=1&showinfo='
                . $showinfo . "&autoplay={$autoplay}&controls=$controls&loop=$loop&mute=$mute&te=$time&objectFit=$objectFit";
    } elseif (strpos($link, 'player.vimeo.com') !== false) {
        // works on:
        // http://player.vimeo.com/video/37985580?title=0&amp;byline=0&amp;portrait=0
        $videoIdRegex = '/player.vimeo.com\/video\/([0-9]+)\??/i';
        preg_match($videoIdRegex, $link, $matches);
        $id = $matches[1];
        return '//player.vimeo.com/video/' . $id;
    } elseif (strpos($link, 'vimeo.com/channels') !== false) {
        //extract the ID
        preg_match(
                '/\/\/(www\.)?vimeo.com\/channels\/[a-z0-9-]+\/(\d+)($|\/)/i',
                $link,
                $matches
        );

        //the ID of the Vimeo URL: 71673549
        $id = $matches[2];
        return '//player.vimeo.com/video/' . $id;
    } elseif (strpos($link, 'vimeo.com') !== false) {
        //extract the ID
        preg_match(
                '/\/\/(www\.)?vimeo.com\/(\d+)($|\/)/',
                $link,
                $matches
        );

        //the ID of the Vimeo URL: 71673549
        $id = $matches[2];
        return '//player.vimeo.com/video/' . $id;
    } elseif (strpos($link, 'dailymotion.com') !== false) {
        //extract the ID
        preg_match(
                '/\/\/(www\.)?dailymotion.com\/video\/([a-zA-Z0-9_]+)($|\/)/',
                $link,
                $matches
        );

        //the ID of the Vimeo URL: 71673549
        $id = $matches[2];
        return '//www.dailymotion.com/embed/video/' . $id;
    } elseif (strpos($link, 'metacafe.com') !== false) {
        //extract the ID
        preg_match(
                '/\/\/(www\.)?metacafe.com\/watch\/([a-zA-Z0-9_\/-]+)$/',
                $link,
                $matches
        );
        $id = $matches[2];
        return '//www.metacafe.com/embed/' . $id;
    } elseif (strpos($link, 'vid.me') !== false) {
        //extract the ID
        preg_match(
                '/\/\/(www\.)?vid.me\/([a-zA-Z0-9_-]+)$/',
                $link,
                $matches
        );

        $id = $matches[2];
        return '//vid.me/e/' . $id;
    } elseif (strpos($link, 'rutube.ru') !== false) {
        //extract the ID
        preg_match('/\/\/(www\.)?rutube.ru\/video\/([a-zA-Z0-9_-]+)\/.*/', $link, $matches);
        $id = $matches[2];
        return '//rutube.ru/play/embed/' . $id;
    } elseif (strpos($link, 'ok.ru') !== false) {
        //extract the ID
        preg_match('/\/\/(www\.)?ok.ru\/video\/([a-zA-Z0-9_-]+)$/', $link, $matches);

        $id = $matches[2];
        return '//ok.ru/videoembed/' . $id;
    } elseif (strpos($link, 'streamable.com') !== false) {
        //extract the ID
        preg_match('/\/\/(www\.)?streamable.com\/([a-zA-Z0-9_-]+)$/', $link, $matches);

        $id = $matches[2];
        return '//streamable.com/s/' . $id;
    } elseif (strpos($link, 'twitch.tv/videos') !== false) {
        //extract the ID
        preg_match('/\/\/(www\.)?twitch.tv\/videos\/([a-zA-Z0-9_-]+)$/', $link, $matches);
        if (!empty($matches[2])) {
            $id = $matches[2];
            return '//player.twitch.tv/?video=' . $id . '&parent=' . parse_url($global['webSiteRootURL'], PHP_URL_HOST);
        }
        //extract the ID
        preg_match('/\/\/(www\.)?twitch.tv\/[a-zA-Z0-9_-]+\/v\/([a-zA-Z0-9_-]+)$/', $link, $matches);

        $id = $matches[2];
        return '//player.twitch.tv/?video=' . $id . '&parent=' . parse_url($global['webSiteRootURL'], PHP_URL_HOST);
    } elseif (strpos($link, 'twitch.tv') !== false) {
        //extract the ID
        preg_match('/\/\/(www\.)?twitch.tv\/([a-zA-Z0-9_-]+)$/', $link, $matches);

        $id = $matches[2];
        return '//player.twitch.tv/?channel=' . $id . '&parent=' . parse_url($global['webSiteRootURL'], PHP_URL_HOST);
    } elseif (strpos($link, 'bitchute.com/video') !== false) {
        //extract the ID
        preg_match('/\/\/(www\.)?bitchute.com\/video\/([^\/]+)/', $link, $matches);
        $id = $matches[2];
        return 'https://www.bitchute.com/embed/' . $id . '/?parent=' . parse_url($global['webSiteRootURL'], PHP_URL_HOST);
    } elseif (strpos($link, '/evideo/') !== false) {
        //extract the ID
        preg_match('/(http.+)\/evideo\/([a-zA-Z0-9_-]+)($|\/)/i', $link, $matches);

        //the AVideo site
        $site = $matches[1];
        $id = $matches[2];
        return $site . '/evideoEmbed/' . $id . "?autoplay={$autoplay}&controls=$controls&loop=$loop&mute=$mute&t=$time";
    } elseif (strpos($link, '/video/') !== false) {
        //extract the ID
        preg_match('/(http.+)\/video\/([a-zA-Z0-9_-]+)($|\/)/i', $link, $matches);

        //the AVideo site
        if (!empty($matches[1])) {
            $site = $matches[1];
            $id = $matches[2];
            return $site . '/videoEmbeded/' . $id . "?autoplay={$autoplay}&controls=$controls&loop=$loop&mute=$mute&t=$time";
        } else {
            return $link;
        }
    }

    $url = $videoString;
    $url_parsed = parse_url($url);
    if (empty($url_parsed['query'])) {
        return "";
    }
    $new_qs_parsed = [];
    // Grab our first query string
    parse_str($url_parsed['query'], $new_qs_parsed);
    // Here's the other query string
    $other_query_string = 'modestbranding=1&showinfo='
            . $showinfo . "&autoplay={$autoplay}&controls=$controls&loop=$loop&mute=$mute&t=$time";
    $other_qs_parsed = [];
    parse_str($other_query_string, $other_qs_parsed);
    // Stitch the two query strings together
    $final_query_string_array = array_merge($new_qs_parsed, $other_qs_parsed);
    $final_query_string = http_build_query($final_query_string_array);
    // Now, our final URL:
    if (empty($url_parsed['scheme'])) {
        $scheme = '';
    } else {
        $scheme = "{$url_parsed['scheme']}:";
    }
    $new_url = $scheme
            . '//'
            . $url_parsed['host']
            . $url_parsed['path']
            . '?'
            . $final_query_string;

    return $new_url;
    // return data
}

$canUseCDN = [];

function canUseCDN($videos_id) {
    if (empty($videos_id)) {
        return false;
    }
    global $global, $canUseCDN;
    if (!isset($canUseCDN[$videos_id])) {
        require_once $global['systemRootPath'] . 'plugin/VR360/Objects/VideosVR360.php';
        $pvr360 = AVideoPlugin::isEnabledByName('VR360');
        // if the VR360 is enabled you can not use the CDN, it fail to load the GL
        $isVR360Enabled = VideosVR360::isVR360Enabled($videos_id);
        if ($pvr360 && $isVR360Enabled) {
            $ret = false;
        } else {
            $ret = true;
        }

        //_error_log(json_encode(array('canUseCDN'=>$ret, '$pvr360'=>$pvr360, '$isVR360Enabled'=>$isVR360Enabled, '$videos_id'=>$videos_id)));
        $canUseCDN[$videos_id] = $ret;
    }
    return $canUseCDN[$videos_id];
}

function clearVideosURL($fileName = "") {
    global $global;
    $path = getCacheDir() . "getVideosURL/";
    if (empty($path)) {
        rrmdir($path);
    } else {
        $cacheFilename = "{$path}{$fileName}.cache";
        @unlink($cacheFilename);
    }
}

function maxLifetime() {
    global $maxLifetime;
    if (!isset($maxLifetime)) {
        $aws_s3 = AVideoPlugin::getObjectDataIfEnabled('AWS_S3');
        $bb_b2 = AVideoPlugin::getObjectDataIfEnabled('Blackblaze_B2');
        $secure = AVideoPlugin::getObjectDataIfEnabled('SecureVideosDirectory');
        $maxLifetime = 0;
        if (!empty($aws_s3) && empty($aws_s3->makeMyFilesPublicRead) && !empty($aws_s3->presignedRequestSecondsTimeout) && (empty($maxLifetime) || $aws_s3->presignedRequestSecondsTimeout < $maxLifetime)) {
            $maxLifetime = $aws_s3->presignedRequestSecondsTimeout;
            //_error_log("maxLifetime: AWS_S3 = {$maxLifetime}");
        }
        if (!empty($bb_b2) && empty($bb_b2->usePublicBucket) && !empty($bb_b2->presignedRequestSecondsTimeout) && (empty($maxLifetime) || $bb_b2->presignedRequestSecondsTimeout < $maxLifetime)) {
            $maxLifetime = $bb_b2->presignedRequestSecondsTimeout;
            //_error_log("maxLifetime: B2 = {$maxLifetime}");
        }
        if (!empty($secure) && !empty($secure->tokenTimeOut) && (empty($maxLifetime) || $secure->tokenTimeOut < $maxLifetime)) {
            $maxLifetime = $secure->tokenTimeOut;
            //_error_log("maxLifetime: Secure = {$maxLifetime}");
        }
    }
    return $maxLifetime;
}

$cacheExpirationTime = false;

function cacheExpirationTime() {
    if (isBot()) {
        return 604800; // 1 week
    }
    global $cacheExpirationTime;
    if (empty($cacheExpirationTime)) {
        $obj = AVideoPlugin::getObjectDataIfEnabled('Cache');
        $cacheExpirationTime = @$obj->cacheTimeInSeconds;
    }
    return intval($cacheExpirationTime);
}

/**
 * Tell whether a file should recreate a cache, based on its time and the plugin's token expiration.
 * @param type $filename
 * @return boolean
 */
function recreateCache($filename) {
    return (!file_exists($filename) || time() - filemtime($filename) > minimumExpirationTime());
}

function _getImagesURL($fileName, $type) {
    global $global;
    $files = [];
    $source = Video::getSourceFile($fileName, ".jpg");
    $file1 = $source['path'];
    if (file_exists($file1)) {
        $files["jpg"] = [
            'filename' => "{$fileName}.jpg",
            'path' => $file1,
            'url' => $source['url'],
            'type' => 'image',
        ];
    } else {
        unset($file1);
        $files["jpg"] = [
            'filename' => "{$type}.png",
            'path' => getCDN() . "view/img/{$type}.png",
            'url' => getCDN() . "view/img/{$type}.png",
            'type' => 'image',
        ];
    }
    $source = Video::getSourceFile($fileName, "_portrait.jpg");
    $file2 = $source['path'];
    if (file_exists($file2)) {
        $files["pjpg"] = [
            'filename' => "{$fileName}_portrait.jpg",
            'path' => $file2,
            'url' => $source['url'],
            'type' => 'image',
        ];
    } elseif ($type !== 'image') {
        if (!empty($file1)) {
            $files["pjpg"] = $files["jpg"];
        } else {
            $files["pjpg"] = [
                'filename' => "{$type}_portrait.png",
                'path' => getCDN() . "view/img/{$type}_portrait.png",
                'url' => getCDN() . "view/img/{$type}_portrait.png",
                'type' => 'image',
            ];
        }
    }
    return $files;
}

function getVideosURLPDF($fileName) {
    global $global;
    if (empty($fileName)) {
        return [];
    }
    $time = microtime();
    $time = explode(' ', $time);
    $time = $time[1] + $time[0];
    $start = $time;

    $source = Video::getSourceFile($fileName, ".pdf");
    $file = $source['path'];
    $files["pdf"] = [
        'filename' => "{$fileName}.pdf",
        'path' => $file,
        'url' => $source['url'],
        'type' => 'pdf',
    ];
    $files = array_merge($files, _getImagesURL($fileName, 'pdf'));
    $time = microtime();
    $time = explode(' ', $time);
    $time = $time[1] + $time[0];
    $finish = $time;
    $total_time = round(($finish - $start), 4);
    //_error_log("getVideosURLPDF generated in {$total_time} seconds. fileName: $fileName ");
    return $files;
}

function getVideosURLIMAGE($fileName) {
    global $global;
    if (empty($fileName)) {
        return [];
    }
    $time = microtime();
    $time = explode(' ', $time);
    $time = $time[1] + $time[0];
    $start = $time;

    $types = ['png', 'gif', 'webp', 'jpg'];

    foreach ($types as $value) {
        $source = Video::getSourceFile($fileName, ".{$value}");
        $file = $source['path'];
        $files["image"] = [
            'filename' => "{$fileName}.{$value}",
            'path' => $file,
            'url' => $source['url'],
            'type' => 'image',
        ];
        if (file_exists($file)) {
            break;
        }
    }

    $files = array_merge($files, _getImagesURL($fileName, 'image'));
    $time = microtime();
    $time = explode(' ', $time);
    $time = $time[1] + $time[0];
    $finish = $time;
    $total_time = round(($finish - $start), 4);
    //_error_log("getVideosURLPDF generated in {$total_time} seconds. fileName: $fileName ");
    return $files;
}

function getVideosURLZIP($fileName) {
    global $global;
    if (empty($fileName)) {
        return [];
    }
    $time = microtime();
    $time = explode(' ', $time);
    $time = $time[1] + $time[0];
    $start = $time;

    $types = ['zip'];

    foreach ($types as $value) {
        $source = Video::getSourceFile($fileName, ".{$value}");
        $file = $source['path'];
        $files["zip"] = [
            'filename' => "{$fileName}.zip",
            'path' => $file,
            'url' => $source['url'],
            'type' => 'zip',
        ];
        if (file_exists($file)) {
            break;
        }
    }

    $files = array_merge($files, _getImagesURL($fileName, 'zip'));
    $time = microtime();
    $time = explode(' ', $time);
    $time = $time[1] + $time[0];
    $finish = $time;
    $total_time = round(($finish - $start), 4);
    //_error_log("getVideosURLPDF generated in {$total_time} seconds. fileName: $fileName ");
    return $files;
}

function getVideosURLArticle($fileName) {
    global $global;
    if (empty($fileName)) {
        return [];
    }
    $time = microtime();
    $time = explode(' ', $time);
    $time = $time[1] + $time[0];
    $start = $time;
    //$files = array_merge($files, _getImagesURL($fileName, 'article'));
    $files = _getImagesURL($fileName, 'article');
    $time = microtime();
    $time = explode(' ', $time);
    $time = $time[1] + $time[0];
    $finish = $time;
    $total_time = round(($finish - $start), 4);
    //_error_log("getVideosURLPDF generated in {$total_time} seconds. fileName: $fileName ");
    return $files;
}

function getVideosURLAudio($fileName, $fileNameisThePath = false) {
    global $global;
    if (empty($fileName)) {
        return [];
    }
    $time = microtime();
    $time = explode(' ', $time);
    $time = $time[1] + $time[0];
    $start = $time;
    if ($fileNameisThePath) {
        $filename = basename($fileName);
        $path = Video::getPathToFile($filename);
        if (filesize($path) < 20) {
            $objCDNS = AVideoPlugin::getObjectDataIfEnabled('CDN');
            if (!empty($objCDNS) && $objCDNS->enable_storage) {
                $url = CDNStorage::getURL("{$filename}");
            }
        }
        if (empty($url)) {
            $url = Video::getURLToFile($filename);
        }

        $files["mp3"] = [
            'filename' => $filename,
            'path' => $path,
            'url' => $url,
            'url_noCDN' => $url,
            'type' => 'audio',
            'format' => 'mp3',
        ];
    } else {
        $source = Video::getSourceFile($fileName, ".mp3");
        $file = $source['path'];
        $files["mp3"] = [
            'filename' => "{$fileName}.mp3",
            'path' => $file,
            'url' => $source['url'],
            'url_noCDN' => @$source['url_noCDN'],
            'type' => 'audio',
            'format' => 'mp3',
        ];
    }

    $files = array_merge($files, _getImagesURL($fileName, 'audio_wave'));
    $time = microtime();
    $time = explode(' ', $time);
    $time = $time[1] + $time[0];
    $finish = $time;
    $total_time = round(($finish - $start), 4);
    //_error_log("getVideosURLAudio generated in {$total_time} seconds. fileName: $fileName ");
    return $files;
}

function getVideosURL($fileName, $cache = true) {
    return getVideosURL_V2($fileName); // disable this function soon
}

function getVideosURLMP4Only($fileName) {
    $allFiles = getVideosURL_V2($fileName);
    if (is_array($allFiles)) {
        foreach ($allFiles as $key => $value) {
            if ($value['format'] !== 'mp4') {
                unset($allFiles[$key]);
            }
        }
        return $allFiles;
    }
    _error_log("getVideosURLMP4Only does not return an ARRAY from getVideosURL_V2($fileName) " . json_encode($allFiles));
    return [];
}

function getVideosURLMP3Only($fileName) {
    $allFiles = getVideosURL_V2($fileName);
    if (is_array($allFiles)) {
        foreach ($allFiles as $key => $value) {
            if ($value['format'] !== 'mp3') {
                unset($allFiles[$key]);
            }
        }
        return $allFiles;
    }
    _error_log("getVideosURLMP4Only does not return an ARRAY from getVideosURL_V2($fileName) " . json_encode($allFiles));
    return [];
}

function getVideosURLWEBMOnly($fileName) {
    $allFiles = getVideosURL_V2($fileName); // disable this function soon
    if (is_array($allFiles)) {
        foreach ($allFiles as $key => $value) {
            if ($value['format'] !== 'webm') {
                unset($allFiles[$key]);
            }
        }
        return $allFiles;
    }
    _error_log("getVideosURLMP4Only does not return an ARRAY from getVideosURL_V2($fileName) " . json_encode($allFiles));
    return [];
}

function getVideosURLMP4WEBMOnly($fileName) {
    return array_merge(getVideosURLMP4Only($fileName), getVideosURLWEBMOnly($fileName));
}

function getVideosURLMP4WEBMMP3Only($fileName) {
    return array_merge(getVideosURLMP4Only($fileName), getVideosURLWEBMOnly($fileName), getVideosURLMP3Only($fileName));
}

function getVideosURLOnly($fileName) {
    $allFiles = getVideosURL_V2($fileName); // disable this function soon
    foreach ($allFiles as $key => $value) {
        if ($value['type'] !== 'video') {
            unset($allFiles[$key]);
        }
    }
    return $allFiles;
}

function getAudioURLOnly($fileName) {
    $allFiles = getVideosURL_V2($fileName); // disable this function soon
    foreach ($allFiles as $key => $value) {
        if ($value['type'] !== 'audio') {
            unset($allFiles[$key]);
        }
    }
    return $allFiles;
}

function getAudioOrVideoURLOnly($fileName) {
    $allFiles = getVideosURL_V2($fileName); // disable this function soon
    foreach ($allFiles as $key => $value) {
        if ($value['type'] !== 'video' && $value['type'] !== 'audio') {
            unset($allFiles[$key]);
        }
    }
    return $allFiles;
}

function getVideosDir() {
    return Video::getStoragePath();
}

$getVideosURL_V2Array = [];

function getVideosURL_V2($fileName, $recreateCache = false) {
    global $global, $getVideosURL_V2Array;
    if (empty($fileName)) {
        return [];
    }
    //$recreateCache = true;
    $cleanfilename = Video::getCleanFilenameFromFile($fileName);

    if (!empty($getVideosURL_V2Array[$cleanfilename])) {
        return $getVideosURL_V2Array[$cleanfilename];
    }

    $paths = Video::getPaths($cleanfilename);

    $pdf = $paths['path'] . "{$cleanfilename}.pdf";
    $mp3 = $paths['path'] . "{$cleanfilename}.mp3";
    if (file_exists($pdf)) {
        return getVideosURLPDF($fileName);
    } elseif (file_exists($mp3)) {
        return getVideosURLAudio($mp3, true);
    }
    $cacheName = "getVideosURL_V2$fileName";
    if (empty($recreateCache)) {
        $lifetime = maxLifetime();

        $TimeLog1 = "getVideosURL_V2($fileName) empty recreateCache";
        TimeLogStart($TimeLog1);
        //var_dump($cacheName, $lifetime);exit;
        $cache = ObjectYPT::getCache($cacheName, $lifetime, true);
        $files = object_to_array($cache);
        if (is_array($files)) {
            //_error_log("getVideosURL_V2: do NOT recreate lifetime = {$lifetime}");
            $preg_match_url = addcslashes(getCDN(), "/") . "videos";
            foreach ($files as $value) {
                // check if is a dummy file and the URL still wrong
                $pathFilesize = filesize($value['path']);
                if (
                        $value['type'] === 'video' && // is a video
                        preg_match("/^{$preg_match_url}/", $value['url']) && // the URL is the same as the main domain
                        $pathFilesize < 20) { // file size is small
                    _error_log("getVideosURL_V2:: dummy file found, fix cache " . json_encode(["/^{$preg_match_url}/", $value['url'], preg_match("/^{$preg_match_url}video/", $value['url']), $pathFilesize, $value]));
                    unset($files);
                    $video = Video::getVideoFromFileName($fileName, true, true);
                    Video::clearCache($video['id']);
                    break;
                } else {
                    //_error_log("getVideosURL_V2:: NOT dummy file ". json_encode(array("/^{$preg_match_url}video/", $value['url'], preg_match("/^{$preg_match_url}video/", $value['url']),filesize($value['path']),$value)));
                }
            }
            //_error_log("getVideosURL_V2:: cachestill good ". json_encode($files));
        } else {
            //_error_log("getVideosURL_V2:: cache not found ". json_encode($files));
        }
        TimeLogEnd($TimeLog1, __LINE__);
    } else {
        _error_log("getVideosURL_V2($fileName) Recreate cache requested " . json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)));
    }
    if (empty($files)) {
        $files = [];
        $plugin = AVideoPlugin::loadPlugin("VideoHLS");
        if (!empty($plugin)) {
            $timeName = "getVideosURL_V2::VideoHLS::getSourceFile($fileName)";
            TimeLogStart($timeName);
            $files = VideoHLS::getSourceFile($fileName, true);
            TimeLogEnd($timeName, __LINE__);
        }
        $video = ['webm', 'mp4'];
        $audio = ['mp3', 'ogg'];
        $image = ['jpg', 'gif', 'webp'];

        $formats = array_merge($video, $audio, $image);

        //$globQuery = getVideosDir()."{$cleanfilename}*.{" . implode(",", $formats) . "}";
        //$filesInDir = glob($globQuery, GLOB_BRACE);
        $timeName = "getVideosURL_V2::globVideosDir($cleanfilename)";
        TimeLogStart($timeName);
        $filesInDir = globVideosDir($cleanfilename, true);
        TimeLogEnd($timeName, __LINE__);

        $timeName = "getVideosURL_V2::foreach";
        TimeLogStart($timeName);
        foreach ($filesInDir as $file) {
            $parts = pathinfo($file);

            if ($parts['filename'] == 'index') {
                $parts['filename'] = str_replace(Video::getPathToFile($parts['dirname']), '', $parts['dirname']);
            }

            //$timeName2 = "getVideosURL_V2::Video::getSourceFile({$parts['filename']}, .{$parts['extension']})";
            //TimeLogStart($timeName2);
            $source = Video::getSourceFile($parts['filename'], ".{$parts['extension']}");
            //TimeLogEnd($timeName2, __LINE__);
            if (empty($source)) {
                continue;
            }
            if (in_array($parts['extension'], $image) && filesize($file) < 1000 && !preg_match("/Dummy File/i", file_get_contents($file))) {
                continue;
            }
            $resolution = '';
            if (preg_match("/{$cleanfilename}(_.+)[.]{$parts['extension']}$/", $file, $matches)) {
                $resolution = $matches[1];
            } else {
                preg_match('/_([^_]{0,4}).' . $parts['extension'] . '$/', $file, $matches);
                $resolution = @$matches[1];
            }
            $type = 'video';
            if (in_array($parts['extension'], $video)) {
                $type = 'video';
            } elseif (in_array($parts['extension'], $audio)) {
                $type = 'audio';
            } elseif (in_array($parts['extension'], $image) || preg_match('/^(gif|jpg|webp|png|jpeg)/i', $parts['extension'])) {
                $type = 'image';
                if (!preg_match('/(thumb|roku)/', $resolution)) {
                    if (preg_match("/{$cleanfilename}_([0-9]+).jpg/", $source['url'], $matches)) {
                        $resolution = '_' . intval($matches[1]);
                    } else {
                        $resolution = '';
                    }
                }
            }
            $files["{$parts['extension']}{$resolution}"] = [
                'filename' => "{$parts['filename']}.{$parts['extension']}",
                'path' => $file,
                'url' => $source['url'],
                'url_noCDN' => @$source['url_noCDN'],
                'type' => $type,
                'format' => strtolower($parts['extension']),
            ];
        }

        TimeLogEnd($timeName, __LINE__);
        ObjectYPT::setCache($cacheName, $files);
    }
    if (is_array($files)) {
        // sort by resolution
        uasort($files, "sortVideosURL");
    }
    //var_dump($files);exit;
    $getVideosURL_V2Array[$cleanfilename] = $files;
    return $getVideosURL_V2Array[$cleanfilename];
}

//Returns < 0 if str1 is less than str2; > 0 if str1 is greater than str2, and 0 if they are equal.
function sortVideosURL($a, $b) {
    if ($a['type'] == 'video') {
        $aRes = getResolutionFromFilename($a['filename']);
        $bRes = getResolutionFromFilename($b['filename']);
        return $aRes - $bRes;
    }

    return 0;
}

function getResolutionFromFilename($filename) {
    global $getResolutionFromFilenameArray;

    if (!isset($getResolutionFromFilenameArray)) {
        $getResolutionFromFilenameArray = [];
    }

    if (!empty($getResolutionFromFilenameArray[$filename])) {
        return $getResolutionFromFilenameArray[$filename];
    }

    if (!preg_match('/^http/i', $filename) && !file_exists($filename)) {
        return 0;
    }
    $res = Video::getResolutionFromFilename($filename);
    if (empty($res)) {
        if (preg_match('/[_\/]hd[.\/]/i', $filename)) {
            $res = 720;
        } elseif (preg_match('/[_\/]sd[.\/]/i', $filename)) {
            $res = 480;
        } elseif (preg_match('/[_\/]low[.\/]/i', $filename)) {
            $res = 240;
        } else {
            $res = 0;
        }
    }
    $getResolutionFromFilenameArray[$filename] = $res;
    return $res;
}

function getSources($fileName, $returnArray = false, $try = 0) {
    if ($returnArray) {
        $videoSources = $audioTracks = $subtitleTracks = [];
    } else {
        $videoSources = $audioTracks = $subtitleTracks = '';
    }

    $video = Video::getVideoFromFileNameLight($fileName);

    if ($video['type'] !== 'audio' && function_exists('getVRSSources')) {
        $videoSources = getVRSSources($fileName, $returnArray);
    } else {
        //$files = getVideosURL($fileName);
        $files = getVideosURL_V2($fileName, !empty($try));
        $sources = '';
        $sourcesArray = [];
        foreach ($files as $key => $value) {
            $path_parts = pathinfo($value['path']);
            if ($path_parts['extension'] == "webm" || $path_parts['extension'] == "mp4" || $path_parts['extension'] == "m3u8" || $path_parts['extension'] == "mp3" || $path_parts['extension'] == "ogg") {
                $obj = new stdClass();
                $obj->type = mime_content_type_per_filename($value['path']);
                $sources .= "<source src=\"{$value['url']}\" type=\"{$obj->type}\">";
                $obj->src = $value['url'];
                $sourcesArray[] = $obj;
            }
        }
        $videoSources = $returnArray ? $sourcesArray : $sources;
    }
    if (function_exists('getVTTTracks')) {
        $subtitleTracks = getVTTTracks($fileName, $returnArray);
    }

    if ($returnArray) {
        $return = array_merge($videoSources, $audioTracks, $subtitleTracks);
    } else {
        $return = $videoSources . $audioTracks . $subtitleTracks;
    }

    $obj = new stdClass();
    $obj->result = $return;
    if (empty($videoSources) && empty($audioTracks) && !empty($video['id']) && $video['type'] == 'video') {
        if (empty($try)) {
            //sleep(1);
            $sources = getSources($fileName, $returnArray, $try + 1);
            if (!empty($sources)) {
                Video::updateFilesize($video['id']);
            }
            Video::clearCache($video['id']);
            return $sources;
        } else {
            _error_log("getSources($fileName) File not found " . json_encode($video));
            if (empty($sources)) {
                $sources = array();
            }
            $obj = new stdClass();
            $obj->type = "video/mp4";
            $obj->src = "Video not found";
            $obj->label = "Video not found";
            $obj->res = 0;
            $sourcesArray["mp4"] = $obj;
            $sources["mp4"] = "<source src=\"\" type=\"{$obj->type}\" label=\"{$obj->label}\" res=\"{$obj->res}\">";
            $return = $returnArray ? $sourcesArray : implode(PHP_EOL, $sources);
        }
    }
    return $return;
}

/**
 *
 * @param type $file_src
 * @return typeget image size with cache
 */
function getimgsize($file_src) {
    global $_getimagesize;
    if (empty($file_src) || !file_exists($file_src)) {
        return [0, 0];
    }
    if (empty($_getimagesize)) {
        $_getimagesize = [];
    }

    $name = "getimgsize_" . md5($file_src);

    if (!empty($_getimagesize[$name])) {
        $size = $_getimagesize[$name];
    } else {
        $cached = ObjectYPT::getCache($name, 86400); //one day
        if (!empty($cached)) {
            $c = (array) $cached;
            $size = [];
            foreach ($c as $key => $value) {
                if (preg_match("/^[0-9]+$/", $key)) {
                    $key = intval($key);
                }
                $size[$key] = $value;
            }
            $_getimagesize[$name] = $size;
            return $size;
        }

        $size = @getimagesize($file_src);

        if (empty($size)) {
            $size = [1024, 768];
        }

        ObjectYPT::setCache($name, $size);
        $_getimagesize[$name] = $size;
    }
    return $size;
}

function im_resize($file_src, $file_dest, $wd, $hd, $q = 80) {
    if (empty($file_dest)) {
        return false;
    }
    if (!file_exists($file_src)) {
        _error_log("im_resize: Source not found: {$file_src}");
        return false;
    }
    $size = getimgsize($file_src);
    if ($size === false) {
        _error_log("im_resize: Could not get image size: {$file_src}");
        return false;
    }

    if (empty($size['mime']) || $size['mime'] == 'image/pjpeg') {
        $size['mime'] = 'image/jpeg';
    }

    $format = strtolower(substr($size['mime'], strpos($size['mime'], '/') + 1));
    if (empty($format)) {
        $format = 'jpeg';
    }
    $destformat = strtolower(substr($file_dest, -4));
    if (empty($destformat)) {
        _error_log("destformat not found {$file_dest}");
        $destformat = ".jpg";
    }
    $icfunc = "imagecreatefrom" . $format;
    if (!function_exists($icfunc)) {
        _error_log("im_resize: Function does not exists: {$icfunc}");
        return false;
    }

    $imgSize = getimagesize($file_src);
    if (empty($imgSize)) {
        _error_log("im_resize: getimagesize($file_src) return false " . json_encode($imgSize));
        return false;
    }
    try {
        $src = $icfunc($file_src);
    } catch (Exception $exc) {
        _error_log("im_resize: ($file_src) " . $exc->getMessage());
        _error_log("im_resize: Try {$icfunc} from string");
        $src = imagecreatefromstring(file_get_contents($file_src));
        if (!$src) {
            _error_log("im_resize: fail {$icfunc} from string");
            return false;
        }
    }
    $ws = imagesx($src);
    $hs = imagesy($src);

    if ($ws <= $hs) {
        $hd = ceil(($wd * $hs) / $ws);
    } else {
        $wd = ceil(($hd * $ws) / $hs);
    }
    if ($ws <= $wd) {
        $wd = $ws;
        $hd = $hs;
    }
    $wc = ($wd * $hs) / $hd;

    if ($wc <= $ws) {
        $hc = ($wc * $hd) / $wd;
    } else {
        $hc = ($ws * $hd) / $wd;
        $wc = ($wd * $hc) / $hd;
    }

    $dest = imagecreatetruecolor($wd, $hd);
    switch ($format) {
        case "png":
            imagealphablending($dest, false);
            imagesavealpha($dest, true);
            $transparent = imagecolorallocatealpha($dest, 255, 255, 255, 127);
            imagefilledrectangle($dest, 0, 0, $wd, $hd, $transparent);

            break;
        case "gif":
// integer representation of the color black (rgb: 0,0,0)
            $background = imagecolorallocate($src, 0, 0, 0);
// removing the black from the placeholder
            imagecolortransparent($src, $background);

            break;
    }

    imagecopyresampled($dest, $src, 0, 0, ($ws - $wc) / 2, ($hs - $hc) / 2, $wd, $hd, $wc, $hc);
    $saved = false;
    if ($destformat == '.png') {
        $saved = imagepng($dest, $file_dest);
    }

    if ($destformat == '.jpg') {
        $saved = imagejpeg($dest, $file_dest, $q);
    }

    if (!$saved) {
        _error_log('saving failed');
    }

    imagedestroy($dest);
    imagedestroy($src);
    @chmod($file_dest, 0666);

    return true;
}

function im_resizeV2($file_src, $file_dest, $wd, $hd, $q = 80) {
    //_error_log("im_resizeV2: $file_src, $file_dest, $wd, $hd, $q");
    $newImage = im_resize($file_src, $file_dest, $wd, $hd, 100);
    if (!$newImage) {
        return false;
    }
    $src = imagecreatefromjpeg($file_dest);
    if (empty($src)) {
        return false;
    }
    $ws = imagesx($src);
    $hs = imagesy($src);

    if ($ws < $wd) {
        $dst_x = ($wd - $ws) / 2;
    } else {
        $dst_x = 0;
    }

    if ($hs < $hd) {
        $dst_y = ($hd - $hs) / 2;
    } else {
        $dst_y = 0;
    }

    $mapImage = imagecreatetruecolor($wd, $hd);
    $bgColor = imagecolorallocate($mapImage, 0, 0, 0);
    imagefill($mapImage, 0, 0, $bgColor);

    $tileImg = imagecreatefromjpeg($file_dest);
    imagecopy($mapImage, $tileImg, $dst_x, $dst_y, 0, 0, $ws, $hs);

    $saved = imagejpeg($mapImage, $file_dest, $q);

    return $saved;
}

function im_resizePNG($file_src, $file_dest, $wd, $hd) {
    $srcImage = imagecreatefrompng($file_src);
    $ws = imagesx($srcImage);
    $hs = imagesy($srcImage);

    $targetImage = imagecreatetruecolor($wd, $hd);
    imagealphablending($targetImage, false);
    imagesavealpha($targetImage, true);

    imagecopyresampled(
            $targetImage,
            $srcImage,
            0,
            0,
            0,
            0,
            $wd,
            $hd,
            $ws,
            $hs
    );

    $saved = imagepng($targetImage, $file_dest);

    return $saved;
}

function im_resizeV3($file_src, $file_dest, $wd, $hd) {
    return im_resizeV2($file_src, $file_dest, $wd, $hd); // ffmpeg disabled

    _error_log("im_resizeV3: $file_src, $file_dest, $wd, $hd");
    // This tries to preserve the aspect ratio of the thumb while letterboxing it in
    // The same way that the encoder now does.
    eval('$ffmpeg ="ffmpeg -i {$file_src} -filter_complex \"scale=(iw*sar)*min({$wd}/(iw*sar)\,{$hd}/ih):ih*min({$wd}/(iw*sar)\,{$hd}/ih), pad={$wd}:{$hd}:({$wd}-iw*min({$wd}/iw\,{$hd}/ih))/2:({$hd}-ih*min({$wd}/iw\,{$hd}/ih))/2\" -sws_flags lanczos -qscale:v 2 {$file_dest}";');
    exec($ffmpeg . " < /dev/null 2>&1", $output, $return_val);
}

function im_resize_gif($file_src, $file_dest, $max_width, $max_height) {
    if (class_exists('Imagick')) {
        $imagick = new Imagick($file_src);

        $format = $imagick->getImageFormat();
        if ($format == 'GIF') {
            $imagick = $imagick->coalesceImages();
            do {
                $imagick->resizeImage($max_width, $max_height, Imagick::FILTER_BOX, 1);
            } while ($imagick->nextImage());
            $imagick = $imagick->deconstructImages();
            $imagick->writeImages($file_dest, true);
        }

        $imagick->clear();
        $imagick->destroy();
    } else {
        copy($file_src, $file_dest);
    }
}

function im_resize_max_size($file_src, $file_dest, $max_width, $max_height) {
    $fn = $file_src;

    $extension = strtolower(pathinfo($file_dest, PATHINFO_EXTENSION));

    if ($extension == 'gif') {
        im_resize_gif($file_src, $file_dest, $max_width, $max_height);
        @unlink($file_src);
        return true;
    }

    $tmpFile = getTmpFile() . ".{$extension}";
    if (empty($fn)) {
        _error_log("im_resize_max_size: file name is empty, Destination: {$file_dest}", AVideoLog::$ERROR);
        return false;
    }
    if (function_exists("exif_read_data")) {
        error_log($fn);
        convertImage($fn, $tmpFile, 100);
        $exif = exif_read_data($tmpFile);
        if ($exif && isset($exif['Orientation'])) {
            $orientation = $exif['Orientation'];
            if ($orientation != 1) {
                $img = imagecreatefromjpeg($tmpFile);
                $deg = 0;
                switch ($orientation) {
                    case 3:
                        $deg = 180;
                        break;
                    case 6:
                        $deg = 270;
                        break;
                    case 8:
                        $deg = 90;
                        break;
                }
                if ($deg) {
                    $img = imagerotate($img, $deg, 0);
                }
                imagejpeg($img, $fn, 100);
            }
        }
    } else {
        _error_log("Make sure you install the php_mbstring and php_exif to be able to rotate images");
    }

    $size = getimagesize($fn);
    $ratio = $size[0] / $size[1]; // width/height
    if ($size[0] <= $max_width && $size[1] <= $max_height) {
        $width = $size[0];
        $height = $size[1];
    } elseif ($ratio > 1) {
        $width = $max_width;
        $height = $max_height / $ratio;
    } else {
        $width = $max_width * $ratio;
        $height = $max_height;
    }

    $src = imagecreatefromstring(file_get_contents($fn));
    $dst = imagecreatetruecolor($width, $height);
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $width, $height, $size[0], $size[1]);
    imagedestroy($src);
    imagejpeg($dst, $file_dest); // adjust format as needed
    imagedestroy($dst);
    @unlink($file_src);
    @unlink($tmpFile);
}

function convertImage($originalImage, $outputImage, $quality) {
    $imagetype = 0;
    if (function_exists('exif_imagetype')) {
        $imagetype = exif_imagetype($originalImage);
    }

    $ext = strtolower(pathinfo($originalImage, PATHINFO_EXTENSION));
    $extOutput = strtolower(pathinfo($outputImage, PATHINFO_EXTENSION));

    if ($ext == $extOutput) {
        return copy($originalImage, $outputImage);
    }

    try {
        if ($imagetype == IMAGETYPE_JPEG || preg_match('/jpg|jpeg/i', $ext)) {
            //_error_log("convertImage: IMAGETYPE_JPEG");
            $imageTmp = imagecreatefromjpeg($originalImage);
        } elseif ($imagetype == IMAGETYPE_PNG || preg_match('/png/i', $ext)) {
            //_error_log("convertImage: IMAGETYPE_PNG");
            $imageTmp = imagecreatefrompng($originalImage);
        } elseif ($imagetype == IMAGETYPE_GIF || preg_match('/gif/i', $ext)) {
            //_error_log("convertImage: IMAGETYPE_GIF");
            $imageTmp = imagecreatefromgif($originalImage);
        } elseif ($imagetype == IMAGETYPE_BMP || preg_match('/bmp/i', $ext)) {
            //_error_log("convertImage: IMAGETYPE_BMP");
            $imageTmp = imagecreatefrombmp($originalImage);
        } elseif ($imagetype == IMAGETYPE_WEBP || preg_match('/webp/i', $ext)) {
            //_error_log("convertImage: IMAGETYPE_WEBP");
            $imageTmp = imagecreatefromwebp($originalImage);
        } else {
            _error_log("convertImage: File Extension not found ($originalImage, $outputImage, $quality) " . exif_imagetype($originalImage));
            return 0;
        }
    } catch (Exception $exc) {
        _error_log("convertImage: " . $exc->getMessage());
        return 0;
    }
    if ($imageTmp === false) {
        //_error_log("convertImage: could not create a resource: $originalImage, $outputImage, $quality, $ext " . json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)));
        return 0;
    }
    // quality is a value from 0 (worst) to 100 (best)
    $response = 0;
    if ($extOutput === 'jpg') {
        if (function_exists('imagejpeg')) {
            $response = imagejpeg($imageTmp, $outputImage, $quality);
        } else {
            _error_log("convertImage ERROR: function imagejpeg does not exists");
        }
    } elseif ($extOutput === 'png') {
        if (function_exists('imagepng')) {
            $response = imagepng($imageTmp, $outputImage, $quality / 10);
        } else {
            _error_log("convertImage ERROR: function imagepng does not exists");
        }
    } elseif ($extOutput === 'webp') {
        if (function_exists('imagewebp')) {
            $response = imagewebp($imageTmp, $outputImage, $quality);
        } else {
            _error_log("convertImage ERROR: function imagewebp does not exists");
        }
    } elseif ($extOutput === 'gif') {
        if (function_exists('imagegif')) {
            $response = imagegif($imageTmp, $outputImage);
        } else {
            _error_log("convertImage ERROR: function imagegif does not exists");
        }
    }

    imagedestroy($imageTmp);

    return $response;
}

function decideMoveUploadedToVideos($tmp_name, $filename, $type = "video") {
    if ($filename == '.zip') {
        return false;
    }
    global $global;
    $obj = new stdClass();
    $aws_s3 = AVideoPlugin::loadPluginIfEnabled('AWS_S3');
    $bb_b2 = AVideoPlugin::loadPluginIfEnabled('Blackblaze_B2');
    $ftp = AVideoPlugin::loadPluginIfEnabled('FTP_Storage');
    $paths = Video::getPaths($filename, true);
    $destinationFile = "{$paths['path']}{$filename}";
    //$destinationFile = getVideosDir() . "{$filename}";
    _error_log("decideMoveUploadedToVideos: {$filename}");
    $path_info = pathinfo($filename);
    if ($type !== "zip" && $path_info['extension'] === 'zip') {
        _error_log("decideMoveUploadedToVideos: ZIp file {$filename}");
        $paths = Video::getPaths($path_info['filename']);
        $dir = $paths['path'];
        unzipDirectory($tmp_name, $dir); // unzip it
        cleanDirectory($dir);
        if (!empty($aws_s3)) {
            //$aws_s3->move_uploaded_file($tmp_name, $filename);
        } elseif (!empty($bb_b2)) {
            $bb_b2->move_uploaded_directory($dir);
        } elseif (!empty($ftp)) {
            //$ftp->move_uploaded_file($tmp_name, $filename);
        }
    } else {
        _error_log("decideMoveUploadedToVideos: NOT ZIp file {$filename}");
        if (!empty($aws_s3)) {
            _error_log("decideMoveUploadedToVideos: S3 {$filename}");
            $aws_s3->move_uploaded_file($tmp_name, $filename);
        } elseif (!empty($bb_b2)) {
            _error_log("decideMoveUploadedToVideos: B2 {$filename}");
            $bb_b2->move_uploaded_file($tmp_name, $filename);
        } elseif (!empty($ftp)) {
            _error_log("decideMoveUploadedToVideos: FTP {$filename}");
            $ftp->move_uploaded_file($tmp_name, $filename);
        } else {
            _error_log("decideMoveUploadedToVideos: Local {$filename}");
            if (!move_uploaded_file($tmp_name, $destinationFile)) {
                if (!rename($tmp_name, $destinationFile)) {
                    if (!copy($tmp_name, $destinationFile)) {
                        $obj->msg = "Error on decideMoveUploadedToVideos({$tmp_name}, $destinationFile)";
                        die(json_encode($obj));
                    }
                }
            }
            if (file_exists($destinationFile)) {
                _error_log("decideMoveUploadedToVideos: SUCCESS Local {$destinationFile}");
            } else {
                _error_log("decideMoveUploadedToVideos: ERROR Local {$destinationFile}");
            }
            chmod($destinationFile, 0644);
        }
    }
    sleep(1);
    $fsize = @filesize($destinationFile);
    _error_log("decideMoveUploadedToVideos: destinationFile {$destinationFile} filesize=" . ($fsize) . " (" . humanFileSize($fsize) . ")");
    Video::clearCacheFromFilename($filename);
    return $destinationFile;
}

function unzipDirectory($filename, $destination) {
    global $global;
    // Wait a couple of seconds to make sure the file has completed transfer
    sleep(2);
    ini_set('memory_limit', '-1');
    ini_set('max_execution_time', 7200); // 2 hours
    $filename = escapeshellarg(safeString($filename, true));
    $destination = escapeshellarg($destination);
    $cmd = "unzip -: {$filename} -d {$destination}" . "  2>&1";
    _error_log("unzipDirectory: {$cmd}");
    exec($cmd, $output, $return_val);
    if ($return_val !== 0 && function_exists("zip_open")) {
        // try to unzip using PHP
        _error_log("unzipDirectory: TRY to use PHP {$filename}");
        $zip = zip_open($filename);
        if ($zip) {
            while ($zip_entry = zip_read($zip)) {
                $path = "{$destination}/" . zip_entry_name($zip_entry);
                $path = str_replace('//', '/', $path);
                //_error_log("unzipDirectory: fopen $path");
                if (substr(zip_entry_name($zip_entry), -1) == '/') {
                    make_path($path);
                } else {
                    make_path($path);
                    try {
                        $fp = fopen($path, "w");
                        if (is_resource($fp) && zip_entry_open($zip, $zip_entry, "r")) {
                            $buf = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
                            fwrite($fp, "$buf");
                            zip_entry_close($zip_entry);
                            fclose($fp);
                        } else {
                            _error_log('unzipDirectory could not open ' . $path);
                        }
                    } catch (Exception $exc) {
                        _error_log($exc->getMessage());
                    }
                }
            }
            zip_close($zip);
        } else {
            _error_log("unzipDirectory: ERROR php zip does not work");
        }
    } else {
        _error_log("unzipDirectory: Success {$destination}");
    }
    @unlink($filename);
}

function make_path($path) {
    $created = false;
    if (substr($path, -1) !== DIRECTORY_SEPARATOR) {
        $path = pathinfo($path, PATHINFO_DIRNAME);
    }
    if (!is_dir($path)) {
        //if(preg_match('/getvideoinfo/i', $path)){var_dump(debug_backtrace());}
        if (preg_match('/cache/i', $path)) {
            $mode = 0777;
        } else {
            $mode = 0755;
        }
        $created = mkdir($path, $mode, true);
        /*
          if (!$created) {
          _error_log('make_path: could not create the dir ' . json_encode($path) . json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)));
          }
         */
    } else {
        $created = true;
    }
    return $created;
}

/**
 * for security clean all non secure files from directory
 * @param type $dir
 * @param type $allowedExtensions
 * @return type
 */
function cleanDirectory($dir, $allowedExtensions = ['key', 'm3u8', 'ts', 'vtt', 'jpg', 'gif', 'mp3', 'webm', 'webp']) {
    $ffs = scandir($dir);

    unset($ffs[array_search('.', $ffs, true)]);
    unset($ffs[array_search('..', $ffs, true)]);

    // prevent empty ordered elements
    if (count($ffs) < 1) {
        return;
    }

    foreach ($ffs as $ff) {
        $current = $dir . '/' . $ff;
        if (is_dir($current)) {
            cleanDirectory($current, $allowedExtensions);
        }
        $path_parts = pathinfo($current);
        if (!empty($path_parts['extension']) && !in_array($path_parts['extension'], $allowedExtensions)) {
            unlink($current);
        }
    }
}

function decideFile_put_contentsToVideos($tmp_name, $filename) {
    global $global;
    $aws_s3 = AVideoPlugin::loadPluginIfEnabled('AWS_S3');
    $bb_b2 = AVideoPlugin::loadPluginIfEnabled('Blackblaze_B2');
    $ftp = AVideoPlugin::loadPluginIfEnabled('FTP_Storage');
    if (!empty($bb_b2)) {
        $bb_b2->move_uploaded_file($tmp_name, $filename);
    } elseif (!empty($aws_s3)) {
        $aws_s3->move_uploaded_file($tmp_name, $filename);
    } elseif (!empty($ftp)) {
        $ftp->move_uploaded_file($tmp_name, $filename);
    } else {
        $path = Video::getPathToFile($filename);
        if (!move_uploaded_file($tmp_name, $path)) {
            $obj->msg = "Error on move_uploaded_file({$tmp_name}, {$filename})";
            die(json_encode($obj));
        }
    }
}

function isAnyStorageEnabled() {
    if ($yptStorage = AVideoPlugin::loadPluginIfEnabled("YPTStorage")) {
        return true;
    } elseif ($aws_s3 = AVideoPlugin::loadPluginIfEnabled("AWS_S3")) {
        return true;
    } elseif ($bb_b2 = AVideoPlugin::loadPluginIfEnabled("Blackblaze_B2")) {
        return true;
    } elseif ($ftp = AVideoPlugin::loadPluginIfEnabled("FTP_Storage")) {
        return true;
    }
    return false;
}

if (!function_exists('mime_content_type')) {

    function mime_content_type($filename) {
        return mime_content_type_per_filename($filename);
    }

}

function fontAwesomeClassName($filename) {
    $mime_type = mime_content_type_per_filename($filename);
    // List of official MIME Types: http://www.iana.org/assignments/media-types/media-types.xhtml
    $icon_classes = [
// Media
        'image' => 'fas fa-file-image',
        'audio' => 'fas fa-file-audio',
        'video' => 'fas fa-file-video',
        // Documents
        'application/pdf' => 'fas fa-file-pdf',
        'application/msword' => 'fas fa-file-word',
        'application/vnd.ms-word' => 'fas fa-file-word',
        'application/vnd.oasis.opendocument.text' => 'fas fa-file-word',
        'application/vnd.openxmlformats-officedocument.wordprocessingml' => 'fas fa-file-word',
        'application/vnd.ms-excel' => 'fas fa-file-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml' => 'fas fa-file-excel',
        'application/vnd.oasis.opendocument.spreadsheet' => 'fas fa-file-excel',
        'application/vnd.ms-powerpoint' => 'fas fa-file-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml' => 'fas fa-file-powerpoint',
        'application/vnd.oasis.opendocument.presentation' => 'fas fa-file-powerpoint',
        'text/plain' => 'far fa-file-alt',
        'text/html' => 'fas fa-code',
        'application/json' => 'fas fa-code',
        // Archives
        'application/gzip' => 'far fa-file-archive',
        'application/zip' => 'far fa-file-archive',
    ];
    foreach ($icon_classes as $text => $icon) {
        if (strpos($mime_type, $text) === 0) {
            return $icon;
        }
    }
    return 'fas fa-file';
}

function mime_content_type_per_filename($filename) {
    $mime_types = [
        'txt' => 'text/plain',
        'htm' => 'text/html',
        'html' => 'text/html',
        'php' => 'text/html',
        'css' => 'text/css',
        'js' => 'application/javascript',
        'json' => 'application/json',
        'xml' => 'application/xml',
        'swf' => 'application/x-shockwave-flash',
        'flv' => 'video/x-flv',
        // images
        'png' => 'image/png',
        'jpe' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'jpg' => 'image/jpeg',
        'gif' => 'image/gif',
        'bmp' => 'image/bmp',
        'ico' => 'image/vnd.microsoft.icon',
        'tiff' => 'image/tiff',
        'tif' => 'image/tiff',
        'svg' => 'image/svg+xml',
        'svgz' => 'image/svg+xml',
        // archives
        'zip' => 'application/zip',
        'rar' => 'application/x-rar-compressed',
        'exe' => 'application/x-msdownload',
        'msi' => 'application/x-msdownload',
        'cab' => 'application/vnd.ms-cab-compressed',
        // audio/video
        'mp3' => 'audio/mpeg',
        'qt' => 'video/quicktime',
        'mov' => 'video/quicktime',
        'mp4' => 'video/mp4',
        'avi' => 'video/avi',
        'mkv' => 'video/mkv',
        'wav' => 'audio/wav',
        'm4v' => 'video/mpeg',
        'webm' => 'video/webm',
        'wmv' => 'video/wmv',
        'mpg' => 'video/mpeg',
        'mpeg' => 'video/mpeg',
        'f4v' => 'video/x-flv',
        'm4v' => 'video/m4v',
        'm4a' => 'video/quicktime',
        'm2p' => 'video/quicktime',
        'rm' => 'video/quicktime',
        'vob' => 'video/quicktime',
        'mkv' => 'video/quicktime',
        '3gp' => 'video/quicktime',
        'm3u8' => 'application/x-mpegURL',
        // adobe
        'pdf' => 'application/pdf',
        'psd' => 'image/vnd.adobe.photoshop',
        'ai' => 'application/postscript',
        'eps' => 'application/postscript',
        'ps' => 'application/postscript',
        // ms office
        'doc' => 'application/msword',
        'rtf' => 'application/rtf',
        'xls' => 'application/vnd.ms-excel',
        'ppt' => 'application/vnd.ms-powerpoint',
        // open office
        'odt' => 'application/vnd.oasis.opendocument.text',
        'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
    ];
    if (filter_var($filename, FILTER_VALIDATE_URL) === false) {
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
    } else {
        $ext = pathinfo(parse_url($filename, PHP_URL_PATH), PATHINFO_EXTENSION);
    }

    if ($ext === 'mp4' || $ext === 'webm') {
        $securePlugin = AVideoPlugin::loadPluginIfEnabled('SecureVideosDirectory');
        if (!empty($securePlugin)) {
            if (method_exists($securePlugin, "useEncoderWatrermarkFromFileName") && $securePlugin->useEncoderWatrermarkFromFileName($filename)) {
                return "application/x-mpegURL";
            }
        }
    }

    if (array_key_exists($ext, $mime_types)) {
        return $mime_types[$ext];
    } elseif (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME);
        $mimetype = finfo_file($finfo, $filename);
        finfo_close($finfo);
        return $mimetype;
    } else {
        return 'application/octet-stream';
    }
}

function combineFiles($filesArray, $extension = "js") {
    global $global, $advancedCustom;

    if ($extension == 'js' && isBot()) {
        return getCDN() . 'view/js/empty.js';
    }

    $relativeDir = 'videos/cache/' . $extension . '/';
    $cacheDir = $global['systemRootPath'] . $relativeDir;
    $str = '';
    $fileName = '';
    foreach ($filesArray as $value) {
        $fileName .= $value . filectime($global['systemRootPath'] . $value) . filemtime($global['systemRootPath'] . $value);
    }
    if ($advancedCustom !== false) {
        $minifyEnabled = $advancedCustom->EnableMinifyJS;
    } else {
        $minifyEnabled = false;
    }
    // temporary disable minify
    $minifyEnabled = false;

    $md5FileName = md5($fileName) . ".{$extension}";
    if (!file_exists($cacheDir . $md5FileName)) {
        foreach ($filesArray as $value) {
            if (file_exists($global['systemRootPath'] . $value)) {
                $str .= "\n/*{$value} created local with systemRootPath */\n" . local_get_contents($global['systemRootPath'] . $value);
            } elseif (file_exists($value)) {
                $str .= "\n/*{$value} created local with full-path given */\n" . local_get_contents($value);
            } else {
                $allowed = '';
                if (ini_get('allow_url_fopen')) {
                    $allowed .= "allow_url_fopen is on and ";
                }
                if (function_exists('curl_init')) {
                    $allowed .= "curl is on";
                } else {
                    $allowed .= "curl is off";
                }

                $content = url_get_contents($value);
                if (empty($content)) {
                    $allowed .= " - web-fallback 1 (add webSiteRootURL)";
                    $content = url_get_contents($global['webSiteRootURL'] . $value);
                }
                $str .= "\n/*{$value} created via web with own url ({$allowed}) */\n" . $content;
            }
        }
        //if ((($extension == "js" || $extension == "css") && ($minifyEnabled))) {
        if ($extension == "css" && ($minifyEnabled)) {
            require_once $global['systemRootPath'] . 'objects/jshrink.php';
            $str = \JShrink\Minifier::minify($str, ['flaggedComments' => false]);
        }
        if (!is_dir($cacheDir)) {
            make_path($cacheDir);
        }
        $bytes = _file_put_contents($cacheDir . $md5FileName, $str);
        if (empty($bytes)) {
            _error_log('combineFiles: error on save strlen=' . strlen($str) . ' ' . $cacheDir . $md5FileName . ' cacheDir=' . $cacheDir);
            return false;
        }
    }

    return getURL($relativeDir . $md5FileName);
}

function combineFilesHTML($filesArray, $extension = "js", $doNotCombine = false) {
    if (empty($doNotCombine)) {
        $jsURL = combineFiles($filesArray, $extension);
    }
    if ($extension == "js") {
        if (empty($jsURL)) {
            $str = '';
            foreach ($filesArray as $value) {
                $jsURL = getURL($value);
                $str .= '<script src="' . $jsURL . '" type="text/javascript"></script>';
            }
            return $str;
        } else {
            return '<script src="' . $jsURL . '" type="text/javascript"></script>';
        }
    } else {
        if (empty($jsURL)) {
            $str = '';
            foreach ($filesArray as $value) {
                $jsURL = getURL($value);
                $str .= '<link href="' . $jsURL . '" rel="stylesheet" type="text/css"/>';
            }
            return $str;
        } else {
            return '<link href="' . $jsURL . '" rel="stylesheet" type="text/css"/>';
        }
    }
}

function getTagIfExists($relativePath) {
    global $global;
    $relativePath = str_replace('\\', '/', $relativePath);
    $file = "{$global['systemRootPath']}{$relativePath}";
    if (file_exists($file)) {
        $url = getURL($relativePath);
    } else if (isValidURL($file)) {
        $url = $file;
    } else {
        return '';
    }
    $ext = pathinfo($relativePath, PATHINFO_EXTENSION);
    if ($ext == "js") {
        return '<script src="' . $url . '" type="text/javascript"></script>';
    } else if ($ext == "css") {
        return '<link href="' . $url . '" rel="stylesheet" type="text/css"/>';
    } else {
        return getImageTagIfExists($relativePath);
    }
}

function getImageTagIfExists($relativePath, $title = '', $id = '', $style = '', $class = 'img img-responsive', $lazyLoad = false) {
    global $global;
    $relativePathOriginal = $relativePath;
    $relativePath = getRelativePath($relativePath);
    $file = "{$global['systemRootPath']}{$relativePath}";
    $wh = '';
    if (file_exists($file)) {
        // check if there is a thumbs
        if (!preg_match('/_thumbsV2.jpg/', $file)) {
            $thumbs = str_replace('.jpg', '_thumbsV2.jpg', $file);
            if (file_exists($thumbs)) {
                $file = $thumbs;
            }
        }
        $file = createWebPIfNotExists($file);

        $url = getURL(getRelativePath($file));
        $image_info = getimagesize($file);
        $wh = $image_info[3];
    } else if (isValidURL($relativePathOriginal)) {
        $url = $relativePathOriginal;
    } else {
        return '<!-- invalid URL ' . $relativePathOriginal . ' -->';
    }
    if (empty($title)) {
        $title = basename($relativePath);
    }
    $title = safeString($title);
    $img = "<img style=\"{$style}\" alt=\"{$title}\" title=\"{$title}\" id=\"{$id}\" class=\"{$class}\" {$wh} ";
    if ($lazyLoad) {
        if (is_string($lazyLoad)) {
            $loading = getURL($lazyLoad);
        } else {
            $loading = getURL('view/img/loading-gif.png');
        }
        $img .= " src=\"{$loading}\" data-src=\"{$url}\" ";
    } else {
        $img .= " src=\"{$url}\" ";
    }
    $img .= "/>";

    return $img;
}

function createWebPIfNotExists($path) {
    if (version_compare(PHP_VERSION, '8.0.0') < 0 || !file_exists($path)) {
        return $path;
    }
    $extension = pathinfo($path, PATHINFO_EXTENSION);
    if ($extension !== 'jpg') {
        return $path;
    }
    $nextGenPath = str_replace('.jpg', '_jpg.webp', $path);

    if (!file_exists($nextGenPath)) {
        convertImage($path, $nextGenPath, 70);
    }
    return $nextGenPath;
}

function getVideoImagewithHoverAnimation($relativePath, $relativePathHoverAnimation = '', $title = '') {
    $id = uniqid();
    $img = getImageTagIfExists($relativePath, $title, "thumbsJPG{$id}", '', 'thumbsJPG img img-responsive') . PHP_EOL;
    if (!empty($relativePathHoverAnimation) && empty($_REQUEST['noImgGif'])) {
        $img .= getImageTagIfExists($relativePathHoverAnimation, $title, "thumbsGIF{$id}", 'position: absolute; top: 0;', 'thumbsGIF img img-responsive ', true) . PHP_EOL;
    }
    return '<div class="thumbsImage">' . $img . '</div>';
}

function getRelativePath($path) {
    global $global;
    $relativePath = '';
    $parts = explode('view/img/', $path);

    if (!empty($parts[1])) {
        $relativePath = 'view/img/' . $parts[1];
    }
    if (empty($relativePath)) {
        $parts = explode('videos/', $path);
        if (!empty($parts[1])) {
            $relativePath = 'videos/' . $parts[1];
        }
    }

    if (empty($relativePath)) {
        $relativePath = $path;
    }
    $parts2 = explode('?', $relativePath);
    $relativePath = str_replace('\\', '/', $relativePath);
    //var_dump($path, $relativePath, $parts);
    return $parts2[0];
}

function local_get_contents($path) {
    if (function_exists('fopen')) {
        $myfile = fopen($path, "r") or die("Unable to open file! [{$path}]");
        $text = fread($myfile, filesize($path));
        fclose($myfile);
        return $text;
    }
}

function getSelfUserAgent() {
    global $global, $AVideoStreamer_UA;
    $agent = $AVideoStreamer_UA . "_";
    $agent .= md5($global['salt']);
    return $agent;
}

function isValidM3U8Link($url, $timeout = 3) {
    if (!isValidURL($url)) {
        return false;
    }
    $content = url_get_contents($url, '', $timeout);
    if (!empty($content)) {
        if (preg_match('/EXTM3U/', $content)) {
            return true;
        }
    }
    return false;
}

function url_get_contents($url, $ctx = "", $timeout = 0, $debug = false) {
    global $global, $mysqlHost, $mysqlUser, $mysqlPass, $mysqlDatabase, $mysqlPort;
    if (!isValidURLOrPath($url)) {
        _error_log('url_get_contents Cannot download ' . $url);
        return false;
    }
    if ($debug) {
        _error_log("url_get_contents: Start $url, $ctx, $timeout " . getSelfURI() . " " . getRealIpAddr() . " " . json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)));
    }
    $agent = getSelfUserAgent();

    if (empty($ctx)) {
        $opts = [
            'http' => ['header' => "User-Agent: {$agent}\r\n"],
            "ssl" => [
                "verify_peer" => false,
                "verify_peer_name" => false,
                "allow_self_signed" => true,
            ],
        ];
        if (!empty($timeout)) {
            ini_set('default_socket_timeout', $timeout);
            $opts['http'] = ['timeout' => $timeout];
        }
        $context = stream_context_create($opts);
    } else {
        $context = $ctx;
    }
    if (ini_get('allow_url_fopen')) {
        if ($debug) {
            _error_log("url_get_contents: allow_url_fopen {$url}");
        }
        try {
            $tmp = @file_get_contents($url, false, $context);
            if ($tmp !== false) {
                $response = remove_utf8_bom($tmp);
                if ($debug) {
                    //_error_log("url_get_contents: SUCCESS file_get_contents($url) {$response}");
                    _error_log("url_get_contents: SUCCESS file_get_contents($url)");
                }
                return $response;
            }
            if ($debug) {
                _error_log("url_get_contents: ERROR file_get_contents($url) ");
            }
        } catch (ErrorException $e) {
            if ($debug) {
                _error_log("url_get_contents: allow_url_fopen ERROR " . $e->getMessage() . "  {$url}");
            }
            return "url_get_contents: " . $e->getMessage();
        }
    } elseif (function_exists('curl_init')) {
        if ($debug) {
            _error_log("url_get_contents: CURL  {$url} ");
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_USERAGENT, $agent);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        if (!empty($timeout)) {
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout + 10);
        }
        $output = curl_exec($ch);
        curl_close($ch);
        if ($debug) {
            _error_log("url_get_contents: CURL SUCCESS {$url}");
        }
        return remove_utf8_bom($output);
    }
    if ($debug) {
        _error_log("url_get_contents: Nothing yet  {$url}");
    }

    // try wget
    $filename = getTmpDir("YPTurl_get_contents") . md5($url);
    if ($debug) {
        _error_log("url_get_contents: try wget $filename {$url}");
    }
    if (wget($url, $filename, $debug)) {
        if ($debug) {
            _error_log("url_get_contents: wget success {$url} ");
        }
        $result = file_get_contents($filename);
        unlink($filename);
        if (!empty($result)) {
            return remove_utf8_bom($result);
        }
    } elseif ($debug) {
        _error_log("url_get_contents: try wget fail {$url}");
    }

    return false;
}

function getUpdatesFilesArray() {
    global $config, $global;
    if (!class_exists('User') || !User::isAdmin()) {
        return [];
    }
    $files1 = scandir($global['systemRootPath'] . "updatedb");
    $updateFiles = [];
    foreach ($files1 as $value) {
        preg_match("/updateDb.v([0-9.]*).sql/", $value, $match);
        if (!empty($match)) {
            if ($config->currentVersionLowerThen($match[1])) {
                $updateFiles[] = ['filename' => $match[0], 'version' => $match[1]];
            }
        }
    }
    usort($updateFiles, function ($a, $b) {
        return version_compare($a['version'], $b['version']);
    });
    return $updateFiles;
}

function thereIsAnyUpdate() {
    if (!User::isAdmin()) {
        return false;
    }
    $name = 'thereIsAnyUpdate';
    if (!isset($_SESSION['sessionCache'][$name])) {
        $files = getUpdatesFilesArray();
        if (!empty($files)) {
            _session_start();
            $_SESSION['sessionCache'][$name] = $files;
        }
    }
    return @$_SESSION['sessionCache'][$name];
}

function thereIsAnyRemoteUpdate() {
    if (!User::isAdmin()) {
        return false;
    }
    global $config;

    $cacheName = '_thereIsAnyRemoteUpdate';
    $cache = ObjectYPT::getCache($cacheName, 86400); // 24 hours
    if (!empty($cache)) {
        return $cache;
    }

    $version = _json_decode(url_get_contents("https://tutorials.wwbn.net/version"));
    //$version = _json_decode(url_get_contents("https://tutorialsavideo.b-cdn.net/version", "", 4));
    if (empty($version)) {
        return false;
    }
    $name = 'thereIsAnyRemoteUpdate';
    if (!isset($_SESSION['sessionCache'][$name])) {
        if (!empty($version)) {
            _session_start();
            if (version_compare($config->getVersion(), $version->version) === -1) {
                $_SESSION['sessionCache'][$name] = $version;
            } else {
                $_SESSION['sessionCache'][$name] = false;
            }
        }
    }
    ObjectYPT::setCache($cacheName, $_SESSION['sessionCache'][$name]);
    return $_SESSION['sessionCache'][$name];
}

function UTF8encode($data) {
    global $advancedCustom, $global;

    if (!empty($advancedCustom->utf8Encode)) {
        return utf8_encode($data);
    }
    if (!empty($advancedCustom->utf8Decode)) {
        return utf8_decode($data);
    }
    return $data;
}

//detect search engine bots
function isBot() {
    global $_isBot;
    if (empty($_SERVER['HTTP_USER_AGENT'])) {
        return true;
    }
    if (isAVideoEncoder()) {
        return false;
    }
    if (isset($_isBot)) {
        return $_isBot;
    }
    $_isBot = false;
    // User lowercase string for comparison.
    $user_agent = strtolower($_SERVER['HTTP_USER_AGENT']);
    // A list of some common words used only for bots and crawlers.
    $bot_identifiers = [
        'bot',
        'slurp',
        'crawler',
        'spider',
        'curl',
        'facebook',
        'fetch',
        'loader',
        'lighthouse',
        'pingdom',
        'gtmetrix',
        'ptst',
        'dmbrowser',
        'dareboost',
    ];
    // See if one of the identifiers is in the UA string.
    foreach ($bot_identifiers as $identifier) {
        if (stripos($user_agent, $identifier) !== false) {
            $_isBot = true;
            break;
        }
    }
    return $_isBot;
}

/**
 * A function that could get me the last N lines of a log file.
 * @param type $filepath
 * @param type $lines
 * @param type $adaptive
 * @return boolean
 */
function tail($filepath, $lines = 1, $adaptive = true, $returnArray = false) {
    if (!function_exists('mb_strlen')) {
        $msg = "AVideoLog::ERROR you need to install the mb_strlen function to make it work, please the command 'sudo apt install php-mbstring'";
        if ($returnArray) {
            return [[$msg]];
        } else {
            return $msg;
        }
    }
    // Open file
    $f = @fopen($filepath, "rb");
    if ($f === false) {
        return false;
    }

    // Sets buffer size, according to the number of lines to retrieve.
    // This gives a performance boost when reading a few lines from the file.
    if (!$adaptive) {
        $buffer = 4096;
    } else {
        $buffer = ($lines < 2 ? 64 : ($lines < 10 ? 512 : 4096));
    }

    // Jump to last character
    fseek($f, -1, SEEK_END);
    // Read it and adjust line number if necessary
    // (Otherwise the result would be wrong if file doesn't end with a blank line)
    if (fread($f, 1) !== "\n") {
        $lines -= 1;
    }

    // Start reading
    $output = '';
    $chunk = '';
    // While we would like more
    while (ftell($f) > 0 && $lines >= 0) {
        // Figure out how far back we should jump
        $seek = min(ftell($f), $buffer);
        // Do the jump (backwards, relative to where we are)
        fseek($f, -$seek, SEEK_CUR);
        // Read a chunk and prepend it to our output
        $output = ($chunk = fread($f, $seek)) . $output;
        // Jump back to where we started reading
        fseek($f, -mb_strlen($chunk, '8bit'), SEEK_CUR);
        // Decrease our line counter
        $lines -= substr_count($chunk, "\n");
    }
    // While we have too many lines
    // (Because of buffer size we might have read too many)
    while ($lines++ < 0) {
        // Find first newline and remove all text before that
        $output = substr($output, strpos($output, "\n") + 1);
    }
    // Close file and return
    fclose($f);
    $output = trim($output);
    if ($returnArray) {
        $array = explode("\n", $output);
        $newArray = [];
        foreach ($array as $value) {
            $newArray[] = [$value];
        }
        return $newArray;
    } else {
        return $output;
    }
}

function encryptPassword($password, $noSalt = false) {
    global $advancedCustom, $global, $advancedCustomUser;
    if (!empty($advancedCustomUser->encryptPasswordsWithSalt) && !empty($global['salt']) && empty($noSalt)) {
        $password .= $global['salt'];
    }

    return md5(hash("whirlpool", sha1($password)));
}

function encryptPasswordVerify($password, $hash, $encodedPass = false) {
    global $advancedCustom, $global;
    if (!$encodedPass || $encodedPass === 'false') {
        //_error_log("encryptPasswordVerify: encrypt");
        $passwordSalted = encryptPassword($password);
        // in case you enable the salt later
        $passwordUnSalted = encryptPassword($password, true);
    } else {
        //_error_log("encryptPasswordVerify: do not encrypt");
        $passwordSalted = $password;
        // in case you enable the salt later
        $passwordUnSalted = $password;
    }
    //_error_log("passwordSalted = $passwordSalted,  hash=$hash, passwordUnSalted=$passwordUnSalted");
    $isValid = $passwordSalted === $hash || $passwordUnSalted === $hash;

    if (!$isValid) {
        $passwordFromHash = User::getPasswordFromUserHashIfTheItIsValid($password);
        $isValid = $passwordFromHash === $hash;
    }

    if (!$isValid) {
        if ($password === $hash) {
            _error_log('encryptPasswordVerify: this is a deprecated password, this will stop to work soon ' . json_encode(debug_backtrace()), AVideoLog::$SECURITY);
            return true;
        }
    }
    return $isValid;
}

function encryptPasswordV2($uniqueSalt, $password, $noSalt = false) {
    global $advancedCustom, $global, $advancedCustomUser;
    if (!empty($advancedCustomUser->encryptPasswordsWithSalt) && !empty($global['salt']) && empty($noSalt)) {
        $password .= $global['salt'];
    }
    $password .= md5($uniqueSalt);
    return md5(hash("whirlpool", sha1($password)));
}

function encryptPasswordVerifyV2($uniqueSalt, $password, $hash, $encodedPass = false) {
    global $advancedCustom, $global;
    if (!$encodedPass || $encodedPass === 'false') {
        //_error_log("encryptPasswordVerify: encrypt");
        $passwordSalted = encryptPasswordV2($uniqueSalt, $password);
        // in case you enable the salt later
        $passwordUnSalted = encryptPasswordV2($uniqueSalt, $password, true);
    } else {
        //_error_log("encryptPasswordVerify: do not encrypt");
        $passwordSalted = $password;
        // in case you enable the salt later
        $passwordUnSalted = $password;
    }
    //_error_log("passwordSalted = $passwordSalted,  hash=$hash, passwordUnSalted=$passwordUnSalted");
    return $passwordSalted === $hash || $passwordUnSalted === $hash || $password === $hash;
}

function isMobile($userAgent = null, $httpHeaders = null) {
    if (empty($userAgent) && empty($_SERVER["HTTP_USER_AGENT"])) {
        return false;
    }
    global $global;
    require_once $global['systemRootPath'] . 'objects/Mobile_Detect.php';
    $detect = new Mobile_Detect();

    return $detect->isMobile($userAgent, $httpHeaders);
}

function isAndroid() {
    global $global;
    require_once $global['systemRootPath'] . 'objects/Mobile_Detect.php';
    $detect = new Mobile_Detect();

    return $detect->is('AndroidOS');
}

function isChannelPage() {
    return strpos($_SERVER["SCRIPT_NAME"], 'view/channel.php') !== false;
}

function isAVideoMobileApp($user_agent = "") {
    if (empty($user_agent)) {
        $user_agent = @$_SERVER['HTTP_USER_AGENT'];
    }
    if (empty($user_agent)) {
        return false;
    }
    global $AVideoMobileAPP_UA;
    if (preg_match("/{$AVideoMobileAPP_UA}(.*)/", $_SERVER["HTTP_USER_AGENT"], $match)) {
        $url = trim($match[1]);
        if (!empty($url)) {
            return $url;
        }
        return true;
    }
    return false;
}

function isAVideoEncoder($user_agent = "") {
    if (empty($user_agent)) {
        $user_agent = @$_SERVER['HTTP_USER_AGENT'];
    }
    if (empty($user_agent)) {
        return false;
    }
    global $AVideoEncoder_UA;
    if (preg_match("/{$AVideoEncoder_UA}(.*)/", $user_agent, $match)) {
        $url = trim($match[1]);
        if (!empty($url)) {
            return $url;
        }
        return true;
    }
    return false;
}

function isCDN() {
    if (empty($_SERVER['HTTP_CDN_HOST'])) {
        return false;
    }
    return isFromCDN($_SERVER['HTTP_CDN_HOST']);
}

function isFromCDN($url) {
    if (preg_match('/cdn.ypt.me/i', $url)) {
        return true;
    }
    return false;
}

function isAVideo($user_agent = "") {
    if (empty($user_agent)) {
        $user_agent = @$_SERVER['HTTP_USER_AGENT'];
    }
    if (empty($user_agent)) {
        return false;
    }
    global $AVideoEncoder_UA;
    if (preg_match("/AVideo(.*)/", $_SERVER["HTTP_USER_AGENT"], $match)) {
        $url = trim($match[1]);
        if (!empty($url)) {
            return $url;
        }
        return true;
    }
    return false;
}

function isAVideoEncoderOnSameDomain() {
    $url = isAVideoEncoder();
    if (empty($url)) {
        return false;
    }
    $url = "http://{$url}";
    return isSameDomainAsMyAVideo($url);
}

function isSameDomainAsMyAVideo($url) {
    global $global;
    if (empty($url)) {
        return false;
    }
    return isSameDomain($url, $global['webSiteRootURL']) || isSameDomain($url, getCDN());
}

function requestComesFromSameDomainAsMyAVideo() {
    global $global;
    $url = '';
    if (!empty($_SERVER['HTTP_REFERER'])) {
        $url = $_SERVER['HTTP_REFERER'];
    } elseif (!empty($_SERVER['HTTP_ORIGIN'])) {
        $url = $_SERVER['HTTP_ORIGIN'];
    }
    //var_dump($_SERVER);exit;
    //_error_log("requestComesFromSameDomainAsMyAVideo: ({$url}) == ({$global['webSiteRootURL']})");
    return isSameDomain($url, $global['webSiteRootURL']) || isSameDomain($url, getCDN()) || isFromCDN($url);
}

function forbidIfIsUntrustedRequest($logMsg = '', $approveAVideoUserAgent = true) {
    global $global;
    if (isUntrustedRequest($logMsg, $approveAVideoUserAgent)) {
        forbiddenPage('Invalid Request ' . getRealIpAddr(), true);
    }
}

function isUntrustedRequest($logMsg = '', $approveAVideoUserAgent = true) {
    global $global;
    if (!empty($global['bypassSameDomainCheck']) || isCommandLineInterface()) {
        return false;
    }
    if (!requestComesFromSameDomainAsMyAVideo()) {
        if ($approveAVideoUserAgent && isAVideoUserAgent()) {
            return false;
        } else {
            _error_log('isUntrustedRequest: ' . json_encode($logMsg), AVideoLog::$SECURITY);
            return true;
        }
    }
    return false;
}

function forbidIfItIsNotMyUsersId($users_id, $logMsg = '') {
    if (itIsNotMyUsersId($users_id)) {
        _error_log("forbidIfItIsNotMyUsersId: [{$users_id}]!=[".User::getId()."] {$logMsg}");
        forbiddenPage('It is not your user ' . getRealIpAddr(), true);
    }
}

function itIsNotMyUsersId($users_id) {
    $users_id = intval($users_id);
    if (empty($users_id)) {
        return false;
    }
    if (!User::isLogged()) {
        return true;
    }
    return User::getId() != $users_id;
}

function requestComesFromSafePlace() {
    return (requestComesFromSameDomainAsMyAVideo() || isAVideo());
}

function addGlobalTokenIfSameDomain($url) {
    if (!filter_var($url, FILTER_VALIDATE_URL) || (empty($_GET['livelink']) || !preg_match("/^http.*/i", $_GET['livelink']))) {
        return $url;
    }
    if (!isSameDomainAsMyAVideo($url)) {
        return $url;
    }
    return addQueryStringParameter($url, 'globalToken', getToken(60));
}

/**
 * Remove a query string parameter from an URL.
 *
 * @param string $url
 * @param string $varname
 *
 * @return string
 */
function removeQueryStringParameter($url, $varname) {
    $parsedUrl = parse_url($url);
    $query = [];

    if (isset($parsedUrl['query'])) {
        parse_str($parsedUrl['query'], $query);
        unset($query[$varname]);
    }

    $path = $parsedUrl['path'] ?? '';
    $query = !empty($query) ? '?' . http_build_query($query) : '';

    if (empty($parsedUrl['scheme'])) {
        $scheme = '';
    } else {
        $scheme = "{$parsedUrl['scheme']}:";
    }
    return $scheme . '//' . $parsedUrl['host'] . $path . $query;
}

/**
 * Add a query string parameter from an URL.
 *
 * @param string $url
 * @param string $varname
 *
 * @return string
 */
function addQueryStringParameter($url, $varname, $value) {
    $parsedUrl = parse_url($url);
    if (empty($parsedUrl['host'])) {
        return "";
    }
    $query = [];

    if (isset($parsedUrl['query'])) {
        parse_str($parsedUrl['query'], $query);
    }
    $query[$varname] = $value;
    $path = $parsedUrl['path'] ?? '';
    $query = !empty($query) ? '?' . http_build_query($query) : '';

    $port = '';
    if (!empty($parsedUrl['port']) && $parsedUrl['port'] != '80') {
        $port = ":{$parsedUrl['port']}";
    }

    if (empty($parsedUrl['scheme'])) {
        $scheme = '';
    } else {
        $scheme = "{$parsedUrl['scheme']}:";
    }
    return $scheme . '//' . $parsedUrl['host'] . $port . $path . $query;
}

function isSameDomain($url1, $url2) {
    if (empty($url1) || empty($url2)) {
        return false;
    }
    return (get_domain($url1) === get_domain($url2));
}

function isAVideoStreamer($user_agent = "") {
    if (empty($user_agent)) {
        $user_agent = @$_SERVER['HTTP_USER_AGENT'];
    }
    if (empty($user_agent)) {
        return false;
    }
    global $AVideoStreamer_UA, $global;
    $md5 = md5($global['salt']);
    if (preg_match("/{$AVideoStreamer_UA}_{$md5}/", $_SERVER["HTTP_USER_AGENT"])) {
        return true;
    }
    return false;
}

function isAVideoUserAgent($user_agent = "") {
    if (empty($user_agent)) {
        $user_agent = @$_SERVER['HTTP_USER_AGENT'];
    }
    if (empty($user_agent)) {
        return false;
    }
    global $AVideoMobileAPP_UA, $AVideoEncoder_UA, $AVideoEncoderNetwork_UA, $AVideoStreamer_UA, $AVideoStorage_UA, $global;

    // Lavf = ffmpeg
    $agents = [$AVideoMobileAPP_UA, $AVideoEncoder_UA, $AVideoEncoderNetwork_UA, $AVideoStreamer_UA, $AVideoStorage_UA, 'Lavf'];

    foreach ($agents as $value) {
        if (preg_match("/{$value}/", $_SERVER["HTTP_USER_AGENT"])) {
            return true;
        }
    }

    return false;
}

function isAVideoStorage($user_agent = "") {
    if (empty($user_agent)) {
        $user_agent = @$_SERVER['HTTP_USER_AGENT'];
    }
    if (empty($user_agent)) {
        return false;
    }
    global $AVideoStorage_UA;
    if (preg_match("/{$AVideoStorage_UA}(.*)/", $_SERVER["HTTP_USER_AGENT"], $match)) {
        $url = trim($match[1]);
        if (!empty($url)) {
            return $url;
        }
        return true;
    }
    return false;
}

function get_domain($url, $ifEmptyReturnSameString = false) {
    $pieces = parse_url($url);
    $domain = $pieces['host'] ?? '';
    if (empty($domain)) {
        return $ifEmptyReturnSameString ? $url : false;
    }
    if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs)) {
        return $regs['domain'];
    } else {
        $isIp = (bool) ip2long($pieces['host']);
        if ($isIp) {
            return $pieces['host'];
        }
    }
    return false;
}

function verify($url) {
    global $global;
    ini_set('default_socket_timeout', 5);
    $cacheFile = sys_get_temp_dir() . '/' . md5($url) . "_verify.log";
    $lifetime = 86400; //24 hours
    _error_log("Verification Start {$url} cacheFile={$cacheFile}");
    $verifyURL = "https://search.ypt.me/verify.php";
    $verifyURL = addQueryStringParameter($verifyURL, 'url', $url);
    $verifyURL = addQueryStringParameter($verifyURL, 'screenshot', 1);
    if (!file_exists($cacheFile) || (time() > (filemtime($cacheFile) + $lifetime))) {
        _error_log("Verification Creating the Cache {$url}");
        $result = url_get_contents($verifyURL, '', 5);
        if ($result !== 'Invalid URL') {
            file_put_contents($cacheFile, $result);
        }
    } else {
        if (!file_exists($cacheFile)) {
            _error_log("Verification GetFrom Cache  !file_exists($cacheFile)");
        }
        $filemtime = filemtime($cacheFile);
        $time = time();
        if ($time > ($filemtime + $lifetime)) {
            _error_log("Verification GetFrom Cache  $time > ($filemtime + $lifetime)");
        }
        _error_log("Verification GetFrom Cache $cacheFile");
        $result = file_get_contents($cacheFile);
        if ($result === 'Invalid URL') {
            unlink($cacheFile);
        }
    }
    _error_log("Verification Response ($verifyURL): {$result}");
    return json_decode($result);
}

function isVerified($url) {
    $resultV = verify($url);
    if (!empty($resultV) && !$resultV->verified) {
        error_log("Error on Login not verified");
        return false;
    }
    return true;
}

function siteMap() {
    _error_log("siteMap: start");
    ini_set('memory_limit', '-1');
    ini_set('max_execution_time', 0);
    @session_write_close();
    global $global, $advancedCustom;
    $global['disableVideoTags'] = 1;
    $date = date('Y-m-d\TH:i:s') . "+00:00";
    $xml = '<?xml version="1.0" encoding="UTF-8"?>
    <urlset
        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd
        http://www.w3.org/1999/xhtml http://www.w3.org/2002/08/xhtml/xhtml1-strict.xsd"
        xmlns:video="http://www.google.com/schemas/sitemap-video/1.1"
        xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:xhtml="http://www.w3.org/1999/xhtml">
        <!-- Main Page -->
        <url>
            <loc>' . $global['webSiteRootURL'] . '</loc>
            <lastmod>' . $date . '</lastmod>
            <changefreq>always</changefreq>
            <priority>1.00</priority>
        </url>

        <url>
            <loc>' . $global['webSiteRootURL'] . 'help</loc>
            <lastmod>' . $date . '</lastmod>
            <changefreq>monthly</changefreq>
            <priority>0.50</priority>
        </url>
        <url>
            <loc>' . $global['webSiteRootURL'] . 'about</loc>
            <lastmod>' . $date . '</lastmod>
            <changefreq>monthly</changefreq>
            <priority>0.50</priority>
        </url>
        <url>
            <loc>' . $global['webSiteRootURL'] . 'contact</loc>
            <lastmod>' . $date . '</lastmod>
            <changefreq>monthly</changefreq>
            <priority>0.50</priority>
        </url>

        <!-- Channels -->
        <url>
            <loc>' . $global['webSiteRootURL'] . 'channels</loc>
            <lastmod>' . $date . '</lastmod>
            <changefreq>daily</changefreq>
            <priority>0.80</priority>
        </url>
        ';

    $_REQUEST['rowCount'] = $advancedCustom->siteMapRowsLimit;
    _error_log("siteMap: rowCount {$_REQUEST['rowCount']} ");
    $_POST['sort']['modified'] = "DESC";
    TimeLogStart("siteMap getAllUsersThatHasVideos");
    $users = User::getAllUsersThatHasVideos(true);
    _error_log("siteMap: getAllUsers " . count($users));
    foreach ($users as $value) {
        $xml .= '
            <url>
                <loc>' . User::getChannelLink($value['id']) . '</loc>
                <lastmod>' . $date . '</lastmod>
                <changefreq>daily</changefreq>
                <priority>0.90</priority>
            </url>
            ';
    }
    TimeLogEnd("siteMap getAllUsersThatHasVideos", __LINE__, 0.5);
    TimeLogStart("siteMap getAllCategories");
    $xml .= '
        <!-- Categories -->
        ';
    $_REQUEST['rowCount'] = $advancedCustom->siteMapRowsLimit;
    $_POST['sort']['modified'] = "DESC";
    $rows = Category::getAllCategories();
    _error_log("siteMap: getAllCategories " . count($rows));
    foreach ($rows as $value) {
        $xml .= '
            <url>
                <loc>' . $global['webSiteRootURL'] . 'cat/' . $value['clean_name'] . '</loc>
                <lastmod>' . $date . '</lastmod>
                <changefreq>weekly</changefreq>
                <priority>0.80</priority>
            </url>
            ';
    }
    TimeLogEnd("siteMap getAllCategories", __LINE__, 0.5);
    TimeLogStart("siteMap getAllVideos");
    $xml .= '<!-- Videos -->';
    $_REQUEST['rowCount'] = $advancedCustom->siteMapRowsLimit * 10;
    $_POST['sort']['created'] = "DESC";
    $rows = Video::getAllVideos(!empty($advancedCustom->showPrivateVideosOnSitemap) ? "viewableNotUnlisted" : "publicOnly");
    _error_log("siteMap: getAllVideos " . count($rows));
    foreach ($rows as $video) {
        $videos_id = $video['id'];

        TimeLogStart("siteMap Video::getPoster $videos_id");
        $img = Video::getPoster($videos_id);
        TimeLogEnd("siteMap Video::getPoster $videos_id", __LINE__, 0.5);

        $description = str_replace(['"', "\n", "\r"], ['', ' ', ' '], empty(trim($video['description'])) ? $video['title'] : $video['description']);
        $duration = parseDurationToSeconds($video['duration']);
        if ($duration > 28800) {
            // this is because this issue https://github.com/WWBN/AVideo/issues/3338 remove in the future if is not necessary anymore
            $duration = 28800;
        }

        TimeLogStart("siteMap Video::getLink $videos_id");
        $loc = Video::getLink($video['id'], $video['clean_title']);
        TimeLogEnd("siteMap Video::getLink $videos_id", __LINE__, 0.5);
        $title = strip_tags($video['title']);
        $description = _substr(strip_tags(br2nl($description)), 0, 2048);
        TimeLogStart("siteMap Video::getLinkToVideo $videos_id");
        $player_loc = Video::getLinkToVideo($video['id'], $video['clean_title'], true);
        TimeLogEnd("siteMap Video::getLinkToVideo $videos_id", __LINE__, 0.5);
        TimeLogStart("siteMap Video::isPublic $videos_id");
        $requires_subscription = Video::isPublic($video['id']) ? "no" : "yes";
        TimeLogEnd("siteMap Video::isPublic $videos_id", __LINE__, 0.5);
        TimeLogStart("siteMap Video::getChannelLink $videos_id");
        $uploader_info = User::getChannelLink($video['users_id']);
        TimeLogEnd("siteMap Video::getChannelLink $videos_id", __LINE__, 0.5);
        TimeLogStart("siteMap Video::getNameIdentificationById $videos_id");
        $uploader = htmlentities(User::getNameIdentificationById($video['users_id']));
        TimeLogEnd("siteMap Video::getNameIdentificationById $videos_id", __LINE__, 0.5);

        $xml .= '
            <url>
                <loc>' . $loc . '</loc>
                <video:video>
                    <video:thumbnail_loc>' . $img . '</video:thumbnail_loc>
                    <video:title><![CDATA[' . $title . ']]></video:title>
                    <video:description><![CDATA[' . $description . ']]></video:description>
                    <video:player_loc><![CDATA[' . $player_loc . ']]></video:player_loc>
                    <video:duration>' . $duration . '</video:duration>
                    <video:view_count>' . $video['views_count'] . '</video:view_count>
                    <video:publication_date>' . date("Y-m-d\TH:i:s", strtotime($video['created'])) . '+00:00</video:publication_date>
                    <video:family_friendly>yes</video:family_friendly>
                    <video:requires_subscription>' . $requires_subscription . '</video:requires_subscription>
                    <video:uploader info="' . $uploader_info . '">' . $uploader . '</video:uploader>
                    <video:live>no</video:live>
                </video:video>
            </url>
            ';
    }
    TimeLogEnd("siteMap getAllVideos", __LINE__, 0.5);
    $xml .= '</urlset> ';
    _error_log("siteMap: done ");
    $newXML1 = preg_replace('/[^\x{0009}\x{000a}\x{000d}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}]+/u', '', $xml);
    if (empty($newXML1)) {
        _error_log("siteMap: pregreplace1 fail ");
        $newXML1 = $xml;
    }
    if (!empty($advancedCustom->siteMapUTF8Fix)) {
        $newXML2 = preg_replace('/&(?!#?[a-z0-9]+;)/', '&amp;', $newXML1);
        if (empty($newXML2)) {
            _error_log("siteMap: pregreplace2 fail ");
            $newXML2 = $newXML1;
        }
        $newXML3 = preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', $newXML2);
        if (empty($newXML3)) {
            _error_log("siteMap: pregreplace3 fail ");
            $newXML3 = $newXML2;
        }
        $newXML4 = preg_replace('/[\x00-\x1F\x7F]/', '', $newXML3);
        if (empty($newXML4)) {
            _error_log("siteMap: pregreplace4 fail ");
            $newXML4 = $newXML3;
        }
        $newXML5 = preg_replace('/[\x00-\x1F\x7F\xA0]/u', '', $newXML4);
        if (empty($newXML5)) {
            _error_log("siteMap: pregreplace5 fail ");
            $newXML5 = $newXML4;
        }
    } else {
        $newXML5 = $newXML1;
    }
    return $newXML5;
}

function object_to_array($obj) {
    //only process if it's an object or array being passed to the function
    if (is_object($obj) || is_array($obj)) {
        $ret = (array) $obj;
        foreach ($ret as &$item) {
            //recursively process EACH element regardless of type
            $item = object_to_array($item);
        }
        return $ret;
    }
    //otherwise (i.e. for scalar values) return without modification
    else {
        return $obj;
    }
}

function allowOrigin() {
    global $global;
    cleanUpAccessControlHeader();
    $HTTP_ORIGIN = empty($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_REFERER'] : $_SERVER['HTTP_ORIGIN'];
    if (empty($HTTP_ORIGIN)) {
        $server = parse_url($global['webSiteRootURL']);
        header('Access-Control-Allow-Origin: ' . $server["scheme"] . '://imasdk.googleapis.com');
    } else {
        header("Access-Control-Allow-Origin: " . $HTTP_ORIGIN);
    }
    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Allow-Methods: GET,HEAD,OPTIONS,POST,PUT");
    header("Access-Control-Allow-Headers: Access-Control-Allow-Headers, Origin,Accept, X-Requested-With, Content-Type, Access-Control-Request-Method, Access-Control-Request-Headers");
}

function cleanUpAccessControlHeader() {
    if (!headers_sent()) {
        foreach (headers_list() as $header) {
            if (preg_match('/Access-Control-Allow-Origin/i', $header)) {
                $parts = explode(':', $header);
                header_remove($parts[0]);
            }
        }
    }
}

function rrmdir($dir) {
    //if(preg_match('/cache/i', $dir)){_error_log("rrmdir($dir) ". json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)));exit;}

    $dir = str_replace(['//', '\\\\'], DIRECTORY_SEPARATOR, $dir);
    //_error_log('rrmdir: ' . $dir);
    if (empty($dir)) {
        _error_log('rrmdir: the dir was empty');
        return false;
    }
    global $global;
    $dir = fixPath($dir);
    $pattern = '/' . addcslashes($dir, DIRECTORY_SEPARATOR) . 'videos[\/\\\]?$/i';
    if ($dir == getVideosDir() || $dir == "{$global['systemRootPath']}videos" . DIRECTORY_SEPARATOR || preg_match($pattern, $dir)) {
        _error_log('rrmdir: A script ties to delete the videos Directory [' . $dir . '] ' . json_encode([$dir == getVideosDir(), $dir == "{$global['systemRootPath']}videos" . DIRECTORY_SEPARATOR, preg_match($pattern, $dir)]));
        return false;
    }
    rrmdirCommandLine($dir);
    if (is_dir($dir)) {
        //_error_log('rrmdir: The Directory was not deleted, trying again ' . $dir);
        $objects = scandir($dir);
        //_error_log('rrmdir: scandir ' . $dir . ' '. json_encode($objects));
        foreach ($objects as $object) {
            if ($object !== '.' && $object !== '..') {
                if (is_dir($dir . DIRECTORY_SEPARATOR . $object)) {
                    rrmdir($dir . DIRECTORY_SEPARATOR . $object);
                } else {
                    unlink($dir . DIRECTORY_SEPARATOR . $object);
                }
            }
        }
        if (preg_match('/(\/|^)videos(\/cache)?\/?$/i', $dir)) {
            _error_log('rrmdir: do not delete videos or cache folder ' . $dir);
            // do not delete videos or cache folder
            return false;
        }
        if (rmdir($dir)) {
            return true;
        } else if (is_dir($dir)) {
            _error_log('rrmdir: could not delete folder ' . $dir);
            return false;
        }
    } else {
        //_error_log('rrmdir: The Directory does not exists '.$dir);
        return true;
    }
}

function rrmdirCommandLine($dir, $async = false) {
    if (is_dir($dir)) {
        $dir = escapeshellarg($dir);
        if (isWindows()) {
            $command = ('rd /s /q ' . $dir);
        } else {
            $command = ('rm -fR ' . $dir);
        }

        if ($async) {
            return execAsync($command);
        } else {
            return exec($command);
        }
    }
}

/**
 * You can now configure it on the configuration.php
 * @return boolean
 */
function ddosProtection() {
    global $global;
    $maxCon = empty($global['ddosMaxConnections']) ? 40 : $global['ddosMaxConnections'];
    $secondTimeout = empty($global['ddosSecondTimeout']) ? 5 : $global['ddosSecondTimeout'];
    $whitelistedFiles = [
        'playlists.json.php',
        'playlistsFromUserVideos.json.php',
        'image404.php',
    ];

    if (in_array(basename($_SERVER["SCRIPT_FILENAME"]), $whitelistedFiles)) {
        return true;
    }

    $time = time();
    if (!isset($_SESSION['bruteForceBlock']) || empty($_SESSION['bruteForceBlock'])) {
        $_SESSION['bruteForceBlock'] = [];
        $_SESSION['bruteForceBlock'][] = $time;
        return true;
    }

    $_SESSION['bruteForceBlock'][] = $time;

    //remove requests that are older than secondTimeout
    foreach ($_SESSION['bruteForceBlock'] as $key => $request_time) {
        if ($request_time < $time - $secondTimeout) {
            unset($_SESSION['bruteForceBlock'][$key]);
        }
    }

    //progressive timeout-> more requests, longer timeout
    $active_connections = count($_SESSION['bruteForceBlock']);
    $timeoutReal = ($active_connections / $maxCon) < 1 ? 0 : ($active_connections / $maxCon) * $secondTimeout;
    if ($timeoutReal) {
        _error_log("ddosProtection:: progressive timeout timeoutReal = ($timeoutReal) active_connections = ($active_connections) maxCon = ($maxCon) ", AVideoLog::$SECURITY);
    }
    sleep($timeoutReal);

    //with strict mode, penalize "attacker" with sleep() above, log and then die
    if ($global['strictDDOSprotection'] && $timeoutReal > 0) {
        $str = "bruteForceBlock: maxCon: $maxCon => secondTimeout: $secondTimeout | IP: " . getRealIpAddr() . " | count:" . count($_SESSION['bruteForceBlock']);
        _error_log($str);
        die($str);
    }

    return true;
}

function getAdsLeaderBoardBigVideo() {
    $ad = AVideoPlugin::getObjectDataIfEnabled('ADs');
    if (!empty($ad)) {
        if (isMobile()) {
            return ADs::giveGoogleATimeout($ad->leaderBoardBigVideoMobile->value);
        } else {
            return ADs::giveGoogleATimeout($ad->leaderBoardBigVideo->value);
        }
    }
}

function getAdsLeaderBoardTop() {
    $ad = AVideoPlugin::getObjectDataIfEnabled('ADs');
    if (!empty($ad)) {
        if (isMobile()) {
            return ADs::giveGoogleATimeout($ad->leaderBoardTopMobile->value);
        } else {
            return ADs::giveGoogleATimeout($ad->leaderBoardTop->value);
        }
    } else {
        return false;
    }
}

function getAdsChannelLeaderBoardTop() {
    $ad = AVideoPlugin::getObjectDataIfEnabled('ADs');
    if (!empty($ad)) {
        if (isMobile()) {
            return ADs::giveGoogleATimeout($ad->channelLeaderBoardTopMobile->value);
        } else {
            return ADs::giveGoogleATimeout($ad->channelLeaderBoardTop->value);
        }
    }
}

function getAdsLeaderBoardTop2() {
    $ad = AVideoPlugin::getObjectDataIfEnabled('ADs');
    if (!empty($ad)) {
        if (isMobile()) {
            return ADs::giveGoogleATimeout($ad->leaderBoardTopMobile2->value);
        } else {
            return ADs::giveGoogleATimeout($ad->leaderBoardTop2->value);
        }
    }
}

function getAdsLeaderBoardMiddle() {
    $ad = AVideoPlugin::getObjectDataIfEnabled('ADs');
    if (!empty($ad)) {
        if (isMobile()) {
            return ADs::giveGoogleATimeout($ad->leaderBoardMiddleMobile->value);
        } else {
            return ADs::giveGoogleATimeout($ad->leaderBoardMiddle->value);
        }
    }
}

function getAdsLeaderBoardFooter() {
    $ad = AVideoPlugin::getObjectDataIfEnabled('ADs');
    if (!empty($ad)) {
        if (isMobile()) {
            return ADs::giveGoogleATimeout($ad->leaderBoardFooterMobile->value);
        } else {
            return ADs::giveGoogleATimeout($ad->leaderBoardFooter->value);
        }
    }
}

function getAdsSideRectangle() {
    $ad = AVideoPlugin::getObjectDataIfEnabled('ADs');
    if (!empty($ad)) {
        if (isMobile()) {
            return ADs::giveGoogleATimeout($ad->sideRectangle->value);
        } else {
            return ADs::giveGoogleATimeout($ad->sideRectangle->value);
        }
    }
}

function isToHidePrivateVideos() {
    $obj = AVideoPlugin::getObjectDataIfEnabled("Gallery");
    if (!empty($obj)) {
        return $obj->hidePrivateVideos;
    }
    $obj = AVideoPlugin::getObjectDataIfEnabled("YouPHPFlix2");
    if (!empty($obj)) {
        return $obj->hidePrivateVideos;
    }
    $obj = AVideoPlugin::getObjectDataIfEnabled("YouTube");
    if (!empty($obj)) {
        return $obj->hidePrivateVideos;
    }
    return false;
}

function convertImageToOG($source, $destination) {
    if (!file_exists($destination)) {
        $w = 200;
        $h = 200;
        $sizes = getimagesize($source);
        if ($sizes[0] < $w || $sizes[1] < $h) {
            $tmpDir = getTmpDir();
            $fileConverted = $tmpDir . "_jpg_" . uniqid() . ".jpg";
            convertImage($source, $fileConverted, 100);
            im_resizeV2($fileConverted, $destination, $w, $h, 100);
            unlink($fileConverted);
        }
    }
    return $destination;
}

function convertImageToRoku($source, $destination) {
    return convertImageIfNotExists($source, $destination, 1280, 720);
}

function convertImageIfNotExists($source, $destination, $width, $height) {
    if (empty($source)) {
        _error_log("convertImageToRoku: source image is empty");
        return false;
    }
    if (file_exists($destination)) {
        $sizes = getimagesize($destination);
        if ($sizes[0] < $width || $sizes[1] < $height) {
            _error_log("convertImageIfNotExists: file is smaller " . json_encode($sizes));
            unlink($destination);
            return false;
        }
    }
    if (!file_exists($destination)) {
        try {
            $tmpDir = getTmpDir();
            $fileConverted = $tmpDir . "_jpg_" . uniqid() . ".jpg";
            convertImage($source, $fileConverted, 100);
            im_resizeV2($fileConverted, $destination, $width, $height, 100);
            @unlink($fileConverted);
        } catch (Exception $exc) {
            _error_log("convertImageIfNotExists: " . $exc->getMessage());
            return false;
        }
    }
    return $destination;
}

function ogSite() {
    global $global, $config;
    include $global['systemRootPath'] . 'objects/functionogSite.php';
}

function getOpenGraph($videos_id) {
    global $global, $config, $advancedCustom;
    include $global['systemRootPath'] . 'objects/functiongetOpenGraph.php';
}

function getLdJson($videos_id) {
    $cache = ObjectYPT::getCache("getLdJson{$videos_id}", 0);
    if (empty($cache)) {
        echo $cache;
    }
    global $global, $config;
    echo "<!-- ld+json -->";
    if (empty($videos_id)) {
        echo "<!-- ld+json no video id -->";
        if (!empty($_GET['videoName'])) {
            echo "<!-- ld+json videoName {$_GET['videoName']} -->";
            $video = Video::getVideoFromCleanTitle($_GET['videoName']);
        }
    } else {
        echo "<!-- ld+json videos_id {$videos_id} -->";
        $video = Video::getVideoLight($videos_id);
    }
    if (empty($video)) {
        echo "<!-- ld+json no video -->";
        return false;
    }
    $videos_id = $video['id'];

    $img = Video::getPoster($videos_id);

    $description = getSEODescription(empty(trim($video['description'])) ? $video['title'] : $video['description']);
    $duration = Video::getItemPropDuration($video['duration']);
    if ($duration == "PT0H0M0S") {
        $duration = "PT0H0M1S";
    }
    $output = '
    <script type="application/ld+json" id="application_ld_json">
        {
        "@context": "http://schema.org/",
        "@type": "VideoObject",
        "name": "' . getSEOTitle($video['title']) . '",
        "description": "' . $description . '",
        "thumbnailUrl": [
        "' . $img . '"
        ],
        "uploadDate": "' . date("Y-m-d\Th:i:s", strtotime($video['created'])) . '",
        "duration": "' . $duration . '",
        "contentUrl": "' . Video::getLinkToVideo($videos_id, '', false, false) . '",
        "embedUrl": "' . Video::getLinkToVideo($videos_id, '', true, false) . '",
        "interactionCount": "' . $video['views_count'] . '",
        "@id": "' . Video::getPermaLink($videos_id) . '",
        "datePublished": "' . date("Y-m-d", strtotime($video['created'])) . '",
        "interactionStatistic": [
        {
        "@type": "InteractionCounter",
        "interactionService": {
        "@type": "WebSite",
        "name": "' . str_replace('"', '', $config->getWebSiteTitle()) . '",
        "@id": "' . $global['webSiteRootURL'] . '"
        },
        "interactionType": "http://schema.org/LikeAction",
        "userInteractionCount": "' . $video['views_count'] . '"
        },
        {
        "@type": "InteractionCounter",
        "interactionType": "http://schema.org/WatchAction",
        "userInteractionCount": "' . $video['views_count'] . '"
        }
        ]
        }
    </script>';
    ObjectYPT::setCache("getLdJson{$videos_id}", $output);
    echo $output;
}

function getItemprop($videos_id) {
    $cache = ObjectYPT::getCache("getItemprop{$videos_id}", 0);
    if (empty($cache)) {
        echo $cache;
    }
    global $global, $config;
    echo "<!-- Itemprop -->";
    if (empty($videos_id)) {
        echo "<!-- Itemprop no video id -->";
        if (!empty($_GET['videoName'])) {
            echo "<!-- Itemprop videoName {$_GET['videoName']} -->";
            $video = Video::getVideoFromCleanTitle($_GET['videoName']);
        }
    } else {
        echo "<!-- Itemprop videos_id {$videos_id} -->";
        $video = Video::getVideoLight($videos_id);
    }
    if (empty($video)) {
        echo "<!-- Itemprop no video -->";
        return false;
    }
    $videos_id = $video['id'];
    $img = Video::getPoster($videos_id);

    $description = getSEODescription(emptyHTML($video['description']) ? $video['title'] : $video['description']);
    $duration = Video::getItemPropDuration($video['duration']);
    if ($duration == "PT0H0M0S") {
        $duration = "PT0H0M1S";
    }
    $output = '<span itemprop="name" content="' . getSEOTitle($video['title']) . '"></span>
    <span itemprop="description" content="' . $description . '"></span>
    <span itemprop="thumbnailUrl" content="' . $img . '"></span>
    <span itemprop="uploadDate" content="' . date("Y-m-d\Th:i:s", strtotime($video['created'])) . '"></span>
    <span itemprop="duration" content="' . $duration . '"></span>
    <span itemprop="contentUrl" content="' . Video::getLinkToVideo($videos_id) . '"></span>
    <span itemprop="embedUrl" content="' . parseVideos(Video::getLinkToVideo($videos_id)) . '"></span>
    <span itemprop="interactionCount" content="' . $video['views_count'] . '"></span>';

    ObjectYPT::setCache("getItemprop{$videos_id}", $output);
    echo $output;
}

function getOS($user_agent = "") {
    if (empty($user_agent)) {
        $user_agent = @$_SERVER['HTTP_USER_AGENT'];
    }

    $os_platform = "Unknown OS Platform";

    $os_array = [
        '/windows nt 10/i' => 'Windows 10',
        '/windows nt 6.3/i' => 'Windows 8.1',
        '/windows nt 6.2/i' => 'Windows 8',
        '/windows nt 6.1/i' => 'Windows 7',
        '/windows nt 6.0/i' => 'Windows Vista',
        '/windows nt 5.2/i' => 'Windows Server 2003/XP x64',
        '/windows nt 5.1/i' => 'Windows XP',
        '/windows xp/i' => 'Windows XP',
        '/windows nt 5.0/i' => 'Windows 2000',
        '/windows me/i' => 'Windows ME',
        '/win98/i' => 'Windows 98',
        '/win95/i' => 'Windows 95',
        '/win16/i' => 'Windows 3.11',
        '/macintosh|mac os x/i' => 'Mac OS X',
        '/mac_powerpc/i' => 'Mac OS 9',
        '/linux/i' => 'Linux',
        '/ubuntu/i' => 'Ubuntu',
        '/iphone/i' => 'iPhone',
        '/ipod/i' => 'iPod',
        '/ipad/i' => 'iPad',
        '/android/i' => 'Android',
        '/blackberry/i' => 'BlackBerry',
        '/webos/i' => 'Mobile',
    ];

    foreach ($os_array as $regex => $value) {
        if (preg_match($regex, $user_agent)) {
            $os_platform = $value;
        }
    }

    return $os_platform;
}

function get_browser_name($user_agent = "") {
    if (empty($user_agent)) {
        $user_agent = @$_SERVER['HTTP_USER_AGENT'];
    }
    if (empty($user_agent)) {
        return 'Unknow';
    }
    // Make case insensitive.
    $t = strtolower($user_agent);

    // If the string *starts* with the string, strpos returns 0 (i.e., FALSE). Do a ghetto hack and start with a space.
    // "[strpos()] may return Boolean FALSE, but may also return a non-Boolean value which evaluates to FALSE."
    //     http://php.net/manual/en/function.strpos.php
    $t = " " . $t;

    // Humans / Regular Users
    if (isAVideoStreamer($t)) {
        return 'AVideo Mobile App';
    } elseif ($url = isAVideoEncoder($t)) {
        return 'AVideo Encoder ' . $url;
    } elseif ($url = isAVideoStreamer($t)) {
        return 'AVideo Streamer ' . $url;
    } elseif (strpos($t, 'crkey')) {
        return 'Chromecast';
    } elseif (strpos($t, 'opera') || strpos($t, 'opr/')) {
        return 'Opera';
    } elseif (strpos($t, 'edge')) {
        return 'Edge';
    } elseif (strpos($t, 'chrome')) {
        return 'Chrome';
    } elseif (strpos($t, 'safari')) {
        return 'Safari';
    } elseif (strpos($t, 'firefox')) {
        return 'Firefox';
    } elseif (strpos($t, 'msie') || strpos($t, 'trident/7')) {
        return 'Internet Explorer';
    } elseif (strpos($t, 'applecoremedia')) {
        return 'Native Apple Player';
    }

    // Search Engines
    elseif (strpos($t, 'google')) {
        return '[Bot] Googlebot';
    } elseif (strpos($t, 'bing')) {
        return '[Bot] Bingbot';
    } elseif (strpos($t, 'slurp')) {
        return '[Bot] Yahoo! Slurp';
    } elseif (strpos($t, 'duckduckgo')) {
        return '[Bot] DuckDuckBot';
    } elseif (strpos($t, 'baidu')) {
        return '[Bot] Baidu';
    } elseif (strpos($t, 'yandex')) {
        return '[Bot] Yandex';
    } elseif (strpos($t, 'sogou')) {
        return '[Bot] Sogou';
    } elseif (strpos($t, 'exabot')) {
        return '[Bot] Exabot';
    } elseif (strpos($t, 'msn')) {
        return '[Bot] MSN';
    }

    // Common Tools and Bots
    elseif (strpos($t, 'mj12bot')) {
        return '[Bot] Majestic';
    } elseif (strpos($t, 'ahrefs')) {
        return '[Bot] Ahrefs';
    } elseif (strpos($t, 'semrush')) {
        return '[Bot] SEMRush';
    } elseif (strpos($t, 'rogerbot') || strpos($t, 'dotbot')) {
        return '[Bot] Moz or OpenSiteExplorer';
    } elseif (strpos($t, 'frog') || strpos($t, 'screaming')) {
        return '[Bot] Screaming Frog';
    }

    // Miscellaneous
    elseif (strpos($t, 'facebook')) {
        return '[Bot] Facebook';
    } elseif (strpos($t, 'pinterest')) {
        return '[Bot] Pinterest';
    }

    // Check for strings commonly used in bot user agents
    elseif (strpos($t, 'crawler') || strpos($t, 'api') ||
            strpos($t, 'spider') || strpos($t, 'http') ||
            strpos($t, 'bot') || strpos($t, 'archive') ||
            strpos($t, 'info') || strpos($t, 'data')) {
        return '[Bot] Other';
    }
    //_error_log("Unknow user agent ($t) IP=" . getRealIpAddr() . " URI=" . getRequestURI());
    return 'Other (Unknown)';
}

/**
 * Due some error on old chrome browsers (version < 70) on decrypt HLS keys with the videojs versions greater then 7.9.7
 * we need to detect the chrome browser and load an older version
 *
 */
function isOldChromeVersion() {
    $global;
    if (empty($_SERVER['HTTP_USER_AGENT'])) {
        return false;
    }
    if (!empty($global['forceOldChrome'])) {
        return true;
    }
    if (preg_match('/Chrome\/([0-9.]+)/i', $_SERVER['HTTP_USER_AGENT'], $matches)) {
        return version_compare($matches[1], '80', '<=');
    }
    return false;
}

function TimeLogStart($name) {
    global $global;
    if (!empty($global['noDebug'])) {
        return false;
    }
    $time = microtime();
    $time = explode(' ', $time);
    $time = $time[1] + $time[0];
    if (empty($global['start']) || !is_array($global['start'])) {
        $global['start'] = [];
    }
    $global['start'][$name] = $time;
    return $name;
}

function TimeLogEnd($name, $line, $TimeLogLimit = 0.7) {
    global $global;
    if (!empty($global['noDebug']) || empty($global['start'][$name])) {
        return false;
    }
    if (!empty($global['TimeLogLimit'])) {
        $TimeLogLimit = $global['TimeLogLimit'];
    }
    $time = microtime();
    $time = explode(' ', $time);
    $time = $time[1] + $time[0];
    $finish = $time;
    $total_time = round(($finish - $global['start'][$name]), 4);
    if (empty($global['noDebugSlowProcess']) && $total_time > $TimeLogLimit) {
        _error_log("Warning: Slow process detected [{$name}] On  Line {$line} takes {$total_time} seconds to complete, Limit ({$TimeLogLimit}). {$_SERVER["SCRIPT_FILENAME"]}");
    }
    TimeLogStart($name);
}

class AVideoLog {

    public static $DEBUG = 0;
    public static $WARNING = 1;
    public static $ERROR = 2;
    public static $SECURITY = 3;
    public static $SOCKET = 4;

}

function _error_log_debug($message, $show_args = false) {
    $array = debug_backtrace();
    $message .= PHP_EOL;
    foreach ($array as $value) {
        $message .= "function: {$value['function']} Line: {{$value['line']}} File: {{$value['file']}}" . PHP_EOL;
        if ($show_args) {
            $message .= print_r($value['args'], true) . PHP_EOL;
        }
    }
    _error_log(PHP_EOL . '***' . PHP_EOL . $message . '***');
}

function _error_log($message, $type = 0, $doNotRepeat = false) {
    if (empty($doNotRepeat)) {
        // do not log it too many times when you are using HLS format, other wise it will fill the log file with the same error
        $doNotRepeat = preg_match("/hls.php$/", $_SERVER['SCRIPT_NAME']);
    }
    if ($doNotRepeat) {
        return false;
    }
    global $global;
    if (!empty($global['noDebug']) && $type == 0) {
        return false;
    }
    if (!is_string($message)) {
        $message = json_encode($message);
    }
    $prefix = "AVideoLog::";
    switch ($type) {
        case AVideoLog::$DEBUG:
            $prefix .= "DEBUG: ";
            break;
        case AVideoLog::$WARNING:
            $prefix .= "WARNING: ";
            break;
        case AVideoLog::$ERROR:
            $prefix .= "ERROR: ";
            break;
        case AVideoLog::$SECURITY:
            $prefix .= "SECURITY: ";
            break;
        case AVideoLog::$SOCKET:
            $prefix .= "SOCKET: ";
            break;
    }
    $str = $prefix . $message . " SCRIPT_NAME: {$_SERVER['SCRIPT_NAME']}";
    if (isCommandLineInterface() && empty($global['doNotPrintLogs'])) {
        echo '[' . date('Y-m-d H:i:s') . '] ' . $str . PHP_EOL;
    }
    error_log($str);
}

function postVariables($url, $array, $httpcodeOnly = true, $timeout = 10) {
    if (!$url || !is_string($url) || !preg_match('/^http(s)?:\/\/[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(\/.*)?$/i', $url)) {
        return false;
    }
    $array = object_to_array($array);
    $ch = curl_init($url);
    if ($httpcodeOnly) {
        @curl_setopt($ch, CURLOPT_HEADER, true);  // we want headers
        @curl_setopt($ch, CURLOPT_NOBODY, true);  // we don't need body
    } else {
        curl_setopt($ch, CURLOPT_USERAGENT, getSelfUserAgent());
    }
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout); //The number of seconds to wait while trying to connect. Use 0 to wait indefinitely.
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout + 1); //The maximum number of seconds to allow cURL functions to execute.
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $array);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

    // execute!
    $response = curl_exec($ch);
    if ($httpcodeOnly) {
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // close the connection, release resources used
        curl_close($ch);
        if ($httpcode == 200) {
            return true;
        }
        return $httpcode;
    } else {
        curl_close($ch);
        return $response;
    }
}

function _session_start(array $options = []) {
    try {
        if (!empty($_GET['PHPSESSID'])) {
            if (!User::isLogged()) {
                if ($_GET['PHPSESSID'] !== session_id()) {
                    if (session_status() !== PHP_SESSION_NONE) {
                        @session_write_close();
                    }
                    session_id($_GET['PHPSESSID']);
                    _error_log("captcha: session_id changed to " . $_GET['PHPSESSID']);
                }
                unset($_GET['PHPSESSID']);
                $session = @session_start($options);

                if (preg_match('/objects\/getCaptcha\.php/i', $_SERVER['SCRIPT_NAME'])) {
                    $regenerateSessionId = false;
                }
                if (!blackListRegenerateSession()) {
                    _error_log("captcha: session_id regenerated new  session_id=" . session_id());
                    session_regenerate_id(true);
                }
                return $session;
            } else {
                //_error_log("captcha: user logged we will not change the session ID PHPSESSID=" . $_GET['PHPSESSID'] . " session_id=" . session_id());
            }
        } elseif (session_status() == PHP_SESSION_NONE) {
            return @session_start($options);
        }
    } catch (Exception $exc) {
        _error_log("_session_start: " . $exc->getTraceAsString());
        return false;
    }
}

/**
 * we will not regenerate the session on this page
 * this is necessary because of the signup from the iframe pages
 * @return boolean
 */
function blackListRegenerateSession() {
    if (!requestComesFromSafePlace()) {
        return false;
    }
    $list = array(
        'objects/getCaptcha.php',
        'objects/userCreate.json.php',
    );
    foreach ($list as $needle) {
        if (str_ends_with($_SERVER['SCRIPT_NAME'], $needle)) {
            return true;
        }
    }
    return false;
}

function _mysql_connect($persistent = false) {
    global $global, $mysqlHost, $mysqlUser, $mysqlPass, $mysqlDatabase, $mysqlPort, $mysql_connect_was_closed;

    $checkValues = array('mysqlHost', 'mysqlUser', 'mysqlPass', 'mysqlDatabase');

    foreach ($checkValues as $value) {
        if (!isset($$value)) {
            _error_log("_mysql_connect Variable NOT set $value");
        }
    }

    try {
        if (!_mysql_is_open()) {
            //_error_log('MySQL Connect '. json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)));
            $mysql_connect_was_closed = 0;
            $global['mysqli'] = new mysqli(($persistent ? 'p:' : '') . $mysqlHost, $mysqlUser, $mysqlPass, '', @$mysqlPort);
            if (isCommandLineInterface() && !empty($global['createDatabase'])) {
                $createSQL = "CREATE DATABASE IF NOT EXISTS {$mysqlDatabase};";
                _error_log($createSQL);
                $global['mysqli']->query($createSQL);
            }
            $global['mysqli']->select_db($mysqlDatabase);
            if (!empty($global['mysqli_charset'])) {
                $global['mysqli']->set_charset($global['mysqli_charset']);
            }
            if (isCommandLineInterface()) {
                _error_log("_mysql_connect HOST=$mysqlHost,DB=$mysqlDatabase");
            }
        }
    } catch (Exception $exc) {
        _error_log($exc->getTraceAsString());
        include $global['systemRootPath'] . 'view/include/offlinePage.php';
        exit;
        return false;
    }
}

function _mysql_close() {
    global $global, $mysql_connect_was_closed;
    if (_mysql_is_open()) {
        //_error_log('MySQL Closed '. json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)));
        $mysql_connect_was_closed = 1;
        @$global['mysqli']->close();
        $global['mysqli'] = false;
    }
}

function _mysql_is_open() {
    global $global, $mysql_connect_was_closed;
    try {
        //if (is_object($global['mysqli']) && (empty($mysql_connect_was_closed) || !empty(@$global['mysqli']->ping()))) {
        if (!empty($global['mysqli']) && is_object($global['mysqli']) && empty($mysql_connect_was_closed)) {
            return true;
        }
    } catch (Exception $exc) {
        return false;
    }
    return false;
}

function remove_utf8_bom($text) {
    if (strlen($text) > 1000000) {
        return $text;
    }

    $bom = pack('H*', 'EFBBBF');
    $text = preg_replace("/^$bom/", '', $text);
    return $text;
}

function getCacheDir() {
    $p = AVideoPlugin::loadPlugin("Cache");
    return $p->getCacheDir();
}

function clearCache($firstPageOnly = false) {
    global $global;
    $lockFile = getVideosDir() . '.clearCache.lock';
    if (file_exists($lockFile) && filectime($lockFile) > strtotime('-5 minutes')) {
        _error_log('clearCache is in progress ' . json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)));
        return false;
    }
    $start = microtime(true);
    _error_log('clearCache starts ');
    file_put_contents($lockFile, time());

    $dir = getVideosDir() . "cache" . DIRECTORY_SEPARATOR;
    $tmpDir = ObjectYPT::getCacheDir('firstPage');
    $parts = explode('firstpage', $tmpDir);

    if ($firstPageOnly || !empty($_REQUEST['FirstPage'])) {
        $tmpDir = $parts[0] . 'firstpage' . DIRECTORY_SEPARATOR;
        //var_dump($tmpDir);exit;
        $dir .= "firstPage" . DIRECTORY_SEPARATOR;
    } else {
        $tmpDir = $parts[0];
    }

    //_error_log('clearCache 1: '.$dir);
    rrmdir($dir);
    rrmdir($tmpDir);
    ObjectYPT::deleteCache("getEncoderURL");
    ObjectYPT::deleteAllSessionCache();
    unlink($lockFile);
    $end = microtime(true) - $start;
    _error_log("clearCache end in {$end} seconds");
    return true;
}

function clearAllUsersSessionCache() {
    sendSocketMessageToAll(time(), 'socketClearSessionCache');
}

function clearFirstPageCache() {
    return clearCache(true);
}

function getUsageFromFilename($filename, $dir = "") {
    global $global;

    if (!empty($global['getUsageFromFilename'])) { // manually add this variable in your configuration.php file to not scan your video usage
        return 0;
    }

    if (empty($dir)) {
        $paths = Video::getPaths($filename);
        $dir = $paths['path'];
    }
    $dir = addLastSlash($dir);
    $totalSize = 0;
    //_error_log("getUsageFromFilename: start {$dir}{$filename} " . json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)));
    //$files = glob("{$dir}{$filename}*");
    $paths = Video::getPaths($filename);

    if (is_dir($paths['path'])) {
        $files = [$paths['path']];
    } else {
        $files = globVideosDir($filename);
    }
    //var_dump($paths, $files, $filename);exit;
    session_write_close();
    $filesProcessed = [];
    if (empty($files)) {
        //_error_log("getUsageFromFilename: we did not find any file for {$dir}{$filename}, we will create a fake one " . json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)));
        make_path($dir);
        file_put_contents("{$dir}{$filename}.notfound", time());
        $totalSize = 10;
    } else {
        foreach ($files as $f) {
            if (strpos($f, '.size.lock') !== false) {
                continue;
            }
            if (is_dir($f)) {
                $dirSize = getDirSize($f, true);
                //_error_log("getUsageFromFilename: is Dir dirSize={$dirSize} " . humanFileSize($dirSize) . " {$f}");
                $totalSize += $dirSize;
                $minDirSize = 4000000;
                $isEnabled = AVideoPlugin::isEnabledByName('YPTStorage');
                $isEnabledCDN = AVideoPlugin::getObjectDataIfEnabled('CDN');
                $isEnabledS3 = AVideoPlugin::loadPluginIfEnabled('AWS_S3');
                if (!empty($isEnabledCDN) && $isEnabledCDN->enable_storage) {
                    $v = Video::getVideoFromFileName($filename);
                    if (!empty($v)) {
                        $size = CDNStorage::getRemoteDirectorySize($v['id']);
                        //_error_log("getUsageFromFilename: CDNStorage found $size " . humanFileSize($size));
                        $totalSize += $size;
                    }
                }
                if ($dirSize < $minDirSize && $isEnabled) {
                    // probably the HLS file is hosted on the YPTStorage
                    $info = YPTStorage::getFileInfo($filename);
                    if (!empty($info->size)) {
                        //_error_log("getUsageFromFilename: found info on the YPTStorage " . print_r($info, true));
                        $totalSize += $info->size;
                    } else {
                        //_error_log("getUsageFromFilename: there is no info on the YPTStorage " . print_r($info, true));
                    }
                } elseif ($dirSize < $minDirSize && $isEnabledS3) {
                    // probably the HLS file is hosted on the S3
                    $size = $isEnabledS3->getFilesize($filename);
                    if (!empty($size)) {
                        //_error_log("getUsageFromFilename: found info on the AWS_S3 {$filename} {$size}");
                        $totalSize += $size;
                    } else {
                        //_error_log("getUsageFromFilename: there is no info on the AWS_S3  {$filename} {$size}");
                    }
                } else {
                    if (!($dirSize < $minDirSize)) {
                        //_error_log("getUsageFromFilename: does not have the size to process $dirSize < $minDirSize");
                    }
                    if (!$isEnabled) {
                        //_error_log("getUsageFromFilename: YPTStorage is disabled");
                    }
                    if (!$isEnabledCDN) {
                        //_error_log("getUsageFromFilename: CDN Storage is disabled");
                    }
                    if (!$isEnabledS3) {
                        //_error_log("getUsageFromFilename: S3 Storage is disabled");
                    }
                }
            } elseif (is_file($f)) {
                $filesize = filesize($f);
                if ($filesize < 20) { // that means it is a dummy file
                    $lockFile = $f . ".size.lock";
                    if (!file_exists($lockFile) || (time() - 600) > filemtime($lockFile)) {
                        file_put_contents($lockFile, time());
                        //_error_log("getUsageFromFilename: {$f} is Dummy file ({$filesize})");
                        $aws_s3 = AVideoPlugin::loadPluginIfEnabled('AWS_S3');
                        //$bb_b2 = AVideoPlugin::loadPluginIfEnabled('Blackblaze_B2');
                        if (!empty($aws_s3)) {
                            //_error_log("getUsageFromFilename: Get from S3");
                            $filesize += $aws_s3->getFilesize($filename);
                        } elseif (!empty($bb_b2)) {
                            // TODO
                        } else {
                            $urls = Video::getVideosPaths($filename, true);
                            //_error_log("getUsageFromFilename: Paths " . json_encode($urls));
                            if (!empty($urls["m3u8"]['url'])) {
                                $filesize += getUsageFromURL($urls["m3u8"]['url']);
                            }
                            if (!empty($urls['mp4'])) {
                                foreach ($urls['mp4'] as $mp4) {
                                    if (in_array($mp4, $filesProcessed)) {
                                        continue;
                                    }
                                    $filesProcessed[] = $mp4;
                                    $filesize += getUsageFromURL($mp4);
                                }
                            }
                            if (!empty($urls['webm'])) {
                                foreach ($urls['webm'] as $mp4) {
                                    if (in_array($mp4, $filesProcessed)) {
                                        continue;
                                    }
                                    $filesProcessed[] = $mp4;
                                    $filesize += getUsageFromURL($mp4);
                                }
                            }
                            if (!empty($urls["pdf"]['url'])) {
                                $filesize += getUsageFromURL($urls["pdf"]['url']);
                            }
                            if (!empty($urls["image"]['url'])) {
                                $filesize += getUsageFromURL($urls["image"]['url']);
                            }
                            if (!empty($urls["zip"]['url'])) {
                                $filesize += getUsageFromURL($urls["zip"]['url']);
                            }
                            if (!empty($urls["mp3"]['url'])) {
                                $filesize += getUsageFromURL($urls["mp3"]['url']);
                            }
                        }
                        unlink($lockFile);
                    }
                } else {
                    //_error_log("getUsageFromFilename: {$f} is File ({$filesize})");
                }
                $totalSize += $filesize;
            }
        }
    }
    return $totalSize;
}

/**
 * Returns the size of a file without downloading it, or -1 if the file
 * size could not be determined.
 *
 * @param $url - The location of the remote file to download. Cannot
 * be null or empty.
 *
 * @return The size of the file referenced by $url, or false if the size
 * could not be determined.
 */
function getUsageFromURL($url) {
    global $global;

    if (!empty($global['doNotGetUsageFromURL'])) { // manually add this variable in your configuration.php file to not scan your video usage
        return 0;
    }

    _error_log("getUsageFromURL: start ({$url})");
    // Assume failure.
    $result = false;

    $curl = curl_init($url);

    _error_log("getUsageFromURL: curl_init ");

    try {
        // Issue a HEAD request and follow any redirects.
        curl_setopt($curl, CURLOPT_NOBODY, true);
        curl_setopt($curl, CURLOPT_HEADER, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        //curl_setopt($curl, CURLOPT_USERAGENT, get_user_agent_string());
        $data = curl_exec($curl);
    } catch (Exception $exc) {
        echo $exc->getTraceAsString();
        _error_log("getUsageFromURL: ERROR " . $exc->getMessage());
        _error_log("getUsageFromURL: ERROR " . curl_errno($curl));
        _error_log("getUsageFromURL: ERROR " . curl_error($curl));
    }

    if ($data) {
        _error_log("getUsageFromURL: response header " . $data);
        $content_length = "unknown";
        $status = "unknown";

        if (preg_match("/^HTTP\/1\.[01] (\d\d\d)/", $data, $matches)) {
            $status = (int) $matches[1];
        }

        if (preg_match("/Content-Length: (\d+)/", $data, $matches)) {
            $content_length = (int) $matches[1];
        }

        // http://en.wikipedia.org/wiki/List_of_HTTP_status_codes
        if ($status == 200 || ($status > 300 && $status <= 308)) {
            $result = $content_length;
        }
    } else {
        _error_log("getUsageFromURL: ERROR no response data " . curl_error($curl));
    }

    curl_close($curl);
    return (int) $result;
}

function getDirSize($dir, $forceNew = false) {
    global $_getDirSize;

    if (!isset($_getDirSize)) {
        $_getDirSize = [];
    }
    if (empty($forceNew) && isset($_getDirSize[$dir])) {
        return $_getDirSize[$dir];
    }

    _error_log("getDirSize: start {$dir}");

    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        $return = foldersize($dir);
        $_getDirSize[$dir] = $return;
        return $return;
    } else {
        $command = "du -sb {$dir}";
        exec($command . " < /dev/null 2>&1", $output, $return_val);
        if ($return_val !== 0) {
            _error_log("getDirSize: ERROR ON Command {$command}");
            $return = 0;
            $_getDirSize[$dir] = $return;
            return $return;
        } else {
            if (!empty($output[0])) {
                preg_match("/^([0-9]+).*/", $output[0], $matches);
            }
            if (!empty($matches[1])) {
                _error_log("getDirSize: found {$matches[1]} from - {$output[0]}");
                $return = intval($matches[1]);
                $_getDirSize[$dir] = $return;
                return $return;
            }

            _error_log("getDirSize: ERROR on pregmatch {$output[0]}");
            $return = 0;
            $_getDirSize[$dir] = $return;
            return $return;
        }
    }
}

function foldersize($path) {
    $total_size = 0;
    $files = scandir($path);
    $cleanPath = rtrim($path, '/') . '/';

    foreach ($files as $t) {
        if ($t <> "." && $t <> "..") {
            $currentFile = $cleanPath . $t;
            if (is_dir($currentFile)) {
                $size = foldersize($currentFile);
                $total_size += $size;
            } else {
                $size = filesize($currentFile);
                $total_size += $size;
            }
        }
    }

    return $total_size;
}

function getDiskUsage() {
    global $global;
    $dir = getVideosDir() . "";
    $obj = new stdClass();
    $obj->disk_free_space = disk_free_space($dir);
    $obj->disk_total_space = disk_total_space($dir);
    $obj->videos_dir = getDirSize($dir);
    $obj->disk_used = $obj->disk_total_space - $obj->disk_free_space;
    $obj->disk_used_by_other = $obj->disk_used - $obj->videos_dir;
    $obj->disk_free_space_human = humanFileSize($obj->disk_free_space);
    $obj->disk_total_space_human = humanFileSize($obj->disk_total_space);
    $obj->videos_dir_human = humanFileSize($obj->videos_dir);
    $obj->disk_used_human = humanFileSize($obj->disk_used);
    $obj->disk_used_by_other_human = humanFileSize($obj->disk_used_by_other);
    // percentage of disk used
    $obj->disk_used_percentage = sprintf('%.2f', ($obj->disk_used / $obj->disk_total_space) * 100);
    $obj->videos_dir_used_percentage = sprintf('%.2f', ($obj->videos_dir / $obj->disk_total_space) * 100);
    $obj->disk_free_space_percentage = sprintf('%.2f', ($obj->disk_free_space / $obj->disk_total_space) * 100);

    return $obj;
}

function unsetSearch() {
    unset($_GET['searchPhrase'], $_POST['searchPhrase'], $_GET['search'], $_GET['q']);
}

function encrypt_decrypt($string, $action) {
    global $global;
    $output = false;

    $encrypt_method = "AES-256-CBC";
    $secret_key = 'This is my secret key';
    $secret_iv = $global['systemRootPath'];
    while (strlen($secret_iv) < 16) {
        $secret_iv .= $global['systemRootPath'];
    }
    if (empty($secret_iv)) {
        $secret_iv = '1234567890abcdef';
    }
    // hash
    $key = hash('sha256', $global['salt']);

    // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
    $iv = substr(hash('sha256', $secret_iv), 0, 16);

    if ($action == 'encrypt') {
        $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
        $output = base64_encode($output);
    } elseif ($action == 'decrypt') {
        $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
    }

    return $output;
}

function compressString($string) {
    if (function_exists("gzdeflate")) {
        $string = gzdeflate($string, 9);
    }
    return $string;
}

function decompressString($string) {
    if (function_exists("gzinflate")) {
        $string = gzinflate($string);
    }
    return $string;
}

function encryptString($string) {
    if (is_object($string)) {
        $string = json_encode($string);
    }
    return encrypt_decrypt($string, 'encrypt');
}

function decryptString($string) {
    return encrypt_decrypt($string, 'decrypt');
}

function getToken($timeout = 0, $salt = "") {
    global $global;
    $obj = new stdClass();
    $obj->salt = $global['salt'] . $salt;
    $obj->timezone = date_default_timezone_get();

    if (!empty($timeout)) {
        $obj->time = time();
        $obj->timeout = $obj->time + $timeout;
    } else {
        $obj->time = strtotime("Today 00:00:00");
        $obj->timeout = strtotime("Today 23:59:59");
        $obj->timeout += cacheExpirationTime();
    }
    $strObj = json_encode($obj);
    //_error_log("Token created: {$strObj}");

    return encryptString($strObj);
}

function isTokenValid($token, $salt = "") {
    return verifyToken($token, $salt);
}

function verifyToken($token, $salt = "") {
    global $global;
    $obj = _json_decode(decryptString($token));
    if (empty($obj)) {
        _error_log("verifyToken invalid token");
        return false;
    }
    if ($obj->salt !== $global['salt'] . $salt) {
        _error_log("verifyToken salt fail");
        return false;
    }
    $old_timezone = date_default_timezone_get();
    date_default_timezone_set($obj->timezone);
    $time = time();
    date_default_timezone_set($old_timezone);
    if (!($time >= $obj->time && $time <= $obj->timeout)) {
        _error_log("verifyToken token timout time = $time; obj->time = $obj->time;  obj->timeout = $obj->timeout");
        return false;
    }
    return true;
}

class YPTvideoObject {

    public $id;
    public $title;
    public $description;
    public $thumbnails;
    public $channelTitle;
    public $videoLink;

    public function __construct($id, $title, $description, $thumbnails, $channelTitle, $videoLink) {
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->thumbnails = $thumbnails;
        $this->channelTitle = $channelTitle;
        $this->videoLink = $videoLink;
    }

}

function isToShowDuration($type) {
    $notShowTo = ['pdf', 'article', 'serie', 'zip', 'image', 'live', 'livelinks'];
    if (in_array($type, $notShowTo)) {
        return false;
    } else {
        return true;
    }
}

function _dieAndLogObject($obj, $prefix = "") {
    $objString = json_encode($obj);
    _error_log($prefix . $objString);
    die($objString);
}

function isAVideoPlayer() {
    if (isVideo() || isSerie()) {
        return true;
    }
    return false;
}

function isFirstPage() {
    global $isFirstPage, $global;
    return !empty($isFirstPage) || getSelfURI() === "{$global['webSiteRootURL']}view/";
}

function isVideo() {
    global $isModeYouTube, $global;
    if (!empty($global['doNotLoadPlayer'])) {
        return false;
    }
    return !empty($isModeYouTube) || isPlayList() || isEmbed() || isLive();
}

function isOffline() {
    global $_isOffline;
    return !empty($_isOffline);
}

function isVideoTypeEmbed() {
    global $isVideoTypeEmbed;

    if (isVideo() && !empty($isVideoTypeEmbed) && $videos_id = getVideos_id()) {
        return $videos_id;
    }

    return false;
}

function isAudio() {
    global $isAudio;
    return !empty($isAudio);
}

function isSerie() {
    return isPlayList();
}

function isPlayList() {
    global $isPlayList, $isSerie;
    return !empty($isSerie) || !empty($isPlayList);
}

function isChannel() {
    global $isChannel;
    if (!empty($isChannel) && !isVideo()) {
        $user_id = 0;
        if (empty($_GET['channelName'])) {
            if (User::isLogged()) {
                $user_id = User::getId();
            } else {
                return false;
            }
        } else {
            $_GET['channelName'] = xss_esc($_GET['channelName']);
            $user = User::getChannelOwner($_GET['channelName']);
            if (!empty($user)) {
                $user_id = $user['id'];
            } else {
                $user_id = $_GET['channelName'];
            }
        }
        return $user_id;
    }
    return false;
}

function isEmbed() {
    global $isEmbed, $global;
    if (!empty($global['doNotLoadPlayer'])) {
        return false;
    }
    return !empty($isEmbed);
}

function isWebRTC() {
    global $isWebRTC, $global;
    if (!empty($global['doNotLoadPlayer'])) {
        return false;
    }
    return !empty($isWebRTC);
}

function isLive() {
    global $isLive, $global;
    if (!empty($global['doNotLoadPlayer'])) {
        return false;
    }
    if (class_exists('LiveTransmition') && class_exists('Live')) {
        $livet = LiveTransmition::getFromRequest();
        if (!empty($livet)) {
            setLiveKey($livet['key'], Live::getLiveServersIdRequest(), @$_REQUEST['live_index']);
            $isLive = 1;
        }
    }
    if (!empty($isLive)) {
        $live = getLiveKey();
        if (empty($live)) {
            $live = ['key' => false, 'live_servers_id' => false, 'live_index' => false, 'live_schedule' => false, 'users_id' => false];
        }
        $live['liveLink'] = isLiveLink();
        return $live;
    } else {
        return false;
    }
}

function isLiveLink() {
    global $isLiveLink;
    if (!empty($isLiveLink)) {
        return $isLiveLink;
    } else {
        return false;
    }
}

function getLiveKey() {
    global $getLiveKey;
    if (empty($getLiveKey)) {
        return false;
    }
    return $getLiveKey;
}

function setLiveKey($key, $live_servers_id, $live_index = '') {
    global $getLiveKey;
    $parameters = Live::getLiveParametersFromKey($key);
    $key = $parameters['key'];
    $cleanKey = $parameters['cleanKey'];
    if (empty($live_index)) {
        $live_index = $parameters['live_index'];
    }
    $key = Live::getLiveKeyFromRequest($key, $live_index, $parameters['playlists_id_live']);
    $lt = LiveTransmition::getFromKey($key);
    $live_schedule = 0;
    $users_id = 0;
    if (!empty($lt['live_schedule_id'])) {
        $live_schedule = $lt['live_schedule_id'];
        $live_servers_id = $lt['live_servers_id'];
        $users_id = $lt['users_id'];
    }
    $getLiveKey = ['key' => $key, 'live_servers_id' => intval($live_servers_id), 'live_index' => $live_index, 'cleanKey' => $cleanKey, 'live_schedule' => $live_schedule, 'users_id' => $users_id];
    return $getLiveKey;
}

function isVideoPlayerHasProgressBar() {
    if (isWebRTC()) {
        return false;
    }
    if (isLive()) {
        $obj = AVideoPlugin::getObjectData('Live');
        if (empty($obj->disableDVR)) {
            return true;
        }
    } elseif (isAVideoPlayer()) {
        return true;
    }
    return false;
}

function isHLS() {
    global $video, $global;
    if (isLive()) {
        return true;
    } elseif (!empty($video) && $video['type'] == 'video' && file_exists(Video::getPathToFile("{$video['filename']}/index.m3u8"))) {
        return true;
    }
    return false;
}

function getRedirectUri($returnThisIfRedirectUriIsNotSet = false) {
    if (isValidURL(@$_GET['redirectUri'])) {
        return $_GET['redirectUri'];
    }
    if (isValidURL(@$_SESSION['redirectUri'])) {
        return $_SESSION['redirectUri'];
    }
    if (isValidURL(@$_REQUEST["redirectUri"])) {
        return $_REQUEST["redirectUri"];
    }
    if (isValidURL(@$_SERVER["HTTP_REFERER"])) {
        return $_SERVER["HTTP_REFERER"];
    }
    if (isValidURL($returnThisIfRedirectUriIsNotSet)) {
        return $returnThisIfRedirectUriIsNotSet;
    } else {
        return getRequestURI();
    }
}

function setRedirectUri($redirectUri) {
    _session_start();
    $_SESSION['redirectUri'] = $redirectUri;
}

function redirectIfRedirectUriIsSet() {
    $redirectUri = false;
    if (!empty($_GET['redirectUri'])) {
        if (isSameDomainAsMyAVideo($_GET['redirectUri'])) {
            $redirectUri = $_GET['redirectUri'];
        }
    }
    if (!empty($_SESSION['redirectUri'])) {
        if (isSameDomainAsMyAVideo($_SESSION['redirectUri'])) {
            $redirectUri = $_SESSION['redirectUri'];
        }
        _session_start();
        unset($_SESSION['redirectUri']);
    }

    if (!empty($redirectUri)) {
        header("Location: {$_SESSION['redirectUri']}");
        exit;
    }
}

function getRedirectToVideo($videos_id) {
    $redirectUri = getRedirectUri();
    $isEmbed = 0;
    if (stripos($redirectUri, "embed") !== false) {
        $isEmbed = 1;
    }
    $video = Video::getVideoLight($videos_id);
    if (empty($video)) {
        return false;
    }
    return Video::getLink($videos_id, $video['clean_title'], $isEmbed);
}

function getRequestURI() {
    if (empty($_SERVER['REQUEST_URI'])) {
        return "";
    }
    return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
}

function getSelfURI() {
    if (empty($_SERVER['PHP_SELF']) || empty($_SERVER['HTTP_HOST'])) {
        return "";
    }
    global $global;
    $http = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
    if (preg_match('/^https:/i', $global['webSiteRootURL'])) {
        $http = 'https';
    }

    $queryStringWithoutError = preg_replace("/error=[^&]*/", "", @$_SERVER['QUERY_STRING']);
    $phpselfWithoutIndex = preg_replace("/index.php/", "", @$_SERVER['PHP_SELF']);
    $url = $http . "://$_SERVER[HTTP_HOST]$phpselfWithoutIndex?$queryStringWithoutError";
    $url = rtrim($url, '?');
    return $url;
}

function isSameVideoAsSelfURI($url) {
    return URLsAreSameVideo($url, getSelfURI());
}

function URLsAreSameVideo($url1, $url2) {
    $videos_id1 = getVideoIDFromURL($url1);
    $videos_id2 = getVideoIDFromURL($url2);
    if (empty($videos_id1) || empty($videos_id2)) {
        return false;
    }
    return $videos_id1 === $videos_id2;
}

function getVideos_id() {
    global $_getVideos_id;
    $videos_id = false;
    if (isset($_getVideos_id) && is_int($_getVideos_id)) {
        return $_getVideos_id;
    }
    if (isVideo()) {
        $videos_id = getVideoIDFromURL(getSelfURI());
        if (empty($videos_id) && !empty($_REQUEST['videoName'])) {
            $video = Video::getVideoFromCleanTitle($_REQUEST['videoName']);
            if (!empty($video)) {
                $videos_id = $video['id'];
            }
        }
        setVideos_id($videos_id);
    }
    if (empty($videos_id) && !empty($_REQUEST['playlists_id'])) {
        AVideoPlugin::loadPlugin('PlayLists');
        $video = PlayLists::isPlayListASerie($_REQUEST['playlists_id']);
        if (!empty($video)) {
            $videos_id = $video['id'];
        }
    }

    if (empty($videos_id) && !empty($_REQUEST['v'])) {
        $videos_id = $_REQUEST['v'];
    }

    if (empty($videos_id) && !empty($_REQUEST['videos_id'])) {
        $videos_id = $_REQUEST['videos_id'];
    }
    
    $videos_id = videosHashToID($videos_id);

    return $videos_id;
}

function setVideos_id($videos_id) {
    global $_getVideos_id;
    $_getVideos_id = $videos_id;
}

function getPlaylists_id() {
    global $_isPlayList;
    if (!isset($_isPlayList)) {
        $_isPlayList = false;
        if (isPlayList()) {
            $_isPlayList = intval(@$_GET['playlists_id']);
            if (empty($_isPlayList)) {
                $videos_id = getVideos_id();
                if (empty($videos_id)) {
                    $_isPlayList = false;
                } else {
                    $v = Video::getVideoLight($videos_id);
                    if (empty($v) || empty($v['serie_playlists_id'])) {
                        $_isPlayList = false;
                    } else {
                        $_isPlayList = $v['serie_playlists_id'];
                    }
                }
            }
        }
    }
    return $_isPlayList;
}

function isVideoOrAudioNotEmbed() {
    if (!isVideo()) {
        return false;
    }
    $videos_id = getVideos_id();
    if (empty($videos_id)) {
        return false;
    }
    $v = Video::getVideoLight($videos_id);
    if (empty($v)) {
        return false;
    }
    $types = ['audio', 'video'];
    if (in_array($v['type'], $types)) {
        return true;
    }
    return false;
}

function getVideoIDFromURL($url) {
    if (preg_match("/v=([0-9]+)/", $url, $matches)) {
        return intval($matches[1]);
    }
    if (preg_match('/\/(video|videoEmbed|v|vEmbed|article|articleEmbed)\/([0-9]+)/', $url, $matches)) {
        if (is_numeric($matches[1])) {
            return intval($matches[1]);
        } elseif (is_numeric($matches[2])) {
            return intval($matches[2]);
        }
    }
    if (AVideoPlugin::isEnabledByName('PlayLists')) {
        if (preg_match('/player.php\?playlists_id=([0-9]+)/', $url, $matches)) {
            $serie_playlists_id = intval($matches[1]);
            $video = PlayLists::isPlayListASerie($serie_playlists_id);
            if ($video) {
                return $video['id'];
            }
        }
    }
    if (preg_match("/v=(\.[0-9a-zA-Z_-]+)/", $url, $matches)) {
        return hashToID($matches[1]);
    }
    if (preg_match('/\/(video|videoEmbed|v|vEmbed|article|articleEmbed)\/(\.[0-9a-zA-Z_-]+)/', $url, $matches)) {
        return hashToID($matches[2]);
    }
    return false;
}

function getBackURL() {
    global $global;
    $backURL = getRedirectUri();
    if (empty($backURL)) {
        $backURL = getRequestURI();
    }
    if (isSameVideoAsSelfURI($backURL)) {
        $backURL = getHomeURL();
    }
    return $backURL;
}

function getHomeURL() {
    global $global, $advancedCustomUser, $advancedCustom;
    if (isValidURL($advancedCustomUser->afterLoginGoToURL)) {
        return $advancedCustomUser->afterLoginGoToURL;
    } elseif (isValidURL($advancedCustom->logoMenuBarURL) && isSameDomainAsMyAVideo($advancedCustom->logoMenuBarURL)) {
        return $advancedCustom->logoMenuBarURL;
    }
    return $global['webSiteRootURL'];
}

function isValidURL($url) {
    //var_dump(empty($url), !is_string($url), preg_match("/^http.*/", $url), filter_var($url, FILTER_VALIDATE_URL));
    if (empty($url) || !is_string($url)) {
        return false;
    }
    if (preg_match("/^http.*/", $url) && filter_var($url, FILTER_VALIDATE_URL)) {
        return true;
    }
    return false;
}

function isValidURLOrPath($str, $insideCacheOrTmpDirOnly = true) {
    //var_dump(empty($url), !is_string($url), preg_match("/^http.*/", $url), filter_var($url, FILTER_VALIDATE_URL));
    if (empty($str) || !is_string($str)) {
        return false;
    }
    if (strtolower(trim($str)) === 'php://input') {
        return true;
    }
    if (isValidURL($str)) {
        return true;
    }
    if (str_starts_with($str, '/') || str_starts_with($str, '../') || preg_match("/^[a-z]:.*/i", $str)) {
        if ($insideCacheOrTmpDirOnly) {
            $absolutePath = realpath($str);
            $absolutePathTmp = realpath(getTmpDir());
            $absolutePathCache = realpath($cacheDir);
            $ext = strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION));
            if ($ext == 'php') {
                _error_log('isValidURLOrPath return false (is php file) ' . $str);
                return false;
            }
            if (
                    str_starts_with($absolutePath, $absolutePathTmp) || 
                    str_starts_with($absolutePath, '/var/www/') || 
                    str_starts_with($absolutePath, $absolutePathCache) || 
                    str_starts_with($absolutePath, $global['systemRootPath']) || 
                    str_starts_with($absolutePath, getVideosDir())) {
                return true;
            }
        } else {
            return true;
        }
        //_error_log('isValidURLOrPath return false not valid absolute path 1 ' . $absolutePath);
        //_error_log('isValidURLOrPath return false not valid absolute path 2 ' . $absolutePathTmp);
        //_error_log('isValidURLOrPath return false not valid absolute path 3 ' . $absolutePathCache);
    }
    //_error_log('isValidURLOrPath return false '.$str);
    return false;
}

function hasLastSlash($word) {
    return substr($word, -1) === '/';
}

function addLastSlash($word) {
    return $word . (hasLastSlash($word) ? "" : "/");
}

function URLHasLastSlash() {
    return hasLastSlash($_SERVER["REQUEST_URI"]);
}

function ucname($str) {
    $str = ucwords(strtolower($str));

    foreach (['\'', '-'] as $delim) {
        if (strpos($str, $delim) !== false) {
            $str = implode($delim, array_map('ucfirst', explode($delim, $str)));
        }
    }
    return $str;
}

function sanitize_input($input) {
    return htmlentities(strip_tags($input));
}

function sanitize_array_item(&$item, $key) {
    $item = sanitize_input($item);
}

function getSEOComplement($parameters = []) {
    global $config;

    $allowedTypes = $parameters["allowedTypes"] ?? null;
    $addAutoPrefix = $parameters["addAutoPrefix"] ?? true;
    $addCategory = $parameters["addCategory"] ?? true;

    $parts = [];

    if (!empty($_GET['error'])) {
        array_push($parts, __("Error"));
    }

    if ($addCategory && !empty($_GET['catName'])) {
        array_push($parts, $_GET['catName']);
    }

    if (!empty($_GET['channelName'])) {
        array_push($parts, $_GET['channelName']);
    }

    if (!empty($_GET['type'])) {
        $type = $_GET['type'];
        if (empty($allowedTypes) || in_array(strtolower($type), $allowedTypes)) {
            array_push($parts, __(ucname($type)));
        }
    }

    if (!empty($_GET['showOnly'])) {
        array_push($parts, $_GET['showOnly']);
    }

    if (!empty($_GET['page'])) {
        $page = intval($_GET['page']);
        if ($page > 1) {
            array_push($parts, sprintf(__("Page %d"), $page));
        }
    }

    // Cleaning all entries in the $parts array
    array_walk($parts, 'sanitize_array_item');

    $txt = implode($config->getPageTitleSeparator(), $parts);
    $txt = (!empty($txt) && $addAutoPrefix ? $config->getPageTitleSeparator() : "") . $txt;

    return $txt;
}

function getCurrentPage() {
    if (!empty($_REQUEST['current'])) {
        return intval($_REQUEST['current']);
    } elseif (!empty($_POST['current'])) {
        return intval($_POST['current']);
    } elseif (!empty($_GET['current'])) {
        return intval($_GET['current']);
    } elseif (isset($_GET['start']) && isset($_GET['length'])) { // for the bootgrid
        $start = intval($_GET['start']);
        $length = intval($_GET['length']);
        if (!empty($start) && !empty($length)) {
            return floor($start / $length) + 1;
        }
    }
    return 1;
}

function getRowCount($default = 1000) {
    global $global;
    if (!empty($_REQUEST['rowCount'])) {
        $defaultN = intval($_REQUEST['rowCount']);
    } elseif (!empty($_POST['rowCount'])) {
        $defaultN = intval($_POST['rowCount']);
    } elseif (!empty($_GET['rowCount'])) {
        $defaultN = intval($_GET['rowCount']);
    } elseif (!empty($_REQUEST['length'])) {
        $defaultN = intval($_REQUEST['length']);
    } elseif (!empty($_POST['length'])) {
        $defaultN = intval($_POST['length']);
    } elseif (!empty($_GET['length'])) {
        $defaultN = intval($_GET['length']);
    } elseif (!empty($global['rowCount'])) {
        $defaultN = intval($global['rowCount']);
    }
    return (!empty($defaultN) && $defaultN > 0) ? $defaultN : $default;
}

function getSearchVar() {
    if (!empty($_REQUEST['search'])) {
        return $_REQUEST['search'];
    } elseif (!empty($_REQUEST['q'])) {
        return $_REQUEST['q'];
    }
    if (!empty($_REQUEST['searchPhrase'])) {
        return $_REQUEST['searchPhrase'];
    } elseif (!empty($_REQUEST['search']['value'])) {
        return $_REQUEST['search']['value'];
    }
    return "";
}

$cleanSearchHistory = '';

function cleanSearchVar() {
    global $cleanSearchHistory;
    $search = getSearchVar();
    if (!empty($search)) {
        $cleanSearchHistory = $search;
    }
    $searchIdex = ['q', 'searchPhrase', 'search'];
    foreach ($searchIdex as $value) {
        unset($_REQUEST[$value], $_POST[$value], $_GET[$value]);
    }
}

function reloadSearchVar() {
    global $cleanSearchHistory;
    $_REQUEST['search'] = $cleanSearchHistory;
    if (empty($_GET['search'])) {
        $_GET['search'] = $cleanSearchHistory;
    }
    if (empty($_POST['search'])) {
        $_POST['search'] = $cleanSearchHistory;
    }
}

function wget($url, $filename, $debug = false) {
    if (empty($url) || $url == "php://input" || !isValidURL($url)) {
        return false;
    }
    if ($lockfilename = wgetIsLocked($url)) {
        if ($debug) {
            _error_log("wget: ERROR the url is already downloading {$lockfilename} $url, $filename");
        }
        return false;
    }
    wgetLock($url);
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        $content = @file_get_contents($url);
        if (!empty($content) && file_put_contents($filename, $content) > 100) {
            wgetRemoveLock($url);
            return true;
        }
        wgetRemoveLock($url);
        return false;
    }

    $filename = escapeshellarg($filename);
    $url = escapeshellarg($url);
    $cmd = "wget --tries=1 {$url} -O {$filename} --no-check-certificate";
    if ($debug) {
        _error_log("wget Start ({$cmd}) ");
    }
    //echo $cmd;
    exec($cmd);
    wgetRemoveLock($url);
    if (!file_exists($filename)) {
        _error_log("wget: ERROR the url does not download $url, $filename");
        return false;
    }
    if ($_SERVER['SCRIPT_NAME'] !== '/plugin/Live/m3u8.php' && empty(filesize($filename))) {
        _error_log("wget: ERROR the url download but is empty $url, $filename");
        return true;
    }
    return false;
}

/**
 * Copy remote file over HTTP one small chunk at a time.
 *
 * @param $infile The full URL to the remote file
 * @param $outfile The path where to save the file
 */
function copyfile_chunked($infile, $outfile) {
    $chunksize = 10 * (1024 * 1024); // 10 Megs

    /**
     * parse_url breaks a part a URL into it's parts, i.e. host, path,
     * query string, etc.
     */
    $parts = parse_url($infile);
    $i_handle = fsockopen($parts['host'], 80, $errstr, $errcode, 5);
    $o_handle = fopen($outfile, 'wb');

    if ($i_handle == false || $o_handle == false) {
        return false;
    }

    if (!empty($parts['query'])) {
        $parts['path'] .= '?' . $parts['query'];
    }

    /**
     * Send the request to the server for the file
     */
    $request = "GET {$parts['path']} HTTP/1.1\r\n";
    $request .= "Host: {$parts['host']}\r\n";
    $request .= "User-Agent: Mozilla/5.0\r\n";
    $request .= "Keep-Alive: 115\r\n";
    $request .= "Connection: keep-alive\r\n\r\n";
    fwrite($i_handle, $request);

    /**
     * Now read the headers from the remote server. We'll need
     * to get the content length.
     */
    $headers = [];
    while (!feof($i_handle)) {
        $line = fgets($i_handle);
        if ($line == "\r\n") {
            break;
        }
        $headers[] = $line;
    }

    /**
     * Look for the Content-Length header, and get the size
     * of the remote file.
     */
    $length = 0;
    foreach ($headers as $header) {
        if (stripos($header, 'Content-Length:') === 0) {
            $length = (int) str_replace('Content-Length: ', '', $header);
            break;
        }
    }

    /**
     * Start reading in the remote file, and writing it to the
     * local file one chunk at a time.
     */
    $cnt = 0;
    while (!feof($i_handle)) {
        $buf = '';
        $buf = fread($i_handle, $chunksize);
        $bytes = fwrite($o_handle, $buf);
        if ($bytes == false) {
            return false;
        }
        $cnt += $bytes;

        /**
         * We're done reading when we've reached the conent length
         */
        if ($cnt >= $length) {
            break;
        }
    }

    fclose($i_handle);
    fclose($o_handle);
    return $cnt;
}

function wgetLockFile($url) {
    return getTmpDir("YPTWget") . md5($url) . ".lock";
}

function wgetLock($url) {
    $file = wgetLockFile($url);
    return file_put_contents($file, time() . PHP_EOL, FILE_APPEND | LOCK_EX);
}

function wgetRemoveLock($url) {
    $filename = wgetLockFile($url);
    if (!file_exists($filename)) {
        return false;
    }
    return unlink($filename);
}

function getLockFile($name) {
    return getTmpDir("YPTLockFile") . md5($name) . ".lock";
}

function setLock($name) {
    $file = getLockFile($name);
    return file_put_contents($file, time());
}

function isLock($name, $timeout = 60) {
    $file = getLockFile($name);
    if (file_exists($file)) {
        $time = intval(file_get_contents($file));
        if ($time + $timeout < time()) {
            return false;
        }
    }
}

function removeLock($name) {
    $filename = getLockFile($name);
    if (!file_exists($filename)) {
        return false;
    }
    return unlink($filename);
}

function wgetIsLocked($url) {
    $filename = wgetLockFile($url);
    if (!file_exists($filename)) {
        return false;
    }
    $time = intval(file_get_contents($filename));
    if (time() - $time > 36000) { // more then 10 hours
        unlink($filename);
        return false;
    }
    return $filename;
}

// due the some OS gives a fake is_writable response
function isWritable($dir) {
    $dir = rtrim($dir, '/') . '/';
    $file = $dir . uniqid();
    $result = false;
    $time = time();
    if (@file_put_contents($file, $time)) {
        if ($fileTime = @file_get_contents($file)) {
            if ($fileTime == $time) {
                $result = true;
            }
        }
    }
    @unlink($file);
    return $result;
}

function _isWritable($dir) {
    if (!isWritable($dir)) {
        return false;
    }
    $tmpFile = "{$dir}" . uniqid();
    $bytes = @file_put_contents($tmpFile, time());
    @unlink($tmpFile);
    return !empty($bytes);
}

function getTmpDir($subdir = "") {
    global $global;
    if (empty($_SESSION['getTmpDir'])) {
        $_SESSION['getTmpDir'] = [];
    }
    if (empty($_SESSION['getTmpDir'][$subdir . "_"])) {
        if (empty($global['tmpDir'])) {
            $tmpDir = sys_get_temp_dir();
            if (empty($tmpDir) || !_isWritable($tmpDir)) {
                $tmpDir = getVideosDir() . "cache" . DIRECTORY_SEPARATOR;
            }
            $tmpDir = rtrim($tmpDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
            $tmpDir = "{$tmpDir}{$subdir}";
        } else {
            $tmpDir = $global['tmpDir'];
        }
        $tmpDir = addLastSlash($tmpDir);
        if (!is_dir($tmpDir)) {
            mkdir($tmpDir, 0777, true);
        }
        _session_start();
        $_SESSION['getTmpDir'][$subdir . "_"] = $tmpDir;
    } else {
        $tmpDir = $_SESSION['getTmpDir'][$subdir . "_"];
    }
    return $tmpDir;
}

function getTmpFile() {
    return getTmpDir("tmpFiles") . uniqid();
}

function getMySQLDate() {
    global $global;
    $sql = "SELECT now() as time FROM configurations LIMIT 1";
    // I had to add this because the about from customize plugin was not loading on the about page http://127.0.0.1/AVideo/about
    $res = sqlDAL::readSql($sql);
    $data = sqlDAL::fetchAssoc($res);
    sqlDAL::close($res);
    if ($res) {
        $row = $data['time'];
    } else {
        $row = false;
    }
    return $row;
}

function _file_put_contents($filename, $data, $flags = 0, $context = null) {
    make_path($filename);
    if (!is_string($data)) {
        $data = _json_encode($data);
    }
    return file_put_contents($filename, $data, $flags, $context);
}

function html2plainText($html) {
    $text = strip_tags($html);
    $text = str_replace(['\\', "\n", "\r", '"'], ['', ' ', ' ', ''], trim($text));
    return $text;
}

function getInputPassword($id, $attributes = 'class="form-control"', $paceholder = '') {
    if (empty($paceholder)) {
        $paceholder = __("Password");
    }
    ?>
    <div class="input-group">
        <span class="input-group-addon"><i class="fas fa-lock"></i></span>
        <input id="<?php echo $id; ?>" name="<?php echo $id; ?>" type="password"  placeholder="<?php echo $paceholder; ?>" <?php echo $attributes; ?> >
            <span class="input-group-addon" style="cursor: pointer;" id="toggle_<?php echo $id; ?>"  data-toggle="tooltip" data-placement="left" title="<?php echo __('Show/Hide Password'); ?>"><i class="fas fa-eye-slash"></i></span>
    </div>
    <script>
        $(document).ready(function () {
            $('#toggle_<?php echo $id; ?>').click(function () {
                $(this).find('i').toggleClass("fa-eye fa-eye-slash");
                if ($(this).find('i').hasClass("fa-eye")) {
                    $("#<?php echo $id; ?>").attr("type", "text");
                } else {
                    $("#<?php echo $id; ?>").attr("type", "password");
                }
            })
        });
    </script>
    <?php
}

function getInputCopyToClipboard($id, $value, $attributes = 'class="form-control" readonly="readonly"', $paceholder = '') {
    if (strpos($value, '"') !== false) {
        $valueAttr = "value='{$value}'";
    } else {
        $valueAttr = 'value="' . $value . '"';
    }
    ?>
    <div class="input-group">
        <input id="<?php echo $id; ?>" type="text"  placeholder="<?php echo $paceholder; ?>" <?php echo $attributes; ?> <?php echo $valueAttr; ?> >
            <span class="input-group-addon" style="cursor: pointer;" id="copyToClipboard_<?php echo $id; ?>"  data-toggle="tooltip" data-placement="left" title="<?php echo __('Copy to Clipboard'); ?>"><i class="fas fa-clipboard"></i></span>
    </div>
    <script>
        var timeOutCopyToClipboard_<?php echo $id; ?>;
        $(document).ready(function () {
            $('#copyToClipboard_<?php echo $id; ?>').click(function () {
                clearTimeout(timeOutCopyToClipboard_<?php echo $id; ?>);
                $('#copyToClipboard_<?php echo $id; ?>').find('i').removeClass("fa-clipboard");
                $('#copyToClipboard_<?php echo $id; ?>').find('i').addClass("text-success");
                $('#copyToClipboard_<?php echo $id; ?>').addClass('bg-success');
                $('#copyToClipboard_<?php echo $id; ?>').find('i').addClass("fa-clipboard-check");
                timeOutCopyToClipboard_<?php echo $id; ?> = setTimeout(function () {
                    $('#copyToClipboard_<?php echo $id; ?>').find('i').removeClass("fa-clipboard-check");
                    $('#copyToClipboard_<?php echo $id; ?>').find('i').removeClass("text-success");
                    $('#copyToClipboard_<?php echo $id; ?>').removeClass('bg-success');
                    $('#copyToClipboard_<?php echo $id; ?>').find('i').addClass("fa-clipboard");
                }, 3000);
                copyToClipboard($('#<?php echo $id; ?>').val());
            })
        });
    </script>
    <?php
}

function getButtontCopyToClipboard($elemToCopyId, $attributes = 'class="btn btn-default btn-sm btn-xs pull-right"', $label = "Copy to Clipboard") {
    $id = "getButtontCopyToClipboard" . uniqid();
    ?>
    <button id="<?php echo $id; ?>" <?php echo $attributes; ?> data-toggle="tooltip" data-placement="left" title="<?php echo __($label); ?>"><i class="fas fa-clipboard"></i> <?php echo __($label); ?></button>
    <script>
        var timeOutCopyToClipboard_<?php echo $id; ?>;
        $(document).ready(function () {
            $('#<?php echo $id; ?>').click(function () {
                clearTimeout(timeOutCopyToClipboard_<?php echo $id; ?>);
                $('#<?php echo $id; ?>').find('i').removeClass("fa-clipboard");
                $('#<?php echo $id; ?>').find('i').addClass("text-success");
                $('#<?php echo $id; ?>').addClass('bg-success');
                $('#<?php echo $id; ?>').find('i').addClass("fa-clipboard-check");
                timeOutCopyToClipboard_<?php echo $id; ?> = setTimeout(function () {
                    $('#<?php echo $id; ?>').find('i').removeClass("fa-clipboard-check");
                    $('#<?php echo $id; ?>').find('i').removeClass("text-success");
                    $('#<?php echo $id; ?>').removeClass('bg-success');
                    $('#<?php echo $id; ?>').find('i').addClass("fa-clipboard");
                }, 3000);
                copyToClipboard($('#<?php echo $elemToCopyId; ?>').val());
            })
        });
    </script>
    <?php
    return $id;
}

function fakeBrowser($url) {
    // create curl resource
    $ch = curl_init();

    // set url
    curl_setopt($ch, CURLOPT_URL, $url);

    //return the transfer as a string
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');

    // $output contains the output string
    $output = curl_exec($ch);

    // close curl resource to free up system resources
    curl_close($ch);
    return $output;
}

function examineJSONError($object) {
    $json = json_encode($object);
    if (json_last_error()) {
        echo "Error 1 Found: " . json_last_error_msg() . "<br>" . PHP_EOL;
    } else {
        return __LINE__;
    }
    $object = object_to_array($object);
    $json = json_encode($object);
    if (json_last_error()) {
        echo "Error 1 Found after array conversion: " . json_last_error_msg() . "<br>" . PHP_EOL;
    } else {
        return __LINE__;
    }

    $json = json_encode($object, JSON_UNESCAPED_UNICODE);
    if (json_last_error()) {
        echo "Error 1 Found with JSON_UNESCAPED_UNICODE: " . json_last_error_msg() . "<br>" . PHP_EOL;
    } else {
        return __LINE__;
    }

    $objectEncoded = $object;

    array_walk_recursive($objectEncoded, function (&$item) {
        if (is_string($item)) {
            $item = utf8_encode($item);
        }
    });
    $json = json_encode($objectEncoded);
    if (json_last_error()) {
        echo "Error 2 Found after array conversion: " . json_last_error_msg() . "<br>" . PHP_EOL;
    } else {
        return __LINE__;
    }

    $json = json_encode($objectEncoded, JSON_UNESCAPED_UNICODE);
    if (json_last_error()) {
        echo "Error 2 Found with JSON_UNESCAPED_UNICODE: " . json_last_error_msg() . "<br>" . PHP_EOL;
    } else {
        return __LINE__;
    }

    $objectDecoded = $object;

    array_walk_recursive($objectDecoded, function (&$item) {
        if (is_string($item)) {
            $item = utf8_decode($item);
        }
    });
    $json = json_encode($objectDecoded);
    if (json_last_error()) {
        echo "Error 2 Found after array conversion: " . json_last_error_msg() . "<br>" . PHP_EOL;
    } else {
        return __LINE__;
    }

    $json = json_encode($objectDecoded, JSON_UNESCAPED_UNICODE);
    if (json_last_error()) {
        echo "Error 2 Found with JSON_UNESCAPED_UNICODE: " . json_last_error_msg() . "<br>" . PHP_EOL;
    } else {
        return __LINE__;
    }

    return false;
}

function _json_encode($object) {
    if (empty($object)) {
        return false;
    }
    if (is_string($object)) {
        return $object;
    }
    $json = json_encode($object);
    if (empty($json) && json_last_error()) {
        //_error_log("_json_encode: Error 1 Found: " . json_last_error_msg());
        $object = object_to_array($object);
        $json = json_encode($object);
        if (empty($json) && json_last_error()) {
            //_error_log("_json_encode: Error 2 Found: " . json_last_error_msg());
            $json = json_encode($object, JSON_UNESCAPED_UNICODE);
            if (json_last_error()) {
                _error_log("_json_encode: Error 3 Found: {$object} " . json_last_error_msg() . PHP_EOL . json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)));
                $objectEncoded = $object;
                array_walk_recursive($objectEncoded, function (&$item) {
                    if (is_string($item)) {
                        $item = utf8_encode($item);
                    }
                });
                $json = json_encode($objectEncoded);
                if (empty($json) && json_last_error()) {
                    _error_log("_json_encode: Error 4 Found: " . json_last_error_msg());
                    $json = json_encode($objectEncoded, JSON_UNESCAPED_UNICODE);
                    if (json_last_error()) {
                        _error_log("_json_encode: Error 5 Found: " . json_last_error_msg());
                        $objectDecoded = $object;
                        array_walk_recursive($objectDecoded, function (&$item) {
                            if (is_string($item)) {
                                $item = utf8_decode($item);
                            }
                        });
                        $json = json_encode($objectDecoded);
                        if (empty($json) && json_last_error()) {
                            _error_log("_json_encode: Error 6 Found: " . json_last_error_msg());
                            $json = json_encode($objectDecoded, JSON_UNESCAPED_UNICODE);
                            if (empty($json) && json_last_error()) {
                                _error_log("_json_encode: Error 7 Found: " . json_last_error_msg());
                                array_walk_recursive($objectDecoded, function (&$item) {
                                    if (is_string($item)) {
                                        $item = cleanString($item);
                                    }
                                });
                                $json = json_encode($users);
                                if (json_last_error()) {
                                    _error_log("_json_encode: Error 8 Found: " . json_last_error_msg());
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    return $json;
}

function _json_decode($object) {
    global $global;
    if (empty($object)) {
        return false;
    }
    if (!is_string($object)) {
        return $object;
    }
    if (isValidURLOrPath($object)) {
        $content = file_get_contents($object);
        if (!empty($content)) {
            $object = $content;
        }
    }
    $json = json_decode($object);
    if ($json === null) {
        $object = str_replace(["\r", "\n"], ['\r', '\n'], $object);
        return json_decode($object);
    } else {
        return $json;
    }
}

// this will make sure the strring will fits in the database field
function _substr($string, $start, $length = null) {
    // make sure the name is not chunked in case of multibyte string
    if (function_exists("mb_strcut")) {
        return mb_strcut($string, $start, $length, "UTF-8");
    } else {
        return substr($string, $start, $length);
    }
}

function _strlen($string) {
    // make sure the name is not chunked in case of multibyte string
    if (function_exists("mb_strlen")) {
        return mb_strlen($string, "UTF-8");
    } else {
        return strlen($string);
    }
}

function getSEODescription($text, $maxChars = 250) {
    $removeChars = ['|', '"'];
    $replaceChars = ['-', ''];
    $newText = trim(str_replace($removeChars, $replaceChars, html2plainText($text)));
    if (_strlen($string) > $maxChars) {
        return $newText;
    } else {
        return _substr($newText, 0, $maxChars) . '...';
    }
}

function getSEOTitle($text, $maxChars = 60) {
    $removeChars = ['|', '"'];
    $replaceChars = ['-', ''];
    $newText = trim(str_replace($removeChars, $replaceChars, safeString($text)));
    if (_strlen($newText) < $maxChars) {
        return $newText;
    } else {
        return _substr($newText, 0, $maxChars-3) . '...';
    }
}

function getPagination($total, $page = 0, $link = "", $maxVisible = 10, $infinityScrollGetFromSelector = "", $infinityScrollAppendIntoSelector = "") {
    global $global, $advancedCustom;
    if ($total < 2) {
        return '<!-- getPagination total < 2 (' . json_encode($total) . ') -->';
    }

    if (empty($page)) {
        $page = getCurrentPage();
    }

    $isInfiniteScroll = !empty($infinityScrollGetFromSelector) && !empty($infinityScrollAppendIntoSelector);

    $uid = md5($link);

    if ($total < $maxVisible) {
        $maxVisible = $total;
    }
    if (empty($link)) {
        $link = getSelfURI();
        if (preg_match("/(current=[0-9]+)/i", $link, $match)) {
            $link = str_replace($match[1], "current={page}", $link);
        } else {
            //$link = addQueryStringParameter($link, 'current', '{page}');
            $link .= (parse_url($link, PHP_URL_QUERY) ? '&' : '?') . 'current={page}';
        }
    }

    $class = '';
    if (!empty($infinityScrollGetFromSelector) && !empty($infinityScrollAppendIntoSelector)) {
        $class = "infiniteScrollPagination{$uid} hidden";
    }

    if ($isInfiniteScroll && $page > 1) {
        if (preg_match("/\{page\}/", $link, $match)) {
            $pageForwardLink = str_replace("{page}", $page + 1, $link);
        } else {
            $pageForwardLink = addQueryStringParameter($link, 'current', $page + 1);
        }

        return "<nav class=\"{$class}\">"
                . "<ul class=\"pagination\">"
                . "<li class=\"page-item\"><a class=\"page-link pagination__next pagination__next{$uid}\" href=\"{$pageForwardLink}\"></a></li></ul></nav>";
    }

    $pag = '<nav aria-label="Page navigation" class="text-center ' . $class . '"><ul class="pagination"><!-- page ' . $page . ' maxVisible = ' . $maxVisible . ' -->';
    $start = 1;
    $end = $maxVisible;

    if ($page > $maxVisible - 2) {
        $start = $page - ($maxVisible - 2);
        $end = $page + 2;
        if ($end > $total) {
            $rest = $end - $total;
            $start -= $rest;
            $end -= $rest;
        }
    }
    if ($start <= 0) {
        $start = 1;
    }
    if (!$isInfiniteScroll) {
        if ($page > 1) {
            $pageLinkNum = 1;
            $pageBackLinkNum = $page - 1;
            if (preg_match("/\{page\}/", $link, $match)) {
                $pageLink = str_replace("{page}", $pageLinkNum, $link);
                $pageBackLink = str_replace("{page}", $pageBackLinkNum, $link);
            } else {
                $pageLink = addQueryStringParameter($link, 'current', $pageLinkNum);
                $pageBackLink = addQueryStringParameter($link, 'current', $pageBackLinkNum);
            }
            if ($start > ($page - 1)) {
                $pag .= PHP_EOL . '<li class="page-item"><a pageNum="' . $pageLinkNum . '" class="page-link backLink1" href="' . $pageLink . '" tabindex="-1" onclick="modal.showPleaseWait();"><i class="fas fa-angle-double-left"></i></a></li>';
            }
            $pag .= PHP_EOL . '<li class="page-item"><a pageNum="' . $pageBackLinkNum . '" class="page-link backLink2" href="' . $pageBackLink . '" tabindex="-1" onclick="modal.showPleaseWait();"><i class="fas fa-angle-left"></i></a></li>';
        }
        for ($i = $start; $i <= $end; $i++) {
            if ($i == $page) {
                $pag .= PHP_EOL . ' <li class="page-item active"><span class="page-link"> ' . $i . ' <span class="sr-only">(current)</span></span></li>';
            } else {
                if (preg_match("/\{page\}/", $link, $match)) {
                    $pageLink = str_replace("{page}", $i, $link);
                } else {
                    $pageLink = addQueryStringParameter($link, 'current', $i);
                }
                $pag .= PHP_EOL . ' <li class="page-item"><a pageNum="' . $i . '"class="page-link pageLink1" href="' . $pageLink . '" onclick="modal.showPleaseWait();"> ' . $i . ' </a></li>';
            }
        }
    }
    if ($page < $total) {
        $pageLinkNum = $total;
        $pageForwardLinkNum = $page + 1;
        if (preg_match("/\{page\}/", $link, $match)) {
            $pageLink = str_replace("{page}", $pageLinkNum, $link);
            $pageForwardLink = str_replace("{page}", $pageForwardLinkNum, $link);
        } else {
            $pageLink = addQueryStringParameter($link, 'current', $pageLinkNum);
            $pageForwardLink = addQueryStringParameter($link, 'current', $pageForwardLinkNum);
        }
        $pag .= PHP_EOL . '<li class="page-item"><a pageNum="' . $pageForwardLinkNum . '" class="page-link  pageLink2 pagination__next' . $uid . '" href="' . $pageForwardLink . '" tabindex="-1" onclick="modal.showPleaseWait();"><i class="fas fa-angle-right"></i></a></li>';
        if ($total > ($end + 1)) {
            $pag .= PHP_EOL . '<li class="page-item"><a pageNum="' . $pageLinkNum . '" class="page-link  pageLink3" href="' . $pageLink . '" tabindex="-1" onclick="modal.showPleaseWait();"><i class="fas fa-angle-double-right"></i></a></li>';
        }
    }
    $pag .= PHP_EOL . '</ul></nav> ';

    if ($isInfiniteScroll) {
        $content = file_get_contents($global['systemRootPath'] . 'objects/functiongetPagination.php');
        $pag .= str_replace(
                ['$uid', '$webSiteRootURL', '$infinityScrollGetFromSelector', '$infinityScrollAppendIntoSelector'],
                [$uid, $global['webSiteRootURL'], $infinityScrollGetFromSelector, $infinityScrollAppendIntoSelector],
                $content
        );
    }

    return $pag;
}

function getShareMenu($title, $permaLink, $URLFriendly, $embedURL, $img, $class = "row bgWhite list-group-item menusDiv", $videoLengthInSeconds = 0, $bitLyLink = '') {
    global $global, $advancedCustom;
    include $global['systemRootPath'] . 'objects/functiongetShareMenu.php';
}

function getSharePopupButton($videos_id, $url = "", $title = "") {
    global $global, $advancedCustom;
    if ($advancedCustom->disableShareOnly || $advancedCustom->disableShareAndPlaylist) {
        return false;
    }
    $video['id'] = $videos_id;
    include $global['systemRootPath'] . 'view/include/socialModal.php';
}

function forbiddenPage($message = '', $logMessage = false, $unlockPassword = '', $namespace = '') {
    global $global;
    if (!empty($unlockPassword)) {
        if (empty($namespace)) {
            $namespace = $_SERVER["SCRIPT_FILENAME"];
        }
        if (!empty($_REQUEST['unlockPassword'])) {
            if ($_REQUEST['unlockPassword'] == $unlockPassword) {
                _session_start();
                if (!isset($_SESSION['user']['forbiddenPage'])) {
                    $_SESSION['user']['forbiddenPage'] = [];
                }
                $_SESSION['user']['forbiddenPage'][$namespace] = $_REQUEST['unlockPassword'];
            }
        }
        if (!empty($_SESSION['user']['forbiddenPage'][$namespace]) && $unlockPassword === $_SESSION['user']['forbiddenPage'][$namespace]) {
            return true;
        }
    }
    $_REQUEST['403ErrorMsg'] = $message;
    if ($logMessage) {
        _error_log($message);
    }

    $headers = headers_list(); // get list of headers
    foreach ($headers as $header) { // iterate over that list of headers
        if (stripos($header, 'Content-Type:') !== FALSE) { // if the current header hasthe String "Content-Type" in it
            $headerParts = explode(':', $header); // split the string, getting an array
            $headerValue = trim($headerParts[1]); // take second part as value
            $contentType = $headerValue;
            break;
        }
    }
    if (empty($unlockPassword) && preg_match('/json/i', $contentType)) {
        header("Content-Type: application/json");
        $obj = new stdClass();
        $obj->error = true;
        $obj->msg = $message;
        $obj->forbiddenPage = true;
        die(json_encode($obj));
    } else {
        header("Content-Type: text/html");
        include $global['systemRootPath'] . 'view/forbiddenPage.php';
    }
    exit;
}

define('E_FATAL', E_ERROR | E_USER_ERROR | E_PARSE | E_CORE_ERROR |
        E_COMPILE_ERROR | E_RECOVERABLE_ERROR);
if (!isCommandLineInterface() && !isAVideoEncoder()) {
    register_shutdown_function('avidoeShutdown');
}

function avidoeShutdown() {
    global $global;
    $error = error_get_last();
    if ($error && ($error['type'] & E_FATAL)) {
        var_dump($error);
        _error_log($error, AVideoLog::$ERROR);
        header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
        if (!User::isAdmin()) {
            if (!preg_match('/json\.php$/i', $_SERVER['PHP_SELF'])) {
                echo '<!-- This page means an error 500 Internal Server Error, check your log file -->' . PHP_EOL;
                include $global['systemRootPath'] . 'view/maintanance.html';
            } else {
                $o = new stdClass();
                $o->error = true;
                $o->msg = __('Under Maintanance');
                echo json_encode($o);
            }
        } else {
            echo '<pre>';
            var_dump($error);
            var_dump(debug_backtrace());
            echo '</pre>';
        }
        exit;
    }
}

function videoNotFound($message, $logMessage = false) {
    global $global;
    $_REQUEST['404ErrorMsg'] = $message;
    if ($logMessage) {
        _error_log($message);
    }
    include $global['systemRootPath'] . 'view/videoNotFound.php';
    exit;
}

function isForbidden() {
    global $global;
    if (!empty($global['isForbidden'])) {
        return true;
    }
    return false;
}

function diskUsageBars() {
    return ''; //TODO check why it is slowing down
    global $global;
    include $global['systemRootPath'] . 'objects/functiondiskUsageBars.php';
    $contents = getIncludeFileContent($global['systemRootPath'] . 'objects/functiondiskUsageBars.php');
    return $contents;
}

function getDomain() {
    global $global, $_getDomain;

    if (isset($_getDomain)) {
        return $_getDomain;
    }

    if (empty($_SERVER['HTTP_HOST'])) {
        $parse = parse_url($global['webSiteRootURL']);
        $domain = $parse['host'];
    } else {
        $domain = $_SERVER['HTTP_HOST'];
    }
    $domain = str_replace("www.", "", $domain);
    $domain = preg_match("/^\..+/", $domain) ? ltrim($domain, '.') : $domain;
    $domain = preg_replace('/:[0-9]+$/', '', $domain);
    $_getDomain = $domain;
    return $domain;
}

function getHostOnlyFromURL($url) {
    $parse = parse_url($url);
    $domain = $parse['host'];
    $domain = str_replace("www.", "", $domain);
    $domain = preg_match("/^\..+/", $domain) ? ltrim($domain, '.') : $domain;
    $domain = preg_replace('/:[0-9]+$/', '', $domain);
    return $domain;
}

/**
 * It's separated by time, version, clock_seq_hi, clock_seq_lo, node, as indicated in the followoing rfc.
 *
 * From the IETF RFC4122:
 * 8-4-4-4-12
 * @return string
 */
function getDeviceID($useRandomString = true) {
    $ip = md5(getRealIpAddr());
    $pattern = "/[^0-9a-z_.-]/i";
    if (empty($_SERVER['HTTP_USER_AGENT'])) {
        $device = "unknowDevice-{$ip}";
        $device .= '-' . intval(User::getId());
        return preg_replace($pattern, '-', $device);
    }

    if (empty($useRandomString)) {
        $device = 'ypt-' . get_browser_name() . '-' . getOS() . '-' . $ip . '-' . md5($_SERVER['HTTP_USER_AGENT']);
        $device = str_replace(
                ['[', ']', ' '],
                ['', '', '_'],
                $device
        );
        $device .= '-' . intval(User::getId());
        return preg_replace($pattern, '-', $device);
    }

    $cookieName = "yptDeviceID";
    if (empty($_COOKIE[$cookieName])) {
        if (empty($_GET[$cookieName])) {
            $id = uniqidV4();
            $_GET[$cookieName] = $id;
        }
        if (empty($_SESSION[$cookieName])) {
            _session_start();
            $_SESSION[$cookieName] = $_GET[$cookieName];
        } else {
            $_GET[$cookieName] = $_SESSION[$cookieName];
        }
        if (!_setcookie($cookieName, $_GET[$cookieName], strtotime("+ 1 year"))) {
            return "getDeviceIDError";
        }
        $_COOKIE[$cookieName] = $_GET[$cookieName];
    }
    return preg_replace($pattern, '-', $_COOKIE[$cookieName]);
}

function deviceIdToObject($deviceID) {
    $parts = explode('-', $deviceID);
    $obj = new stdClass();
    $obj->browser = '';
    $obj->os = '';
    $obj->ip = '';
    $obj->user_agent = '';
    $obj->users_id = 0;

    foreach ($parts as $key => $value) {
        $parts[$key] = str_replace('_', ' ', $value);
    }

    switch ($parts[0]) {
        case 'ypt':
            $obj->browser = $parts[1];
            $obj->os = $parts[2];
            $obj->ip = $parts[3];
            $obj->user_agent = $parts[4];
            $obj->users_id = $parts[5];
            break;
        case 'unknowDevice':
            $obj->browser = $parts[0];
            $obj->os = 'unknow OS';
            $obj->ip = $parts[1];
            $obj->user_agent = 'unknow UA';
            $obj->users_id = $parts[2];
            break;
        default:
            break;
    }
    return $obj;
}

function uniqidV4() {
    $randomString = openssl_random_pseudo_bytes(16);
    $time_low = bin2hex(substr($randomString, 0, 4));
    $time_mid = bin2hex(substr($randomString, 4, 2));
    $time_hi_and_version = bin2hex(substr($randomString, 6, 2));
    $clock_seq_hi_and_reserved = bin2hex(substr($randomString, 8, 2));
    $node = bin2hex(substr($randomString, 10, 6));

    /**
     * Set the four most significant bits (bits 12 through 15) of the
     * time_hi_and_version field to the 4-bit version number from
     * Section 4.1.3.
     * @see http://tools.ietf.org/html/rfc4122#section-4.1.3
     */
    $time_hi_and_version = hexdec($time_hi_and_version);
    $time_hi_and_version = $time_hi_and_version >> 4;
    $time_hi_and_version = $time_hi_and_version | 0x4000;

    /**
     * Set the two most significant bits (bits 6 and 7) of the
     * clock_seq_hi_and_reserved to zero and one, respectively.
     */
    $clock_seq_hi_and_reserved = hexdec($clock_seq_hi_and_reserved);
    $clock_seq_hi_and_reserved = $clock_seq_hi_and_reserved >> 2;
    $clock_seq_hi_and_reserved = $clock_seq_hi_and_reserved | 0x8000;

    return sprintf('%08s-%04s-%04x-%04x-%012s', $time_low, $time_mid, $time_hi_and_version, $clock_seq_hi_and_reserved, $node);
}

// guid

function _setcookie($cookieName, $value, $expires = 0) {
    if (empty($expires)) {
        if (empty($config) || !is_object($config)) {
            $config = new Configuration();
        }
        $expires = time() + $config->getSession_timeout();
    }

    if (version_compare(phpversion(), '7.3', '>=')) {
        $cookie_options = [
            'expires' => $expires,
            'path' => '/',
            'domain' => getDomain(),
            'secure' => true,
            'httponly' => true,
            'samesite' => 'None'
        ];
        return setcookie($cookieName, $value, $cookie_options);
    } else {
        return setcookie($cookieName, $value, (int) $expires, "/", getDomain());
    }
}

function _unsetcookie($cookieName) {
    $domain = getDomain();
    $expires = strtotime("-10 years");
    $value = '';
    _setcookie($cookieName, $value, $expires);
    setcookie($cookieName, $value, (int) $expires, "/") && setcookie($cookieName, $value, (int) $expires);
    setcookie($cookieName, $value, (int) $expires, "/", str_replace("www", "", $domain));
    setcookie($cookieName, $value, (int) $expires, "/", "www." . $domain);
    setcookie($cookieName, $value, (int) $expires, "/", "." . $domain);
    setcookie($cookieName, $value, (int) $expires, "/", $domain);
    setcookie($cookieName, $value, (int) $expires, "/");
    setcookie($cookieName, $value, (int) $expires);
    unset($_COOKIE[$cookieName]);
}

/**
 * This function is not 100% but try to tell if the site is in an iFrame
 * @global type $global
 * @return boolean
 */
function isIframeInDifferentDomain() {
    global $global;
    if (!isIframe()) {
        return false;
    }
    return isSameDomainAsMyAVideo($_SERVER['HTTP_REFERER']);
}

function isIframe() {
    global $global;
    if (isset($_SERVER['HTTP_SEC_FETCH_DEST']) && $_SERVER['HTTP_SEC_FETCH_DEST'] === 'iframe') {
        return true;
    }
    if (empty($_SERVER['HTTP_REFERER']) || $_SERVER['HTTP_REFERER'] == $global['webSiteRootURL'] || str_replace('view/', '', getSelfURI()) == $global['webSiteRootURL']) {
        return false;
    }
    return true;
}
function inIframe() {
    return isIframe();
}

function getCredentialsURL() {
    global $global;
    return "webSiteRootURL=" . urlencode($global['webSiteRootURL']) . "&user=" . urlencode(User::getUserName()) . "&pass=" . urlencode(User::getUserPass()) . "&encodedPass=1";
}

function gotToLoginAndComeBackHere($msg) {
    global $global;
    if (User::isLogged()) {
        forbiddenPage($msg);
        exit;
    }
    if (!empty($_GET['comebackhere'])) {
        return false;
    }
    header("Location: {$global['webSiteRootURL']}user?redirectUri=" . urlencode(getSelfURI()) . "&msg=" . urlencode($msg) . "&comebackhere=1");
    exit;
}

function setAlertMessage($msg, $type = "msg") {
    _session_start();
    $_SESSION['YPTalertMessage'][] = [$msg, $type];
}

function setToastMessage($msg) {
    setAlertMessage($msg, "toast");
}

function showAlertMessage() {
    if (!requestComesFromSafePlace()) {
        echo PHP_EOL, "/** showAlertMessage !requestComesFromSafePlace **/";
        return false;
    }
    if (!empty($_SESSION['YPTalertMessage'])) {
        foreach ($_SESSION['YPTalertMessage'] as $value) {
            if (!empty($value[0])) {
                if (empty($_GET[$value[1]])) {
                    $_GET[$value[1]] = [];
                }
                $_GET[$value[1]][] = $value[0];
            }
        }
        _session_start();
        unset($_SESSION['YPTalertMessage']);
    }

    $joinString = ['error', 'msg', 'success', 'toast'];
    foreach ($joinString as $value) {
        if (!empty($_GET[$value])) {
            if (is_array($_GET[$value])) {
                $_GET[$value] = array_unique($_GET[$value]);
                $newStr = [];
                foreach ($_GET[$value] as $value2) {
                    if (!empty($value2)) {
                        $newStr[] = $value2;
                    }
                }
                $_GET[$value] = implode("<br>", $newStr);
            } else {
                $_GET[$value] = $_GET[$value];
            }
        }
    }

    $check = ['error', 'msg', 'success', 'toast'];
    foreach ($check as $value) {
        if (!empty($_GET[$value])) {
            if (is_array($_GET[$value])) {
                $newStr = [];
                foreach ($_GET[$value] as $key => $value2) {
                    $value2 = str_replace('"', "''", $value2);
                    if (!empty($value2)) {
                        $newStr[] = $value2;
                    }
                }
                $_GET[$value] = $newStr;
            } else {
                $_GET[$value] = str_replace('"', "''", $_GET[$value]);
            }
        }
    }
    echo "/** showAlertMessage **/", PHP_EOL;
    if (!empty($_GET['error'])) {
        echo 'avideoAlertError("' . $_GET['error'] . '");';
        echo 'window.history.pushState({}, document.title, "' . getSelfURI() . '");';
    }
    if (!empty($_GET['msg'])) {
        echo 'avideoAlertInfo("' . $_GET['msg'] . '");';
        echo 'window.history.pushState({}, document.title, "' . getSelfURI() . '");';
    }
    if (!empty($_GET['success'])) {
        echo 'avideoAlertSuccess("' . $_GET['success'] . '");';
        echo 'window.history.pushState({}, document.title, "' . getSelfURI() . '");';
    }
    if (!empty($_GET['toast'])) {
        if (!is_array($_GET['toast'])) {
            $_GET['toast'] = [$_GET['toast']];
        } else {
            $_GET['toast'] = array_unique($_GET['toast']);
        }
        foreach ($_GET['toast'] as $key => $value) {
            $hideAfter = strlen(strip_tags($value)) * 150;

            if ($hideAfter < 3000) {
                $hideAfter = 3000;
            }
            if ($hideAfter > 15000) {
                $hideAfter = 15000;
            }

            echo '$.toast({
                    text: "' . strip_tags($value, $allowable_tags) . '",
                    hideAfter: ' . $hideAfter . '   // in milli seconds
                });console.log("Toast Hide after ' . $hideAfter . '");';
        }
        echo 'window.history.pushState({}, document.title, "' . getSelfURI() . '");';
    }
    echo PHP_EOL, "/** showAlertMessage END **/";
}

function getResolutionLabel($res) {
    if ($res == 720) {
        return "<span class='label label-danger' style='padding: 0 2px; font-size: .8em; display: inline;'>" . getResolutionText($res) . "</span>";
    } elseif ($res == 1080) {
        return "<span class='label label-danger' style='padding: 0 2px; font-size: .8em; display: inline;'>" . getResolutionText($res) . "</span>";
    } elseif ($res == 1440) {
        return "<span class='label label-danger' style='padding: 0 2px; font-size: .8em; display: inline;'>" . getResolutionText($res) . "</span>";
    } elseif ($res == 2160) {
        return "<span class='label label-danger' style='padding: 0 2px; font-size: .8em; display: inline;'>" . getResolutionText($res) . "</span>";
    } elseif ($res == 4320) {
        return "<span class='label label-danger' style='padding: 0 2px; font-size: .8em; display: inline;'>" . getResolutionText($res) . "</span>";
    } else {
        return '';
    }
}

function getResolutionText($res) {
    $res = intval($res);
    if ($res >= 720 && $res < 1080) {
        return "HD";
    } elseif ($res >= 1080 && $res < 1440) {
        return "FHD";
    } elseif ($res >= 1440 && $res < 2160) {
        return "FHD+";
    } elseif ($res >= 2160 && $res < 4320) {
        return "4K";
    } elseif ($res >= 4320) {
        return "8K";
    } else {
        return '';
    }
}

function getResolutionTextRoku($res) {
    $res = intval($res);
    if ($res >= 720 && $res < 1080) {
        return "HD";
    } elseif ($res >= 1080 && $res < 2160) {
        return "FHD";
    } elseif ($res >= 2160) {
        return "UHD";
    } else {
        return 'SD';
    }
}

// just realize the readdir is a lot faster then glob
function _glob($dir, $pattern) {
    global $_glob;
    if (empty($dir)) {
        return [];
    }
    if (empty($_glob)) {
        $_glob = [];
    }
    $name = md5($dir . $pattern);
    if (isset($_glob[$name])) {
        return $_glob[$name];
    }
    $dir = rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    $array = [];
    if (is_dir($dir) && $handle = opendir($dir)) {
        $count = 0;
        while (false !== ($file_name = readdir($handle))) {
            if ($file_name == '.' || $file_name == '..') {
                continue;
            }
            //_error_log("_glob: {$dir}{$file_name} [$pattern]");
            //var_dump($pattern, $file_name, preg_match($pattern, $file_name));
            if (preg_match($pattern, $file_name)) {
                $array[] = "{$dir}{$file_name}";
            }
        }
        closedir($handle);
    }
    $_glob[$name] = $array;
    return $array;
}

function globVideosDir($filename, $filesOnly = false) {
    global $global;
    if (empty($filename)) {
        return [];
    }
    $cleanfilename = Video::getCleanFilenameFromFile($filename);
    $paths = Video::getPaths($filename);

    $dir = $paths['path'];

    if (is_dir($dir . $filename)) {
        $dir = $dir . $filename;
        $cleanfilename = '';
    }

    $pattern = "/{$cleanfilename}.*";
    if (!empty($filesOnly)) {
        $formats = getValidFormats();
        $pattern .= ".(" . implode("|", $formats) . ")";
    }
    $pattern .= "/";
    //var_dump($dir, $pattern);
    return _glob($dir, $pattern);
}

function getValidFormats() {
    $video = ['webm', 'mp4', 'm3u8'];
    $audio = ['mp3', 'ogg'];
    $image = ['jpg', 'gif', 'webp'];
    return array_merge($video, $audio, $image);
}

function isValidFormats($format) {
    $format = str_replace(".", "", $format);
    return in_array($format, getValidFormats());
}

function getTimerFromDates($startTime, $endTime = 0) {
    if (!is_int($startTime)) {
        $startTime = strtotime($startTime);
    }
    if (!is_int($endTime)) {
        $endTime = strtotime($endTime);
    }
    if (empty($endTime)) {
        $endTime = time();
    }
    $timer = abs($endTime - $startTime);
    $uid = uniqid();
    return "<span id='{$uid}'></span><script>$(document).ready(function () {startTimer({$timer}, '#{$uid}', '');})</script>";
}

function getServerClock() {
    $id = uniqid();
    $today = getdate();
    $html = '<span id="' . $id . '">00:00:00</span>';
    $html .= "<script type=\"text/javascript\">
    $(document).ready(function () {
        var d = new Date({$today['year']},{$today['mon']},{$today['mday']},{$today['hours']},{$today['minutes']},{$today['seconds']});
        setInterval(function() {
            d.setSeconds(d.getSeconds() + 1);
            $('#{$id}').text((d.getHours() +':' + d.getMinutes() + ':' + d.getSeconds() ));
        }, 1000);
    });
</script>";
    return $html;
}

/**
 * Xsendfile and FFMPEG are required for this feature
 * @global type $global
 * @param type $filepath
 * @return boolean
 */
function downloadHLS($filepath) {
    global $global;

    if (!CustomizeUser::canDownloadVideos()) {
        _error_log("downloadHLS: CustomizeUser::canDownloadVideos said NO");
        return false;
    }

    if (!file_exists($filepath)) {
        _error_log("downloadHLS: file NOT found: {$filepath}");
        return false;
    }
    $output = m3u8ToMP4($filepath);

    if (!empty($output['error'])) {
        $msg = 'downloadHLS was not possible';
        if (User::isAdmin()) {
            $msg .= '<br>' . "m3u8ToMP4($filepath) return empty<br>" . nl2br($output['msg']);
        }
        _error_log("downloadHLS: m3u8ToMP4($filepath) return empty");
        die($msg);
    }

    $outputpath = $output['path'];
    $outputfilename = $output['filename'];

    if (!empty($_REQUEST['title'])) {
        $quoted = sprintf('"%s"', addcslashes(basename($_REQUEST['title']), '"\\'));
    } elseif (!empty($_REQUEST['file'])) {
        $quoted = sprintf('"%s"', addcslashes(basename($_REQUEST['file']), '"\\')) . ".mp4";
    } else {
        $quoted = $outputfilename;
    }

    header('Content-Description: File Transfer');
    header('Content-Disposition: attachment; filename=' . $quoted);
    header('Content-Transfer-Encoding: binary');
    header('Connection: Keep-Alive');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    header("X-Sendfile: {$outputpath}");
    exit;
}

function playHLSasMP4($filepath) {
    global $global;

    if (!CustomizeUser::canDownloadVideos()) {
        _error_log("playHLSasMP4: CustomizeUser::canDownloadVideos said NO");
        return false;
    }

    if (!file_exists($filepath)) {
        _error_log("playHLSasMP4: file NOT found: {$filepath}");
        return false;
    }
    $output = m3u8ToMP4($filepath);

    if (!empty($output['error'])) {
        $msg = 'playHLSasMP4 was not possible';
        if (User::isAdmin()) {
            $msg .= '<br>' . "m3u8ToMP4($filepath) return empty<br>" . nl2br($output['msg']);
        }
        die($msg);
    }

    $outputpath = $output['path'];

    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Cache-Control: post-check=0, pre-check=0', false);
    header('Pragma: no-cache');
    header('Content-type: video/mp4');
    header('Content-Length: ' . filesize($outputpath));
    header("X-Sendfile: {$outputpath}");
    exit;
}

function getSocialModal($videos_id, $url = "", $title = "") {
    global $global;
    $video['id'] = $videos_id;
    $sharingUid = uniqid();
    $filePath = $global['systemRootPath'] . 'objects/functionGetSocialModal.php';
    $contents = getIncludeFileContent($filePath,
            array(
                'videos_id' => $videos_id,
                'url' => $url,
                'title' => $title,
                'video' => $video,
                'sharingUid' => $sharingUid
            )
    );
    return ['html' => $contents, 'id' => $sharingUid];
}

function getCroppie(
        $buttonTitle,
        $callBackJSFunction,
        $resultWidth = 0,
        $resultHeight = 0,
        $viewportWidth = 0,
        $boundary = 25,
        $viewportHeight = 0,
        $enforceBoundary = true
) {
    global $global;

    if (empty($resultWidth) && empty($resultHeight)) {
        if (isMobile()) {
            $viewportWidth = 250;
        } else {
            $viewportWidth = 800;
        }

        if (defaultIsPortrait()) {
            $resultWidth = 540;
            $resultHeight = 800;
        } else {
            $resultWidth = 1280;
            $resultHeight = 720;
        }
    }

    if (empty($viewportWidth)) {
        $viewportWidth = $resultWidth;
    }
    $zoom = 0;
    if (empty($viewportHeight)) {
        $zoom = ($viewportWidth / $resultWidth);
        $viewportHeight = $zoom * $resultHeight;
    }
    if (empty($enforceBoundary)) {
        $boundary = 0;
    }
    $boundaryWidth = $viewportWidth + $boundary;
    $boundaryHeight = $viewportHeight + $boundary;
    $uid = uniqid();

    $varsArray = array(
        'buttonTitle' => $buttonTitle,
        'callBackJSFunction' => $callBackJSFunction,
        'resultWidth' => $resultWidth,
        'resultHeight' => $resultHeight,
        'viewportWidth' => $viewportWidth,
        'boundary' => $boundary,
        'viewportHeight' => $viewportHeight,
        'enforceBoundary' => $enforceBoundary,
        'zoom' => $zoom,
        'boundaryWidth' => $boundaryWidth,
        'boundaryHeight' => $boundaryHeight,
        'uid' => $uid,);

    $contents = getIncludeFileContent($global['systemRootPath'] . 'objects/functionCroppie.php', $varsArray);

    $callBackJSFunction = addcslashes($callBackJSFunction, "'");
    return [
        "html" => $contents,
        "id" => "croppie{$uid}",
        "uploadCropObject" => "uploadCrop{$uid}",
        "getCroppieFunction" => "getCroppie(uploadCrop{$uid}, '{$callBackJSFunction}', {$resultWidth}, {$resultHeight});",
        "createCroppie" => "createCroppie{$uid}",
        "restartCroppie" => "restartCroppie{$uid}",
    ];
}

function saveCroppieImage($destination, $postIndex = "imgBase64") {
    if (empty($destination) || empty($_POST[$postIndex])) {
        return false;
    }
    $fileData = base64DataToImage($_POST[$postIndex]);

    $path_parts = pathinfo($destination);
    $tmpDestination = $destination;
    $extension = strtolower($path_parts['extension']);
    if ($extension !== 'png') {
        $tmpDestination = $destination . '.png';
    }

    $saved = _file_put_contents($tmpDestination, $fileData);

    if ($saved) {
        if ($extension !== 'png') {
            convertImage($tmpDestination, $destination, 100);
            unlink($tmpDestination);
        }
    }
    //var_dump($saved, $tmpDestination, $destination, $extension);exit;
    return $saved;
}

function get_ffmpeg($ignoreGPU = false) {
    global $global;
    $complement = '';
    //$complement = ' -user_agent "'.getSelfUserAgent("FFMPEG").'" ';
    //return 'ffmpeg -headers "User-Agent: '.getSelfUserAgent("FFMPEG").'" ';
    $ffmpeg = 'ffmpeg  ';
    if (empty($ignoreGPU) && !empty($global['ffmpegGPU'])) {
        $ffmpeg .= ' --enable-nvenc ';
    }
    if (!empty($global['ffmpeg'])) {
        $ffmpeg = "{$global['ffmpeg']}{$ffmpeg}";
    }
    return $ffmpeg . $complement;
}

function convertVideoFileWithFFMPEG($fromFileLocation, $toFileLocation, $try = 0) {
    $localFileLock = getVideosDir() . "{$relativeFilename}.lock";
    if (file_exists($localFileLock)) {
        _error_log('convertVideoFileWithFFMPEG: download from CDN There is a process running for ' . $localFile);
        return false;
    }
    make_path($toFileLocation);
    file_put_contents($localFileLock, time());
    $fromFileLocationEscaped = escapeshellarg($fromFileLocation);
    $toFileLocationEscaped = escapeshellarg($toFileLocation);

    $format = pathinfo($toFileLocation, PATHINFO_EXTENSION);

    if ($format == 'mp3') {
        switch ($try) {
            case 0:
                $command = get_ffmpeg() . " -i \"{$fromFileLocation}\" -c:a libmp3lame \"{$toFileLocation}\"";
                break;
            default:
                return false;
                break;
        }
    } else {
        switch ($try) {
            case 0:
                $command = get_ffmpeg() . " -i {$fromFileLocationEscaped} -c copy {$toFileLocationEscaped}";
                break;
            case 1:
                $command = get_ffmpeg() . " -allowed_extensions ALL -y -i {$fromFileLocationEscaped} -c:v copy -c:a copy -bsf:a aac_adtstoasc -strict -2 {$toFileLocationEscaped}";
                break;
            case 2:
                $command = get_ffmpeg() . " -y -i {$fromFileLocationEscaped} -c:v copy -c:a copy -bsf:a aac_adtstoasc -strict -2 {$toFileLocationEscaped}";
                break;
            default:
                return false;
                break;
        }
        
    }
    $progressFile = getConvertVideoFileWithFFMPEGProgressFilename($toFileLocation);
    $progressFileEscaped = escapeshellarg($progressFile);
    $command .= " 1> {$progressFileEscaped} 2>&1";
    _error_log("convertVideoFileWithFFMPEG try[{$try}]: " . $command);
    session_write_close();
    _mysql_close();
    exec($command, $output, $return);
    _session_start();
    _mysql_connect();
    _error_log("convertVideoFileWithFFMPEG try[{$try}] output: " . json_encode($output));

    unlink($localFileLock);
    
    return ['return'=> $return, 'output'=>$output, 'command'=>$command, 'fromFileLocation'=>$fromFileLocation, 'toFileLocation'=>$toFileLocation, 'progressFile'=>$progressFile];
}

function m3u8ToMP4($input) {
    $videosDir = getVideosDir();
    $outputfilename = str_replace($videosDir, "", $input);
    $parts = explode("/", $outputfilename);
    $resolution = Video::getResolutionFromFilename($input);
    $outputfilename = $parts[0] . "_{$resolution}_.mp4";
    $outputpath = "{$videosDir}cache/downloads/{$outputfilename}";
    $msg = '';
    $error = true;
    if (empty($outputfilename)) {
        $msg = "downloadHLS: empty outputfilename {$outputfilename}";
        _error_log($msg);
        return ['error' => $error, 'msg' => $msg];
    }
    _error_log("downloadHLS: m3u8ToMP4($input)");
    //var_dump(!preg_match('/^http/i', $input), filesize($input), preg_match('/.m3u8$/i', $input));
    $ism3u8 = preg_match('/.m3u8$/i', $input);
    if (!preg_match('/^http/i', $input) && (filesize($input) <= 10 || $ism3u8)) { // dummy file
        $filepath = pathToRemoteURL($input, true, true);
        if ($ism3u8 && !preg_match('/.m3u8$/i', $filepath)) {
            $filepath = addLastSlash($filepath) . 'index.m3u8';
        }

        $token = getToken(60);
        $filepath = addQueryStringParameter($filepath, 'globalToken', $token);
    } else {
        $filepath = escapeshellcmd($input);
    }

    if (is_dir($filepath)) {
        $filepath = addLastSlash($filepath) . 'index.m3u8';
    }

    if (!file_exists($outputpath)) {
        var_dump($filepath, $outputpath);exit;
        $return = convertVideoFileWithFFMPEG($filepath, $outputpath);
        var_dump($return);exit;
        if (empty($return)) {
            $msg3 = "downloadHLS: ERROR 2 " . implode(PHP_EOL, $output);
            $finalMsg = $msg1 . PHP_EOL . $msg2 . PHP_EOL . $msg3;
            _error_log($msg3);
            return ['error' => $error, 'msg' => $finalMsg];
        }
    } else {
        $msg = "downloadHLS: outputpath already exists ({$outputpath})";
        _error_log($msg);
    }
    $error = false;
    return ['error' => $error, 'msg' => $msg, 'path' => $outputpath, 'filename' => $outputfilename];
}


function getConvertVideoFileWithFFMPEGProgressFilename($toFileLocation) {
    $progressFile = $toFileLocation . '.log';
    return $progressFile;
}

function convertVideoToDownlaodProgress($toFileLocation) {
    $progressFile = getConvertVideoFileWithFFMPEGProgressFilename($toFileLocation);
    return parseFFMPEGProgress($progressFile);
}

function get_php() {
    global $global;
    $php = 'php  ';
    if (!empty($global['php'])) {
        $php = "{$global['php']} ";
    }
    return $php;
}

function isHTMLPage($url) {
    if (preg_match('/https?:\/\/(www\.)?(youtu.be|youtube.com|vimeo.com|bitchute.com)\//i', $url)) {
        return true;
    } elseif ($type = getHeaderContentTypeFromURL($url)) {
        if (preg_match('/text\/html/i', $type)) {
            return true;
        }
    }
    return false;
}

function getHeaderContentTypeFromURL($url) {
    if (isValidURL($url) && $type = get_headers($url, 1)["Content-Type"]) {
        return $type;
    }
    return false;
}

function canFullScreen() {
    global $doNotFullScreen;
    if (!empty($doNotFullScreen) || isSerie() || !isVideo()) {
        return false;
    }
    return true;
}

function getTinyMCE($id, $simpleMode = false) {
    global $global;
    $contents = getIncludeFileContent($global['systemRootPath'] . 'objects/functionsGetTinyMCE.php', array('id' => $id, 'simpleMode' => $simpleMode));
    return $contents;
}

function pathToRemoteURL($filename, $forceHTTP = false, $ignoreCDN = false) {
    global $pathToRemoteURL, $global;
    if (!isset($pathToRemoteURL)) {
        $pathToRemoteURL = [];
    }

    if (isset($pathToRemoteURL[$filename])) {
        return $pathToRemoteURL[$filename];
    }
    if (!file_exists($filename) || filesize($filename) < 1000) {
        $fileName = getFilenameFromPath($filename);
        if ($yptStorage = AVideoPlugin::loadPluginIfEnabled("YPTStorage")) {
            $source = $yptStorage->getAddress("{$fileName}");
            $url = $source['url'];
        } elseif (!preg_match('/index.m3u8$/', $filename)) {
            if ($aws_s3 = AVideoPlugin::loadPluginIfEnabled("AWS_S3")) {
                $source = $aws_s3->getAddress("{$fileName}");
                $url = $source['url'];
                if (empty($ignoreCDN)) {
                    $url = replaceCDNIfNeed($url, 'CDN_S3');
                } elseif (!empty($source['url_noCDN'])) {
                    $url = $source['url_noCDN'];
                }
            } elseif ($bb_b2 = AVideoPlugin::loadPluginIfEnabled("Blackblaze_B2")) {
                $source = $bb_b2->getAddress("{$fileName}");
                $url = $source['url'];
                if (empty($ignoreCDN)) {
                    $url = replaceCDNIfNeed($url, 'CDN_B2');
                } elseif (!empty($source['url_noCDN'])) {
                    $url = $source['url_noCDN'];
                }
            } elseif ($ftp = AVideoPlugin::loadPluginIfEnabled("FTP_Storage")) {
                $source = $ftp->getAddress("{$fileName}");
                $url = $source['url'];
                if (empty($ignoreCDN)) {
                    $url = replaceCDNIfNeed($url, 'CDN_FTP');
                } elseif (!empty($source['url_noCDN'])) {
                    $url = $source['url_noCDN'];
                }
            }
        }
    }
    if (empty($url)) {
        if ($forceHTTP) {
            $paths = Video::getPaths($filename);
            //$url = str_replace(getVideosDir(), getCDN() . "videos/", $filename);
            if (empty($ignoreCDN)) {
                $url = getCDN() . "{$paths['relative']}";
            } else {
                $url = "{$global['webSiteRootURL']}{$paths['relative']}";
            }
            if (preg_match('/index.m3u8$/', $filename) && !preg_match('/index.m3u8$/', $url)) {
                $url .= 'index.m3u8';
            }
        } else {
            $url = $filename;
        }
    }

    //$url = str_replace(array($global['systemRootPath'], '/videos/videos/'), array("", '/videos/'), $url);

    $pathToRemoteURL[$filename] = $url;
    return $url;
}

function getFilenameFromPath($path) {
    global $global;
    $fileName = Video::getCleanFilenameFromFile($path);
    return $fileName;
}

function showCloseButton() {
    global $global, $showCloseButtonIncluded;
    if (!empty($showCloseButtonIncluded)) {
        return '<!-- showCloseButton is already included -->';
    }
    if (isSerie()) {
        return '<!-- showCloseButton is a serie -->';
    }

    if (!isLive() && $obj = AVideoPlugin::getDataObjectIfEnabled("Gallery")) {
        if (!empty($obj->playVideoOnFullscreen)) {
            $_REQUEST['showCloseButton'] = 1;
        }
    }
    if (isLive() && $obj = AVideoPlugin::getDataObjectIfEnabled("Live")) {
        if (!empty($obj->playLiveInFullScreen)) {
            $_REQUEST['showCloseButton'] = 1;
        }
    }
    if (!empty($_REQUEST['showCloseButton'])) {
        $showCloseButtonIncluded = 1;
        include $global['systemRootPath'] . 'view/include/youtubeModeOnFullscreenCloseButton.php';
    }
    return '<!-- showCloseButton finished -->';
}

function getThemes() {
    global $_getThemes, $global;
    if (isset($_getThemes)) {
        return $_getThemes;
    }
    $_getThemes = [];
    foreach (glob("{$global['systemRootPath']}view/css/custom/*.css") as $filename) {
        $fileEx = basename($filename, ".css");
        $_getThemes[] = $fileEx;
    }
    return $_getThemes;
}

function getCurrentTheme() {
    global $config;
    if (!empty($_REQUEST['customCSS'])) {
        _setcookie('customCSS', $_REQUEST['customCSS']);
        return $_REQUEST['customCSS'];
    }
    if (!empty($_COOKIE['customCSS'])) {
        return $_COOKIE['customCSS'];
    }
    return $config->getTheme();
}

/*
 * $users_id="" or 0 means send messages to all users
 * $users_id="-1" means send to no one
 */

function sendSocketMessage($msg, $callbackJSFunction = "", $users_id = "-1", $send_to_uri_pattern = "") {
    if (AVideoPlugin::isEnabledByName('YPTSocket')) {
        if (!is_string($msg)) {
            $msg = json_encode($msg);
        }
        $obj = YPTSocket::send($msg, $callbackJSFunction, $users_id, $send_to_uri_pattern);
        if ($obj->error && !empty($obj->msg)) {
            _error_log("sendSocketMessage " . $obj->msg);
        }
        return $obj;
    }
    return false;
}

function sendSocketMessageToUsers_id($msg, $users_id, $callbackJSFunction = "") {

    if (empty($users_id)) {
        return false;
    }
    _error_log("sendSocketMessageToUsers_id start " . json_encode($users_id));
    if (!is_array($users_id)) {
        $users_id = [$users_id];
    }

    $resp = [];
    foreach ($users_id as $value) {
        $resp[] = sendSocketMessage($msg, $callbackJSFunction, $value);
    }

    return $resp;
}

function sendSocketErrorMessageToUsers_id($msg, $users_id, $callbackJSFunction = "avideoResponse") {
    $newMessage = new stdClass();
    $newMessage->error = true;
    $newMessage->msg = $msg;
    return sendSocketMessageToUsers_id($newMessage, $users_id, $callbackJSFunction);
}

function sendSocketSuccessMessageToUsers_id($msg, $users_id, $callbackJSFunction = "avideoResponse") {
    $newMessage = new stdClass();
    $newMessage->error = false;
    $newMessage->msg = $msg;
    return sendSocketMessageToUsers_id($newMessage, $users_id, $callbackJSFunction);
}

function sendSocketMessageToAll($msg, $callbackJSFunction = "", $send_to_uri_pattern = "") {
    return sendSocketMessage($msg, $callbackJSFunction, "", $send_to_uri_pattern);
}

function sendSocketMessageToNone($msg, $callbackJSFunction = "") {
    return sendSocketMessage($msg, $callbackJSFunction, -1);
}

function execAsync($command) {
    //$command = escapeshellarg($command);
    // If windows, else
    if (isWindows()) {
        //echo $command;
        //$pid = system("start /min  ".$command. " > NUL");
        //$commandString = "start /B " . $command;
        //pclose($pid = popen($commandString, "r"));
        _error_log($command);
        $pid = exec($command, $output, $retval);
        _error_log('execAsync: ' . json_encode($output) . ' ' . $retval);
    } else {
        $newCommand = $command . " > /dev/null 2>&1 & echo $!; ";
        _error_log($newCommand);
        $pid = exec($newCommand);
    }
    return $pid;
}

function killProcess($pid) {
    $pid = intval($pid);
    if (empty($pid)) {
        return false;
    }
    if (isWindows()) {
        exec("taskkill /F /PID $pid");
    } else {
        exec("kill -9 $pid");
    }
    return true;
}

function isWindows() {
    return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
}

function getPIDUsingPort($port) {
    $port = intval($port);
    if (empty($port)) {
        return false;
    }
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        $command = 'netstat -ano | findstr ' . $port;
        exec($command, $output, $retval);
        $pid = 0;
        foreach ($output as $value) {
            if (preg_match('/LISTENING[^0-9]+([0-9]+)/i', $value, $matches)) {
                if (!empty($matches[1])) {
                    $pid = intval($matches[1]);
                    return $pid;
                }
            }
        }
    } else {
        $command = 'lsof -n -i :' . $port . ' | grep LISTEN';
        exec($command, $output, $retval);
        $pid = 0;
        foreach ($output as $value) {
            if (preg_match('/[^ ] +([0-9]+).*/i', $value, $matches)) {
                if (!empty($matches[1])) {
                    $pid = intval($matches[1]);
                    return $pid;
                }
            }
        }
    }
    return false;
}

function isURL200($url, $forceRecheck = false) {
    global $_isURL200;
    $name = "isURL200" . DIRECTORY_SEPARATOR . md5($url);
    if (empty($forceRecheck)) {
        $result = ObjectYPT::getCache($name, 30);
        if (!empty($result)) {
            $object = _json_decode($result);
            return $object->result;
        }
    }


    $object = new stdClass();
    $object->url = $url;
    $object->forceRecheck = $forceRecheck;

    //error_log("isURL200 checking URL {$url}");
    $headers = @get_headers($url);
    if (!is_array($headers)) {
        $headers = [$headers];
    }

    $object->result = false;
    foreach ($headers as $value) {
        if (
                strpos($value, '200') ||
                strpos($value, '302') ||
                strpos($value, '304')
        ) {
            $object->result = true;
            break;
        } else {
            //_error_log('isURL200: '.$value);
        }
    }

    ObjectYPT::setCache($name, json_encode($object));

    return $object->result;
}

function isURL200Clear() {
    $tmpDir = ObjectYPT::getCacheDir();
    $cacheDir = $tmpDir . "isURL200" . DIRECTORY_SEPARATOR;
    _error_log('isURL200Clear: ' . json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)));
    rrmdir($cacheDir);
}

function deleteStatsNotifications() {
    Live::deleteStatsCache();
    $cacheName = "getStats" . DIRECTORY_SEPARATOR . "getStatsNotifications";
    ObjectYPT::deleteCache($cacheName);
}

function getLiveVideosFromCategory($categories_id) {
    $stats = getStatsNotifications();
    $videos = array();
    if (!empty($categories_id)) {
        $cat = new Category($categories_id);
        foreach ($stats["applications"] as $key => $value) {
            if (empty($value['categories_id']) || $categories_id != $value['categories_id']) {
                continue;
            }
            foreach ($value as $key => $value2) {
                if (preg_match('/^html/i', $key)) {
                    unset($value[$key]);
                }
            }
            if (!empty($value['liveLinks_id'])) {
                $ll = new LiveLinksTable($value['liveLinks_id']);

                $m3u8 = $ll->getLink();
            } else if (!empty($value['key'])) {
                $m3u8 = Live::getM3U8File($value['key']);
            } else {
                $m3u8 = '';
            }

            $user = new User($value['users_id']);

            $video = array(
                'id' => intval(rand(999999, 9999999)),
                'isLive' => 1,
                'categories_id' => $categories_id,
                'description' => '',
                'user' => $user->getUser(),
                'name' => $user->getName(),
                'email' => $user->getEmail(),
                'isAdmin' => $user->getIsAdmin(),
                'photoURL' => $user->getPhotoURL(),
                'canStream' => $user->getCanStream(),
                'canUpload' => $user->getCanUpload(),
                'channelName' => $user->getChannelName(),
                'emailVerified' => $user->getEmailVerified(),
                'views_count' => 0,
                'rrating' => "",
                'users_id' => $value['users_id'],
                'type' => 'ready',
                'title' => $value['title'],
                'clean_title' => cleanURLName($value['title']),
                'poster' => @$value['poster'],
                'thumbsJpgSmall' => @$value['poster'],
                'href' => @$value['href'],
                'link' => @$value['link'],
                'imgGif' => @$value['imgGif'],
                'className' => @$value['className'],
                'galleryCallback' => @$value['callback'],
                'stats' => $value,
                'embedlink' => addQueryStringParameter($value['href'], 'embed', 1),
                'images' => array(
                    "poster" => @$value['poster'],
                    "posterPortrait" => @$value['poster'],
                    "posterPortraitPath" => @$value['poster'],
                    "posterPortraitThumbs" => @$value['poster'],
                    "posterPortraitThumbsSmall" => @$value['poster'],
                    "thumbsGif" => @$value['imgGif'],
                    "gifPortrait" => @$value['imgGif'],
                    "thumbsJpg" => @$value['poster'],
                    "thumbsJpgSmall" => @$value['poster'],
                    "spectrumSource" => false,
                    "posterLandscape" => @$value['poster'],
                    "posterLandscapePath" => @$value['poster'],
                    "posterLandscapeThumbs" => @$value['poster'],
                    "posterLandscapeThumbsSmall" => @$value['poster']
                ),
                'videos' => array(
                    "m3u8" => array(
                        "url" => $m3u8,
                        "url_noCDN" => $m3u8,
                        "type" => "video",
                        "format" => "m3u8",
                        "resolution" => "auto"
                    )
                ),
                'Poster' => @$value['poster'],
                'Thumbnail' => @$value['poster'],
                'createdHumanTiming' => 'Live',
                "videoLink" => "",
                "next_videos_id" => null,
                "isSuggested" => 0,
                "trailer1" => "",
                "trailer2" => "",
                "trailer3" => "",
                "total_seconds_watching" => 0,
                "duration" => 'Live',
                "type" => 'Live',
                "duration_in_seconds" => 0,
                "likes" => 0,
                "dislikes" => 0,
                "users_id_company" => null,
                "iconClass" => $cat->getIconClass(),
                "category" => $cat->getName(),
                "clean_category" => $cat->getClean_name(),
                "category_description" => $cat->getDescription(),
                "videoCreation" => date('Y-m-d H:i:s'),
                "videoModified" => date('Y-m-d H:i:s'),
                "groups" => array(),
                "tags" => array(),
                "videoTags" => [
                    array(
                        "type_name" => "Starring",
                        "name" => ""
                    ),
                    array(
                        "type_name" => "Language",
                        "name" => "English"
                    ),
                    array(
                        "type_name" => "Release_Date",
                        "name" => date('Y')
                    ),
                    array(
                        "type_name" => "Running_Time",
                        "name" => ""
                    ),
                    array(
                        "type_name" => "Genres",
                        "name" => $cat->getName()
                    )
                ],
                "videoTagsObject" => array('Starring' => array(), 'Language' => array("English"), 'Release_Date' => array(date('Y')), 'Running_Time' => array('0'), 'Genres' => array($cat->getName())),
                'descriptionHTML' => '',
                "progress" => array(
                    "percent" => 0,
                    "lastVideoTime" => 0
                ),
                "isFavorite" => null,
                "isWatchLater" => null,
                "favoriteId" => null,
                "watchLaterId" => null,
                "total_seconds_watching_human" => "",
                "views_count_short" => "",
                "identification" => $user->getNameIdentificationBd(),
                "UserPhoto" => $user->getPhotoURL(),
                "isSubscribed" => true,
                "subtitles" => [],
                "subtitlesSRT" => [],
                "comments" => [],
                "commentsTotal" => 0,
                "subscribers" => 1,
                'relatedVideos' => [],
                "wwbnURL" => @$value['href'],
                "wwbnEmbedURL" => addQueryStringParameter($value['href'], 'embed', 1),
                "wwbnImgThumbnail" => @$value['poster'],
                "wwbnImgPoster" => @$value['poster'],
                "wwbnTitle" => $value['title'],
                "wwbnDescription" => '',
                "wwbnChannelURL" => $user->getChannelLink(),
                "wwbnImgChannel" => $user->getPhoto(),
                "wwbnType" => "live",
            );

            $videos[] = $video;
        }
    }
    //var_dump($videos);exit;
    return $videos;
}

function getStatsNotifications($force_recreate = false, $listItIfIsAdminOrOwner = true) {
    global $__getStatsNotifications__;
    $isLiveEnabled = AVideoPlugin::isEnabledByName('Live');
    $cacheName = "getStats" . DIRECTORY_SEPARATOR . "getStatsNotifications";
    unset($_POST['sort']);
    if ($force_recreate) {
        if ($isLiveEnabled) {
            Live::deleteStatsCache();
        }
    } else {
        if (!empty($__getStatsNotifications__)) {
            return $__getStatsNotifications__;
        }
        $json = ObjectYPT::getCache($cacheName, 0, true);
    }
    if ($isLiveEnabled && (empty($json) || !empty($json->error) || !isset($json->error))) {
        //_error_log('getStatsNotifications: 1 ' . json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)));
        $json = Live::getStats();
        $json = object_to_array($json);
        // make sure all the applications are listed on the same array, even from different live servers
        if (empty($json['applications']) && is_array($json)) {
            $oldjson = $json;
            $json = [];
            $json['applications'] = [];
            $json['hidden_applications'] = [];
            foreach ($oldjson as $key => $value) {
                if (!empty($value['applications'])) {
                    $json['applications'] = array_merge($json['applications'], $value['applications']);
                }
                if (!empty($value['hidden_applications'])) {
                    $json['hidden_applications'] = array_merge($json['hidden_applications'], $value['hidden_applications']);
                }
                unset($json[$key]);
            }
        }

        $appArray = AVideoPlugin::getLiveApplicationArray();
        if (!empty($appArray)) {
            if (empty($json)) {
                $json = [];
            }
            $json['error'] = false;
            if (empty($json['msg'])) {
                $json['msg'] = "OFFLINE";
            }
            $json['nclients'] = count($appArray);
            if (empty($json['applications'])) {
                $json['applications'] = [];
            }
            $json['applications'] = array_merge($json['applications'], $appArray);
        }

        $count = 0;
        if (!isset($json['total'])) {
            $json['total'] = 0;
        }
        if (!empty($json['applications'])) {
            $json['total'] += count($json['applications']);
        }
        while (!empty($json[$count])) {
            $json['total'] += count($json[$count]['applications']);
            $count++;
        }
        if (!empty($json['applications'])) {
            $applications = array();
            foreach ($json['applications'] as $key => $value) {
                // remove duplicated
                if (in_array($value['href'], $applications)) {
                    unset($json['applications'][$key]);
                    continue;
                }
                $applications[] = $value['href'];
                if (empty($value['users_id']) && !empty($value['user'])) {
                    $u = User::getFromUsername($value['user']);
                    $json['applications'][$key]['users_id'] = $u['id'];
                }
                /*
                 * It was creating a duplicated records
                  if (!empty($json['applications'][$key]['key']) && $json['applications'][$key]["type"] === "live") {
                  // make sure it is online
                  $lth = new LiveTransmitionHistory();
                  $lth->setTitle($json['applications'][$key]['title']);
                  $lth->setKey($json['applications'][$key]['key']);
                  $lth->setUsers_id($json['applications'][$key]['users_id']);
                  $lth->setLive_servers_id($json['applications'][$key]['live_servers_id']);
                  $json['applications'][$key]['live_transmitions_history_id'] = $lth->save();
                  }
                 * 
                 */
            }
        }
        $cache = ObjectYPT::setCache($cacheName, $json);
        Live::unfinishAllFromStats();
        //_error_log('Live::createStatsCache ' . json_encode($cache));
    } else {
        //_error_log('getStatsNotifications: 2 cached result');
        $json = object_to_array($json);
    }

    if (empty($json['applications'])) {
        $json['applications'] = array();
    }

    foreach ($json['applications'] as $key => $value) {
        if (!Live::isApplicationListed(@$value['key'], $listItIfIsAdminOrOwner)) {
            $json['hidden_applications'][] = $value;
            unset($json['applications'][$key]);
        }
    }
    if (!empty($json['applications']) && is_array($json['applications'])) {
        $json['countLiveStream'] = count($json['applications']);
    } else {
        $json['countLiveStream'] = 0;
    }
    $json['timezone'] = date_default_timezone_get();
    $__getStatsNotifications__ = $json;
    return $json;
}

function getSocketConnectionLabel() {
    $html = '<span class="socketStatus">
            <span class="socket_icon socket_loading_icon">
                <i class="fas fa-sync fa-spin"></i>
            </span>
            <span class="socket_icon socket_not_loading socket_disconnected_icon">
                <span class="fa-stack">
  <i class="fas fa-slash fa-stack-1x"></i>
  <i class="fas fa-plug fa-stack-1x"></i>
</span> ' . __('Disconnected') . '
            </span>
            <span class="socket_icon socket_not_loading socket_connected_icon">
                <span class="fa-stack">
  <i class="fas fa-plug fa-stack-1x"></i>
</span>  ' . __('Connected') . '
            </span>
        </span>';
    return $html;
}

function getSocketVideoClassName($videos_id) {
    return 'total_on_videos_id_' . $videos_id;
}

function getSocketLiveClassName($key, $live_servers_id) {
    return 'total_on_live_' . $key . '_' . intval($live_servers_id);
}

function getSocketLiveLinksClassName($live_links_id) {
    return 'total_on_live_links_id_' . $live_links_id;
}

function getLiveUsersLabelVideo($videos_id, $totalViews = null, $viewsClass = "label label-default", $counterClass = "label label-primary") {
    global $global;
    $label = '';
    if (AVideoPlugin::isEnabledByName('LiveUsers') && method_exists("LiveUsers", "getLabels")) {
        $label .= LiveUsers::getLabels(getSocketVideoClassName($videos_id), $totalViews, $viewsClass, $counterClass, 'video');
    }
    return $label;
}

function getLiveUsersLabelLive($key, $live_servers_id, $viewsClass = "label label-default", $counterClass = "label label-primary") {
    if (AVideoPlugin::isEnabledByName('LiveUsers') && method_exists("LiveUsers", "getLabels")) {
        $totalViews = 0;
        if (User::isLogged()) {
            $totalViews = LiveUsers::getTotalUsers($key, $live_servers_id);
        }
        return LiveUsers::getLabels(getSocketLiveClassName($key, $live_servers_id), $totalViews, $viewsClass, $counterClass, 'live');
    }
}

function getLiveUsersLabelLiveLinks($liveLinks_id, $totalViews = null, $viewsClass = "label label-default", $counterClass = "label label-primary") {
    if (AVideoPlugin::isEnabledByName('LiveUsers') && method_exists("LiveUsers", "getWatchingNowLabel")) {
        return LiveUsers::getWatchingNowLabel(getSocketLiveLinksClassName($liveLinks_id), "label label-primary", '', $viewsClass, 'livelinks');
    }
}

function getLiveUsersLabel($viewsClass = "label label-default", $counterClass = "label label-primary") {
    if (AVideoPlugin::isEnabledByName('LiveUsers')) {
        $live = isLive();
        if (!empty($live)) {
            if (!empty($live['key'])) {
                return getLiveUsersLabelLive($live['key'], $live['live_servers_id'], $viewsClass, $counterClass);
            } elseif (!empty($live['liveLinks_id'])) {
                return getLiveUsersLabelLiveLinks($live['liveLinks_id'], null, $viewsClass, $counterClass);
            }
        } else {
            $videos_id = getVideos_id();
            if (!empty($videos_id)) {
                $v = new Video("", "", $videos_id);
                $totalViews = $v->getViews_count();
                return getLiveUsersLabelVideo($videos_id, $totalViews, $viewsClass, $counterClass);
            }
        }
    }
    return "";
}

function getLiveUsersLabelHTML($viewsClass = "label label-default", $counterClass = "label label-primary") {
    global $global, $_getLiveUsersLabelHTML;
    if (!empty($_getLiveUsersLabelHTML)) {
        return '';
    }
    $_getLiveUsersLabelHTML = 1;

    $htmlMediaTag .= '<div style="z-index: 999; position: absolute; top:5px; left: 5px; opacity: 0.8; filter: alpha(opacity=80);" class="liveUsersLabel">';
    $htmlMediaTag .= getIncludeFileContent($global['systemRootPath'] . 'plugin/Live/view/onlineLabel.php', array('viewsClass' => $viewsClass, 'counterClass' => $counterClass));
    $htmlMediaTag .= getLiveUsersLabel($viewsClass, $counterClass);
    $htmlMediaTag .= '</div>';
    return $htmlMediaTag;
}

function getHTMLTitle($titleArray) {
    global $config, $global;

    if (!is_array($titleArray)) {
        $titleArray = [];
    }
    $titleArray[] = $config->getWebSiteTitle();

    $title = implode($config->getPageTitleSeparator(), $titleArray);
    $global['pageTitle'] = $title;
    return "<title>{$title}</title>";
}

function getButtonSignInAndUp() {
    $signIn = getButtonSignIn();
    $signUp = getButtonSignUp();
    $html = $signIn . $signUp;
    if (!empty($signIn) && !empty($signIn)) {
        return '<div class="btn-group justified">' . $html . '</div>';
    } else {
        return $html;
    }
}

function getButtonSignUp() {
    global $global;
    $obj = AVideoPlugin::getDataObject('CustomizeUser');
    if (!empty($obj->disableNativeSignUp)) {
        return '';
    }

    $url = $global['webSiteRootURL'] . 'signUp';
    $url = addQueryStringParameter($url, 'redirectUri', getRedirectUri());

    $html = '<a class="btn navbar-btn btn-default" href="' . $url . '" ><i class="fas fa-user-plus"></i> ' . __("Sign Up") . '</a> ';
    return $html;
}

function getButtonSignIn() {
    global $global;
    $obj = AVideoPlugin::getDataObject('CustomizeUser');
    if (!empty($obj->disableNativeSignIn)) {
        return '';
    }

    $url = $global['webSiteRootURL'] . 'user';
    $url = addQueryStringParameter($url, 'redirectUri', getRedirectUri());

    $html = '<a class="btn navbar-btn btn-success" href="' . $url . '" ><i class="fas fa-sign-in-alt" ></i> ' . __("Sign In") . '</a> ';
    return $html;
}

function getTitle() {
    global $global;
    if (empty($global['pageTitle'])) {
        $url = getSelfURI();

        $global['pageTitle'] = str_replace($global['webSiteRootURL'], '', $url);

        if (preg_match('/\/plugin\/([^\/])/i', $url, $matches)) {
            $global['pageTitle'] = __('Plugin') . ' ' . __($matches[1]);
        }

        $title = $global['pageTitle'];
    }

    return $global['pageTitle'];
}

function outputAndContinueInBackground($msg = '') {
    global $outputAndContinueInBackground;

    if (!empty($outputAndContinueInBackground)) {
        return false;
    }
    $outputAndContinueInBackground = 1;
    @session_write_close();
    //_mysql_close();
    // Instruct PHP to continue execution
    ignore_user_abort(true);
    if (function_exists('fastcgi_finish_request')) {
        fastcgi_finish_request();
    }
    _ob_start();
    echo $msg;
    @header("Connection: close");
    @header("Content-Length: " . ob_get_length());
    @header("HTTP/1.1 200 OK");
    ob_end_flush();
    flush();
}

function cleanUpRowFromDatabase($row) {
    if (is_array($row)) {
        foreach ($row as $key => $value) {
            if (preg_match('/pass/i', $key)) {
                unset($row[$key]);
            }
        }
    }
    return $row;
}

function getImageTransparent1pxURL() {
    global $global;
    return getCDN() . "view/img/transparent1px.png";
}

function getDatabaseTime() {
    global $global, $_getDatabaseTime;
    if (isset($_getDatabaseTime)) {
        return $_getDatabaseTime;
    }
    $sql = "SELECT CURRENT_TIMESTAMP";
    $res = sqlDAL::readSql($sql);
    $data = sqlDAL::fetchAssoc($res);
    sqlDAL::close($res);
    if ($res) {
        $row = $data;
    } else {
        $row = false;
    }
    $_getDatabaseTime = strtotime($row['CURRENT_TIMESTAMP']);
    return $_getDatabaseTime;
}

function getDatabaseTimezoneName() {
    global $global, $_getDatabaseTimezoneName;
    if (isset($_getDatabaseTimezoneName)) {
        return $_getDatabaseTimezoneName;
    }
    $sql = "SELECT @@system_time_zone as time_zone";
    $res = sqlDAL::readSql($sql);
    $data = sqlDAL::fetchAssoc($res);
    sqlDAL::close($res);
    if ($res) {
        $_getDatabaseTimezoneName = $data['time_zone'];
    } else {
        $_getDatabaseTimezoneName = false;
    }

    if ($_getDatabaseTimezoneName == 'PDT' || $_getDatabaseTimezoneName == 'PST') {
        $_getDatabaseTimezoneName = 'America/Los_Angeles';
    }else if ($_getDatabaseTimezoneName == 'EDT' || $_getDatabaseTimezoneName == 'EST') {
        $_getDatabaseTimezoneName = 'America/New_York';
    }else if ($_getDatabaseTimezoneName == 'CDT' || $_getDatabaseTimezoneName == 'CST') {
        $_getDatabaseTimezoneName = 'America/Chicago';
    }else if ($_getDatabaseTimezoneName == 'CEST') {
        $_getDatabaseTimezoneName = 'Europe/Madrid';
    }

    return $_getDatabaseTimezoneName;
}

function get_js_availableLangs() {
    global $global;
    if (empty($global['js_availableLangs'])) {
        include_once $global['systemRootPath'] . 'objects/bcp47.php';
    }
    return $global['js_availableLangs'];
}

function listAllWordsToTranslate() {
    global $global;
    $cacheName = 'listAllWordsToTranslate';
    $cache = ObjectYPT::getCache($cacheName, 0);
    if (!empty($cache)) {
        return object_to_array($cache);
    }
    ini_set('max_execution_time', 300);

    function listAll($dir) {
        $vars = [];
        if (preg_match('/vendor.*$/', $dir)) {
            return $vars;
        }
        if ($handle = opendir($dir)) {
            while (false !== ($entry = readdir($handle))) {
                if ($entry !== '.' && $entry !== '..') {
                    $filename = $dir . '/' . $entry;
                    if (is_dir($filename)) {
                        $vars = listAll($filename);
                    } elseif (preg_match("/\.php$/", $entry)) {
                        $data = file_get_contents($filename);
                        $regex = '/__\(["\']{1}(.*)["\']{1}\)/U';
                        preg_match_all(
                                $regex,
                                $data,
                                $matches
                        );

                        foreach ($matches[0] as $key => $value) {
                            $vars[$matches[1][$key]] = $matches[1][$key];
                        }
                    }
                }
            }

            closedir($handle);
        }
        return $vars;
    }

    $vars = listAll($global['systemRootPath']);
    sort($vars);
    ObjectYPT::setCache($cacheName, $vars);
    return $vars;
}

function secondsInterval($time1, $time2) {
    if (!is_numeric($time1)) {
        $time1 = strtotime($time1);
    }
    if (!is_numeric($time2)) {
        $time2 = strtotime($time2);
    }

    return $time1 - $time2;
}

function secondsIntervalHuman($time, $useDatabaseTime = true) {
    $dif = secondsIntervalFromNow($time, $useDatabaseTime);
    if ($dif < 0) {
        return humanTimingAfterwards($time, 0, $useDatabaseTime);
    } else {
        return humanTimingAgo($time, 0, $useDatabaseTime);
    }
}

function isTimeForFuture($time, $useDatabaseTime = true) {
    $dif = secondsIntervalFromNow($time, $useDatabaseTime);
    if ($dif < 0) {
        return true;
    } else {
        return false;
    }
}

function secondsIntervalFromNow($time, $useDatabaseTimeOrTimezoneString = true) {
    $timeNow = time();
    //var_dump($time, $useDatabaseTimeOrTimezoneString);
    if (!empty($useDatabaseTimeOrTimezoneString)) {
        if (is_numeric($useDatabaseTimeOrTimezoneString) || is_bool($useDatabaseTimeOrTimezoneString)) {
            //echo $time . '-' . __LINE__ . '=>';
            $timeNow = getDatabaseTime();
        } elseif (is_string($useDatabaseTimeOrTimezoneString)) {
            //echo '-' . __LINE__ . PHP_EOL . PHP_EOL;
            $timeNow = getTimeInTimezone($timeNow, $useDatabaseTimeOrTimezoneString);
        }
    }
    return secondsInterval($timeNow, $time);
}

function getScriptRunMicrotimeInSeconds() {
    global $global;
    $time_now = microtime(true);
    return ($time_now - $global['avideoStartMicrotime']);
}

function fixSystemPath() {
    global $global;
    $global['systemRootPath'] = fixPath($global['systemRootPath']);
}

function fixPath($path, $addLastSlash = false) {
    if (empty($path)) {
        return false;
    }
    if (isWindows()) {
        $path = str_replace('/', DIRECTORY_SEPARATOR, $path);
        $path = str_replace('\\\\\\', DIRECTORY_SEPARATOR, $path);
    } else {
        $path = str_replace('\\', DIRECTORY_SEPARATOR, $path);
    }
    if ($addLastSlash) {
        $path = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }
    return $path;
}

function idToHash($id) {
    global $global, $_idToHash;

    if (!isset($_idToHash)) {
        $_idToHash = [];
    }

    if (!empty($_idToHash[$id])) {
        return $_idToHash[$id];
    }

    if (!empty($global['useLongHash'])) {
        $base = 2;
        $cipher_algo = 'des';
    } else {
        $base = 32;
        $cipher_algo = 'rc4';
    }
    if (empty($global['salt'])) {
        $global['salt'] = '11234567890abcdef';
    }
    $idConverted = base_convert($id, 10, $base);
    $hash = (@openssl_encrypt($idConverted, $cipher_algo, $global['salt']));
    //$hash = preg_replace('/^([+]+)/', '', $hash);
    $hash = preg_replace('/(=+)$/', '', $hash);
    $hash = str_replace(['/', '+', '='], ['_', '-', '.'], $hash);
    //return base64_encode($hash);
    $_idToHash[$id] = $hash;
    return $hash;
}

function hashToID($hash) {
    global $global;
    if (!empty($global['useLongHash'])) {
        $base = 2;
        $cipher_algo = 'des';
    } else {
        $base = 32;
        $cipher_algo = 'rc4';
    }
    //$hash = str_pad($hash,  4, "=");
    $hash = str_replace(['_', '-', '.'], ['/', '+', '='], $hash);
    //$hash = base64_decode($hash);
    $decrypt = openssl_decrypt(($hash), $cipher_algo, $global['salt']);
    $decrypt = base_convert($decrypt, $base, 10);
    return intval($decrypt);
}

function videosHashToID($hash_of_videos_id) {
    if (is_int($hash_of_videos_id)) {
        return $hash_of_videos_id;
    }
    if (!is_string($hash_of_videos_id) && !is_numeric($hash_of_videos_id)) {
        if (is_array($hash_of_videos_id)) {
            return $hash_of_videos_id;
        } else {
            return 0;
        }
    }
    if (preg_match('/^\.([0-9a-z._-]+)/i', $hash_of_videos_id, $matches)) {
        $hash_of_videos_id = hashToID($matches[1]);
    }
    return $hash_of_videos_id;
}

/**
 *
 * @global type $advancedCustom
 * @global type $global
 * @global type $_getCDNURL
 * @param type $type enum(CDN, CDN_S3,CDN_B2,CDN_FTP,CDN_YPTStorage,CDN_Live,CDN_LiveServers)
 * @param type $id the ID of the URL in case the CDN is an array
 * @return \type
 */
function getCDN($type = 'CDN', $id = 0) {
    global $advancedCustom, $global, $_getCDNURL;
    $index = $type . $id;
    if (!isset($_getCDNURL)) {
        $_getCDNURL = [];
    }
    if (empty($_getCDNURL[$index])) {
        if (!empty($type) && class_exists('AVideoPlugin') && AVideoPlugin::isEnabledByName('CDN')) {
            $_getCDNURL[$index] = CDN::getURL($type, $id);
        }
    }
    if ($type == 'CDN') {
        if (!empty($global['ignoreCDN'])) {
            return $global['webSiteRootURL'];
        } elseif (!empty($advancedCustom) && isValidURL($advancedCustom->videosCDN)) {
            $_getCDNURL[$index] = addLastSlash($advancedCustom->videosCDN);
        } elseif (empty($_getCDNURL[$index])) {
            $_getCDNURL[$index] = $global['webSiteRootURL'];
        }
    }
    //var_dump($type, $id, $_getCDNURL[$index]);
    return empty($_getCDNURL[$index]) ? false : $_getCDNURL[$index];
}

function getURL($relativePath, $ignoreCDN = false) {
    global $global;
    $relativePath = str_replace('\\', '/', $relativePath);
    $relativePath = getRelativePath($relativePath);
    if (!isset($_SESSION['user']['sessionCache']['getURL'])) {
        $_SESSION['user']['sessionCache']['getURL'] = [];
    }
    if (!empty($_SESSION['user']['sessionCache']['getURL'][$relativePath])) {
        return $_SESSION['user']['sessionCache']['getURL'][$relativePath];
    }

    $file = "{$global['systemRootPath']}{$relativePath}";
    if (empty($ignoreCDN)) {
        $url = getCDN() . $relativePath;
    } else {
        $url = $global['webSiteRootURL'] . $relativePath;
    }
    if (file_exists($file)) {
        $cache = @filemtime($file) . '_' . @filectime($file);
        $url = addQueryStringParameter($url, 'cache', $cache);
        $_SESSION['user']['sessionCache']['getURL'][$relativePath] = $url;
    } else {
        $url = addQueryStringParameter($url, 'cache', 'not_found');
    }

    return $url;
}

function getCDNOrURL($url, $type = 'CDN', $id = 0) {
    if (!preg_match('/^http/i', $url)) {
        return $url;
    }
    $cdn = getCDN($type, $id);
    if (!empty($cdn)) {
        return $cdn;
    }
    return addLastSlash($url);
}

function replaceCDNIfNeed($url, $type = 'CDN', $id = 0) {
    $cdn = getCDN($type, $id);
    if (!empty($_GET['debug'])) {
        $obj = AVideoPlugin::getDataObject('Blackblaze_B2');
        var_dump($url, $type, $id, $cdn, $obj->CDN_Link);
        exit;
    }
    if (empty($cdn)) {
        if ($type == 'CDN_B2') {
            $obj = AVideoPlugin::getDataObject('Blackblaze_B2');
            if (isValidURL($obj->CDN_Link)) {
                $basename = basename($url);
                return addLastSlash($obj->CDN_Link) . $basename;
            }
        } else if ($type == 'CDN_S3') {
            $obj = AVideoPlugin::getDataObject('AWS_S3');
            if (isValidURL($obj->CDN_Link)) {
                $cdn = $obj->CDN_Link;
            }
        }
        if (empty($cdn)) {
            return $url;
        }
    }

    return str_replace(parse_url($url, PHP_URL_HOST), parse_url($cdn, PHP_URL_HOST), $url);
}

function isIPPrivate($ip) {
    if ($ip == '192.168.1.4') {
        return false;
    }
    if (!filter_var($ip, FILTER_VALIDATE_IP)) {
        return false;
    }
    $result = filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
    );
    if (empty($result)) {
        return true;
    }
    return false;
}

function countDownPage($toTime, $message, $image, $bgImage, $title) {
    global $global;
    include $global['systemRootPath'] . 'objects/functionCountDownPage.php';
    exit;
}

function inputToRequest() {
    $content = file_get_contents("php://input");
    if (!empty($content)) {
        $json = json_decode($content);
        if (empty($json)) {
            return false;
        }
        foreach ($json as $key => $value) {
            if (!isset($_REQUEST[$key])) {
                $_REQUEST[$key] = $value;
            }
        }
    }
}

function useVideoHashOrLogin() {
    if (!empty($_REQUEST['video_id_hash'])) {
        $videos_id = Video::getVideoIdFromHash($_REQUEST['video_id_hash']);
        if (!empty($videos_id)) {
            $users_id = Video::getOwner($videos_id);
            $user = new User($users_id);
            _error_log("useVideoHashOrLogin: $users_id, $videos_id");
            return $user->login(true);
        }
    }
    return User::loginFromRequest();
}

function strip_specific_tags($string, $tags_to_strip = ['script', 'style', 'iframe', 'object', 'applet', 'link']) {
    foreach ($tags_to_strip as $tag) {
        $string = preg_replace('/<' . $tag . '[^>]*>(.*?)<\/' . $tag . '>/s', '$1', $string);
    }
    return $string;
}

function strip_render_blocking_resources($string) {
    $tags_to_strip = ['link', 'style'];
    $head = preg_match('/<head>(.*)<\/head>/s', $string, $matches);
    $string = str_replace($matches[0], '{_head_}', $string);
    foreach ($tags_to_strip as $tag) {
        $string = preg_replace('/<' . $tag . '[^>]*>(.*?)<\/' . $tag . '>/s', '', $string);
        $string = preg_replace('/<' . $tag . '[^>]*\/>/s', '', $string);
    }
    $string = str_replace('{_head_}', $matches[0], $string);
    return $string;
}

function optimizeHTMLTags($html) {
    return $html;
    //$html = optimizeCSS($html);
    //$html = optimizeJS($html);
    return $html . '<--! optimized -->';
}

function optimizeCSS($html) {
    global $global;
    $css = '';
    $cacheDir = getVideosDir() . 'cache/';
    $cacheName = md5(getSelfURI() . User::getId()) . '.css';
    $filename = "{$cacheDir}{$cacheName}";
    $urlname = "{$global['webSiteRootURL']}videos/cache/{$cacheName}";
    $HTMLTag = "<link href=\"{$urlname}\" rel=\"stylesheet\" type=\"text/css\"/>";
    $fileExists = file_exists($filename);
    //$fileExists = false;
    // get link tags
    $pattern = '/((<(link)[^>]*(stylesheet|css)[^>]*\/>)|(<(style)[^>]*>([^<]+)<\/style>))/i';
    preg_match_all($pattern, $html, $matches);
    foreach ($matches[3] as $key => $type) {
        if (strtolower($type) == 'link') {
            $linkTag = $matches[0][$key];
            $pattern = '/href=.(http[^"\']+)/i';
            preg_match($pattern, $linkTag, $href);
            if (empty($href)) {
                continue;
            }
            if (!$fileExists) {
                $content = url_get_contents($href[1]);
                if (empty($content)) {
                    continue;
                }
                $css .= PHP_EOL . " /* link {$href[1]} */ " . $content;
            }
            $html = str_replace($linkTag, '', $html);
        } else {
            if (!$fileExists) {
                $css .= PHP_EOL . ' /* style */ ' . $matches[7][$key];
            }
            $html = str_replace($matches[1][$key], '', $html);
        }
    }
    if (!$fileExists) {
        _file_put_contents($filename, $css);
    }
    return str_replace('</title>', '</title><!-- optimized CSS -->' . PHP_EOL . $HTMLTag . PHP_EOL . '', $html);
}

function optimizeJS($html) {
    global $global;
    $js = '';
    $cacheDir = getVideosDir() . 'cache/';
    $cacheName = md5(getSelfURI() . User::getId()) . '.js';
    $filename = "{$cacheDir}{$cacheName}";
    $urlname = "{$global['webSiteRootURL']}videos/cache/{$cacheName}";
    $HTMLTag = "<script src=\"{$urlname}\"></script>";
    $fileExists = file_exists($filename);
    $fileExists = false;
    // get link tags
    $pattern = '/((<script[^>]+(src=[^ ]+)[^>]*>( *)<\/script>)|(<script[^>]*>([^<]+)<\/script>))/si';
    preg_match_all($pattern, $html, $matches);
    foreach ($matches[2] as $key => $type) {
        if (empty($type)) {
            if (preg_match('/application_ld_json/i', $matches[1][$key])) {
                continue;
            }
            $js .= PHP_EOL . " /* js */ " . $matches[6][$key];
            $html = str_replace($matches[1][$key], '', $html);
        } else {
            $pattern = '/src=.(http[^"\']+)/i';
            preg_match($pattern, $type, $href);
            if (empty($href)) {
                continue;
            }
            if (preg_match('/(jquery|video-js|videojs)/i', $href[1])) {
                continue;
            }
            if (!$fileExists) {
                $content = url_get_contents($href[1]);
                if (empty($content)) {
                    continue;
                }
                $js .= PHP_EOL . " /* js link {$href[1]} */ " . $content;
            }
            $html = str_replace($type, '', $html);
        }
    }
    if (!$fileExists) {
        _file_put_contents($filename, $js);
    }
    return str_replace('</body>', '<!-- optimized JS -->' . PHP_EOL . $HTMLTag . PHP_EOL . '</body>', $html);
}

function mysqlBeginTransaction() {
    global $global;
    _error_log('Begin transaction ' . getSelfURI());
    $global['mysqli']->autocommit(false);
}

function mysqlRollback() {
    global $global;
    _error_log('Rollback transaction ' . getSelfURI(), AVideoLog::$ERROR);
    $global['mysqli']->rollback();
    $global['mysqli']->autocommit(true);
}

function mysqlCommit() {
    global $global;
    _error_log('Commit transaction ' . getSelfURI());
    $global['mysqli']->commit();
    $global['mysqli']->autocommit(true);
}

function number_format_short($n, $precision = 1) {
    $n = floatval($n);
    if ($n < 900) {
        // 0 - 900
        $n_format = number_format($n, $precision);
        $suffix = '';
    } elseif ($n < 900000) {
        // 0.9k-850k
        $n_format = number_format($n / 1000, $precision);
        $suffix = 'K';
    } elseif ($n < 900000000) {
        // 0.9m-850m
        $n_format = number_format($n / 1000000, $precision);
        $suffix = 'M';
    } elseif ($n < 900000000000) {
        // 0.9b-850b
        $n_format = number_format($n / 1000000000, $precision);
        $suffix = 'B';
    } else {
        // 0.9t+
        $n_format = number_format($n / 1000000000000, $precision);
        $suffix = 'T';
    }

    // Remove unnecessary zeroes after decimal. "1.0" -> "1"; "1.00" -> "1"
    // Intentionally does not affect partials, eg "1.50" -> "1.50"
    if ($precision > 0) {
        $dotzero = '.' . str_repeat('0', $precision);
        $n_format = str_replace($dotzero, '', $n_format);
    }

    return $n_format . $suffix;
}

function seconds2human($ss) {
    $s = $ss % 60;
    $m = floor(($ss % 3600) / 60);
    $h = floor(($ss % 86400) / 3600);
    $d = floor(($ss % 2592000) / 86400);
    $M = floor($ss / 2592000);

    $times = [];

    if (!empty($M)) {
        $times[] = "$M " . __('m');
    }
    if (!empty($d)) {
        $times[] = "$d " . __('d');
    }
    if (!empty($h)) {
        $times[] = "$h " . __('h');
    }
    if (!empty($m)) {
        $times[] = "$m " . __('min');
    }
    if (!empty($s)) {
        $times[] = "$s " . __('sec');
    }

    return implode(', ', $times);
}

/**
 * convert a time in a timezone into my time
 * @param type $time
 * @param type $timezone
 * @return type
 */
function getTimeInTimezone($time, $timezone) {
    if (!is_numeric($time)) {
        $time = strtotime($time);
    }
    if (empty($timezone) || empty(date_default_timezone_get()) || $timezone == date_default_timezone_get()) {
        return $time;
    }
    $date = new DateTime(date('Y-m-d H:i:s', $time));
    $date->setTimezone(new DateTimeZone($timezone));
    //$date->setTimezone(date_default_timezone_get());
    $dateString = $date->format('Y-m-d H:i:s');
    return strtotime($dateString);
}

function listFolderFiles($dir) {
    if (empty($dir)) {
        return [];
    }
    $ffs = scandir($dir);

    unset($ffs[array_search('.', $ffs, true)]);
    unset($ffs[array_search('..', $ffs, true)]);

    $files = [];
    // prevent empty ordered elements
    if (count($ffs) >= 1) {
        foreach ($ffs as $ff) {
            $dir = rtrim($dir, DIRECTORY_SEPARATOR);
            $file = $dir . DIRECTORY_SEPARATOR . $ff;
            if (is_dir($file)) {
                $files[] = listFolderFiles($file);
            } else {
                $files[] = $file;
            }
        }
    }
    return $files;
}

function convertToMyTimezone($date, $fromTimezone) {
    $time = getTimestampFromTimezone($date, $fromTimezone);
    return date('Y-m-d H:i:s', $time);
}

function getTimestampFromTimezone($date, $fromTimezone) {
    $date = new DateTime($date, new DateTimeZone($fromTimezone));
    return $date->getTimestamp();
}

function getCSSAnimation($type = 'animate__flipInX', $loaderSequenceName = 'default', $delay = 0.1) {
    global $_getCSSAnimationClassDelay;
    getCSSAnimationClassAndStyleAddWait($delay, $loaderSequenceName);
    return ['class' => 'animate__animated ' . $type, 'style' => "-webkit-animation-delay: {$_getCSSAnimationClassDelay[$loaderSequenceName]}s; animation-delay: {$_getCSSAnimationClassDelay[$loaderSequenceName]}s;"];
}

function getCSSAnimationClassAndStyleAddWait($delay, $loaderSequenceName = 'default') {
    global $_getCSSAnimationClassDelay;
    if (!isset($_getCSSAnimationClassDelay)) {
        $_getCSSAnimationClassDelay = [];
    }
    if (empty($_getCSSAnimationClassDelay[$loaderSequenceName])) {
        $_getCSSAnimationClassDelay[$loaderSequenceName] = 0;
    }
    $_getCSSAnimationClassDelay[$loaderSequenceName] += $delay;
}

function getCSSAnimationClassAndStyle($type = 'animate__flipInX', $loaderSequenceName = 'default', $delay = 0.1) {
    $array = getCSSAnimation($type, $loaderSequenceName, $delay);
    return "{$array['class']}\" style=\"{$array['style']}";
}

function isImage($file) {
    [$width, $height, $type, $attr] = getimagesize($file);
    if ($type == IMAGETYPE_PNG) {
        return 'png';
    }
    if ($type == IMAGETYPE_JPEG) {
        return 'jpg';
    }
    if ($type == IMAGETYPE_GIF) {
        return 'gif';
    }
    return false;
}

function isHTMLEmpty($html_string) {
    $html_string_no_tags = strip_specific_tags($html_string, ['br', 'p', 'span', 'div']);
    //var_dump($html_string_no_tags, $html_string);
    return empty(trim(str_replace(array("\r", "\n"), array('', ''), $html_string_no_tags)));
}

function emptyHTML($html_string) {
    return isHTMLEmpty($html_string);
}

function totalImageColors($image_path) {
    $img = imagecreatefromjpeg($image_path);
    $w = imagesx($img);
    $h = imagesy($img);

    // capture the raw data of the image
    _ob_start();
    imagegd2($img, null, $w);
    $data = _ob_get_clean();
    $totalLength = strlen($data);

    // calculate the length of the actual pixel data
    // from that we can derive the header size
    $pixelDataLength = $w * $h * 4;
    $headerLength = $totalLength - $pixelDataLength;

    // use each four-byte segment as the key to a hash table
    $counts = [];
    for ($i = $headerLength; $i < $totalLength; $i += 4) {
        $pixel = substr($data, $i, 4);
        $count = & $counts[$pixel];
        $count += 1;
    }
    $colorCount = count($counts);
    return $colorCount;
}

function isImageCorrupted($image_path) {
    $fsize = filesize($image_path);
    if (strpos($image_path, 'thumbsSmall') !== false) {
        if ($fsize < 1000) {
            return true;
        }
    } else {
        if ($fsize < 2000) {
            return true;
        }
    }

    if (totalImageColors($image_path) === 1) {
        return true;
    }

    if (!isGoodImage($image_path)) {
        return true;
    }
    return false;
}

// detect partial grey immages
function isGoodImage($fn) {
    [$w, $h] = getimagesize($fn);
    $im = imagecreatefromstring(file_get_contents($fn));
    $grey = 0;
    for ($i = 0; $i < 5; ++$i) {
        for ($j = 0; $j < 5; ++$j) {
            $x = $w - 5 + $i;
            $y = $h - 5 + $j;
            [$r, $g, $b] = array_values(imagecolorsforindex($im, imagecolorat($im, $x, $y)));
            if ($r == $g && $g == $b && $b == 128) {
                ++$grey;
            }
        }
    }
    return $grey < 12;
}

function defaultIsPortrait() {
    global $_defaultIsPortrait;

    if (!isset($_defaultIsPortrait)) {
        $_defaultIsPortrait = false;
        if ($obj = AVideoPlugin::getDataObjectIfEnabled('YouPHPFlix2') && empty($obj->landscapePosters)) {
            $_defaultIsPortrait = true;
        }
    }

    return $_defaultIsPortrait;
}

function isDummyFile($filePath) {
    global $_isDummyFile;

    if (!isset($_isDummyFile)) {
        $_isDummyFile = array();
    }
    if (isset($_isDummyFile[$filePath])) {
        return $_isDummyFile[$filePath];
    }

    $return = false;

    if (file_exists($filePath)) {
        $fileSize = filesize($filePath);
        if ($fileSize > 5 && $fileSize < 20) {
            $return = true;
        } else
        if ($fileSize < 100) {
            $return = preg_match("/Dummy File/i", file_get_contents($filePath));
        }
    }
    $_isDummyFile[$filePath] = $return;
    return $return;
}

function forbiddenPageIfCannotEmbed($videos_id) {
    global $customizedAdvanced, $advancedCustomUser, $global;
    if (empty($customizedAdvanced)) {
        $customizedAdvanced = AVideoPlugin::getObjectDataIfEnabled('CustomizeAdvanced');
    }
    if (empty($advancedCustomUser)) {
        $customizedAdvanced = AVideoPlugin::getObjectDataIfEnabled('CustomizeUser');
    }
    if (!isAVideoMobileApp()) {
        if (!isSameDomain(@$_SERVER['HTTP_REFERER'], $global['webSiteRootURL'])) {
            if (!empty($advancedCustomUser->blockEmbedFromSharedVideos) && !CustomizeUser::canShareVideosFromVideo($videos_id)) {
                $reason = array();
                if (!empty($advancedCustomUser->blockEmbedFromSharedVideos)) {
                    error_log("forbiddenPageIfCannotEmbed: Embed is forbidden: \$advancedCustomUser->blockEmbedFromSharedVideos");
                    $reason[] = __('Admin block video sharing');
                }
                if (!CustomizeUser::canShareVideosFromVideo($videos_id)) {
                    error_log("forbiddenPageIfCannotEmbed: Embed is forbidden: !CustomizeUser::canShareVideosFromVideo(\$video['id'])");
                    $reason[] = __('User block video sharing');
                }
                forbiddenPage("Embed is forbidden " . implode('<br>', $reason));
            }
        }

        $objSecure = AVideoPlugin::loadPluginIfEnabled('SecureVideosDirectory');
        if (!empty($objSecure)) {
            $objSecure->verifyEmbedSecurity();
        }
    }
}

function getMediaSessionPosters($imagePath) {
    global $global;
    if (empty($imagePath)) {
        return false;
    }
    $sizes = array(96, 128, 192, 256, 384, 512);

    $posters = array();

    foreach ($sizes as $value) {
        $destination = str_replace('.jpg', "_{$value}.jpg", $imagePath);
        $path = convertImageIfNotExists($imagePath, $destination, $value, $value);
        if (!empty($path)) {
            $convertedImage = convertImageIfNotExists($imagePath, $destination, $value, $value);
            $relativePath = str_replace($global['systemRootPath'], '', $convertedImage);
            $url = getURL($relativePath);
            $posters[$value] = array('path' => $path, 'relativePath' => $relativePath, 'url' => $url);
        }
    }
    return $posters;
}

function deleteMediaSessionPosters($imagePath) {
    if (empty($imagePath)) {
        return false;
    }
    $sizes = array(96, 128, 192, 256, 384, 512);

    foreach ($sizes as $value) {
        $destination = str_replace('.jpg', "_{$value}.jpg", $imagePath);
        @unlink($destination);
    }
}

function getMediaSession() {
    $MediaMetadata = new stdClass();
    $MediaMetadata->title = '';
    $videos_id = getVideos_id();
    if ($liveLink = isLiveLink()) {
        $MediaMetadata = LiveLinks::getMediaSession($liveLink);
    } else if ($live = isLive()) {
        $MediaMetadata = Live::getMediaSession($live['key'], $live['live_servers_id'], @$live['live_schedule_id']);
    } else if (!empty($videos_id)) {
        if (!empty($videos_id)) {
            $MediaMetadata = Video::getMediaSession($videos_id);
        } else {
            echo '<!-- mediaSession videos id is empty -->';
        }
    } else if (!empty($_REQUEST['videos_id'])) {
        $MediaMetadata = Video::getMediaSession($_REQUEST['videos_id']);
    } else if (!empty($_REQUEST['key'])) {
        $MediaMetadata = Live::getMediaSession($_REQUEST['key'], @$_REQUEST['live_servers_id'], @$_REQUEST['live_schedule_id']);
    }
    if (empty($MediaMetadata)) {
        $MediaMetadata->title = '';
    } else {
        $MediaMetadata->title = getSEOTitle($MediaMetadata->title);
    }
    return $MediaMetadata;
}

function _ob_start($force = false) {
    global $global;
    if (!isset($global['ob_start_callback'])) {
        $global['ob_start_callback'] = 'ob_gzhandler';
    } else {
        if (empty($global['ob_start_callback'])) {
            $global['ob_start_callback'] = null;
        }
    }
    if (!empty($global['ob_start_callback']) && empty($force) && ob_get_level()) {
        return false;
    }
    ob_start($global['ob_start_callback']);
}

/**
 *
  clear  return  send    stop
  ob_clean          x
  ob_end_clean      x                      x
  ob_end_flush                      x      x
  ob_flush                          x
  ob_get_clean      x        x             x  // should be called ob_get_end_clean
  ob_get_contents            x
  ob_get_flush               x      x
 */
function _ob_get_clean() {
    $content = ob_get_contents();
    _ob_end_clean();
    _ob_start();
    return $content;
}

function getIncludeFileContent($filePath, $varsArray = array()) {
    global $global, $config;
    if (!empty($global['getIncludeFileContent'])) {
        return getIncludeFileContentV2($filePath, $varsArray);
    } else {
        return getIncludeFileContentV1($filePath, $varsArray);
    }
}

function getIncludeFileContentV1($filePath, $varsArray = array()) {
    global $global, $config;
    foreach ($varsArray as $key => $value) {
        $$key = $value;
    }
    /*
      if(doesPHPVersioHasOBBug()){
      include $filePath;
      return '';
      }
     */

    _ob_start();
    if (!ob_get_level()) {
        _ob_start(true);
    }
    if (!ob_get_level()) {
        include $filePath;
        return '';
    }
    $__out = _ob_get_clean();
    if (!ob_get_level()) {
        echo $__out;
        include $filePath;
        return '';
    }
    //_ob_start();
    $basename = basename($filePath);
    $return = "<!-- {$basename} start -->";
    include $filePath;
    _ob_start();
    $returnOB = _ob_get_clean();
    $return .= "{$returnOB}<!-- {$basename} end -->";
    echo $__out;
    return $return;
}

function getIncludeFileContentV2($filePath, $varsArray = array()) {
    global $global, $config;
    foreach ($varsArray as $key => $value) {
        $$key = $value;
    }
    _ob_start();
    $__out = ob_get_contents();
    ob_clean();
    $basename = basename($filePath);
    $return = "<!-- {$basename} start -->";
    include $filePath;
    _ob_start();
    $return .= ob_get_contents();
    $return .= "<!-- {$basename} end -->";
    ob_clean();
    echo $__out;
    return $return;
}

//https://github.com/php/php-src/issues/8218
function doesPHPVersioHasOBBug() {
    if (version_compare(phpversion(), "8.1.4", "==")) {
        return true;
    } else if (version_compare(phpversion(), "8.0.17", "==")) {
        return true;
    } else {
        return false;
    }
}

//https://github.com/php/php-src/issues/8218#issuecomment-1072439915
function _ob_end_clean() {
    @ob_end_clean();
    header_remove("Content-Encoding");
    /*
      if(!doesPHPVersioHasOBBug()){
      header_remove("Content-Encoding");
      }
     */
}

function pluginsRequired($arrayPluginName, $featureName = '') {
    global $global;
    $obj = new stdClass();
    $obj->error = false;
    $obj->msg = '';

    foreach ($arrayPluginName as $name) {
        $loadPluginFile = "{$global['systemRootPath']}plugin/{$name}/{$name}.php";
        if (!file_exists($loadPluginFile)) {
            $obj->error = true;
            $obj->msg = "Plugin {$name} is required for $featureName ";
            break;
        }
        if (!AVideoPlugin::isEnabledByName($name)) {
            $obj->error = true;
            $obj->msg = "Please enable Plugin {$name} it is required for $featureName ";
            break;
        }
    }
    return $obj;
}

function _strtotime($datetime) {
    if (is_int($datetime)) {
        return $datetime;
    }
    return strtotime($datetime);
}

function _isSocketPresentOnCrontab() {
    foreach (getValidCrontabLines() as $line) {
        if (!empty($line) && preg_match('/plugin\/YPTSocket\/server.php/', $line)) {
            return true;
        }
    }
    return false;
}

function _isSchedulerPresentOnCrontab() {
    foreach (getValidCrontabLines() as $line) {
        if (!empty($line) && preg_match('/plugin\/Scheduler\/run.php/', $line)) {
            return true;
        }
    }
    return false;
}

function getValidCrontabLines() {
    global $_validCrontabLines;
    if (empty($validCrontabLines)) {
        $crontab = shell_exec('crontab -l');
        $crontabLines = preg_split("/\r\n|\n|\r/", $crontab);
        $_validCrontabLines = array();

        foreach ($crontabLines as $line) {
            $line = trim($line);
            if (!empty($line) && !preg_match('/^#/', $line)) {
                $_validCrontabLines[] = $line;
            }
        }
    }
    return $_validCrontabLines;
}

/**
 * 
 * @param type $strOrArray 
 * @return type return an array with the valid emails. 
 */
function is_email($strOrArray) {
    if (empty($strOrArray)) {
        return array();
    }
    if (!is_array($strOrArray)) {
        $strOrArray = array($strOrArray);
    }
    $valid_emails = array();
    foreach ($strOrArray as $email) {
        if (is_numeric($email)) {
            $email = User::getEmailDb($email);
        }
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $valid_emails[] = $email;
        }
    }
    return $valid_emails;
}

/**
 * https://codepen.io/ainalem/pen/LJYRxz
 * @global type $global
 * @param type $id
 * @param type $type 1 to 8 [1=x, 2=<-, 3=close, 4=x, 5=<-, 6=x, 7=x, 8=x]
 * @param type $parameters
 * @return type
 */
function getHamburgerButton($id = '', $type = 0, $parameters = 'class="btn btn-default hamburger"', $startActive = false, $invert = false) {
    global $global;
    if ($type === 'x') {
        $XOptions = array(1, 4, 6, 7, 8);
        $type = $XOptions[rand(0, 4)];
    } else if ($type === '<-') {
        $XOptions = array(2, 5);
        $type = $XOptions[rand(0, 1)];
    }
    $type = intval($type);
    if (empty($type) || ($type < 1 && $type > 8)) {
        $type = rand(1, 8);
    }
    if (empty($id)) {
        $id = uniqid();
    }
    $filePath = $global['systemRootPath'] . 'objects/functionGetHamburgerButton.php';
    return getIncludeFileContent($filePath, array('type' => $type, 'id' => $id, 'parameters' => $parameters, 'startActive' => $startActive, 'invert' => $invert));
}

function getUserOnlineLabel($users_id, $class = '', $style = '') {
    if (AVideoPlugin::isEnabledByName('YPTSocket')) {
        return YPTSocket::getUserOnlineLabel($users_id, $class, $style);
    } else {
        return '';
    }
}

function sendToEncoder($videos_id, $downloadURL, $checkIfUserCanUpload = false) {
    global $config;
    _error_log("sendToEncoder($videos_id, $downloadURL) start");
    $video = Video::getVideoLight($videos_id);

    $user = new User($video['users_id']);

    if ($checkIfUserCanUpload && !$user->getCanUpload()) {
        _error_log("sendToEncoder:  user cannot upload users_id={$video['users_id']}=" . $user->getBdId());
        return false;
    }
    global $global;
    $obj = new stdClass();
    $obj->error = true;

    $target = $config->getEncoderURL() . "queue";
    $postFields = [
        'user' => $user->getUser(),
        'pass' => $user->getPassword(),
        'fileURI' => $downloadURL,
        'videoDownloadedLink' => $downloadURL,
        'filename' => $video['filename'],
        'videos_id' => $videos_id,
        'notifyURL' => $global['webSiteRootURL'],
    ];

    if (empty($types) && AVideoPlugin::isEnabledByName("VideoHLS")) {
        $postFields['inputAutoHLS'] = 1;
    } elseif (!empty($types)) {
        foreach ($types as $key => $value) {
            $postFields[$key] = $value;
        }
    }

    _error_log("sendToEncoder: SEND To QUEUE: ($target) " . json_encode($postFields));
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $target);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_SAFE_UPLOAD, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $postFields);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    $r = curl_exec($curl);
    $obj->response = $r;
    if ($errno = curl_errno($curl)) {
        $error_message = curl_strerror($errno);
        //echo "cURL error ({$errno}):\n {$error_message}";
        $obj->msg = "cURL error ({$errno}):\n {$error_message}";
    } else {
        $obj->error = false;
    }
    _error_log("sendToEncoder: QUEUE CURL: ($target) " . json_encode($obj));
    curl_close($curl);
    Configuration::deleteEncoderURLCache();
    return $obj;
}

function parseFFMPEGProgress($progressFilename) {
    //get duration of source
    $obj = new stdClass();

    $obj->duration = 0;
    $obj->currentTime = 0;
    $obj->progress = 0;
    $obj->from = '';
    $obj->to = '';
    if (!file_exists($progressFilename)) {
        return $obj;
    }

    $content = url_get_contents($progressFilename);
    if (empty($content)) {
        return $obj;
    }
    //var_dump($content);exit;
    preg_match("/Duration: (.*?), start:/", $content, $matches);
    if (!empty($matches[1])) {

        $rawDuration = $matches[1];

        //rawDuration is in 00:00:00.00 format. This converts it to seconds.
        $ar = array_reverse(explode(":", $rawDuration));
        $duration = floatval($ar[0]);
        if (!empty($ar[1])) {
            $duration += intval($ar[1]) * 60;
        }
        if (!empty($ar[2])) {
            $duration += intval($ar[2]) * 60 * 60;
        }

        //get the time in the file that is already encoded
        preg_match_all("/time=(.*?) bitrate/", $content, $matches);

        $rawTime = array_pop($matches);

        //this is needed if there is more than one match
        if (is_array($rawTime)) {
            $rawTime = array_pop($rawTime);
        }

        //rawTime is in 00:00:00.00 format. This converts it to seconds.
        $ar = array_reverse(explode(":", $rawTime));
        $time = floatval($ar[0]);
        if (!empty($ar[1])) {
            $time += intval($ar[1]) * 60;
        }
        if (!empty($ar[2])) {
            $time += intval($ar[2]) * 60 * 60;
        }

        if (!empty($duration)) {
            //calculate the progress
            $progress = round(($time / $duration) * 100);
        } else {
            $progress = 'undefined';
        }
        $obj->duration = $duration;
        $obj->currentTime = $time;
        $obj->remainTime = ($obj->duration - $time);
        $obj->remainTimeHuman = secondsToVideoTime($obj->remainTime);
        $obj->progress = $progress;
    }

    preg_match("/Input[a-z0-9 #,]+from '([^']+)':/", $content, $matches);
    if (!empty($matches[1])) {
        $path_parts = pathinfo($matches[1]);
        $partsExtension = explode('?', $path_parts['extension']);
        $obj->from = $partsExtension[0];
    }

    preg_match("/Output[a-z0-9 #,]+to '([^']+)':/", $content, $matches);
    if (!empty($matches[1])) {
        $path_parts = pathinfo($matches[1]);
        $partsExtension = explode('?', $path_parts['extension']);
        $obj->to = $partsExtension[0];
    }

    return $obj;
}

function getExtension($link) {
    $path_parts = pathinfo($link);
    //$extension = strtolower(@$path_parts["extension"]);
    $filebasename = explode('?', $path_parts['basename']);
    return pathinfo($filebasename[0], PATHINFO_EXTENSION);
}

/**
 * It return true in case the $html_string is a string 'false' (good for post/get variables check)
 * It also return true in case it is an empty HTML
 * @param type $html_string
 * @return boolean
 */
function _empty($html_string) {
    if (is_string($html_string)) {
        if (strtolower($html_string) == 'false') {
            return true;
        }
    }
    return emptyHTML($html_string);
}

function adminSecurityCheck($force = false) {
    if (empty($force)) {
        if (!empty($_SESSION['adminSecurityCheck'])) {
            return false;
        }
        if (!User::isAdmin()) {
            return false;
        }
    }
    global $global;
    $videosHtaccessFile = getVideosDir() . '.htaccess';
    $originalHtaccessFile = "{$global['systemRootPath']}objects/htaccess_for_videos.conf";
    $videosHtaccessFileVersion = getHtaccessForVideoVersion($videosHtaccessFile);
    $originalHtaccessFileVersion = getHtaccessForVideoVersion($originalHtaccessFile);
    //_error_log("adminSecurityCheck: videos.htaccess new version = {$originalHtaccessFileVersion} old version = {$videosHtaccessFileVersion}");
    if (version_compare($videosHtaccessFileVersion, $originalHtaccessFileVersion, '<')) {
        unlink($videosHtaccessFile);
        _error_log("adminSecurityCheck: file deleted new version = {$originalHtaccessFileVersion} old version = {$videosHtaccessFileVersion}");
    }
    if (!file_exists($videosHtaccessFile)) {
        $bytes = copy($originalHtaccessFile, $videosHtaccessFile);
        _error_log("adminSecurityCheck: file created {$videosHtaccessFile} {$bytes} bytes");
    }
    _session_start();
    $_SESSION['adminSecurityCheck'] = time();
    return true;
}

function getHtaccessForVideoVersion($videosHtaccessFile) {
    if (!file_exists($videosHtaccessFile)) {
        return 0;
    }
    $f = fopen($videosHtaccessFile, 'r');
    $line = fgets($f);
    fclose($f);
    preg_match('/# version +([0-9.]+)/i', $line, $matches);
    return @$matches[1];
}

function fileIsAnValidImage($filepath) {
    if (file_exists($filepath)) {
        if (!function_exists('exif_imagetype')) {
            if (( list($width, $height, $type, $attr) = getimagesize($filepath) ) !== false) {
                return $type;
            }
        } else {
            return exif_imagetype($filepath);
        }
    }
    return false;
}

/**
 * return true if de file was deleted or does not exits and false if the file still present on the system
 * @param type $filepath
 * @return boolean
 */
function deleteInvalidImage($filepath) {
    if (file_exists($filepath)) {
        if (!fileIsAnValidImage($filepath)) {
            _error_log("deleteInvalidImage($filepath)");
            unlink($filepath);
            return true;
        }
        return false;
    }
    return true;
}

/**
 * add the twitterjs if the link is present
 * @param string $text
 * @return string
 */
function addTwitterJS($text) {
    if (preg_match('/href=.+twitter.com.+ref_src=.+/', $text)) {
        if (!preg_match('/platform.twitter.com.widgets.js/', $text)) {
            $text .= '<script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>';
        }
    }
    return $text;
}
