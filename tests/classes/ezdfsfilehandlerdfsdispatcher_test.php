<?php
/**
 * This file is part of the eZ Publish Legacy package
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributd with this source code.
 * @version //autogentag//
 */

/**
 * Tests the dispatcher using two handlers in the registry.
 *
 * The registry will dispatch based on the path prefixes: "one://" and "two://".
 * Handler mocks can be obtained via {@see customHandler} and {@see defaultHandler}
 *
 * @covers eZDFSFileHandlerDFSDispatcher
 * @group eZDispatchableDFS
 */
class eZDFSFileHandlerDFSDispatcherTest extends ezpTestCase
{
    /** @var eZDFSFileHandlerDFSDispatcher */
    private $dispatcher;

    /** @var eZDFSFileHandlerDFSBackendInterface[]|PHPUnit_Framework_MockObject_MockObject */
    private $customHandler;

    /** @var eZDFSFileHandlerDFSBackendInterface[]|PHPUnit_Framework_MockObject_MockObject */
    private $defaultHandler;

    /**
     * The test setup will use two handlers.
     *
     * The first one, $handlerOne, will match the path one://
     * The second one will match anything
     */
    public function setUp()
    {
        $this->customHandler = $this->getMock( 'eZDFSFileHandlerDFSBackendInterface' );
        $this->defaultHandler = $this->getMock( 'eZDFSFileHandlerDFSBackendInterface' );

        $this->dispatcher = new eZDFSFileHandlerDFSDispatcher(
            new eZDFSFileHandlerDFSRegistry(
                $this->defaultHandler,
                array( 'one://' => $this->customHandler )
            )
        );
    }

    public function testCopyFromDFSToDFSSameHandler()
    {
        $srcPath = 'one://source_file';
        $dstPath = 'one://dest_file';

        $this->customHandler
            ->expects( $this->once() )
            ->method( 'copyFromDFSToDFS' )
            ->with( $srcPath, $dstPath )
            ->will( $this->returnValue( true ) );

        self::assertTrue( $this->dispatcher->copyFromDFSToDFS( $srcPath, $dstPath ) );
    }

    public function testCopyFromDFSToDFSDifferentHandler()
    {
        $srcPath = "one://src_file";
        $dstPath = "two://dst_file";
        $contents = __FILE__;

        $this->customHandler
            ->expects( $this->once() )
            ->method( 'getContents' )
            ->with( $srcPath )
            ->will( $this->returnValue( $contents ) );

        $this->defaultHandler
            ->expects( $this->once() )
            ->method( 'createFileOnDFS' )
            ->with( $dstPath, $contents )
            ->will( $this->returnValue( true ) );

        self::assertTrue(
            $this->dispatcher->copyFromDFSToDFS( $srcPath, $dstPath )
        );
    }

    public function testCopyFromDFS()
    {
        $srcPath = 'one://source_file';

        $this->customHandler
            ->expects( $this->once() )
            ->method( 'copyFromDFS' )
            ->with( $srcPath )
            ->will( $this->returnValue( true ) );

        self::assertTrue(
            $this->dispatcher->copyFromDFS( $srcPath )
        );
    }

    public function testCopyToDFS()
    {
        $srcPath = '/tmp/source_file';
        $dstPath = 'one://source_file';

        $this->customHandler
            ->expects( $this->once() )
            ->method( 'copyToDFS' )
            ->with( $srcPath, $dstPath )
            ->will( $this->returnValue( true ) );

        self::assertTrue(
            $this->dispatcher->copyToDFS( $srcPath, $dstPath )
        );
    }

    public function testDelete()
    {
        $path = 'one://file';

        $this->customHandler
            ->expects( $this->once() )
            ->method( 'delete' )
            ->with( $path )
            ->will( $this->returnValue( true ) );

        self::assertTrue(
            $this->dispatcher->delete( $path )
        );
    }

