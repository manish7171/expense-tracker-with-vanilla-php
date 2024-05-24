<?php

declare(strict_types=1);

namespace App;

use App\Contracts\AuthInterface;
use App\Contracts\SessionInterface;
use App\Contracts\UserInterface;
use App\Contracts\UserProviderServiceInterface;

class Auth implements AuthInterface
{
  private ?UserInterface $user = null;

  public function __construct(private readonly UserProviderServiceInterface $userProvider, private readonly SessionInterface $session)
  {
  }

  public function user(): ?UserInterface
  {
    if ($this->user !== null) {
      return $this->user;
    }

    $userId = $this->session->get('user');

    if (!$userId) {
      return null;
    }

    $user = $this->userProvider->getById($userId);

    if (!$user) {
      return null;
    }

    $this->user = $user;

    return $this->user;
  }

  public function attemptLogin(array $credentials): bool
  {

    $user = $this->userProvider->getByCredentials($credentials);

    if (!$user || !$this->checkCredential($user, $credentials)) {
      return false;
    }

    $this->session->regenerate();

    $this->session->put("user", $user->getId());

    $this->user = $user;

    return true;
  }

  public function checkCredential(UserInterface $user, array $credentials): bool
  {
    return password_verify($credentials['password'], $user->getPassword());
  }

  public function logout(): void
  {
    $this->session->forget("user");

    $this->session->regenerate();

    $this->user = null;
  }
}
