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

use angellco\portal\models\Target;
use angellco\portal\records\Target as TargetRecord;
use angellco\printshop\models\File;
use angellco\printshop\PrintShop;

use angellco\printshop\records\File as FileRecord;
use Craft;
use craft\base\Component;
use craft\elements\Asset;
use Craft\OrderAssets_FileModel;

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
        $record = FileRecord::find()->all();

        if (!$records) {
            return false;
        }

        $models = [];

        foreach ($records as $record) {
            $models[] = $this->_createFileFromRecord($record);
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

        $record = FileRecord::find()
            ->where(['id' => $id])
            ->one();

        if (!$record) {
            return false;
        }

        return $this->_createFileFromRecord($record);
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

        $record = FileRecord::find()
            ->where(['lineItemId' => $lineItemId])
            ->one();

        if (!$record) {
            return false;
        }

        return $this->_createFileFromRecord($record);
    }

    /**
     * Save a File
     *
     * @param File $file
     *
     * @return bool
     * @throws \Throwable
     */
    public function saveFile(File $file)
    {
        if ($file->id) {
            $fileRecord = FileRecord::find()
                ->where(['id' => $file->id])
                ->one();

            if (!$fileRecord) {
                throw new Exception(Craft::t('print-shop', 'No Files exist with the ID “{id}”', ['id' => $file->id]));
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

            // Delete any other records that exist for this line item
            $records = FileRecord::find()
                ->where(['lineItemId' => $file->lineItemId])
                ->all();

            foreach ($records as $record) {
                if ($record->id !== $file->id) {
                    $this->deleteFileById($record->id);
                }
            }

            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }

        return true;
    }

    /**
     * Deletes a File by its id
     *
     * @param int $fileId
     * @throws \Exception
     * @return bool
     */
    public function deleteFileById($fileId)
    {
        if (!$fileId) {
            return false;
        }

        $transaction = Craft::$app->getDb()->beginTransaction();
        try {

            // Remove the asset element - this will cascade down to our record
            $file = $this->getFileById($fileId);
            $result = Craft::$app->getElements()->deleteElementById($file->assetId, Asset::class);

            // XXX Why is this not deleting?

            Craft::dd($result);

            $transaction->commit();

            return (bool) $result;
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    // Private Methods
    // =========================================================================

    /**
     * Creates a File with attributes from a FileRecord.
     *
     * @param FileRecord|null $fileRecord
     *
     * @return File|null
     */
    private function _createFileFromRecord(FileRecord $fileRecord = null)
    {
        if (!$fileRecord) {
            return null;
        }

        $file = new File($fileRecord->toArray([
            'id',
            'assetId',
            'lineItemId',
        ]));

        return $file;
    }
    
}

