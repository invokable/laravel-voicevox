<?php

declare(strict_types=1);

namespace Revolution\Voicevox\Engine;

use Illuminate\Support\Arr;

/**
 * JSON-file–backed preset store for the VOICEVOX engine endpoints.
 *
 * Presets are persisted at storage_path('voicevox/presets.json') so that
 * the engine routes can serve and manage presets without the official engine
 * running.  The file format is a JSON array of Preset objects, matching the
 * schema used by /presets, /add_preset, /update_preset and /delete_preset.
 */
class NativePresetStore
{
    private readonly string $path;

    /** @var list<array<string, mixed>> */
    private array $presets;

    public function __construct(?string $path = null)
    {
        $this->path = $path ?? config('voicevox.core.presets');
        $this->presets = $this->load();
    }

    /**
     * Return all presets.
     *
     * @return list<array<string, mixed>>
     */
    public function all(): array
    {
        return $this->presets;
    }

    /**
     * Find a preset by ID, or null when not found.
     *
     * @return array<string, mixed>|null
     */
    public function find(int $id): ?array
    {
        foreach ($this->presets as $preset) {
            if ($preset['id'] === $id) {
                return $preset;
            }
        }

        return null;
    }

    /**
     * Add a new preset and return its assigned ID.
     *
     * If the requested ID already exists the preset receives a new
     * auto-incremented ID (matching official engine behaviour).
     *
     * @param  array<string, mixed>  $preset
     */
    public function add(array $preset): int
    {
        $requestedId = (int) Arr::get($preset, 'id', 0);

        // Auto-assign an ID when the requested one is already taken
        if ($requestedId === 0 || $this->find($requestedId) !== null) {
            $preset['id'] = $this->nextId();
        }

        $this->presets[] = $preset;
        $this->save();

        return (int) $preset['id'];
    }

    /**
     * Update an existing preset (matched by ID) and return its ID.
     *
     * @param  array<string, mixed>  $preset
     */
    public function update(array $preset): int
    {
        $id = (int) Arr::get($preset, 'id', 0);

        $this->presets = array_map(
            fn (array $p) => $p['id'] === $id ? $preset : $p,
            $this->presets,
        );

        $this->save();

        return $id;
    }

    /**
     * Delete the preset with the given ID.
     */
    public function delete(int $id): void
    {
        $this->presets = array_values(
            array_filter($this->presets, fn (array $p) => $p['id'] !== $id),
        );

        $this->save();
    }

    // -------------------------------------------------------------------------

    /** @return list<array<string, mixed>> */
    private function load(): array
    {
        if (! file_exists($this->path)) {
            return [];
        }

        $data = json_decode((string) file_get_contents($this->path), true);

        return is_array($data) ? array_values($data) : [];
    }

    private function save(): void
    {
        $dir = dirname($this->path);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, recursive: true);
        }

        file_put_contents($this->path, json_encode($this->presets, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    private function nextId(): int
    {
        if (empty($this->presets)) {
            return 1;
        }

        return max(array_column($this->presets, 'id')) + 1;
    }
}
