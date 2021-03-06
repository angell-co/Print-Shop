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
use craft\helpers\DateTimeHelper;

/**
 * @author    Angell & Co
 * @package   PrintShop
 * @since     2.0.0
 */
class Proof extends Model
{
    // Constants
    // =========================================================================

    const STATUS_NEW = 'new';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    // Public Properties
    // =========================================================================

    /**
     * @var int ID
     */
    public $id;

    /**
     * @var int File ID
     */
    public $fileId;

    /**
     * @var int Asset ID
     */
    public $assetId;

    /**
     * @var string Status
     */
    public $status;

    /**
     * @var string Staff Notes
     */
    public $staffNotes;

    /**
     * @var string Customer Notes
     */
    public $customerNotes;

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

    public $file;
    public $asset;

    /**
     * @var string
     */
    public $date;

    // Public Methods
    // =========================================================================

    /**
     * Returns the File set on the model
     *
     * @return File|bool
     */
    public function getFile()
    {
        if (!$this->file) {
            $this->setFile();
        }

        return $this->file;
    }

    /**
     * Setter for $file
     *
     * @param null $file
     */
    public function setFile($file = null)
    {
        if (!$this->file) {
            $this->file = PrintShop::$plugin->files->getFileById($this->fileId);
        }
    }

    /**
     * Returns the Asset set on the model
     *
     * @return \craft\elements\Asset|null
     */
    public function getAsset()
    {
        if (!$this->asset) {
            $this->setAsset();
        }

        return $this->asset;
    }

    /**
     * Setter for $asset
     *
     * @param null $asset
     */
    public function setAsset($asset = null)
    {
        if ($asset === null) {
            $this->asset = Craft::$app->getAssets()->getAssetById($this->assetId);
        }
    }

    /**
     * Returns the dateCreated prop as an ISO-8601 string
     *
     * @return false|string
     */
    public function getDate()
    {
        if (!$this->date) {
            $this->setDate();
        }

        return $this->date;
    }

    /**
     * Setter for $date
     *
     * @param null $date
     */
    public function setDate($date = null)
    {
        if ($date === null) {
            $this->date = DateTimeHelper::toIso8601($this->dateCreated);
        }
    }

    /**
     * Returns an array of all proofs that are related to the same File as
     * this one is
     */
    public function getAllProofs()
    {
        return PrintShop::$plugin->proofs->getProofsByFileId($this->fileId);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = parent::rules();
        $rules[] = [['id', 'fileId', 'assetId'], 'number', 'integerOnly' => true];
        $rules[] = [['status'], 'in', 'range' => [static::STATUS_NEW, static::STATUS_APPROVED, static::STATUS_REJECTED]];
        $rules[] = [['fileId', 'assetId', 'status'], 'required'];
        return $rules;
    }
}
