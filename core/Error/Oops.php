<?php

namespace Core\Error;

class Oops
{
    /** @var Response $response */
    protected $response;
    /** @var \Exception $exception */
    protected $exception;

    public function __construct(Response $response)
    {
        $this->response = $response;
    }

    /**
     * Capture une erreur et retourne une réponse HTTP
     *
     * @param \Exception $e
     * @return Response
     * @throws \ReflectionException
     */
    public function capture(\Exception $e): Response
    {
        $this->exception = $e;

        $this->response->view('core.exception', [
            'name' => (new \ReflectionClass($this->exception))->getShortName(),
            'message' => $this->exception->getMessage(),
        ]);

        return $this->response;
    }
}
