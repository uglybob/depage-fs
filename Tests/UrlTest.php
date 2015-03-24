<?php

use Depage\Fs\Url;

class UrlTest extends PHPUnit_Framework_TestCase
{
    protected function cleanUrl($url, $path)
    {
        $url->path = $path;
        $url->clean();
        return $url->__toString();
    }

    // {{{ testParse
    public function testParse()
    {
        $expected = array(
            'path'=>'/path/to/file',
            'scheme'=>'file',
        );
        $this->assertEquals($expected, Url::parse('file:///path/to/file'));

        $this->assertEquals(array('path'=>'/path/to/file'), Url::parse('/path/to/file'));

        $expected = array(
            'path'      => '/path/to/file',
            'scheme'    => 'testScheme',
            'user'      => 'testUser',
            'pass'      => 'testPass',
            'host'      => 'testHost',
            'port'      => '42',
        );
        $this->assertEquals($expected, Url::parse('testScheme://testUser:testPass@testHost:42/path/to/file'));
    }
    // }}}
    // {{{ testParseUrlPath
    public function testParseUrlPath()
    {
        $this->assertEquals(array('path'=>''),          Url::parse(''));
        $this->assertEquals(array('path'=>'abc'),       Url::parse('abc'));
        $this->assertEquals(array('path'=>'a[bd]c'),    Url::parse('a[bd]c'));
        $this->assertEquals(array('path'=>'abc*'),      Url::parse('abc*'));
        $this->assertEquals(array('path'=>'*abc'),      Url::parse('*abc'));
        $this->assertEquals(array('path'=>'*abc*'),     Url::parse('*abc*'));
        $this->assertEquals(array('path'=>'*'),         Url::parse('*'));
        $this->assertEquals(array('path'=>'**'),        Url::parse('**'));
        $this->assertEquals(array('path'=>'abc?'),      Url::parse('abc?'));
        $this->assertEquals(array('path'=>'ab?c'),      Url::parse('ab?c'));
        $this->assertEquals(array('path'=>'?abc'),      Url::parse('?abc'));
        $this->assertEquals(array('path'=>'?abc?'),     Url::parse('?abc?'));
        $this->assertEquals(array('path'=>'?'),         Url::parse('?'));
        $this->assertEquals(array('path'=>'??'),        Url::parse('??'));
        $this->assertEquals(array('path'=>'a&b'),       Url::parse('a&b'));
        $this->assertEquals(array('path'=>'&'),         Url::parse('&'));
        $this->assertEquals(array('path'=>'&&'),        Url::parse('&&'));
    }
    // }}}
    // {{{ testExtractFileName
    public function testExtractFileName()
    {
        // @todo clean up
        $this->assertEquals('filename.extension', (new Url('scheme://path/to/filename.extension'))->getFileName());
        $this->assertEquals('filename.extension', (new Url('path/to/filename.extension'))->getFileName());
        $this->assertEquals('filename.extension', (new Url('/filename.extension'))->getFileName());
        $this->assertEquals('filename.extension', (new Url('filename.extension'))->getFileName());

        $this->assertEquals('filename', (new Url('scheme://path/to/filename'))->getFileName());
        $this->assertEquals('filename', (new Url('path/to/filename'))->getFileName());
        $this->assertEquals('filename', (new Url('/filename'))->getFileName());
        $this->assertEquals('filename', (new Url('filename'))->getFileName());

        $this->assertEquals('filename.stuff.extension', (new Url('scheme://path/to/filename.stuff.extension'))->getFileName());
        $this->assertEquals('filename.stuff.extension', (new Url('path/to/filename.stuff.extension'))->getFileName());
        $this->assertEquals('filename.stuff.extension', (new Url('/filename.stuff.extension'))->getFileName());
        $this->assertEquals('filename.stuff.extension', (new Url('filename.stuff.extension'))->getFileName());

        $this->assertEquals('filename.', (new Url('scheme://path/to/filename.'))->getFileName());
        $this->assertEquals('filename.', (new Url('path/to/filename.'))->getFileName());
        $this->assertEquals('filename.', (new Url('/filename.'))->getFileName());
        $this->assertEquals('filename.', (new Url('filename.'))->getFileName());

        $this->assertEquals('.extension', (new Url('scheme://path/to/.extension'))->getFileName());
        $this->assertEquals('.extension', (new Url('path/to/.extension'))->getFileName());
        $this->assertEquals('.extension', (new Url('/.extension'))->getFileName());
        $this->assertEquals('.extension', (new Url('.extension'))->getFileName());
    }
    // }}}
    // {{{ testCleanPath
    public function testCleanPath()
    {
        $this->assertEquals('',                 Url::cleanPath(''));
        $this->assertEquals('/',                 Url::cleanPath('/'));
        $this->assertEquals('path',             Url::cleanPath('path'));
        $this->assertEquals('path',             Url::cleanPath('path/'));
        $this->assertEquals('/path',             Url::cleanPath('/path'));
        $this->assertEquals('/path',             Url::cleanPath('/path/'));
        $this->assertEquals('path/to/file',     Url::cleanPath('path/to/file'));
        $this->assertEquals('/path/to/file',     Url::cleanPath('/path/to/file'));
        $this->assertEquals('/path/to/file',     Url::cleanPath('/path/to/file/'));
        $this->assertEquals('/path/file',        Url::cleanPath('/path/to/../file'));
        $this->assertEquals('/path/to/file',     Url::cleanPath('/path/to/./file'));
        $this->assertEquals('/file',             Url::cleanPath('/path/../../file'));
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

        $url = new Url($params);
        $this->assertEquals('testScheme://testUser:testPass@testHost:42/path/to/file', $this->cleanUrl($url, 'path/to/file'));
        $this->assertEquals('testScheme://testUser:testPass@testHost:42/path/to/file', $this->cleanUrl($url, '/path/to/file'));
        $this->assertEquals('testScheme://testUser:testPass@testHost:42/path/to/directory', $this->cleanUrl($url, '/path/to/directory/'));
    }
    // }}}
    // {{{ testCleanUrlSpecialCharacters
    public function testCleanUrlSpecialCharacters()
    {
        $params = array('scheme' => 'testScheme');
        $url = new Url($params);

        $this->assertEquals('testScheme:///path',           $this->cleanUrl($url, 'path'));
        $this->assertEquals('testScheme:///path/to/file',   $this->cleanUrl($url, 'path/to/file'));
        $this->assertEquals('testScheme:///path/to/file',   $this->cleanUrl($url, '/path/to/file'));
        $this->assertEquals('testScheme:/// ',              $this->cleanUrl($url, ' '));
        $this->assertEquals('testScheme:///pa h/to/fi e',   $this->cleanUrl($url, '/pa h/to/fi e'));
        $this->assertEquals('testScheme:///?',              $this->cleanUrl($url, '?'));
        $this->assertEquals('testScheme:///pa?h/to/fi?e',   $this->cleanUrl($url, '/pa?h/to/fi?e'));
        $this->assertEquals('testScheme:///|',              $this->cleanUrl($url, '|'));
        $this->assertEquals('testScheme:///pa|h/to/fi|e',   $this->cleanUrl($url, '/pa|h/to/fi|e'));
        $this->assertEquals('testScheme:///<',              $this->cleanUrl($url, '<'));
        $this->assertEquals('testScheme:///>',              $this->cleanUrl($url, '>'));
        $this->assertEquals('testScheme:///pa<h/to/fi>e',   $this->cleanUrl($url, '/pa<h/to/fi>e'));
        $this->assertEquals('testScheme:///(',              $this->cleanUrl($url, '('));
        $this->assertEquals('testScheme:///)',              $this->cleanUrl($url, ')'));
        $this->assertEquals('testScheme:///pa(h/to/fi)e',   $this->cleanUrl($url, '/pa(h/to/fi)e'));
        $this->assertEquals('testScheme:///[',              $this->cleanUrl($url, '['));
        $this->assertEquals('testScheme:///]',              $this->cleanUrl($url, ']'));
        $this->assertEquals('testScheme:///pa[h/to/fi]e',   $this->cleanUrl($url, '/pa[h/to/fi]e'));
        $this->assertEquals('testScheme:///"',              $this->cleanUrl($url, '"'));
        $this->assertEquals('testScheme:///pa"h/to/fi"e',   $this->cleanUrl($url, '/pa"h/to/fi"e'));
        $this->assertEquals('testScheme:///\'',             $this->cleanUrl($url, '\''));
        $this->assertEquals('testScheme:///pa\'h/to/fi\'e', $this->cleanUrl($url, '/pa\'h/to/fi\'e'));
    }
    // }}}
}

/* vim:set ft=php sw=4 sts=4 fdm=marker et : */
