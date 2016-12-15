<?php

namespace Depage\Fs\Tests;

class FsFileTest extends OperationsTestCase
{
    // {{{ createSrc
    protected function createSrc()
    {
        return new FsLocal($this->root . '/Temp');
    }
    // }}}
    // {{{ createDst
    protected function createDst()
    {
        return new FsLocal($this->root . '/Temp2');
    }
    // }}}

    // {{{ testGet
    public function testGet()
    {
        // file-scheme: create subdirectory so we don't overwrite the 'local' file
        $this->mkdirRemote('testDir');
        $this->createRemoteTestFile('testDir/testFile');

        $this->fs->cd('testDir');
        $this->fs->get('testFile');
        $this->assertTrue($this->confirmLocalTestFile('testFile'));
    }
    // }}}
    // {{{ testCdIntoWrapperUrl
    public function testCdIntoWrapperUrl()
    {
        $pwd = $this->fs->pwd();
        mkdir($this->remoteDir . '/testDir');
        $this->fs->cd('file://' . $this->remoteDir . '/testDir');
        $newPwd = $this->fs->pwd();

        $this->assertEquals($pwd . 'testDir/', $newPwd);
    }
    // }}}
    // {{{ testMkdirFail
    /**
     * @expectedException           Depage\Fs\Exceptions\FsException
     * @expectedExceptionMessage    mkdir(): No such file or directory
     */
    public function testMkdirFail()
    {
        return parent::testMkdirFail();
    }
    // }}}

    // {{{ testCleanUrlFile
    public function testCleanUrlFile()
    {
        $fs = $this->createTestObject();
        $fs->lateConnect();

        $this->assertEquals('file://' . getcwd() . '/path/to/file', $fs->cleanUrl('file://' . getcwd() . '/path/to/file'));
        $this->assertEquals('file://' . getcwd() . '/path/to/file', $fs->cleanUrl('path/to/file'));
        $this->assertEquals('file://' . getcwd() . '/path/to/file', $fs->cleanUrl(getcwd() . '/path/to/file'));
    }
    // }}}
}

/* vim:set ft=php sw=4 sts=4 fdm=marker et : */
