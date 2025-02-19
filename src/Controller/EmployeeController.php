<?php

namespace App\Controller;

use App\Entity\Employee;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

#[Route('/api/employees')]
final class EmployeeController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ValidatorInterface $validator,
        private readonly SerializerInterface $serializer
    ) {}

    #[OA\Post(
        description: 'Создание нового сотрудника',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: new Model(type: Employee::class, groups: ['create']))
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Успешно создано',
                content: new OA\JsonContent(ref: new Model(type: Employee::class, groups: ['read']))
            ),
            new OA\Response(
                response: 400,
                description: 'Ошибка валидации',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'errors', type: 'object')
                    ]
                )
            )
        ]
    )]
    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        // Обработка ошибок десериализации JSON
        try {
            /** @var Employee $employee */
            $employee = $this->serializer->deserialize(
                $request->getContent(),
                Employee::class,
                'json',
                ['groups' => ['create'], 'datetime_format' => 'Y-m-d']
            );
        } catch (\Throwable $e) {
            return $this->json(
                ['error' => 'Неверный формат данных: ' . $e->getMessage()],
                Response::HTTP_BAD_REQUEST
            );
        }

        $violations = $this->validator->validate($employee);
        if (count($violations) > 0) {
            return $this->json(
                ['errors' => $this->getValidationErrors($violations)],
                Response::HTTP_BAD_REQUEST
            );
        }

        $this->entityManager->persist($employee);
        $this->entityManager->flush();

        return $this->json($employee, Response::HTTP_CREATED);
    }

    #[OA\Get(
        description: 'Возвращает список  сотрудников',
        responses: [
            new OA\Response(
                response: 200,
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: new Model(type: Employee::class))
                )
            )
        ]
    )]
    #[Route('', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $employees = $this->entityManager->getRepository(Employee::class)->findAll();
        return $this->json($employees);
    }

    #[OA\Get(
        description: 'Возвращение информации о сотруднике по ID',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Информация о сотруднике',
                content: new OA\JsonContent(ref: new Model(type: Employee::class))
            ),
            new OA\Response(
                response: 404,
                description: 'Не найдено'
            )
        ]
    )]
    #[Route('/{id}', methods: ['GET'])]
    public function read(int $id): JsonResponse
    {
        $employee = $this->entityManager->getRepository(Employee::class)->find($id);

        if (!$employee) {
            return $this->json(
                ['error' => 'Сотрудник не найден'],
                Response::HTTP_NOT_FOUND
            );
        }

        return $this->json($employee);
    }

    #[OA\Put(
        description: 'Обновление информации о сотруднике',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: new Model(type: Employee::class, groups: ['update']))
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Данные успешно обновлены',
                content: new OA\JsonContent(ref: new Model(type: Employee::class, groups: ['read']))
            ),
            new OA\Response(
                response: 400,
                description: 'Ошибка валидации'
            ),
            new OA\Response(
                response: 404,
                description: 'Не найдено'
            )
        ]
    )]
    #[Route('/{id}', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $employee = $this->entityManager->getRepository(Employee::class)->find($id);

        if (!$employee) {
            return $this->json(
                ['error' => 'Сотрудник не найден'],
                Response::HTTP_NOT_FOUND
            );
        }

        try {
            $this->serializer->deserialize(
                $request->getContent(),
                Employee::class,
                'json',
                [
                    'datetime_format' => 'Y-m-d',
                    'groups' => ['update'],
                    'object_to_populate' => $employee
                ]
            );
        } catch (\Throwable $e) {
            return $this->json(
                ['error' => 'Неверный формат данных: ' . $e->getMessage()],
                Response::HTTP_BAD_REQUEST
            );
        }

        $violations = $this->validator->validate($employee);
        if (count($violations) > 0) {
            return $this->json(
                ['errors' => $this->getValidationErrors($violations)],
                Response::HTTP_BAD_REQUEST
            );
        }

        $this->entityManager->flush();

        return $this->json($employee);
    }

    #[OA\Delete(
        description: 'Удаляет сотрудника',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 204,
                description: 'Успешно удалено'
            ),
            new OA\Response(
                response: 404,
                description: 'Не найдено'
            )
        ]
    )]
    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $employee = $this->entityManager->getRepository(Employee::class)->find($id);

        if (!$employee) {
            return $this->json(
                ['error' => 'Сотрудник не найден'],
                Response::HTTP_NOT_FOUND
            );
        }

        $this->entityManager->remove($employee);
        $this->entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    private function getValidationErrors(ConstraintViolationListInterface $violations): array
    {
        $errors = [];
        foreach ($violations as $violation) {
            $errors[$violation->getPropertyPath()][] = $violation->getMessage();
        }
        return $errors;
    }
}
