# 🔍 Elasticsearch + PHP Complete Demo

This repository demonstrates how to use **Elasticsearch** with **PHP 7.4** for indexing, searching, CRUD, bulk operations, pagination, and analyzers — all in a single guide.

---

## 🧩 Requirements

| Software | Version | Description |
|-----------|----------|-------------|
| PHP | 7.4+ | Required |
| Composer | Latest | PHP dependency manager |
| Elasticsearch | 7.x | Local or Docker instance |

---

## ⚙️ Installation & Setup

### Step 1: Create Project Directory

```bash
cd /var/www/html
mkdir "PLI 2025-26"
cd "PLI 2025-26"
mkdir elasticsearch_learnings
cd elasticsearch_learnings
```

### Step 2: Install Dependencies

```bash
composer require elasticsearch/elasticsearch:^7.17
```

This installs the official PHP client for Elasticsearch 7.x.

---

## 🧱 Setting up Elasticsearch

### Option A — Install on Ubuntu/Debian

```bash
sudo apt update
sudo apt install elasticsearch
sudo systemctl enable elasticsearch
sudo systemctl start elasticsearch
```

### Option B — Run via Docker

```bash
docker run -d --name elasticsearch   -p 9200:9200   -e "discovery.type=single-node"   docker.elastic.co/elasticsearch/elasticsearch:7.17.9
```

### Verify Installation

```bash
curl -X GET "http://localhost:9200"
```

Expected output:

```json
{
  "name": "node-1",
  "cluster_name": "elasticsearch",
  "version": { "number": "7.17.9" },
  "tagline": "You Know, for Search"
}
```

---

## 💻 PHP Demo Code

Create a new file called **demo.php** and paste the following:

```php
<?php
require __DIR__ . '/vendor/autoload.php';
use Elasticsearch\ClientBuilder;

$client = ClientBuilder::create()
    ->setHosts(['http://127.0.0.1:9200'])
    ->build();

// 1️⃣ Create Index with Mapping
$params = [
    'index' => 'products',
    'body'  => [
        'mappings' => [
            'properties' => [
                'name' => ['type' => 'text', 'analyzer' => 'standard'],
                'category' => ['type' => 'keyword'],
                'price' => ['type' => 'float'],
                'created_at' => ['type' => 'date']
            ]
        ]
    ]
];

try {
    $client->indices()->create($params);
    echo "✅ Index created successfully\n";
} catch (Exception $e) {
    echo "⚠️ Index may already exist: " . $e->getMessage() . "\n";
}

// 2️⃣ Insert Documents
$docs = [
    ['name' => 'Elasticsearch for Beginners', 'category' => 'books', 'price' => 24.99],
    ['name' => 'Advanced Elasticsearch', 'category' => 'books', 'price' => 29.99],
    ['name' => 'PHP & Elasticsearch', 'category' => 'courses', 'price' => 19.99],
];

foreach ($docs as $id => $doc) {
    $client->index([
        'index' => 'products',
        'id' => $id + 1,
        'body' => $doc
    ]);
}
echo "✅ Documents indexed\n";

// 3️⃣ Basic Search + Filter
$params = [
    'index' => 'products',
    'body' => [
        'query' => [
            'bool' => [
                'must' => [['match' => ['name' => 'Elasticsearch']]],
                'filter' => [['term' => ['category' => 'books']]]
            ]
        ]
    ]
];

$response = $client->search($params);
echo "🔍 Search Results:\n";
foreach ($response['hits']['hits'] as $hit) {
    echo "- " . $hit['_source']['name'] . " (" . $hit['_source']['price'] . ")\n";
}

// 4️⃣ Update Document
$client->update([
    'index' => 'products',
    'id' => 1,
    'body' => ['doc' => ['price' => 22.50]]
]);
echo "✅ Document #1 updated\n";

// 5️⃣ Delete Document
$client->delete(['index' => 'products', 'id' => 2]);
echo "🗑️ Document #2 deleted\n";

// 6️⃣ Bulk Operations
$bulk = [
    'body' => [
        ['index' => ['_index' => 'products', '_id' => 10]],
        ['name' => 'Elastic Search Course', 'category' => 'courses', 'price' => 30],
        ['index' => ['_index' => 'products', '_id' => 11]],
        ['name' => 'Elastic Book', 'category' => 'books', 'price' => 15],
    ]
];
$client->bulk($bulk);
echo "✅ Bulk documents indexed\n";

// 7️⃣ Pagination
$params = [
    'index' => 'products',
    'body' => [
        'from' => 0,
        'size' => 2,
        'query' => ['match_all' => (object)[]]
    ]
];

$response = $client->search($params);
echo "📄 Paginated Results:\n";
foreach ($response['hits']['hits'] as $hit) {
    echo "- " . $hit['_source']['name'] . "\n";
}

echo "\n🎉 Demo completed successfully!\n";
```
---

## 🧾 Troubleshooting

| Error | Cause | Fix |
|-------|--------|-----|
| `Class 'Elasticsearch\ClientBuilder' not found` | Missing library | Run `composer require elasticsearch/elasticsearch:^7.17` |
| `No alive nodes found in your cluster` | Elasticsearch not running | Start service or Docker container |
| `Connection refused` | Wrong host or port | Ensure Elasticsearch runs at `http://localhost:9200` |

---

## 📦 Folder Structure

```
elasticsearch_learnings/
├── demo.php
├── composer.json
├── composer.lock
├── vendor/
└── README.md
```

---
