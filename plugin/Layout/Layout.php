<?php

global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';

class Layout extends PluginAbstract {

    static private $tags = array();

    public function getTags() {
        return array(
            PluginTags::$RECOMMENDED,
            PluginTags::$FREE
        );
    }

    public function getDescription() {
        return "Finetune the layout and helpers";
    }

    public function getName() {
        return "Layout";
    }

    public function getPluginVersion() {
        return "1.1";
    }

    public function getUUID() {
        return "layout84-8f5a-4d1b-b912-172c608bf9e3";
    }

    public function getEmptyDataObject() {
        global $global;
        $obj = new stdClass();
        /*
          $variablesFile = $global['systemRootPath'] . 'plugin/Customize/sass/variables.scss';
          $subject = file_get_contents($variablesFile);
          $o = new stdClass();
          $o->type = "textarea";
          $o->value = $subject;
          $obj->colorsVariables = $o;
          $obj->showCustomCSS = true;
         * 
         */
        //$obj->showButtonNotification = false;
        $obj->categoriesTopButtons = false;
        $obj->categoriesTopButtonsShowOnlyOnFirstPage = true;
        $obj->categoriesTopButtonsShowVideosCount = false;
        $obj->categoriesTopButtonsFluid = true;
        $obj->enableAccessibility = false;

        $o = new stdClass();
        $o->type = array(0 => '-- ' . __("Random")) + self::getLoadersArray();
        $o->value = 'avideo';
        $obj->pageLoader = $o;

        return $obj;
    }

    static function getLoadersArray() {
        $files = Layout::getLoadersFiles();
        $response = array();
        foreach ($files as $key => $value) {
            $response[$value['name']] = ucfirst($value['name']);
        }
        return $response;
    }

    static function getLoadersFiles() {
        global $global;
        $files = _glob($global['systemRootPath'] . 'plugin/Layout/loaders/', '/.*html/');
        $response = array();
        foreach ($files as $key => $value) {
            $name = str_replace('.html', '', basename($value));
            $response[$name] = array('path' => $value, 'name' => $name);
        }
        return $response;
    }

    static public function getLoader($file) {
        global $global;
        $files = self::getLoadersFiles();
        $name = '';
        if (!empty($file)) {
            foreach ($files as $key => $value) {
                if ($file == $value['name']) {
                    $name = $value['name'];
                    break;
                }
            }
        }
        if (empty($name)) {
            $rand_key = array_rand($files);
            $name = $files[$rand_key]['name'];
        }

        $content = file_get_contents($global['systemRootPath'] . 'plugin/Layout/loaders/' . $name . '.html');
        return trim(preg_replace('/\s+/', ' ', str_replace('lds-', 'lds-' . uniqid(), $content)));
    }

    static function getLoaderDefault() {
        global $_getLoaderDefault;

        if (!isset($_getLoaderDefault)) {
            $obj = AVideoPlugin::getObjectData('Layout');
            $loader = Layout::getLoader($obj->pageLoader->value);
            //$loader = Layout::getLoader('spinner');
            $parts = explode('</style>', $loader);
            if (preg_match('/style/', $parts[0])) {
                $parts[0] .= '</style>';
            }
            $_getLoaderDefault = array('css' => $parts[0], 'html' => $parts[1]);
        }
        return $_getLoaderDefault;
    }

    static function getBGAnimationFiles() {
        global $global;
        $files = _glob($global['systemRootPath'] . 'plugin/Layout/animatedBackGrounds/', '/.*php/');
        $response = array();
        foreach ($files as $key => $value) {
            $name = basename($value);
            if ($name === 'index.php') {
                continue;
            }
            $name = str_replace('.php', '', $name);
            $response[$name] = array('path' => $value, 'name' => $name);
        }
        return $response;
    }

