<?php

namespace App\Catalogue\Tests;

use App\Catalogue\Models\Catalogue;
use App\Catalogue\Traits\CatalogueTrait;
use SilverStripe\Assets\Dev\TestAssetStore;
use SilverStripe\Assets\File;
use SilverStripe\Assets\Folder;
use SilverStripe\Assets\Image;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\ORM\ValidationException;
use SilverStripe\Versioned\Versioned;

class CatalogueTraitTest extends SapphireTest
{
    protected static $fixture_file = '../Fixtures/Catalogue.yml';

    protected function setUp(): void
    {
        parent::setUp();

        $this->logInWithPermission('ADMIN');
        Versioned::set_stage(Versioned::DRAFT);

        // In SilverStripe asset stores/folders do not exist when they have no file objects.
        // So we set up a dummy file in our test asset store so it actually exists.
        foreach (File::get()->exclude('ClassName', Folder::class) as $file) {
            /** @var File $file */

            $file->File->Hash = sha1('version 1');
            $file->write();

            // Create variant for each file
            $file->setFromString(
                'version 1',
                $file->getFilename(),
                $file->getHash(),
                null,
            );
        }
    }

    /**
     * @throws ValidationException
     */
    public function testFindOrCreateAssetFolders(): void
    {
        $movieData = $this->objFromFixture(Catalogue::class,'movie_alien_vs_predator');
        $trait = $this->getMockForTrait(CatalogueTrait::class);

        $cleanedMetadataFolderTitle = $trait::getSanitizedTitle($movieData->Title);
        $expectedParts = [
            'folder_name' => $cleanedMetadataFolderTitle . '-' . $movieData->ImdbID,
            'folder_title' => $movieData->Title . ' (' . $movieData->Year . ')',
        ];

        // Get our folder for this title.
        $createCatalogueItemFolder = $trait::findOrCreateAssetFolders(
            $expectedParts['folder_name'],
            $expectedParts['folder_title'],
        );

        $this->assertEquals($expectedParts['folder_title'], $createCatalogueItemFolder->FolderTitle);
        $this->assertEquals($expectedParts['folder_name'], $createCatalogueItemFolder->Name);

        // Simulate we've saved a metadata file to this folder.
        $metadataFile = $this->objFromFixture(File::class, 'movie_alien_vs_predator_metadata');
        $metadataFile->ParentID = $createCatalogueItemFolder->ID;
        $metadataFile->write();
        $metadataFile->publishSingle();

        $this->assertEquals(
            $createCatalogueItemFolder->ID,
            $metadataFile->ParentID,
            'ParentID should be root when creating a new catalogue item.'
        );

        // Get our folder for this title.
        $updateCatalogueItemFolder = $trait::findOrCreateAssetFolders(
            $expectedParts['folder_name'],
            $expectedParts['folder_title'],
            1
        );

        // Simulate we saved a poster image to this folder.
        $poster = $this->objFromFixture(Image::class, 'movie_alien_vs_predator_poster');
        $poster->ParentID = $createCatalogueItemFolder->ID;
        $poster->write();
        $poster->publishSingle();

        $this->assertEquals(
            $updateCatalogueItemFolder->ID,
            $poster->ParentID,
        'When saving a poster file the ParentID should remain'
        );
        $this->assertEquals(
            $createCatalogueItemFolder->ID,
            $updateCatalogueItemFolder->ID,
        'ParentID should match if we are updating a preexisting catalogue item.'
        );

        /**
         * The {@see CatalogueTrait::findOrCreateAssetFolders()} method
         * does not create either metadata or poster files, but this provides us extra coverage
         * for working inside a catalogue items' folder.
         * Thus, we finally check our title folder has 2 items, metadata file and poster image.
         */
        $folder = Folder::get()->filter('Name', $expectedParts['folder_name'])->first();
        $this->assertTrue($folder->hasChildren(), 'Catalogue folder is updated and should have children');
        $this->assertCount(2, $folder->myChildren());
        $this->assertEquals(
            'Alien-vs-Predator-tt0370263.jpg',
            $folder->myChildren()->find('ID', $poster->ID)->Name
        );
        $this->assertEquals(
            'Alien-vs-Predator-tt0370263.txt',
            $folder->myChildren()->find('ID', $metadataFile->ID)->Name
        );
    }

    /**
     * @dataProvider sanitizedTitleDataProvider
     */
    public function testGetSanitizedTitle(string $title, string $expected): void
    {
        // Get a stub for our trait.
        $trait = $this->getMockForTrait(CatalogueTrait::class);

        // Test an actual catalogue item.
        $movieData = $this->objFromFixture(Catalogue::class,'movie_alien_vs_predator');
        $this->assertEquals('Alien-vs-Predator', $trait::getSanitizedTitle($movieData->Title));

        // Now run through made up examples.
        $this->assertEquals($expected, $trait::getSanitizedTitle($title));
    }

    /**
     * @return string[][]
     */
    private function sanitizedTitleDataProvider(): array
    {
        return [
            ['Hello!! This___ is a-- test__string', 'Hello-This-is-a-test-string'],
            ['No special characters or consecutive hyphens here', 'No-special-characters-or-consecutive-hyphens-here'],
            ['@$!^&*()_+=-Cool--!Runnings', 'Cool-Runnings'],
            ['----', ''],
            ['--The---D@rk--Knight--', 'The-D-rk-Knight']
        ];
    }

    protected function tearDown(): void
    {
        TestAssetStore::reset();
        parent::tearDown();
    }
}
