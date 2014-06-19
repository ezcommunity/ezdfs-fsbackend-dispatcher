<?php
/**
 * This file is part of the eZ Publish Legacy package
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributd with this source code.
 * @version //autogentag//
 */

/**
 * @covers eZDFSFileHandlerDFSRegistry
 * @group eZDispatchableDFS
 */
class eZDFSFileHandlerDFSRegistryTest extends ezpTestCase
{
    /** @var eZDFSFileHandlerDFSRegistry */
    private $registry;

    /** @var  eZDFSFileHandlerDFSBackendInterface */
    private $defaultHandler;

    /** @var  eZDFSFileHandlerDFSBackendInterface */
    private $customHandler1;

    /** @var  eZDFSFileHandlerDFSBackendInterface */
    private $customHandler2;

    public function setUp()
    {
        $this->defaultHandler = $this->getMock( 'eZDFSFileHandlerDFSBackendInterface' );
        $this->customHandler1 = $this->getMock( 'eZDFSFileHandlerDFSBackendInterface' );
        $this->customHandler2 = $this->getMock( 'eZDFSFileHandlerDFSBackendInterface' );

        $this->registry = new eZDFSFileHandlerDFSRegistry(
            $this->defaultHandler,
            array(
                'path/to/' => $this->customHandler1,
                'otherpath/to/' => $this->customHandler2
            )
        );
    }

    public function testGetHandlerOne()
    {
        self::assertSame( $this->customHandler1, $this->registry->getHandler( 'path/to/file' ) );
    }

    public function testGetHandlerTwo()
    {
        self::assertSame( $this->customHandler1, $this->registry->getHandler( 'otherpath/to/file' ) );
    }

    public function testGetDefaultHandler()
    {
        self::assertSame( $this->customHandler1, $this->registry->getHandler( 'differentpath/to/file' ) );
    }
}