    static function includeBGAnimationFile($file) {
        if (empty($file)) {
            return false;
        }
        $files = self::getBGAnimationFiles();
        if ($file == 1) {
            $f = $files[array_rand($files)];
            echo '<!-- ' . $f['name'] . ' -->';
            include $f['path'];
        }
        foreach ($files as $key => $value) {
            if ($file == $value['name']) {
                include $value['path'];
                break;
            }
        }
        return true;
    }

    public function getPluginMenu() {
        global $global;
        return "";
        $filename = $global['systemRootPath'] . 'plugin/Customize/pluginMenu.html';
        return file_get_contents($filename);
    }

    public function getHeadCode() {
        global $global;
        $loaderParts = self::getLoaderDefault();
        echo $loaderParts['css'];
        echo "<script>var avideoLoader = '{$loaderParts['html']}';</script>";
        return false;
    }

    static function getIconsList() {
        global $global;
        include $global['systemRootPath'] . 'plugin/Layout/fontAwesomeFAB.php';
        // Fetch variables scss file in variable
        $font_vars = file_get_contents($global['systemRootPath'] . 'node_modules/fontawesome-free/scss/_variables.scss');

        // Find the position of first $fa-var , as all icon class names start after that
        $pos = strpos($font_vars, '$fa-var');

        // Filter the string and return only the icon class names
        $font_vars = substr($font_vars, $pos);

        // Create an array of lines
        $lines = explode("\n", $font_vars);

        $fonts_list = array();
        foreach ($lines as $line) {
            // Discard any black line or anything without :
            if (strpos($line, ':') !== false) {
                // Icon names and their Unicode Private Use Area values are separated with : and hence, explode them.
                $t = explode(":", $line);

                // Remove the $fa-var with fa, to use as css class names.
                $className = str_replace('$fa-var-', '', $t[0]);
                if (!in_array($className, $font_awesome_brands)) {
                    $classNameFull = "fa fa-{$className}";
                } else {
                    $classNameFull = "fab fa-{$className}";
                }
                $fonts_list[$className] = array($className, $classNameFull, $t[1]);
            }
        }
        return $fonts_list;
    }

    static function getSelectSearchable($optionsArray, $name, $selected, $id = "", $class = "", $placeholder = false, $templatePlaceholder = '') {
        global $global;
        if (empty($id)) {
            $id = $name;
        }
        $html = "";
        if (empty($global['getSelectSearchable'])) {
            $html .= '<link href="' . getURL('view/js/select2/select2.min.css') . '" rel="stylesheet" />';
            $html .= '<style>
                .select2-selection__rendered {line-height: 32px !important;}
                .select2-selection {min-height: 34px !important;}</style>';
        }
        if (empty($class)) {
            $class = "js-select-search";
        }
        $html .= '<select class="form-control ' . $class . '" name="' . $name . '" id="' . $id . '" style="display:none;">';
        if ($placeholder) {
            $html .= '<option value="" > -- </option>';
        }
        foreach ($optionsArray as $key => $value) {
            $selectedString = "";
            if (is_array($value)) { // need this because of the category icons
                $_value = $value[1];
                $_parameters = @$value[2];
                $_text = $value[0];
            } else {
                $_parameters = '';
                $_value = $key;
                $_text = $value;
            }
            if ($_value == $selected) {
                $selectedString = "selected";
            }
            $html .= '<option value="' . $_value . '" ' .
                    $selectedString . ' ' . $_parameters . '>' .
                    $_text . '</option>';
        }
        $html .= '</select>';
        // this is just to display something before load the select2
        if (empty($templatePlaceholder)) {
            $html .= '<select class="form-control" id="deleteSelect_' . $id . '" ><option></option></select>';
        } else {
            $html .= $templatePlaceholder;
        }
        $html .= '<script>$(document).ready(function() {$(\'#deleteSelect_' . $id . '\').remove();});</script>';

        $global['getSelectSearchable'] = 1;
        return $html;
    }

