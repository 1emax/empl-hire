<?php

namespace App\Tests\Controller;

use App\Entity\Employee;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class EmployeeControllerTest extends WebTestCase
{
    private $client;
    private $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);

        // Очищаем базу данных перед каждым тестом
        $this->entityManager->createQuery('DELETE FROM App\Entity\Employee e')->execute();
    }

    public function testCreateEmployee(): void
    {
        $this->client->request(
            'POST',
            '/api/employees',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'firstName' => 'John',
                'lastName' => 'Doe',
                'email' => 'john@example.com',
                'hireDate' => (new \DateTime('tomorrow'))->format('Y-m-d'),
                'salary' => '1000.00'
            ])
        );

        $this->assertResponseStatusCodeSame(201);
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('id', $responseData);
        $this->assertEquals('John', $responseData['firstName']);
    }

    public function testCreateEmployeeValidation(): void
    {
        $this->client->request(
            'POST',
            '/api/employees',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'firstName' => '',
                'lastName' => 'Doe',
                'email' => 'invalid-email',
                'hireDate' => (new \DateTime('yesterday'))->format('Y-m-d'),
                'salary' => '50.00'
            ])
        );

        $this->assertResponseStatusCodeSame(400);
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('errors', $responseData);
    }

    public function testListEmployees(): void
    {
        // Создаем тестового сотрудника
        $employee = new Employee();
        $employee->setFirstName('John');
        $employee->setLastName('Doe');
        $employee->setEmail('john@example.com');
        $employee->setHireDate(new \DateTime('tomorrow'));
        $employee->setSalary('1000.00');

        $this->entityManager->persist($employee);
        $this->entityManager->flush();

        $this->client->request('GET', '/api/employees');

        $this->assertResponseIsSuccessful();
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($responseData);
        $this->assertCount(1, $responseData);
    }

    public function testReadEmployee(): void
    {
        // Создаем тестового сотрудника
        $employee = new Employee();
        $employee->setFirstName('John');
        $employee->setLastName('Doe');
        $employee->setEmail('john@example.com');
        $employee->setHireDate(new \DateTime('tomorrow'));
        $employee->setSalary('1000.00');

        $this->entityManager->persist($employee);
        $this->entityManager->flush();

        $this->client->request('GET', '/api/employees/' . $employee->getId());

        $this->assertResponseIsSuccessful();
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('John', $responseData['firstName']);
    }

    public function testReadNonExistentEmployee(): void
    {
        $this->client->request('GET', '/api/employees/999');
        $this->assertResponseStatusCodeSame(404);
    }

    public function testUpdateEmployee(): void
    {
        // Создаем тестового сотрудника
        $employee = new Employee();
        $employee->setFirstName('John');
        $employee->setLastName('Doe');
        $employee->setEmail('john@example.com');
        $employee->setHireDate(new \DateTime('tomorrow'));
        $employee->setSalary('1000.00');

        $this->entityManager->persist($employee);
        $this->entityManager->flush();

        $this->client->request(
            'PUT',
            '/api/employees/' . $employee->getId(),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'firstName' => 'Jane',
                'lastName' => 'Doe',
                'email' => 'jane@example.com',
                'hireDate' => (new \DateTime('tomorrow'))->format('Y-m-d'),
                'salary' => '1500.00'
            ])
        );

        $this->assertResponseIsSuccessful();
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Jane', $responseData['firstName']);
        $this->assertEquals('1500.00', $responseData['salary']);
    }

    public function testDeleteEmployee(): void
    {
        // Создаем тестового сотрудника
        $employee = new Employee();
        $employee->setFirstName('John');
        $employee->setLastName('Doe');
        $employee->setEmail('john@example.com');
        $employee->setHireDate(new \DateTime('tomorrow'));
        $employee->setSalary('1000.00');

        $this->entityManager->persist($employee);
        $this->entityManager->flush();

        $id = $employee->getId();

        // Проверяем, что сотрудник существует
        $this->assertNotNull(
            $this->entityManager->getRepository(Employee::class)->find($id),
            'Сотрудник должен существовать перед удалением'
        );

        $this->client->request('DELETE', '/api/employees/' . $id);

        $this->assertResponseStatusCodeSame(204);

        $this->entityManager->clear();

        // Проверяем, что сотрудник больше не существует
        $this->assertNull(
            $this->entityManager->getRepository(Employee::class)->find($id),
            'Сотрудник не должен существовать после удаления'
        );
    }
}
