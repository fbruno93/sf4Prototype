<?php

namespace App\User\Controller;

use App\Core\Model\UserModel;
use OpenApi\Annotations as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

/**
 * @OA\Tag(name="user")
 *
 * @Route("/user")
 */
class UserController extends AbstractController
{
    /**
     * @Route("/", name="user_profile", methods={"GET"})
     */
    public function userProfile(UserModel $userModel): JsonResponse
    {
        $userModel->load($this->getUser()->getUsername());

        $userModel->getCity()->setName("bfy");
        $userModel->save();

        return $this->json($userModel, Response::HTTP_OK);
    }
}
