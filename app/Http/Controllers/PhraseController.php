<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PhraseController extends Controller
{
    public function index()
    {
        $results = DB::select(
            "SELECT
            phrases.id,
            phrases.phrase,
            phrases.translation,
            IFNULL(tests.passages_cnt, 0) AS passages_cnt,
            IFNULL(tests.real_passages_cnt, 0) AS real_passages_cnt,
            IFNULL(tests.first_passage, '0000-00-00 00:00:00') AS first_passage,
            IFNULL(tests.last_passage, '0000-00-00 00:00:00') AS last_passage,
            IF(tests.phrase_id IS NULL, 0, 1) AS test_exists
            FROM phrases
            LEFT JOIN tests
            ON (phrases.id = tests.phrase_id)
            ORDER BY passages_cnt, last_passage, id
            LIMIT :phrases_count",
            ['phrases_count' => env('QUESTIONS_COUNT', 30)]
        );

        return response()->json($results);
    }

    public function studiedPhrases()
    {
        $results = DB::select(
            "SELECT
            phrases.id,
            phrases.phrase,
            phrases.translation,
            IFNULL(tests.passages_cnt, 0) AS passages_cnt,
            IFNULL(tests.real_passages_cnt, 0) AS real_passages_cnt,
            IFNULL(tests.first_passage, '0000-00-00 00:00:00') AS first_passage,
            IFNULL(tests.last_passage, '0000-00-00 00:00:00') AS last_passage,
            IF(tests.phrase_id IS NULL, 0, 1) AS test_exists
            FROM phrases
            LEFT JOIN tests
            ON (phrases.id = tests.phrase_id)
            WHERE tests.passages_cnt >= 3
            ORDER BY last_passage, id
            LIMIT :phrases_count",
            ['phrases_count' => env('STUDIED_QUESTIONS_COUNT', 30)]
        );

        return response()->json($results);
    }

    public function update(int $phraseId)
    {
        $results = DB::select(
            "SELECT
            phrases.id,
            IFNULL(tests.passages_cnt, 0) AS passages_cnt,
            IFNULL(tests.real_passages_cnt, 0) AS real_passages_cnt,
            IF(tests.phrase_id IS NULL, 0, 1) AS test_exists
            FROM phrases
            LEFT JOIN tests
            ON (phrases.id = tests.phrase_id)
            WHERE phrases.id = :phrase_id",
            ['phrase_id' => $phraseId]
        );

        if (count($results) === 0) {
            return response()->json(['message' => 'Phrase not found'], 404);
        }

        $testExists = $results[0]->test_exists;
        $passagesCnt = $results[0]->passages_cnt;
        $realPassagesCnt = $results[0]->real_passages_cnt;
        $newPassagesCnt = ($passagesCnt < 3) ? $passagesCnt + 1 : $passagesCnt;
        $newRealPassagesCnt = $realPassagesCnt + 1;

        if ($testExists) {
            $this->updateTest($phraseId, $newPassagesCnt, $newRealPassagesCnt);
        }
        else {
            $this->insertTest($phraseId);
        }

        return response()->json(['message' => 'Success'], 200);
    }

    private function insertTest($phraseId)
    {
        DB::insert(
            "INSERT INTO tests (phrase_id, passages_cnt, real_passages_cnt, first_passage, last_passage)
            VALUES (:phrase_id, 1, 1, NOW(), NOW())",
            ['phrase_id' => $phraseId]
        );
    }

    private function updateTest($phraseId, $passagesCnt, $realPassagesCnt)
    {
        DB::update(
            "UPDATE tests 
            SET passages_cnt = :passages_cnt, real_passages_cnt = :real_passages_cnt, last_passage = NOW()
            WHERE phrase_id = :phrase_id", [
                'phrase_id' => $phraseId,
                'passages_cnt' => $passagesCnt,
                'real_passages_cnt' => $realPassagesCnt
            ]
        );
    }
}
