<?php

namespace Depage\Fs\Tests;

class FsLocal
{
    // {{{ constructor
    public function __construct($testPath)
    {
        $this->path = $testPath . '/Test';
    }
    // }}}
    // {{{ setup
    public function setup()
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

    // {{{ createFile
    public function createFile($path = 'testFile', $contents = 'testString')
    {
        $testFile = fopen($path, 'w');
        fwrite($testFile, $contents);
        fclose($testFile);

        return $this->checkFile($path, $contents);
    }
    // }}}
    // {{{ checkFile
    public function checkFile($path = 'testFile', $contents = 'testString')
    {
        $file = file($path);

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
        } elseif (is_file($path)) {
            $result = $result && $this->unlink($path);
        }

        return $result;
    }
    // }}}
    // {{{ rmdir
    public function rmdir($path)
    {
        return \rmdir($path);
    }
    // }}}
    // {{{ mkdir
    public function mkdir($path)
    {
        return \mkdir($path);
    }
    // }}}
    // {{{ file_exists
    public function file_exists($path)
    {
        return \file_exists($path);
    }
    // }}}
    // {{{ unlink
    public function unlink($path)
    {
        return \unlink($path);
    }
    // }}}

    // {{{ is_dir
    public function is_dir($path)
    {
        return \is_dir($path);
    }
    // }}}
    // {{{ is_file
    public function is_file($path)
    {
        return \is_file($path);
    }
    // }}}

    // {{{ sha1_file
    public function sha1_file($path)
    {
        return \sha1_file($path);
    }
    // }}}
}
