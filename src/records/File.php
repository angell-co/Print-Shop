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

use angellco\printshop\PrintShop;

use Craft;
use craft\commerce\records\LineItem;
use craft\db\ActiveRecord;
use craft\records\Asset;

/**
 * @author    Angell & Co
 * @package   PrintShop
 * @since     2.0.0
 */
class File extends ActiveRecord
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%printshop_files}}';
    }

    /**
     * Returns the associated asset.
     *
     * @return ActiveQueryInterface The relational query object.
     */
    public function getAsset(): ActiveQueryInterface
    {
        return $this->hasOne(Asset::class, ['id' => 'assetId']);
    }

    /**
     * Returns the associated line item.
     *
     * @return ActiveQueryInterface The relational query object.
     */
    public function getLineItem(): ActiveQueryInterface
    {
        return $this->hasOne(LineItem::class, ['id' => 'lineItemId']);
    }

}
