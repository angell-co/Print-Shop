<?php
/**
 * Print Shop plugin for Craft CMS 3.x
 *
 * Everything you need to build a print shop with Craft Commerce 2.
 *
 * @link      https://angell.io
 * @copyright Copyright (c) 2019 Angell & Co
 */

namespace angellco\printshop\services;

use angellco\printshop\models\Proof;
use angellco\printshop\PrintShop;
use angellco\printshop\models\File;
use angellco\printshop\records\File as FileRecord;

use Craft;
use craft\base\Component;
use craft\commerce\models\LineItem;
use craft\elements\Asset;
use yii\db\Exception;
use yii\web\ServerErrorHttpException;

/**
 * @author    Angell & Co
 * @package   PrintShop
 * @since     2.0.0
 */
class Files extends Component
{
    // Public Methods
    // =========================================================================

    /**
     * Returns an array of all the order assets
     *
     * @return array|bool
     */
    public function getAllFiles()
    {
        $records = FileRecord::find()->all();

        if (!$records) {
            return false;
        }

        $models = [];

        foreach ($records as $record) {
            $models[] = new File($record);
        }

        return $models;
    }

    /**
     * Returns a File model by its id
     *
     * @param null $id
     *
     * @return bool|File
     */
    public function getFileById($id = null)
    {
        if (!$id) {
            return false;
        }

        $result = FileRecord::find()
            ->where(['id' => $id])
            ->one();

        if (!$result) {
            return false;
        }

        return $result ? new File($result) : null;
    }

    /**
     * Returns a File model or false for a given $lineItemId
     *
     * @param null $lineItemId
     *
     * @return bool|File
     */
    public function getFileByLineItemId($lineItemId = null)
    {
        if (!$lineItemId) {
            return false;
        }

        $result = FileRecord::find()
            ->where(['lineItemId' => $lineItemId])
            ->one();

        if (!$result) {
            return false;
        }

        return $result ? new File($result) : null;
    }

    /**
     * Save a File
     *
     * @param File $file
     *
     * @return bool
     * @throws \Throwable
     */
    public function saveFile(File $file): bool
    {
        if ($file->id) {
            $fileRecord = FileRecord::find()
                ->where(['id' => $file->id])
                ->one();

            if (!$fileRecord) {
                throw new ServerErrorHttpException(Craft::t('print-shop', 'No Files exist with the ID “{id}”', ['id' => $file->id]));
            }
        } else {
            $fileRecord = new FileRecord();
        }

        $fileRecord->assetId    = $file->assetId;
        $fileRecord->lineItemId = $file->lineItemId;

        // Validate
        if (!$fileRecord->validate()) {
            Craft::info('File not saved due to validation error: ' . print_r($fileRecord->getErrors(), true), __METHOD__);
            return false;
        }

        $transaction = Craft::$app->getDb()->beginTransaction();
        try {

            // Save it!
            $fileRecord->save(false);

            // Now that we have an ID, save it on the model
            if (!$file->id) {
                $file->id = $fileRecord->id;
            }

            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }

        // Get any other records that exist for this line item
        $records = FileRecord::find()
            ->where(['lineItemId' => $file->lineItemId])
            ->all();

        // Soft delete the assets and hard delete the File Record
        foreach ($records as $record) {
            if ($record->id !== $file->id) {
                $this->deleteFileById($record->id);
            }
        }

        return true;
    }

    /**
     * Deletes a File by its id, including the Asset element but not the
     * LineItem of course.
     *
     * @param $fileId
     *
     * @return bool|int
     * @throws \Throwable
     */
    public function deleteFileById($fileId)
    {
        if (!$fileId) {
            return false;
        }

        /** @var File $file */
        $file = $this->getFileById($fileId);

        if (!$file) {
            return false;
        }

        // Remove the asset element
        Craft::$app->getElements()->deleteElementById($file->assetId, Asset::class);

        // Delete the File record
        return FileRecord::deleteAll(['id' => $file->id]);
    }


    /**
     * @param File     $file
     * @param LineItem $lineItem
     * @param bool     $copyLatestProof
     *
     * @throws Exception
     * @throws \Throwable
     * @throws \craft\errors\ElementNotFoundException
     * @throws \yii\base\Exception
     */
    public function copyFileToNewLineItem(File $file, LineItem $lineItem, $copyLatestProof = false)
    {
        // Get the order and asset folders
        $order = $lineItem->getOrder();

        if (!$order) {
            throw new Exception(Craft::t('print-shop', 'Order not found.'));
        }

        $folders = PrintShop::$plugin->folders->getFoldersForOrder($order->number);


        // First copy over the asset to the new order folder
        $asset = $file->getAsset();
        if (!$asset) {
            throw new Exception(Craft::t('print-shop', 'There was an error copying over the files.'));
        }
        $assetCopyTempPath = $asset->getCopyOfFile();

        $newAsset = new Asset();
        $newAsset->tempFilePath = $assetCopyTempPath;
        $newAsset->filename = $asset->filename;
        $newAsset->newFolderId = $folders['files']->id;
        $newAsset->volumeId = $folders['files']->volumeId;
        $newAsset->avoidFilenameConflicts = true;
        $newAsset->setScenario(Asset::SCENARIO_CREATE);

        if (!Craft::$app->getElements()->saveElement($newAsset)) {
            throw new Exception(Craft::t('print-shop', 'There was an error copying over the files.'));
        }


        // Make a new File model with the new asset and line item ids
        $newFile = new File([
            'assetId' => $newAsset->id,
            'lineItemId' => $lineItem->id
        ]);

        // Save it
        $result = $this->saveFile($newFile);


        // Process the latest proof
        if ($result && $copyLatestProof) {
            $proof = $file->getLatestProof();
            if ($proof) {

                // First copy over the asset to the new order folder
                $proofAsset = $proof->getAsset();
                if (!$proofAsset) {
                    throw new Exception(Craft::t('print-shop', 'There was an error copying over the proofs.'));
                }
                $proofAssetCopyTempPath = $proofAsset->getCopyOfFile();

                $newProofAsset = new Asset();
                $newProofAsset->tempFilePath = $proofAssetCopyTempPath;
                $newProofAsset->filename = $proofAsset->filename;
                $newProofAsset->newFolderId = $folders['proofs']->id;
                $newProofAsset->volumeId = $folders['proofs']->volumeId;
                $newProofAsset->avoidFilenameConflicts = true;
                $newProofAsset->setScenario(Asset::SCENARIO_CREATE);

                if (!Craft::$app->getElements()->saveElement($newProofAsset)) {
                    throw new Exception(Craft::t('print-shop', 'There was an error copying over the proofs.'));
                }


                // Make the new proof
                $newProof = new Proof([
                    'fileId' => $newFile->id,
                    'assetId' => $newProofAsset->id,
                    'status' => $proof->status,
                    'staffNotes' => $proof->staffNotes,
                    'customerNotes' => $proof->customerNotes
                ]);

                // Save it
                $result = PrintShop::$plugin->proofs->saveProof($newProof);
            }
        }

        return $result;
    }

}

