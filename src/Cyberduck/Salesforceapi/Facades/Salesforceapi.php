<?php namespace Cyberduck\Salesforceapi\Facades;
 
use Illuminate\Support\Facades\Facade;
 
class Salesforceapi extends Facade {
 
  /**
   * Get the registered name of the component.
   *
   * @return string
   */
  protected static function getFacadeAccessor() { return 'salesforceapi'; }
 
}