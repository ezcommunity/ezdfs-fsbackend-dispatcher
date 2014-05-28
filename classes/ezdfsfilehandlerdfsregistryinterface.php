<?php
/**
 * This file is part of the eZ Publish Legacy package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 * @package kernel
 */

interface eZDFSFileHandlerDFSRegistryInterface
{
    /**
     * Returns the FSHandler for $path
     * @param $path
     * @return eZDFSFileHandlerDFSBackendInterface
     */
    public function getHandler( $path );

    /**
     * Returns all the registered FS handlers
     * @return eZDFSFileHandlerDFSBackendInterface[]
     */
    public function getAllHandlers();
}
