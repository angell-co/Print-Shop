<?php
/**
 * Print Shop plugin for Craft CMS 3.x
 *
 * Everything you need to build a print shop with Craft Commerce 2.
 *
 * @link      https://angell.io
 * @copyright Copyright (c) 2019 Angell & Co
 */

namespace angellco\printshop\models;

use angellco\printshop\PrintShop;

use Craft;
use craft\base\Model;
use craft\helpers\UrlHelper;

/**
 * @property int $id ID
 * @property int $assetId Asset ID
 * @property int $lineItemId LineItem ID
 *
 * @author    Angell & Co
 * @package   PrintShop
 * @since     2.0.0
 */
class File extends Model
{
    // Public Properties
    // =========================================================================

    /**
     * @var int ID
     */
    public $id;

    /**
     * @var int Asset ID
     */
    public $assetId;

    /**
     * @var int LineItem ID
     */
    public $lineItemId;

    /**
     * @var \DateTime|null Date created
     */
    public $dateCreated;

    /**
     * @var \DateTime|null Date updated
     */
    public $dateUpdated;

    /**
     * @var string|null Uid
     */
    public $uid;

    // Public Methods
    // =========================================================================

    /**
     * Returns the asset set on the model
     *
     * @return \craft\elements\Asset|null
     */
    public function getAsset()
    {
        return Craft::$app->getAssets()->getAssetById($this->assetId);
    }

    /**
     * Returns the line item set on the model
     *
     * @return \craft\commerce\models\LineItem|null
     */
    public function getLineItem()
    {
        return PrintShop::$commerce->getLineItems()->getLineItemById($this->lineItemId);
    }

    /**
     * Returns an action URL that allows the Asset to be downloaded
     *
     * @return string
     */
    public function getDownloadUrl()
    {
        return UrlHelper::actionUrl('print-shop/files/download', ['uid' => $this->uid]);
    }

    /**
     * Returns the latest proof for this File ID
     *
     * @return Proof|bool|null
     */
    public function getLatestProof()
    {
        return PrintShop::$plugin->proofs->getLatestProofByFileId($this->id);
    }

    /**
     * Returns all the proofs this File
     *
     * @param bool $expandProofMethods
     *
     * @return array
     */
    public function getProofs($expandProofMethods = false)
    {
        return PrintShop::$plugin->proofs->getProofsByFileId($this->id, $expandProofMethods);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = parent::rules();
        $rules[] = [['id', 'assetId', 'lineItemId'], 'number', 'integerOnly' => true];
        $rules[] = [['assetId', 'lineItemId'], 'required'];
        return $rules;
    }
}
