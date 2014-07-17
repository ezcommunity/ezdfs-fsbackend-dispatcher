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
    /**
     * Handlers based on path (as key)
     * @var eZDFSFileHandlerDFSBackendInterface[string] */
    private $pathHandlers = array();

    /**
     * The default handler, used when no {@see $pathHandlers} matches
     * @var eZDFSFileHandlerDFSBackendInterface
     */
    private $defaultHandler;

    /**
     * @param eZDFSFileHandlerDFSBackendInterface $defaultHandler
     * @param eZDFSFileHandlerDFSBackendInterface[] $pathHandlers
     */
    public function __construct( eZDFSFileHandlerDFSBackendInterface $defaultHandler, array $pathHandlers = array() )
    {
        foreach ( $pathHandlers as $supportedPath => $handler )
        {
            if ( !$handler instanceof eZDFSFileHandlerDFSBackendInterface )
            {
                throw new InvalidArgumentException( get_class( $handler ) . " does not implement eZDFSFileHandlerDFSBackendInterface" );
            }
        }

        $this->defaultHandler = $defaultHandler;
        $this->pathHandlers = $pathHandlers;
    }

    /**
     * Returns the FSHandler for $path
     * @param $path
     * @return eZDFSFileHandlerDFSBackendInterface
     * @throws OutOfRangeException If no handler supports $path
     */
    public function getHandler( $path )
    {
        foreach ( $this->pathHandlers as $supportedPath => $handler )
        {
            if ( is_array( $path ) )
            {
                foreach ( $path as $p )
                {
                    if ( strstr( $p, $supportedPath ) !== false )
                    {
                        return $handler;
                    }
                }
            }
            else
            {
                if ( strstr( $path, $supportedPath ) !== false )
                {
                    return $handler;
                }
            }
        }

        return $this->defaultHandler;
    }

    public function getAllHandlers()
    {
        $handlers = array_values( $this->pathHandlers );
        $handlers[] = $this->defaultHandler;
        return $handlers;
    }

    /**
     * Builds a registry using either the provided configuration, or settings from self::getConfiguration
     * @return self
     */
    public static function build()
    {
        $ini = eZINI::instance( 'file.ini' );
        $defaultHandler = eZDFSFileHandlerBackendFactory::buildHandler(
            $ini->variable( 'DispatchableDFS', 'DefaultBackend' )
        );

        $pathHandlers = array();
        foreach ( $ini->variable( 'DispatchableDFS', 'PathBackends' ) as $supportedPath => $backendClass )
        {
            // @todo Make it possible to use a Symfony2 service
            $pathHandlers[$supportedPath] = eZDFSFileHandlerBackendFactory::buildHandler( $backendClass );
        }

        return new static( $defaultHandler, $pathHandlers );
    }
}