    public function testArray()
    {
        $path = array( 'one://file1', 'one://file2', 'two://file3', 'two://file4', 'one://file5';

        $this->customHandler
            ->expects( $this->once() )
            ->method( 'delete' )
            ->with( array( 'one://file1', 'one://file2', 'one://file5' ) )
            ->will( $this->returnValue( true ) );

        $this->customHandler
            ->expects( $this->once() )
            ->method( 'delete' )
            ->with( array( 'two://file3', 'two://file4' ) )
            ->will( $this->returnValue( true ) );

        self::assertTrue(
            $this->dispatcher->delete( $path )
        );
    }

    public function testPassthrough()
    {
        $path = 'one://file';

        $this->customHandler
            ->expects( $this->once() )
            ->method( 'passthrough' )
            ->with( $path )
            ->will( $this->returnValue( true ) );

        self::assertTrue(
            $this->dispatcher->passthrough( $path )
        );
    }

    public function testGetContents()
    {
        $path = 'one://file';

        $this->customHandler
            ->expects( $this->once() )
            ->method( 'getContents' )
            ->with( $path )
            ->will( $this->returnValue( __FILE__ ) );

        self::assertEquals(
            __FILE__,
            $this->dispatcher->getContents( $path )
        );
    }

    public function testCreateFileOnDFS()
    {
        $path = 'one://file';
        $contents = __FILE__;

        $this->customHandler
            ->expects( $this->once() )
            ->method( 'createFileOnDFS' )
            ->with( $path, $contents )
            ->will( $this->returnValue( true ) );

        self::assertTrue(
            $this->dispatcher->createFileOnDFS( $path, $contents )
        );
    }

    public function testRenameOnDFSSameHandler()
    {
        $oldPath = "one://old_file";
        $newPath = "one://new_file";

        $this->customHandler
            ->expects( $this->once() )
            ->method( 'renameOnDFS' )
            ->with( $oldPath, $newPath )
            ->will( $this->returnValue( true ) );

        $this->dispatcher->renameOnDFS( $oldPath, $newPath );
    }

    public function testRenameOnDFSDifferentHandler()
    {
        $oldPath = "one://old_file";
        $newPath = "two://new_file";
        $contents = __FILE__;

        $this->customHandler
            ->expects( $this->once() )
            ->method( 'getContents' )
            ->with( $oldPath )
            ->will( $this->returnValue( $contents ) );

        $this->defaultHandler
            ->expects( $this->once() )
            ->method( 'createFileOnDFS' )
            ->with( $newPath, $contents )
            ->will( $this->returnValue( true ) );

        $this->customHandler
            ->expects( $this->once() )
            ->method( 'delete' )
            ->with( $oldPath )
            ->will( $this->returnValue( true ) );

        $this->dispatcher->renameOnDFS( $oldPath, $newPath );
    }

    public function testGetDfsFileSize()
    {
        $path = 'one://file';
        $size = 12345;

        $this->customHandler
            ->expects( $this->once() )
            ->method( 'getDfsFileSize' )
            ->with( $path )
            ->will( $this->returnValue( $size ) );

        self::assertEquals(
            $size,
            $this->dispatcher->getDfsFileSize( $path )
        );
    }

    public function testExistsOnDFS()
    {
        $path = 'one://file';

        $this->customHandler
            ->expects( $this->once() )
            ->method( 'existsOnDFS' )
            ->with( $path )
            ->will( $this->returnValue( true ) );

        self::assertTrue(
            $this->dispatcher->existsOnDFS( $path )
        );
    }

    /**
     * @return eZDFSFileHandlerDFSBackendInterface[]|PHPUnit_Framework_MockObject_MockObject
     */
    private function getHandlerMock( $index )
    {
        if ( isset( $this->fsHandlerMocks[$index] ) )
        {
            return $this->fsHandlerMocks[$index];
        }
        throw new OutOfBoundsException( "No handler at index #$index" );
    }
}
