<?php

require 'vendor/autoload.php';

$openstack = new OpenStack\OpenStack([
    'authUrl' => '{authUrl}',
    'region'  => '{region}',
    'user'    => ['id' => '{userId}', 'password' => '{password}'],
    'scope'   => ['project' => ['id' => '{projectId}']]
]);

$service = $openstack->blockStorageV2();

$snapshots = $service->listSnapshots(false, ['sortKey' => '{sortKey}', 'sortDir' => '{sortDir}']);

foreach ($snapshots as $snapshot) {
    /** @var $snapshot \OpenStack\BlockStorage\v2\Models\Snapshot */
}
