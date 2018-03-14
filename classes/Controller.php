<?php namespace Affiliates\Classes;

use Str;
use App;
use File;
use View;
use Config;
use Response;
use Illuminate\Routing\Controller as ControllerBase;
use October\Rain\Router\Helper as RouterHelper;
use Closure;

/**
 * This is the master controller for all front-end pages.
 * All requests that are prefixed with the frontend URI pattern are sent here,
 * then the next URI segments are analysed and the request is routed to the
 * relevant back-end controller.
 *
 * For example, a request with the URL `/private/onetoone` will look
 * for the `OneToOne` controller
 *
 * @see Affiliates\Classes\ControllerBase class for back-end controllers
 * @package october\bbf
 * @author Manuel Ketterer
 */
class Controller extends ControllerBase
{
    use \October\Rain\Extension\ExtendableTrait;

    /**
     * @var array Behaviors implemented by this controller.
     */
    public $implement;

    /**
     * @var string Allows early access to page action.
     */
    public static $action;

    /**
     * @var array Allows early access to page parameters.
     */
    public static $params;

    /**
     * Instantiate a new FrontendController instance.
     */
    public function __construct()
    {
        $this->extendableConstruct();
    }

    /**
     * Extend this object properties upon construction.
     */
    public static function extend(Closure $callback)
    {
        self::extendableExtendCallback($callback);
    }

    /**
     * Finds and serves the requested backend controller.
     * If the controller cannot be found, returns the Cms page with the URL /404.
     * If the /404 page doesn't exist, returns the system 404 page.
     * @param string $url Specifies the requested page URL.
     * If the parameter is omitted, the current URL used.
     * @return string Returns the processed page content.
     */
    public function run($url = null)
    {
        $params = RouterHelper::segmentizeUrl($url);

        /*
         * Database check
         */
        if (!App::hasDatabase()) {
            return Config::get('app.debug', false)
                ? Response::make(View::make('backend::no_database'), 200)
                : App::make('Cms\Classes\Controller')->run($url);
        }

        /*
         * Look for a fitting controller
         */
        $controller = isset($params[0]) ? $params[0] : 'index';
        self::$action = $action = isset($params[1]) ? $this->parseAction($params[1]) : 'index';
        self::$params = $controllerParams = array_slice($params, 2);
        $controllerClass = '\\Affiliates\\Controllers\\'.$controller;
        if ($controllerObj = $this->findController(
            $controllerClass,
            $action,
            base_path().'/modules'
        )) {
            return $controllerObj->run($action, $controllerParams);
        }

        /*
         * Fall back on Cms controller
         */
        return App::make('Cms\Classes\Controller')->run($url);
    }

    /**
     * This method is used internally.
     * Finds a backend controller with a callable action method.
     * @param string $controller Specifies a method name to execute.
     * @param string $action Specifies a method name to execute.
     * @param string $inPath Base path for class file location.
     * @return ControllerBase Returns the backend controller object
     */
    protected function findController($controller, $action, $inPath)
    {
        /*
         * Workaround: Composer does not support case insensitivity.
         */
        if (!class_exists($controller)) {
            $controller = Str::normalizeClassName($controller);
            $controllerFile = $inPath.strtolower(str_replace('\\', '/', $controller)) . '.php';
            if ($controllerFile = File::existsInsensitive($controllerFile)) {
                include_once($controllerFile);
            }
        }

        if (!class_exists($controller)) {
            return false;
        }

        $controllerObj = App::make($controller);

        if ($controllerObj->actionExists($action)) {
            return $controllerObj;
        }

        return false;
    }

    /**
     * Process the action name, since dashes are not supported in PHP methods.
     * @param  string $actionName
     * @return string
     */
    protected function parseAction($actionName)
    {
        if (strpos($actionName, '-') !== false) {
            return camel_case($actionName);
        }

        return $actionName;
    }
}
