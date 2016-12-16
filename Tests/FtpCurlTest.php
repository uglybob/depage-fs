<?php

namespace Depage\Fs\Tests;

use Depage\Fs\Tests\TestClasses\FtpCurlTestClass;

class FtpCurlTest extends \PHPUnit_Framework_TestCase
{
    // {{{ setUp
    public function setUp()
    {
        FtpCurlTestClass::registerStream('ftp', ['caCert' => 'docker/ssl/ca.pem']);

        $this->url = 'ftp://' .
            $GLOBALS['REMOTE_USER'] . ':' .
            $GLOBALS['REMOTE_PASS'] . '@' .
            $GLOBALS['REMOTE_HOST'] . '/';
    }
    // }}}
    // {{{ tearDown
    public function tearDown()
    {
        stream_wrapper_restore('ftp');
    }
    // }}}

    // {{{ testScandir
    public function testScandir()
    {
        $this->assertSame([], scandir($this->url));
    }
    // }}}
}

/* vim:set ft=php sw=4 sts=4 fdm=marker et : */
