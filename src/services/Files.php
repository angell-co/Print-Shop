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

use angellco\printshop\models\File;
use angellco\printshop\records\File as FileRecord;

use Craft;
use craft\base\Component;
use craft\elements\Asset;
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


    public function copyFileToNewLineItem($file, $lineItem, $copyLatestProof = false)
    {
        Craft::dd($file);
    }

}

