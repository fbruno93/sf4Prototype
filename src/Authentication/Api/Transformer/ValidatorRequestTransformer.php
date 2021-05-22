<?php

namespace App\Authentication\Api\Transformer;

use App\Core\Exception\ApiException;

use ReflectionClass;
use ReflectionException;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ValidatorRequestTransformer implements ArgumentValueResolverInterface
{
    private ValidatorInterface $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @inheritDoc
     *
     * @param Request $request
     * @param ArgumentMetadata $argument
     *
     * @return bool
     */
    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        try {
            $reflection = new ReflectionClass($argument->getType());
            return $reflection->implementsInterface(RequestTransformerInterface::class);
        } catch (ReflectionException $e) {
            return false;
        }
    }

    /**
     * @inheritDoc
     *
     * @param Request $request
     * @param ArgumentMetadata $argument
     *
     * @return iterable
     *
     * @throws ApiException
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $class = $argument->getType();
        $dto = new $class($request);

        $errors = $this->validator->validate($dto);
        if (count($errors) > 0) {
            throw new ApiException($errors->get(0)->getMessage(), Response::HTTP_BAD_REQUEST);
        }

        yield $dto;
    }

}