<?php

namespace Depage\Fs\Tests;

abstract class RemoteOperationsTestCase extends LocalOperationsTestCase
{
    // {{{ createDst
    protected function createDst()
    {
        return new FsRemote('/Temp');
    }
    // }}}
}
