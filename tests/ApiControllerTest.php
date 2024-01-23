<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ApiControllerTest extends WebTestCase
{
    public function testScoreAction()
    {
        $client = static::createClient();

        $term = 'php';
        $from = 'github';

        $client->request('GET', '/score', ['term' => $term, 'from' => $from]);

        $response = $client->getResponse();

        // response is successful (HTTP 200)
        $this->assertTrue($response->isSuccessful());

        // response is in JSON format
        $this->assertTrue($response->headers->contains('Content-Type', 'application/json'));

        // Decode the JSON response
        $data = json_decode($response->getContent(), true);

        // JSON response has the expected structure
        $this->assertArrayHasKey('term', $data);
        $this->assertArrayHasKey('score', $data);

        // 'term' and 'score' values match the expected values
        $this->assertEquals($term, $data['term']);
        $this->assertIsFloat($data['score']); // score is a float

    }
}