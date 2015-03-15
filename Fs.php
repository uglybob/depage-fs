<?php

namespace Depage\Fs;

class Fs
{
    // {{{ variables
    protected $base = null;
    protected $cwd = null;
    protected $url = null;
    protected $hidden = false;
    protected $streamContextOptions = array();
    protected $streamContext = null;
    // }}}
    // {{{ constructor
    public function __construct($params = array())
    {
        $this->url = new Url($params);
        $this->hidden = (isset($params['hidden'])) ? $params['hidden'] : false;

        $this->streamContext = stream_context_create($this->streamContextOptions);
    }
    // }}}
    // {{{ factory
    public static function factory($url, $params = array())
    {
        $parsed = Url::parse($url);
        if (is_array($parsed)) {
            $params = array_merge($parsed, $params);
        }
        $scheme = isset($params['scheme']) ? $params['scheme'] : null;
        $alias = self::schemeAlias($scheme);

        $schemeClass = '\Depage\Fs\Fs' . ucfirst($alias['class']);
        $params['scheme'] = $alias['scheme'];

        return new $schemeClass($params);
    }
    // }}}
    // {{{ schemeAlias
    protected static function schemeAlias($alias = '')
    {
        $aliases = array(
            ''          => array('class' => 'file', 'scheme' => 'file'),
            'file'      => array('class' => 'file', 'scheme' => 'file'),
            'ftp'       => array('class' => 'ftp',  'scheme' => 'ftp'),
            'ftps'      => array('class' => 'ftp',  'scheme' => 'ftps'),
            'ssh2.sftp' => array('class' => 'ssh',  'scheme' => 'ssh2.sftp'),
            'ssh'       => array('class' => 'ssh',  'scheme' => 'ssh2.sftp'),
            'sftp'      => array('class' => 'ssh',  'scheme' => 'ssh2.sftp'),
        );

        if (array_key_exists($alias, $aliases)) {
            $translation = $aliases[$alias];
        } else {
            $translation = array('class' => '', 'scheme' => $alias);
        }

        return $translation;
    }
    // }}}

    // {{{ pwd
    public function pwd()
    {
        $this->preCommandHook();
        // @todo "copy" constructor
        $pwd = new Url($this->url->__toString());
        $pwd->prefix($this->base . $this->cwd);

        $this->postCommandHook();
        return $pwd->__toString();
    }
    // }}}
    // {{{ ls
    public function ls($url)
    {
        $this->preCommandHook();

        $lsUrl = $this->absolute($url);
        $path = str_replace($this->pwd() . '/', '', $lsUrl);
        $ls = $this->lsRecursive($path, '');

        $this->postCommandHook();
        return $ls;
    }
    // }}}
    // {{{ lsDir
    public function lsDir($path = '')
    {
        $this->preCommandHook();

        $lsDir = $this->lsFilter($path, 'is_dir');

        $this->postCommandHook();
        return $lsDir;
    }
    // }}}
    // {{{ lsFiles
    public function lsFiles($path = '')
    {
        $this->preCommandHook();

        $lsFiles = $this->lsFilter($path, 'is_file');

        $this->postCommandHook();
        return $lsFiles;
    }
    // }}}
    // {{{ exists
    /**
     * Checks if file exists
     *
     * @public
     *
     * @param $path (string) path to file to check
     *
     * @return $exist (bool) true if file exists, false otherwise
     */
    public function exists($remotePath)
    {
        $this->preCommandHook();

        $remote = $this->absolute($remotePath);
        $exists = file_exists($remote);

        $this->postCommandHook();
        return $exists;
    }
    // }}}
    // {{{ fileInfo
    public function fileInfo($remotePath)
    {
        $this->preCommandHook();

        $remote = $this->absolute($remotePath);
        $fileInfo = new \SplFileInfo($remote);

        $this->postCommandHook();
        return $fileInfo;
    }
    // }}}

