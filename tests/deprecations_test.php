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

/**
 * Loads every class of the component in a fresh process and fails on any
 * deprecation PHP raises while compiling and linking them (implicitly
 * nullable parameters, missing tentative return types and the like). Those
 * are raised once per process, when a class is first loaded, so the other
 * tests do not see them reliably.
 *
 * @package Archive
 * @version //autogen//
 * @subpackage Tests
 */
class ezcArchiveDeprecationsTest extends ezcTestCase
{
    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testLoadingEveryClassRaisesNoDeprecation()
    {
        $src = realpath( __DIR__ . '/../src' );
        $classMap = require __DIR__ . '/../vendor/composer/autoload_classmap.php';

        $deprecations = array();
        set_error_handler( function ( $errno, $errstr, $errfile, $errline ) use ( &$deprecations )
        {
            $deprecations[] = "$errstr in $errfile:$errline";
            return true;
        }, E_DEPRECATED | E_USER_DEPRECATED );

        $loaded = 0;
        try
        {
            foreach ( $classMap as $class => $file )
            {
                if ( strpos( realpath( $file ), $src . DIRECTORY_SEPARATOR ) !== 0 )
                {
                    continue;
                }
                class_exists( $class ) || interface_exists( $class );
                $loaded++;
            }
        }
        finally
        {
            restore_error_handler();
        }

        $this->assertGreaterThan( 0, $loaded );
        $this->assertSame( array(), $deprecations );
    }

    public static function suite()
    {
        return new \PHPUnit\Framework\TestSuite( __CLASS__ );
    }
}
?>
