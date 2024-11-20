<?php

declare(strict_types=1);











namespace PhpCsFixer\Cache;

use PhpCsFixer\Utils;






final class Cache implements CacheInterface
{
private SignatureInterface $signature;




private array $hashes = [];

public function __construct(SignatureInterface $signature)
{
$this->signature = $signature;
}

public function getSignature(): SignatureInterface
{
return $this->signature;
}

public function has(string $file): bool
{
return \array_key_exists($file, $this->hashes);
}

public function get(string $file): ?string
{
if (!$this->has($file)) {
return null;
}

return $this->hashes[$file];
}

public function set(string $file, string $hash): void
{
$this->hashes[$file] = $hash;
}

public function clear(string $file): void
{
unset($this->hashes[$file]);
}

public function toJson(): string
{
$json = json_encode([
'php' => $this->getSignature()->getPhpVersion(),
'version' => $this->getSignature()->getFixerVersion(),
'indent' => $this->getSignature()->getIndent(),
'lineEnding' => $this->getSignature()->getLineEnding(),
'rules' => $this->getSignature()->getRules(),
'hashes' => $this->hashes,
]);

if (JSON_ERROR_NONE !== json_last_error() || false === $json) {
throw new \UnexpectedValueException(\sprintf(
'Cannot encode cache signature to JSON, error: "%s". If you have non-UTF8 chars in your signature, like in license for `header_comment`, consider enabling `ext-mbstring` or install `symfony/polyfill-mbstring`.',
json_last_error_msg()
));
}

return $json;
}




public static function fromJson(string $json): self
{
$data = json_decode($json, true);

if (null === $data && JSON_ERROR_NONE !== json_last_error()) {
throw new \InvalidArgumentException(\sprintf(
'Value needs to be a valid JSON string, got "%s", error: "%s".',
$json,
json_last_error_msg()
));
}

$requiredKeys = [
'php',
'version',
'indent',
'lineEnding',
'rules',
'hashes',
];

$missingKeys = array_diff_key(array_flip($requiredKeys), $data);

if (\count($missingKeys) > 0) {
throw new \InvalidArgumentException(\sprintf(
'JSON data is missing keys %s',
Utils::naturalLanguageJoin(array_keys($missingKeys))
));
}

$signature = new Signature(
$data['php'],
$data['version'],
$data['indent'],
$data['lineEnding'],
$data['rules']
);

$cache = new self($signature);



$cache->hashes = array_map(static fn ($v): string => \is_int($v) ? (string) $v : $v, $data['hashes']);

return $cache;
}




public function backfillHashes(self $oldCache): bool
{
if (!$this->getSignature()->equals($oldCache->getSignature())) {
return false;
}

$this->hashes = array_merge($oldCache->hashes, $this->hashes);

return true;
}
}
