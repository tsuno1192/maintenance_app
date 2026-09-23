<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Face recognition driver
    |--------------------------------------------------------------------------
    |
    | - fake: ローカル/テスト用（実顔認証なし。参照画像と完全一致で照合）
    | - aws:  Amazon Rekognition（Collection + SearchFacesByImage）
    |
    | 保育園の児童顔データは要配慮個人情報です。利用目的の明示・保護者同意・
    | 保管期間・アクセス制御を運用ポリシーと合わせてください。
    |
    */
    'driver' => env('FACE_RECOGNITION_DRIVER', 'fake'),

    'confidence_threshold' => (float) env('FACE_RECOGNITION_THRESHOLD', 80),

    'aws' => [
        'region' => env('AWS_DEFAULT_REGION', 'ap-northeast-1'),
        'collection_id' => env('AWS_REKOGNITION_COLLECTION', 'hoikuku-children-faces'),
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
    ],

];
