<?php

namespace OpenStack\Integration\Images\v2;

use OpenStack\BlockStorage\v2\Models\Snapshot;
use OpenStack\Images\v2\Models\Image;
use OpenStack\Images\v2\Models\Member;
use OpenStack\Integration\TestCase;
use OpenStack\Integration\Utils;

class CoreTest extends TestCase
{
    private $service;

    private function getService(): \OpenStack\Images\v2\Service
    {
        if (null === $this->service) {
            $this->service = Utils::getOpenStack()->imagesV2();
        }

        return $this->service;
    }

    public function runTests()
    {
        $this->startTimer();

        $this->logger->info('-> Images');
        $this->images();

        $this->logger->info('-> Members');
        $this->members();

        $this->logger->info('-> Image list');
        $this->imageList();

        $this->outputTimeTaken();
    }

    public function images()
    {
        $replacements = [
            '{name}'            => 'Ubuntu 12.10',
            '{tag1}'            => 'ubuntu',
            '{tag2}'            => 'quantal',
            '{containerFormat}' => 'bare',
            '{diskFormat}'      => 'qcow2',
            '{visibility}'      => 'private',
        ];

        $this->logStep('Creating image');
        /** @var Image $image */
        require_once $this->sampleFile($replacements, 'images/create.php');
        self::assertInstanceOf(Image::class, $image);

        $replacements = ['{imageId}' => $image->id];

        $this->logStep('Listing images');
        /** @var \Generator $images */
        require_once $this->sampleFile($replacements, 'images/list.php');

        $this->logStep('Getting image');
        /** @var Image $image */
        require_once $this->sampleFile($replacements, 'images/get.php');
        self::assertInstanceOf(Image::class, $image);

        $replacements += [
            '{name}'       => 'newName',
            '{visibility}' => 'private',
        ];

        $this->logStep('Updating image');
        /** @var Image $image */
        require_once $this->sampleFile($replacements, 'images/update.php');

        $this->logStep('Deleting image');
        /** @var Image $image */
        require_once $this->sampleFile($replacements, 'images/delete.php');
    }

    public function members()
    {
        $replacements = [
            '{name}'            => 'Ubuntu 12.10',
            '{tag1}'            => 'ubuntu',
            '{tag2}'            => 'quantal',
            '{containerFormat}' => 'bare',
            '{diskFormat}'      => 'qcow2',
            '{visibility}'      => 'shared',
            'true'              => 'false',
        ];

        $this->logStep('Creating image');
        /** @var Image $image */
        require_once $this->sampleFile($replacements, 'images/create.php');

        $this->logStep(sprintf('Image created with id=%s', $image->id));

        $this->logStep('Adding member');
        $replacements += ['{imageId}' => $image->id];
        /** @var Member $member */
        require_once $this->sampleFile(['{imageId}' => $image->id, ], 'members/add.php');
        self::assertInstanceOf(Member::class, $member);

        $replacements += ['status' => Member::STATUS_REJECTED];
        $this->logStep('Updating member status');
        /** @var Member $member */
        require_once $this->sampleFile($replacements, 'members/update_status.php');
        self::assertInstanceOf(Member::class, $member);

        $this->logStep('Deleting member');
        /** @var Member $member */
        require_once $this->sampleFile($replacements, 'members/delete.php');

        $this->logStep('Deleting image');
        /** @var Image $image */
        require_once $this->sampleFile($replacements, 'images/delete.php');
    }

    public function imageList()
    {
        $this->logStep('Creating image');

        $postfix = $this->randomStr();
        $names = ['b' . $postfix, 'a' . $postfix, 'd' . $postfix, 'c' . $postfix];
        $createdImages = [];
        foreach ($names as $name) {
            $this->logStep("Creating image $name");
            $image = $this->getService()->createImage([
                'name' => $name,
            ]);

            self::assertInstanceOf(Image::class, $image);
            $createdImages[] = $image;
        }


        $this->logStep('Listing images sorted asc');

        $replacements = [
            '{sortKey}' => 'name',
            '{sortDir}' => 'asc',
        ];

        /** @var \OpenStack\Images\v2\Models\Image $image */
        require_once $this->sampleFile($replacements, 'images/list_sorted.php');
        self::assertInstanceOf(Image::class, $image);
        self::assertEquals($names[2], $image->name);


        $this->logStep('Listing images sorted desc');

        $replacements['{sortDir}'] = 'desc';
        /** @var \OpenStack\Images\v2\Models\Image $image */
        require_once $this->sampleFile($replacements, 'images/list_sorted.php');
        self::assertInstanceOf(Image::class, $image);
        self::assertEquals($names[1], $image->name);

        foreach ($createdImages as $image) {
            $this->logStep("Deleting image $image->name");
            $image->delete();
        }
    }

}
