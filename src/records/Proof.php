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
use craft\db\ActiveRecord;

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
        return '{{%printshop_proof}}';
    }
}
