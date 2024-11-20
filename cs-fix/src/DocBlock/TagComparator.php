<?php

declare(strict_types=1);











namespace PhpCsFixer\DocBlock;










final class TagComparator
{







public const DEFAULT_GROUPS = [
['deprecated', 'link', 'see', 'since'],
['author', 'copyright', 'license'],
['category', 'package', 'subpackage'],
['property', 'property-read', 'property-write'],
];






public static function shouldBeTogether(Tag $first, Tag $second, array $groups = self::DEFAULT_GROUPS): bool
{
@trigger_error('Method '.__METHOD__.' is deprecated and will be removed in version 4.0.', E_USER_DEPRECATED);

$firstName = $first->getName();
$secondName = $second->getName();

if ($firstName === $secondName) {
return true;
}

foreach ($groups as $group) {
if (\in_array($firstName, $group, true) && \in_array($secondName, $group, true)) {
return true;
}
}

return false;
}
}
