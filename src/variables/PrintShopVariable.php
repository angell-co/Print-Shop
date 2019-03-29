<?php
/**
 * Print Shop plugin for Craft CMS 3.x
 *
 * Everything you need to build a print shop with Craft Commerce 2.
 *
 * @link      https://angell.io
 * @copyright Copyright (c) 2019 Angell & Co
 */

namespace angellco\printshop\variables;

use angellco\printshop\PrintShop;

use Craft;

/**
 * @author    Angell & Co
 * @package   PrintShop
 * @since     2.0.0
 */
class PrintShopVariable
{
    // Public Methods
    // =========================================================================

    /**
     * Returns a File model for a given LineItem ID
     *
     * @param int $lineItemId
     *
     * @return \angellco\printshop\models\File|bool
     */
    public function getFile($lineItemId)
    {
        return PrintShop::$plugin->files->getFileByLineItemId($lineItemId);
    }

    /**
     * Returns a Proof model by its uid
     *
     * @param string $number
     *
     * @return mixed
     */
    public function getProof($number)
    {
        //
    }

}
