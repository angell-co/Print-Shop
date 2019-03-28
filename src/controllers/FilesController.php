<?php
/**
 * Print Shop plugin for Craft CMS 3.x
 *
 * Everything you need to build a print shop with Craft Commerce 2.
 *
 * @link      https://angell.io
 * @copyright Copyright (c) 2019 Angell & Co
 */

namespace angellco\printshop\controllers;

use angellco\printshop\models\File;
use angellco\printshop\models\Settings;
use angellco\printshop\PrintShop;

use Craft;
use craft\elements\Asset;
use craft\errors\UploadFailedException;
use craft\helpers\Assets;
use craft\helpers\Db;
use craft\models\VolumeFolder;
use craft\web\Controller;
use craft\web\UploadedFile;

/**
 * @author    Angell & Co
 * @package   PrintShop
 * @since     2.0.0
 */
class FilesController extends Controller
{

    // Protected Properties
    // =========================================================================

    /**
     * @var    bool|array Allows anonymous access to this controller's actions.
     *         The actions must be in 'kebab-case'
     * @access protected
     */
    protected $allowAnonymous = ['upload','delete','download-file'];

    // Public Methods
    // =========================================================================

    /**
     * Handle a request going to our plugin's index action URL, e.g.: actions/orderAssets
     */
    public function actionUpload()
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();
        $assets = Craft::$app->getAssets();
        $lineItemId = Craft::$app->getRequest()->getRequiredParam('lineItemId');

        // Get the plugin settings so we have access to the volume info
        /** @var Settings $settings */
        $settings = PrintShop::$plugin->getSettings();
        $filesVolumeId = Db::idByUid('{{%volumes}}', $settings->filesVolumeUid);


        /**
         * Make a folder for the order using the order hash
         */
        $cart = PrintShop::$commerce->getCarts()->getCart();
        $rootFolder = $assets->getRootFolderByVolumeId($filesVolumeId);

        // Try for a folder with that name first - in case it already exists
        $folder = $assets->findFolder([
            'parentId' => $rootFolder->id,
            'name' => $cart->number,
            'path' => $settings->filesVolumeSubpath.'/'.$cart->number.'/'
        ]);

        // Check if we got one
        if (!$folder)
        {
            // We didn’t, so create it
            try {
                $folderModel = new VolumeFolder();
                $folderModel->name = $cart->number;
                $folderModel->parentId = $rootFolder->id;
                $folderModel->volumeId = $rootFolder->volumeId;
                $folderModel->path = $settings->filesVolumeSubpath.'/'.$cart->number.'/';

                $assets->createFolder($folderModel);

                $folder = $folderModel;
            } catch (AssetException $exception) {
                return $this->asErrorJson($exception->getMessage());
            }
        }

        // Final folderId check
        if (!$folder)
        {
            return $this->asErrorJson(Craft::t('print-shop', 'Unable to upload files at this time.'));
        }


        /**
         * Upload the file to Assets and store it in the folder we got from above.
         * Straight from `AssetsController::actionExpressUpload()`
         */
        $uploadedFile = UploadedFile::getInstanceByName('file_'.$lineItemId);
        $fileName = $uploadedFile->name;

        if ($uploadedFile->getExtension() === 'tif') {
            $fileName = $uploadedFile->getBaseName().'.tiff';
        }

        try {
            $tempPath = $this->_getUploadedFileTempPath($uploadedFile);

            $fileName = Assets::prepareAssetName($fileName);

            $asset = new Asset();
            $asset->tempFilePath = $tempPath;
            $asset->filename = $fileName;
            $asset->newFolderId = $folder->id;
            $asset->volumeId = $folder->volumeId;
            $asset->avoidFilenameConflicts = true;
            $asset->setScenario(Asset::SCENARIO_CREATE);

            $result = Craft::$app->getElements()->saveElement($asset);

            // In case of error, let user know about it.
            if (!$result) {
                $errors = $asset->getFirstErrors();
                return $this->asErrorJson(Craft::t('print-shop', 'Failed to save the file:') . implode(";\n", $errors));
            }

        } catch (\Throwable $e) {
            Craft::error('An error occurred when saving an asset: ' . $e->getMessage(), __METHOD__);
            Craft::$app->getErrorHandler()->logException($e);
            return $this->asErrorJson($e->getMessage());
        }


        /**
         * Insert a new File record
         */
        $file = new File();
        $file->assetId = $asset->id;
        $file->lineItemId = $lineItemId;

        if (!PrintShop::$plugin->files->saveFile($file)) {
            return $this->asErrorJson(Craft::t('print-shop', 'Sorry there was a problem, please try again.'));
        }

        return $this->asJson(['success' => true, 'file' => $file]);
    }


    /**
     * Deletes an OrderAsset_File
     *
     * @return json
     */
    public function actionDelete()
    {

        $this->requirePostRequest();
        $id = craft()->request->getRequiredPost('id');

        $success = craft()->orderAssets_files->deleteOrderAssetFileById($id);

        if ($success)
        {
            if (craft()->request->isAjaxRequest)
            {
                $this->returnJson(array(
                    'success' => true,
                    'message' => Craft::t('File deleted.'),
                ));
            }

            craft()->userSession->setNotice(Craft::t('File deleted.'));
            $this->redirectToPostedUrl();
        }
        else
        {
            if (craft()->request->isAjaxRequest)
            {
                $this->returnJson(array(
                    'success' => false,
                    'message' => Craft::t('Sorry there was a problem, please try again.'),
                ));
            }
            craft()->userSession->setError(Craft::t('Sorry there was a problem, please try again.'));
        }

    }

    /**
     * Downloads a file and cleans up old temporary assets
     *
     * @throws Exception
     */
    public function actionDownloadFile()
    {
        // Clean up temp assets files that are more than a day old
        $files = IOHelper::getFiles(craft()->path->getTempPath(), true);
        foreach ($files as $file)
        {
            $lastModifiedTime = IOHelper::getLastTimeModified($file, true);
            if (substr(IOHelper::getFileName($file, false, true), 0, 6) === "assets" && DateTimeHelper::currentTimeStamp() - $lastModifiedTime->getTimestamp() >= 86400)
            {
                IOHelper::deleteFile($file);
            }
        }
        // Sort out the file we want to download
        $id = craft()->request->getParam('id');
        $criteria = craft()->elements->getCriteria(ElementType::Asset);
        $criteria->id = $id;
        /** @var AssetFileModel $asset */
        $asset = $criteria->first();
        if ($asset)
        {
            // Get a local copy of the file
            $sourceType = craft()->assetSources->getSourceTypeById($asset->sourceId);
            $localCopy = $sourceType->getLocalCopy($asset);
            // Send it to the browser
            craft()->request->sendFile($asset->filename, IOHelper::getFileContents($localCopy), array('forceDownload' => true));
            craft()->end();
        }
    }

    // Private Methods
    // =========================================================================

    /**
     * Borrowed from AssetsController.php
     *
     * @param UploadedFile $uploadedFile
     *
     * @return string
     * @throws UploadFailedException
     */
    private function _getUploadedFileTempPath(UploadedFile $uploadedFile)
    {
        if ($uploadedFile->getHasError()) {
            throw new UploadFailedException($uploadedFile->error);
        }

        // Move the uploaded file to the temp folder
        try {
            $tempPath = $uploadedFile->saveAsTempFile();
        } catch (ErrorException $e) {
            throw new UploadFailedException(0, null, $e);
        }

        if ($tempPath === false) {
            throw new UploadFailedException(UPLOAD_ERR_CANT_WRITE);
        }

        return $tempPath;
    }

}
