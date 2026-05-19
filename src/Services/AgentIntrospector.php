<?php

namespace Ashrafic\AiOrbit\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionParameter;

class AgentIntrospector
{
    private const DISPLAY_COLUMN_CANDIDATES = ['name', 'title', 'subject', 'email', 'label', 'slug'];

    private const SCALAR_TYPES = ['string', 'int', 'float', 'bool', 'array'];

    /**
     * Analyze an agent class constructor and classify each parameter.
     *
     * @return array{resolvable: bool, needs_input: bool, params: array<int, array>}
     */
    public function analyzeConstructor(string $agentClass): array
    {
        $params = [];
        $needsInput = false;
        $fullyResolvable = true;

        if (! class_exists($agentClass)) {
            return [
                'resolvable' => false,
                'needs_input' => false,
                'params' => [],
            ];
        }

        $reflection = new ReflectionClass($agentClass);
        $constructor = $reflection->getConstructor();

        if ($constructor === null) {
            return [
                'resolvable' => true,
                'needs_input' => false,
                'params' => [],
            ];
        }

        foreach ($constructor->getParameters() as $parameter) {
            $paramInfo = $this->classifyParameter($parameter, $agentClass);

            $params[] = $paramInfo;

            if (in_array($paramInfo['strategy'], ['eloquent_picker', 'input'], true)) {
                $needsInput = true;
            }

            if ($paramInfo['strategy'] === 'unresolvable') {
                $fullyResolvable = false;
            }
        }

        return [
            'resolvable' => $fullyResolvable,
            'needs_input' => $needsInput,
            'params' => $params,
        ];
    }

    /**
     * Classify a single constructor parameter.
     */
    private function classifyParameter(ReflectionParameter $parameter, string $agentClass): array
    {
        $type = $parameter->getType();
        $typeName = null;

        if ($type instanceof ReflectionNamedType) {
            $typeName = $type->getName();
        }

        $name = $parameter->getName();

        // Strategy 1: Eloquent Model
        if ($typeName !== null && $this->isEloquentModel($typeName)) {
            $displayColumns = $this->detectDisplayColumns($typeName);
            /** @var Model $model */
            $model = new $typeName;

            return [
                'name' => $name,
                'type' => $typeName,
                'strategy' => 'eloquent_picker',
                'label' => class_basename($typeName),
                'table' => $model->getTable(),
                'display_columns' => $displayColumns,
            ];
        }

        // Strategy 2: Container resolvable (interface or concrete class bound in container)
        if ($typeName !== null && $this->isContainerResolvable($typeName, $parameter)) {
            return [
                'name' => $name,
                'type' => $typeName,
                'strategy' => 'container',
            ];
        }

        // Strategy 3: Scalar with default value
        if ($this->isScalarType($typeName)) {
            if ($parameter->isDefaultValueAvailable()) {
                return [
                    'name' => $name,
                    'type' => $typeName ?? 'string',
                    'strategy' => 'default',
                    'default' => $parameter->getDefaultValue(),
                    'input_type' => $this->htmlInputType($typeName),
                ];
            }

            // Strategy 4: Scalar without default
            return [
                'name' => $name,
                'type' => $typeName ?? 'string',
                'strategy' => 'input',
                'input_type' => $this->htmlInputType($typeName),
            ];
        }

        // Strategy 5: Unresolvable
        return [
            'name' => $name,
            'type' => $typeName ?? 'mixed',
            'strategy' => 'unresolvable',
        ];
    }

    /**
     * Fetch recent records for a model to populate the picker dropdown.
     */
    /**
     * Fetch recent records for a model to populate the picker dropdown.
     *
     * @return Collection<int, Model>
     */
    public function getModelRecords(string $modelClass, int $limit = 20): Collection
    {
        if (! $this->isEloquentModel($modelClass)) {
            return collect();
        }

        try {
            return $modelClass::latest()->limit($limit)->get();
        } catch (\Throwable) {
            return collect();
        }
    }

    /**
     * Resolve constructor parameters from user-provided inputs.
     *
     * @param  array<string, mixed>  $userInputs
     * @return array<string, mixed>
     */
    public function resolveParams(string $agentClass, array $userInputs): array
    {
        $analysis = $this->analyzeConstructor($agentClass);
        $resolved = [];

        foreach ($analysis['params'] as $param) {
            $name = $param['name'];
            $input = $userInputs[$name] ?? null;

            $resolved[$name] = match ($param['strategy']) {
                'eloquent_picker' => $this->resolveEloquentModel($param['type'], $input),
                'container' => $this->resolveContainer($param['type']),
                'default' => $input ?? $param['default'],
                'input' => $this->castScalar($input, $param['type']),
                'unresolvable' => null,
                default => $input,
            };
        }

        return $resolved;
    }

    /**
     * Get display values for a model record for the picker option text.
     *
     * @return array<int, string>
     */
    public function getDisplayValues(Model $record): array
    {
        $columns = $this->detectDisplayColumns(get_class($record));
        $values = [];

        foreach ($columns as $column) {
            $value = $record->getAttribute($column);

            if ($value !== null && $value !== '') {
                $values[] = (string) $value;
            }
        }

        return $values;
    }

    /**
     * Check if a type name is an Eloquent Model subclass.
     */
    private function isEloquentModel(?string $typeName): bool
    {
        if ($typeName === null) {
            return false;
        }

        if (! class_exists($typeName)) {
            return false;
        }

        return is_subclass_of($typeName, Model::class);
    }

    /**
     * Check if a type can be resolved from the container.
     */
    private function isContainerResolvable(string $typeName, ReflectionParameter $parameter): bool
    {
        if ($parameter->isDefaultValueAvailable()) {
            return true;
        }

        try {
            App::make($typeName);

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Auto-detect which display columns exist on a model's table.
     *
     * @return array<int, string>
     */
    private function detectDisplayColumns(string $modelClass): array
    {
        try {
            /** @var Model $model */
            $model = new $modelClass;
            $tableColumns = Schema::getColumnListing($model->getTable());

            return array_values(array_intersect(self::DISPLAY_COLUMN_CANDIDATES, $tableColumns));
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * Check if a type is a PHP scalar type.
     */
    private function isScalarType(?string $type): bool
    {
        if ($type === null) {
            return false;
        }

        return in_array($type, self::SCALAR_TYPES, true);
    }

    /**
     * Map a PHP type to an HTML input type.
     */
    private function htmlInputType(?string $type): string
    {
        return match ($type) {
            'int', 'float' => 'number',
            'bool' => 'checkbox',
            default => 'text',
        };
    }

    /**
     * Resolve an Eloquent model from user input (typically a primary key).
     */
    private function resolveEloquentModel(string $modelClass, mixed $input): ?Model
    {
        if (empty($input)) {
            return null;
        }

        try {
            return $modelClass::findOrFail($input);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Resolve a type from the Laravel container.
     */
    private function resolveContainer(string $typeName): mixed
    {
        try {
            return App::make($typeName);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Cast a user input value to the expected PHP scalar type.
     */
    private function castScalar(mixed $value, string $type): mixed
    {
        if ($value === null) {
            return null;
        }

        return match ($type) {
            'int' => (int) $value,
            'float' => (float) $value,
            'bool' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'array' => is_array($value) ? $value : [$value],
            default => (string) $value,
        };
    }
}
