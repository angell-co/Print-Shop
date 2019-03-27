<?php
/**
 * Print Shop plugin for Craft CMS 3.x
 *
 * Everything you need to build a print shop with Craft Commerce 2.
 *
 * @link      https://angell.io
 * @copyright Copyright (c) 2019 Angell & Co
 */

namespace angellco\printshop\services;

use angellco\printshop\PrintShop;

use Craft;
use craft\base\Component;

/**
 * @author    Angell & Co
 * @package   PrintShop
 * @since     2.0.0
 */
class Proofs extends Component
{
    // Public Methods
    // =========================================================================

    /*
     * @return mixed
     */
    public function exampleService()
    {
        $result = 'something';
        // Check our Plugin's settings for `someAttribute`
        if (PrintShop::$plugin->getSettings()->someAttribute) {
        }

        return $result;
    }
}
