<?php

namespace Depage\Fs\Tests;

abstract class RemoteOperationsTestCase extends \PHPUnit_Framework_TestCase
{
    // {{{ createDst
    protected function createDst()
    {
        return new FsRemote('/Temp');
    }
    // }}}
}
