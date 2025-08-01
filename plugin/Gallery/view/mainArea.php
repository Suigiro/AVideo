<div class="row mainArea">
    <?php
    if (!empty($currentCat)) {
        include $global['systemRootPath'] . 'plugin/Gallery/view/Category.php';
    }
    $obj = AVideoPlugin::getObjectData("Gallery");
    if ($obj->searchOnChannels) {
        if (!empty($_REQUEST['search'])) {
            $users_id_array = VideoStatistic::getUsersIDFromChannelsWithMoreViews();
            $channels = Channel::getChannels(true, "u.id, '" . implode(",", $users_id_array) . "'");
            if (!empty($channels)) {
                ?>
                <div id="channelsResults" class="clear clearfix">
                    <h3 class="galleryTitle"> <i class="fas fa-user"></i> <?php echo __('Channels'); ?></h3>
                    <div class="row">
                        <?php
                        $search = $_REQUEST['search'];
                        clearSearch();
                        foreach ($channels as $value) {
                            echo '<div class="col-sm-12">';
                            User::getChannelPanel($value['id']);
                            echo '</div>';
                        }
                        reloadSearch();
                        ?>
                    </div>
                </div>
                <?php
                global $contentSearchFound;
                $contentSearchFound = true;
            }
        }
    }
    if (!empty($video)) {
        global $contentSearchFound;
        $contentSearchFound = true;
        $img_portrait = ($video['rotation'] === "90" || $video['rotation'] === "270") ? "img-portrait" : "";
        if (empty($_GET['search'])) {
            include $global['systemRootPath'] . 'plugin/Gallery/view/BigVideo.php';
        }
        echo '<center style="margin:5px;">' . getAdsLeaderBoardTop2() . '</center>';
        if (empty($_GET['catName'])) {
            $objLive = AVideoPlugin::getDataObject('Live');
            if (empty($objLive->doNotShowLiveOnVideosList)) {
                ?>
                <!-- For Live Videos -->
                <div id="liveVideos" class="clear clearfix" style="display: none;">
                    <h3 class="galleryTitle text-danger"> <i class="fas fa-play-circle"></i> <?php echo __("Live"); ?></h3>
                    <div class="extraVideos"></div>
                </div>
                <!-- For Live Schedule Videos -->
                <div id="liveScheduleVideos" class="clear clearfix" style="display: none;">
                    <h3 class="galleryTitle"> <i class="far fa-calendar-alt"></i> <?php echo __($objLive->live_schedule_label); ?></h3>
                    <div class="extraVideos"></div>
                </div>
                <!-- For Live Videos End -->
                <?php
            }
        }
        echo AVideoPlugin::getGallerySection();

        $sections = Gallery::getSectionsOrder();
        $countSections = 0;
        if (!empty($_GET['catName'])) {
            $currentCat = Category::getCategoryByName($_GET['catName']);
            //createGallery($category['name'], 'created', $obj->CategoriesRowCount, 'dateAddedOrder', __("newest"), __("oldest"), $orderString, "DESC", !$obj->hidePrivateVideos, $category['iconClass'], true);

            include $global['systemRootPath'] . 'plugin/Gallery/view/mainAreaCategory.php';
        } else {
            foreach ($sections as $value) {
                if (empty($value['active'])) {
                    continue;
                }
                $countSections++;
                if(preg_match('/Channel_([0-9]+)_/', $value['name'], $matches)){
                    $users_id = intval($matches[1]);
                    User::getChannelPanel($users_id);
                } else
                if ($value['name'] == 'Suggested') {
                    createGallery(!empty($obj->SuggestedCustomTitle) ? $obj->SuggestedCustomTitle : __("Suggested"), 'suggested', $obj->SuggestedRowCount, 'SuggestedOrder', "", "", $orderString, "ASC", !$obj->hidePrivateVideos, "fas fa-star");
                } else
                if ($value['name'] == 'Trending') {
                    createGallery(!empty($obj->TrendingCustomTitle) ? $obj->TrendingCustomTitle : __("Trending"), 'trending', $obj->TrendingRowCount, 'TrendingOrder', "zyx", "abc", $orderString, "ASC", !$obj->hidePrivateVideos, "fas fa-chart-line");
                } else
                if ($value['name'] == 'SortByName') {
                    createGallery(!empty($obj->SortByNameCustomTitle) ? $obj->SortByNameCustomTitle : __("Sort by name"), 'title', $obj->SortByNameRowCount, 'sortByNameOrder', "zyx", "abc", $orderString, "ASC", !$obj->hidePrivateVideos, "fas fa-font");
                } else
                if ($value['name'] == 'DateAdded' && empty($_GET['catName'])) {
                    createGallery(!empty($obj->DateAddedCustomTitle) ? $obj->DateAddedCustomTitle : __("Date added"), 'created', $obj->DateAddedRowCount, 'dateAddedOrder', __("newest"), __("oldest"), $orderString, "DESC", !$obj->hidePrivateVideos, "far fa-calendar-alt");
                } else
                if ($value['name'] == 'PrivateContent') {
                    createGallery(!empty($obj->PrivateContentCustomTitle) ? $obj->PrivateContentCustomTitle : __("Private Content"), 'created', $obj->PrivateContentRowCount, 'privateContentOrder', __("Most"), __("Fewest"), $orderString, "DESC", true, "fas fa-lock");
                } else
                if ($value['name'] == 'MostWatched') {
                    createGallery(!empty($obj->MostWatchedCustomTitle) ? $obj->MostWatchedCustomTitle : __("Most watched"), 'views_count', $obj->MostWatchedRowCount, 'mostWatchedOrder', __("Most"), __("Fewest"), $orderString, "DESC", !$obj->hidePrivateVideos, "far fa-eye");
                } else
                if ($value['name'] == 'MostPopular') {
                    createGallery(!empty($obj->MostPopularCustomTitle) ? $obj->MostPopularCustomTitle : __("Most popular"), 'likes', $obj->MostPopularRowCount, 'mostPopularOrder', __("Most"), __("Fewest"), $orderString, "DESC", !$obj->hidePrivateVideos, "fas fa-fire");
                } else
                if ($value['name'] == 'SubscribedChannels' && User::isLogged() && empty($_GET['showOnly'])) {
                    include $global['systemRootPath'] . 'plugin/Gallery/view/mainAreaChannels.php';
                } else
                if ($value['name'] == 'Categories' && empty($_GET['showOnly'])) {
                    if (empty($currentCat) && !empty(getSearchVar())) {
                        $onlySuggested = $obj->CategoriesShowOnlySuggested;
                        cleanSearchVar();
                        $categories = Category::getAllCategories(false, true, $onlySuggested);
                        reloadSearchVar();
                        foreach ($categories as $value) {
                            $currentCat = $value['clean_name'];
                            include $global['systemRootPath'] . 'plugin/Gallery/view/modeGalleryCategory.php';
                        }
                    } else {
                        include $global['systemRootPath'] . 'plugin/Gallery/view/modeGalleryCategory.php';
                    }
                }
            }
            if (empty($countSections) && !empty($_GET['catName'])) {
                $category = Category::getCategoryByName($_GET['catName']);
                createGallery($category['name'], 'created', $obj->CategoriesRowCount, 'dateAddedOrder', __("newest"), __("oldest"), $orderString, "DESC", !$obj->hidePrivateVideos, $category['iconClass'], true);
            }
        }
    } else {
        include $global['systemRootPath'] . 'plugin/Gallery/view/modeGalleryCategoryLive.php';
        $ob = _ob_get_clean();
        _ob_start();
        echo AVideoPlugin::getGallerySection();
        $ob2 = _ob_get_clean();
        echo $ob;
        global $contentSearchFound;
        if (empty($contentSearchFound) && empty($ob2)) {
            //$contentSearchFound = false;
        } else {
            $contentSearchFound = true;
        }
    }

    global $contentSearchFound;
    if (empty($contentSearchFound)) {
        _session_start();
        unset($_SESSION['type']);
        ?>
        <div class="alert alert-warning">
            <h1>
                <span class="glyphicon glyphicon-facetime-video"></span>
                <?php echo __("Warning"); ?>!
            </h1>
            <!-- <?php echo basename(__FILE__); ?> -->
            <?php echo __("We have not found any videos or audios to show"); ?>.
        </div>
        <?php
        _error_log('contentSearchFound NOT FOUND '. json_encode(debug_backtrace()));
        _error_log('contentSearchFound NOT FOUND LAST SQL '. $debugLastGetVideoSQL);
        _error_log('contentSearchFound NOT FOUND LAST TOTAL SQL '. $lastGetTotalVideos);
        include $global['systemRootPath'] . 'view/include/notfound.php';
    }
    ?>
</div>