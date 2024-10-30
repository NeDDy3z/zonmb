<?php

declare(strict_types=1);











namespace PhpCsFixer\RuleSet\Sets;

use PhpCsFixer\RuleSet\AbstractMigrationSetDescription;




final class PHP73MigrationSet extends AbstractMigrationSetDescription
{
public function getRules(): array
{
return [
'@PHP71Migration' => true,
'heredoc_indentation' => true,
'method_argument_space' => ['after_heredoc' => true],
'no_whitespace_before_comma_in_array' => ['after_heredoc' => true],
'trailing_comma_in_multiline' => ['after_heredoc' => true],
];
}
}
