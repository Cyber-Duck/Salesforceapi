<?php namespace Cyberduck\Salesforceapi;

use Illuminate\Support\ServiceProvider;

class SalesforceapiServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->package('cyberduck/salesforceapi');
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{		

			$this->app['salesforceapi'] = $this->app->share(function($app) {
				return new Salesforceapi($app['config']);
			});

			$this->app->booting(function()
			{
			  	$loader = \Illuminate\Foundation\AliasLoader::getInstance();
			  	$loader->alias('Salesforceapi', 'Cyberduck\Salesforceapi\Facades\Salesforceapi');
			});
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array('salesforceapi');
	}

}
