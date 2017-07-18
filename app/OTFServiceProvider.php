<?php

namespace Calhoun\OTF;

use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;

class OTFServiceProvider extends ServiceProvider
{
  public function boot()
  {
    Collection::macro('eloquent', function($callback, $name = null) {
        $name = is_null($name) ? 'oft' : $name;
        $results = eloquent($this->items, $callback, $name);
        if($results instanceof Collection) return new static($results->toArray());
        if(is_array($results)) return new static($results);
        return $results;
    });
  }
}

?>
