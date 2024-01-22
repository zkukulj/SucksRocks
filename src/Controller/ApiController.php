<?php

namespace App\Controller;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;  
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Controller\ApiLinks;

class ApiController extends AbstractController
{
    /**
     * @Route("/score", name="score")
     */
    public function score(Request $request)
    {
        $term = $request->query->get('term');
        $from = $request->query->get('from');
        // Get the URL from the API class
        $api = new ApiLinks();
        $url = $api->getUrl($from);

        // Check if the result table exists in the database at checkResultTableExists ln 104
        $resultTableExists = $this->checkResultTableExists();
        if (!$resultTableExists) {
            // Create the result table if it does not exist
            $this->createResultTable();
        }

        // Check if the result already exists in the database at checkResultExists ln 122
        $resultExists = $this->checkResultExists(strtolower($from), $term);
        if ($resultExists) {
            // Return the existing result if found
            $result = $this->getExistingResult(strtolower($from), $term);
            return new JsonResponse([
                'term' => $term,
                'score' => $result['score']
            ]);
        }

        // Make a request to the API and fetch the results at fetchResults ln 59 
        $results = $this->fetchResults($url, $term, $from);
        // Calculate the score
        $score = $this->calculateScore($results, $term);

        // Save the result in the SQLite database at saveResult ln 95
        $this->saveResult(strtolower($from), $term, $score);

        // Return the formatted result as JSON
        return new JsonResponse([
            'term' => $term,
            'score' => $score
        ]);
    }

    private function fetchResults($url, $term, $from)
    {
        // We use Guzzle as HTTP client library for making API requests.
        // RequestOptions::VERIFY => false !!! Must not be used in production env.
            $client = new Client([
                RequestOptions::VERIFY => false,
            ]);
            $sucks=" sucks";
            $rocks=" rocks";
            // GitHub API specifies where to look for the results
            $where=" in:comments";
            // AS FOR THE SUCKS
            $sucks_response = $client->get($url."$term".urlencode($sucks.$where));
            $sucks_results = json_decode($sucks_response->getBody(), true);
            // AND THE ROCKS
            $rocks_response = $client->get($url."$term".urlencode($rocks.$where));
            $rocks_results = json_decode($rocks_response->getBody(), true);
            return $sucks_results["total_count"]."*".$rocks_results["total_count"];
    }

    private function calculateScore($results, $term)
    {
        // Parsing our return
        $flattenedResults = explode("*",$results);

        // Separatiing rocks and sucks
        $positiveCount = $flattenedResults[1];
        $negativeCount = $flattenedResults[0];

        // Calculate the score as a ratio of positive results to the total number of results
        $totalResults = $positiveCount+$negativeCount;
        $score = ($totalResults > 0) ? ($positiveCount / $totalResults) * 10 : 0;

        return round($score, 2);
    }

    private function saveResult($from, $term, $score)
    {
        // Saving to SQLite database
        $pdo = new \PDO('sqlite:' . $this->getParameter('kernel.project_dir') . '/var/data.db');
        $pdo->exec("CREATE TABLE IF NOT EXISTS scores (api TEXT, term TEXT, score REAL)");
        $stmt = $pdo->prepare("INSERT INTO scores (api, term, score) VALUES (?, ?, ?)");
        $stmt->execute([$from, $term, $score]);
    }

    private function checkResultTableExists()
    {
        // Check if the result table exists in the database
        $pdo = new \PDO('sqlite:' . $this->getParameter('kernel.project_dir') . '/var/data.db');
        $stmt = $pdo->prepare("SELECT name FROM sqlite_master WHERE type='table' AND name='scores'");
        $stmt->execute();
        $result = $stmt->fetch();
        return !empty($result);
    }

    private function createResultTable()
    {
        // Create the result table if it does not exist
        $pdo = new \PDO('sqlite:' . $this->getParameter('kernel.project_dir') . '/var/data.db');
        $stmt = $pdo->prepare("CREATE TABLE scores (api TEXT, term TEXT, score REAL)");
        $stmt->execute();
    }

    private function checkResultExists($from, $term)
    {
        // Check if the result exists in the database
        $pdo = new \PDO('sqlite:' . $this->getParameter('kernel.project_dir') . '/var/data.db');
        $stmt = $pdo->prepare("SELECT * FROM scores WHERE api = ? AND term = ?");
        $stmt->execute([$from, $term]);
        $result = $stmt->fetch();
        return !empty($result);
    }

    private function getExistingResult($from, $term)
    {
        // Get the existing result from the database
        $pdo = new \PDO('sqlite:' . $this->getParameter('kernel.project_dir') . '/var/data.db');
        $stmt = $pdo->prepare("SELECT * FROM scores WHERE api = ? AND term = ?");
        $stmt->execute([$from, $term]);
        $result = $stmt->fetch();
        return [
            'term' => $result['term'],
            'score' => $result['score'],
        ];
    }
    
    
}
