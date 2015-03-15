<?php

namespace Depage\Fs;

class Url
{
    // {{{ variables
    public $scheme;
    public $user;
    public $pass;
    public $port;
    public $path;
    // }}}
    // {{{ constructor
    public function __construct($url = '')
    {
        if (is_string($url)) {
            $parsed = $this->parse($url);
        } elseif (is_array($url)) {
            $parsed = $url;
        }

        $this->scheme   = (isset($parsed['scheme']))    ? $parsed['scheme']                 : null;
        $this->user     = (isset($parsed['user']))      ? $parsed['user']                   : null;
        $this->pass     = (isset($parsed['pass']))      ? $parsed['pass']                   : null;
        $this->host     = (isset($parsed['host']))      ? $parsed['host']                   : null;
        $this->port     = (isset($parsed['port']))      ? $parsed['port']                   : null;
        $this->path     = (isset($parsed['path']))      ? $parsed['path']                   : null;
    }
    // }}}

    // {{{ cleanPath
    public static function cleanPath($path)
    {
        // @todo handle backslashes
        $dirs = explode('/', $path);
        $newDirs = array();

        foreach ($dirs as $dir) {
            if ($dir == '..') {
                array_pop($newDirs);
            } elseif ($dir != '.' && $dir != '') {
                $newDirs[] = $dir;
            }
        }

        $newPath = implode('/', $newDirs);

        return $newPath;
    }
    // }}}
    // {{{ clean
    public function clean()
    {
        $this->path = $this->cleanPath($this->path);
    }
    // }}}
    // {{{ parse
    public static function parse($url)
    {
        $parsed = parse_url($url);

        // hack, parse_url matches anything after the first question mark as "query"
        $path = (isset($parsed['path'])) ? $parsed['path'] : null;
        $query = (isset($parsed['query'])) ? $parsed['query'] : null;
        if ($query !== null || preg_match('/\?$/', $url)) {
            $parsed['path'] = $path . '?' . $query;
            unset($parsed['query']);
        }

        return $parsed;
    }
    // }}}
    // {{{ prefix
    public function prefix($prefix)
    {
        $this->path = $this->cleanPath($prefix . '/' . $this->path);
    }
    // }}}

    // {{{ getFileName
    public function getFileName()
    {
        $pathInfo = pathinfo($this->path);
        $fileName = $pathInfo['filename'];

        if (isset($pathInfo['extension'])) {
            $fileName .= '.' . $pathInfo['extension'];
        }

        return $fileName;
    }
    // }}}

    // {{{ __toString
    public function __toString()
    {
        $path = $this->scheme . '://';
        $path .= $this->user;
        $path .= ($this->pass) ? ':' . $this->pass  : '';
        $path .= ($this->user) ? '@'                : '';
        $path .= $this->host;
        $path .= ($this->port) ? ':' . $this->port  : '';
        $path .= ($this->path) ? '/' . $this->path  : '/';

        return $path;
    }
    // }}}
    // {{{ errorMessage
    public function errorMessage()
    {
        // @todo boilerplate
        $path = $this->scheme . '://';
        $path .= $this->user;
        $path .= ($this->pass) ? ':...'             : '';
        $path .= ($this->user) ? '@'                : '';
        $path .= $this->host;
        $path .= ($this->port) ? ':' . $this->port  : '';
        $path .= ($this->path) ? '/' . $this->path  : '/';

        return $path;
    }
    // }}}
}

/* vim:set ft=php sw=4 sts=4 fdm=marker : */
