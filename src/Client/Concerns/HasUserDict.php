<?php

declare(strict_types=1);

namespace Revolution\Voicevox\Client\Concerns;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;

trait HasUserDict
{
    /**
     * Get User Dictionary Words.
     */
    public function userDict(): array
    {
        return $this->http()->get('user_dict')->json();
    }

    /**
     * Add a word to User Dictionary.
     *
     * @throws RequestException
     * @throws ConnectionException
     */
    public function addWord(string $surface, string $pronunciation, int $accentType, ?string $wordType = null, ?int $priority = null): string
    {
        return $this->http()->withQueryParameters(array_filter([
            'surface' => $surface,
            'pronunciation' => $pronunciation,
            'accent_type' => $accentType,
            'word_type' => $wordType,
            'priority' => $priority,
        ], fn ($v) => ! is_null($v)))
            ->post('user_dict_word')
            ->throw()
            ->json();
    }

    /**
     * Update a word in User Dictionary.
     *
     * @throws RequestException
     * @throws ConnectionException
     */
    public function updateWord(string $wordUuid, string $surface, string $pronunciation, int $accentType, ?string $wordType = null, ?int $priority = null): void
    {
        $this->http()->withQueryParameters(array_filter([
            'surface' => $surface,
            'pronunciation' => $pronunciation,
            'accent_type' => $accentType,
            'word_type' => $wordType,
            'priority' => $priority,
        ], fn ($v) => ! is_null($v)))
            ->put("user_dict_word/{$wordUuid}")
            ->throw();
    }

    /**
     * Delete a word from User Dictionary.
     *
     * @throws RequestException
     * @throws ConnectionException
     */
    public function deleteWord(string $wordUuid): void
    {
        $this->http()->delete("user_dict_word/{$wordUuid}")->throw();
    }

    /**
     * Import User Dictionary.
     *
     * @throws RequestException
     * @throws ConnectionException
     */
    public function importUserDict(array $words, bool $override = false): void
    {
        $this->http()->withQueryParameters(['override' => $override])
            ->post('import_user_dict', $words)->throw();
    }
}
