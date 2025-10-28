<?php
require __DIR__ . '/vendor/autoload.php';

use Elasticsearch\ClientBuilder;  // ✅ correct namespace for v7 client

$client = ClientBuilder::create()
    ->setHosts(['localhost:9200'])
    ->build();


$index = 'demo_index';

// Step 2: Delete index if it already exists (for clean demo)
if ($client->indices()->exists(['index' => $index])) {
    $client->indices()->delete(['index' => $index]);
}

// Step 3: Create index with mapping + analyzer
$params = [
    'index' => $index,
    'body' => [
        'settings' => [
            'analysis' => [
                'analyzer' => [
                    'my_custom_analyzer' => [
                        'type' => 'standard',
                        'stopwords' => '_english_'
                    ]
                ]
            ]
        ],
        'mappings' => [
            'dynamic' => true,
            'properties' => [
                'title' => ['type' => 'text', 'analyzer' => 'my_custom_analyzer'],
                'author' => ['type' => 'keyword'],
                'published_date' => ['type' => 'date'],
                'price' => ['type' => 'float']
            ]
        ]
    ]
];
$client->indices()->create($params);
echo "Index created successfully.\n";

// Step 4: Create (index) documents
$docs = [
    ['id' => 1, 'title' => 'Elasticsearch for Beginners', 'author' => 'Jane Doe', 'price' => 29.99],
    ['id' => 2, 'title' => 'Advanced Elasticsearch', 'author' => 'John Smith', 'price' => 39.99],
    ['id' => 3, 'title' => 'PHP & Elasticsearch', 'author' => 'Alice', 'price' => 19.99],
];

foreach ($docs as $doc) {
    $client->index([
        'index' => $index,
        'id'    => $doc['id'],
        'body'  => $doc
    ]);
}
echo "Documents indexed.\n";

// Step 5: Search with filter + pagination
$page = 1;
$size = 2;
$from = ($page - 1) * $size;

$params = [
    'index' => $index,
    'body' => [
        'query' => [
            'bool' => [
                'must' => [
                    ['match' => ['title' => 'Elasticsearch']]
                ],
                'filter' => [
                    ['range' => ['price' => ['lte' => 40]]]
                ]
            ]
        ],
        'from' => $from,
        'size' => $size,
        'sort' => [
            ['price' => ['order' => 'asc']]
        ]
    ]
];

$response = $client->search($params);

echo "Search results:\n";
foreach ($response['hits']['hits'] as $hit) {
    echo "- {$hit['_source']['title']} ({$hit['_source']['price']})\n";
}

// Step 6: Update one document
$client->update([
    'index' => $index,
    'id'    => 1,
    'body'  => [
        'doc' => ['price' => 24.99]
    ]
]);
echo "Document #1 updated.\n";

// Step 7: Delete one document
$client->delete(['index' => $index, 'id' => 2]);
echo "Document #2 deleted.\n";

// Step 8: Bulk indexing example
$bulkParams = ['body' => []];
$newDocs = [
    ['id' => 4, 'title' => 'Search Engines in Depth', 'author' => 'Bob', 'price' => 49.99],
    ['id' => 5, 'title' => 'New Search Trends', 'author' => 'Charlie', 'price' => 34.50],
];
foreach ($newDocs as $d) {
    $bulkParams['body'][] = [
        'index' => ['_index' => $index, '_id' => $d['id']]
    ];
    $bulkParams['body'][] = $d;
}
$client->bulk($bulkParams);
echo "Bulk documents indexed.\n";

echo "\nDemo completed successfully!\n";