    // {{{ cd
    /**
     * Changes current directory
     *
     * @public
     *
     * @param $path (string) path of directory to change to
     */
    public function cd($url)
    {
        $this->preCommandHook();

        $absoluteUrl = $this->absolute($url);
        if (is_dir($absoluteUrl) && is_readable($absoluteUrl . '/.')) {
            $this->cwd = str_replace($this->pwd(), '', $absoluteUrl) . '/';
        } else {
            throw new Exceptions\FsException('Directory not accessible "' . $absoluteUrl->path . '".');
        }

        $this->postCommandHook();
    }
    // }}}
    // {{{ mkdir
    /**
     * Creates new directory recursive if it doesn't exist
     *
     * @public
     *
     * @param $path (string) path of new directory
     */
    public function mkdir($pathName, $mode = 0777, $recursive = true)
    {
        $this->preCommandHook();

        $absoluteUrl = $this->absolute($pathName);
        $success = mkdir($absoluteUrl, $mode, $recursive, $this->streamContext);

        if (!$success) {
            throw new Exceptions\FsException('Error while creating directory "' . $absoluteUrl->path . '".');
        }

        $this->postCommandHook();
    }
    // }}}
    // {{{ rm
    /**
     * Removes files and directories recursive
     *
     * @public
     *
     * @param $path (string) path to file or directory
     */
    public function rm($url)
    {
        $this->preCommandHook();

        $absoluteUrl = $this->absolute($url);
        if (preg_match('/^' . preg_quote($absoluteUrl, '/') . '\//', $this->pwd() . '/')) {
            // @todo error message
            throw new Exceptions\FsException('Cannot delete current or parent directory "' . $this->pwd() . '".');
        }
        $this->rmRecursive($url);

        $this->postCommandHook();
    }
    // }}}
    // {{{ mv
    /**
     * Renames or moves file or directory
     *
     * @public
     *
     * @param    $source (string) name of source file or directory
     * @param    $target (string) target
     */
    public function mv($sourcePath, $targetPath)
    {
        $this->preCommandHook();

        $source = $this->absolute($sourcePath);
        $target = $this->absolute($targetPath);

        if (file_exists($source)) {
            if(file_exists($target) && is_dir($target)) {
                $target .= '/' . $source->getFileName();
            }
            if (!$this->rename($source, $target)) {
                throw new Exceptions\FsException('Cannot move "' . $source->errorMessage() . '" to "' . $target->errorMessage()  . '".');
            }
        } else {
            throw new Exceptions\FsException('Cannot move "' . $source->errorMessage()  . '" to "' . $target->errorMessage() . '" - source doesn\'t exist.');
        }

        $this->postCommandHook();
    }
    // }}}

    // {{{ get
    /**
     * Writes content of a remote file to targetfile
     *
     * @public
     *
     * @param    $filepath (string) name of targetfile
     * @param    $sourcefile (string) path to sourcefile
     */
    public function get($remotePath, $local = null)
    {
        $this->preCommandHook();

        $remote = $this->absolute($remotePath);
        if ($local === null) {
            $local = $remote->getFileName();
        }
        if (!copy($remote, $local, $this->streamContext)) {
            throw new Exceptions\FsException('Cannot copy "' . $remote->errorMessage()  . '" to "' . $local . '".');
        }

        $this->postCommandHook();
    }
    // }}}
    // {{{ put
    /**
     * Writes content of a local file to targetfile
     *
     * @public
     *
     * @param    $filepath (string) name of targetfile
     * @param    $sourcefile (string) path to sourcefile
     */
    public function put($local, $remotePath)
    {
        $this->preCommandHook();

        $remote = $this->absolute($remotePath);
        if (!copy($local, $remote, $this->streamContext)) {
            throw new Exceptions\FsException('Cannot copy "' . $local . '" to "' . $remote->errorMessage() . '".');
        }

        $this->postCommandHook();
    }
    // }}}
    // {{{ getString
    public function getString($remotePath)
    {
        $this->preCommandHook();

        $remote = $this->absolute($remotePath);
        $string = file_get_contents($remote, false, $this->streamContext);
        if ($string === false) {
            throw new Exceptions\FsException('Cannot get contents of "' . $remote->errorMessage() . '".');
        }

        $this->postCommandHook();
        return $string;
    }
    // }}}
    // {{{ putString
    /**
     * Writes a String directly to a file
     *
     * @public
     *
     * @param    $filepath (string) name of targetfile
     * @param    $str (string) content to write to file
     */
    public function putString($remotePath, $string)
    {
        $this->preCommandHook();

        $remote = $this->absolute($remotePath);
        $bytes = file_put_contents($remote, $string, 0, $this->streamContext);
        if ($bytes === false) {
            throw new Exceptions\FsException('Cannot write string to "' . $remote->errorMessage() . '".');
        }

        $this->postCommandHook();
    }
    // }}}

    // {{{ test
    public function test(&$error = null)
    {
        $testFile = 'depage-fs-test-file.tmp';
        $testString = 'depage-fs-test-string';
        $success = false;

//        try {
            if (!$this->exists($testFile)) {
                $this->putString($testFile, $testString);
                if ($this->getString($testFile) === $testString) {
                    $this->rm($testFile);
                    $success = !$this->exists($testFile);
                }
            }
 /*       } catch (Exceptions\FsException $exception) {
            $error = $exception->getMessage();
            $success = false;
        }
        */

        return $success;
    }
    // }}}

