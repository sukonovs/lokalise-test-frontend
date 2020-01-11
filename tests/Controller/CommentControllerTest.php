<?php

namespace App\Tests\Controller;

use App\Entity\Comment;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Factory;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CommentControllerTest extends WebTestCase
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var KernelBrowser
     */
    private $client;

    protected function setUp()
    {
        /** @var KernelBrowser client */
        $this->client = static::createClient();

        $this->em = self::$container->get(EntityManagerInterface::class);
        $this->truncateEntities(
            [
                Comment::class,
            ]
        );
    }

    public function testEmptyComments()
    {
        $this->client->request('GET', '/comment/');
        $response = $this->client->getResponse();

        $this->assertResponseIsSuccessful();

        $content = $response->getContent();
        $this->assertEquals('[]', $content);
    }

    public function testTwoComments()
    {
        $comment1 = new Comment();
        $comment1->setEmail('email1@email.com');
        $comment1->setText('text1');
        $this->em->persist($comment1);
        $this->em->flush();

        $this->client->request('GET', '/comment/');
        $response = $this->client->getResponse();
        $this->assertResponseIsSuccessful();

        $content = $response->getContent();
        $this->assertJson($content);

        $responseAsArray = json_decode($content, true);

        $this->assertCount(1, $responseAsArray);

        $this->assertArrayHasKey('email', $responseAsArray[0]);
        $this->assertArrayHasKey('text', $responseAsArray[0]);
        $this->assertEquals($responseAsArray[0]['email'], 'email1@email.com');
        $this->assertEquals($responseAsArray[0]['text'], 'text1');

        $comment2 = new Comment();
        $comment2->setEmail('email2@email.com');
        $comment2->setText('text2');
        $this->em->persist($comment2);
        $this->em->flush();

        $this->client->request('GET', '/comment/');
        $response = $this->client->getResponse();
        $this->assertResponseIsSuccessful();

        $content = $response->getContent();
        $this->assertJson($content);

        $responseAsArray = json_decode($content, true);

        $this->assertCount(2, $responseAsArray);

        $this->assertArrayHasKey('email', $responseAsArray[0]);
        $this->assertArrayHasKey('text', $responseAsArray[0]);
        $this->assertEquals($responseAsArray[0]['email'], 'email1@email.com');
        $this->assertEquals($responseAsArray[0]['text'], 'text1');

        $this->assertArrayHasKey('email', $responseAsArray[1]);
        $this->assertArrayHasKey('text', $responseAsArray[1]);
        $this->assertEquals($responseAsArray[1]['email'], 'email2@email.com');
        $this->assertEquals($responseAsArray[1]['text'], 'text2');
    }

    public function testValidCommentCreation()
    {
        $faker = Factory::create();

        $this->client->xmlHttpRequest(
            'POST',
            '/comment/new',
            [
                'email' => $faker->safeEmail,
                'text'  => $faker->text(100),
            ]
        );
        $this->assertResponseStatusCodeSame(201);
        
        $comments = $this->em->getRepository(Comment::class)->findAll();
        $this->assertCount(1, $comments);
    }

    public function testNothingPosted()
    {
        $this->client->xmlHttpRequest(
            'POST',
            '/comment/new',
            [
            ]
        );

        $this->assertResponseStatusCodeSame(400);

        $content = $this->client->getResponse()->getContent();

        $this->assertContains('"email":["This value should not be blank."]', $content);
        $this->assertContains('"text":["This value should not be blank."', $content);
    }

    public function testEmailNoPosted()
    {
        $faker = Factory::create();

        $this->client->xmlHttpRequest(
            'POST',
            '/comment/new',
            [
                'text' => $faker->text(100),
            ]
        );

        $this->assertResponseStatusCodeSame(400);

        $content = $this->client->getResponse()->getContent();

        $this->assertContains('"email":["This value should not be blank."]', $content);
    }

    public function testTextNotPosted()
    {
        $faker = Factory::create();

        $this->client->xmlHttpRequest(
            'POST',
            '/comment/new',
            [
                'email' => $faker->safeEmail,
            ]
        );

        $this->assertResponseStatusCodeSame(400);

        $content = $this->client->getResponse()->getContent();

        $this->assertContains('"text":["This value should not be blank."]', $content);
    }

    public function testTextTooShort()
    {
        $faker = Factory::create();

        $this->client->xmlHttpRequest(
            'POST',
            '/comment/new',
            [
                'email' => $faker->safeEmail,
                'text'  => '',
            ]
        );

        $this->assertResponseStatusCodeSame(400);

        $content = $this->client->getResponse()->getContent();

        $this->assertContains('"text":["This value should not be blank."]', $content);
    }

    public function testTextTooLong()
    {
        $faker = Factory::create();

        $this->client->xmlHttpRequest(
            'POST',
            '/comment/new',
            [
                'email' => $faker->safeEmail,
                'text'  => $faker->realText(2000),
            ]
        );

        $this->assertResponseStatusCodeSame(400);

        $content = $this->client->getResponse()->getContent();

        $this->assertContains('"text":["This value is too long. It should have 1000 characters or less."]', $content);
    }

    public function testEmailTooShort()
    {
        $faker = Factory::create();

        $this->client->xmlHttpRequest(
            'POST',
            '/comment/new',
            [
                'email' => '',
                'text'  => $faker->text(200),
            ]
        );

        $this->assertResponseStatusCodeSame(400);

        $content = $this->client->getResponse()->getContent();

        $this->assertContains('"email":["This value should not be blank."]', $content);
    }

    public function testEmailTooLong()
    {
        $faker = Factory::create();

        $this->client->xmlHttpRequest(
            'POST',
            '/comment/new',
            [
                'email' => str_replace(' ', '', $faker->text(2000)).'@email.com',
                'text'  => $faker->text(200),
            ]
        );

        $this->assertResponseStatusCodeSame(400);

        $content = $this->client->getResponse()->getContent();

        $this->assertContains('"email":["This value is too long. It should have 255 characters or less."]', $content);
    }


    /**
     * @param array $entities
     *
     * @see https://symfonycasts.com/screencast/phpunit/control-database#clearing-the-database-before-tests
     */
    private function truncateEntities(array $entities)
    {
        $connection = $this->em->getConnection();
        $databasePlatform = $connection->getDatabasePlatform();
        if ($databasePlatform->supportsForeignKeyConstraints()) {
            $connection->query('SET FOREIGN_KEY_CHECKS=0');
        }
        foreach ($entities as $entity) {
            $query = $databasePlatform->getTruncateTableSQL(
                $this->em->getClassMetadata($entity)->getTableName()
            );
            $connection->executeUpdate($query);
        }
        if ($databasePlatform->supportsForeignKeyConstraints()) {
            $connection->query('SET FOREIGN_KEY_CHECKS=1');
        }
    }
}
