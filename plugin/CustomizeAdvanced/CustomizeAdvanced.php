<?php

global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';

class CustomizeAdvanced extends PluginAbstract {

    public function getTags() {
        return array(
            PluginTags::$RECOMMENDED,
            PluginTags::$FREE
        );
    }

    public function getDescription() {
        $txt = "Fine Tuning your AVideo";
        $help = "<br><small><a href='https://github.com/WWBN/AVideo/wiki/Advanced-Customization-Plugin' target='__blank'><i class='fas fa-question-circle'></i> Help</a></small>";
        return $txt . $help;
    }

    public function getName() {
        return "CustomizeAdvanced";
    }

    public function getUUID() {
        return "55a4fa56-8a30-48d4-a0fb-8aa6b3f69033";
    }

    public function getPluginVersion() {
        return "1.0";
    }

    public function getEmptyDataObject() {
        global $global;
        $obj = new stdClass();
        $obj->logoMenuBarURL = "";
        $obj->encoderNetwork = "https://network.wwbn.net/";
        $obj->useEncoderNetworkRecomendation = false;
        $obj->doNotShowEncoderNetwork = true;
        $obj->doNotShowUploadButton = false;
        $obj->uploadButtonDropdownIcon = "fas fa-video";
        $obj->uploadButtonDropdownText = "";
        $obj->encoderNetworkLabel = "";
        $obj->doNotShowUploadMP4Button = true;
        $obj->disablePDFUpload = false;
        $obj->disableImageUpload = false;
        $obj->disableZipUpload = true;
        $obj->disableMP4Upload = false;
        $obj->disableMP3Upload = false;
        $obj->uploadMP4ButtonLabel = "";
        $obj->doNotShowImportMP4Button = true;
        $obj->importMP4ButtonLabel = "";
        $obj->doNotShowEncoderButton = false;
        $obj->encoderButtonLabel = "";
        $obj->doNotShowEmbedButton = false;
        $obj->embedBackgroundColor = "white";
        $obj->embedButtonLabel = "";
        $obj->embedCodeTemplate = '<div class="embed-responsive embed-responsive-16by9"><iframe width="640" height="360" style="max-width: 100%;max-height: 100%; border:none;" src="{embedURL}" frameborder="0" allowfullscreen="allowfullscreen" allow="autoplay" scrolling="no" videoLengthInSeconds="{videoLengthInSeconds}">iFrame is not supported!</iframe></div>';
        $obj->embedCodeTemplateObject = '<div class="embed-responsive embed-responsive-16by9"><object width="640" height="360"><param name="movie" value="{embedURL}"></param><param name="allowFullScreen" value="true"></param><param name="allowscriptaccess" value="always"></param><embed src="{embedURL}" allowscriptaccess="always" allowfullscreen="true" width="640" height="360"></embed></object></div>';
        $obj->htmlCodeTemplate = '<a href="{permaLink}"><img src="{imgSRC}">{title}</a>';
        $obj->BBCodeTemplate = '[url={permaLink}][img]{imgSRC}[/img]{title}[/url]';

        $o = new stdClass();
        $o->type = array(-1 => __("Basic Controls"), 0 => __("No Controls"), 1 => __("All controls"));
        $o->value = 1;
        $obj->embedControls = $o;
        $obj->embedAutoplay = false;
        $obj->embedLoop = false;
        $obj->embedStartMuted = false;
        $obj->embedShowinfo = true;

        $obj->doNotShowEncoderHLS = false;
        $obj->doNotShowEncoderResolutionLow = false;
        $obj->doNotShowEncoderResolutionSD = false;
        $obj->doNotShowEncoderResolutionHD = false;
        $obj->openEncoderInIFrame = false;
        $obj->showOnlyEncoderAutomaticResolutions = true;
        $obj->doNotShowEncoderAutomaticHLS = false;
        $obj->doNotShowEncoderAutomaticMP4 = false;
        $obj->doNotShowEncoderAutomaticWebm = false;
        $obj->doNotShowEncoderAutomaticAudio = false;
        $obj->saveOriginalVideoResolution = false;
        self::addDataObjectHelper('saveOriginalVideoResolution', 'Do not save original video', 'This option will make your encoder at the end trancode the video into the original format resolution');
        $obj->doNotShowExtractAudio = false;
        $obj->doNotShowCreateVideoSpectrum = false;
        $obj->doNotShowLeftMenuAudioAndVideoButtons = false;
        $obj->doNotShowWebsiteOnContactForm = false;
        $obj->doNotUseXsendFile = false;
        $obj->makeVideosInactiveAfterEncode = false;
        $obj->makeVideosUnlistedAfterEncode = false;
        $obj->usePermalinks = false;
        $obj->useVideoIDOnSEOLinks = true;
        $obj->disableAnimatedGif = false;
        $obj->removeBrowserChannelLinkFromMenu = false;
        $obj->EnableMinifyJS = false;
        $obj->disableShareAndPlaylist = false;
        $obj->disableShareOnly = false;
        $obj->disableEmailSharing = false;
        $obj->splitBulkEmailSend = 50;
        $obj->disableComments = false;
        $obj->commentsMaxLength = 200;
        $obj->commentsNoIndex = false;
        $obj->disableYoutubePlayerIntegration = false;
        $obj->utf8Encode = false;
        $obj->utf8Decode = false;
        $o = new stdClass();
        $o->type = "textarea";
        $o->value = "";
        $obj->menuBarHTMLCode = $o;
        $o->type = "textarea";
        $o->value = "";
        $obj->underMenuBarHTMLCode = $o;
        $o->type = "textarea";
        $o->value = "";
        $obj->footerHTMLCode = $o;
        $obj->signInOnRight = true;
        $obj->signInOnLeft = true;
        $obj->forceCategory = false;
        $obj->showCategoryTopImages = true;
        $obj->autoPlayAjax = false;

        $plugins = Plugin::getAllEnabled();
        //import external plugins configuration options
        foreach ($plugins as $value) {
            $p = AVideoPlugin::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $foreginObjects = $p->getCustomizeAdvancedOptions();
                if ($foreginObjects) {
                    foreach ($foreginObjects as $optionName => $defaultValue)
                        $obj->{$optionName} = $defaultValue;
                }
            }
        }

