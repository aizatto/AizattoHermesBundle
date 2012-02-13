<?php

namespace Aizatto\Bundle\HermesBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Assetic\Util\ProcessBuilder;

class PackagesCommand extends ContainerAwareCommand
{

  protected function configure()
  {
    $this
      ->setName('hermes:packages')
      ->setDescription('Write packages to directory');
  }

  protected function initialize(InputInterface $input,
                                OutputInterface $output) {
    parent::initialize($input, $output);
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $config = $this->getContainer()->getParameter('hermes');
    $packages = $config['packages'];

    $hermes = $this->getContainer()->get('hermes');
    $this->package('css', idx($packages, 'stylesheets', array()), $hermes->stylesheets, $output);
    $this->package('js', idx($packages, 'scripts', array()), $hermes->scripts, $output);
  }

  protected function package($type, $packages, $hermes, $output) {
    $root = 'web/_/'.$type;
    $filesystem = $this->getContainer()->get('filesystem');

    foreach ($packages as $package) {
      $provides = $package['provides'];
      $target = $root.'/'.$provides.'.'.$type;

      $resources = $hermes->resolveResources($package['requires']);
      $output->writeln(sprintf(
        '%s %s => %s',
        $provides, implode(', ', $package['requires']), $target));
      $resources = ipull($resources, 'path');

      $command = "cat ".implode(' ', $resources).' > '.$target;
      $builder = new Process($command);
      $builder->run();
    }

  }

}

