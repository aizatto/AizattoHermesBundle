<?php

namespace Aizatto\Bundle\HermesBundle;

use Symfony\Component\Routing\Router;

class AssetManager {

  public
    $scripts,
    $stylesheets,
    $router,
    $assets_base_url;

  /**
   * TODO separate JS and CSS assets
   */
  public function __construct($root_dir,
                              $config,
                              Router $router,
                              $assets_base_url) {
    $root_dir = $root_dir.'/../';
    $this->scripts = $this->constructAssetManagerContainer($root_dir, $config, 'scripts');
    $this->stylesheets = $this->constructAssetManagerContainer($root_dir, $config, 'stylesheets');
    $this->router = $router;
    $this->assets_base_url = $assets_base_url;
  }

  private function constructAssetManagerContainer($root_dir, $config, $type) {
    $assets = idx($config, $type, array());
    $packages = idx(idx($config, 'packages', array()), $type, array());
    return new AssetManagerContainer($root_dir, $assets, $packages);
  }

  public function getURL($type, $asset) {
    switch ($type) {
      case 'js':
        $url = $this->router->generate('hermes_js', array('id' => $asset)).'.js';
        break;
      
      case 'css':
        $url = $this->router->generate('hermes_css', array('id' => $asset)).'.css';
        break;
    }
    return $this->assets_base_url.substr($url, 1);
  }

}
