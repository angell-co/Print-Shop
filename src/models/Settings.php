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
 * @property string $filesVolumeUid
 * @property string $filesVolumeSubpath
 * @property string $proofsVolumeUid
 * @property string $proofsVolumeSubpath
 *
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
            [
                [
                    'filesVolumeUid',
                    'filesVolumeSubpath',
                    'proofsVolumeUid',
                    'proofsVolumeSubpath'
                ],
                'string'
            ],
            [
                [
                    'filesVolumeUid',
                    'proofsVolumeUid',
                ],
                'required'
            ],
        ];
    }
}
