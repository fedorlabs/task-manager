<?php

declare(strict_types=1);

namespace App\Controller;

use App\Domain\Entity\User;
use App\Domain\Exception\UnauthorizedException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

abstract class ApiController extends AbstractController
{
    protected function getCurrentUser(): User
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw new UnauthorizedException('The user is not authorized');
        }
        return $user;
    }
}
