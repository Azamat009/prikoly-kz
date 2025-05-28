<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RequestStack;

class UserManager{

    private bool $isNewUser = false;
    private EntityManagerInterface $entityManager;
    private RequestStack $requestStack;

    public function __construct(EntityManagerInterface $entityManager, RequestStack $requestStack){
        $this->requestStack = $requestStack;
        $this->entityManager = $entityManager;
    }

    public function getCurrentUser(): User{
        try {
            $request = $this->requestStack->getCurrentRequest();
            $uuid = $request->cookies->get('user_uuid');
            $user = null;

            if ($uuid) {
                $uuidObj = Uuid::fromString($uuid);
                $user = $this->entityManager->getRepository(User::class)->findOneBy(['uuid' => $uuidObj]);
            }

            if (!$user) {
                $user = $this->createNewUser();
                $this->isNewUser = true;
            }

            return $user;
        } catch (\Exception $exception){
            throw $exception;
        }
    }

    public function isNewUser(): bool{
        return $this->isNewUser;
    }

    public function createNewUserCookie(User $user): Cookie{
        return new Cookie(
            'user_uuid',
            $user->getUuid()->toString(),
            time() + (3600*24*365),
            '/',
            null,
            false,
            false
        );
    }

    private function createNewUser(): User{
        try {
            $user = new User();
            $user->setUuid(Uuid::uuid4());

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            return $user;
        } catch (\Exception $exception){
            throw $exception;
        }
    }

}