<?php

namespace Depage\Fs;

class FsFile extends Fs
{
    // {{{ setBase
    protected function setBase()
    {
        $realPath = realpath($this->url->path);

        if ($realPath === false) {
            throw new Exceptions\FsException('Invalid path: "' . $this->url->path . '"');
        }

        return parent::setBase($realPath);
    }
    // }}}
    // {{{ rmdir
    protected function rmdir($url)
    {
        // workaround, rmdir does not support file stream wrappers <= PHP 5.6.2
        return parent::rmdir(preg_replace(';^file://;', '', $url));
    }
    // }}}
}

/* vim:set ft=php sw=4 sts=4 fdm=marker : */
