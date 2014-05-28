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
 * Handler mocks can be obtained via {@see getHandlerOne()} and {@see getHandlerTwo()}
 *
 * @covers eZDFSFileHandlerDFSDispatcher
 */
class eZDFSFileHandlerDFSDispatcherTest extends ezpTestCase
{
    /** @var eZDFSFileHandlerDFSDispatcher */
    private $dispatcher;

    /** @var eZDFSFileHandlerDFSBackendInterface[]|PHPUnit_Framework_MockObject_MockObject */
    private $handlerOne;

    /** @var eZDFSFileHandlerDFSBackendInterface[]|PHPUnit_Framework_MockObject_MockObject */
    private $handlerTwo;

    public function setUp()
    {
        $this->handlerOne = $this->getMock( 'eZDFSFileHandlerDFSBackendInterface' );
        $this->handlerOne
            ->expects( $this->any() )
            ->method( 'supports' )
            ->will( $this->returnCallback( function ( $path ) { return substr( $path, 0, 6 ) == 'one://'; } ) );

        $this->handlerTwo = $this->getMock( 'eZDFSFileHandlerDFSBackendInterface' );
        $this->handlerTwo
            ->expects( $this->any() )
            ->method( 'supports' )
            ->will( $this->returnCallback( function ( $path ) { return substr( $path, 0, 6 ) == 'two://'; } ) );

        $this->dispatcher = new eZDFSFileHandlerDFSDispatcher(
            new eZDFSFileHandlerDFSRegistry( array( $this->handlerOne, $this->handlerTwo ) )
        );
    }

    public function testCopyFromDFSToDFSSameHandler()
    {
        $srcPath = 'one://source_file';
        $dstPath = 'one://dest_file';

        $this->getHandlerOne()
            ->expects( $this->once() )
            ->method( 'copyFromDFSToDFS' )
            ->with( $srcPath, $dstPath );

        self::assertTrue( $this->dispatcher->copyFromDFSToDFS( $srcPath, $dstPath ) );
    }

    public function testCopyFromDFSToDFSDifferentHandler()
    {
        $srcPath = "one://src_file";
        $dstPath = "two://dst_file";
        $contents = __FILE__;

        $this->getHandlerOne()
            ->expects( $this->once() )
            ->method( 'getContents' )
            ->with( $srcPath )
            ->will( $this->returnValue( $contents ) );

        $this->getHandlerTwo()
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

        $this->getHandlerOne()
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

        $this->getHandlerOne()
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

        $this->getHandlerOne()
            ->expects( $this->once() )
            ->method( 'delete' )
            ->with( $path )
            ->will( $this->returnValue( true ) );

        self::assertTrue(
            $this->dispatcher->delete( $path )
        );
    }

    public function testPassthrough()
    {
        $path = 'one://file';

        $this->getHandlerOne()
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

        $this->getHandlerOne()
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

        $this->getHandlerOne()
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

        $this->getHandlerOne()
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

        $this->getHandlerOne()
            ->expects( $this->once() )
            ->method( 'getContents' )
            ->with( $oldPath )
            ->will( $this->returnValue( $contents ) );

        $this->getHandlerTwo()
            ->expects( $this->once() )
            ->method( 'createFileOnDFS' )
            ->with( $newPath, $contents )
            ->will( $this->returnValue( true ) );

        $this->getHandlerOne()
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

        $this->getHandlerOne()
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

        $this->getHandlerOne()
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
    private function getHandlerOne()
    {
        return $this->handlerOne;
    }

    /**
     * @return eZDFSFileHandlerDFSBackendInterface[]|PHPUnit_Framework_MockObject_MockObject
     */
    private function getHandlerTwo()
    {
        return $this->handlerTwo;
    }

    /**
     * @return eZDFSFileHandlerDFSBackendInterface[]|PHPUnit_Framework_MockObject_MockObject
     */
    private function getDefaultHandlerMock()
    {
        return $this->getHandlerMock( 0 );
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
