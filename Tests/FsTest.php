<?php

use Depage\Fs\Fs;

class FsTest extends PHPUnit_Framework_TestCase
{
    // {{{ setUp
    public function setUp()
    {
        $params = array(
            'scheme' => 'file'
        );

        $this->fs = new Depage\Fs\FsFile($params);
    }
    // }}}
    // {{{ invokeCleanUrl
    public function invokeCleanUrl($url)
    {
        return invoke($this->fs, 'cleanUrl', array($url));
    }
    // }}}

    // {{{ testCleanUrl
    public function testCleanUrl()
    {
        $params = array(
            'scheme' => 'testScheme',
            'user' => 'testUser',
            'pass' => 'testPass',
            'host' => 'testHost',
            'port' => 42,
        );

        $fs = new Depage\Fs\Fs($params);
        invoke($fs, 'lateConnect');
        $this->assertEquals('testScheme://testUser:testPass@testHost:42/path/to/file', invoke($fs, 'cleanUrl', array('path/to/file')));
        $this->assertEquals('testScheme://testUser:testPass@testHost:42/path/to/file', invoke($fs, 'cleanUrl', array('/path/to/file')));

        $params['path'] = '/testSubDir';
        $fsSubDir = new Depage\Fs\Fs($params);
        invoke($fsSubDir, 'lateConnect');
        $this->assertEquals('testScheme://testUser:testPass@testHost:42/testSubDir/path/to/file', invoke($fsSubDir, 'cleanUrl', array('path/to/file')));
        $this->assertEquals('testScheme://testUser:testPass@testHost:42/testSubDir/path/to/file', invoke($fsSubDir, 'cleanUrl', array('/testSubDir/path/to/file')));

        $params['path'] = '/testSubDir/';
        $fsSubDir = new Depage\Fs\Fs($params);
        invoke($fsSubDir, 'lateConnect');
        $this->assertEquals('testScheme://testUser:testPass@testHost:42/testSubDir/path/to/file', invoke($fsSubDir, 'cleanUrl', array('path/to/file')));
        $this->assertEquals('testScheme://testUser:testPass@testHost:42/testSubDir/path/to/file', invoke($fsSubDir, 'cleanUrl', array('/testSubDir/path/to/file')));
    }
    // }}}
    // {{{ testCleanUrlSpecialCharacters
    public function testCleanUrlSpecialCharacters()
    {
        $params = array('scheme' => 'testScheme');

        $fs = new Depage\Fs\Fs($params);
        invoke($fs, 'lateConnect');
        $this->assertEquals('testScheme:///path',           invoke($fs, 'cleanUrl', array('path')));
        $this->assertEquals('testScheme:///path/to/file',   invoke($fs, 'cleanUrl', array('path/to/file')));
        $this->assertEquals('testScheme:///path/to/file',   invoke($fs, 'cleanUrl', array('/path/to/file')));
        $this->assertEquals('testScheme:/// ',              invoke($fs, 'cleanUrl', array(' ')));
        $this->assertEquals('testScheme:///pa h/to/fi e',   invoke($fs, 'cleanUrl', array('/pa h/to/fi e')));
        $this->assertEquals('testScheme:///?',              invoke($fs, 'cleanUrl', array('?')));
        $this->assertEquals('testScheme:///pa?h/to/fi?e',   invoke($fs, 'cleanUrl', array('/pa?h/to/fi?e')));
        $this->assertEquals('testScheme:///|',              invoke($fs, 'cleanUrl', array('|')));
        $this->assertEquals('testScheme:///pa|h/to/fi|e',   invoke($fs, 'cleanUrl', array('/pa|h/to/fi|e')));
        $this->assertEquals('testScheme:///<',              invoke($fs, 'cleanUrl', array('<')));
        $this->assertEquals('testScheme:///>',              invoke($fs, 'cleanUrl', array('>')));
        $this->assertEquals('testScheme:///pa<h/to/fi>e',   invoke($fs, 'cleanUrl', array('/pa<h/to/fi>e')));
        $this->assertEquals('testScheme:///(',              invoke($fs, 'cleanUrl', array('(')));
        $this->assertEquals('testScheme:///)',              invoke($fs, 'cleanUrl', array(')')));
        $this->assertEquals('testScheme:///pa(h/to/fi)e',   invoke($fs, 'cleanUrl', array('/pa(h/to/fi)e')));
        $this->assertEquals('testScheme:///[',              invoke($fs, 'cleanUrl', array('[')));
        $this->assertEquals('testScheme:///]',              invoke($fs, 'cleanUrl', array(']')));
        $this->assertEquals('testScheme:///pa[h/to/fi]e',   invoke($fs, 'cleanUrl', array('/pa[h/to/fi]e')));
        $this->assertEquals('testScheme:///"',              invoke($fs, 'cleanUrl', array('"')));
        $this->assertEquals('testScheme:///pa"h/to/fi"e',   invoke($fs, 'cleanUrl', array('/pa"h/to/fi"e')));
        $this->assertEquals('testScheme:///\'',             invoke($fs, 'cleanUrl', array('\'')));
        $this->assertEquals('testScheme:///pa\'h/to/fi\'e', invoke($fs, 'cleanUrl', array('/pa\'h/to/fi\'e')));
    }
    // }}}
    // {{{ testCleanUrlFile
    public function testCleanUrlFile()
    {
        invoke($this->fs, 'lateConnect');
        $this->assertEquals('file://' . getcwd() . '/path/to/file', $this->invokeCleanUrl('file://' . getcwd() . '/path/to/file'));
        $this->assertEquals('file://' . getcwd() . '/path/to/file', $this->invokeCleanUrl('path/to/file'));
        $this->assertEquals('file://' . getcwd() . '/path/to/file', $this->invokeCleanUrl(getcwd() . '/path/to/file'));
    }
    // }}}
}

/* vim:set ft=php sw=4 sts=4 fdm=marker et : */
