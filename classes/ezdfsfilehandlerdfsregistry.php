<?php
/**
 * This file is part of the eZ Publish Legacy package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 * @package kernel
 */

/**
 * Holds a registry of DFS FS handlers.
 *
 * Returns the appropriate one
 */
class eZDFSFileHandlerDFSRegistry implements eZDFSFileHandlerDFSRegistryInterface
{
    /** @var eZDFSFileHandlerDFSBackendInterface[] */
    private $fsHandlers = array();

    /**
     * @param eZDFSFileHandlerDFSBackendInterface[] $fsHandlers
     */
    public function __construct( array $fsHandlers = array() )
    {
        $this->fsHandlers = $fsHandlers;
    }

    /**
     * Returns the FSHandler for $path
     * @param $path
     * @return eZDFSFileHandlerDFSBackendInterface
     * @throws OutOfRangeException If no handler supports $path
     */
    public function getHandler( $path )
    {
        foreach ( $this->fsHandlers as $handler )
        {
            if ( $handler->supports( $path ) )
            {
                return $handler;
            }
        }

        throw new OutOfRangeException( "No handler found with support for $path" );
    }
}
