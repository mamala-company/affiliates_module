<?php namespace Affiliates;

use App;
use Event;
use BackendMenu;
use BackendAuth;
use Cms\Classes\ComponentManager;
use October\Rain\Support\ModuleServiceProvider;
use System\Classes\CombineAssets;

class ServiceProvider extends ModuleServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        parent::register('affiliates');

        $this->registerSingletons();
        $this->registerComponents();
        $this->registerMailer();
        $this->registerAssetBundles();
    }

    /**
     * Bootstrap the module events.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot('affiliates');

        $this->bootEvents();
    }

    protected function registerSingletons()
    {
        App::singleton('affiliates.auth', function() {
            return Classes\AuthManager::instance();
        });

        App::singleton('affiliates.helper', function () {
            return new Helpers\AffiliateHelper();
        });
    }

    /**
     * Register all components
     */
    protected function registerComponents()
    {

    }

    protected function registerModelExtensions()
    {

    }

    /**
     * Register mail templates
     */
    protected function registerMailer()
    {

    }

    /**
     * Register asset bundles
     */
    protected function registerAssetBundles()
    {
        CombineAssets::registerCallback(function ($combiner) {

        });
    }

    protected function bootEvents()
    {

    }

    protected function registerBackendNavigation()
    {

    }

    protected function registerBackendExtensions()
    {

    }

    protected function registerBackendEvents()
    {

    }
}
