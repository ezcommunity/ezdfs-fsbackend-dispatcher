<?php
/**
 * This file is part of the eZ Publish Legacy package
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributd with this source code.
 * @version //autogentag//
 */

interface eZDFSFileHandlerDispatchableDFSBackendInterface
{
    /**
     * Checks if $path is supported by this handler
     * @param string $path
     * @return bool
     */
    public function supports( $path );
}
