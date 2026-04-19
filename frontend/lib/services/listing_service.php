<?php

namespace Lib\services;

require_once __DIR__ . '/../../config/bootstrap.php';

use Lib\db\MongoDB;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;

class listing_service{

    private \MongoDB\Collection $collection;

    public function __construct()
    {
        $this->collection = MongoDB::getDatabase()->selectCollection('listings');
    }

    public function createListing(string $uid, string $name, string $description) {
        $document = [
            '_id' => new ObjectId(),
            'uid' => $uid,
            'name' => $name,
            'description' => $description,
            'created_at' => new UTCDateTime(),
            'updated_at' => new UTCDateTime(),
        ];

        try {
            $result = $this->collection->insertOne($document);

            if ($result->getInsertedCount() !== 1) {
                error_log("Thisting insert failed - no document written");
                throw new  \RuntimeException("Error Listing failed to be created");
            }

            return [
                'id' => (string) $result->getInsertedId(),
                'name' => $name,
                'description' => $description,
            ];
        } catch (\MongoDB\Driver\Exception\BulkWriteException $e) {
            error_log("MongoDB write error: " . $e->getMessage());
            throw new \RuntimeException("Failed to create listing.");
        }
    }
}