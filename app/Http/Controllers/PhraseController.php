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
            IFNULL(tests.first_passage, '0000-00-00 00:00:00') AS first_passage,
            IFNULL(tests.last_passage, '0000-00-00 00:00:00') AS last_passage,
            IF(tests.phrase_id IS NULL, 0, 1) AS test_exists
            FROM phrases
            LEFT JOIN tests
            ON (phrases.id = tests.phrase_id)
            ORDER BY last_passage, id
            LIMIT :phrases_count",
            ['phrases_count' => env('QUESTIONS_COUNT', 30)]
        );

        return response()->json($results);
    }

    public function update(int $phraseId)
    {
        return 'UPD';
    }
}
