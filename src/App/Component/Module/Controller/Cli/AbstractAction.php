<?php

/*
 * Copyright © 2020 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Component\Module\Controller\Cli;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @category CrazyCat
 * @package  CrazyCat\Framework
 * @author   Liwei Zeng <zengliwei@163.com>
 * @link     https://crazy-cat.cn
 */
abstract class AbstractAction extends \CrazyCat\Framework\App\Component\Module\Controller\AbstractAction
{

    /**
     * @var \Symfony\Component\Console\Command\Command
     */
    protected $command;

    /**
     * @param \Symfony\Component\Console\Command\Command $command
     * @return $this
     */
    public function setCommand(Command $command)
    {
        $this->command = $command;

        return $this;
    }

    /**
     * @return $this
     */
    public function init()
    {
        $this->configure($this->command);

        return $this;
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function run()
    {
        $this->beforeRun();
        list($input, $output) = func_get_args();
        $this->execute($input, $output);
        $this->afterRun();
    }

    /**
     * @param \Symfony\Component\Console\Command\Command $command
     */
    abstract protected function configure(Command $command);

    /**
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @see https://symfony.com/doc/3.4/console/style.html
     */
    abstract protected function execute(InputInterface $input, OutputInterface $output);
}