        $obj->disableInstallPWAButton = false;
        $obj->disablePlayLink = false;
        $obj->disableHelpLeftMenu = false;
        $obj->disableAboutLeftMenu = false;
        $obj->disableContactLeftMenu = false;
        $obj->disableAnimations = false;
        $obj->disableNavbar = false;
        $obj->disableNavBarInsideIframe = true;
        $obj->autoHideNavbar = true;
        $obj->autoHideNavbarInSeconds = 0;
        $obj->videosCDN = "";
        $obj->useFFMPEGToGenerateThumbs = false;
        $obj->thumbsWidthPortrait = 170;
        $obj->thumbsHeightPortrait = 250;
        $obj->thumbsWidthLandscape = 640;
        $obj->thumbsHeightLandscape = 360;
        $obj->usePreloadLowResolutionImages = false;
        $obj->showImageDownloadOption = false;
        $obj->doNotDisplayViews = false;
        $obj->doNotDisplayLikes = false;
        $obj->doNotDisplayCategoryLeftMenu = false;
        $obj->doNotDisplayCategory = false;
        $obj->doNotDisplayGroupsTags = false;
        $obj->doNotDisplayPluginsTags = false;
        $obj->showNotRatedLabel = false;
        $obj->showShareMenuOpenByDefault = false;

        foreach ($global['social_medias'] as $key => $value) {
            eval("\$obj->showShareButton_{$key} = true;");
        }

        $obj->askRRatingConfirmationBeforePlay_G = false;
        $obj->askRRatingConfirmationBeforePlay_PG = false;
        $obj->askRRatingConfirmationBeforePlay_PG13 = false;
        $obj->askRRatingConfirmationBeforePlay_R = false;
        $obj->askRRatingConfirmationBeforePlay_NC17 = true;
        $obj->askRRatingConfirmationBeforePlay_MA = true;
        $obj->filterRRating = false;

        $obj->doNotShowLeftHomeButton = false;
        $obj->doNotShowLeftTrendingButton = false;

        $obj->CategoryLabel = "Categories";
        $obj->ShowAllVideosOnCategory = false;
        $obj->hideCategoryVideosCount = false;
        $obj->hideEditAdvancedFromVideosManager = false;

