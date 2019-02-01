<?php
/*
 * This file is part of the Pagerfanta package.
 *
 * (c)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DAPClientBundle\Pagination\Pagerfanta;

use Pagerfanta\View\TwitterBootstrap4View;

class DefaultView extends TwitterBootstrap4View
{
    protected function createDefaultTemplate()
    {
        return new Template\DefaultTemplate();
    }
}
