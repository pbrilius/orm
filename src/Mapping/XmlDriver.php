<?php
declare(strict_types=1);

namespace Oryx\ORM\Mapping;

use Doctrine\ORM\Mapping\Driver\SimplifiedXmlDriver;

/**
 * Loads metadata from XML files.
 */
class XmlDriver extends SimplifiedXmlDriver
{
    /**
     * @param string[] $paths      An array of paths where to look for mapping files.
     * @param string   $extension  The extension the mapping files have.
     */
    public function __construct(array $paths, string $extension = '.orm.xml')
    {
        parent::__construct($paths, $extension);
    }
}