<?php

namespace Depage\Fs\Tests;

class FsLocal
{
    // {{{ constructor
    public function __construct($path)
    {
        $this->path = $path;
    }
    // }}}
    // {{{ setUp
    public function setUp()
    {
        $result = true;

        if ($this->is_dir($this->path)) {
            $result = $this->rm($this->path);
        }

        $result = $result && $this->mkdir($this->path, 0777);
        $result = $result && $this->is_dir($this->path);

        return $result;
    }
    // }}}
    // {{{ tearDown
    public function tearDown()
    {
        return $this->rm($this->path);
    }
    // }}}

    // {{{ translatePath
    protected function translatePath($path)
    {
        return $this->path . '/' . $path;
    }
    // }}}

    // {{{ createFile
    public function createFile($path = 'testFile', $contents = 'testString')
    {
        $newPath = $this->translatePath($path);

        $testFile = fopen($newPath, 'w');
        fwrite($testFile, $contents);
        fclose($testFile);

        return $this->checkFile($path, $contents);
    }
    // }}}
    // {{{ checkFile
    public function checkFile($path = 'testFile', $contents = 'testString')
    {
        $file = file($this->translatePath($path));

        return $file === [$contents];
    }
    // }}}

    // {{{ rm
    public function rm($path)
    {
        $result = true;

        if ($this->is_dir($path)) {
            $scanDir = array_diff($this->scandir($path), ['.', '..']);

            foreach ($scanDir as $nested) {
                $result = $result && $this->rm($path . '/' . $nested);
            }
            $result = $result && $this->rmdir($path);
        } elseif ($this->is_file($path)) {
            $result = $result && $this->unlink($path);
        }

        return $result;
    }
    // }}}
    // {{{ rmdir
    public function rmdir($path)
    {
        return \rmdir($this->translatePath($path));
    }
    // }}}
    // {{{ mkdir
    public function mkdir($path)
    {
        return \mkdir($this->translatePath($path));
    }
    // }}}
    // {{{ file_exists
    public function file_exists($path)
    {
        return \file_exists($this->translatePath($path));
    }
    // }}}
    // {{{ unlink
    public function unlink($path)
    {
        return \unlink($this->translatePath($path));
    }
    // }}}
    // {{{ touch
    public function touch($path, $mode = 0777)
    {
        $path = $this->translatePath($path);
        $result = \touch($path, $mode);

        return $result && chmod($path, $mode);
    }
    // }}}

    // {{{ is_dir
    public function is_dir($path)
    {
        return \is_dir($this->translatePath($path));
    }
    // }}}
    // {{{ is_file
    public function is_file($path)
    {
        return \is_file($this->translatePath($path));
    }
    // }}}

    // {{{ sha1_file
    public function sha1_file($path)
    {
        return \sha1_file($this->translatePath($path));
    }
    // }}}
}
