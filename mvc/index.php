<?php

/**
 * Class Utils
 *
 * Many utils functions
 */
class Utils
{
    /**
     * Print error message
     *
     * @param string $str
     * @param bool $die
     */
    static function printError(string $str, bool $die = true): void
    {
        if ($die) {
            die($str);
        }
        echo $str;
    }

    /**
     * Parse date to FR date
     *
     * @param string $date
     * @return string
     */
    static function parseDate(string $date): string
    {
        setlocale (LC_TIME, 'fr_FR.utf8','fra');

        $strDate = strtotime($date);
        return strftime("%d %B %Y", $strDate);
    }

    /**
     * Parse string and secure it
     *
     * @param string $str
     * @param bool $replace
     * @return string
     */
    static function secureString($str, $replace = true): string
    {
        if ($replace) {
            $str = preg_replace("/>.*?</s", "><", $str);
        }
        return htmlspecialchars(strip_tags($str), ENT_QUOTES, 'UTF-8');
    }
}


/**
 * Class Model
 */
class Model {};

/**
 * Class ModelPost
 */
class ModelPost extends Model
{
    public function getValue(): array
    {
        return [0 => "ok"];
    }
}

/**
 * Class Controller
 */
class Controller
{

    /**
     * Get the model instance
     *
     * @param string $modelName
     * @return bool|Model
     */
    public function getModel($modelName)
    {
        if (!class_exists($modelName)) return false;

        $model = new $modelName;
        if (isset($model)) {
            return $model;
        }

        return false;
    }
}

/**
 * Class Router
 */
class Router
{
    private array $routes = [];
    private array $routesAvailableMethods = ["GET", "POST"];

    /**
     * Print page depending URL path
     *
     * @param string $_URL
     */
    public function exec($_URL): void
    {
        if ($_URL && array_key_exists($_URL, $this->routes)) {
            $this->routes[$_URL]['fn']($this->routes[$_URL]['args']);
        } else {
            if (!empty($this->routes) && isset($this->routes[''])) {
                $this->routes['']['fn']($this->routes['']['args']);
            } else {
                Utils::printError("Impossible d'afficher la page.", true);
            }
        }
    }

    /**
     * Add route in $routes array
     *
     * @param string $routeName
     * @param callable $functionName
     *
     * @return bool
     */
    public function add_route(string $routeName, callable $functionName, $args = []): bool
    {
        if (is_callable($functionName)) {
            $this->routes[$routeName] = array(
                "fn" => $functionName,
                "args" => $args
            );
            return true;
        }
        return false;
    }

    /**
     * Check if method is valid
     *
     * @param array $args
     * @return bool
     */
    private function checkMethodValidity(array $args): bool
    {
        if (!isset($args['method'])) return false;

        $method = Utils::secureString($args['method']);

        $methodValidity = array_filter($this->routesAvailableMethods, function($value) use ($method) {
            if ($method === $value) return true;
        });
        if (empty($methodValidity)) return false;

        if ($method === 'POST' && empty($_POST)) return false;
        if ($method === 'GET' && empty($_GET)) return false;

        return true;
    }

    /**
     * Get the model
     *
     * @param array $args
     * @return bool|Model
     */
    private function getModel($args)
    {
        if (!isset($args['model'])) return false;
        $modelName = htmlspecialchars($args['model']);
        return (new Controller())->getModel($modelName);
    }

    /**
     * Redirect to an other page
     *
     * @param string $key
     */
    private function redirectTo($key): void
    {
        $this->routes[$key]['fn']($this->routes[$key]['args']);
    }

    /**
     * Redirect with reload to an other page
     *
     * @param string $key
     */
    private function reloadTo($key): void
    {
        header("Location: $key");
        die('Redirection... .');
    }

    /**
     * Home page
     *
     * @param array $args
     * @return false
     */
    private function homeView(array $args)
    {
        $isGood = $this->checkMethodValidity($args);
        if (!$isGood) return false;

        echo "Home page \n\r";
    }

    private function contactView(array $args)
    {
        $isGood = $this->checkMethodValidity($args);
        if (!$isGood) $this->reloadTo('/');

        echo "Contact page \n\r";
    }

    private function postApi(array $args)
    {
        $isGood = $this->checkMethodValidity($args);
        if (!$isGood) $this->reloadTo('/');

        $model = $this->getModel($args);
        if (!$model) return false;

        // $model->getValue()
        echo "Post Api \n\r";

        unset($model);
        return true;
    }
}

/**
 * Class App
 */
class App
{
    private string $_URL;
    private Router $router;

    public function __construct(string $URL)
    {
        $this->_URL = $URL;
        $this->router = new Router();
        $this->create_routes();
        $this->exec_route();
    }

    /**
     * Call exec func to print view depending of URL value
     */
    private function exec_route(): void
    {
        $this->router->exec($this->_URL);
    }

    /**
     * Add routes in router
     */
    private function create_routes(): void
    {
        $status = $this->router->add_route('', array($this->router, 'homeView'), ['method' => 'GET']);
        if (!$status) die('Route home not added!');

        $status = $this->router->add_route('contact', array($this->router, 'contactView'), ['method' => 'GET']);
        if (!$status) die('Route contact not added!');

        $status = $this->router->add_route('post', array($this->router, 'postApi'), ['method' => 'POST', 'model' => 'ModelPost']);
        if (!$status) die('Route postApi not added!');
    }
}

(new App(Utils::secureString($_GET['url'])));