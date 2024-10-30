<?php

declare(strict_types=1);











namespace PhpCsFixer\RuleSet\Sets;

use PhpCsFixer\RuleSet\AbstractRuleSetDescription;








final class PERCS2x0Set extends AbstractRuleSetDescription
{
public function getName(): string
{
return '@PER-CS2.0';
}

public function getRules(): array
{
return [
'@PER-CS1.0' => true,
'array_indentation' => true,
'array_syntax' => true,
'cast_spaces' => true,
'concat_space' => ['spacing' => 'one'],
'function_declaration' => [
'closure_fn_spacing' => 'none',
],
'method_argument_space' => true,
'new_with_parentheses' => [
'anonymous_class' => false,
],
'single_line_empty_body' => true,
'single_space_around_construct' => [
'constructs_followed_by_a_single_space' => [
'abstract',
'as',
'case',
'catch',
'class',
'const',
'const_import',
'do',
'else',
'elseif',
'enum',
'final',
'finally',
'for',
'foreach',
'function',
'function_import',
'if',
'insteadof',
'interface',
'match',
'named_argument',
'namespace',
'new',
'private',
'protected',
'public',
'readonly',
'static',
'switch',
'trait',
'try',
'type_colon',
'use',
'use_lambda',
'while',
],
'constructs_preceded_by_a_single_space' => [
'as',
'else',
'elseif',
'use_lambda',
],
],
'trailing_comma_in_multiline' => [
'after_heredoc' => true,
'elements' => [
'arguments',
'array_destructuring',
'arrays',
'match',
'parameters',
],
],
];
}

public function getDescription(): string
{
return 'Rules that follow `PER Coding Style 2.0 <https://www.php-fig.org/per/coding-style/>`_.';
}
}
