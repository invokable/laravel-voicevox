<?php

declare(strict_types=1);

namespace Revolution\Voicevox\Engine;

use Revolution\Voicevox\Core\Enums\UserDictWordType;
use Revolution\Voicevox\Core\UserDict;

/**
 * Persists the VOICEVOX core UserDict to storage so the engine endpoints
 * can serve user-dictionary requests without the official engine process.
 *
 * The dict is stored at storage_path('voicevox/user_dict.json') as plain JSON.
 */
class NativeUserDict
{
    private UserDict $dict;

    private readonly string $path;

    public function __construct()
    {
        $this->dict = new UserDict;
        $this->path = config('voicevox.core.user_dict');

        if (file_exists($this->path)) {
            $this->dict->load($this->path);
        }
    }

    /**
     * Return all words as an associative array (UUID → word data).
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return json_decode($this->dict->toJson(), true) ?? [];
    }

    public function all(): array
    {
        return $this->toArray();
    }

    /**
     * Add a word and return its UUID.
     */
    public function addWord(
        string $surface,
        string $pronunciation,
        int $accentType,
        ?string $wordType = null,
        ?int $priority = null,
    ): string {
        $type = $this->parseWordType($wordType);
        $uuid = $this->dict->addWord($surface, $pronunciation, $accentType, $type, $priority ?? 5);
        $this->save();

        return $uuid;
    }

    public function add(
        string $surface,
        string $pronunciation,
        int $accentType,
        ?string $wordType = null,
        ?int $priority = null,
    ): string {
        return $this->addWord($surface, $pronunciation, $accentType, $wordType, $priority);
    }

    /**
     * Update an existing word by UUID.
     */
    public function updateWord(
        string $wordUuid,
        string $surface,
        string $pronunciation,
        int $accentType,
        ?string $wordType = null,
        ?int $priority = null,
    ): void {
        $type = $this->parseWordType($wordType);
        $this->dict->updateWord($wordUuid, $surface, $pronunciation, $accentType, $type, $priority ?? 5);
        $this->save();
    }

    public function update(
        string $wordUuid,
        string $surface,
        string $pronunciation,
        int $accentType,
        ?string $wordType = null,
        ?int $priority = null,
    ): void {
        $this->updateWord($wordUuid, $surface, $pronunciation, $accentType, $wordType, $priority);
    }

    /**
     * Remove a word by UUID.
     */
    public function removeWord(string $wordUuid): void
    {
        $this->dict->removeWord($wordUuid);
        $this->save();
    }

    public function delete(string $wordUuid): void
    {
        $this->removeWord($wordUuid);
    }

    /**
     * Import words from another user dictionary JSON string.
     * Existing words are preserved; imported words are merged in.
     */
    public function import(string $json, bool $override = false): void
    {
        $tmp = tempnam(sys_get_temp_dir(), 'voicevox_dict_');
        try {
            file_put_contents($tmp, $json);
            $other = new UserDict;
            $other->load($tmp);
            if ($override) {
                $this->dict = $other;
            } else {
                $this->dict->importDict($other);
            }
        } finally {
            @unlink($tmp);
        }
        $this->save();
    }

    /**
     * Return the current dictionary as a JSON string.
     */
    public function toJson(): string
    {
        return $this->dict->toJson();
    }

    private function save(): void
    {
        $dir = dirname($this->path);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $this->dict->save($this->path);
    }

    private function parseWordType(?string $wordType): UserDictWordType
    {
        return match (strtoupper((string) $wordType)) {
            'PROPER_NOUN' => UserDictWordType::ProperNoun,
            'VERB' => UserDictWordType::Verb,
            'ADJECTIVE' => UserDictWordType::Adjective,
            'SUFFIX' => UserDictWordType::Suffix,
            default => UserDictWordType::CommonNoun,
        };
    }
}
