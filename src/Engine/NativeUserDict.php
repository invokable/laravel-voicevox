<?php

declare(strict_types=1);

namespace Revolution\Voicevox\Engine;

use Revolution\Voicevox\Core\Enums\UserDictWordType;
use Revolution\Voicevox\Core\UserDict;

/**
 * Persists the VOICEVOX core UserDict to storage so the engine endpoints
 * can serve user-dictionary requests without the official engine process.
 *
 * The dict is stored at storage_path('voicevox/user_dict') in the core's
 * native binary format (save/load round-trip).  toJson() is used only to
 * produce the API response JSON.
 */
class NativeUserDict
{
    private readonly UserDict $dict;

    private readonly string $path;

    public function __construct()
    {
        $this->dict = new UserDict();
        $this->path = storage_path('voicevox/user_dict');

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

    /**
     * Remove a word by UUID.
     */
    public function removeWord(string $wordUuid): void
    {
        $this->dict->removeWord($wordUuid);
        $this->save();
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
