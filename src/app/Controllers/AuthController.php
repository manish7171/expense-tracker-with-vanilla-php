<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Attributes\Get;
use App\Attributes\Post;
use App\Entities\User;
use App\Exceptions\ValidationException;
use Twig\Environment;
use Doctrine\ORM\EntityManager;
use Valitron\Validator;

class AuthController
{

  public function __construct(private Environment $twig, private EntityManager $em)
  {
  }

  #[Get('/login')]
  public function loginView(): string
  {
    return $this->twig->render('auth/login.twig');
  }

  #[Get('/register')]
  public function registerView(): string
  {
    $errors = $_SESSION['errors'] ?? [];
    $old = $_SESSION['old'] ?? [];
    unset($_SESSION['errors']);
    return $this->twig->render('auth/register.twig', ['errors' => $errors, 'old' => $old]);
  }

  #[Post('/register')]
  public function register(): void
  {
    $data = $_POST;

    $user = new User();

    $user->setName($data['name']);
    $user->setEmail('test@test.com');
    $user->setPassword(password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]));

    $this->em->persist($user);
    $this->em->flush();

    //TODO: redirect to login page
  }
}