    static function getSelectSearchableHTML($optionsArray, $name, $selected, $id = "", $class = "", $placeholder = false, $templatePlaceholder = '') {
        global $global;
        if (empty($id)) {
            $id = $name;
        }
        $html = self::getSelectSearchable($optionsArray, $name, $selected, $id, $class, $placeholder, $templatePlaceholder);

        $html .= "<script>function getSelectformatStateResult{$name} (state) {
                                    if (!state.id) {
                                      return state.text;
                                    }
                                    var \$state = $(
                                      '<span><i class=\"' + state.id + '\"></i>'+
                                      state.text + '</span>'
                                    );
                                    return \$state;
                                  };";
        $html .= '$(document).ready(function() {$(\'#' . $id . '\').select2({templateSelection: getSelectformatStateResult' . $name . ', templateResult: getSelectformatStateResult' . $name . ',width: \'100%\'});});</script>';
        return $html;
    }

    static function getIconsSelect($name, $selected = "", $id = "", $class = "") {
        global $getIconsSelect;
        $getIconsSelect = 1;
        $icons = self::getIconsList();
        if (empty($id)) {
            $id = uniqid();
        }
        $code = "<script>function getIconsSelectformatStateResult (state) {
                                    if (!state.id) {
                                      return state.text;
                                    }
                                    var \$state = $(
                                      '<span><i class=\"' + state.id + '\"></i>'+
                                      ' - ' + state.text + '</span>'
                                    );
                                    return \$state;
                                  };</script>";
        self::addFooterCode($code);
        $code = '<script>$(document).ready(function() {$(\'#' . $id . '\').select2({templateSelection: getIconsSelectformatStateResult, templateResult: getIconsSelectformatStateResult,width: \'100%\'});});</script>';
        self::addFooterCode($code);
        return self::getSelectSearchable($icons, $name, $selected, $id, $class . " iconSelect", true);
    }

    static function getAvilableFlags() {
        global $global;
        $flags = array();
        include_once $global['systemRootPath'] . 'objects/bcp47.php';
        $files = _glob("{$global['systemRootPath']}locale", '/^[a-z]{2}(_.*)?.php$/');
        foreach ($files as $filename) {
            $filename = basename($filename);
            $fileEx = basename($filename, ".php");

            $name = $global['bcp47'][$fileEx]['label'];
            $flag = $global['bcp47'][$fileEx]['flag'];

            $flags[$fileEx] = array(json_encode(array('text' => $name, 'icon' => "flagstrap-icon flagstrap-{$flag}")), $fileEx, 'val3-' . $name);
        }
        return $flags;
    }

    static function getAllFlags() {
        global $global;
        $flags = array();
        include_once $global['systemRootPath'] . 'objects/bcp47.php';
        foreach ($global['bcp47'] as $key => $filename) {

            $name = $filename['label'];
            $flag = $filename['flag'];

            $flags[$key] = array(json_encode(array('text' => $name, 'icon' => "flagstrap-icon flagstrap-{$flag}")), $key, 'val3-' . $name);
        }
        return $flags;
    }

    static function getLangsSelect($name, $selected = "", $id = "", $class = "", $flagsOnly = false, $getAll = false) {
        global $getLangsSelect;
        $getLangsSelect = 1;
        if ($getAll) {
            $flags = self::getAllFlags();
        } else {
            $flags = self::getAvilableFlags();
        }
        if (empty($id)) {
            $id = uniqid();
        }
        if ($selected == 'us') {
            $selected = 'en_US';
        }

        if (!empty($flags[$selected])) {
            $selectedJson = _json_decode($flags[$selected][0]);
            $selectedJsonIcon = $selectedJson->icon;
        } else {
            $selectedJsonIcon = '';
        }

        $html = '<div class="btn-group">
            <button type="button" class="btn btn-default  dropdown-toggle navbar-btn" data-toggle="dropdown" aria-expanded="true">
                <i class="selectedflagicon ' . $selectedJsonIcon . '"></i> <span class="caret"></span>
            </button>
            <ul class="dropdown-menu dropdown-menu-right" role="menu">';

        $selfURI = getSelfURI();
        foreach ($flags as $key => $value) {
            $info = json_decode($value[0]);
            $url = addQueryStringParameter($selfURI, 'lang', $key);
            $html .= '<li class="dropdown-submenu">
                    <a tabindex="-1" href="' . $url . '">
                        <i class="' . $info->icon . '" aria-hidden="true"></i> ' . $info->text . '</a>
                    </li>';
        }

        $html .= '</ul></div>';
        return $html;
    }

    static function getUserSelect($name, $users_id_list, $selected = "", $id = "", $class = "") {
        $elements = array();
        foreach ($users_id_list as $users_id) {
            $name = User::getNameIdentificationById($users_id);
            $photo = User::getPhoto($users_id);
            $elements[$users_id] = htmlentities("<img src='{$photo}' class='img img-responsive pull-left' style='max-height:20px;margin-top: 2px;'> {$name}");
            if ($users_id == User::getId()) {
                $elements[$users_id] .= " (Me)";
            }
        }
        if (empty($id)) {
            $id = uniqid();
        }
        $methodName = __FUNCTION__;
        $code = "<script>function {$methodName}formatStateResult (state) {
                                    if (!state.id) {
                                      return state.text;
                                    }
                                    var \$state = $(
                                      '<span>' + state.text + '</span>'
                                    );
                                    return \$state;
                                  };</script>";
        self::addFooterCode($code);
        $code = '<script>$(document).ready(function() {$(\'#' . $id . '\').select2({templateSelection: ' . $methodName . 'formatStateResult, templateResult: ' . $methodName . 'formatStateResult,width: \'100%\'});});</script>';
        self::addFooterCode($code);
        return self::getSelectSearchable($elements, $name, $selected, $id, $class, true);
    }

    static function getCategorySelect($name, $selected = "", $id = "", $class = "") {
        $rows = Category::getAllCategories(true, false);
        array_multisort(array_column($rows, 'hierarchyAndName'), SORT_ASC, $rows);
        $cats = array();
        foreach ($rows as $value) {
            $cats[$value['id']] = htmlentities("<i class='{$value['iconClass']}'></i> " . $value['hierarchyAndName']);
        }
        if (empty($id)) {
            $id = uniqid();
        }
        $methodName = __FUNCTION__;
        $code = "<script>function {$methodName}formatStateResult (state) {
                                    if (!state.id) {
                                      return state.text;
                                    }
                                    var \$state = $(
                                      '<span>' + state.text + '</span>'
                                    );
                                    return \$state;
                                  };";
        self::addFooterCode($code);
        $code = '$(document).ready(function() {$(\'#' . $id . '\').select2({templateSelection: ' . $methodName . 'formatStateResult, templateResult: ' . $methodName . 'formatStateResult,width: \'100%\'});});</script>';
        self::addFooterCode($code);
        return self::getSelectSearchable($cats, $name, $selected, $id, $class, true);
    }

    static function getUserGroupsSelect($name, $selected = "", $id = "", $class = "") {
        $rows = UserGroups::getAllUsersGroupsArray();
        if (empty($id)) {
            $id = uniqid();
        }
        $methodName = __FUNCTION__;
        $code = "<script>function {$methodName}formatStateResult (state) {
                                    if (!state.id) {
                                      return state.text;
                                    }
                                    var \$state = $(
                                      '<span>' + state.text + '</span>'
                                    );
                                    return \$state;
                                  };";
        self::addFooterCode($code);
        $code = '$(document).ready(function() {$(\'#' . $id . '\').select2({templateSelection: ' . $methodName . 'formatStateResult, templateResult: ' . $methodName . 'formatStateResult,width: \'100%\'});});</script>';
        self::addFooterCode($code);
        return self::getSelectSearchable($rows, $name, $selected, $id, $class, true);
    }

    public function getFooterCode() {
        global $global;

        $obj = $this->getDataObject();
        $content = '';
        if (!empty($global['getSelectSearchable'])) {
            $content .= '<script src="' . getURL('view/js/select2/select2.min.js') . '"></script>';
            // $content .= '<script>$(document).ready(function() {$(\'.js-select-search\').select2();});</script>';
        }

        if (!empty($obj->enableAccessibility)) {

            $file = $global['systemRootPath'] . 'plugin/Layout/accessibility/accessibility.php';
            $content .= getIncludeFileContent($file);
        }


        $content .= self::_getFooterCode();
        return $content;
    }

    private static function addFooterCode($code) {
        global $LayoutaddFooterCode;
        if (!isset($LayoutaddFooterCode)) {
            $LayoutaddFooterCode = array();
        }
        $LayoutaddFooterCode[] = $code;
    }

    private static function _getFooterCode() {
        global $LayoutaddFooterCode;
        if (!isset($LayoutaddFooterCode)) {
            return "";
        }
        $LayoutaddFooterCode = array_unique($LayoutaddFooterCode);
        return implode(PHP_EOL, $LayoutaddFooterCode);
    }

    public function getHTMLMenuRight() {
        global $global;
        $obj = $this->getDataObject();
        if (empty($obj->showButtonNotification)) {
            return false;
        }
        include $global['systemRootPath'] . 'plugin/Layout/menuRight.php';
    }

    public function navBarAfter() {
        global $global;
        $obj = $this->getDataObject();
        if (!AVideoPlugin::isEnabledByName('YouPHPFlix2') && !empty($obj->categoriesTopButtons)) {
            if (!empty($obj->categoriesTopButtonsShowOnlyOnFirstPage) && !isFirstPage()) {
                return '';
            }
            include $global['systemRootPath'] . 'plugin/Layout/categoriesTopButtons.php';
        }
    }

    static function getUserAutocomplete($default_users_id = 0, $id = '', $parameters = array()) {
        global $global;
        $default_users_id = intval($default_users_id);
        if (empty($id)) {
            $id = 'getUserAutocomplete_' . uniqid();
        }
        include $global['systemRootPath'] . 'plugin/Layout/userAutocomplete.php';
        return "updateUserAutocomplete{$id}();";
    }

    static function organizeHTML($html) {
        global $global; // add socket twice on live page
        //return $html;
        if (!empty($global['doNOTOrganizeHTML'])) {
            return $html;
        }
        self::$tags = array();
        //return $html;
        //var_dump($html);exit;
        $html = self::getTagsLinkCSS($html);
        $html = self::getTagsScript($html);
        $html = self::separeteTag($html, 'style');
        $html = self::separeteTag($html, 'script');
        //$html = preg_replace('/<script.*><\/script>/i', '', $html);
        //return $html;
        //var_dump(self::$tags['script']);exit;
        if (!empty(self::$tags['tagcss'])) {
            $html = str_replace('</head>', implode('', array_unique(self::$tags['tagcss'])) . '</head>', $html);
        }
        //return $html;
        if (!empty(self::$tags['style'])) {
            $html = str_replace('</head>', '<style>' . implode(' ', array_unique(self::$tags['style'])) . '</style></head>', $html);
        }
        if (!empty(self::$tags['tagscript'])) {
            $html = str_replace('</body>', implode('', array_unique(self::$tags['tagscript'])) . '</body>', $html);
        }
        if (!empty(self::$tags['script'])) {
            $html = str_replace('</body>', '<script>' . implode('; ', array_unique(self::$tags['script'])) . '</script></body>', $html);
        }
        $html = self::removeExtraSpacesFromHead($html);
        $html = self::removeExtraSpacesFromScript($html);
        //echo $html;exit;
        return $html;
    }

    private static function tryToReplace($search, $replace, $subject) {
        if(true || self::codeIsValid($subject)){
            $newSubject = str_replace($search, $replace, $subject, $count);
            return ['newSubject' => $newSubject, 'success' => $count];
        }else{
            _error_log('organizeHTML: Invalid code: '.$subject);
            return ['newSubject' => $subject, 'success' => false];
        }
    }

    private static function codeIsValid($string) {
        $len = strlen($string);
        $stack = array();
        for ($i = 0; $i < $len; $i++) {
            switch ($string[$i]) {
                case '{': array_push($stack, 0);
                    break;
                case '}':
                    if (array_pop($stack) !== 0)
                        return false;
                    break;
                case '(': array_push($stack, 0);
                    break;
                case ')':
                    if (array_pop($stack) !== 0)
                        return false;
                    break;
                case '[': array_push($stack, 1);
                    break;
                case ']':
                    if (array_pop($stack) !== 1)
                        return false;
                    break;
                default: break;
            }
        }
        return (empty($stack));
    }

    static function removeExtraSpacesFromHead($html) {
        preg_match('/(<head.+<\/head>)/Usi', $html, $matches);
        $str = preg_replace('/\s+/', ' ', $matches[0]);
        //var_dump($str);exit;
        $html = str_replace($matches[0], $str, $html);
        return $html;
    }

    static function removeExtraSpacesFromScript($html) {
        preg_match_all('/(<script[^>]*>.+<\/script>)/Usi', $html, $matches);
        foreach ($matches as $value) {
            $str = preg_replace('/ +/', ' ', $value);
            $html = str_replace($value, $str, $html);
        }
        return $html;
    }

    static function getTagsLinkCSS($html) {
        preg_match_all('/<link[^>]+href=[^>]+css[^>]+>/Usi', $html, $matches);
        if (!empty($matches)) {
            foreach ($matches[0] as $value) {
                $response = self::tryToReplace($value, '', $html);
                if ($response['success']) {
                    self::addTag('tagcss', $value);
                    $html = $response['newSubject'];
                }
            }
        }
        return $html;
    }

    static function getTagsScript($html) {
        preg_match_all('/<script[^<]+src=[^<]+<\/script>/Usi', $html, $matches);
        if (!empty($matches)) {
            foreach ($matches[0] as $key => $value) {
                // ignore google analitics
                if (!self::shouldIgnoreJS($value)) {
                    $response = self::tryToReplace($value, '', $html);
                    if ($response['success']) {
                        self::addTag('tagscript', $value);
                        $html = $response['newSubject'];
                    }
                }
            }
        }
        return $html;
    }

    static function separeteTag($html, $tag) {
        $reg = '/<' . $tag . '[^>]*>(.*)<\/' . $tag . '>/Usi';
        //var_dump($reg, $html);
        preg_match_all($reg, $html, $matches);
        //var_dump($matches);exit;
        if (!empty($matches)) {
            foreach ($matches[0] as $key => $value) {
                if (!self::shouldIgnoreJS($value)) {
                    $response = self::tryToReplace($value, '', $html);
                    if ($response['success']) {
                        self::addTag($tag, $matches[1][$key]);
                        $html = $response['newSubject'];
                    }
                }
            }
        }
        return $html;
    }
    
    static function shouldIgnoreJS($tag) {
        if (
                preg_match('/application.+json/i', $tag) ||
                preg_match('/function gtag\(/i', $tag) || 
                preg_match('/<script async/i', $tag)) {
            return true;
        }
        return false;
    }

    static public function addTag($tag, $value) {
        if (empty($value)) {
            return false;
        }
        if (!isset(self::$tags[$tag])) {
            self::$tags[$tag] = array();
        }
        self::$tags[$tag][] = $value;
        return true;
    }

    public function getEnd() {
        global $global;
        $html = _ob_get_clean();
        $html = self::organizeHTML($html);
        _ob_start();
        echo $html;
    }

}
