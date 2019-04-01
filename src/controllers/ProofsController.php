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

use Craft;
use craft\web\Controller;
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


        // TODO
//
//
//        $postData = craft()->request->getPost('fields');
//
//        $newProof = isset($postData['newProof']) ? $postData['newProof'] : false;
//
//        if ($newProof && is_array($newProof)) {
//
//
//            $lineItemId = array_keys($newProof)[0];
//            $assetId = isset($newProof[$lineItemId]['asset'][0]) ? $newProof[$lineItemId]['asset'][0] : null;
//
//            if (!$assetId) {
//                $this->returnErrorJson(Craft::t('You must add a file.'));
//            }
//
//            $notes = $newProof[$lineItemId]['notes'];
//            $orderAssetFileId = $newProof[$lineItemId]['orderAssetFileId'];
//
//            $model = new OrderAssets_ProofModel();
//            $model->orderAssetFileId = $orderAssetFileId;
//            $model->assetId = $assetId;
//            $model->notes = $notes;
//
//            if ($savedProof = craft()->orderAssets_proofs->saveOrderAssetProof($model)) {
//                $this->returnJson([
//                    'success' => true,
//                    'rowHtml' => craft()->templates->render('orderAssets/fieldtype/_row', [
//                        'proof' => $savedProof
//                    ])
//                ]);
//            }
//        }
//
//        $this->returnErrorJson(Craft::t('An unknown error occurred.'));

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
