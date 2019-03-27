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

/**
 * @author    Angell & Co
 * @package   PrintShop
 * @since     2.0.0
 */
class Settings extends Model
{
    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public $filesVolumeUid;

    /**
     * @var string
     */
    public $filesVolumeSubpath;

    /**
     * @var string
     */
    public $proofsVolumeUid;

    /**
     * @var string
     */
    public $proofsVolumeSubpath;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['filesVolumeUid', 'string'],
            ['filesVolumeSubpath', 'string'],
            ['proofsVolumeUid', 'string'],
            ['proofsVolumeSubpath', 'string'],
        ];
    }
}
