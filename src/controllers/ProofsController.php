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

use angellco\printshop\models\Proof;
use angellco\printshop\PrintShop;

use Craft;
use craft\helpers\App;
use craft\helpers\FileHelper;
use craft\helpers\Json;
use craft\web\Controller;
use yii\web\BadRequestHttpException;
use yii\web\Response;

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
    protected $allowAnonymous = ['approve', 'reject'];

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
            return $this->asErrorJson(Craft::t('print-shop', 'Couldn’t get custom file.'));
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



//    /**
//     * Approves an existing proof
//     *
//     * @throws HttpException
//     */
//    public function actionApprove()
//    {
//        $proof = $this->_getProofFromPost();
//
//        if ($proof) {
//
//            $proof->status = 'approved';
//
//            if (craft()->orderAssets_proofs->saveOrderAssetProof($proof)) {
//
//
//                if (craft()->request->isAjaxRequest) {
//                    $this->returnJson([
//                        'success' => true,
//                    ]);
//                }
//                $this->redirectToPostedUrl();
//            }
//        }
//
//        if (craft()->request->isAjaxRequest) {
//            $this->returnJson([
//                'error' => 'Sorry there was an error updating your proof.'
//            ]);
//        }
//        craft()->userSession->setError(Craft::t('Sorry there was an error updating your proof.'));
//
//    }
//
//    /**
//     * Rejects an existing proof
//     *
//     * @throws HttpException
//     */
//    public function actionReject()
//    {
//        $proof = $this->_getProofFromPost();
//
//        if ($proof) {
//
//            $proof->status = 'rejected';
//
//            if (craft()->orderAssets_proofs->saveOrderAssetProof($proof)) {
//
//
//                if (craft()->request->isAjaxRequest) {
//                    $this->returnJson([
//                        'success' => true,
//                    ]);
//                }
//                $this->redirectToPostedUrl();
//            }
//        }
//
//        if (craft()->request->isAjaxRequest) {
//            $this->returnJson([
//                'error' => 'Sorry there was an error updating your proof.'
//            ]);
//        }
//        craft()->userSession->setError(Craft::t('Sorry there was an error updating your proof.'));
//
//    }
//
//
//    // Private Methods
//    // =========================================================================
//
//    /**
//     * Preps the proof from post
//     *
//     * @return bool
//     * @throws HttpException
//     */
//    private function _getProofFromPost()
//    {
//        $proofId = craft()->request->getRequiredPost('id');
//        $proof = craft()->orderAssets_proofs->getOrderAssetProofById($proofId);
//
//        if ($proof) {
//            $proof->customerNotes = craft()->request->getPost('notes');
//            return $proof;
//        }
//
//        return false;
//    }
    
}
