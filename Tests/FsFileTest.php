<?php

class FsFileTest extends TestBase
{
    // {{{ createTestObject
    public function createTestObject($override = array())
    {
        $params = array('scheme' => 'file');
        $newParams = array_merge($params, $override);

        return new Depage\Fs\FsFile($newParams);
    }
    // }}}

    // {{{ mkdirRemote
    protected function mkdirRemote($path, $mode = 0777, $recursive = true)
    {
        $remotePath = $this->remoteDir . '/' . $path;
        mkdir($remotePath, $mode, $recursive);
        chmod($remotePath, $mode);
    }
    // }}}
    // {{{ touchRemote
    protected function touchRemote($path, $mode = 0777)
    {
        $remotePath = $this->remoteDir . '/' . $path;
        touch($remotePath, $mode);
        chmod($remotePath, $mode);
    }
    // }}}

    // {{{ createRemoteTestDir
    public function createRemoteTestDir()
    {
        return $this->localDir;
    }
    // }}}
    // {{{ deleteRemoteTestDir
    public function deleteRemoteTestDir()
    {
        $this->rmr($this->localDir);
    }
    // }}}
    // {{{ createRemoteTestFile
    public function createRemoteTestFile($path, $content = null)
    {
        $this->createTestFile($path, $content);
    }
    // }}}

    // {{{ testGet
    public function testGet()
    {
        // file-scheme: create sub directory so we don't overwrite the 'local' file
        $this->mkdirRemote('testDir');
        $this->fs->cd('testDir');
        $this->createRemoteTestFile('testDir/testFile');

        $this->fs->get('testFile');
        $this->assertTrue($this->confirmTestFile('testFile'));
    }
    // }}}
    // {{{ testCdIntoWrapperUrl
    public function testCdIntoWrapperUrl()
    {
        $pwd = $this->fs->pwd();
        mkdir($this->remoteDir . '/testDir');
        $this->fs->cd('file://' . $this->remoteDir . '/testDir');
        $newPwd = $this->fs->pwd();

        $this->assertEquals($pwd . '/testDir', $newPwd);
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
}

/* vim:set ft=php sw=4 sts=4 fdm=marker et : */
