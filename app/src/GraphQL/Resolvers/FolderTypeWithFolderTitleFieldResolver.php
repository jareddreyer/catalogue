<?php

namespace App\Catalogue\GraphQL\Resolvers;

use SilverStripe\AssetAdmin\GraphQL\Resolvers\FolderTypeResolver;

class FolderTypeWithFolderTitleFieldResolver extends FolderTypeResolver
{

    /**
     * Grabs FolderTitle from a Folder object and returns it
     * if its not set then we default back to Name.
     *
     * We basically do this because graphql is incredibly complex to override or inject a new
     * field without first creating new types and then creating a new resolver.
     */
    public static function resolveFolderTitle($object): string|null
    {
        return $object->FolderTitle ?? $object->Name;
    }

}
