<?php

namespace Aizatto\Bundle\HermesBundle;

class AssetManager {

  private 
    $root_dir,
    $assets;

  public function __construct($root_dir, $assets) {
    $this->root_dir = $root_dir.'/../';
    $this->assets = $assets;
  }

  public function get($asset) {
    $asset = str_replace('-', '_', $asset);
    if (!isset($this->assets[$asset])) {
      return null;
    }

    return $this->root_dir.$this->assets[$asset]['path'];
  }

}
