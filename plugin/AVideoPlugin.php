<?php
require_once $global['systemRootPath'] . 'objects/plugin.php';

class AVideoPlugin
{
    public static function YPTstart()
    {
        global $global;
        $time = microtime();
        $time = explode(' ', $time);
        $time = $time[1] + $time[0];
        $global['AVideoPluginStart'] = $time;
    }

    public static function YPTend($pluginName, $timeLimit=0)
    {
        global $global;
        require_once $global['systemRootPath'] . 'objects/user.php';
        $time = microtime();
        $time = explode(' ', $time);
        $time = $time[1] + $time[0];
        $finish = $time;
        $total_time = round(($finish - $global['AVideoPluginStart']), 4);
        if(empty($timeLimit)){
            $timeLimit = empty($global['noDebug']) ? 0.5 : 1;
        }
        if ($total_time > $timeLimit) {
            _error_log("The plugin [{$pluginName}] takes {$total_time} seconds to complete. URL: " . getSelfURI() . " IP: " . getRealIpAddr() . ' '.json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 50)), AVideoLog::$WARNING);
        }
    }

    public static function addRoutes()
    {
        $plugins = Plugin::getAllEnabled();
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $p->addRoutes();
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
        return false;
    }

    public static function addView($videos_id, $total)
    {
        $plugins = Plugin::getAllEnabled();
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $p->addView($videos_id, $total);
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
        return false;
    }

    public static function getHeadCode()
    {
        $plugins = Plugin::getAllEnabled();
        $str = "";
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                //echo $value['dirName'].PHP_EOL;
                //_error_log('getHeadCode start 1'.$value['dirName']);
                $str .= $p->getHeadCode();
                //_error_log('getHeadCode end '.$value['dirName']);
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
        return $str;
    }

    public static function getChartTabs()
    {
        $plugins = Plugin::getAllEnabled();
        $str = "";
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                if (method_exists($p, 'getChartTabs')) {
                    $str .= $p->getChartTabs();
                } else {
                    $checkStr = $p->getChartContent();
                    if (!empty($checkStr)) {
                        $str .= '<li><a data-toggle="tab" id="pluginMenuLink' . $p->getName() . '" href="#pluginMenu' . $p->getName() . '">' . $p->getName() . '</a></li>';
                    }
                }
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
        return $str;
    }

    public static function getChartContent()
    {
        $plugins = Plugin::getAllEnabled();
        $str = "";
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $checkStr = $p->getChartContent();
                if (!empty($checkStr)) {
                    $str .= '<div id="pluginMenu' . $p->getName() . '" class="tab-pane fade" style="padding: 10px;"><div class="row">' . $checkStr . '</div></div>';
                }
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
        return $str;
    }

    public static function getGallerySection()
    {
        $plugins = Plugin::getAllEnabled();
        $str = "";
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $str .= $p->getGallerySection();
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
        return $str;
    }

    public static function getHelpToc()
    {
        $plugins = Plugin::getAllEnabled();
        $str = "<h4>" . __("Table of content") . "</h4><ul>";
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $t = $p->getHelp();
                if (!empty($t)) {
                    $str .= "<li><a href='#" . $value['name'] . " help'>" . __($value['name']) . "</a></li>";
                }
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
        return $str . "</ul>";
    }

    public static function getHelp()
    {
        $plugins = Plugin::getAllEnabled();
        $str = "";
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $t = $p->getHelp();
                $str .= $t;
                if (!empty($t)) {
                    $str .= "<hr />";
                }
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
        return $str;
    }

    public static function getFooterCode()
    {
        $plugins = Plugin::getAllEnabled();
        $str = "";
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $str .= PHP_EOL . "<!-- {$value['dirName']} Footer Begin -->" . PHP_EOL;
                $str .= $p->getFooterCode();
                $str .= PHP_EOL . "<!-- {$value['dirName']} Footer End -->" . PHP_EOL;
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
        return $str;
    }

    public static function getJSFiles()
    {
        $plugins = Plugin::getAllEnabled();
        $allFiles = [];
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $js = $p->getJSFiles();
                if (is_array($js)) {
                    $allFiles = array_merge($allFiles, $js);
                }
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
        return $allFiles;
    }

    public static function getCSSFiles()
    {
        $plugins = Plugin::getAllEnabled();
        $allFiles = [];
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $allFiles = array_merge($allFiles, $p->getCSSFiles());
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
        return $allFiles;
    }

    public static function getHTMLBody()
    {
        $plugins = Plugin::getAllEnabled();
        $str = "";
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $str .= $p->getHTMLBody();
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
        return $str;
    }

    public static function getHTMLMenuLeft()
    {
        $plugins = Plugin::getAllEnabled();
        $str = "";
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $str .= $p->getHTMLMenuLeft();
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
        return $str;
    }

    public static function getHTMLMenuRight()
    {
        $plugins = Plugin::getAllEnabled();
        $str = "";
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $str .= $p->getHTMLMenuRight();
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
        return $str;
    }

    public static function getFirstPage()
    {
        return static::getEnabledFirstPage();
    }

    public static function getEnabledFirstPage()
    {
        $plugins = Plugin::getAllEnabled();
        $firstPage = false;
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (!is_object($p)) {
                continue;
            }
            $fp = $p->getFirstPage();
            if (!empty($fp)) {
                $firstPage = $fp;
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
        return $firstPage;
    }

    public static function loadPlugin($name, $forceReload = false)
    {
        global $global, $pluginIsLoaded;
        if (empty($pluginIsLoaded)) {
            $pluginIsLoaded = [];
        }
        $loadPluginFile = "{$global['systemRootPath']}plugin/{$name}/{$name}.php";
        // need to add dechex because some times it return an negative value and make it fails on javascript playlists
        if (!isset($pluginIsLoaded[$name]) && empty($forceReload)) {
            $pluginIsLoaded[$name] = false;
            if (file_exists($loadPluginFile)) {
                require_once $loadPluginFile;
                if (class_exists($name)) {
                    $code = "\$p = new {$name}();";
                    eval($code);
                    if (is_object($p)) {
                        $pluginIsLoaded[$name] = $p;
                    } else {
                        _error_log("[loadPlugin] eval failed for plugin ($name) code ($code) code result ($codeResult) included file $loadPluginFile", AVideoLog::$ERROR);
                    }
                }
            }
        }
        return $pluginIsLoaded[$name];
    }

    public static function loadPluginIfEnabled($name)
    {
        $p = static::loadPlugin($name);
        if ($p) {
            $uuid = $p->getUUID();
            if (static::isEnabled($uuid)) {
                return $p;
            }
        }
        return false;
    }

    public static function isPluginTablesInstalled($name, $installIt = false)
    {
        global $global, $isPluginTablesInstalled;
        $name = self::fixName($name);
        $installSQLFile = "{$global['systemRootPath']}plugin/{$name}/install/install.sql";
        if (isset($isPluginTablesInstalled[$installSQLFile])) {
            return $isPluginTablesInstalled[$installSQLFile];
        }
        //_error_log("isPluginTablesInstalled: Check for {$installSQLFile}");
        if (!file_exists($installSQLFile)) {
            $isPluginTablesInstalled[$installSQLFile] = true;
            return $isPluginTablesInstalled[$installSQLFile];
        }
        $lines = file($installSQLFile);
        foreach ($lines as $line) {
            $pattern1 = "/CREATE TABLE IF NOT EXISTS `?([a-z0-9_]+)[` (]?/i";
            $pattern2 = "/CREATE TABLE[^`]+`?([a-z0-9_]+)[` (]?/i";
            if (preg_match($pattern1, $line, $matches)) {
                if (!empty($matches[1])) {
                    //_error_log("isPluginTablesInstalled: Found ({$matches[1]})");
                    if (!ObjectYPT::isTableInstalled($matches[1])) {
                        //_error_log("isPluginTablesInstalled: ({$matches[1]}) is NOT installed");
                        if ($installIt) {
                            sqlDAL::executeFile($installSQLFile);
                            return self::isPluginTablesInstalled($name);
                        } else {
                            //_error_log("isPluginTablesInstalled: You need to install table {$matches[1]} for the plugin ({$name})", AVideoLog::$ERROR);
                            $isPluginTablesInstalled[$installSQLFile] = false;
                            return $isPluginTablesInstalled[$installSQLFile];
                        }
                    } else {
                        //_error_log("isPluginTablesInstalled: ({$matches[1]}) is installed");
                    }
                }
            } elseif (preg_match($pattern2, $line, $matches)) {
                if (!empty($matches[1])) {
                    if (!ObjectYPT::isTableInstalled($matches[1])) {
                        _error_log("You need to install table {$matches[1]} for the plugin ({$name})", AVideoLog::$ERROR);

                        $isPluginTablesInstalled[$installSQLFile] = false;
                        return $isPluginTablesInstalled[$installSQLFile];
                    }
                }
            }
        }
        $isPluginTablesInstalled[$installSQLFile] = true;
        return $isPluginTablesInstalled[$installSQLFile];
    }

    public static function getObjectData($name)
    {
        return self::getDataObject($name);
    }
    
    public static function setObjectData($name, $object){
        $p = static::loadPlugin($name);
        if ($p) {
            return $p->setDataObject($object);
        }
        return false;
    }
    
    public static function setObjectDataParameter($name, $parameterName, $value){
        $p = static::loadPlugin($name);
        if ($p) {
            return $p->setDataObjectParameter($parameterName, $value);
        }
        return false;
    }

    public static function getDataObject($name)
    {
        global $pluginGetDataObject;
        if (!isset($pluginGetDataObject)) {
            $pluginGetDataObject = [];
        }
        if (!empty($pluginGetDataObject[$name])) {
            return $pluginGetDataObject[$name];
        }
        $p = static::loadPlugin($name);
        if ($p) {
            $pluginGetDataObject[$name] = $p->getDataObject();
            return $pluginGetDataObject[$name];
        }
        return false;
    }

    public static function getObjectDataIfEnabled($name)
    {
        return self::getDataObjectIfEnabled($name);
    }

    public static function getDataObjectIfEnabled($name)
    {
        global $_getDataObjectIfEnabled;
        if (!isset($_getDataObjectIfEnabled)) {
            $_getDataObjectIfEnabled = [];
        }
        if (isset($_getDataObjectIfEnabled[$name])) {
            return $_getDataObjectIfEnabled[$name];
        }
        $p = static::loadPlugin($name);
        if ($p) {
            $uuid = $p->getUUID();
            if (static::isEnabled($uuid)) {
                $_getDataObjectIfEnabled[$name] = static::getObjectData($name);
                return $_getDataObjectIfEnabled[$name];
            }
        }
        $_getDataObjectIfEnabled[$name] = false;
        return $_getDataObjectIfEnabled[$name];
    }

    public static function xsendfilePreVideoPlay()
    {
        $plugins = Plugin::getAllEnabled();
        $str = "";
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);

            if (is_object($p)) {
                $str .= $p->xsendfilePreVideoPlay();
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
        return $str;
    }

    public static function getVideosManagerListButton()
    {
        $plugins = Plugin::getAllEnabled();
        $str = "";
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);

            if (is_object($p)) {
                $str .= $p->getVideosManagerListButton();
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
        return $str;
    }

    public static function getVideosManagerListButtonTitle()
    {
        $plugins = Plugin::getAllEnabled();
        $str = "";
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);

            if (is_object($p)) {
                $str .= $p->getVideosManagerListButtonTitle();
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
        return $str;
    }

    public static function getUsersManagerListButton()
    {
        $plugins = Plugin::getAllEnabled();
        $str = "";
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);

            if (is_object($p)) {
                $str .= $p->getUsersManagerListButton();
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
        return $str;
    }

    public static function getWatchActionButton($videos_id)
    {
        $plugins = Plugin::getAllEnabled();
        $str = "";
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);

            if (is_object($p)) {
                $str .= $p->getWatchActionButton($videos_id);
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
        return $str;
    }

    public static function getNetflixActionButton($videos_id)
    {
        $plugins = Plugin::getAllEnabled();
        $str = "";
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);

            if (is_object($p)) {
                $str .= $p->getNetflixActionButton($videos_id);
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
        return $str;
    }

    public static function getGalleryActionButton($videos_id)
    {
        $plugins = Plugin::getAllEnabled();
        $str = "";
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);

            if (is_object($p)) {
                $str .= $p->getGalleryActionButton($videos_id);
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
        return $str;
    }

    public static function isEnabled($uuid)
    {
        return !empty(Plugin::getEnabled($uuid));
    }

    public static function exists($name)
    {
        global $global;
        $name = self::fixName($name);
        $filename = "{$global['systemRootPath']}plugin/{$name}/{$name}.php";
        return file_exists($filename);
    }

    public static function isEnabledByName($name, $minVersion = '')
    {
        global $isPluginEnabledByName;
        if (empty($isPluginEnabledByName)) {
            $isPluginEnabledByName = [];
        }
        $index = "{$name}_{$minVersion}";
        if (!isset($isPluginEnabledByName[$index])) {
            $p = static::loadPluginIfEnabled($name);
            $isPluginEnabledByName[$index] = false;
            if ($minVersion) {
                if (!empty($p)) {
                    if (version_compare($p->getPluginVersion(), $minVersion, '>=')) {
                        $isPluginEnabledByName[$index] = true;
                    } else {
                        _error_log("You need to update your plugin {$name} to version {$minVersion} or greater", AVideoLog::$WARNING);
                    }
                }
            } else {
                $isPluginEnabledByName[$index] = !empty($p);
            }
        }
        return $isPluginEnabledByName[$index];
    }

    public static function getLogin()
    {
        $plugins = Plugin::getAllEnabled();
        $logins = [];
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            $dataObject = self::getDataObject($value['dirName']);

            if (is_object($p)) {
                $l = $p->getLogin();
                if (is_string($l) && file_exists($l)) { // it is a login form
                    $logins[] = $l;
                } elseif (!empty($l->type)) { // it is a hybridauth
                    $logins[] = ['parameters' => $l, 'loginObject' => $p, 'dirName' => $value['dirName'], 'dataObject'=>$dataObject];
                } elseif (is_array($l)) { // it is a hybridauth
                    foreach ($l as $value2) {
                        if (is_string($value2) && file_exists($value2)) { // it is a login form
                            $logins[] = $value2;
                        } elseif (!empty($value2->type)) { // it is a hybridauth
                            $logins[] = ['parameters' => $value2, 'loginObject' => $p, 'dirName' => $value['dirName'], 'dataObject'=>$dataObject];
                        }
                    }
                }
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
        return $logins;
    }

    public static function getStart()
    {
        $plugins = Plugin::getAllEnabled();
        foreach ($plugins as $value) {
            //self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                //echo $value['dirName'].PHP_EOL;
                //_error_log('AVideoPlugin::getStart: '.$value['dirName']);
                $p->getStart();
            }
            //self::YPTend("{$value['dirName']}::".__FUNCTION__);
        }
    }

    public static function getEnd()
    {
        $plugins = Plugin::getAllEnabled();
        usort($plugins, function ($a, $b) {
            if ($a['name'] == 'Cache') {
                return 1;
            } elseif ($b['name'] == 'Cache') {
                return -1;
            }else if ($a['name'] == 'Layout') {
                return 1;
            } elseif ($b['name'] == 'Layout') {
                return -1;
            }
            return 0;
        });
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                //_error_log("{$value['dirName']}::" . __FUNCTION__);
                if(!empty($_REQUEST['debug'])){
                    echo "<!-- {$value['dirName']} getEnd -->".PHP_EOL;
                }
                $p->getEnd();
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
    }

    public static function afterVideoJS()
    {
        $plugins = Plugin::getAllEnabled();
        $r = "";
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $r .= $p->afterVideoJS();
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
        return $r;
    }

    public static function afterNewVideo($videos_id)
    {
        $plugins = Plugin::getAllEnabled();
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $p->afterNewVideo($videos_id);
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
    }
        
    public static function onNewVideo($videos_id){
        if(empty($videos_id)){
            return false;
        }
        $plugins = Plugin::getAllEnabled();
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $p->onNewVideo($videos_id);
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
    }
    
    public static function onUpdateVideo($videos_id){
        if(empty($videos_id)){
            return false;
        }
        $plugins = Plugin::getAllEnabled();
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $p->onUpdateVideo($videos_id);
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
    }

    public static function onEncoderReceiveImage($videos_id)
    {
        $plugins = Plugin::getAllEnabled();
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $p->onEncoderReceiveImage($videos_id);
                $p->onReceiveFile($videos_id);
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
    }

    public static function onEncoderNotifyIsDone($videos_id)
    {
        $plugins = Plugin::getAllEnabled();
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $p->onEncoderNotifyIsDone($videos_id);
                $p->onReceiveFile($videos_id);
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
    }

    public static function onUploadIsDone($videos_id)
    {
        $plugins = Plugin::getAllEnabled();
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $p->onUploadIsDone($videos_id);
                $p->onReceiveFile($videos_id);
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
    }

    public static function afterDonation($from_users_id, $how_much, $videos_id, $users_id, $extraParameters)
    {
        $plugins = Plugin::getAllEnabled();
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $p->afterDonation($from_users_id, $how_much, $videos_id, $users_id, $extraParameters);
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
    }

    public static function afterNewComment($comments_id)
    {
        $plugins = Plugin::getAllEnabled();
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $p->afterNewComment($comments_id);
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
    }

    public static function afterNewResponse($comments_id)
    {
        $plugins = Plugin::getAllEnabled();
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $p->afterNewResponse($comments_id);
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
    }

    public static function getChannelButton()
    {
        $plugins = Plugin::getAllEnabled();
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $p->getChannelButton();
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
    }

    public static function getVideoManagerButton()
    {
        $plugins = Plugin::getAllEnabled();
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $p->getVideoManagerButton();
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
    }

    public static function getLivePanel()
    {
        $plugins = Plugin::getAllEnabled();
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $p->getLivePanel();
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
    }

    public static function getModeYouTube($videos_id)
    {
        $plugins = Plugin::getAllEnabled();
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $p->getModeYouTube($videos_id);
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
    }

    public static function getModeLive($key)
    {
        $plugins = Plugin::getAllEnabled();
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $p->getModeLive($key);
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
    }

    public static function getModeLiveLink($liveLink_id)
    {
        $plugins = Plugin::getAllEnabled();
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $p->getModeLiveLink($liveLink_id);
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
    }

    public static function getModeYouTubeLive($users_id)
    {
        $plugins = Plugin::getAllEnabled();
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $p->getModeYouTubeLive($users_id);
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
    }

    public static function getEmbed($videos_id)
    {
        $plugins = Plugin::getAllEnabled();
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $p->getEmbed($videos_id);
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
    }

    public static function getChannel($user_id, $user)
    {
        $plugins = Plugin::getAllEnabled();
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $p->getChannel($user_id, $user);
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
    }

    public static function getLiveApplicationArray()
    {
        global $_getLiveApplicationArrayPlugin;
        if(!isset($_getLiveApplicationArrayPlugin)){
            $_getLiveApplicationArrayPlugin = array();
            $plugins = Plugin::getAllEnabled();
            $array = [];
            foreach ($plugins as $value) {
                self::YPTstart();
                $p = static::loadPlugin($value['dirName']);
                if (is_object($p)) {
                    $appArray = $p->getLiveApplicationArray();
                    if (is_array($appArray)) {
                        if (!is_array($array)) {
                            $array = $appArray;
                        } else {
                            $array = array_merge($array, $appArray);
                        }
                    }
                }
                self::YPTend("{$value['dirName']}::" . __FUNCTION__);
            }
            $_getLiveApplicationArrayPlugin = $array;
        }

        usort($_getLiveApplicationArrayPlugin, "getLiveApplicationArrayCMP");
        
        return $_getLiveApplicationArrayPlugin;
    }

    public static function getPlayListButtons($playlist_id = "")
    {
        if (empty($playlist_id)) {
            return "";
        }
        $plugins = Plugin::getAllEnabled();
        $str = "";
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $str .= $p->getPlayListButtons($playlist_id);
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
        return $str;
    }

    public static function getMyAccount($users_id = "")
    {
        if (empty($users_id)) {
            return "";
        }
        $plugins = Plugin::getAllEnabled();
        $str = "";
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $str .= $p->getMyAccount($users_id);
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
        return $str;
    }

    public static function getPluginUserOptions()
    {
        $plugins = Plugin::getAllEnabled();
        $userOptions = [];
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $userOptions = array_merge($userOptions, $p->getUserOptions());
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
        return $userOptions;
    }

    /**
     *
     * @return type return a list of IDs of the user groups
     */
    public static function getDynamicUserGroupsId($users_id)
    {
        $plugins = Plugin::getAllEnabled();
        $array = [];
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $appArray = $p->getDynamicUserGroupsId($users_id);
                //echo $value['dirName']." - {$users_id} - ". json_encode($appArray).PHP_EOL;
                $array = array_merge($array, $appArray);
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
        return $array;
    }

    public static function getDynamicUsersId($users_groups_id)
    {
        $plugins = Plugin::getAllEnabled();
        $array = [];
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $appArray = $p->getDynamicUsersId($users_groups_id);
                $array = array_merge($array, $appArray);
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
        return $array;
    }

    public static function getUserOptions()
    {
        $userOptions = static::getPluginUserOptions();
        $str = "";
        foreach ($userOptions as $userOption => $id) {
            $str .= "
                <li class=\"list-group-item\">" . __($userOption) .
                    "<div class=\"material-switch pull-right\">
                        <input type=\"checkbox\" value=\"$id\" id=\"$id\"/>
                        <label for=\"$id\" class=\"label-success\"></label>
                    </div>
                </li>
            ";
        }
        return $str;
    }

    public static function addUserBtnJS()
    {
        $userOptions = static::getPluginUserOptions();
        $userOptions = [];
        $js = "";
        foreach ($userOptions as $userOption => $id) {
            $js .= "                    $('#$id').prop('checked', false);\n";
        }
        return $js;
    }

    public static function updateUserFormJS()
    {
        $userOptions = static::getPluginUserOptions();
        $js = "";
        foreach ($userOptions as $userOption => $id) {
            $js .= "                            \"$id\": $('#$id').is(':checked'),\n";
        }
        return $js;
    }

    public static function loadUsersFormJS()
    {
        $userOptions = static::getPluginUserOptions();
        $js = "";
        foreach ($userOptions as $userOption => $id) {
            $js .= "                        $('#$id').prop('checked', (row.$id == \"1\" ? true : false));
\n";
        }
        return $js;
    }

    public static function navBarButtons()
    {
        $plugins = Plugin::getAllEnabled();
        $userOptions = [];
        $navBarButtons = "";
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $navBarButtons .= $p->navBarButtons();
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
        return $navBarButtons;
    }

    public static function navBarProfileButtons()
    {
        $plugins = Plugin::getAllEnabled();
        $userOptions = [];
        $navBarButtons = "";
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $navBarButtons .= $p->navBarProfileButtons();
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
        return $navBarButtons;
    }

    public static function navBar()
    {
        $plugins = Plugin::getAllEnabled();
        $userOptions = [];
        $navBarButtons = "<!-- Plugin::navBar Start -->";
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $navBarButtons .= $p->navBar();
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
        $navBarButtons .= "<!-- Plugin::navBar END -->";
        return $navBarButtons;
    }

    public static function navBarAfter()
    {
        $plugins = Plugin::getAllEnabled();
        $userOptions = [];
        $navBarButtons = "";
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $navBarButtons .= $p->navBarAfter();
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
        return $navBarButtons;
    }

    /**
     * Execute update function at plugin and
     * update plugin version at database
     */
    public static function updatePlugin($name)
    {
        $p = static::loadPlugin($name);
        if (empty($p)) {
            return false;
        }
        $currentVersion = $p->getPluginVersion();
        $uuid = $p->getUUID();
        _error_log("AVideoPlugin::updatePlugin name=($name) uuid=($uuid) ");
        if (method_exists($p, 'updateScript')) {
            _error_log("AVideoPlugin::updatePlugin method_exists ", AVideoLog::$WARNING);
            if ($p->updateScript()) {
                Plugin::setCurrentVersionByUuid($uuid, $currentVersion);
            } else {
                return false;
            }
        } else {
            _error_log("AVideoPlugin::updatePlugin method NOT exists ", AVideoLog::$WARNING);
            Plugin::setCurrentVersionByUuid($uuid, $currentVersion);
        }
        return true;
    }

    public static function getCurrentVersion($name)
    {
        $p = static::loadPlugin($name);
        $uuid = $p->getUUID();
        return Plugin::getCurrentVersionByUuid($uuid);
    }

    /**
     *
     * @param type $name
     * @param type $version
     * @return type
     * -1 if your plugin is lower,
     * 0 if they are equal, and
     * 1 if your plugin is greater.
     */
    public static function compareVersion($name, $version)
    {
        $currentVersion = self::getCurrentVersion($name);
        return version_compare($currentVersion, $version);
    }

    public static function getSwitchButton($name)
    {
        global $global;
        $p = static::loadPlugin($name);
        $btn = "";
        if (!empty($p)) {
            $uid = uniqid();
            $btn = '<div class="material-switch">
                    <input class="pluginSwitch" data-toggle="toggle" type="checkbox" id="subsSwitch' . $uid . '" value="1" ' . (self::isEnabledByName($name) ? "checked" : "") . ' >
                    <label for="subsSwitch' . $uid . '" class="label-primary"></label>
                </div><script>
                $(document).ready(function () {
                $("#subsSwitch' . $uid . '").change(function (e) {
                    modal.showPleaseWait();
                    $.ajax({
                        url: "' . $global['webSiteRootURL'] . 'objects/pluginSwitch.json.php",
                        data: {"uuid": "' . $p->getUUID() . '", "name": "' . $name . '", "dir": "' . $name . '", "enable": $(this).is(":checked")},
                        type: "post",
                        success: function (response) {
                            modal.hidePleaseWait();
                        }
                    });
                });
                });</script>';
        }
        return $btn;
    }

    public static function getAllVideosExcludeVideosIDArray()
    {
        $plugins = Plugin::getAllEnabled();
        $array = [];
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $array = array_merge($array, $p->getAllVideosExcludeVideosIDArray());
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
        return $array;
    }

    public static function userCanUpload($users_id)
    {
        if (empty($users_id)) {
            return false;
        }
        $resp = true;
        $plugins = Plugin::getAllEnabled();
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $can = $p->userCanUpload($users_id);
                if (!empty($can)) {
                    if ($can < 0) {
                        if (!empty($users_id)) {
                            _error_log("userCanUpload: DENIED The plugin {$value['dirName']} said the user ({$users_id}) can NOT upload a video ");
                        }
                        $resp = false;
                    }
                    if ($can > 0) {
                        if (!empty($users_id)) {
                            _error_log("userCanUpload: SUCCESS The plugin {$value['dirName']} said the user ({$users_id}) can upload a video ");
                        }
                        return true;
                    }
                }
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
        return $resp;
    }

    public static function userCanWatchVideo($users_id, $videos_id)
    {
        global $userCanWatchVideoFunction;

        if (!isset($userCanWatchVideoFunction)) {
            $userCanWatchVideoFunction = [];
        }
        if (!isset($userCanWatchVideoFunction[$users_id])) {
            $userCanWatchVideoFunction[$users_id] = [];
        }
        if (isset($userCanWatchVideoFunction[$users_id][$videos_id])) {
            return $userCanWatchVideoFunction[$users_id][$videos_id];
        }

        $cacheName = "userCanWatchVideo($users_id, $videos_id)";
        $cache = ObjectYPT::getSessionCache($cacheName, 600);
        if (isset($cache)) {
            return $cache;
        }

        $plugins = Plugin::getAllEnabled();
        $resp = Video::userGroupAndVideoGroupMatch($users_id, $videos_id);
        $video = new Video("", "", $videos_id);
        if (empty($video)) {
            _error_log("userCanWatchVideo: the usergroup and the video group does not match, User = $users_id, video = $videos_id)");
            $userCanWatchVideoFunction[$users_id][$videos_id] = false;
            ObjectYPT::setSessionCache($cacheName, false);
            return false;
        }
        // check if the video is for paid plans only
        if ($video->getOnly_for_paid()) {
            _error_log("userCanWatchVideo: the video ({$videos_id}) is set Only_for_paid = true)");
            $resp = false;
        }
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $can = $p->userCanWatchVideo($users_id, $videos_id);
                if (!empty($can)) {
                    if ($can < 0) {
                        if (!empty($users_id)) {
                            _error_log("userCanWatchVideo: DENIED The plugin {$value['dirName']} said the user ({$users_id}) can NOT watch the video ({$videos_id})");
                        }
                        $resp = false;
                    }
                    if ($can > 0) {
                        if (!empty($users_id)) {
                            _error_log("userCanWatchVideo: SUCCESS The plugin {$value['dirName']} said the user ({$users_id}) can watch the video ({$videos_id})");
                        }
                        $userCanWatchVideoFunction[$users_id][$videos_id] = true;
                        ObjectYPT::setSessionCache($cacheName, true);
                        return true;
                    }
                }
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
        if (!empty($users_id)) {
            //_error_log("userCanWatchVideo: No plugins approve user ({$users_id}) watch the video ({$videos_id}) ");
        }
        $userCanWatchVideoFunction[$users_id][$videos_id] = $resp;
        ObjectYPT::setSessionCache($cacheName, $resp);
        return $resp;
    }

    public static function userCanWatchVideoWithAds($users_id, $videos_id)
    {
        global $userCanWatchVideoWithAdsFunction;
        $users_id = intval($users_id);
        if (!isset($userCanWatchVideoWithAdsFunction)) {
            $userCanWatchVideoWithAdsFunction = [];
        }
        if (!isset($userCanWatchVideoWithAdsFunction[$users_id])) {
            $userCanWatchVideoWithAdsFunction[$users_id] = [];
        }
        if (isset($userCanWatchVideoWithAdsFunction[$users_id][$videos_id])) {
            return $userCanWatchVideoWithAdsFunction[$users_id][$videos_id];
        }
        $plugins = Plugin::getAllEnabled();
        $resp = Video::userGroupAndVideoGroupMatch($users_id, $videos_id);
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $can = $p->userCanWatchVideoWithAds($users_id, $videos_id);
                if (!empty($can)) {
                    $resp = $can > 0 ? true : false;
                    if ($resp) {
                        if (!empty($users_id)) {
                            _error_log("userCanWatchVideoWithAds the plugin ({$value['dirName']}) said user ({$users_id}) can watch");
                        }
                        $userCanWatchVideoWithAdsFunction[$users_id][$videos_id] = true;
                        return true;
                    } else {
                        //_error_log("userCanWatchVideoWithAds: users_id = $users_id, videos_id = $videos_id {$value['dirName']} said no");
                    }
                }
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
        $userCanWatchVideoWithAdsFunction[$users_id][$videos_id] = $resp;
        return $resp;
    }

    public static function showAds($videos_id)
    {
        global $_showAds;
        if(isBot()){
            return false;
        }
        if (!isset($_showAds)) {
            $_showAds = [];
        }
        if (isset($_showAds[$videos_id])) {
            return $_showAds[$videos_id];
        }
        $plugins = Plugin::getAllEnabled();
        $resp = true;
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $showAds = $p->showAds($videos_id);
                if (!$showAds) {
                    _error_log("showAds: {$value['dirName']} said NOT to show ads on {$videos_id}");
                    $_showAds[$videos_id] = false;
                    return false;
                }
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
        $_showAds[$videos_id] = $resp;
        return $resp;
    }

    public static function isPaidUser($users_id)
    {
        global $_isPaidUser;
        if (!isset($_isPaidUser)) {
            $_isPaidUser = [];
        }
        if (isset($_isPaidUser[$users_id])) {
            return $_isPaidUser[$users_id];
        }
        $plugins = Plugin::getAllEnabled();
        $resp = false;
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $isPaidUser = $p->isPaidUser($users_id);
                if ($isPaidUser) {
                    _error_log("isPaidUser: {$value['dirName']} said {$users_id} is a paid user");
                    $_isPaidUser[$users_id] = true;
                    return true;
                }
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
        $_isPaidUser[$users_id] = $resp;
        return $resp;
    }

    /**
     * In case some plugin needs to play a video that is not allowed for some reason.
     * A plugin can replace the getVideo method from the youtubeMode page
     * @return type
     */
    public static function getVideo()
    {
        $plugins = Plugin::getAllEnabled();
        $resp = null;
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $video = $p->getVideo();
                if (!empty($video)) {
                    return $video;
                }
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
        return $resp;
    }

    public static function onUserSignIn($users_id)
    {
        $plugins = Plugin::getAllEnabled();
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $p->onUserSignIn($users_id);
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
    }

    public static function onUserSignup($users_id)
    {
        $plugins = Plugin::getAllEnabled();
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $p->onUserSignup($users_id);
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
    }

    public static function onLiveStream($users_id, $live_servers_id)
    {
        $plugins = Plugin::getAllEnabled();
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $p->onLiveStream($users_id, $live_servers_id);
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
    }
    
    public static function on_publish_done($live_transmitions_history_id, $users_id, $key, $live_servers_id){
        $plugins = Plugin::getAllEnabled();
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $p->onLiveStream($live_transmitions_history_id, $users_id, $key, $live_servers_id);
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
    }

    public static function onUserSocketConnect()
    {
        _mysql_connect();
        $plugins = Plugin::getAllEnabled();
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $p->onUserSocketConnect();
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
        _mysql_close();
    }

    public static function onUserSocketDisconnect()
    {
        _mysql_connect();
        $plugins = Plugin::getAllEnabled();
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $p->onUserSocketDisconnect();
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
        _mysql_close();
    }

    public static function thumbsOverlay($videos_id)
    {
        $plugins = Plugin::getAllEnabled();
        $r = "";
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $r .= $p->thumbsOverlay($videos_id);
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
        return $r;
    }

    public static function profileTabName($users_id)
    {
        $plugins = Plugin::getAllEnabled();
        $r = "";
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $r .= $p->profileTabName($users_id);
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
        return $r;
    }

    public static function profileTabContent($users_id)
    {
        $plugins = Plugin::getAllEnabled();
        $r = "";
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $r .= $p->profileTabContent($users_id);
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
        return $r;
    }

    public static function getVideoTags($videos_id)
    {
        global $global, $advancedCustom;
        if (empty($videos_id) || !empty($global['disableVideoTags']) || !empty($advancedCustom->disableVideoTags)) {
            return [];
        }

        global $_getVideoTags;
        if (empty($_getVideoTags)) {
            $_getVideoTags = [];
        }

        if (isset($_getVideoTags[$videos_id])) {
            $array = $_getVideoTags[$videos_id];
        } else {
            $name = "getVideoTags{$videos_id}";
            $array = ObjectYPT::getCache($name, 86400);
            //_error_log("getVideoTags $name ".(empty($array)?"new":"old"));
            if (empty($array)) {
                TimeLogStart("AVideoPlugin::getVideoTags($videos_id)");
                $plugins = Plugin::getAllEnabled();
                $array = [];
                foreach ($plugins as $value) {
                    $TimeLog = "AVideoPlugin::getVideoTags($videos_id) {$value['dirName']} ";
                    TimeLogStart($TimeLog);
                    $p = static::loadPlugin($value['dirName']);
                    if (is_object($p)) {
                        $array = array_merge($array, $p->getVideoTags($videos_id));
                    }
                    TimeLogEnd($TimeLog, __LINE__);
                }
                TimeLogEnd("AVideoPlugin::getVideoTags($videos_id)", __LINE__);
                ObjectYPT::setCache($name, $array);
                $_getVideoTags[$videos_id] = $array;
            } else {
                //$array = object_to_array($array);
            }
        }
        return $array;
    }

    public static function deleteVideoTags($videos_id)
    {
        if (empty($videos_id)) {
            return false;
        }
        $name = "getVideoTags{$videos_id}";
        //_error_log("deleteVideoTags {$name}");
        return Cache::deleteCache($name);
    }

    public static function getVideoWhereClause()
    {
        $plugins = Plugin::getAllEnabled();
        $r = "";
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $r .= $p->getVideoWhereClause();
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
        return $r;
    }

    public static function getManagerVideosAddNew()
    {
        $plugins = Plugin::getAllEnabled();
        $r = "";
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $r .= $p->getManagerVideosAddNew();
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
        return $r;
    }

    public static function saveVideosAddNew($post, $videos_id)
    {
        $plugins = Plugin::getAllEnabled();
        $r = true;
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $r = $p->saveVideosAddNew($post, $videos_id) && $r;
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
        return $r;
    }

    public static function getManagerVideosReset()
    {
        $plugins = Plugin::getAllEnabled();
        $r = "";
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $r .= $p->getManagerVideosReset();
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
        return $r;
    }

    public static function getManagerVideosEdit()
    {
        $plugins = Plugin::getAllEnabled();
        $r = "";
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $r .= $p->getManagerVideosEdit();
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
        return $r;
    }

    public static function getManagerVideosEditField()
    {
        $plugins = Plugin::getAllEnabled();
        $r = "";
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $r .= $p->getManagerVideosEditField();
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
        return $r;
    }

    public static function getManagerVideosJavaScripts()
    {
        $plugins = Plugin::getAllEnabled();
        $r = "";
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $r .= $p->getManagerVideosJavaScripts();
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
        return $r;
    }

    public static function getManagerVideosTab()
    {
        $plugins = Plugin::getAllEnabled();
        $r = "";
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $r .= $p->getManagerVideosTab();
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
        return $r;
    }

    public static function getManagerVideosBody()
    {
        $plugins = Plugin::getAllEnabled();
        $r = "";
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $r .= $p->getManagerVideosBody();
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
        return $r;
    }

    public static function getAllVideosArray($videos_id)
    {
        $plugins = Plugin::getAllEnabled();
        $r = [];
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $r = array_merge($r, $p->getAllVideosArray($videos_id));
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
        return $r;
    }

    public static function getUploadMenuButton()
    {
        $plugins = Plugin::getAllEnabled();
        $r = "";
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $btn = $p->getUploadMenuButton();
                if (empty($btn)) {
                    continue;
                }
                $r .= "<!-- {$value['dirName']} getUploadMenuButton start -->" . $btn . "<!-- {$value['dirName']} getUploadMenuButton end -->";
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
        return $r;
    }

    public static function dataSetup()
    {
        $plugins = Plugin::getAllEnabled();
        $r = [];
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $data = $p->dataSetup();
                if (!empty($data)) {
                    $r[] = $data;
                }
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
        return implode(",", $r);
    }

    /* Video properties hooks */

    public static function onVideoSetLive_transmitions_history_id($video_id, $oldValue, $newValue)
    {
        $plugins = Plugin::getAllEnabled();
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $p->onVideoSetLive_transmitions_history_id($video_id, $oldValue, $newValue);
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
        return;
    }

    public static function onVideoSetEncoderURL($video_id, $oldValue, $newValue)
    {
        $plugins = Plugin::getAllEnabled();
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $p->onVideoSetEncoderURL($video_id, $oldValue, $newValue);
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
        return;
    }

    public static function onVideoSetFilepath($video_id, $oldValue, $newValue)
    {
        $plugins = Plugin::getAllEnabled();
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $p->onVideoSetFilepath($video_id, $oldValue, $newValue);
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
        return;
    }

    public static function onVideoSetFilesize($video_id, $oldValue, $newValue)
    {
        $plugins = Plugin::getAllEnabled();
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $p->onVideoSetFilesize($video_id, $oldValue, $newValue);
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
        return;
    }

    public static function onVideoSetUsers_id($video_id, $oldValue, $newValue)
    {
        $plugins = Plugin::getAllEnabled();
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $p->onVideoSetUsers_id($video_id, $oldValue, $newValue);
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
        return;
    }

    public static function onVideoSetSites_id($video_id, $oldValue, $newValue)
    {
        $plugins = Plugin::getAllEnabled();
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $p->onVideoSetSites_id($video_id, $oldValue, $newValue);
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
        return;
    }

    public static function onVideoSetVideo_password($video_id, $oldValue, $newValue)
    {
        $plugins = Plugin::getAllEnabled();
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $p->onVideoSetVideo_password($video_id, $oldValue, $newValue);
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
        return;
    }

    public static function onVideoSetClean_title($video_id, $oldValue, $newValue)
    {
        $plugins = Plugin::getAllEnabled();
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $p->onVideoSetClean_title($video_id, $oldValue, $newValue);
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
        return;
    }

    public static function onVideoSetDuration($video_id, $oldValue, $newValue)
    {
        $plugins = Plugin::getAllEnabled();
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $p->onVideoSetDuration($video_id, $oldValue, $newValue);
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
        return;
    }

    public static function onVideoSetIsSuggested($video_id, $oldValue, $newValue)
    {
        $plugins = Plugin::getAllEnabled();
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $p->onVideoSetIsSuggested($video_id, $oldValue, $newValue);
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
        return;
    }

    public static function onVideoSetStatus($video_id, $oldValue, $newValue)
    {
        $plugins = Plugin::getAllEnabled();
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $p->onVideoSetStatus($video_id, $oldValue, $newValue);
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
        return;
    }

    public static function onVideoSetType($video_id, $oldValue, $newValue, $force)
    {
        $plugins = Plugin::getAllEnabled();
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $p->onVideoSetType($video_id, $oldValue, $newValue, $force);
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
        return;
    }

    public static function onVideoSetRotation($video_id, $oldValue, $newValue)
    {
        $plugins = Plugin::getAllEnabled();
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $p->onVideoSetRotation($video_id, $oldValue, $newValue);
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
        return;
    }

    public static function onVideoSetZoom($video_id, $oldValue, $newValue)
    {
        $plugins = Plugin::getAllEnabled();
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $p->onVideoSetZoom($video_id, $oldValue, $newValue);
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
        return;
    }

    public static function onVideoSetDescription($video_id, $oldValue, $newValue)
    {
        $plugins = Plugin::getAllEnabled();
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $p->onVideoSetDescription($video_id, $oldValue, $newValue);
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
        return;
    }

    public static function onVideoSetCategories_id($video_id, $oldValue, $newValue)
    {
        $plugins = Plugin::getAllEnabled();
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $p->onVideoSetCategories_id($video_id, $oldValue, $newValue);
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
        return;
    }

    public static function onVideoSetVideoDownloadedLink($video_id, $oldValue, $newValue)
    {
        $plugins = Plugin::getAllEnabled();
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $p->onVideoSetVideoDownloadedLink($video_id, $oldValue, $newValue);
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
        return;
    }

    public static function onVideoSetVideoGroups($video_id, $oldValue, $newValue)
    {
        $plugins = Plugin::getAllEnabled();
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $p->onVideoSetVideoGroups($video_id, $oldValue, $newValue);
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
        return;
    }

    public static function onVideoSetTrailer1($video_id, $oldValue, $newValue)
    {
        $plugins = Plugin::getAllEnabled();
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $p->onVideoSetTrailer1($video_id, $oldValue, $newValue);
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
        return;
    }

    public static function onVideoSetTrailer2($video_id, $oldValue, $newValue)
    {
        $plugins = Plugin::getAllEnabled();
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $p->onVideoSetTrailer2($video_id, $oldValue, $newValue);
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
        return;
    }

    public static function onVideoSetTrailer3($video_id, $oldValue, $newValue)
    {
        $plugins = Plugin::getAllEnabled();
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $p->onVideoSetTrailer3($video_id, $oldValue, $newValue);
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
        return;
    }

    public static function onVideoSetRate($video_id, $oldValue, $newValue)
    {
        $plugins = Plugin::getAllEnabled();
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $p->onVideoSetRate($video_id, $oldValue, $newValue);
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
        return;
    }

    public static function onVideoSetYoutubeId($video_id, $oldValue, $newValue)
    {
        $plugins = Plugin::getAllEnabled();
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $p->onVideoSetYoutubeId($video_id, $oldValue, $newValue);
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
        return;
    }

    public static function onVideoSetTitle($video_id, $oldValue, $newValue)
    {
        $plugins = Plugin::getAllEnabled();
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $p->onVideoSetTitle($video_id, $oldValue, $newValue);
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
        return;
    }

    public static function onVideoSetFilename($video_id, $oldValue, $newValue, $force)
    {
        $plugins = Plugin::getAllEnabled();
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $p->onVideoSetFilename($video_id, $oldValue, $newValue, $force);
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
        return;
    }

    public static function onVideoSetNext_videos_id($video_id, $oldValue, $newValue)
    {
        $plugins = Plugin::getAllEnabled();
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $p->onVideoSetNext_videos_id($video_id, $oldValue, $newValue);
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
        return;
    }

    public static function onVideoSetVideoLink($video_id, $oldValue, $newValue)
    {
        $plugins = Plugin::getAllEnabled();
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $p->onVideoSetVideoLink($video_id, $oldValue, $newValue);
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
        return;
    }

    public static function onVideoSetCan_download($video_id, $oldValue, $newValue)
    {
        $plugins = Plugin::getAllEnabled();
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $p->onVideoSetCan_download($video_id, $oldValue, $newValue);
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
        return;
    }

    public static function onVideoSetCan_share($video_id, $oldValue, $newValue)
    {
        $plugins = Plugin::getAllEnabled();
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $p->onVideoSetCan_share($video_id, $oldValue, $newValue);
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
        return;
    }

    public static function getWalletConfigurationHTML($users_id, $wallet, $walletDataObject)
    {
        $plugins = Plugin::getAllEnabled();
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $p->getWalletConfigurationHTML($users_id, $wallet, $walletDataObject);
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
        return;
    }

    public static function onVideoSetOnly_for_paid($video_id, $oldValue, $newValue)
    {
        $plugins = Plugin::getAllEnabled();
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $p->onVideoSetOnly_for_paid($video_id, $oldValue, $newValue);
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
        return;
    }

    public static function onVideoSetRrating($video_id, $oldValue, $newValue)
    {
        $plugins = Plugin::getAllEnabled();
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $p->onVideoSetRrating($video_id, $oldValue, $newValue);
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
        return;
    }

    public static function onVideoSetExternalOptions($video_id, $oldValue, $newValue)
    {
        $plugins = Plugin::getAllEnabled();
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $p->onVideoSetExternalOptions($video_id, $oldValue, $newValue);
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
        return;
    }

    public static function onVideoSetVideoStartSeconds($video_id, $oldValue, $newValue)
    {
        $plugins = Plugin::getAllEnabled();
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $p->onVideoSetVideoStartSeconds($video_id, $oldValue, $newValue);
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
        return;
    }

    public static function onVideoSetSerie_playlists_id($video_id, $oldValue, $newValue)
    {
        $plugins = Plugin::getAllEnabled();
        foreach ($plugins as $value) {
            self::YPTstart();
            $p = static::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $p->onVideoSetSerie_playlists_id($video_id, $oldValue, $newValue);
            }
            self::YPTend("{$value['dirName']}::" . __FUNCTION__);
        }
        return;
    }

    public static function getPluginsOnByDefault($getUUID = true)
    {
        if (empty($getUUID)) {
            return [
                'CustomizeUser', // CustomizeUser
                'CustomizeAdvanced', // CustomizeAdvanced
                'Layout', // Layout
                'PlayerSkins', // PlayerSkins
                'Permissions', // Permissions
                'Scheduler', // Permissions
            ];
        } else {
            return [
                '55a4fa56-8a30-48d4-a0fb-8aa6b3fuser3', // CustomizeUser
                '55a4fa56-8a30-48d4-a0fb-8aa6b3f69033', // CustomizeAdvanced
                'layout84-8f5a-4d1b-b912-172c608bf9e3', // Layout
                'e9a568e6-ef61-4dcc-aad0-0109e9be8e36', // PlayerSkins
                'Permissions-5ee8405eaaa16', // Permissions
                'Scheduler-5ee8405eaaa16', // Permissions
            ];
        }
    }

    public static function getPluginsNameOnByDefaultFromUUID($UUID)
    {
        $UUIDs = self::getPluginsOnByDefault();
        $key = array_search($UUID, $UUIDs);
        if ($key === false) {
            return false;
        }
        $names = self::getPluginsOnByDefault(false);
        if (empty($names[$key])) {
            return false;
        }
        return $names[$key];
    }

    public static function isPluginOnByDefault($UUID)
    {
        $UUIDs = self::getPluginsOnByDefault();
        return in_array($UUID, $UUIDs);
    }

    public static function fixName($name)
    {
        if ($name === 'Programs') {
            return 'PlayLists';
        }
        return $name;
    }
}

class YouPHPTubePlugin extends AVideoPlugin
{
}     

function getLiveApplicationArrayCMP($a, $b) {
    //var_dump($a['title'],$a['comingsoon'], $b['title'],$b['comingsoon'], $a['comingsoon'] - $b['comingsoon']);
    return $a['comingsoon'] - $b['comingsoon'];
}