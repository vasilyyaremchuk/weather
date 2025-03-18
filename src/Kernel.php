<?php

/**
 * This file is part of the Weather Application.
 */

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

/**
 * Application kernel class.
 */
class Kernel extends BaseKernel
{
    use MicroKernelTrait;
}
