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

use angellco\printshop\PrintShop;
use angellco\printshop\models\Proof;

use Craft;
use craft\helpers\App;
use craft\helpers\Db;
use craft\helpers\FileHelper;
use craft\helpers\Json;
use craft\web\Controller;
use yii\web\BadRequestHttpException;
use yii\web\Response;
use yii\web\ServerErrorHttpException;

/**
 * @author    Angell & Co
 * @package   PrintShop
 * @since     2.0.0
 */
class ProofsController extends Controller
{

    // Protected Properties
    // =========================================================================

    /**
     * @var    bool|array Allows anonymous access to this controller's actions.
     *         The actions must be in 'kebab-case'
     * @access protected
     */
    protected $allowAnonymous = ['approve', 'reject','download'];

    // Public Methods
    // =========================================================================

    public function actionSave(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $payload = Json::decode(Craft::$app->getRequest()->getRawBody(), true);
        $lineItemId = (isset($payload['lineItemId']) ? $payload['lineItemId'] : null);
        $assetIds = (isset($payload['assetIds']) ? $payload['assetIds'] : null);
        $staffNotes = (isset($payload['staffNotes']) ? $payload['staffNotes'] : null);

        if (!$lineItemId) {
            return $this->asErrorJson(Craft::t('print-shop', 'Missing required line item.'));
        }

        if (!$assetIds) {
            return $this->asErrorJson(Craft::t('print-shop', 'You must add a file.'));
        }

        // Get File by line item ID
        $lineItemFile = PrintShop::$plugin->files->getFileByLineItemId($lineItemId);

        if (!$lineItemFile) {
            return $this->asErrorJson(Craft::t('print-shop', 'Couldn’t get customer file.'));
        }

        // Save the proof
        $proof = new Proof();
        $proof->fileId = $lineItemFile->id;
        $proof->assetId = $assetIds[0];
        $proof->status = Proof::STATUS_NEW;
        $proof->staffNotes = $staffNotes;

        if (!PrintShop::$plugin->proofs->saveProof($proof)) {
            return $this->asErrorJson(Craft::t('print-shop', 'Couldn’t save proof.'));
        }

        return $this->asJson([
            'success' => true,
            'proof' => PrintShop::$plugin->proofs->getProofById($proof->id, true)
        ]);
    }

    /**
     * Downloads the Asset from a Proof model, modified from AssetsController.php
     *
     * @return Response
     * @throws BadRequestHttpException
     */
    public function actionDownload(): Response
    {
        $proofUid = Craft::$app->getRequest()->getRequiredParam('uid');

        $proof = PrintShop::$plugin->proofs->getProofByUid($proofUid);
        if (!$proof) {
            throw new BadRequestHttpException(Craft::t('print-shop', 'The Proof you’re trying to access does not exist.'));
        }

        $asset = $proof->getAsset();
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

    /**
     * Approves an existing proof
     *
     * @return Response|null
     * @throws BadRequestHttpException
     * @throws ServerErrorHttpException
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function actionApprove(): ?Response
    {
        if (Craft::$app->request->isPost || Craft::$app->request->isAjax) {
            $proof = $this->_getProofFromPost();
        } else {
            $proof = $this->_getProofFromUrl();
        }

        if ($proof) {

            $proof->status = Proof::STATUS_APPROVED;

            if (PrintShop::$plugin->proofs->saveProof($proof)) {

                if (Craft::$app->request->isAjax) {
                    return $this->asJson([
                        'success' => true,
                    ]);
                }

                if (Craft::$app->request->isPost) {
                    return $this->redirectToPostedUrl();
                }

                $redirect = Craft::$app->request->getRequiredQueryParam('redirect');
                $redirectUrl = Craft::$app->getSecurity()->validateData($redirect);
                return $this->redirect($redirectUrl);
            }
        }

        if (Craft::$app->request->isAjax) {
            return $this->asErrorJson(Craft::t('print-shop', 'Sorry, there was an error approving your proof.'));
        }

        throw new ServerErrorHttpException(Craft::t('print-shop', 'Sorry, there was an error approving your proof.'));
    }

    /**
     * Rejects an existing proof
     *
     * @return Response|null
     * @throws BadRequestHttpException
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function actionReject(): ?Response
    {
        if (Craft::$app->request->isPost || Craft::$app->request->isAjax) {
            $proof = $this->_getProofFromPost();
        } else {
            $proof = $this->_getProofFromUrl();
        }

        if ($proof) {

            $proof->status = Proof::STATUS_REJECTED;

            if (PrintShop::$plugin->proofs->saveProof($proof)) {


                if (Craft::$app->request->isAjax) {
                    return $this->asJson([
                        'success' => true,
                    ]);
                }

                if (Craft::$app->request->isPost) {
                    return $this->redirectToPostedUrl();
                }

                $redirect = Craft::$app->request->getRequiredQueryParam('redirect');
                $redirectUrl = Craft::$app->getSecurity()->validateData($redirect);
                return $this->redirect($redirectUrl);
            }
        }

        if (Craft::$app->request->isAjax) {
            return $this->asErrorJson(Craft::t('print-shop', 'Sorry, there was an error rejecting your proof.'));
        }

        throw new ServerErrorHttpException(Craft::t('print-shop', 'Sorry, there was an error rejecting your proof.'));
    }

    // Private Methods
    // =========================================================================

    /**
     * Preps the proof from post
     *
     * @return bool
     * @throws BadRequestHttpException
     */
    private function _getProofFromPost()
    {
        $proofId = Craft::$app->request->getRequiredParam('proofId');
        $proof = PrintShop::$plugin->proofs->getProofById($proofId);

        if ($proof) {
            $proof->customerNotes = Craft::$app->request->getParam('customerNotes');
            return $proof;
        }

        return false;
    }

    /**
     * Preps the proof from the URL
     *
     * @return Proof|bool|null
     * @throws BadRequestHttpException
     */
    private function _getProofFromUrl()
    {
        $proofUid = Craft::$app->request->getRequiredParam('proofUid');
        return PrintShop::$plugin->proofs->getProofByUid($proofUid);
    }
}
