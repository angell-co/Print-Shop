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
use craft\helpers\App;
use craft\helpers\Assets;
use craft\helpers\Db;
use craft\helpers\FileHelper;
use craft\models\VolumeFolder;
use craft\web\Controller;
use craft\web\UploadedFile;
use yii\web\BadRequestHttpException;
use yii\web\Response;

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
     * Upload a file against a line item.
     *
     * @return Response
     * @throws BadRequestHttpException
     * @throws \Throwable
     * @throws \craft\errors\AssetConflictException
     * @throws \craft\errors\ElementNotFoundException
     * @throws \craft\errors\VolumeObjectExistsException
     * @throws \yii\base\Exception
     */
    public function actionUpload(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();
        $assets = Craft::$app->getAssets();
        $lineItemId = Craft::$app->getRequest()->getRequiredParam('lineItemId');

        // Get the plugin settings so we have access to the volume info
        /** @var Settings $settings */
        $settings = PrintShop::$plugin->getSettings();
        $volumeId = Db::idByUid('{{%volumes}}', $settings->volumeUid);

        $volumeSubpath = $settings->volumeSubpath ? $settings->volumeSubpath.'/' : '';


        /**
         * Make a folder for the order using the order hash
         */
        $cart = PrintShop::$commerce->getCarts()->getCart();
        $rootFolder = $assets->getRootFolderByVolumeId($volumeId);

        // Try for an order folder - in case it already exists
        $orderFolder = $assets->findFolder([
            'parentId' => $rootFolder->id,
            'name' => $cart->shortNumber,
            'path' => $volumeSubpath.$cart->shortNumber.'/'
        ]);

        // If we didn’t get one, create it
        if ($orderFolder === null) {
            try {
                $folderModel = new VolumeFolder();
                $folderModel->name = $cart->shortNumber;
                $folderModel->parentId = $rootFolder->id;
                $folderModel->volumeId = $rootFolder->volumeId;
                $folderModel->path = $volumeSubpath.$cart->shortNumber.'/';

                $assets->createFolder($folderModel);

                $orderFolder = $folderModel;
            } catch (AssetException $exception) {
                return $this->asErrorJson($exception->getMessage());
            }
        }

        // Final order folder check
        if (!$orderFolder) {
            return $this->asErrorJson(Craft::t('print-shop', 'Unable to upload files at this time.'));
        }


        /**
         * Make a folder for the customer files inside the order folder
         */
        // Try for the customer files folder - in case it already exists
        $customerFilesFolder = $assets->findFolder([
            'parentId' => $orderFolder->id,
            'name' => Craft::t('print-shop','Customer Files'),
            'path' => $orderFolder->path.Craft::t('print-shop','Customer-Files').'/',
        ]);

        // Check if we got one
        if (!$customerFilesFolder) {
            // We didn’t, so create it
            try {
                $folderModel = new VolumeFolder();
                $folderModel->name = Craft::t('print-shop','Customer Files');
                $folderModel->parentId = $orderFolder->id;
                $folderModel->volumeId = $orderFolder->volumeId;
                $folderModel->path = $orderFolder->path.Craft::t('print-shop','Customer-Files').'/';

                $assets->createFolder($folderModel);

                $customerFilesFolder = $folderModel;
            } catch (AssetException $exception) {
                return $this->asErrorJson($exception->getMessage());
            }
        }

        // Final customer files folder check
        if (!$customerFilesFolder) {
            return $this->asErrorJson(Craft::t('print-shop', 'Unable to upload files at this time.'));
        }


        /**
         * Also make a folder for the proofs whilst we’re here
         */
        // Try for the proofs files folder - in case it already exists
        $proofsFolder = $assets->findFolder([
            'parentId' => $orderFolder->id,
            'name' => Craft::t('print-shop','Proofs'),
            'path' => $orderFolder->path.Craft::t('print-shop','Proofs').'/',
        ]);

        // Check if we got one
        if (!$proofsFolder) {
            // We didn’t, so create it
            try {
                $folderModel = new VolumeFolder();
                $folderModel->name = Craft::t('print-shop','Proofs');
                $folderModel->parentId = $orderFolder->id;
                $folderModel->volumeId = $orderFolder->volumeId;
                $folderModel->path = $orderFolder->path.Craft::t('print-shop','Proofs').'/';

                $assets->createFolder($folderModel);

                $proofsFolder = $folderModel;
            } catch (AssetException $exception) {
                return $this->asErrorJson($exception->getMessage());
            }
        }

        // Final proofs folder check
        if (!$proofsFolder) {
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
            $asset->newFolderId = $customerFilesFolder->id;
            $asset->volumeId = $customerFilesFolder->volumeId;
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
     * Deletes a File
     */
//    public function actionDelete()
//    {
//
//        $this->requirePostRequest();
//        $id = craft()->request->getRequiredPost('id');
//
//        $success = craft()->orderAssets_files->deleteOrderAssetFileById($id);
//
//        if ($success)
//        {
//            if (craft()->request->isAjaxRequest)
//            {
//                $this->returnJson(array(
//                    'success' => true,
//                    'message' => Craft::t('File deleted.'),
//                ));
//            }
//
//            craft()->userSession->setNotice(Craft::t('File deleted.'));
//            $this->redirectToPostedUrl();
//        }
//        else
//        {
//            if (craft()->request->isAjaxRequest)
//            {
//                $this->returnJson(array(
//                    'success' => false,
//                    'message' => Craft::t('Sorry there was a problem, please try again.'),
//                ));
//            }
//            craft()->userSession->setError(Craft::t('Sorry there was a problem, please try again.'));
//        }
//
//    }

    /**
     * Downloads the Asset from a File model, modified from AssetsController.php
     *
     * @return Response
     * @throws BadRequestHttpException
     */
    public function actionDownload(): Response
    {
        $fileUid = Craft::$app->getRequest()->getRequiredParam('uid');
        $fileId = Db::idByUid('{{%printshop_files}}', $fileUid);

        $file = PrintShop::$plugin->files->getFileById($fileId);
        if (!$file) {
            throw new BadRequestHttpException(Craft::t('print-shop', 'The File you’re trying to access does not exist.'));
        }
        
        $asset = $file->getAsset();
        if (!$asset) {
            throw new BadRequestHttpException(Craft::t('app', 'The Asset you’re trying to download does not exist.'));
        }

        // All systems go, engage hyperdrive! (so PHP doesn't interrupt our stream)
        App::maxPowerCaptain();
        $localPath = $asset->getCopyOfFile();

        $response = Craft::$app->getResponse()->sendFile($localPath, $asset->filename);
        FileHelper::unlink($localPath);

        return $response;
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
