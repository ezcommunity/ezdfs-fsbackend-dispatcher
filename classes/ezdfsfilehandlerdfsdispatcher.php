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
 * DFS FS handler that dispatches calls to the appropriate FS handler
 */
class eZDFSFileHandlerDFSDispatcher implements eZDFSFileHandlerDFSBackendInterface
{
    /** @var eZDFSFileHandlerDFSRegistry */
    private $fsHandlersRegistry = array();

    /**
     * @param eZDFSFileHandlerDFSRegistry $fsHandlers
     */
    public function __construct( eZDFSFileHandlerDFSRegistry $fsHandlersRegistry )
    {
        $this->fsHandlersRegistry = $fsHandlersRegistry;
    }

    /**
     * @return self
     */
    public static function factory()
    {
        $handlers = array();
        $ini = eZINI::instance( 'file.ini' );
        foreach ( $ini->variable( 'eZDFSClusteringSettings', 'DFSBackends' ) as $backend )
        {
            $backendIniGroup = "DFSBackend_$backend";
            $class = $ini->variable( $backendIniGroup, 'Class' );
            if ( $ini->hasVariable( $backendIniGroup, 'FactoryMethod' ) )
            {
                $handler = $class::factory();
            }
            else
            {
                $handler = new $class;
            }
            $handlers[] = $handler;
        }

        return new self( new eZDFSFileHandlerDFSRegistry( $handlers ) );
    }

    /**
     * Returns the FSHandler for $path
     * @param $path
     * @return eZDFSFileHandlerDFSBackendInterface
     */
    private function getHandler( $path )
    {
        return $this->fsHandlersRegistry->getHandler( $path );
    }

    /**
     * Creates a copy of $srcFilePath from DFS to $dstFilePath on DFS
     *
     * @param string $srcFilePath Local source file path
     * @param string $dstFilePath Local destination file path
     */
    public function copyFromDFSToDFS( $srcFilePath, $dstFilePath )
    {
        $srcHandler = $this->getHandler( $srcFilePath );
        $dstHandler = $this->getHandler( $dstFilePath );

        if ( $srcHandler === $dstHandler )
        {
            return $srcHandler->copyFromDFSToDFS( $srcFilePath, $dstFilePath );
        }
        else
        {
            return $dstHandler->createFileOnDFS( $dstFilePath, $srcHandler->getContents( $srcFilePath ) );
        }
    }

    /**
     * Copies the DFS file $srcFilePath to FS
     *
     * @param string $srcFilePath Source file path (on DFS)
     * @param string $dstFilePath
     *        Destination file path (on FS). If not specified, $srcFilePath is
     *        used
     *
     * @return bool
     */
    public function copyFromDFS( $srcFilePath, $dstFilePath = false )
    {
        return $this->getHandler( $srcFilePath )->copyFromDFS( $srcFilePath, $dstFilePath );
    }

    /**
     * Copies the local file $filePath to DFS under the same name, or a new name
     * if specified
     *
     * @param string      $srcFilePath Local file path to copy from
     * @param bool|string $dstFilePath
     *        Optional path to copy to. If not specified, $srcFilePath is used
     *
     * @return bool
     */
    public function copyToDFS( $srcFilePath, $dstFilePath = false )
    {
        return $this->getHandler( $dstFilePath ?: $srcFilePath )->copyToDFS( $srcFilePath, $dstFilePath );
    }

    /**
     * Deletes one or more files from DFS
     *
     * @param string|array $filePath
     *        Single local filename, or array of local filenames
     *
     * @return bool true if deletion was successful, false otherwise
     * @todo Improve error handling using exceptions
     */
    public function delete( $filePath )
    {
        return $this->getHandler( $filePath )->delete( $filePath );
    }

    /**
     * Sends the contents of $filePath to default output
     *
     * @param string   $filePath File path
     * @param int      $startOffset Starting offset
     * @param bool|int $length Length to transmit, false means everything
     *
     * @return bool true, or false if operation failed
     */
    public function passthrough( $filePath, $startOffset = 0, $length = false )
    {
        return $this->getHandler( $filePath )->passthrough( $filePath, $startOffset, $length );
    }

    /**
     * Returns the binary content of $filePath from DFS
     *
     * @param string $filePath local file path
     *
     * @return binary|bool file's content, or false
     * @todo Handle errors using exceptions
     */
    public function getContents( $filePath )
    {
        return $this->getHandler( $filePath )->getContents( $filePath );
    }

    /**
     * Creates the file $filePath on DFS with content $contents
     *
     * @param string $filePath
     * @param binary $contents
     *
     * @return bool
     */
    public function createFileOnDFS( $filePath, $contents )
    {
        return $this->getHandler( $filePath )->createFileOnDFS( $filePath, $contents );
    }

    /**
     * Renamed DFS file $oldPath to DFS file $newPath
     *
     * @param string $oldPath
     * @param string $newPath
     *
     * @return bool
     */
    public function renameOnDFS( $oldPath, $newPath )
    {
        $oldPathHandler = $this->getHandler( $oldPath );
        $newPathHandler = $this->getHandler( $newPath );

        // same handler, normal rename
        if ( $oldPathHandler === $newPathHandler )
        {
            return $oldPathHandler->renameOnDFS( $oldPath, $newPath );
        }
        // different handlers, create on new, delete on old
        else
        {
            // @todo some error handling wouldn't hurt
            if ( $newPathHandler->createFileOnDFS( $newPath, $oldPathHandler->getContents( $oldPath ) ) !== true )
                return false;

            return $oldPathHandler->delete( $oldPath );
        }
    }

    /**
     * Checks if a file exists on the DFS
     *
     * @param string $filePath
     *
     * @return bool
     */
    public function existsOnDFS( $filePath )
    {
        return $this->getHandler( $filePath )->existsOnDFS( $filePath );
    }

    /**
     * Returns the mount point
     *
     * @return string
     */
    public function getMountPoint()
    {
        // TODO: Implement getMountPoint() method.
    }

    /**
     * Returns size of a file in the DFS backend, from a relative path.
     *
     * @param string $filePath The relative file path we want to get size of
     *
     * @return int
     */
    public function getDfsFileSize( $filePath )
    {
        return $this->getHandler( $filePath )->getDfsFileSize( $filePath );
    }

    /**
     * @todo bad interfaces splitting, refactor with a DispatchableDFSHandler or something
     */
    public function supports( $path )
    {
        return false;
    }
}
