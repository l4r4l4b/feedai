<?php

use Symfony\Component\Yaml\Yaml;

if (! function_exists('buildAgentComponentReference')) {
    /**
     * Builds the component reference block injected into agent system prompts
     * from every YAML in config/feedai/component-schemas/.
     *
     * Each component gets a semantic block (purpose / use_when / avoid_when /
     * examples) plus the field schema. The semantic part drives the agent's
     * component selection when a vendor describes an idea in chat.
     */
    function buildAgentComponentReference(): string
    {
        $schemaDir = config_path('feedai/component-schemas');
        $files = glob($schemaDir.'/*.yaml') ?: [];
        sort($files);

        $blocks = [];

        foreach ($files as $path) {
            /** @var array<string, mixed> $schema */
            $schema = Yaml::parseFile($path);
            if (! is_array($schema)) {
                continue;
            }

            $type = (string) ($schema['type'] ?? basename($path, '.yaml'));
            $label = (string) ($schema['label'] ?? $type);
            $description = (string) ($schema['description'] ?? '');

            $lines = ["### `{$type}` · {$label}"];

            if ($description !== '') {
                $lines[] = $description;
            }

            $agent = is_array($schema['agent'] ?? null) ? $schema['agent'] : [];

            if (! empty($agent['purpose'])) {
                $lines[] = '';
                $lines[] = '**Purpose:** '.$agent['purpose'];
            }

            if (! empty($agent['use_when']) && is_array($agent['use_when'])) {
                $lines[] = '';
                $lines[] = '**Use when:**';
                foreach ($agent['use_when'] as $entry) {
                    $lines[] = '  - '.$entry;
                }
            }

            if (! empty($agent['avoid_when']) && is_array($agent['avoid_when'])) {
                $lines[] = '';
                $lines[] = '**Avoid when:**';
                foreach ($agent['avoid_when'] as $entry) {
                    $lines[] = '  - '.$entry;
                }
            }

            if (! empty($agent['examples']) && is_array($agent['examples'])) {
                $lines[] = '';
                $lines[] = '**Examples:**';
                foreach ($agent['examples'] as $entry) {
                    $lines[] = '  - '.$entry;
                }
            }

            // Field schema
            $fieldsList = [];
            foreach (($schema['fields'] ?? []) as $name => $definition) {
                if (! is_array($definition)) {
                    continue;
                }
                $required = ($definition['required'] ?? false) ? ' [required]' : '';
                $fieldType = $definition['type'] ?? 'string';
                $fieldDesc = ! empty($definition['description']) ? ': '.$definition['description'] : '';
                $fieldsList[] = "  - `{$name}` ({$fieldType}){$required}{$fieldDesc}";
            }

            if ($fieldsList !== []) {
                $lines[] = '';
                $lines[] = '**Fields:**';
                $lines = array_merge($lines, $fieldsList);
            }

            $blocks[] = implode("\n", $lines);
        }

        return implode("\n\n", $blocks);
    }
}
