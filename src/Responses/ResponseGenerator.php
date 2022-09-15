<?php

namespace Mtrajano\LaravelSwagger\Responses;

use Mtrajano\LaravelSwagger\DataObjects\Route;

class ResponseGenerator
{
    /**
     * @var Route
     */
    private $route;

    /**
     * @var array
     */
    private $errorDefinitions;

    public function __construct(Route $route, array $errorDefinitions)
    {
        $this->route = $route;
        $this->errorDefinitions = $errorDefinitions;
    }

    public function generate(): array
    {
        $generators = $this->getGenerators();

        $response = [];
        foreach ($generators as $generator) {
            $response += $generator->generate();
        }
        $responseRef = $this->route->getResponseRef();
        if($responseRef){
            $response[] = [
                '200' => [
                    '$ref' => $responseRef,
                ]
            ];
        }
        if (empty($response)) {
            // Set default response
            $response = [
                '200' => [
                    'description' => 'OK',
                ],
            ];
        }

        return $response;
    }

    /**
     * @return array
     */
    private function getGenerators()
    {
        return [
            new SuccessResponseGenerator($this->route),
            new ErrorResponseGenerator($this->route, $this->errorDefinitions),
        ];
    }
}
