<?php
/**
 * Print Shop plugin for Craft CMS 3.x
 *
 * Everything you need to build a print shop with Craft Commerce 2.
 *
 * @link      https://angell.io
 * @copyright Copyright (c) 2019 Angell & Co
 */

namespace angellco\printshop\records;

use craft\db\ActiveRecord;
use craft\records\Asset;
use yii\db\ActiveQueryInterface;

/**
 * @author    Angell & Co
 * @package   PrintShop
 * @since     2.0.0
 */
class Proof extends ActiveRecord
{
    // Public Static Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%printshop_proofs}}';
    }

    /**
     * Returns the File that this Proof relates to.
     *
     * @return ActiveQueryInterface The relational query object.
     */
    public function getFile(): ActiveQueryInterface
    {
        return $this->hasOne(File::class, ['id' => 'fileId']);
    }

    /**
     * Returns the associated asset with this Proof.
     *
     * @return ActiveQueryInterface The relational query object.
     */
    public function getAsset(): ActiveQueryInterface
    {
        return $this->hasOne(Asset::class, ['id' => 'assetId']);
    }
}
