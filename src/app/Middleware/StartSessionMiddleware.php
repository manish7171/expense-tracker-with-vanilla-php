<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Exceptions\SessionException;
use App\Exceptions\ValidationException;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class StartSessionMiddleware implements MiddlewareInterface
{
  public function __construct(private readonly ResponseFactoryInterface $responseFactory)
  {
  }

  public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
  {
    if (session_status() === PHP_SESSION_ACTIVE) {
      throw new SessionException("Session has already been started.");
    }

    if (headers_sent($filename, $line)) {
      throw new SessionException("Headers already sent");
    }

    session_start();

    $response = $handler->handle($request);

    session_write_close();

    return $response;
  }
}
