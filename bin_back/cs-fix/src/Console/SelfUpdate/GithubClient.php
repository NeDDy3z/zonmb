<?php

declare(strict_types=1);











namespace PhpCsFixer\Console\SelfUpdate;




final class GithubClient implements GithubClientInterface
{
private string $url = 'https://api.github.com/repos/PHP-CS-Fixer/PHP-CS-Fixer/tags';

public function getTags(): array
{
$result = @file_get_contents(
$this->url,
false,
stream_context_create([
'http' => [
'header' => 'User-Agent: PHP-CS-Fixer/PHP-CS-Fixer',
],
])
);

if (false === $result) {
throw new \RuntimeException(\sprintf('Failed to load tags at "%s".', $this->url));
}









$result = json_decode($result, true);
if (JSON_ERROR_NONE !== json_last_error()) {
throw new \RuntimeException(\sprintf(
'Failed to read response from "%s" as JSON: %s.',
$this->url,
json_last_error_msg()
));
}

return array_map(
static fn (array $tagData): string => $tagData['name'],
$result
);
}
}
