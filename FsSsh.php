<?php

namespace Depage\Fs;

class FsSsh extends Fs
{
    // {{{ variables
    protected $session = null;
    protected $connection = null;
    protected $privateKeyFile = null;
    protected $publicKeyFile = null;
    protected $privateKey = null;
    protected $publicKey = null;
    protected $fingerprint = null;
    protected $tmp = null;
    // }}}
    // {{{ constructor
    public function __construct($params = array())
    {
        parent::__construct($params);
        $this->privateKeyFile = (isset($params['privateKeyFile'])) ? $params['privateKeyFile'] : false;
        $this->publicKeyFile = (isset($params['publicKeyFile'])) ? $params['publicKeyFile'] : false;
        $this->privateKey = (isset($params['privateKey'])) ? $params['privateKey'] : false;
        $this->publicKey = (isset($params['publicKey'])) ? $params['publicKey'] : false;
        $this->tmp = (isset($params['tmp'])) ? $params['tmp'] : false;
        $this->fingerprint = (isset($params['fingerprint'])) ? $params['fingerprint'] : false;
    }
    // }}}
    // {{{ destructor
    public function __destruct()
    {
        $this->disconnect();
    }
    // }}}

    // {{{ lateConnect
    protected function lateConnect()
    {
        parent::lateConnect();
        $this->getSession();
    }
    // }}}
    // {{{ getFingerprint
    public function getFingerprint()
    {
        $this->getConnection($fingerprint);
        return $fingerprint;
    }
    // }}}
    // {{{ getConnection
    protected function getConnection(&$fingerprint = null)
    {
        if (!$this->connection) {
            if ($this->url->port === null) {
                $this->connection = ssh2_connect($this->url->host);
            } else {
                $this->connection = ssh2_connect($this->url->host, $this->url->port);
            }
        }
        $fingerprint = ssh2_fingerprint($this->connection);

        return $this->connection;
    }
    // }}}
    // {{{ getSession
    protected function getSession()
    {
        if (!$this->url->session) {
            $connection = $this->getConnection($fingerprint);

            if (strcasecmp($this->fingerprint, $fingerprint) !== 0) {
                throw new Exceptions\FsException('SSH RSA Fingerprints don\'t match.');
            }

            if (
                $this->privateKeyFile
                || $this->publicKeyFile
                || $this->privateKey
                || $this->publicKey
                || $this->tmp
            ) {
                $authenticated = $this->authenticateByKey($connection);
            } else {
                $authenticated = $this->authenticateByPassword($connection);
            }

            if ($authenticated) {
                $this->url->session = ssh2_sftp($connection);
            } else {
                throw new Exceptions\FsException('Could not authenticate session.');
            }
        }

        return $this->url->session;
    }
    // }}}
    // {{{ authenticateByPassword
    protected function authenticateByPassword($connection)
    {
        return ssh2_auth_password(
            $connection,
            $this->url->user,
            $this->url->pass
        );
    }
    // }}}
    // {{{ authenticateByKey
    protected function authenticateByKey($connection)
    {
        if (!$this->isValidKeyCombination()) {
            throw new Exceptions\FsException('Invalid SSH key combination.');
        }

        if ($this->privateKeyFile) {
            $private = new PrivateSshKey($this->privateKeyFile);
        } elseif ($this->privateKey) {
            $private = new PrivateSshKey($this->privateKey, $this->tmp);
        }

        if ($this->publicKeyFile) {
            $public = new PublicSshKey($this->publicKeyFile);
        } elseif ($this->publicKey) {
            $public = new PublicSshKey($this->publicKey, $this->tmp);
        } else {
            $public = $private->extractPublicKey($this->tmp);
        }

        $authenticated = ssh2_auth_pubkey_file(
            $connection,
            $this->url->user,
            $public,
            $private,
            $this->url->pass
        );

        $private->clean();
        $public->clean();

        return $authenticated;
    }
    // }}}
    // {{{ isValidKeyCombination
    protected function isValidKeyCombination()
    {
        return ($this->privateKeyFile && $this->publicKeyFile)
            || ($this->tmp && ($this->privateKeyFile || $this->privateKey));
    }
    // }}}
    // {{{ disconnect
    protected function disconnect()
    {
        $this->connection = null;
        $this->url->session = null;
    }
    // }}}

    // {{{ rename
    protected function rename($source, $target)
    {
        // workaround, rename doesn't overwrite files via ssh
        if (file_exists($target) && is_file($target)) {
            $this->rm($target);
        }

        return parent::rename($source, $target);
    }
    // }}}
}

/* vim:set ft=php sw=4 sts=4 fdm=marker : */
