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

use angellco\printshop\models\Settings;
use angellco\printshop\PrintShop;

use Craft;
use craft\web\Controller;

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
        $lineItemId = Craft::$app->getRequest()->getRequiredParam('lineItemId');

        // Get the plugin settings so we have access to the volume info
        /** @var Settings $settings */
        $settings = PrintShop::$plugin->getSettings();
        $filesVolumeId = Db::idByUid('{{%volumes}}', $settings->filesVolumeUid);


        /**
         * Make a folder for the order using the order hash
         */
        $cart = PrintShop::$commerce->getCarts()->getCart();
        $rootFolder = Craft::$app->getAssets()->getRootFolderByVolumeId($filesVolumeId);

        // XXX here

        // Try for a folder with that name first - in case it already exists
        $folder = craft()->assets->findFolder(array(
            'parent' => $rootFolder->id,
            'name' => $cart->number
        ));

        // Check if we got one
        if ($folder)
        {
            $folderId = $folder->id;
        }
        else
        {
            // We didn’t, so create it
            $folderResponse = craft()->assets->createFolder($rootFolder->id, $cart->number);

            if ($folderResponse->isError())
            {
                if (craft()->request->isAjaxRequest)
                {
                    $this->returnJson(array(
                        'success' => false,
                        'message' => $folderResponse->getAttribute('errorMessage'),
                    ));
                }
                craft()->userSession->setError($folderResponse->getAttribute('errorMessage'));
            }
            else
            {
                $folderId = $folderResponse->getDataItem('folderId');
            }
        }

        // Final folderId check
        if (!$folderId)
        {
            if (craft()->request->isAjaxRequest)
            {
                $this->returnJson(array(
                    'success' => false,
                    'message' => Craft::t('Unable to upload files at this time.'),
                ));
            }
            craft()->userSession->setError(Craft::t('Unable to upload files at this time.'));
        }


        /**
         * Upload the file to Assets and store it in the folder we got from above.
         * Straight from `AssetsController::actionExpressUpload()`
         */
        $fileName = $_FILES['file_'.$lineItemId]['name'];

        $fileInfo = pathinfo($fileName);
        if ($fileInfo['extension'] === 'tif') {
            $fileName = $fileInfo['filename'].'.tiff';
        }

        $fileLocation = AssetsHelper::getTempFilePath(pathinfo($fileName, PATHINFO_EXTENSION));
        move_uploaded_file($_FILES['file_'.$lineItemId]['tmp_name'], $fileLocation);

        $response = craft()->assets->insertFileByLocalPath($fileLocation, $fileName, $folderId, AssetConflictResolution::KeepBoth);

        IOHelper::deleteFile($fileLocation, true);

        if ($response->isError())
        {
            if (craft()->request->isAjaxRequest)
            {
                $this->returnJson(array(
                    'success' => false,
                    'message' => $response->getAttribute('errorMessage'),
                ));
            }
            craft()->userSession->setError($response->getAttribute('errorMessage'));
        }

        // Keep hold of the Asset ID
        $fileId = $response->getDataItem('fileId');


        /**
         * Insert an OrderAssets_File record
         */
        $model = new OrderAssets_FileModel();
        $model->assetId = $fileId;
        $model->lineItemId = $lineItemId;

        $success = craft()->orderAssets_files->saveOrderAssetFile($model);
        if ($success)
        {
            if (craft()->request->isAjaxRequest)
            {
                $this->returnJson(array(
                    'success' => true,
                    'message' => Craft::t('File uploaded.'),
                    'file'    => $model
                ));
            }

            craft()->userSession->setNotice(Craft::t('File uploaded.'));
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

}
