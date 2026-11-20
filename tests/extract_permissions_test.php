<?php
/**
 *
 * Licensed to the Apache Software Foundation (ASF) under one
 * or more contributor license agreements.  See the NOTICE file
 * distributed with this work for additional information
 * regarding copyright ownership.  The ASF licenses this file
 * to you under the Apache License, Version 2.0 (the
 * "License"); you may not use this file except in compliance
 * with the License.  You may obtain a copy of the License at
 * 
 *   http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing,
 * software distributed under the License is distributed on an
 * "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
 * KIND, either express or implied.  See the License for the
 * specific language governing permissions and limitations
 * under the License.
 *
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @filesource
 * @package Archive
 * @version //autogen//
 * @subpackage Tests
 */

require_once( dirname( __FILE__ ) . "/archive_test_case.php" );
require_once( dirname( __FILE__ ) . "/tar/tar_test_builder.php" );

/**
 * Tests that extracting does not apply setuid, setgid or sticky bits by
 * default.
 *
 * @package Archive
 * @version //autogen//
 * @subpackage Tests
 */
class ezcArchiveExtractPermissionsTest extends ezcArchiveTestCase
{
    protected $dir;
    protected $target;

    protected function setUp() : void
    {
        date_default_timezone_set( "UTC" );
        $this->createTempDir( "ezcArchive_" );
        $this->dir = realpath( $this->getTempDir() );
        $this->target = $this->dir . "/target";
        mkdir( $this->target );
    }

    protected function tearDown() : void
    {
        $this->removeTempDir();
    }

    /**
     * Extracts the tar archive made of $blocks into the target directory.
     */
    protected function extractTar( $blocks, ?ezcArchiveOptions $options = null )
    {
        $archive = ezcArchive::open( ezcArchiveTarTestBuilder::write( $this->dir . "/archive.tar", $blocks ), ezcArchive::TAR_USTAR, $options );
        $archive->extract( $this->target );
    }

    public function testSpecialPermissionBitsAreRemoved()
    {
        $blocks = ezcArchiveTarTestBuilder::header( "dir/", "5", 0, "", "3775", ezcArchiveTarTestBuilder::MAGIC_USTAR ) .
                  ezcArchiveTarTestBuilder::file( "setuid", "x", "4755" ) .
                  ezcArchiveTarTestBuilder::file( "setgid", "x", "2755" );

        $this->extractTar( $blocks );
        clearstatcache();
        self::assertSame( "755", sprintf( "%o", fileperms( $this->target . "/setuid" ) & 07777 ) );
        self::assertSame( "755", sprintf( "%o", fileperms( $this->target . "/setgid" ) & 07777 ) );
        self::assertSame( "775", sprintf( "%o", fileperms( $this->target . "/dir" ) & 07777 ) );
    }

    public function testSpecialPermissionBitsArePreservedOnRequest()
    {
        if ( substr( php_uname( 's' ), 0, 7 ) == 'Windows' )
        {
            self::markTestSkipped( "Windows has no setuid bit." );
        }

        $probe = $this->dir . "/probe";
        touch( $probe );
        @chmod( $probe, 04755 );
        clearstatcache();
        if ( ( fileperms( $probe ) & 07777 ) !== 04755 )
        {
            self::markTestSkipped( "The file system does not keep the setuid bit." );
        }

        $options = new ezcArchiveOptions( array( "preserveSpecialPermissions" => true ) );
        $this->extractTar( ezcArchiveTarTestBuilder::file( "setuid", "x", "4755" ), $options );
        clearstatcache();
        self::assertSame( "4755", sprintf( "%o", fileperms( $this->target . "/setuid" ) & 07777 ) );
    }

    public function testPermissionsWithOnlySpecialBitsAreApplied()
    {
        if ( substr( php_uname( 's' ), 0, 7 ) == 'Windows' )
        {
            self::markTestSkipped( "Windows has no Unix permissions." );
        }

        // Mode 4000 is 0 without the setuid bit. The file must not keep the
        // default permissions instead.
        $this->extractTar( ezcArchiveTarTestBuilder::file( "setuid-only", "x", "4000" ) );
        clearstatcache();
        self::assertSame( "0", sprintf( "%o", fileperms( $this->target . "/setuid-only" ) & 07777 ) );
    }

    public function testEntryWithoutPermissionsKeepsDefaultPermissions()
    {
        // ZIP archives made on Windows store no Unix permissions at all.
        $this->extractTar( ezcArchiveTarTestBuilder::file( "no-mode", "x", "0000" ) );
        clearstatcache();
        self::assertNotSame( 0, fileperms( $this->target . "/no-mode" ) & 07777 );
        self::assertSame( "x", file_get_contents( $this->target . "/no-mode" ) );
    }

    public static function suite()
    {
        return new \PHPUnit\Framework\TestSuite( __CLASS__ );
    }
}
?>
