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
use angellco\printshop\models\Settings;
use angellco\printshop\PrintShop;
use angellco\printshop\records\Proof as ProofRecord;

use Craft;
use craft\base\Component;
use craft\elements\Asset;
use craft\helpers\Db;
use yii\web\ServerErrorHttpException;

/**
 * @author    Angell & Co
 * @package   PrintShop
 * @since     2.0.0
 */
class Proofs extends Component
{
    // Public Methods
    // =========================================================================

    /**
     * Returns a Proof by its id
     *
     * @param null $id
     * @param bool $expandProofMethods
     *
     * @return Proof|bool|null
     */
    public function getProofById($id = null, $expandProofMethods = false)
    {
        if (!$id) {
            return false;
        }

        $result = ProofRecord::find()
            ->where(['id' => $id])
            ->one();

        if (!$result) {
            return null;
        }

        $proof = new Proof($result);

        if ($expandProofMethods) {
            $proof->setFile();
            $proof->setAsset();
            $proof->setDate();
        }

        return $proof;
    }

    /**
     * Returns a Proof by its uid
     *
     * @param null $uid
     * @param bool $expandProofMethods
     *
     * @return Proof|bool|null
     */
    public function getProofByUid($uid = null, $expandProofMethods = false)
    {
        if (!$uid) {
            return false;
        }

        $result = ProofRecord::find()
            ->where(['uid' => $uid])
            ->one();

        if (!$result) {
            return null;
        }

        $proof = new Proof($result);

        if ($expandProofMethods) {
            $proof->setFile();
            $proof->setAsset();
            $proof->setDate();
        }

        return $proof;
    }

    /**
     * Returns an array of Proofs for a given File ID
     *
     * @param null $fileId
     * @param bool $expandProofMethods
     *
     * @return array
     */
    public function getProofsByFileId($fileId = null, $expandProofMethods = false)
    {
        if (!$fileId) {
            return [];
        }

        $results = ProofRecord::find()
            ->where(['fileId' => $fileId])
            ->orderBy('dateCreated asc')
            ->all();

        if (!$results) {
            return [];
        }

        $proofs = [];
        foreach ($results as $result) {
            $proof = new Proof($result);

            if ($expandProofMethods) {
                $proof->setFile();
                $proof->setAsset();
                $proof->setDate();
            }

            $proofs[] = $proof;
        }

        return $proofs;
    }

    /**
     * Save a Proof
     *
     * @param Proof $proof
     *
     * @return bool
     * @throws \Throwable
     */
    public function saveProof(Proof $proof)
    {
        if ($proof->id) {
            $proofRecord = ProofRecord::find()
                ->where(['id' => $proof->id])
                ->one();

            if (!$proofRecord) {
                throw new ServerErrorHttpException(Craft::t('print-shop', 'No Proofs exist with the ID “{id}”', ['id' => $proof->id]));
            }
        } else {
            $proofRecord = new ProofRecord();
        }

        $proofRecord->fileId        = $proof->fileId;
        $proofRecord->assetId       = $proof->assetId;
        $proofRecord->status        = $proof->status;
        $proofRecord->staffNotes    = $proof->staffNotes;
        $proofRecord->customerNotes = $proof->customerNotes;

        // Validate
        if (!$proofRecord->validate()) {
            Craft::info('Proof not saved due to validation error: ' . print_r($proofRecord->getErrors(), true), __METHOD__);
            return false;
        }

        $transaction = Craft::$app->getDb()->beginTransaction();
        try {

            // Save it!
            $proofRecord->save(false);

            // Now that we have an ID, save it on the model
            if (!$proof->id) {
                $proof->id = $proofRecord->id;
            }

            $transaction->commit();

            // Before we send the email, make an order history model
            $lineItem = $proof->getFile()->getLineItem();
            if (!$lineItem) {
                throw new ServerErrorHttpException(Craft::t('print-shop', 'Couldn’t get line item for File on Proof with ID “{id}”', ['id' => $proof->id]));
            }

            $order = $lineItem->getOrder();
            if (!$order) {
                throw new ServerErrorHttpException(Craft::t('print-shop', 'Couldn’t get order for File on Proof with ID “{id}”', ['id' => $proof->id]));
            }

            // Proofs sent
            /** @var Settings $pluginSettings */
            $pluginSettings = PrintShop::$plugin->getSettings();
            $statusUid = $pluginSettings->proofsSentStatusUid;
            $message = Craft::t('print-shop', "Proof added by staff.");

            // Proof Approved
            if ($proof->status === Proof::STATUS_APPROVED) {
                $statusUid = $pluginSettings->proofsApprovedStatusUid;
                $message = Craft::t('print-shop', "Proof approved by customer.");
            }

            // Proof Rejected
            if ($proof->status === Proof::STATUS_REJECTED) {
                $statusUid = $pluginSettings->proofsRejectedStatusUid;
                $message = Craft::t('print-shop', "Proof rejected by customer.");
            }

            // Save the status on the Order if its changed, this also
            // generates a new History line
            $orderStatus = $order->getOrderStatus();
            if (!$orderStatus || $orderStatus->uid !== $statusUid) {
                $statusId = Db::idByUid('{{%commerce_orderstatuses}}', $statusUid);
                $order->orderStatusId = $statusId;
                $order->message = $message;
                Craft::$app->getElements()->saveElement($order);
            }

            // TODO: here we are
//            // If the proof status is "new" we can try and email the customer
//            if ($model->status === 'new') {
//
//                // Get the email
//                /** @var Commerce_EmailModel $email */
//                $email = craft()->commerce_emails->getEmailById(5);
//                if ($email) {
//
//                    // Smush the proof onto the message field ... :/
//                    $order->message = ['proof' => $model];
//
//                    $orderHistories = $order->getHistories();
//                    $orderHistory = $orderHistories[0];
//
//                    craft()->commerce_emails->sendEmail($email, $order, $orderHistory);
//                }
//
//            }

        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }

        return true;
    }

    /**
     * Deletes a Proof by its id, including the Asset element
     *
     * @param $proofId
     *
     * @return bool|int
     * @throws \Throwable
     */
    public function deleteFileById($proofId)
    {
        if (!$proofId) {
            return false;
        }

        /** @var Proof $proof */
        $proof = $this->getProofById($proofId);

        if (!$proof) {
            return false;
        }

        // Remove the asset element
        Craft::$app->getElements()->deleteElementById($proof->assetId, Asset::class);

        // Delete the Proof record
        return ProofRecord::deleteAll(['id' => $proof->id]);
    }
}
