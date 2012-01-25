<?php

namespace Aizatto\Bundle\HermesBundle\Command;

use Filesystem;
use FileFinder;
use PhutilDocblockParser;
use Aizatto\Bundle\HermesBundle\Graph\ResourceGraph;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml;

class DumpCommand extends ContainerAwareCommand
{
  protected function configure()
  {
    $this
      ->setName('hermes:dump')
      ->setDescription('Dumps all assets to the filesystem');
  }

  protected function initialize(InputInterface $input,
                                OutputInterface $output) {
    parent::initialize($input, $output);
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    phutil_require_module('phutil', 'filesystem');
    phutil_require_module('phutil', 'filesystem/filefinder');
    phutil_require_module('phutil', 'parser/docblock');

    $root = $this->getContainer()->get('kernel')->getRootDir();
    $root = Filesystem::resolvePath($root.'/../');

    $output->writeln("Finding static resources...");
    $scripts = $this->find($root, 'js', $output);
    $stylesheets = $this->find($root, 'css', $output);

    $dump = array(
      'aizatto_hermes' => array(
        'scripts' => $scripts,
        'stylesheets' => $stylesheets,
      ),
    );

    $dumper = new Yaml\Dumper();
    $yaml = $dumper->dump($dump, 6);
    $path = 'app/config/hermes.yml';
    file_put_contents($root.'/'.$path, $yaml);
    $output->writeln(sprintf('Updated config: <info>%s</info>', $path));
  }

  protected function find($root, $suffix, $output) {
    $finder = id(new FileFinder($root))
      ->withType('f')
      ->withSuffix($suffix)
      ->withFollowSymlinks(true)
      ->setGenerateChecksums(true);

    $paths = $this->getContainer()->getParameter('hermes.paths');
    foreach ($paths as $path) {
      $path = '*/'.$path.'*.'.$suffix;
      $finder->withPath($path);
    }

    $files = $finder->find();

    $output->writeln(sprintf(
      "Processing <info>%d</info> %s files",
      count($files),
      $suffix));

    $file_map = array();
    $length = strlen($root) + 1;
    foreach ($files as $path => $hash) {
      $path = substr($path, $length);
      $name = '/'.Filesystem::readablePath($path, $root);
      $file_map[$name] = $path;
    }

    $hash_map = array();
    $resource_graph = array();
    foreach ($file_map as $name => $path) {
      $value = $this->processPath($name, $path, $output);
      if (!$value) {
        continue;
      }

      $provides = $value['provides'];

      $hash_map[$provides] = $value;
      $resource_graph[$provides] = $value['requires'];
    }

    $output->writeln(sprintf(
      "Found <info>%d</info> %s files",
      count($hash_map),
      $suffix));

    $hermes_resource_graph = new ResourceGraph();
    $hermes_resource_graph->setResourceGraph($resource_graph);
    $hermes_resource_graph->addNodes($resource_graph);
    $hermes_resource_graph->loadGraph();
    foreach ($resource_graph as $provides => $requires) {
      $cycle = $hermes_resource_graph->detectCycles($provides);
      if ($cycle) {
        $output->writeln(sprintf(
          '<error>Cycle detected in resource graph: %s</error>',
          implode($cycle, ' => ')));
      }
    }

    return $hash_map;
  }

  private function processPath($name, $path, $output) {
    $data = Filesystem::readFile($path);
    $matches = array();
    $ok = preg_match('@/[*][*].*?[*]/@s', $data, $matches);
    if (!$ok) {
      return;
    }

    $parser = new PhutilDocblockParser();
    list($description, $metadata) = $parser->parse($matches[0]);
    $provides = idx($metadata, 'provides');
    if (!$provides) {
      return;
      $output->writeln(sprintf(
        'Provides is missing <error>%s</error>', $path));
      return;
    }

    $provides = preg_split('/\s+/', trim($provides));
    $requires = preg_split('/\s+/', trim(idx($metadata, 'requires')));
    $provides = array_filter($provides);
    $requires = array_filter($requires);

    if (count($provides) > 1) {
      // NOTE: Documentation-only JS is permitted to @provide no targets.
      $output->writeln(sprintf(
        '<error> File %s must provide only one @provide</error>', $path));
      return;
    }

    $provides = reset($provides);

    $type = 'js';
    if (preg_match('/\.css$/', $path)) {
      $type = 'css';
    }

    return array(
      'provides' => $provides,
      'requires' => $requires,
      'path' => $path,
    );
  }

}
