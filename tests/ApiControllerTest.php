<?php

namespace App\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;  
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use App\Controller\ApiLinks;
use App\Controller\ApiController;

class ApiControllerTest extends TestCase
{
    public function testScore()
    {
        $client = $this->getMockBuilder(Client::class)->disableOriginalConstructor()->getMock();
        $request = $this->createMock(Request::class);
        $api = $this->createMock(ApiLinks::class);
        $api->method('getUrl')->willReturn('https://api.example.com');

        $controller = new ApiController($client, $api);

        $request->method('get')->willReturnMap([
            ['term', 'symfony'],
            ['from', 'github']
        ]);

        $client->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive(
                ['https://api.example.com/symfony?in:comments'],
                ['https://api.example.com/symfony?in:comments']
            )
            ->willReturnOnConsecutiveCalls(
                json_encode(['total_count' => 5]),
                json_encode(['total_count' => 10])
            );

        $results = $controller->fetchResults($request, 'symfony','github');
        $this->assertEquals('5*10', $results);

        $score = $controller->calculateScore($results, 'symfony');
        $this->assertEquals(50, $score);
    }
}