    // {{{ preCommandHook
    protected function preCommandHook()
    {
        $this->lateConnect();
        $this->errorHandler(true);
    }
    // }}}
    // {{{ postCommandHook
    protected function postCommandHook()
    {
        $this->errorHandler(false);
    }
    // }}}
    // {{{ lateConnect
    protected function lateConnect()
    {
        if ($this->base === null) {
            $this->setBase();
        }
    }
    // }}}
    // {{{ errorHandler
    protected function errorHandler($start)
    {
        if ($start) {
            set_error_handler(
                function($errno, $errstr, $errfile, $errline, array $errcontext) {
                    restore_error_handler();
                    throw new Exceptions\FsException($errstr);
                }
            );
        } else {
            restore_error_handler();
        }
    }
    // }}}

    // {{{ setBase
    protected function setBase()
    {
        $trimmed = trim($this->url->path, '/');
        $this->base = ($trimmed) ? $trimmed . '/' : '/';
        $this->url->path = '';
    }
    // }}}
    // {{{ absolute
    protected function absolute($url)
    {
        $newUrl = new Url($url);

        if (!$newUrl->scheme) {
            $newUrl->scheme = $this->url->scheme;
            $newUrl->user = $this->url->user;
            $newUrl->pass = $this->url->pass;
            $newUrl->host = $this->url->host;
            $newUrl->port = $this->url->port;

            if (substr($newUrl->path, 0, 1) !== '/') {
                $newUrl->prefix($this->base . $this->cwd);
            }
        }
        if (!preg_match(';^' . preg_quote($this->base) . '(.*)$;', $newUrl->path)) {
            throw new Exceptions\FsException('Cannot leave base directory "' . $this->base . '".');
        }

        return $newUrl;
    }
    // }}}

    // {{{ lsFilter
    protected function lsFilter($path = '', $function)
    {
        // @todo slow
        $ls = $this->ls($path);
        $pwd = $this->pwd();
        $lsFiltered = array_filter(
            $ls,
            function ($element) use ($function, $pwd) {
                return $function($pwd . '/' . $element);
            }
        );
        natcasesort($lsFiltered);
        $sorted = array_values($lsFiltered);

        return $sorted;
    }
    // }}}
    // {{{ lsRecursive
    protected function lsRecursive($path, $current)
    {
        $result = array();
        $patterns = explode('/', $path);
        $count = count($patterns);

        if ($count) {
            $pattern = array_shift($patterns);
            if (preg_match('/[\*\?\[\]]/', $pattern)) {
                $matches = array_filter(
                    $this->scandir($this->pwd() . '/' . $current),
                    function ($node) use ($pattern) { return fnmatch($pattern, $node); }
                );
            } else {
                $matches = array($pattern);
            }

            foreach ($matches as $match) {
                $next = ($current) ? $current . '/' . $match : $match;

                if ($count == 1) {
                    $result[] = $next;
                    // @todo speed up (get rid of clean call)
                } elseif (is_dir($this->pwd() . '/' . $next)) {
                    $result = array_merge(
                        $result,
                        $this->lsRecursive(implode('/', $patterns), $next)
                    );
                }
            }
        }

        return $result;
    }
    // }}}
    // {{{ rmRecursive
    protected function rmRecursive($url)
    {
        // @todo rename cleanUrl, concate
        $cleanUrl = $this->absolute($url);
        $success = false;

        if (!file_exists($cleanUrl)) {
            throw new Exceptions\FsException('"' . $cleanUrl . '" doesn\'t exist.');
        } elseif (is_dir($cleanUrl)) {
            foreach ($this->scandir($cleanUrl, true) as $nested) {
                $this->rmRecursive($cleanUrl . '/' .  $nested);
            }
            $success = $this->rmdir($cleanUrl);
        } elseif (is_file($cleanUrl)) {
            $success = unlink($cleanUrl, $this->streamContext);
        }

        if ($success) {
            clearstatcache(true, $cleanUrl);
        } else {
            throw new Exceptions\FsException('Cannot delete "' . $cleanUrl . '".');
        }
    }
    // }}}

    // {{{ scandir
    protected function scandir($url = '', $hidden = null)
    {
        // @todo rename cleanUrl, concate
        if ($hidden === null) {
            $hidden = $this->hidden;
        }

        $scanDir = scandir($url, 0, $this->streamContext);
        $filtered = array_diff($scanDir, array('.', '..'));

        if (!$hidden) {
            $filtered = array_filter(
                $filtered,
                function ($node) { return ($node[0] != '.'); }
            );
        }

        natcasesort($filtered);
        $sorted = array_values($filtered);

        return $sorted;
    }
    // }}}
    // {{{ rmdir
    protected function rmdir($url)
    {
        return rmdir($url, $this->streamContext);
    }
    // }}}
    // {{{ rename
    protected function rename($source, $target)
    {
        return rename($source, $target, $this->streamContext);
    }
    // }}}
}

/* vim:set ft=php sw=4 sts=4 fdm=marker : */
