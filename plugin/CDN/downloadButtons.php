<?php
require_once dirname(__FILE__) . '/../../videos/configuration.php';

$videos_id = intval($_REQUEST['videos_id']);

if (empty($videos_id)) {
    forbiddenPage('Videos ID is required');
}

if (!User::canWatchVideo($videos_id)) {
    forbiddenPage('You cannot watch this video');
}

$videoHLSObj = AVideoPlugin::getDataObjectIfEnabled('VideoHLS');
if (empty($videoHLSObj)) {
    forbiddenPage('VideoHLS plugin is required for that');
}
$downloadOptions = VideoHLS::getMP3ANDMP4DownloadLinks($videos_id);

if (empty($downloadOptions)) {
    forbiddenPage('All download options on VideoHLS plugin are disabled');
}
$video = Video::getVideoLight($videos_id);
$height = 'calc(50vh - 50px)';
if (count($downloadOptions) == 1) {
    $height = 'calc(100vh - 50px)';
}
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <title><?php echo $config->getWebSiteTitle(); ?>  :: Download Video</title>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
        <style>
            #downloadButtons .btn{
                height: <?php echo $height; ?>;
                font-size: 30px;
            }
            #downloadButtons a.btn span{
                display: block !important;
                white-space: break-spaces;
                padding-top: 15vh;
            }
        </style>
    </head>
    <body class="<?php echo $global['bodyClass']; ?>">
        <?php
        include $global['systemRootPath'] . 'view/include/navbar.php';
        ?>
        <div class="container-fluid">
            <div id="downloadButtons">
                <?php
                $count = 0;
                $lastURL = '';
                $lastFormat = '';
                foreach ($downloadOptions as $theLink) {
                    if (!empty($theLink)) {
                        $count++;
                        $lastURL = $theLink['url'];
                        $progress = $theLink['progress'];
                        $lastFormat = strtolower($theLink['name']);
                        ?>
                        <button type="button" onclick="_goToURLOrAlertError('<?php echo $lastURL; ?>', '<?php echo $progress; ?>', '<?php echo $lastFormat; ?>');" 
                                class="btn btn-default btn-light btn-lg btn-block" target="_blank">
                            <i class="fas fa-download"></i> Download <?php echo $theLink['name']; ?>
                        </button>    
                        <?php
                    }
                }
                ?>
            </div>
        </div>
        <script>
            function _goToURLOrAlertError(url, progress, format) {
                avideoToastSuccess(<?php echo json_encode(__('Downloading') . '... ' . $video['title']); ?>);
                downloadURLOrAlertError(url, {}, '<?php echo $video['clean_title']; ?>.' + format, progress);
            }
        </script>
        <?php
        include $global['systemRootPath'] . 'view/include/footer.php';

        if ($count == 1) {
            ?>
            <script>
                $(function () {
                    _goToURLOrAlertError('<?php echo $lastURL; ?>', '<?php echo $progress; ?>', '<?php echo $lastFormat; ?>');
                });
            </script>
            <?php
        }
        ?>
    </body>
</html>