        //ver 7.1
        $obj->paidOnlyUsersTellWhatVideoIs = false;
        $obj->paidOnlyShowLabels = false;
        $obj->paidOnlyLabel = "Premium";
        $obj->paidOnlyFreeLabel = "Free";
        $obj->removeSubscribeButton = false;
        $obj->removeThumbsUpAndDown = false;

        $o = new stdClass();
        $o->type = "textarea";
        $o->value = "";
        $obj->videoNotFoundText = $o;
        $obj->siteMapRowsLimit = 100;
        $obj->siteMapUTF8Fix = false;
        $obj->showPrivateVideosOnSitemap = false;
        $obj->enableOldPassHashCheck = true;
        $obj->disableHTMLDescription = false;
        $obj->disableShowMOreLessDescription = false;
        $obj->disableVideoSwap = false;
        $obj->makeSwapVideosOnlyForAdmin = false;
        $obj->disableCopyEmbed = false;
        $obj->disableDownloadVideosList = false;
        $obj->videosManegerRowCount = "[10, 25, 50, -1]"; //An Array of Integer which will be shown in the dropdown box to choose the row count. Default value is [10, 25, 50, -1]. -1 means all. When passing an Integer value the dropdown box will disapear.
        $obj->videosListRowCount = "[10, 20, 30, 40, 50]"; //An Array of Integer which will be shown in the dropdown box to choose the row count. Default value is [10, 25, 50, -1]. -1 means all. When passing an Integer value the dropdown box will disapear.
        $obj->videosManegerBulkActionButtons = true;

        $parse = parse_url($global['webSiteRootURL']);
        $domain = str_replace(".", "", $parse['host']);
        $obj->twitter_site = "@{$domain}";
        $obj->twitter_player = true;
        $obj->twitter_summary_large_image = false;
        $obj->footerStyle = "position: fixed;bottom: 0;width: 100%;";
        $obj->disableVideoTags = false;
        $obj->doNotAllowEncoderOverwriteStatus = false;
        $obj->doNotAllowUpdateVideoId = false;
        $obj->makeVideosIDHarderToGuess = false;
        self::addDataObjectHelper('makeVideosIDHarderToGuess', 'Make the videos ID harder to guess', 'This will change the videos_id on the URL to a crypted value. this crypt user your $global[salt] (configuration.php), so make sure you keep it save in case you need to restore your site, otherwise all the shared links will be lost');

        $o = new stdClass();
        $o->type = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
        $o->value = 0;
        $obj->timeZone = $o;

        $obj->keywords = "AVideo, videos, live, movies";
        $obj->doNotSaveCacheOnFilesystem = false;

