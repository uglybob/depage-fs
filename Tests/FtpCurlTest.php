<?php

namespace Depage\Fs\Tests;

use Depage\Fs\Tests\TestClasses\FtpCurlTestClass;

class FtpCurlTest extends \PHPUnit_Framework_TestCase
{
    // {{{ constructor
    public function __construct()
    {
        $this->root = __DIR__;
        $this->cert = $this->root . '/' . $GLOBALS['CA_CERT'];
        $this->src = new HelperFsLocal($this->root . '/Temp');
        $this->dst = new HelperFsRemote('/Temp');

        $this->url = 'ftp://' .
            $GLOBALS['REMOTE_USER'] . ':' .
            $GLOBALS['REMOTE_PASS'] . '@' .
            $GLOBALS['REMOTE_HOST'] . '/Temp/';
    }
    // }}}

    // {{{ setUp
    public function setUp()
    {
        FtpCurlTestClass::registerStream('ftp', ['caCert' => $this->cert]);

        $this->assertTrue($this->src->setUp());
        $this->assertTrue($this->dst->setUp());

        $this->assertTrue(chdir($this->src->getRoot()));
    }
    // }}}
    // {{{ tearDown
    public function tearDown()
    {
        $this->assertTrue($this->src->tearDown());
        $this->assertTrue($this->dst->tearDown());

        $this->assertTrue(chdir($this->root));
        $this->assertTrue(stream_wrapper_restore('ftp'));
    }
    // }}}

    // {{{ testScandir
    public function testScandir()
    {
        $this->assertSame(['.', '..'], scandir($this->url));
    }
    // }}}
}

/* vim:set ft=php sw=4 sts=4 fdm=marker et : */
