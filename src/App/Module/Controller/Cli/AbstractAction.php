<?php

/*
 * Copyright © 2018 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Module\Controller\Cli;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @category CrazyCat
 * @package CrazyCat\Framework
 * @author Bruce Z <152416319@qq.com>
 * @link http://crazy-cat.co
 */
abstract class AbstractAction extends \CrazyCat\Framework\App\Module\Controller\AbstractAction {

    /**
     * @var \Symfony\Component\Console\Command\Command
     */
    protected $command;

    /**
     * @param \Symfony\Component\Console\Command\Command $command
     * @return $this
     */
    public function setCommand( Command $command )
    {
        $this->command = $command;

        return $this;
    }

    /**
     * @return $this
     */
    public function init()
    {
        $this->configure( $this->command );

        return $this;
    }

    /**
     * @return void
     */
    public function execute()
    {
        $this->eventManager->dispatch( 'controller_execute_before', [ 'action' => $this ] );

        list( $input, $output ) = func_get_args();

        $this->run( $input, $output );
    }

    /**
     * @param \Symfony\Component\Console\Command\Command $command
     */
    abstract protected function configure( Command $command );

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @see https://symfony.com/doc/3.4/console/style.html
     */
    abstract protected function run( InputInterface $input, OutputInterface $output );
}
