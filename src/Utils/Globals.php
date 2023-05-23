<?php

namespace Tiknil\Skipper\Utils;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tiknil\Skipper\Config\Repository;

class Globals
{
    public static InputInterface $input;

    public static OutputInterface $output;

    public static TiknilStyle $io;

    public static Repository $configRepo;
}
