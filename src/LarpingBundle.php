<?php

namespace LarpingBase\LarpingBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class LarpingBundle extends Bundle
{
    /**
     * Returns the path the bundle is in
     *
     * @return string
     */
    public function getPath(): string
    {
        return \dirname(__DIR__);

    }//end getPath()
}//end class
