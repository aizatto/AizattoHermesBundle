<?php

namespace Aizatto\Bundle\HermesBundle;

class AssetManager {

  public
    $scripts,
    $stylesheets;

  /**
   * TODO separate JS and CSS assets
   */
  public function __construct($root_dir, $assets) {
    $root_dir = $root_dir.'/../';
    $this->scripts = new AssetManagerContainer($root_dir, $assets['scripts']);
    $this->stylesheets = new AssetManagerContainer($root_dir, $assets['stylesheets']);
  }

}
