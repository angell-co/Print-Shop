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
    public $volumeUid;

    /**
     * @var string
     */
    public $volumeSubpath;

    /**
     * @var string
     */
    public $proofsSentStatusUid;

    /**
     * @var string
     */
    public $proofsApprovedStatusUid;

    /**
     * @var string
     */
    public $proofsRejectedStatusUid;

    /**
     * @var string
     */
    public $proofEmailUid;


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
                    'volumeUid',
                    'volumeSubpath',
                    'proofsSentStatusUid',
                    'proofsApprovedStatusUid',
                    'proofsRejectedStatusUid',
                    'proofEmailUid',
                ],
                'string'
            ],
            [
                [
                    'volumeUid',
                    'proofsSentStatusUid',
                    'proofsApprovedStatusUid',
                    'proofsRejectedStatusUid',
                    'proofEmailUid',
                ],
                'required'
            ],
        ];
    }
}
