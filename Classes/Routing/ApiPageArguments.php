<?php

namespace Fr\ApiToken\Routing;

use TYPO3\CMS\Core\Routing\PageArguments;
use TYPO3\CMS\Core\Routing\Route;

class ApiPageArguments extends PageArguments
{
    /**
     * @var Route
     */
    protected $route;

    /** @noinspection PhpMissingParentConstructorInspection */
    public function __construct(PageArguments $pageArguments, Route $route)
    {
        $this->cloneFromParent($pageArguments);
        $this->route = $route;
    }

    /**
     * @return Route
     */
    public function getRoute(): Route
    {
        return $this->route;
    }

    /**
     * @param PageArguments $pageArguments
     */
    private function cloneFromParent(PageArguments $pageArguments): void
    {
        $variables = get_object_vars($pageArguments);
        foreach ($variables as $variable => $value) {
            $this->{$variable} = $value;
        }
    }
}
