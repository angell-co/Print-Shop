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

use angellco\printshop\PrintShop;
use angellco\printshop\models\Proof;
use angellco\printshop\models\Settings;
use angellco\printshop\records\Proof as ProofRecord;

use Craft;
use craft\base\Component;
use craft\commerce\elements\Order;
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
     * Returns the latest proof for this File ID
     *
     * @param $fileId
     *
     * @return Proof|bool|null
     */
    public function getLatestProofByFileId($fileId)
    {
        if (!$fileId) {
            return false;
        }

        $result = ProofRecord::find()
            ->where(['fileId' => $fileId])
            ->orderBy('dateCreated asc')
            ->one();

        if (!$result) {
            return null;
        }

        return new Proof($result);
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

            // Now that we have IDs, save them on the model
            if (!$proof->id) {
                $proof->id = $proofRecord->id;
            }
            if (!$proof->uid) {
                $proof->uid = $proofRecord->uid;
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

            // If the proof status is "new" we can try and email the customer
            if ($proof->status === Proof::STATUS_NEW) {

                // Get the email
                $emailId = Db::idByUid('{{%commerce_emails}}', $pluginSettings->proofEmailUid);
                $email = PrintShop::$commerce->emails->getEmailById($emailId);
                if ($email) {

                    // Smush the proof onto the order history ... :/
                    $orderHistories = $order->getHistories();
                    $orderHistory = $orderHistories[0];
                    $orderHistory->message = [
                        'proof' => $proof
                    ];

                    // Send the email
                    PrintShop::$commerce->emails->sendEmail($email, $order, $orderHistory);
                }

            }

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
    public function deleteProofById($proofId)
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

    /**
     * Returns the status html for the order index page attributes
     *
     * @param Order $order
     *
     * @return string
     */
    public function getProofingStatusHtml(Order $order): string
    {
        $lineItemsThatNeedProofs = 0;
        $statuses = [
            Proof::STATUS_NEW => 0,
            Proof::STATUS_APPROVED => 0,
            Proof::STATUS_REJECTED => 0,
        ];

        foreach ($order->getLineItems() as $lineItem) {

            $file = PrintShop::$plugin->files->getFileByLineItemId($lineItem->id);
            if ($file) {

                $lineItemsThatNeedProofs++;

                $latestProof = $this->getLatestProofByFileId($file->id);
                if ($latestProof) {
                    $statuses[$latestProof->status]++;
                }
            }

        }


        // First up, are they all approved?
        if ($lineItemsThatNeedProofs && $lineItemsThatNeedProofs === $statuses[Proof::STATUS_APPROVED]) {
            return "<span class='status green' style='margin-right: 2px;'></span> ".Craft::t('print-shop', 'All approved');
        }


        // If not, we need to work out the details
        $totalProofs = $statuses[Proof::STATUS_NEW] + $statuses[Proof::STATUS_APPROVED] + $statuses[Proof::STATUS_REJECTED];
        $missingProofs = $lineItemsThatNeedProofs - $totalProofs;
        $statusLines = [];

        // If we have any missing entirely
        if ($missingProofs) {
            $statusLines[] = "<span class='status red' style='margin-right: 2px;'></span> ".Craft::t('print-shop', '{num} needed', ['num' => $missingProofs]);
        }

        // Pending
        if ($statuses[Proof::STATUS_NEW]) {
            $statusLines[] = "<span class='status' style='margin-right: 2px;'></span> ".Craft::t('print-shop', '{num} pending', ['num' => $statuses[Proof::STATUS_NEW]]);
        }

        // Approved
        if ($statuses[Proof::STATUS_APPROVED]) {
            $statusLines[] = "<span class='status green' style='margin-right: 2px;'></span> ".Craft::t('print-shop', '{num} approved', ['num' => $statuses[Proof::STATUS_APPROVED]]);
        }

        // Rejected
        if ($statuses[Proof::STATUS_REJECTED]) {
            $statusLines[] = "<span class='status red' style='margin-right: 2px;'></span> ".Craft::t('print-shop', '{num} rejected', ['num' => $statuses[Proof::STATUS_REJECTED]]);
        }

        // Output the array if we have anything in it
        if (count($statuses)) {
            return implode(', ', $statusLines);
        }

        return '';
    }

}