        $o = new stdClass();
        $o->type = "textarea";
        $o->value = "Allow: /plugin/Live/?*
Allow: /plugin/PlayLists/*.css
Allow: /plugin/PlayLists/*.js
Allow: /plugin/TopMenu/*.css
Allow: /plugin/TopMenu/*.js
Allow: /plugin/SubtitleSwitcher/*.css
Allow: /plugin/SubtitleSwitcher/*.js
Allow: /plugin/Gallery/*.css
Allow: /plugin/Gallery/*.js
Allow: /plugin/YouPHPFlix2/*.png
Allow: /plugin/Live/*.css
Allow: /plugin/Live/*.js
Allow: /plugin/*.css
Allow: /plugin/*.js
Allow: .js
Allow: .css
Disallow: /user
Disallow: /plugin
Disallow: /mvideos
Disallow: /usersGroups
Disallow: /charts
Disallow: /upload
Disallow: /comments
Disallow: /subscribes
Disallow: /update
Disallow: /locale
Disallow: /objects/*
Allow: /plugin/Live/?*
Allow: /plugin/LiveLink/?*
Allow: /plugin/PlayLists/*.css
Allow: /plugin/PlayLists/*.js
Allow: /plugin/TopMenu/*.css
Allow: /plugin/TopMenu/*.js
Allow: /plugin/SubtitleSwitcher/*.css
Allow: /plugin/SubtitleSwitcher/*.js
Allow: /plugin/Gallery/*.css
Allow: /plugin/Gallery/*.js
Allow: /plugin/YouPHPFlix2/*.png
Allow: /plugin/Live/*.css
Allow: /plugin/Live/*.js
Allow: /plugin/*.css
Allow: /plugin/*.js
Allow: .js
Allow: .css";
        $obj->robotsTXT = $o;
        self::addDataObjectHelper('robotsTXT', 'robots.txt file content', 'robots.txt is a plain text file that follows the Robots Exclusion Standard. A robots.txt file consists of one or more rules. Each rule blocks (or allows) access for a given crawler to a specified file path in that website.');
        
        $o = new stdClass();
        $o->type = "textarea";
        $o->value = "";
        $obj->beforeNavbar = $o;
        self::addDataObjectHelper('beforeNavbar', 'Add some code before the navbar HTML');
        
        
        $o = new stdClass();
        $o->type = array(1 => __("1 Day"), 10 => __("10 Days"), 15 => __("15 Days"), 20 => __("20 Days"), 25 => __("25 Days"), 30 => __("30 Days"), 60 => __("60 Days"));
        $o->value = 30;
        $obj->trendingOnLastDays = $o;
        self::addDataObjectHelper('trendingOnLastDays', 'Trending Days', 'For the result of trending videos, use the statistics contained in the last few days');

        
        $obj->removeVideoList = false;
        $o = new stdClass();
        $o->type = array(
            'titleAZ' => __("Title (A-Z)"), 
            'titleZA' => __("Title (Z-A)"), 
            'newest' => __("Date added (newest)"), 
            'oldest' => __("Date added (oldest)"), 
            'popular' => __("Most popular"), 
            'views_count' => __("Most watched"), 
            'suggested' => __("Suggested"), 
            'trending' => __("Trending")
            );
        $o->value = 'newest';
        $obj->sortVideoListByDefault = $o;
        self::addDataObjectHelper('sortVideoListByDefault', 'Sort Video List By Default');
        
        return $obj;
    }
    
    public function navBar() {
        $obj = $this->getDataObject();
        $str = '';
        if(!emptyHTML($obj->beforeNavbar->value)){
            $str .= $obj->beforeNavbar->value;
        }
        return $str;
    }

    public function getHelp() {
        if (User::isAdmin()) {
            return "<h2 id='CustomizeAdvanced help'>CustomizeAdvanced (admin)</h2><p>" . $this->getDescription() . "</p>";
        }
        return "";
    }

    public function getModeYouTube($videos_id) {
        global $global, $config;
        
        $redirectVideo = self::getRedirectVideo($videos_id);
        //var_dump($redirectVideo);exit;
        if(!empty($redirectVideo) && !empty($redirectVideo->code) && isValidURL($redirectVideo->url) && getSelfURI() !== $redirectVideo->url){
            header("Location: {$redirectVideo->url}", true, $redirectVideo->code);
            exit;
        }
        
        $obj = $this->getDataObject();
        $video = Video::getVideo($videos_id, "viewable", true);
        if (!empty($video['rrating']) && empty($_GET['rrating'])) {
            $suffix = strtoupper(str_replace("-", "", $video['rrating']));
            eval("\$show = \$obj->askRRatingConfirmationBeforePlay_$suffix;");
            if (!empty($show)) {
                include "{$global['systemRootPath']}plugin/CustomizeAdvanced/confirmRating.php";
                exit;
            }
        }
    }

    public function getEmbed($videos_id) {
        return $this->getModeYouTube($videos_id);
    }

    public function getFooterCode() {
        global $global;

        $obj = $this->getDataObject();
        $content = '';
        if ($obj->autoHideNavbar && !isEmbed()) {
            $content .= '<script>$(function () {setTimeout(function(){if(typeof $("#mainNavBar").autoHidingNavbar == "function"){$("#mainNavBar").autoHidingNavbar();}},5000);});</script>';
            $content .= '<script>' . file_get_contents($global['systemRootPath'] . 'plugin/CustomizeAdvanced/autoHideNavbar.js') . '</script>';
        }
        if ($obj->autoHideNavbarInSeconds && !isEmbed()) {
            $content .= '<script>'
                    . 'var autoHidingNavbarTimeoutMiliseconds = ' . intval($obj->autoHideNavbarInSeconds * 1000) . ';'
                    . file_get_contents($global['systemRootPath'] . 'plugin/CustomizeAdvanced/autoHideNavbarInSeconds.js')
                    . '</script>';
        }
        return $content;
    }

    public function getHTMLMenuRight() {
        global $global, $config,$advancedCustom;
        $obj = $this->getDataObject();
        if (!empty($obj->menuBarHTMLCode->value)) {
            echo $obj->menuBarHTMLCode->value;
        }
        if ($obj->filterRRating) {
            include $global['systemRootPath'] . 'plugin/CustomizeAdvanced/menuRight.php';
        }
        if (User::canUpload() && empty($obj->doNotShowUploadButton)) {
            include $global['systemRootPath'] . 'view/include/navbarUpload.php';
        } else {
            include $global['systemRootPath'] . 'view/include/navbarNotUpload.php';
        }
    }

    public function getHTMLMenuLeft() {
        global $global;
        $obj = $this->getDataObject();
        if ($obj->filterRRating) {
            include $global['systemRootPath'] . 'plugin/CustomizeAdvanced/menuLeft.php';
        }
    }

    public static function getVideoWhereClause() {
        $sql = "";
        $obj = AVideoPlugin::getObjectData("CustomizeAdvanced");
        if ($obj->filterRRating && isset($_GET['rrating'])) {
            if ($_GET['rrating'] === "0") {
                $sql .= " AND v.rrating = ''";
            } else if (in_array($_GET['rrating'], Video::$rratingOptions)) {
                $sql .= " AND v.rrating = '{$_GET['rrating']}'";
            }
        }
        return $sql;
    }

    public function getVideosManagerListButton() {
        $btn = "";
        if (User::isAdmin()) {
            $btn = '<button type="button" class="btn btn-default btn-light btn-sm btn-xs btn-block " onclick="updateDiskUsage(\' + row.id + \');" data-row-id="right"  data-toggle="tooltip" data-placement="left" title="' . __("Update disk usage for this media") . '"><i class="fas fa-chart-line"></i> ' . __("Update Disk Usage") . '</button>';
            $btn .= '<button type="button" class="btn btn-default btn-light btn-sm btn-xs btn-block " onclick="removeThumbs(\' + row.id + \');" data-row-id="right"  data-toggle="tooltip" data-placement="left" title="' . __("Remove thumbs for this media") . '"><i class="fas fa-images"></i> ' . __("Remove Thumbs") . '</button>';
        }
        return $btn;
    }

    public function getHeadCode() {
        global $global;
        $obj = $this->getDataObject();

        if ($obj->makeVideosIDHarderToGuess) {
            if (isVideo()) {
                if (!empty($global['makeVideosIDHarderToGuessNotDecrypted'])) {
                    unset($global['makeVideosIDHarderToGuessNotDecrypted']);
                    forbiddenPage(__('Invalid ID'));
                }
            }
        }

        $baseName = basename($_SERVER['REQUEST_URI']);

        $js = "";
        if (empty($obj->autoPlayAjax)) {
            $js .= "<script>var autoPlayAjax=false;</script>";
        } else {
            $js .= "<script>var autoPlayAjax=true;</script>";
        }
        if ($baseName === 'mvideos') {
            $js .= "<script>function updateDiskUsage(videos_id){
                                    modal.showPleaseWait();
                                    \$.ajax({
                                        url: '{$global['webSiteRootURL']}plugin/CustomizeAdvanced/updateDiskUsage.php',
                                        data: {\"videos_id\": videos_id},
                                        type: 'post',
                                        success: function (response) {
                                        if(response.error){
                                            swal({
                                                title: \"" . __("Sorry!") . "\",
                                                text: response.msg,
                                                type: \"error\",
                                                html: true
                                            });
                                        }else{
                                            $(\"#grid\").bootgrid('reload');
                                        }
                                            console.log(response);
                                            modal.hidePleaseWait();
                                        }
                                    });}</script>";
            $js .= "<script>function removeThumbs(videos_id){
                                    modal.showPleaseWait();
                                    \$.ajax({
                                        url: '{$global['webSiteRootURL']}plugin/CustomizeAdvanced/deleteThumbs.php',
                                        data: {\"videos_id\": videos_id},
                                        type: 'post',
                                        success: function (response) {
                                        if(response.error){
                                            swal({
                                                title: \"" . __("Sorry!") . "\",
                                                text: response.msg,
                                                icon: \"error\"
                                            });
                                        }else{
                                            swal({
                                                title: \"" . __("Success!") . "\",
                                                text: \"\",
                                                icon: \"success\"
                                            });
                                        }
                                            console.log(response);
                                            modal.hidePleaseWait();
                                        }
                                    });}</script>";
        }
        return $js;
    }

    public function onReceiveFile($videos_id) {
        Video::updateFilesize($videos_id);
        return true;
    }
    
    public static function getManagerVideosAddNew() {
        global $global;
        $filename = $global['systemRootPath'] . 'plugin/CustomizeAdvanced/getManagerVideosAddNew.js';
        return file_get_contents($filename);
    }

    public static function getManagerVideosEdit() {
        global $global;
        $filename = $global['systemRootPath'] . 'plugin/CustomizeAdvanced/getManagerVideosEdit.js';
        return file_get_contents($filename);
    }

    public static function getManagerVideosEditField() {
        global $global;
        include $global['systemRootPath'] . 'plugin/CustomizeAdvanced/managerVideosEdit.php';
        return '';
    }

    public static function saveVideosAddNew($post, $videos_id) {
        self::setDoNotShowAds($videos_id, !_empty($post['doNotShowAdsOnThisVideo']));
        self::setRedirectVideo($videos_id, @$post['redirectVideoCode'], @$post['redirectVideoURL']);
    }
    
    public static function setDoNotShowAds($videos_id, $doNotShowAdsOnThisVideo) {
        if (!Permissions::canAdminVideos()) {
            return false;
        }
        $video = new Video('', '', $videos_id);
        $externalOptions = _json_decode($video->getExternalOptions());
        $externalOptions->doNotShowAdsOnThisVideo = $doNotShowAdsOnThisVideo;
        $video->setExternalOptions(json_encode($externalOptions));
        return $video->save();
    }

    public static function getDoNotShowAds($videos_id): bool {
        $video = new Video('', '', $videos_id);
        $externalOptions = _json_decode($video->getExternalOptions());
        return !empty($externalOptions->doNotShowAdsOnThisVideo);
    }
    
    public static function setRedirectVideo($videos_id, $code, $url) {
        if (!Permissions::canAdminVideos()) {
            return false;
        }
        $video = new Video('', '', $videos_id);
        $externalOptions = _json_decode($video->getExternalOptions());
        $externalOptions->redirectVideo = array('code'=>$code, 'url'=>$url);
        $video->setExternalOptions(json_encode($externalOptions));
        return $video->save();
    }

    public static function getRedirectVideo($videos_id) {
        $video = new Video('', '', $videos_id);
        $externalOptions = _json_decode($video->getExternalOptions());
        return @$externalOptions->redirectVideo;
    }
    
    public function showAds($videos_id): bool {
        return !self::getDoNotShowAds($videos_id);
    }

}

class SocialMedias {

    public $href;
    public $class;
    public $title;
    public $iclass;
    public $img;
    public $onclick;

    function __construct($iclass, $img = '') {
        $this->iclass = $iclass;
        $this->img = $img;
    }

    function getHref() {
        return $this->href;
    }

    function getClass() {
        return $this->class;
    }

    function getTitle() {
        return $this->title;
    }

    function getIclass() {
        return $this->iclass;
    }

    function getImg() {
        return $this->img;
    }

    function getOnclick() {
        return $this->onclick;
    }

    function setHref($href): void {
        $this->href = $href;
    }

    function setClass($class): void {
        $this->class = $class;
    }

    function setTitle($title): void {
        $this->title = $title;
    }

    function setIclass($iclass): void {
        $this->iclass = $iclass;
    }

    function setImg($img): void {
        $this->img = $img;
    }

    function setOnclick($onclick): void {
        $this->onclick = $onclick;
    }
    
}

$global['social_medias'] = array(
    'Facebook' => new SocialMedias('fab fa-facebook-square', ''),
    'Twitter' => new SocialMedias('fab fa-twitter', ''),
    'Tumblr' => new SocialMedias('fab fa-tumblr', ''),
    'Pinterest' => new SocialMedias('fab fa-pinterest-p', ''),
    'Reddit' => new SocialMedias('fab fa-reddit-alien', ''),
    'LinkedIn' => new SocialMedias('fab fa-linkedin-in', ''),
    'Wordpress' => new SocialMedias('fab fa-wordpress-simple', ''),
    'Pinboard' => new SocialMedias('fas fa-thumbtack', ''),
    'Gab' => new SocialMedias('', getURL('view/img/gab.png')),
    'CloutHub' => new SocialMedias('', getURL('view/img/cloutHub.png')),
);
